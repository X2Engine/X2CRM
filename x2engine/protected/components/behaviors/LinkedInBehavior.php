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
 * A behavior for interacting with the linkedIn API
 * @package application.components.behaviors
 */
class LinkedInBehavior extends CBehavior {

    private $client_id;
    private $client_secret;
    private $redirect_uri;

    /**
     * Creates an instance of LinkedInBehavior
     * 
     * @param array params: Parameters for behavior fields
     * @return LinkedInBehavior: Instance of LinkedInBehavior
     */
    public static function createLinkedInInstance() {
        $linkedIn = Yii::app()->controller->attachBehavior('LinkedInBehavior', new LinkedInBehavior);
 	$credentials = Credentials::model()->findBySql("SELECT * FROM x2_credentials WHERE modelClass='LinkedInAccount' LIMIT 1",array('userid'=>  Yii::app()->user->id));
        if (is_null($credentials) || empty($credentials)) {
	    return "fail";
        }
	$linkedIn->initialize($linkedIn->getLinkedInCredentials());
        return $linkedIn;
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
     * Check if LinkedIn is enabled
     * 
     * @return Boolean: If LinkedIn is enabled
     */
    public function checkLinkedInCredentials() {
        return $this->checkLinkedInHub() || $this->checkLinkedInIntegration();
    }
    
    /**
     * Gets LinkedIn credentials
     * 
     * @return Array: LinkedIn credentials
     */
    public function getLinkedInCredentials() {

        // If hub enabled
        // TODO: LinkedIn credentials (which is needed for certain type of
        // requests with the LinkedIn API) in hub don't make sense yet because
        // each user needs to manually input their client id and secret 
        // so might as well store it in the app. Maybe when we use a general set of
        // credentials for all x2vps users then we can move the credentials to
        // Hub which makes more sense. 
        /*if ($this->checkLinkedInHub()) {
            $credentials = $this->getLinkedInHubCredentials();
            if (!empty($credentials)) {
                $this->client_id = $credentials['client_id'];
                $this->client_secret = $credentials['client_secret'];
                $this->redirect_uri = $credentials['redirect_uri'];
            }
        }*/
        $credentials = Credentials::model()->findBySql("SELECT * FROM x2_credentials WHERE modelClass='LinkedInAccount' LIMIT 1",array('userid'=>  Yii::app()->user->id));
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
     * Check if LinkedIn is enabled from hub
     * 
     * @return Boolean: If LinkedIn is enabled from integration
     */
    private function checkLinkedInHub() {
        $hub = HubConnectionBehavior::createHubInstance();
        $credentials = json_decode($hub->getLinkedInCredentials());

        $credCheck = $credentials &&
                $credentials->client_id !== '' &&
                $credentials->client_secret !== '' &&
                $credentials->redirect_uri !== '';
        
        return HubConnectionBehavior::checkHubEnabled() && $credentials && $credCheck;
    }
    /**
     * Gets LinkedIn hub credentials
     * 
     * @return array LinkedIn credentials
     */
    private function getLinkedInHubCredentials() {
        $hub = HubConnectionBehavior::createHubInstance();
        $credentials = json_decode($hub->getLinkedInCredentials());

        return array(
            'client_id' => $credentials->client_id,
            'client_secret' => $credentials->client_secret,
            'redirect_uri' => $credentials->redirect_uri,
        );
    }


    public function getLoginUrl()
    {

        return 'https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id='.
                $this->client_id.'&state='.Yii::app()->request->csrfToken.'&redirect_uri='.$this->redirect_uri;
    }
    
    public function getAccessToken()
    {
        $response='';
        try {
            $code=Yii::app()->session['linkedIn_code'];
            $url = 'https://www.linkedin.com/oauth/v2/accessToken?'.'grant_type=authorization_code'
                    .'&code='.$code.'&redirect_uri='.$this->redirect_uri.'&client_id='.$this->client_id
                    . '&client_secret='.$this->client_secret;
            $response = RequestUtil::request(array(
                        'url' => $url,
                        'header' => array(
                            'Content-Type' => 'application/x-www-form-urlencoded',
                        ),
            ));
            $response = CJSON::decode($response);
        } catch (CHttpException $e) {
            // most likely that user very recently revoked authorization.
            // In any event, we don't have an access token, so throw an exception.
            throw new CHttpException(404,'Could not get access token: The user may have revoked the authorization response from LinkedIn.com was empty.');
        }

        if (empty($response)) {
            return 'fail';
        }

        $_SESSION['linkedin_access_token'] = $response['access_token'];

        if (empty($response['access_token'])) {
            throw new CHttpException(404,'Could not get access token: The response from LinkedIn.com did not contain a token.');
        }

        return $response['access_token'];
    }
    

}
