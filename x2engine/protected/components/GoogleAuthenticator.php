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




require_once 'protected/integration/Google/google-api-php-client/src/Google/autoload.php';

/**
 * Wrapper class for interaction with Google's API and authentication methods.
 * This is designed to handle all user authentication and returning of Google API
 * Client classes in an easy to use manner. Much of the code is from Google's stock
 * PHP API examples, but it has been modified to be usable with our software and
 * as such some of the comments/classes are Google developers' not mine.
 */
class GoogleAuthenticator {

    /**
     * Client ID of the Google API Project
     * @var string
     */
    public $clientId = '';

    /**
     * Client secret of the Google API Project
     * @var string
     */
    public $clientSecret = '';

    /**
     * Redirect URI for the authentication request
     * @var string
     */
    public $redirectUri = '';

    /**
     * A list of scopes required by the Google API to use for Google Integration
     * within the software. This list defines the permissions that Google will ask
     * for when a user is authenticating with them and X2.
     * @var array
     */
    public $scopes = array(
        'https://www.googleapis.com/auth/plus.login', // Google+ login required to login with Google
        'https://www.googleapis.com/auth/drive', // Drive required for drive integration
        'https://www.googleapis.com/auth/userinfo.email', // Email required for Google login
        'https://www.googleapis.com/auth/userinfo.profile', // Basic profile info required for Google login
        'https://www.googleapis.com/auth/calendar', // Calendar required for Calendar sync
        'https://www.googleapis.com/auth/calendar.readonly', // Read only Calendar required for Calendar list
    );

    /**
     * An array of errors to be returned or displayed in case something goes wrong.
     * @var array
     */
    private $_errors;

    /**
     * Master control variable that prevents most methods being called unless
     * Google Integration is enabled in the admin settings.
     * @var boolean
     */
    private $_enabled;

    /**
     * X2 Hub Credentials, if available
     * @var Credentials
     */
    private $_hubCredentials;

    /**
     * Current GoogleAuthenticator scenario for Hub interactions
     */
    private $_hubScenario = null;

    /**
     * Valid GoogleAuthenticator scenarios for Hub interactions
     */
    private $_hubScenarios = array(
        'calendar',
    );

    /**
     * Constructor that sets up the Authenticator with all the required data to
     * connect to Google properly.
     */
    public function __construct($scenario = null) {
        $this->_enabled = Yii::app()->settings->googleIntegration || HubConnectionBehavior::checkHubEnabled(); // Check if integration is enabled in the first place
        $credentials = Yii::app()->settings->getGoogleIntegrationCredentials();
        if (Yii::app()->settings->hubCredentialsId && in_array($scenario, $this->_hubScenarios)) {
            // Check if Hub integration is configured
            $hubCreds = Credentials::model()->findByPk(Yii::app()->settings->hubCredentialsId);
            if ($hubCreds && $hubCreds->auth && $hubCreds->auth->hubEnabled) {
                if ($scenario === 'calendar' && $hubCreds->auth->enableGoogleCalendar) {
                    $this->_hubScenario = $scenario;
                    if (empty($this->redirectUri)) {
                        $this->redirectUri = (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') .
                                $_SERVER['HTTP_HOST'] . Yii::app()->controller->createUrl('');
                    }
                    $this->_hubCredentials = $hubCreds;
                    return;
                }
            }
        }
        if ($this->_enabled) {
            $this->clientId = $credentials['clientId'];
            $this->clientSecret = $credentials['clientSecret'];
            if (empty($this->redirectUri)) {
                $this->redirectUri = (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') .
                        $_SERVER['HTTP_HOST'] . Yii::app()->controller->createUrl('');
            }
        }
    }

    /**
     * Retrieved stored credentials for the provided user ID.
     *
     * @param String $userId User's ID.
     * @return String Json representation of the OAuth 2.0 credentials.
     */
    public function getStoredCredentials($userId) {
        $profile = X2Model::model('Profile')->findByPk($userId);
        if (isset($profile)) {
            return $profile->googleRefreshToken;
        }
        return null;
    }

    /**
     * Store OAuth 2.0 credentials in the application's database.
     *
     * @param Integer $userId User's ID.
     * @param String $credentials Json representation of the OAuth 2.0 credentials to
      store.
     */
    public function storeCredentials($userId, $credentials) {
        $profile = X2Model::model('Profile')->findByPk($userId);
        $credentialsArray = json_decode($credentials, true);
        if (isset($profile) && isset($credentialsArray['refresh_token'])) {
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
    public function exchangeCode($authorizationCode) {
        if ($this->_enabled) {
            try {
                $client = new Google_Client();
                $client->setClientId($this->clientId);
                $client->setClientSecret($this->clientSecret);
                $client->setRedirectUri($this->redirectUri);
                $_GET['code'] = $authorizationCode;
                return $client->authenticate($authorizationCode);
            } catch (Google_Auth_Exception $e) {
                $this->setErrors($e->getMessage());
                throw new CodeExchangeException(null);
            }
        } else {
            return false;
        }
    }

    public function exchangeRefreshToken($refreshToken) {
        if ($this->_hubCredentials) {
            $hub = Yii::app()->controller->attachBehavior('HubConnectionBehavior', new HubConnectionBehavior);
            $credentials = $hub->getGoogleAccessToken(Yii::app()->user->id, $this->_hubScenario, $this->redirectUri, null, $refreshToken);
            if ($credentials)
                return $credentials;
        }
        if ($this->_enabled) {
            $client = new Google_Client();
            $client->setClientId($this->clientId);
            $client->setClientSecret($this->clientSecret);
            try {
                $client->refreshToken($refreshToken); // Try to get an access token based on the stored refresh token
                $credentials = $client->getAccessToken();
                return $credentials;
            } catch (Google_AuthException $e) {
                $profile = Yii::app()->params->profile;
                if (isset($profile)) { // If there was an error using the refresh token, remove it from the database so it can't cause issues.
                    $profile->googleRefreshToken = null;
                    $profile->update(array('googleRefreshToken'));
                }
                return false;
            }
        }
    }

    /**
     * Send a request to the UserInfo API to retrieve the user's information.
     *
     * @param String credentials OAuth 2.0 credentials to authorize the request.
     * @return Userinfo User's information.
     * @throws NoUserIdException An error occurred.
     */
    public function getUserInfo($credentials) {
        if ($this->_enabled) {
            $apiClient = new Google_Client();
            $apiClient->setAccessToken($credentials);
            $userInfoService = new Google_Service_Oauth2($apiClient);
            $userInfo = null;
            try {
                $userInfo = $userInfoService->userinfo->get();
            } catch (Google_Exception $e) {
                $this->setErrors($e->getMessage());
            }
            if ($userInfo != null && $userInfo->getId() != null) {
                return $userInfo;
            } else {
                throw new NoUserIdException();
            }
        } else {
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
    public function getAuthorizationUrl($state) {
        if (HubConnectionBehavior::checkHubEnabled()) {
            $url = $this->getAuthorizationUrlFromHub($state);
            if ($url) {
                return $url;
            }
        }
        if ($this->_enabled) {
            $client = new Google_Client();

            $client->setClientId($this->clientId);
            switch ($state) {
                case 'calendar':
                    $_SESSION['calendarForceRefresh'] = 1;
                    $client->setRedirectUri(
                            (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] .
                            Yii::app()->controller->createUrl(
                                    '/calendar/calendar/syncActionsToGoogleCalendar'));
                    break;
                default:
                    $client->setRedirectUri($this->redirectUri);
            }
            $client->setAccessType('offline');
            $client->setApprovalPrompt('force');
            $client->setState($state);
            $client->setScopes($this->scopes);

            return $client->createAuthUrl();
        } else {
            return false;
        }
    }

    protected function getAuthorizationUrlFromHub($state) {
        if (HubConnectionBehavior::checkHubEnabled()) {
            $type = $this->_hubScenario;
            $hub = HubConnectionBehavior::createHubInstance();
            switch ($state) {
                case 'calendar':
                    $_SESSION['calendarForceRefresh'] = 1;
                    $redirectUri = (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] .
                            Yii::app()->controller->createUrl(
                                    '/calendar/calendar/syncActionsToGoogleCalendar');
                    break;
                default:
                    $redirectUri = $this->redirectUri;
            }
            return $hub->getGoogleAuthorizationUrl(Yii::app()->user->id, $type, $redirectUri, $state);
        }
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
    public function getCredentials($authorizationCode, $state) {
        if ($this->_enabled) {
            try {
                $credentials = $this->exchangeCode($authorizationCode);
                $userId = Yii::app()->user->getId();
                $credentialsArray = json_decode($credentials, true);
                if (isset($credentialsArray['refresh_token'])) {
                    if (!empty($userId)) {
                        $this->storeCredentials($userId, $credentials);
                    }
                    return $credentials;
                } else {
                    $credentials = $this->getStoredCredentials($userId);
                    $credentialsArray = json_decode($credentials, true);
                    if ($credentials != null &&
                            isset($credentialsArray['refresh_token'])) {
                        return $credentials;
                    }
                }
            } catch (CodeExchangeException $e) {
                $this->setErrors($e->getMessage());
                // Drive apps should try to retrieve the user and credentials for the current
                // session.
                // If none is available, redirect the user to the authorization URL.
                $e->setAuthorizationUrl($this->getAuthorizationUrl($state));
                throw $e;
            } catch (NoUserIdException $e) {
                $this->setErrors('No e-mail address could be retrieved.');
            }
            // No refresh token has been retrieved.
            $authorizationUrl = $this->getAuthorizationUrl($state);
            throw new NoRefreshTokenException($authorizationUrl);
        } else {
            return false;
        }
    }

    /**
     * Sometimes, terrible things happen. When an auth error occurs or a problem
     * with the credentials arises, flush every place they're stored immediately
     * to stop any errors badly provided credentials may be causing.
     *
     * @param boolean full Whether or not to flush all credentials or just temporary
     * ones. This is useful because the token in the session will not contain a refresh
     * token in most cases, but the refresh token may still be valid. In that case,
     * just clearing the session tokens will allow for another attempt using the
     * refresh token.
     */
    public function flushCredentials($full = true) {
        if ($this->_enabled || $this->_hubCredentials) {
            unset($_SESSION['access_token']);
            unset($_SESSION['token']);
            unset($_GET['code']);
            $profile = Yii::app()->params->profile;
            if ($full && isset($profile)) {
                $profile->googleRefreshToken = null;
                $profile->update(array('googleRefreshToken'));
            }
        }
    }

    /**
     * This function is used to get the current access token. This function is
     * vital to the class as it allows any code using the Authenticator to discover
     * if a connection to Google has been made and if any actions connecting to
     * Google can procede. This function is called in a large number of places
     * as a gate to continuing integration tasks.
     * @param Integer $userId The ID of the current User
     * @return String|boolean Returns either the JSON encoded access token, or false on failure
     */
    public function getAccessToken($userId = null) {
        if (HubConnectionBehavior::checkHubEnabled()) {
            $token = $this->getAccessTokenFromHub($userId);
            if ($token) {
                return $token;
            }
        }
        if ($this->_enabled) {
            $client = new Google_Client();
            $client->setClientId($this->clientId);
            $client->setClientSecret($this->clientSecret);
            if (empty($userId)) {
                $userId = Yii::app()->user->getId();
            }
            if (isset($_SESSION['access_token'])) { // The access token is already stored in the session, return it.
                return $_SESSION['access_token'];
            }
            if (!empty($userId) && !is_null($this->getStoredCredentials($userId))) { // We found a stored refresh token
                $refreshToken = $this->getStoredCredentials($userId);
                try {
                    $client->refreshToken($refreshToken); // Try to get an access token based on the stored refresh token
                    $credentials = $client->getAccessToken(); // No recursion, this is a different function
                    $_SESSION['token'] = $credentials; // Set credentials as a session variable for quicker lookup.
                    $_SESSION['access_token'] = $credentials;
                    return $credentials;
                } catch (Google_Auth_Exception $e) {
                    $profile = Yii::app()->params->profile;
                    if (isset($profile)) { // If there was an error using the refresh token, remove it from the database so it can't cause issues.
                        $profile->googleRefreshToken = null;
                        $profile->update(array('googleRefreshToken'));
                    }
                    return false;
                }
            }
            if (isset($_GET['code'])) { // There is a Google auth code in the GET request header.
                try {
                    $credentials = $this->getCredentials($_GET['code'], null); // Attempt to exchange the auth code for an access token.
                    $_SESSION['token'] = $credentials;
                    $_SESSION['access_token'] = $credentials;
                    return $credentials;
                } catch (CodeExchangeException $e) {
                    return false;
                }
            }
        }
        return false; // No token was ever returned due to data not being set or exceptions. Return false to indicate a failure.
    }

    protected function getAccessTokenFromHub($userId) {
        if (isset($_SESSION['access_token'])) { // The access token is already stored in the session, return it.
            return $_SESSION['access_token'];
        }
        if (HubConnectionBehavior::checkHubEnabled()) {
            $type = $this->_hubScenario;
            $hub = HubConnectionBehavior::createHubInstance();

            $userId = empty($userId) ? Yii::app()->user->getId() : $userId;
                
            if (!empty($userId) && !is_null($this->getStoredCredentials($userId))) { // We found a stored refresh token
                $refreshToken = $this->getStoredCredentials($userId);
                try {
                    $credentials = $hub->getGoogleAccessToken($userId, $type, $this->redirectUri, null, $refreshToken);
                    if ($credentials) {
                        $_SESSION['token'] = $credentials; // Set credentials as a session variable for quicker lookup.
                        $_SESSION['access_token'] = $credentials;
                        return $credentials;
                    }
                } catch (Google_Auth_Exception $e) {
                    $profile = Yii::app()->params->profile;
                    if (isset($profile)) { // If there was an error using the refresh token, remove it from the database so it can't cause issues.
                        $profile->googleRefreshToken = null;
                        $profile->update(array('googleRefreshToken'));
                    }
                    return false;
                }
            }
            if (isset($_GET['code'])) { // There is a Google auth code in the GET request header.
                try {
                    $credentials = $hub->getGoogleAccessToken($userId, $type, $this->redirectUri, $_GET['code']); // Attempt to exchange the auth code for an access token.
                    if ($credentials) {
                        $_SESSION['token'] = $credentials;
                        $_SESSION['access_token'] = $credentials;
                        $credentialsArray = CJSON::decode($credentials, true);
                        if (!empty($userId) && $credentialsArray && isset($credentialsArray['refresh_token'])) {
                            $this->storeCredentials($userId, $credentials);
                        }
                        return $credentials;
                    }
                } catch (CodeExchangeException $e) {
                    return false;
                }
            }
        }
        return false; // No token was ever returned due to data not being set or exceptions. Return false to indicate a failure.
    }

    public function getDriveService() {
        if ($this->getAccessToken()) {
            $client = new Google_Client();
            $client->setClientId($this->clientId);
            $client->setClientSecret($this->clientSecret);
            $client->setRedirectUri($this->redirectUri);
            $client->setScopes(array('https://www.googleapis.com/auth/drive'));
            $client->setAccessToken($this->getAccessToken());
            return new Google_Service_Drive($client);
        } else {
            return false;
        }
    }

    public function getCalendarService() {
        if ($this->getAccessToken()) {
            $client = new Google_Client();
            $client->setClientId($this->clientId);
            $client->setClientSecret($this->clientSecret);
            $client->setRedirectUri($this->redirectUri);
            $client->setScopes(array(
                'https://www.googleapis.com/auth/calendar',
                'https://www.googleapis.com/auth/calendar.readonly'));
            $client->setAccessToken($this->getAccessToken());
            return new Google_Service_Calendar($client);
        } else {
            return false;
        }
    }

    public function setErrors($message) {
        if (!is_array($this->_errors)) {
            $this->_errors = array(
                $message
            );
        } else {
            $this->_errors[] = $message;
        }
    }

    public function getErrors() {
        if (!is_array($this->_errors) || empty($this->_errors)) {
            return false;
        } else {
            return $this->_errors;
        }
    }

}

// All code below this point is stock Google code which I have not modified.

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
    public function __construct($authorizationUrl) {
        $this->authorizationUrl = $authorizationUrl;
    }

    /**
     * @return the authorizationUrl.
     */
    public function getAuthorizationUrl() {
        return $this->authorizationUrl;
    }

    /**
     * Set the authorization URL.
     */
    public function setAuthorizationurl($authorizationUrl) {
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
