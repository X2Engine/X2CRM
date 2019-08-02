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




Yii::import('application.components.TwitterAPI.TwitterAPIExchange');

/**
 * Class for displaying contact twitter feeds
 * 
 * @package application.components.sortableWidget
 */
class TwitterFeedWidget extends SortableWidget {

    private static $_JSONPropertiesStructure;
    public $viewFile = '_twitterFeedWidget';
    public $model;
    public $sortableWidgetJSClass = 'TwitterFeedWidget';
    public $template = '<div class="submenu-title-bar widget-title-bar">{twitterLogo}{widgetLabel}{screenNameSelector}{closeButton}{minimizeButton}</div>{widgetContents}';
    private $_username;

    public static function getJSONPropertiesStructure() {
        if (!isset(self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge(
                    parent::getJSONPropertiesStructure(), array(
                'label' => 'Twitter Feed',
                'hidden' => false,
                    )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    /**
     * Render functions (Automatically renders)
     */
    public function renderTwitterLogo() {
        echo '<span id="twitter-widget-top-bar-logo"></span>';
    }

    public function renderScreenNameSelector() {
        $options = array();
        $aliases = RecordAliases::getAliases($this->model, 'twitter');
        foreach ($aliases as $alias) {
            $options[$alias->alias] = $alias->alias;
        }
        echo CHtml::dropDownList('screenName', null, $options, array(
            'class' => 'x2-minimal-select',
            'id' => 'screen-name-selector',
        ));
    }

    /**
     * @Override
     * 
     * Adds JS file necessary to run the setup script.
     */
    public function getPackages() {
        if (!isset($this->_packages)) {
            $this->_packages = array_merge(parent::getPackages(), array(
                'TwitterFeedWidgetJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/sortableWidgets/TwitterFeedWidget.js',
                    ),
                    'depends' => array('SortableWidgetJS')
                ),
                    )
            );
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
     * @Override
     * 
     * Runs widget
     */
    public function run() {
        $twitter = TwitterBehavior::createTwitterInstance();
        if (!$twitter->checkTwitterCredentials()) {
            return '';
        }
        
        if (!extension_loaded('curl')) {
            $this->addError(Yii::t('app', 'The Twitter widget requires the PHP curl extension.'));
            return parent::run();
        } else {
            $aliases = RecordAliases::getAliases($this->model, 'twitter');
            
            if (!count($aliases)) {
                return '';
            }
            if (isset($_GET['twitterScreenName'])) {
                $this->_username = $_GET['twitterScreenName'];
            } else {
                $this->_username = $aliases[0]->alias;
            }
            try {
                $this->getTweetDataProvider();
            } catch (TwitterFeedWidgetException $e) {
                $errorMessage = $e->getMessage();
                if (isset($_GET['twitterFeedAjax'])) {
                    throw new CHttpException(429, $errorMessage);
                } else {
                    $this->addError($errorMessage);
                }
            }
        }
        return parent::run();
    }

    /**
     * Gets id of last tweet in data provider
     * 
     * @return String: id of last tweet in data provider
     */
    public function getLastTweetId() {
        $tweetDP = $this->getTweetDataProvider();
        if (!$tweetDP) {
            return null;
        }
        $data = $tweetDP->getData();
        $lastTweetId = count($data) ? $data[count($data) - 1]['id_str'] : null;
        return $lastTweetId;
    }

    /**
     * Get tweet cache key
     * 
     * @return String: Tweet cache key 
     */
    public function getCacheKey() {
        $username = $this->_username;
        return 'TwitterFeedWidget' . $username;
    }

    /**
     * Renders timestamp with formatted date
     * 
     * @param Array tweet: Tweet to have time rendered
     * @return String: Formatted html
     */
    public function renderTimestamp(array $tweet) {
        $nowTs = time();
        $now = getDate($nowTs);
        $timestamp = strtotime($tweet['created_at']);
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

        return '<a href="https://www.twitter.com/' .
                urlencode($this->_username) . '/status/' .
                $tweet['id_str'] . '">' .
                CHtml::encode($formattedTimestamp) . '</a>';
    }

    /**
     * Replaces text entities with twitter entities
     * 
     * @param Array tweet: Tweet to replace text entities
     */
    public function replaceTextEntities(array &$tweet) {
        if (isset($tweet['retweeted_status'])) {
            $name = $tweet['user']['name'];
            $retweetedByText = '<div class="retweeted-by-text-container">' .
                    '<span class="retweet-icon-small"></span>' .
                    CHtml::encode(Yii::t('app', 'Retweeted by')) .
                    '&nbsp;<a href="https://twitter.com/' . urlencode($name) .
                    '">' . $name . '</a>' . '</div>';
            $tweet = $tweet['retweeted_status'];
        }

        if (!isset($tweet['entities'])) {
            return $tweet['text'];
        }

        $text = $tweet['text'];
        $entities = $tweet['entities'];

        // collapse entities array so that they can be more easily ordered
        $orderedEntities = array();
        foreach ($entities as $type => $entitiesOfType) {
            foreach ($entitiesOfType as $entity) {
                $orderedEntities[] = array_merge(array('type' => $type), $entity);
            }
        }

        // order entities by index into tweet text
        usort($orderedEntities, function ($a, $b) {
            return $b['indices'][0] - $a['indices'][0];
        });

        // replace entities in reverse order to preserve indices in original tweet text
        foreach ($orderedEntities as $entity) {
            switch ($entity['type']) {
                case 'hashtags':
                    $link = "<a href='https://twitter.com/hashtag/" .
                            urlencode($entity['text']) . "'?src=hash>
                        #" . CHtml::encode($entity['text']) . "</a>";
                    break;
                case 'symbols':
                    $link = "<a href='https://twitter.com/search?1=" .
                            urlencode($entity['text']) . "'?src=ctag>
                        $" . CHtml::encode($entity['text']) . "</a>";
                    break;
                case 'urls':
                    $link = "<a 
                        title='" . $entity['expanded_url'] . "'
                        href='" . $entity['url'] . "'>
                        " . CHtml::encode($entity['display_url']) . "</a>";
                    break;
                case 'user_mentions':
                    $link = "<a href='https://twitter.com/" . urlencode($entity['screen_name']) . "'>
                        @" . CHtml::encode($entity['screen_name']) . "</a>";
                    break;
                default:
                    continue 2;
            }
            $text = mb_substr($text, 0, $entity['indices'][0], 'UTF-8') . $link .
                    mb_substr($text, $entity['indices'][1] + 1, null, 'UTF-8');
        }

        $text .= isset($retweetedByText) ? $retweetedByText : '';
        $tweet['text'] = $text;
    }

    /**
     * Gets remaining number of requests
     * 
     * @param String resourceName: Name of the api-fetched resource
     * @param Int value: Used to update the remaining requests count
     */
    public function remainingRequests($resourceName, $value = null) {
        $name = preg_replace('/\.json$/', '', $resourceName);
        $status = $this->getRateLimitStatus();

        // no rate limit info available
        if (!$status) {
            return false;
        }

        $matches = array();
        preg_match('/^\/([^\/]+)\//', $name, $matches);
        $category = $matches[1];

        // rate limit info not found
        if (!isset($status['resources'][$category][$name])) {
            return false;
        }

        if ($value !== null) {
            $status['resources'][$category][$name][
                    'remaining'] = $value;
            $this->setRateLimitStatus($status);
        }

        $entry = $status['resources'][$category][$name];
        $remaining = (int) $entry['remaining'];

        return $remaining;
    }

    /**
     * Update rate limit caches 
     */
    private function setRateLimitStatus(array $rateLimitStatus) {
        $this->_rateLimits = $rateLimitStatus;
        Yii::app()->settings->twitterRateLimits = $rateLimitStatus;
        Yii::app()->settings->save();
    }

    /**
     * Retrieve rate limit status. The status is cached in the admin table and refreshed whenever
     * the rate limit window (15 minutes) has passed
     */
    private $_rateLimits;

    private function getRateLimitStatus() {
        if (isset($this->_rateLimits)) {
            return $this->_rateLimits;
        }

        $rateLimitWindow = 60 * 15;

        // first check the cache
        $rateLimits = Yii::app()->settings->twitterRateLimits;
        if (ctype_digit($rateLimits)) { // setting is set to window expiration date
            if ((int) $rateLimits >= time()) { // window hasn't expired
                return false;
            }
        } elseif (is_array($rateLimits)) {
            // if rate limit field is set but doesn't include needed rate limits, there's no
            // way of knowing whether an additional request would surpass the rate limit. 
            // Set the rate limit to the window size to ensure that the rate limit gets reset before
            // making another api request.
            if (!isset(
                            $rateLimits['resources']['application']['/application/rate_limit_status'])) {

                Yii::app()->settings->twitterRateLimits = time() + $rateLimitWindow;
                Yii::app()->settings->save();
                return false;
            }

            $entry = $rateLimits['resources']['application']['/application/rate_limit_status'];
            if ($entry['reset'] > time()) { // end of window hasn't been reached
                if ((int) $entry['remaining'] < 1) {
                    // rate limit on number of requests to retrieve the rate limit has been reached
                    return false;
                } else {
                    // rate limit info is valid
                    //AuxLib::debugLogR ('cache hit');
                    return $rateLimits;
                }
            }
        } else if ($rateLimits !== null) {
            // rate limit was set to an invalid value
            Yii::app()->settings->twitterRateLimits = time() + $rateLimitWindow;
            Yii::app()->settings->save();
            return false;
        }

        // refresh the rate limit status cache
        $twitter = TwitterBehavior::createTwitterInstance();
        $credentials = $twitter->getTwitterCredentials();
        $url = 'https://api.twitter.com/1.1/application/rate_limit_status.json';
        $requestMethod = 'GET';
        $twitterApi = new TwitterAPIExchange($credentials);
        $rateLimitStatus = CJSON::decode($twitterApi->buildOauth($url, $requestMethod)->performRequest());
        
        if (($statusCode = $twitterApi->getLastStatusCode()) != 200) {
            $this->throwApiException($rateLimitStatus, $statusCode);
        }
        
        Yii::app()->settings->twitterRateLimits = $rateLimitStatus;
        Yii::app()->settings->save();

        $this->_rateLimits = $rateLimitStatus;
        return $rateLimitStatus;
    }

    /**
     * Handles tweet requests, caching, and pagination. 
     *
     * Widget pagination is handled by means of the GET parameter maxTweetId; if set, the cache 
     * will be scanned for a tweet with the specified id. If that id is found, all tweets in the 
     * cache will be returned up to one page past the specified id. If the max id is not in the 
     * cache, one attempt will be made to fetch new tweets into the cache.
     *
     * @param bool $append If true, assuming tweets are cached, new tweets will be fetched 
     *  with max id set to the last id of the cached tweets. The results will be appended to 
     *  the cache.
     * @throws CException if tweets cannot be fetched due to rate limit being met
     */
    private $_tweets;

    public function requestTweets($append = false) {
        $this->getRateLimitStatus();
        $maxId = isset($_GET['maxTweetId']) ? $_GET['maxTweetId'] : -1;

        if (isset($this->_tweets) && !$append) {
            return $this->_tweets;
        }

        $username = $this->_username;
        $cache = Yii::app()->cache2;
        $cacheKey = $this->getCacheKey();
        $pageSize = 5;
        $tweets = $cache->get($cacheKey);

        if ($append && !$tweets) {
            // another page of tweets has been requested but the newer tweets have been 
            // invalidated. To avoid having to determine how many pages down the user is, 
            // we simply refresh the feed.

            $append = false;
            $maxId = -1;
        }

        if (!$tweets || $append) { // fetch tweets and add to cache
            $tweetCount = 100;
            $twitter = TwitterBehavior::createTwitterInstance();
            $credentials = $twitter->getTwitterCredentials();
            $resourceName = '/statuses/user_timeline.json';
            $remainingRequests = $this->remainingRequests($resourceName);

            // rate limit met
            if ($remainingRequests < 1) {
                throw new TwitterFeedWidgetException(Yii::t(
                        'app', 'Twitter feed could not be retrieved. Please try again later.'));
            }

            $url = 'https://api.twitter.com/1.1' . $resourceName;

            $getfield = '?screen_name=' . $username . '&count=' . $tweetCount;
            if ($append) {
                $maxId = $tweets[count($tweets) - 1]['id_str'];
                $getfield .= '&max_id=' . $maxId;
            }

            $requestMethod = 'GET';
            $twitterApi = new TwitterAPIExchange($credentials);
            $oldTweets = $tweets;

            $tweets = CJSON::decode($twitterApi->setGetfield($getfield)
                                    ->buildOauth($url, $requestMethod)
                                    ->performRequest());
            if (($statusCode = $twitterApi->getLastStatusCode()) != 200) {
                $this->throwApiException($tweets, $statusCode);
            }
            $this->remainingRequests($resourceName, $remainingRequests - 1);
            if ($append) {
                $tweets = array_merge($oldTweets, $tweets);
            }
            $cache->set($cacheKey, $tweets, 60 * 5);
        }

        if ($maxId === -1) { // initial page load, just return the first page
            $this->_tweets = array_slice($tweets, 0, $pageSize);
            return $this->_tweets;
        }

        // max id specified, return all tweets up one page beyond max id
        $found = false;
        for ($i = 0; $i < count($tweets); $i++) {
            $tweet = $tweets[$i];
            if ($tweet['id_str'] !== $maxId) {
                continue;
            }
            $found = true;
            break;
        }

        if ($found && $i + $pageSize < count($tweets)) {
            $this->_tweets = array_slice($tweets, 0, $i + $pageSize + 1);
        } else if (!$append) { // only request more tweets once
            return $this->requestTweets(true);
        } else { // giving up on searching for specified tweet, just display the first page
            $this->_tweets = array_slice($tweets, 0, $pageSize);
        }
        return $this->_tweets;
    }

    private $_tweetDataProvider;

    public function getTweetDataProvider() {
        if (!isset($this->_tweetDataProvider)) {
            $tweets = $this->requestTweets();
            $this->_tweetDataProvider = new CArrayDataProvider($tweets, array(
                'pagination' => array(
                    'pageSize' => PHP_INT_MAX,
                ),
            ));
        }
        return $this->_tweetDataProvider;
    }

    public function getTimeline() {
        if (isset($_GET['twitterFeedAjax'])) {
            ob_clean();
            ob_start();
        }
        $dataProvider = $this->getTweetDataProvider();
        if (!$dataProvider) {
            return;
        }

        Yii::app()->controller->widget('zii.widgets.CListView', array(
            'id' => 'twitter-feed',
            'ajaxVar' => 'twitterFeedAjax',
            'htmlOptions' => array(
                'class' => 'list-view twitter-feed-list-view',
            ),
            'viewData' => array(
                'twitterFeedWidget' => $this,
            ),
            'dataProvider' => $dataProvider,
            'itemView' => 'application.components.sortableWidget.views._tweet',
            'template' => '{items}',
        ));
        if (isset($_GET['twitterFeedAjax'])) {
            echo '<script>x2.TwitterFeedWidget.lastTweetId = "' .
            $this->getLastTweetId() . '";</script>';
            echo ob_get_clean();
            ob_flush();
            Yii::app()->end();
        }
    }

    protected function getJSSortableWidgetParams() {
        if (!isset($this->_JSSortableWidgetParams)) {
            if (!$this->hasError()) {
                $lastTweetId = $this->getLastTweetId();
            } else {
                $lastTweetId = null;
            }
            $this->_JSSortableWidgetParams = array_merge(parent::getJSSortableWidgetParams(), array(
                'lastTweetId' => $lastTweetId,
                    )
            );
        }
        return $this->_JSSortableWidgetParams;
    }

    /**
     * @param array $response decoded Twitter API response
     * @param int $code http status code
     */
    private function throwApiException($response, $code) {
        $error = isset($response['error']) ? $response['error'] : '';
        switch ($code) {
            case 404:
                $message = Yii::t('app', 'Twitter username not found.');
                break;
            case 401:
                $message = Yii::t(
                                'app', 'Twitter Integration credentials are missing or incorrect. Please ' .
                                'contact an administrator.');
                break;
            default:
                $message = Yii::t('app', 'Twitter API {code} error{message}', array(
                            '{code}' => $code,
                            '{message}' => $error ? ': ' . $error : '',
                ));
        }
        throw new TwitterFeedWidgetException($message);
    }

}

class TwitterFeedWidgetException extends CException {
    
}
