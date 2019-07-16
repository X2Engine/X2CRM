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
 * A behavior for interacting with the google api
 * @package application.components.behaviors
 */
class GoogleBehavior extends CBehavior {
    //$types = array('maps', 'staticmap', 'directions', 'geocoding');

    /**
     * Creates an instance of GoogleBehavior
     * 
     * @param array params: Parameters for behavior fields
     * @return GoogleBehavior: Instance of GoogleBehavior
     */
    public static function createGoogleInstance() {
        $google = Yii::app()->controller->attachBehavior('GoogleBehavior', new GoogleBehavior);
        $google->initialize($google->retrieveGoogleCredentials());
        return $google;
    }

    /**
     * Initializes behavior parameters
     * 
     * @param Array params: Parameters for behavior fields 
     */
    public function initialize(array $params = array()) {
        foreach ($params as $key => $value) {
            if (in_array($key, array('apiKey', 'clientId', 'clientSecret'))) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Check if google api key exists
     * 
     * @return Boolean: If Twitter is enabled from integration
     */
    public function checkGoogleApiKey() {
        return $this->checkGoogleHubApiKey() && $this->checkGoogleIntegrationApiKey();
    }

    /**
     * Check if google access token exists
     * 
     * @return Boolean: If Twitter is enabled from integration
     */
    public function checkGoogleAccessToken() {
        return $this->checkGoogleHubAccessToken() && $this->checkGoogleIntegrationAccessToken();
    }

    /**
     * Gets google api key
     * 
     * @param String type: Type of api key
     * @return String: Api key
     */
    public function getGoogleApiKey($type) {
        $hub = HubConnectionBehavior::createHubInstance();
        $creds = Credentials::model()->findByPk($this->hubCredentialsId);

        $apiKey = '';

        // Check hub first
        if ($this->checkGoogleHubApiKey() && $creds->auth->enableGoogleMaps) {
            $apiKey = $hub->getGoogleApiKey(Yii::app()->user->id, $type);
        }

        // Use google integration settings if hub not enabled
        else if ($this->checkGoogleIntegrationApiKey()) {
            $credId = Yii::app()->settings->googleCredentialsId;
            $apiKey = Credentials::model()->findByPk($credId)->auth->apiKey;
        }

        return $apiKey;
    }
    
    /**
     * Gets google access token
     * 
     * @param String type: Type of api key
     * @param String redirect: Redirect link
     * @param String code: Access code
     * @param String refresh: Refresh token
     * @return Array: Access token
     */
    public function getGoogleAccessToken($type, $redirect, $code = null, $refresh = null) {
        $credentials = array();

        // Check hub first
        if ($this->checkGoogleHubAccessToken()) {
            $hub = HubConnectionBehavior::createHubInstance();
            $credentials = $hub->getGoogleAccessToken(Yii::app()->user->id, $type, $redirect, $code, $refresh);
        }

        // Use google integration settings if hub not enabled
        else if ($this->checkGoogleIntegrationAccessToken()) {
            $credId = Yii::app()->settings->googleCredentialsId;
            $creds = Credentials::model()->findByPk($credId);
            $credentials = array(
                'clientId' => $creds->auth->clientId,
                'clientSecret' => $creds->auth->clientSecret,
            );
        }

        return $credentials;
    }

    /**
     * Check if google is enabled from integration
     * 
     * @return Boolean: If Twitter is enabled from integration
     */
    private function checkGoogleHubApiKey() {
        $credId = Yii::app()->settings->googleCredentialsId;
        $credentials = Credentials::model()->findByPk($credId);

        $credCheck = $credentials && $credentials->auth->apiKey !== '';

        return HubConnectionBehavior::checkHubEnabled() && $credId && $credentials && $credCheck;
    }

    /**
     * Check if google is enabled from integration
     * 
     * @return Boolean: If Twitter is enabled from integration
     */
    private function checkGoogleHubAccessToken() {
        $credId = Yii::app()->settings->googleCredentialsId;
        $credentials = Credentials::model()->findByPk($credId);

        $credCheck = $credentials &&
                $credentials->auth->clientId !== '' &&
                $credentials->auth->clientSecret !== '';

        return HubConnectionBehavior::checkHubEnabled() && $credId && $credentials && $credCheck;
    }

    /**
     * Check if google is enabled from integration
     * 
     * @return Boolean: If Twitter is enabled from integration
     */
    private function checkGoogleIntegrationApiKey() {
        $credId = Yii::app()->settings->googleCredentialsId;
        $credentials = Credentials::model()->findByPk($credId);

        $credCheck = $credentials &&
                $credentials->auth->apiKey;

        return $credId && $credentials && $credCheck;
    }

    /**
     * Check if google is enabled from integration
     * 
     * @return Boolean: If Twitter is enabled from integration
     */
    private function checkGoogleIntegrationAccessToken() {
        $credId = Yii::app()->settings->googleCredentialsId;
        $credentials = Credentials::model()->findByPk($credId);

        $credCheck = $credentials &&
                $credentials->auth->clientId !== '' &&
                $credentials->auth->clientSecret !== '';

        return $credId && $credentials && $credCheck;
    }

    /**
     * Gets Twitter hub credentials
     * 
     * @return array Twitter credentials
     */
    private function getGoogleHubCredentials($type) {
        $id = Yii::app()->user->id;
        $hub = HubConnectionBehavior::createHubInstance();
        $credentials = json_decode($hub->getGoogleCredentials());

        $googleApiKey = $hub->getGoogleApiKey($id, $type);
        $accessToken = $hub->getGoogleAccessToken();

        return array(
            'apiKey' => $credentials->oauth_access_token,
            'clientId' => $credentials->oauth_access_token_secret,
            'clientSecret' => $credentials->consumer_key
        );
    }

}
