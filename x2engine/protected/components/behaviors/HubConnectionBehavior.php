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






//Yii::import('application.modules.actions.models.*');

/**
 * Arbitrates connections through X2Hub
 * 
 * @package application.components.behaviors
 * @author Raymond Colebaugh <raymond@x2engine.com>
 */
class HubConnectionBehavior extends CModelBehavior {
    
    /**
     * Creates an instance of FacebookBehavior
     * 
     * @param array params: Parameters for behavior fields
     * @return HubConnectionBehavior: Instance of FacebookBehavior
     */
    public static function createHubInstance() {
        $hub = Yii::app()->controller->attachBehavior('HubConnectionBehavior', new HubConnectionBehavior);
        return $hub;
    }
    
    public static function checkHubEnabled() {
        $hubId = Yii::app()->settings->hubCredentialsId;
        
        if (isset($hubId)) {
            $creds = Credentials::model()->findByPk($hubId);
            return isset($creds) && isset($creds->auth) && isset($creds->auth->hubEnabled) && $creds->auth->hubEnabled;
        }
        return false;
    }

    public function getHubServerUrl() {
        return 'https://hub.x2crm.com/index.php';
    }

    public function pingHub() {
        $response = $this->hubRequest('site/ping');
        if (isset($response['error']) && $response['error'] === false) {
            return $response['message'] === 'enabled';
        }
    }

    /**
     * Request a two factor auth code from X2Hub for a user
     * @param Profile $user User profile to send verify code to
     * @return string Verification code
     */
    public function requestTwoFA(Profile $user) {
        $response = $this->hubRequest('twoFA/request', array(
            'userId' => $user->id,
            'phone' => $user->cellPhone,
        ));
        return isset($response['error']) && $response['error'] === false ? $response['message'] : '';
    }

    /**
     * Request a Google API key from X2Hub for a user
     * 
     * @param int $userId User id requesting service
     * @param string $type X2Hub Activity type
     * @return string API Key
     */
    public function getGoogleApiKey($userId, $type) {
        $response = $this->hubRequest('google/getApiKey', array(
            'userId' => $userId,
            'type' => $type,
        ));

        return isset($response['error']) && $response['error'] === false ? $response['message'] : '';
    }

    /**
     * Request a Google OAuth access token from X2Hub for a user
     * 
     * @param int $userId User id requesting service
     * @param string $type X2Hub Activity type
     * @return string Access token
     */
    public function getGoogleAccessToken($userId, $type, $redirectUri, $code = null, $refreshToken = null) {
        $response = $this->hubRequest('google/getAccessToken', array(
            'userId' => $userId,
            'type' => $type,
            'code' => $code,
            'refreshToken' => $refreshToken,
            'redirectUri' => $redirectUri,
        ));

        return isset($response['error']) && $response['error'] === false ? $response['message'] : '';
    }

    public function getGoogleAuthorizationUrl($userId, $type, $redirectUri, $state) {
        $response = $this->hubRequest('google/getAuthorizationUrl', array(
            'userId' => $userId,
            'state' => $state,
            'redirectUri' => $redirectUri,
        ));
        
        return isset($response['error']) && $response['error'] === false ? $response['message'] : '';
    }

    /**
     * Request Facebook credentials from X2Hub for user
     * 
     * @return string API Key
     */
    public function getFacebookCredentials() {
        $response = $this->hubRequest('facebook/getCredentials');
        
        return isset($response['error']) && $response['error'] === false ? $response['message'] : '';
    }

    /**
     * Request a Twitter access token from X2Hub for a user
     * 
     * @param int $userId User id requesting service
     * @param string $type X2Hub Activity type
     * @return string API Key
     */
    public function getTwitterCredentials() {
        $response = $this->hubRequest('twitter/getCredentials');

        return isset($response['error']) && $response['error'] === false ? $response['message'] : '';
    }
    
    /**
     * Request a LinkedIn access token from X2Hub for a user
     * 
     * @param int $userId User id requesting service
     * @param string $type X2Hub Activity type
     * @return string API Key
     */
    public function getLinkedInCredentials() {
        $response = $this->hubRequest('linkedin/getCredentials');
        return isset($response['error']) && $response['error'] === false ? $response['message'] : '';
    }
    
    /**
     * Request a Dropbox access token from X2Hub for a user
     */
    public function getDropboxCredentials() {
        $response = $this->hubRequest('dropbox/getCredentials');
        
        return isset($response['error']) && $response['error'] === false ? $response['message'] : '';
    }

    /**
     * Request docusign account id from x2hub for a user
     * 
     * @param int $userId User id requesting service
     * @param string $type X2Hub Activity type
     * @return string API Key
     */
    public function getDocusignAccountId() {
        $response = $this->hubRequest('docusign/getAccountId');
        
        return isset($response['error']) && $response['error'] === false ? $response['message'] : '';
    }

    /**
     * Issue a request to X2Hub
     * 
     * @param string $action X2Hub Action to perform
     * @param array $params Request parameters
     * @return array Response details
     */
    protected function hubRequest($action, array $params = array()) {
        $creds = Credentials::model()->findByPk(Yii::app()->settings->hubCredentialsId);
        if ($creds && $creds->auth) {
            $params = array_merge($params, array(
                'unique_id' => $creds->auth->unique_id,
            ));
            $query = http_build_query($params);
            $url = $this->hubServerUrl . '/' . $action . '?' . $query;
            $response = RequestUtil::request(array(
                        'timeout' => 15,
                        'url' => $url,
                        'header' => array(
                            'Content-Type' => 'application/json',
                        ),
            ));
            return CJSON::decode($response, true);
        }
    }

}
