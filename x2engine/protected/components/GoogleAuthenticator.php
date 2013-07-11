<?php

/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/
require_once 'protected/extensions/google-api-php-client/src/Google_Client.php';
require_once 'protected/extensions/google-api-php-client/src/contrib/Google_DriveService.php';
require_once 'protected/extensions/google-api-php-client/src/contrib/Google_Oauth2Service.php';
require_once "protected/extensions/google-api-php-client/src/contrib/Google_CalendarService.php";

class GoogleAuthenticator {

    public $clientId = '';
    public $clientSecret = '';
    public $redirectUri = '';
    public $scopes = array(
        'https://www.googleapis.com/auth/plus.login',
        'https://www.googleapis.com/auth/drive',
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/userinfo.profile',
        'https://www.googleapis.com/auth/calendar',
        'https://www.googleapis.com/auth/calendar.readonly',
    );
    private $_errors;
    private $_enabled;

    public function __construct($clientId = null, $clientSecret = null, $redirectUri = null){
        $this->_enabled = Yii::app()->params->admin->googleIntegration;
        if($this->_enabled){
            $this->clientId = $clientId;
            $this->clientSecret = $clientSecret;
            $this->redirectUri = $redirectUri;
            if(empty($this->clientId)){
                $this->clientId = Yii::app()->params->admin->googleClientId;
            }
            if(empty($this->clientSecret)){
                $this->clientSecret = Yii::app()->params->admin->googleClientSecret;
            }
            if(empty($this->redirectUri)){
                $this->redirectUri = (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].Yii::app()->controller->createUrl('');
            }
        }
    }

    /**
     * Retrieved stored credentials for the provided user ID.
     *
     * @param String $userId User's ID.
     * @return String Json representation of the OAuth 2.0 credentials.
     */
    public function getStoredCredentials($userId){
        $profile = X2Model::model('Profile')->findByPk($userId);
        if(isset($profile)){
            return $profile->googleRefreshToken;
        }
        return null;
    }

    /**
     * Store OAuth 2.0 credentials in the application's database.
     *
     * @param String $userId User's ID.
     * @param String $credentials Json representation of the OAuth 2.0 credentials to
      store.
     */
    public function storeCredentials($userId, $credentials){
        $profile = X2Model::model('Profile')->findByPk($userId);
        $credentialsArray = json_decode($credentials, true);
        if(isset($profile) && isset($credentialsArray['refresh_token'])){
            $profile->googleRefreshToken = $credentialsArray['refresh_token'];
            $profile->update(array('googleRefreshToken'));
        }
    }

    /**
     * Exchange an authorization code for OAuth 2.0 credentials.
     *
     * @param String $authorizationCode Authorization code to exchange for OAuth 2.0
     *                                  credentials.
     * @return String Json representation of the OAuth 2.0 credentials.
     * @throws CodeExchangeException An error occurred.
     */
    public function exchangeCode($authorizationCode){
        if($this->_enabled){
            try{
                $client = new Google_Client();
                $client->setClientId($this->clientId);
                $client->setClientSecret($this->clientSecret);
                $client->setRedirectUri($this->redirectUri);
                $_GET['code'] = $authorizationCode;
                return $client->authenticate();
            }catch(Google_AuthException $e){
                $this->setErrors($e->getMessage());
                throw new CodeExchangeException(null);
            }
        }else{
            return false;
        }
    }

    /**
     * Send a request to the UserInfo API to retrieve the user's information.
     *
     * @param String credentials OAuth 2.0 credentials to authorize the request.
     * @return Userinfo User's information.
     * @throws NoUserIdException An error occurred.
     */
    public function getUserInfo($credentials){
        if($this->_enabled){
            $apiClient = new Google_Client();
            $apiClient->setUseObjects(true);
            $apiClient->setAccessToken($credentials);
            $userInfoService = new Google_Oauth2Service($apiClient);
            $userInfo = null;
            try{
                $userInfo = $userInfoService->userinfo->get();
            }catch(Google_Exception $e){
                $this->setErrors($e->getMessage());
            }
            if($userInfo != null && $userInfo->getId() != null){
                return $userInfo;
            }else{
                throw new NoUserIdException();
            }
        }else{
            return false;
        }
    }

    /**
     * Retrieve the authorization URL.
     *
     * @param String $emailAddress User's e-mail address.
     * @param String $state State for the authorization URL.
     * @return String Authorization URL to redirect the user to.
     */
    public function getAuthorizationUrl($state){
        if($this->_enabled){
            $client = new Google_Client();

            $client->setClientId($this->clientId);
            $client->setRedirectUri($this->redirectUri);
            $client->setAccessType('offline');
            $client->setApprovalPrompt('force');
            $client->setState($state);
            $client->setScopes($this->scopes);

            return $client->createAuthUrl();
        }else{
            return false;
        }
//        $tmpUrl = parse_url($client->createAuthUrl());
//        $query = explode('&', $tmpUrl['query']);
//        $query[] = 'user_id='.urlencode($emailAddress);
//        return
//                $tmpUrl['scheme'].'://'.$tmpUrl['host'].$tmpUrl['port'].
//                $tmpUrl['path'].'?'.implode('&', $query);
    }

    /**
     * Retrieve credentials using the provided authorization code.
     *
     * This function exchanges the authorization code for an access token and
     * queries the UserInfo API to retrieve the user's e-mail address. If a
     * refresh token has been retrieved along with an access token, it is stored
     * in the application database using the user's e-mail address as key. If no
     * refresh token has been retrieved, the function checks in the application
     * database for one and returns it if found or throws a NoRefreshTokenException
     * with the authorization URL to redirect the user to.
     *
     * @param String authorizationCode Authorization code to use to retrieve an access
     *                                 token.
     * @param String state State to set to the authorization URL in case of error.
     * @return String Json representation of the OAuth 2.0 credentials.
     * @throws NoRefreshTokenException No refresh token could be retrieved from
     *         the available sources.
     */
    public function getCredentials($authorizationCode, $state){
        if($this->_enabled){
            try{
                $credentials = $this->exchangeCode($authorizationCode);
                $userId = Yii::app()->user->getId();
                $credentialsArray = json_decode($credentials, true);
                if(isset($credentialsArray['refresh_token'])){
                    if(!empty($userId)){
                        $this->storeCredentials($userId, $credentials);
                    }
                    return $credentials;
                }else{
                    $credentials = $this->getStoredCredentials($userId);
                    $credentialsArray = json_decode($credentials, true);
                    if($credentials != null &&
                            isset($credentialsArray['refresh_token'])){
                        return $credentials;
                    }
                }
            }catch(CodeExchangeException $e){
                $this->setErrors($e->getMessage());
                // Drive apps should try to retrieve the user and credentials for the current
                // session.
                // If none is available, redirect the user to the authorization URL.
                $e->setAuthorizationUrl($this->getAuthorizationUrl($state));
                throw $e;
            }catch(NoUserIdException $e){
                $this->setErrors('No e-mail address could be retrieved.');
            }
            // No refresh token has been retrieved.
            $authorizationUrl = $this->getAuthorizationUrl($state);
            throw new NoRefreshTokenException($authorizationUrl);
        }else{
            return false;
        }
    }

    public function getAccessToken($userId = null){
        if($this->_enabled){
            $client = new Google_Client();
            $client->setClientId($this->clientId);
            $client->setClientSecret($this->clientSecret);
            if(empty($userId)){
                $userId = Yii::app()->user->getId();
            }
            if(isset($_SESSION['access_token'])){
//            $token=json_decode($_SESSION['access_token']);
//            $reqUrl = 'https://www.googleapis.com/oauth2/v1/tokeninfo?access_token='.
//                    $token->access_token;
//            $req = new Google_HttpRequest($reqUrl);
//
//            $tokenInfo = json_decode(
//                    $client::getIo()->authenticatedRequest($req)->getResponseBody());
//            if(!isset($tokenInfo->error)){
                return $_SESSION['access_token'];
//            }
            }
            if(!empty($userId) && !is_null($this->getStoredCredentials($userId))){
                $refreshToken = $this->getStoredCredentials($userId);
                $client->refreshToken($refreshToken);
                $credentials = $client->getAccessToken();
                $_SESSION['token'] = $credentials;
                $_SESSION['access_token'] = $credentials;
                return $credentials;
            }
            if(isset($_GET['code'])){
                $credentials = $this->getCredentials($_GET['code'], null);
                $_SESSION['token'] = $credentials;
                $_SESSION['access_token'] = $credentials;
                return $credentials;
            }
        }
        return false;
    }

    public function getDriveService(){
        if($this->getAccessToken()){
            $client = new Google_Client();
            $client->setClientId($this->clientId);
            $client->setClientSecret($this->clientSecret);
            $client->setRedirectUri($this->redirectUri);
            $client->setScopes(array('https://www.googleapis.com/auth/drive'));
            $client->setAccessToken($this->getAccessToken());
            return new Google_DriveService($client);
        }else{
            return false;
        }
    }

    public function getCalendarService(){
        if($this->getAccessToken()){
            $client = new Google_Client();
            $client->setClientId($this->clientId);
            $client->setClientSecret($this->clientSecret);
            $client->setRedirectUri($this->redirectUri);
            $client->setScopes(array(
                'https://www.googleapis.com/auth/calendar',
                'https://www.googleapis.com/auth/calendar.readonly'));
            $client->setAccessToken($this->getAccessToken());
            return new Google_CalendarService($client);
        }else{
            return false;
        }
    }

    public function setErrors($message){
        if(!is_array($this->_errors)){
            $this->_errors = array(
                $message
            );
        }else{
            $this->_errors[] = $message;
        }
    }

    public function getErrors(){
        if(!is_array($this->_errors) || empty($this->_errors)){
            return false;
        }else{
            return $this->_errors;
        }
    }

}

/**
 * Exception thrown when an error occurred while retrieving credentials.
 */
class GetCredentialsException extends Exception {

    protected $authorizationUrl;

    /**
     * Construct a GetCredentialsException.
     *
     * @param authorizationUrl The authorization URL to redirect the user to.
     */
    public function __construct($authorizationUrl){
        $this->authorizationUrl = $authorizationUrl;
    }

    /**
     * @return the authorizationUrl.
     */
    public function getAuthorizationUrl(){
        return $this->authorizationUrl;
    }

    /**
     * Set the authorization URL.
     */
    public function setAuthorizationurl($authorizationUrl){
        $this->authorizationUrl = $authorizationUrl;
    }

}

/**
 * Exception thrown when no refresh token has been found.
 */
class NoRefreshTokenException extends GetCredentialsException {

}

/**
 * Exception thrown when a code exchange has failed.
 */
class CodeExchangeException extends GetCredentialsException {

}

/**
 * Exception thrown when no user ID could be retrieved.
 */
class NoUserIdException extends Exception {

}

?>
