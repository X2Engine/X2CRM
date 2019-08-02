<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/






require_once realpath(
    dirname(__FILE__) . 
    '/../../../integration/Google/google-api-php-client/src/Google/autoload.php');

/**
 * Wrapper around Google+ API client. Adds caching layers and rendering/utility methods.
 */
class GooglePlusResources extends CComponent {

    /**
     * Number of activities to fetch from cache at a time
     */
    const PAGE_SIZE = 5;

    /**
     * Number of activities to fetch via g+ API at a time 
     */
    const MAX_RESULTS = 10;

    public $userId;

    private static $_credentials;

    private $_cacheEntry;

    /**
     * Get user-friendly error message based on google exception
     */
    public static function getErrorMessage (Google_Exception $exception) {
        switch (get_class ($exception)) {
            case 'Google_Auth_Exception':
                return (Yii::t('app', 'Google API authentication failed.'));
            case 'Google_Service_Exception':
                return (Yii::t('app', 'Google+ profile could not be retrieved.'));
            default:
                return (
                    Yii::t('app', 'An error occurred when attempting to access this Google+ '.
                        'profile.'));
        }
    }

    public static function integrationIsEnabled () {
        return Yii::app()->settings->googleIntegration && 
            GooglePlusResources::getGooglePlusAPICredentials ();
    }

    /**
     * @return Credentials|null
     */
    public static function getGooglePlusAPICredentials () {
        if (!isset (self::$_credentials)) {
            $credId = Yii::app()->settings->googleCredentialsId;
            if ($credId && ($credentials = Credentials::model ()->findByPk ($credId))) {
                self::$_credentials = array(
                    'apiKey' => $credentials->auth->apiKey,
                );
            }
        }
        return self::$_credentials;
    }

    /**
     * Meant to be called immediately after class instantiation
     * @throws Google_Exception
     */
    public function init () {
        // request profile and activity data from API, or retrieve from cache
        $cacheKey = $this->getCacheKey ();
        $cache = Yii::app()->cache2;
        $cacheEntry = $cache->get ($cacheKey);
        if (!$cacheEntry) {
            $cacheEntry = $this->refreshCache ();
        }
        // being called just for side-effects to $cacheEntry
        $this->getActivities ($cacheEntry); // check if activities cache needs to be expanded
        $this->_cacheEntry = $cacheEntry;
    }

    /**
     * @return bool whether or not there's another page of activities 
     */
    private $_hasNextPage = false;
    public function getHasNextPage () {
        return $this->_hasNextPage;
    }

    /**
     * @param bool $hasNextPage 
     */
    public function setHasNextPage ($hasNextPage) {
        $this->_hasNextPage = $hasNextPage;
    }

    private $_purifier;
    public function getPurifier () {
        if (!isset ($this->_purifier)) {
            $this->_purifier = new CHtmlPurifier ();
        }
        return $this->_purifier;
    }

    /**
     * Retrieve g+ API client 
     */
    private $_client;
    public function getClient () {
        if (!isset ($this->_client)) {
            $client = new Google_Client();
            $creds = self::getGooglePlusAPICredentials ();
            $client->setDeveloperKey ($creds['apiKey']);
            $client->setUseBatch (true);
            $this->_client = $client;
        }
        return $this->_client;
    }

    /**
     * Retrieve g+ secondary API client 
     */
    private $_service;
    public function getService () {
        if (!isset ($this->_service)) {
            $plus = new Google_Service_Plus($this->getClient ());
            $this->_service = $plus;
        }
        return $this->_service;
    }

    /**
     * @return Google_Service_Plus_Person 
     */
    private $_profile;
    public function getProfile () {
        if (!isset ($this->_profile)) {
            $cacheEntry = $this->_cacheEntry;
            $this->_profile = $cacheEntry->person;
        }
        return $this->_profile;
    }

    /**
     * Load profile and activities into cache 
     * @throws Google_Exception
     */
    public function refreshCache () {
        $cacheEntry = new GooglePlusCacheEntry;
        $client = $this->getClient ();
        $plus = $this->getService ();
        $batch = new Google_Http_Batch ($client);
        $profileResource = $this->getProfileResource ($plus);
        $batch->add ($profileResource, 'person');
        $activitiesResource = $this->getActivitiesResource ($plus);
        $batch->add ($activitiesResource, 'activities');
        $results = $batch->execute ();
        $cacheEntry->person = $results['response-person'];
        $cacheEntry->activities = $this->makeActivitiesArray ($results['response-activities']);

        if ($cacheEntry->person instanceof Google_Exception) {
            throw $cacheEntry->person;
        }

        $this->updateAlias ($cacheEntry->person->displayName);
        return $cacheEntry;
    }

    /**
     * Update label of g+ alias with fresh data from API request
     */
    public function updateAlias ($displayName) {
        Yii::app()->db->createCommand ()
            ->update (
                'x2_record_aliases', array ('label' => $displayName), 
                'alias=:alias', array (':alias' => $this->userId));
    }

    /**
     * Retrieve requested pages from profile activities cache, expanding cache if necessary 
     * @param GooglePlusCacheEntry|null cache entry from which activities will be retrieved. This
     *  must be included in the initial invokation of this method.
     * @param mixed $nextPageToken token included in API response which can be used to retrieve
     *  the next page of activities via API. If false, the first page is retrieved.
     * @return array 
     */
    private $_activities;
    public function getActivities ($cacheEntry=null, $nextPageToken=false) {
        assert (!(!$cacheEntry && !isset ($this->_activities)));

        if (!isset ($this->_activities)) {
            $maxId = isset ($_GET['maxActivityId']) ? (int) $_GET['maxActivityId'] : -1;
            if ($maxId < -1) $maxId = -1;

            if ($nextPageToken) {
                // Expand cache 

                $plus = $this->getService ();
                $params = array (
                    'pageToken' => $nextPageToken,
                );
                $activities = $this->getActivitiesResource ($plus, $params);

                $cacheEntry->activities = array_merge (
                    $cacheEntry->activities,
                    $this->makeActivitiesArray ($activities)
                );
                $cacheEntry->nextPageToken = $activities->nextPageToken;
                $activities = $cacheEntry->activities;
                $cache->set ($cacheKey, $cacheEntry, 60 * 5);
            } else {
                $activities = $cacheEntry->activities;
            }

            if ($maxId === -1) { // requesting first page
                $activities = array_slice ($activities, 0, self::PAGE_SIZE);
            } else {
                if ($maxId + self::PAGE_SIZE < count ($activities)) {
                    // requested page is inside cache
                    $activities = array_slice (
                        $activities, 0, $maxId + self::PAGE_SIZE);
                } elseif (!$nextPageToken && // only request another page once
                    $cacheEntry->nextPageToken) {

                    return $this->getActivities ($cacheEntry, $cacheEntry->nextPageToken); 
                } // else
                    // there's no extra page to fetch via API, so just return all the available
                    // activities
            }

            // there's another page if either there are remaining entries in the cache or another
            // page can be fetched via API
            $this->hasNextPage = count ($activities) < count ($cacheEntry->activities) ||
                (bool) $cacheEntry->nextPageToken;
            $this->_activities = $activities;
        }
        return $this->_activities;
    }

    /**
     * Adjusts GET parameter which specifies image size 
     */
    public function adjustImageUrlSize ($imageUrl, $newSize) {
        return UrlUtil::mergeParams ($imageUrl, array (
            'sz' => $newSize,
        ));
    }

    /**
     * Convert g+ API client activities iterator object into an array. Allows activity pages to
     * be more easily merged.
     */
    public function makeActivitiesArray ($activities) {
        if (!is_object ($activities) || 
            !($activities instanceof Google_Service_Plus_ActivityFeed)) {

            return array ();
        }
        $posts = array ();
        // convert iterator into array
        foreach ($activities as $post) $posts[] = $post;
        return $posts;
    }

    /**
     * Render display name of profile
     * @return string 
     */
    public function renderName () {
        return CHtml::link (
            CHtml::encode ($this->getProfile ()->displayName),
            $this->getProfile ()->url, 
            array ('class' => 'profile-name min-link', 'target' => '_blank'));
    }

//    public function renderFollowers () {
//        return '<b>'.$this->getProfile ()->circledByCount.'</b> '.Yii::t('app', 'followers');
//    }

    /**
     * Render profile image 
     * @return string
     */
    public function renderImage () {
        if ($this->getProfile ()->image) {
            $url = $this->adjustImageUrlSize ($this->getProfile ()->image->url, 70);
            return CHtml::image ($url, 'Profile Image', array (
                'class' => 'profile-image',
            ));
        }
    }

    /**
     * @return string 
     */
    public function renderActivityMetaData ($activity) {
        if (!($activity instanceof Google_Service_Plus_Activity)) return;
        $timePublished = @strtotime ($activity->published);
        $html = CHtml::openTag ('span', array (
            'class' => 'date-published',
        ));

        if (Formatter::isToday ($timePublished)) {
            $html .= CHtml::encode (Yii::t('app', 'Shared at {time}', array (
                '{time}' => Yii::app()->dateFormatter->format ('h:mm a', $timePublished),
            )));
        } else {
            $html .= CHtml::encode (Yii::t('app', 'Shared on {time}', array (
                '{time}' => Yii::app()->dateFormatter->formatDateTime (
                    $timePublished, 'medium', null),
            )));
        }
        $html .= CHtml::closeTag ('span');
        return $html;
    }

    /**
     * Render an activity  
     * @return string
     */
    public function renderObject ($object) {
        if (!($object instanceof Google_Service_Plus_ActivityObject)) return;

        $html = '';
        $purifier = $this->getPurifier ();

        if ($object->content) {
            $html .= CHtml::tag ('div', array (
                'class' => 'object-content',
            ), $purifier->purify ($object->content));
        }

        if ($object->attachments) {
            foreach ($object->attachments as $attachment) {

                switch ($attachment->objectType) {
                    case 'photo':
                        if ($attachment->image) {
                            $html .= CHtml::openTag ('div', array (
                                'class' => 'photo-image-container',
                            ));
                                $html .= CHtml::image (
                                    $attachment->image->url, 'Attachment Image', array (
                                        'class' => 'photo-image',
                                    ));
                            $html .= CHtml::closeTag ('div');
                        }
                        break;
                    case 'article':
                        $html .= '<div class="article-container">';
                            if ($attachment->image) {
                                $html .= CHtml::link (
                                    CHtml::image (
                                        $attachment->image->url, 'Attachment Image', array (
                                            'class' => 'article-image',
                                        )), 
                                    $attachment->url);
                            }
                            $html .= '<div class="article-text-content-container">';
                                $html .= CHtml::link (
                                    CHtml::encode (
                                        $attachment->displayName), $attachment->url, array (
                                            'class' => 'article-display-name',
                                        ));
                                $html .= CHtml::link (
                                    preg_replace ('/^https?:\/\//', '', $attachment->url), 
                                    $attachment->url, array (
                                        'class' => 'article-url',
                                    ));
                                if ($attachment->content) {
                                    $html .= CHtml::tag ('div', array (
                                        'class' => 'article-content',
                                    ), CHtml::encode ($attachment->content));
                                }
                            $html .= '</div>';
                        $html .= '</div>';
                        break;
                    case 'album':
                        break;
                    case 'event':
                        $html .= CHtml::link (
                            CHtml::encode ($attachment->displayName), $attachment->url, array (
                                'class' => 'event-display-name x2-hint',
                                'title' => 'View event'
                            ));
                        break;
                    case 'video':
                        if ($attachment->image && $attachment->embed) {
                            // YouTube embed
                            $html .= CHtml::openTag ('div', array (
                                'class' => 'video-container',
                            ));
                                $html .= CHtml::image (
                                    $attachment->image->url, '', array (
                                        'class' => 'video-image',
                                        'data-embed' => UrlUtil::mergeParams (
                                            $attachment->embed->url, array ('autoplay' => 1))
                                    ));
                                $html .= CHtml::tag ('div', array (
                                    'class' => 'video-play-button fa fa-play'
                                ), ' ');
                            $html .= CHtml::closeTag ('div');
                        } elseif ($attachment->url && $attachment->image) {
                            // video upload
                            $html .= CHtml::openTag ('div', array (
                                'class' => 'video-container video-upload-container',
                            ));
                                $html .= CHtml::link (
                                    CHtml::image (
                                        $attachment->image->url, '', array (
                                            'class' => 'video-image',
                                        )),
                                    $attachment->url, array (
                                        'target' => '_blank',
                                    )
                                );
                            $html .= CHtml::closeTag ('div');
                        }
                        break;
                    default: 
                        break;
                }
            }
        }
        return $html;
    }

    /**
     * Get profile activities data profider 
     * @return CArrayDataProvider
     */
    public function getDataProvider () {
        return new CArrayDataProvider ($this->getActivities (), array (
            'pagination' => array (
                'pageSize' => PHP_INT_MAX,
            ),
        ));
    }

    /**
     * @return string posts cache key 
     */
    private function getCacheKey () {
        $username = $this->userId;
        return 'GooglePlusResource'.$username;
    }

    private function getProfileResource ($plus) {
        $params = array (
            'fields' => 'displayName,url,image/url'
        );
        return $plus->people->get ($this->userId, $params);
    }

    /**
     * Specifies fields for partial API response
     */
    private function getActivitiesFieldsParam () {
        return 'nextPageToken,'.
            'items('.
                'published,object('.
                    'content,attachments('.
                        'image/url,displayName,url,content,embed,embed/url,objectType)))';
    }

    private function getActivitiesResource ($plus, array $params=array ()) {
        return $plus->activities->listActivities ($this->userId, 'public', array_merge (array (
            'maxResults' => self::MAX_RESULTS,
            'fields' => $this->getActivitiesFieldsParam ()
        ), $params));
    }

}

/**
 * Used to structure cached API responses
 */
class GooglePlusCacheEntry {
    
    public $nextPageToken;

    /**
     * @var array $activities activities retrieved from the Google+ API
     */
    public $activities; 

    /**
     * @var Google_Service_Plus_Person|null profile retrieved form Google+ API
     */
    public $person;

}

?>
