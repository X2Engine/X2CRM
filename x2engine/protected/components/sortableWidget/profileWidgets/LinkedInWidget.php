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




/**
 * Class for displaying contact linkedIn feeds
 * 
 * @package application.components.sortableWidget
 */
class LinkedInWidget extends SortableWidget {

    private static $_JSONPropertiesStructure;
    public $viewFile = '_linkedInFeedWidget';
    public $model;
    public $sortableWidgetJSClass = 'LinkedInWidget';
    public $template = '<div class="submenu-title-bar widget-title-bar">{linkedInLogo}{widgetLabel}{screenNameSelector}{closeButton}{minimizeButton}</div>{widgetContents}';
    private $_username;

    /**
     * overrides parent method
     */
    public static function getJSONPropertiesStructure() {
        if (!isset(self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge(
                    parent::getJSONPropertiesStructure(), array(
                'docId' => '', // id of the doc record to be displayed
                'label' => Yii::t('app', 'LinkedIn'),
                'height' => '200',
                'hidden' => true
                    )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    /**
     * Render functions (Automatically renders)
     */
    public function renderLinkedInLogo() {
        echo '<span id="linkedIn-widget-top-bar-logo"></span>';
    }

    /**
     * @Override
     * 
     * Adds JS file necessary to run the setup script.
     */
    public function getPackages() {
        if (!isset($this->_packages)) {
            $this->_packages = array_merge(parent::getPackages(), array(
                'LinkedInWidgetJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/sortableWidgets/LinkedInWidget.js',
                    ),
                    'depends' => array('SortableWidgetJS')
                ),
            ));
        }
        return $this->_packages;
    }

    /**
     * @Override
     * 
     * Gets view file params of widget
     */
    public function getViewFileParams() {
        if (!isset($this->_viewFileParams)) {
            $this->_viewFileParams = array_merge(
                    parent::getViewFileParams(), array(
                'username' => $this->_username,
                    )
            );
        }
        return $this->_viewFileParams;
    }

    /**
     * Get linkedInPost cache key
     * 
     * @return String: LinkedInPost cache key 
     */
    public function getCacheKey() {
        $username = $this->_username;
        return 'LinkedInWidget' . $username;
    }

    /**
     * Renders timestamp with formatted date
     * 
     * @param Array linkedInPost: LinkedInPost to have time rendered
     * @return String: Formatted html
     */
    public function renderTimestamp(array $linkedInPost) {
        $nowTs = time();
        $now = getDate($nowTs);
        $timestamp = strtotime($linkedInPost['created_at']);
        $date = getDate($timestamp);
        $formattedTimestamp = '';

        // long format
        if ($date['year'] !== $now['year']) {
            $formattedTimestamp = Yii::app()->dateFormatter->format(
                    'd MMM yy', $timestamp);
        }

        // month day format
        else if ($date['yday'] !== $now['yday']) {
            $formattedTimestamp = Yii::app()->dateFormatter->format(
                    'd MMM', $timestamp);
        }

        // hour format
        else if ($now['hours'] - $date['hours'] > 1) {
            $diffTs = $nowTs - $timestamp;
            $formattedTimestamp = floor($diffTs / 60 / 60) . 'h';
        }

        // minute format
        else if ($now['minutes'] - $date['minutes'] > 1) {
            $diffTs = $nowTs - $timestamp;
            $formattedTimestamp = floor($diffTs / 60) . 'm';
        }

        // second format
        else {
            $diffTs = $nowTs - $timestamp;
            $formattedTimestamp = $diffTs . 's';
        }

        return '<a href="https://www.linkedIn.com/' .
                urlencode($this->_username) . '/status/' .
                $linkedInPost['id_str'] . '">' .
                CHtml::encode($formattedTimestamp) . '</a>';
    }

    /**
     * Handles linkedInPost requests, caching, and pagination. 
     *
     * Widget pagination is handled by means of the GET parameter maxLinkedInPostId; if set, the cache 
     * will be scanned for a linkedInPost with the specified id. If that id is found, all linkedInPosts in the 
     * cache will be returned up to one page past the specified id. If the max id is not in the 
     * cache, one attempt will be made to fetch new linkedInPosts into the cache.
     *
     * @param bool $append If true, assuming linkedInPosts are cached, new linkedInPosts will be fetched 
     *  with max id set to the last id of the cached linkedInPosts. The results will be appended to 
     *  the cache.
     * @throws CException if linkedInPosts cannot be fetched due to rate limit being met
     */
    private $_linkedInPosts;

    public function requestLinkedInPosts($append = false) {
        //LinkedIn might not have a rate limit status like Twitter
        //$this->getRateLimitStatus();
        $maxId = isset($_GET['maxLinkedInPostId']) ? $_GET['maxLinkedInPostId'] : -1;

        if (isset($this->_linkedInPosts) && !$append) {
            return $this->_linkedInPosts;
        }

        $username = $this->_username;
        $cache = Yii::app()->cache2;
        $cacheKey = $this->getCacheKey();
        $pageSize = 5;
        $linkedInPosts = $cache->get($cacheKey);

        if ($append && !$linkedInPosts) {
            // another page of linkedInPosts has been requested but the newer linkedInPosts have been 
            // invalidated. To avoid having to determine how many pages down the user is, 
            // we simply refresh the feed.

            $append = false;
            $maxId = -1;
        }
        $linkedIn = LinkedInBehavior::createLinkedInInstance();
        if ($linkedIn === "fail")
            return array('Could not retrieve linkedIn credentials, make sure you make an account under "Manage Apps"');
        $url = 'https://api.linkedin.com/v1/people/~:(id,num-connections,'
                . 'summary,first-name,last-name,maiden-name,'
                . 'formatted-name,phonetic-first-name,phonetic-last-name,'
                . 'formatted-phonetic-name,headline,industry,current-share,'
                . 'num-connections-capped,specialties,public-profile-url,'
                . 'email-address,location,positions,picture-urls::(original),'
                . 'site-standard-profile-request)?format=json';
        $ch = curl_init();
        $headers[0] = 'Authorization: Bearer ' . $linkedIn->getAccessToken();
        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        //execute post
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code === 200) {
            //close connection
            $response = $result;
        } else {
            //throw new CHttpException(500, Yii::t('app', 'Failed to fetch linkedin data'));
            return array('Info' => 'Care to merge your X2CRM profile data with your LinkedIn profile data?');
        }
        $linkedInPosts = CJSON::decode($response);
        return $linkedInPosts;
    }

    private $_linkedInPostDataProvider;

    public function getLinkedInPostDataProvider() {
        if (!isset($this->_linkedInPostDataProvider)) {
            $linkedInPosts = $this->requestLinkedInPosts();
            /* $this->_linkedInPostDataProvider = new CArrayDataProvider($linkedInPosts, array(
              'pagination' => array(
              'pageSize' => PHP_INT_MAX,
              ),
              )); */
        }
        //return $this->_linkedInPostDataProvider;
        return $linkedInPosts;
    }

    private function getDataRecursively($key = null, $value = null, $model = null, $keyArray=null) {
        array_push($keyArray,$key);
        if (!is_array($value)) {
            if (strstr($value, 'media.licdn.com')) {
                $contents = file_get_contents($value);
                foreach ($http_response_header as $index => $header)
                    if (strstr($header, 'Content-Type')) {
                        $contentType = explode(":", $header);
                        break;
                    }

                //close connection
                $filename = md5(uniqid(rand(), true));
                $profile = Yii::app()->params->profile;
                $userFolderPath = implode(DIRECTORY_SEPARATOR, array(
                    Yii::app()->basePath,
                    '..',
                    'uploads',
                    'protected',
                    'media',
                    $profile->username
                ));
                // if user folder doesn't exit, try to create it
                if (!(file_exists($userFolderPath) && is_dir($userFolderPath))) {
                    if (!@mkdir($userFolderPath, 0777, true)) { // make dir with edit permission
                        throw new CHttpException(500, "Couldn't create user folder $userFolderPath");
                    }
                }
                if (strstr($contentType[1], 'jpeg'))
                    $filename.='.jpeg';
                $media = new Media;
                $media->setAttributes(array(
                    'fileName' => $filename,
                    'mimetype' => $contentType[1],
                        ), false);
                $media->createDate = time();
                $media->lastUpdated = time();
                $media->uploadedBy = $profile->username;
                $media->associationType = 'User';
                $media->associationId = $profile->id;
                $media->resolveNameConflicts();
                $associatedMedia = Yii::app()->file->set($userFolderPath . DIRECTORY_SEPARATOR . $media->fileName);
                $associatedMedia->create();
                $associatedMedia->setContents($contents);
                if (!$media->save() && !$associatedMedia->exists) {
                    throw new CException(implode(';', $media->getAllErrorMessages()));
                }
                $model->avatar = 'uploads/protected/media/' . $profile->username . '/' . $filename;
            } else if (strstr($value, '@')){
                $model->emailAddress = $value;
            } else if (in_array('headline',$keyArray)) {
                $model->tagLine = $value;
            } else if (in_array('name',$keyArray)) {
                $model->address = $value;
            } else if (in_array('summary',$keyArray)) {
                $model->notes .= '|'.$value;
            } else if (in_array('industry',$keyArray)) {
                $model->notes .= '|'.$value;
            } else if (in_array('position',$keyArray)) {
                $model->notes .= '|'.$value;
            }
            /*if (preg_match('/^[http][A-Za-z0-9]+/', $value) && strstr($value, 'media.licdn.com'))
                echo '<img src="' . $value . '" height="142" width="142">';
            else if (preg_match('/^[http][A-Za-z0-9]+/', $value) && strstr($value, 'www.linkedin.com'))
                echo $key . ': <a href="' . $value . '" >LinkedIn Profile</a></br >';
            else
                echo '<div>' . $key . ' : ' . $value . '</div>';*/
            return;
        }
        foreach ($value as $key => $data) {
            $keyArray = array();
            $this->getDataRecursively($key, $data, $model, $keyArray);
        }
    }

    public function getLinkedInProfile() {
        if (isset($_GET['linkedInFeedAjax'])) {
            ob_clean();
            ob_start();
        }
        $dataProvider = $this->getLinkedInPostDataProvider();
        if (!$dataProvider) {
            return;
        }
        $linkedIn = LinkedInBehavior::createLinkedInInstance();
        if ($linkedIn === "fail")
            return array('Could not retrieve linkedIn credentials, make sure you make an account under "Manage Apps"');
        if (!Yii::app()->settings->hubCredentialsId) {
            echo "Hub Credentials ID not found. Please check if it is set on the 'X2 Hub Services' page";
            return;
        }
        $hubCreds = Credentials::model()->findByPk(Yii::app()->settings->hubCredentialsId);
        if ($hubCreds->auth->enableLinkedIn) {
            echo CHtml::link('Merge LinkedIn Profile Info to X2CRM Profile Info', $linkedIn->getLoginUrl(), array('id' => 'refresh-linkedin-profile-button', 'class' => 'x2-button'));
        }
        $model = Profile::model()->findByPk(Yii::app()->user->getId());
        $this->getDataRecursively(null, $dataProvider, $model, array());
        if (!$model->save()) {
            throw new CHttpException(500, Yii::t('app', 'Failed to merge linkedin data with your X2CRM profile'));
        }
    }

    protected function getJSSortableWidgetParams() {
        if (!isset($this->_JSSortableWidgetParams)) {
            $this->_JSSortableWidgetParams = array_merge(parent::getJSSortableWidgetParams(), array(
                'enableResizing' => true,
                    )
            );
        }
        return $this->_JSSortableWidgetParams;
    }

    /**
     * @param array $response decoded LinkedIn API response
     * @param int $code http status code
     */
    private function throwApiException($response, $code) {
        $error = isset($response['error']) ? $response['error'] : '';
        switch ($code) {
            case 404:
                $message = Yii::t('app', 'LinkedIn username not found.');
                break;
            case 401:
                $message = Yii::t(
                                'app', 'LinkedIn Integration credentials are missing or incorrect. Please ' .
                                'contact an administrator.');
                break;
            default:
                $message = Yii::t('app', 'LinkedIn API {code} error{message}', array(
                            '{code}' => $code,
                            '{message}' => $error ? ': ' . $error : '',
                ));
        }
        throw new LinkedInWidgetException($message);
    }

    /**
     * Magic getter. Returns this widget's css
     * @return array key is the proposed name of the css string which should be passed as the first
     *  argument to yii's registerCss. The value is the css string.
     */
    protected function getCss() {
        if (!isset($this->_css)) {
            $this->_css = array_merge(
                    parent::getCss(), array(
                'docViewerProfileWidgetCss' => "
                        #" . get_called_class() . "-widget-content-container {
                            padding-bottom: 1px;
                        }

                        #select-a-document-dialog p {
                            display: inline;
                            margin-right: 5px;
                        }

                        .default-text-container {
                            text-align: center;
                            position: absolute;
                            top: 0;
                            bottom: 0;
                            left: 0;
                            right: 0;
                        }

                        .default-text-container a {
                            height: 17%;
                            text-decoration: none;
                            font-size: 16px;
                            margin: auto;
                            position: absolute;
                            left: 0;
                            top: 0;
                            right: 0;
                            bottom: 0;
                            color: #222222 !important;
                        }
                    "
                    )
            );
        }
        return $this->_css;
    }

}

class LinkedInWidgetException extends CException {
    
}
