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



Yii::import('application.components.ThemeGenerator.LoginThemeHelper');
/**
 * For code shared between mobile and full app site controllers
 *
 * @package application.controllers
 */
class CommonSiteControllerBehavior extends CBehavior {

    /**
     * Displays the login page
     * @param object $formModel
     */
    public function login (LoginForm $model, $isMobile=false){
        ob_start();
        if($isMobile && SessionToken::cleanSessionTokenCookie()) {
            unset(Yii::app()->request->cookies['sessionToken']);
        }
        Session::cleanUpSessions();
        if($isMobile) {
            SessionToken::cleanUpSessions();
        }
        $ip = $this->owner->getRealIp();
        $this->verifyIpAccess ($ip);
        $activeUser = null;
        $sessionToken = null;
        $sessionIdToken = null;
        if($isMobile) {
            if(empty(Yii::app()->request->cookies['sessionToken']->value)){
                if(isset($_POST['LoginForm']))
                    $model->attributes = $_POST['LoginForm']; // get user input data

                $userModel = $model->getUser();
                $isRealUser = $userModel instanceof User;
                $effectiveUsername = $isRealUser ? $userModel->username : $model->username;
                $isActiveUser = $isRealUser && $userModel->status == User::STATUS_ACTIVE;
                /* increment count on every session with this user/IP, to prevent brute force attacks 
                   using session_id spoofing or whatever */
                Yii::app()->db->createCommand(
                    'UPDATE x2_sessions SET status=status-1,lastUpdated=:time WHERE user=:name AND 
                    CAST(IP AS CHAR)=:ip AND status BETWEEN -2 AND 0')
                        ->bindValues(
                            array(':time' => time(), ':name' => $effectiveUsername, ':ip' => $ip))
                        ->execute();

                $activeUser = Yii::app()->db->createCommand() // see if this is an actual, active user
                        ->select('username')
                        ->from('x2_users')
                        ->where('username=:name AND status=1', array(':name' => $model->username))
                        ->limit(1)
                        ->queryScalar(); // get the correctly capitalized username  

                //create new sessionToken and save to server
                $sessionIdToken = PasswordUtil::getToken(32);

                $sessionToken = new SessionToken;
                $sessionToken->id = $sessionIdToken;
                $sessionToken->user = $model->getSessionUserName();
                $sessionToken->lastUpdated = time();
                $sessionToken->status = 0;
                $sessionToken->IP = $ip;
                $sessionToken->save();

            } else {

                $sessionTokenCookie = Yii::app()->request->cookies['sessionToken']->value;
                $sessionTokenModel = X2Model::model('SessionToken')->findByPk($sessionTokenCookie);

                $userModel =  User::model()->findByAlias($sessionTokenModel->user);
                $isRealUser = $userModel instanceof User;
                $effectiveUsername = $isRealUser ? $userModel->username : null;
                $isActiveUser = $isRealUser && $userModel->status == User::STATUS_ACTIVE;

                /* increment count on every session with this user/IP, to prevent brute force attacks 
                   using session_id spoofing or whatever */
                if($sessionTokenModel !== null){
                    Yii::app()->db->createCommand(
                        'UPDATE x2_sessions_token SET status=status-1 WHERE user=:name AND 
                        CAST(IP AS CHAR)=:ip AND status BETWEEN -2 AND 0')
                            ->bindValues(
                                array(':name' => $sessionTokenModel->user, ':ip' => $ip))
                            ->execute();
                }

                $activeUser = Yii::app()->db->createCommand() // see if this is an actual, active user
                        ->select('username')
                        ->from('x2_users')
                        ->where('username=:name AND status=1', array(':name' => $sessionTokenModel->user))
                        ->limit(1)
                        ->queryScalar(); // get the correctly capitalized username 

            }
        } else {
            if(isset($_POST['LoginForm']))
                $model->attributes = $_POST['LoginForm']; // get user input data

            $userModel = $model->getUser();
            $isRealUser = $userModel instanceof User;
            $effectiveUsername = $isRealUser ? $userModel->username : $model->username;
            $isActiveUser = $isRealUser && $userModel->status == User::STATUS_ACTIVE;            
            Yii::app()->db->createCommand(
                    'UPDATE x2_sessions SET status=status-1,lastUpdated=:time WHERE user=:name AND 
                    CAST(IP AS CHAR)=:ip AND status BETWEEN -2 AND 0')
                        ->bindValues(
                            array(':time' => time(), ':name' => $effectiveUsername, ':ip' => $ip))
                        ->execute();

            $activeUser = Yii::app()->db->createCommand() // see if this is an actual, active user
                    ->select('username')
                    ->from('x2_users')
                    ->where('username=:name AND status=1', array(':name' => $model->username))
                    ->limit(1)
                    ->queryScalar(); // get the correctly capitalized username  
        }


        if(isset($_SESSION['sessionId']))
            $sessionId = $_SESSION['sessionId'];
        else
            $sessionId = $_SESSION['sessionId'] = session_id();

        $session = X2Model::model('Session')->findByPk($sessionId);

        /* get the number of failed login attempts from this IP within timeout interval. If the 
        number of login attempts exceeds maximum, display captcha */
        $badAttemptsRefreshTimeout = 900;
        $maxFailedLoginAttemptsPerIP = 100;
        $maxLoginsBeforeCaptcha = 5;
        
        if (Yii::app()->contEd('pla')) {
            $badAttemptsRefreshTimeout = Yii::app()->settings->loginTimeout;
            $maxFailedLoginAttemptsPerIP = Yii::app()->settings->maxFailedLogins;
            $maxLoginsBeforeCaptcha = Yii::app()->settings->failedLoginsBeforeCaptcha;
        }
        
        $this->pruneTimedOutBans ($badAttemptsRefreshTimeout);
        $failedLoginRecord = FailedLogins::model()->findActiveByIp ($ip);
        $badAttemptsWithThisIp = ($failedLoginRecord) ? $failedLoginRecord->attempts : 0;
        if ($badAttemptsWithThisIp >= $maxFailedLoginAttemptsPerIP) {
            $this->recordFailedLogin ($ip);
            throw new CHttpException (403, Yii::t('app',
                'You are not authorized to use this application'));
        }
        // if this client has already tried to log in, increment their attempt count
        if ($session === null) {
            $session = new Session;
            $session->id = $sessionId;
            $session->user = $model->getSessionUserName();
            $session->lastUpdated = time();
            $session->status = 0;
            $session->IP = $ip;
        } else {
            $session->lastUpdated = time();
            if($model->getSessionUserName() !== null)
                $session->user = $model->getSessionUserName();
        }

        if($model->validate() && $model->login() && $isActiveUser){  // user successfully logged in
                
            $this->recordSuccessfulLogin ($activeUser, $ip);
            $userModel->logLocation('login', 'POST');


            if($model->rememberMe){
                foreach(array('username','rememberMe') as $attr) {
                    $cookieName = CHtml::resolveName ($model, $attr);
                    $cookie = new CHttpCookie(
                        $cookieName, $model->$attr);
                    $cookie->expire = time () + 2592000; //60*60*24*30 = 30 days
                    Yii::app()->request->cookies[$cookieName] = $cookie; // save cookie
                }
                if($isMobile){
                    $cookie = new CHttpCookie('sessionToken', $sessionIdToken);
                    $cookie->expire = time()+60*60*24*180; 
                    Yii::app()->request->cookies['sessionToken'] = $cookie;            
                }
            } else{
                foreach(array('username','rememberMe') as $attr) {
                    // Remove the cookie if they unchecked the box
                    AuxLib::clearCookie(CHtml::resolveName($model, $attr));
                }
            }

            // We're not using the isAdmin parameter of the application
            // here because isAdmin in this context hasn't been set yet.
            $isAdmin = Yii::app()->user->checkAccess('AdminIndex');
            if($isAdmin) {
                $this->owner->attachBehavior('updaterBehavior', new UpdaterBehavior);
                $this->owner->checkUpdates();   // check for updates if admin
            } else
                Yii::app()->session['versionCheck'] = true; // ...or don't

            $session->status = 1;
            $session->save();            
            
            SessionLog::logSession($model->username, $sessionId, 'login');
            $_SESSION['playLoginSound'] = true;

            if(YII_UNIT_TESTING && defined ('X2_DEBUG_EMAIL') && X2_DEBUG_EMAIL)
                Yii::app()->session['debugEmailWarning'] = 1;

            LoginThemeHelper::login();
            
            if ($isMobile) {
                $this->owner->redirect($this->owner->createUrl('/mobile/home'));
            } else {
                if(Yii::app()->user->returnUrl == '/site/index') {
                    $this->owner->redirect(array('/site/index'));
                } else {
                    // after login, redirect to wherever
                    $this->owner->redirect(Yii::app()->user->returnUrl); 
                }
            }

        } else if ($isMobile && $model->loginSessionToken() && $isActiveUser){ 
                
            //refresh the number of tokens a user owns
            SessionToken::cleanUpUserTokens (Yii::app()->user->name);
            $this->recordSuccessfulLogin ($activeUser, $ip);
            $userModel->logLocation('login', 'POST');

            // We're not using the isAdmin parameter of the application
            // here because isAdmin in this context hasn't been set yet.
            $isAdmin = Yii::app()->user->checkAccess('AdminIndex');
            if($isAdmin) {
                $this->owner->attachBehavior('updaterBehavior', new UpdaterBehavior);
                $this->owner->checkUpdates();   // check for updates if admin
            } else
                Yii::app()->session['versionCheck'] = true; // ...or don't

            $session->status = 1;
            $session->save();
            if($isMobile && Yii::app()->request->cookies['sessionToken']->value != null){
                SessionLog::logSession(Yii::app()->user->name, Yii::app()->request->cookies['sessionToken']->value, 'login');
            }
            $_SESSION['playLoginSound'] = true;

            if(YII_UNIT_TESTING && defined ('X2_DEBUG_EMAIL') && X2_DEBUG_EMAIL)
                Yii::app()->session['debugEmailWarning'] = 1;      

            LoginThemeHelper::login();

            if ($isMobile) {
                $this->owner->redirect($this->owner->createUrl('/mobile/home'));
            } else {
                if(Yii::app()->user->returnUrl == '/site/index') {
                    $this->owner->redirect(array('/site/index'));
                } else {
                    // after login, redirect to wherever
                    $this->owner->redirect(Yii::app()->user->returnUrl); 
                }
            }
        } else{ // login failed
            $model->verifyCode = ''; // clear captcha code
            $this->recordFailedLogin ($ip);
            $session->save();

            if ($badAttemptsWithThisIp + 1 >= $maxFailedLoginAttemptsPerIP) {
                throw new CHttpException (403, Yii::t('app',
                    'You are not authorized to use this application'));
            } else if ($badAttemptsWithThisIp >= $maxLoginsBeforeCaptcha - 1) {
                $model->useCaptcha = true;
                $model->setScenario('loginWithCaptcha');
                    $session->status = -2;
            }
        }
        
       
    }

    /**
     * @return bool Whether this IP has reached the CAPTCHA threshold
     */
    protected function loginRequiresCaptcha() {
        if (isset($_SESSION['sessionId'])) {
            $failedLoginRecord = FailedLogins::model()->findActiveByIp ($this->owner->getRealIp());
            $badAttemptsWithThisIp = ($failedLoginRecord) ? $failedLoginRecord->attempts : 0;

            $maxLoginsBeforeCaptcha = 5;
            
            if (Yii::app()->contEd('pla'))
                $maxLoginsBeforeCaptcha = Yii::app()->settings->failedLoginsBeforeCaptcha;
            

            return $badAttemptsWithThisIp >= $maxLoginsBeforeCaptcha;
        }
    }

    public function recordFailedLogin($ip) {
        $record = FailedLogins::model()->findActiveByIp ($ip);
        if ($record) {
            $record->attempts++;
        } else {
            $record = new FailedLogins;
            $record->IP = $ip;
            $record->attempts = 1;
        }
        $record->lastAttempt = time();
        $record->save();
    }

    /**
     * Update any timed out bans and mark them as inactive
     * @param int Timeout period (in minutes)
     */
    private function pruneTimedOutBans ($badAttemptsRefreshTimeout) {
        Yii::app()->db->createCommand()
            ->update (
                'x2_failed_logins',
                array('active' => 0),
                'active = 1 AND lastAttempt < :timeout',
                array(':timeout' => time() - ($badAttemptsRefreshTimeout * 60))
            );
    }

    
    public function recordSuccessfulLogin($user, $ip) {
        $loginRecord = new SuccessfulLogins;
        $loginRecord->username = $user;
        $loginRecord->IP = $ip;
        $loginRecord->save();
    }

    /**
     * Enforce the applications whitelist/blacklist settings
     * @param string $ip IP Address
     */
    public function verifyIpAccess($ip) {
        if (empty($ip))
            return;
        $admin = Yii::app()->settings;
        if ($admin->accessControlMethod === 'blacklist') {
            if ($this->isBannedIp ($ip))
                throw new CHttpException (403, Yii::t('app',
                    'You are not authorized to use this application'));
        } else {
            if (!$this->isWhitelistedIp ($ip))
                throw new CHttpException (403, Yii::t('app',
                    'You are not authorized to use this application'));
        }
    }

    /**
     * Determine whether an IP address is in the blacklist
     * @param string $ip IP Address
     * @return boolean Whether the IP has been banned
     */
    public function isBannedIp($ip) {
        $admin = Yii::app()->settings;
        $bannedIps = $admin->ipBlacklist;
        if (!$bannedIps || !is_array($bannedIps)){
            return false;
        }

        return $this->checkIpList($bannedIps, $ip);
    }

    /**
     * Determine whether an IP address is in the whitelist
     * @param string $ip IP Address
     * @return boolean Whether the IP is allowed
     */
    public function isWhitelistedIp($ip) {
        $admin = Yii::app()->settings;
        $allowedIps = $admin->ipWhitelist;
        // No whitelist available: allow connections to prevent lockout
        if (!$allowedIps || !is_array($allowedIps)){
            return true;
        }

        return $this->checkIpList($allowedIps, $ip);
    }

    /**
     * Scan a list of IP addresses looking for a match
     * @param array $list List of IP addresses
     * @param string $ip IP address to search for
     */
    private function checkIpList($list, $ip) {
        foreach ($list as $address) {
            if (preg_match('|/|', $address)) {
                if (X2IPAddress::subnetContainsIp($address, $ip))
                    return true;
            } else {
                if ($address === $ip)
                    return true;
            }
        }
        return false;
    }
    
}

