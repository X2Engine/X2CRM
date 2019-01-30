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
 * Class for displaying contact dropbox feeds
 * 
 * @package application.components.sortableWidget
 */
class DropboxWidget extends SortableWidget {

    private static $_JSONPropertiesStructure;
    public $viewFile = '_dropboxFeedWidget';
    public $model;
    public $sortableWidgetJSClass = 'DropboxWidget';
    public $template = '<div class="submenu-title-bar widget-title-bar">{dropboxLogo}{widgetLabel}{screenNameSelector}{closeButton}{minimizeButton}</div>{widgetContents}';
    private $_username;

    /**
     * overrides parent method
     */
    public static function getJSONPropertiesStructure() {
        if (!isset(self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge(
                    parent::getJSONPropertiesStructure(), array(
                'docId' => '', // id of the doc record to be displayed
                'label' => Yii::t('app', 'Dropbox'),
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
    public function renderDropboxLogo() {
        echo '<span id="dropbox-widget-top-bar-logo"></span>';
    }

    /**
     * @Override
     * 
     * Adds JS file necessary to run the setup script.
     */
    public function getPackages() {
        if (!isset($this->_packages)) {
            $this->_packages = array_merge(parent::getPackages(), array(
                'DropboxWidgetJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/sortableWidgets/DropboxWidget.js',
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
     * Get dropboxPost cache key
     * 
     * @return String: DropboxPost cache key 
     */
    public function getCacheKey() {
        $username = $this->_username;
        return 'DropboxWidget' . $username;
    }

    /**
     * Renders timestamp with formatted date
     * 
     * @param Array dropboxPost: DropboxPost to have time rendered
     * @return String: Formatted html
     */
    public function renderTimestamp(array $dropboxPost) {
        $nowTs = time();
        $now = getDate($nowTs);
        $timestamp = strtotime($dropboxPost['created_at']);
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

        return '<a href="https://www.dropbox.com/' .
                urlencode($this->_username) . '/status/' .
                $dropboxPost['id_str'] . '">' .
                CHtml::encode($formattedTimestamp) . '</a>';
    }

    /**
     * Handles dropboxPost requests, caching, and pagination. 
     *
     * Widget pagination is handled by means of the GET parameter maxDropboxPostId; if set, the cache 
     * will be scanned for a dropboxPost with the specified id. If that id is found, all dropboxPosts in the 
     * cache will be returned up to one page past the specified id. If the max id is not in the 
     * cache, one attempt will be made to fetch new dropboxPosts into the cache.
     *
     * @param bool $append If true, assuming dropboxPosts are cached, new dropboxPosts will be fetched 
     *  with max id set to the last id of the cached dropboxPosts. The results will be appended to 
     *  the cache.
     * @throws CException if dropboxPosts cannot be fetched due to rate limit being met
     */
    private $_dropboxPosts;

    public function requestDropboxPosts($append = false) {
        //Dropbox might not have a rate limit status like Twitter
        //$this->getRateLimitStatus();
        $maxId = isset($_GET['maxDropboxPostId']) ? $_GET['maxDropboxPostId'] : -1;

        if (isset($this->_dropboxPosts) && !$append) {
            return $this->_dropboxPosts;
        }

        $username = $this->_username;
        $cache = Yii::app()->cache2;
        $cacheKey = $this->getCacheKey();
        $pageSize = 5;
        $dropboxPosts = $cache->get($cacheKey);

        if ($append && !$dropboxPosts) {
            // another page of dropboxPosts has been requested but the newer dropboxPosts have been 
            // invalidated. To avoid having to determine how many pages down the user is, 
            // we simply refresh the feed.

            $append = false;
            $maxId = -1;
        }
        $dropbox = DropboxBehavior::createDropboxInstance();
        if (is_string($dropbox) && (strstr($dropbox, 'Error') || strstr($dropbox, 'fail')))
            return array($dropbox);
        $url = 'https://api.dropboxapi.com/2/files/list_folder';
        $ch = curl_init();
        $headers[0] = 'Authorization: Bearer ' . $dropbox->getAccessToken();
        $headers[1] = 'Content-Type: application/json';
        $data = array("path" => "/X2CRM");
        $data_string = json_encode($data);
        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        // Set request method to POST
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        //execute post
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code === 200) {
            //close connection
            $response = $result;
        } else {
            //throw new CHttpException(500, Yii::t('app', 'Failed to fetch dropbox data'));
            return array('Info' => 'Care to merge your X2CRM Dropbox folder into your Docs module?');
        }
        $dropboxPosts = CJSON::decode($response);
        return $dropboxPosts;
    }

    private $_dropboxPostDataProvider;

    public function getDropboxPostDataProvider() {
        if (!isset($this->_dropboxPostDataProvider)) {
            $dropboxPosts = $this->requestDropboxPosts();
            /* $this->_dropboxPostDataProvider = new CArrayDataProvider($dropboxPosts, array(
              'pagination' => array(
              'pageSize' => PHP_INT_MAX,
              ),
              )); */
        }
        //return $this->_dropboxPostDataProvider;
        return $dropboxPosts;
    }

    private function getDropboxData($dataProvider = null, $model = null, $dropbox = null) {
        foreach ($dataProvider as &$value) {
            //close connection
            $url = 'https://content.dropboxapi.com/2/files/download';
            $ch = curl_init();
            $headers[0] = 'Authorization: Bearer ' . $_SESSION['dropbox_access_token'];
            $headers[1] = 'Dropbox-API-Arg: {"path":"/X2CRM/' . $value['name'] . '"}';
            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            // Set request method to POST
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            //execute post
            $result = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($http_code === 200) {
                //close connection
                $response = $result;
            } else {
                //throw new CHttpException(500, Yii::t('app', 'Failed to fetch dropbox data'));
                return array('Info' => 'Care to merge your X2CRM Dropbox folder into your Docs module?');
            }
            $profile = Yii::app()->params->profile;

            $doc = new Docs;
            $doc->name = $value['name'];
            $doc->visibility = 1;
            $doc->text = $response;
            if (!$doc->save()) {
                throw new CException(implode(';', $doc->getAllErrorMessages()));
            }
        }
    }

    public function getDropboxProfile() {
        if (isset($_GET['dropboxFeedAjax'])) {
            ob_clean();
            ob_start();
        }
        $dataProvider = $this->getDropboxPostDataProvider();
        $dropbox = DropboxBehavior::createDropboxInstance();
        if (is_string($dropbox) && strstr($dropbox, 'Error'))
            return array($dropbox);
        if (!Yii::app()->settings->hubCredentialsId) {
            echo "Hub Credentials ID not found. Please check if it is set on the 'X2 Hub Services' page";
            return;
        }
        $hubCreds = Credentials::model()->findByPk(Yii::app()->settings->hubCredentialsId);
        if ($hubCreds->auth->enableDropbox && $dropbox !== "fail") {
            echo CHtml::link('Merge In Dropbox Folder to Docs', $dropbox->getLoginUrl(), array('id' => 'refresh-dropbox-profile-button', 'class' => 'x2-button'));
        }
        if (!$dataProvider || array_key_exists("Info", $dataProvider)) {
            return;
        }
        $model = Profile::model()->findByPk(Yii::app()->user->getId());
        if (array_key_exists("entries", $dataProvider)) {
            $this->getDropboxData($dataProvider['entries'], $model, $dropbox);
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
     * @param array $response decoded Dropbox API response
     * @param int $code http status code
     */
    private function throwApiException($response, $code) {
        $error = isset($response['error']) ? $response['error'] : '';
        switch ($code) {
            case 404:
                $message = Yii::t('app', 'Dropbox username not found.');
                break;
            case 401:
                $message = Yii::t(
                                'app', 'Dropbox Integration credentials are missing or incorrect. Please ' .
                                'contact an administrator.');
                break;
            default:
                $message = Yii::t('app', 'Dropbox API {code} error{message}', array(
                            '{code}' => $code,
                            '{message}' => $error ? ': ' . $error : '',
                ));
        }
        throw new DropboxWidgetException($message);
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

class DropboxWidgetException extends CException {
    
}
