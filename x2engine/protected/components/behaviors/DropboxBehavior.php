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
 * A behavior for interacting with the dropbox API
 * @package application.components.behaviors
 */
class DropboxBehavior extends CBehavior {

    private $client_id;
    private $client_secret;
    private $redirect_uri;

    /**
     * Creates an instance of DropboxBehavior
     * 
     * @param array params: Parameters for behavior fields
     * @return DropboxBehavior: Instance of DropboxBehavior
     */
    public static function createDropboxInstance() {
        $dropbox = Yii::app()->controller->attachBehavior('DropboxBehavior', new DropboxBehavior);
        $credentials = Credentials::model()->findBySql("SELECT * FROM x2_credentials WHERE modelClass='DropboxAccount' LIMIT 1", array('userid' => Yii::app()->user->id));
        if (is_null($credentials) || empty($credentials)) {
            return "fail";
        }
        $dropbox->initialize($dropbox->getDropboxCredentials());
        return $dropbox;
    }

    /**
     * Initializes behavior parameters
     * 
     * @param Array params: Parameters for behavior fields 
     */
    public function initialize(array $params = array()) {
        foreach ($params as $key => $value) {
            if (in_array($key, array('appId', 'appSecret'))) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Check if Dropbox is enabled
     * 
     * @return Boolean: If Dropbox is enabled
     */
    public function checkDropboxCredentials() {
        return $this->checkDropboxHub() || $this->checkDropboxIntegration();
    }

    /**
     * Gets Dropbox credentials
     * 
     * @return Array: Dropbox credentials
     */
    public function getDropboxCredentials() {

        // If hub enabled
        // TODO: Dropbox credentials (which is needed for certain type of
        // requests with the Dropbox API) in hub don't make sense yet because
        // each user needs to manually input their client id and secret 
        // so might as well store it in the app. Maybe when we use a general set of
        // credentials for all x2vps users then we can move the credentials to
        // Hub which makes more sense. 
        /* if ($this->checkDropboxHub()) {
          $credentials = $this->getDropboxHubCredentials();
          if (!empty($credentials)) {
          $this->client_id = $credentials['client_id'];
          $this->client_secret = $credentials['client_secret'];
          $this->redirect_uri = $credentials['redirect_uri'];
          }
          } */
        $credentials = Credentials::model()->findBySql("SELECT * FROM x2_credentials WHERE modelClass='DropboxAccount' LIMIT 1", array('userid' => Yii::app()->user->id));
        $this->client_id = $credentials->auth->cid;
        $this->client_secret = $credentials->auth->sid;
        $this->redirect_uri = $credentials->auth->redirectUrl;
        return array(
            'client_id' => $credentials->auth->cid,
            'client_secret' => $credentials->auth->sid,
            'redirect_uri' => $credentials->auth->redirectUrl,
        );
    }

    /**
     * Check if Dropbox is enabled from hub
     * 
     * @return Boolean: If Dropbox is enabled from integration
     */
    private function checkDropboxHub() {
        $hub = HubConnectionBehavior::createHubInstance();
        $credentials = json_decode($hub->getDropboxCredentials());

        $credCheck = $credentials &&
                $credentials->client_id !== '' &&
                $credentials->client_secret !== '' &&
                $credentials->redirect_uri !== '';

        return HubConnectionBehavior::checkHubEnabled() && $credentials && $credCheck;
    }

    /**
     * Gets Dropbox hub credentials
     * 
     * @return array Dropbox credentials
     */
    private function getDropboxHubCredentials() {
        $hub = HubConnectionBehavior::createHubInstance();
        $credentials = json_decode($hub->getDropboxCredentials());

        return array(
            'client_id' => $credentials->client_id,
            'client_secret' => $credentials->client_secret,
            'redirect_uri' => $credentials->redirect_uri,
        );
    }

    public function getLoginUrl() {

        return 'https://www.dropbox.com/1/oauth2/authorize?response_type=code&client_id=' .
                $this->client_id . '&state=' . Yii::app()->request->csrfToken . '&redirect_uri=' . $this->redirect_uri;
    }

    public function getAccessToken() {
        $response = '';
        try {
            $code = Yii::app()->session['dropbox_code'];
            $url = 'https://api.dropbox.com/1/oauth2/token?' . 'code=' . $code 
                    . '&grant_type=authorization_code'. '&redirect_uri=' 
                    . $this->redirect_uri;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $this->client_id.":".$this->client_secret);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            // Set request method to POST
            curl_setopt($ch, CURLOPT_POST, 1);
            $result = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($http_code) {
                //close connection
                $output = $result;
            } else {
                //throw new CHttpException(500, Yii::t('app', 'Failed to fetch linkedin data'));
                return array('Info' => 'Care to merge your X2CRM profile data with your LinkedIn profile data?');
            }
            $response = CJSON::decode($output);
        } catch (CHttpException $e) {
            // most likely that user very recently revoked authorization.
            // In any event, we don't have an access token, so throw an exception.
            throw new CHttpException(404, 'Could not get access token: The user may have revoked the authorization response from Dropbox.com was empty.');
        }

        if (empty($response) || !empty($response['error_description'])) {
            return 'Error: '.$response['error'].'=>'.$response['error_description'];
        }

        $_SESSION['dropbox_access_token'] = $response['access_token'];

        if (empty($response['access_token'])) {
            throw new CHttpException(404, 'Could not get access token: The response from Dropbox.com did not contain a token.');
        }

        return $response['access_token'];
    }

}
