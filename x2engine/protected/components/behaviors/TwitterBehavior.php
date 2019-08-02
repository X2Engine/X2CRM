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
 * A behavior for interacting with the twitter API
 * @package application.components.behaviors
 */
class TwitterBehavior extends CBehavior {

    private $oauthAccessToken;
    private $oauthAccessTokenSecret;
    private $consumerKey;
    private $consumerSecret;

    /**
     * Creates an instance of TwitterBehavior
     * 
     * @param array params: Parameters for behavior fields
     * @return TwitterBehavior: Instance of TwitterBehavior
     */
    public static function createTwitterInstance() {
        $twitter = Yii::app()->controller->attachBehavior('TwitterBehavior', new TwitterBehavior);
        $twitter->initialize($twitter->retrieveTwitterCredentials());
        return $twitter;
    }

    /**
     * Initializes behavior parameters
     * 
     * @param Array params: Parameters for behavior fields 
     */
    public function initialize(array $params = array()) {
        foreach ($params as $key => $value) {
            if (in_array($key, array('oauthAccessToken', 'oauthAccessTokenSecret', 'consumerKey', 'consumerSecret'))) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Check if Twitter is enabled
     * 
     * @return Boolean: If Twitter is enabled
     */
    public function checkTwitterCredentials() {
        return $this->oauthAccessToken && $this->oauthAccessToken !== '' &&
                $this->oauthAccessTokenSecret && $this->oauthAccessTokenSecret !== '' &&
                $this->consumerKey && $this->consumerKey !== '' &&
                $this->consumerSecret && $this->consumerSecret !== '';
    }

    /**
     * Gets twitter credentials
     * 
     * @return Array: Twitter credentials
     */
    public function getTwitterCredentials() {
        return array(
            'oauth_access_token' => $this->oauthAccessToken,
            'oauth_access_token_secret' => $this->oauthAccessTokenSecret,
            'consumer_key' => $this->consumerKey,
            'consumer_secret' => $this->consumerSecret
        );
    }

    public function retrieveTwitterCredentials() {
        $credentials = array();

        // If hub enabled
        if ($this->checkTwitterHub()) {
            $credentials = $this->getTwitterHubCredentials();
        }

        // If credentials
        else if ($this->checkTwitterIntegration()) {
            $credentials = $this->getTwitterIntegrationCredentials();
        }

        return $credentials;
    }

    /**
     * Check if Twitter is enabled from hub
     * 
     * @return Boolean: If Twitter is enabled from integration
     */
    private function checkTwitterHub() {
        $hub = HubConnectionBehavior::createHubInstance();
        $credentials = json_decode($hub->getTwitterCredentials());

        $credCheck = $credentials &&
                $credentials->oauth_access_token !== '' &&
                $credentials->oauth_access_token_secret !== '' &&
                $credentials->consumer_key !== '' &&
                $credentials->consumer_secret !== '';

        return HubConnectionBehavior::checkHubEnabled() && $credentials && $credCheck;
    }

    /**
     * Check if Twitter is enabled from integration
     * 
     * @return Boolean: If Twitter is enabled from integration
     */
    private function checkTwitterIntegration() {
        $credId = Yii::app()->settings->twitterCredentialsId;
        $credentials = Credentials::model()->findByPk($credId);

        $credCheck = $credentials &&
                $credentials->auth->oauthAccessToken !== '' &&
                $credentials->auth->oauthAccessTokenSecret !== '' &&
                $credentials->auth->consumerKey !== '' &&
                $credentials->auth->consumerSecret !== '';

        return $credId && $credentials && $credCheck;
    }

    /**
     * Gets Twitter hub credentials
     * 
     * @return array Twitter credentials
     */
    private function getTwitterHubCredentials() {
        $hub = HubConnectionBehavior::createHubInstance();
        $credentials = json_decode($hub->getTwitterCredentials());

        return array(
            'oauthAccessToken' => $credentials->oauth_access_token,
            'oauthAccessTokenSecret' => $credentials->oauth_access_token_secret,
            'consumerKey' => $credentials->consumer_key,
            'consumerSecret' => $credentials->consumer_secret,
        );
    }

    /**
     * Gets Twitter integration credentials
     * 
     * @return array Twitter credentials
     */
    private function getTwitterIntegrationCredentials() {
        $credId = Yii::app()->settings->twitterCredentialsId;
        $credentials = Credentials::model()->findByPk($credId);
        return array(
            'oauthAccessToken' => $credentials->auth->oauthAccessToken,
            'oauthAccessTokenSecret' => $credentials->auth->oauthAccessTokenSecret,
            'consumerKey' => $credentials->auth->consumerKey,
            'consumerSecret' => $credentials->auth->consumerSecret,
        );
    }

}
