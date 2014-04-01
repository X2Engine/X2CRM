<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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

/**
 * For code shared between mobile and full app site controllers
 *
 * @package application.controllers
 */
class CommonSiteControllerBehavior extends CBehavior {

    /**
     * Displays the login page
     * @param object $formModel
     * @param bool $isMobile Whether this was called from mobile site controller
     */
    public function login (LoginForm $model, $isMobile=false){
            
        $model->attributes = $_POST['LoginForm']; // get user input data
        Session::cleanUpSessions();

        $ip = $this->owner->getRealIp();

        /* increment count on every session with this user/IP, to prevent brute force attacks 
           using session_id spoofing or whatever */
        Yii::app()->db->createCommand(
            'UPDATE x2_sessions SET status=status-1,lastUpdated=:time WHERE user=:name AND 
            CAST(IP AS CHAR)=:ip AND status BETWEEN -2 AND 0')
                ->bindValues(
                    array(':time' => time(), ':name' => $model->username, ':ip' => $ip))
                ->execute();

        $activeUser = Yii::app()->db->createCommand() // see if this is an actual, active user
                ->select('username')
                ->from('x2_users')
                ->where('username=:name AND status=1', array(':name' => $model->username))
                ->limit(1)
                ->queryScalar(); // get the correctly capitalized username

        if(isset($_SESSION['sessionId']))
            $sessionId = $_SESSION['sessionId'];
        else
            $sessionId = $_SESSION['sessionId'] = session_id();

        $session = X2Model::model('Session')->findByPk($sessionId);

        /* get the number of failed login attempts from this IP within timeout interval. If the 
        number of login attempts exceeds maximum, display captcha */
        $badAttemptsRefreshTimeout = 900;
        $maxFailedLoginAttemptsPerIP = 100; 
        $badAttemptsWithThisIp = Yii::app()->db->createCommand(
            'SELECT SUM(status) FROM x2_sessions where lastUpdated > :cutoff GROUP BY ip')
            ->queryScalar(array (
                ':cutoff' => time () - $badAttemptsRefreshTimeout
            ));

        // if this client has already tried to log in, increment their attempt count
        if($session === null){
            $session = new Session;
            $session->id = $sessionId;
            $session->user = $model->username;
            $session->lastUpdated = time();
            $session->status = 0;
            $session->IP = $ip;
        }else{
            $session->lastUpdated = time();
            $session->user = $model->username;
            if($session->status < -1) {
                $model->useCaptcha = true;
                if($session->status < -2)
                    $model->setScenario('loginWithCaptcha');
            }
        }

        if ($badAttemptsWithThisIp > $maxFailedLoginAttemptsPerIP) {
            $model->useCaptcha = true;
            $session->status = -2;
        }

        if($activeUser === false){
            $model->verifyCode = ''; // clear captcha code
            $model->validate (); // validate captcha if it's being used
            $model->addError('username', Yii::t('app', 'Incorrect username or password.'));
            $model->addError('password', Yii::t('app', 'Incorrect username or password.'));
            $session->save();
        }else{
            if($model->validate() && $model->login()){  // user successfully logged in
                if($model->rememberMe){
                    foreach(array('username','rememberMe') as $attr) {
                        // Expires in 30 days
                        AuxLib::setCookie (CHtml::resolveName ($model, $attr), $model->$attr,
                            2592000);
                    }
                }else{
                    foreach(array('username','rememberMe') as $attr) {
                        // Remove the cookie if they unchecked the box
                        AuxLib::clearCookie(CHtml::resolveName($model, $attr));
                    }
                }

                // We're not using the isAdmin parameter of the application
                // here because isAdmin in this context hasn't been set yet.
                $isAdmin = Yii::app()->user->checkAccess('AdminIndex');
                if($isAdmin && !$isMobile) {
                    $this->owner->attachBehavior('updaterBehavior', new UpdaterBehavior);
                    $this->owner->checkUpdates();   // check for updates if admin
                } else
                    Yii::app()->session['versionCheck'] = true; // ...or don't

                $session->status = 1;
                $session->save();
                SessionLog::logSession($model->username, $sessionId, 'login');
                $_SESSION['playLoginSound'] = true;

                if(YII_DEBUG && EmailDeliveryBehavior::DEBUG_EMAIL)
                    Yii::app()->session['debugEmailWarning'] = 1;

                if ($isMobile) {
                    $cookie = new CHttpCookie('x2mobilebrowser', 'true'); // create cookie
                    $cookie->expire = time() + 31104000; // expires in 1 year
                    Yii::app()->request->cookies['x2mobilebrowser'] = $cookie; // save cookie
                    $this->owner->redirect($this->owner->createUrl('/mobile/site/home'));
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
                if($model->hasErrors()){
                    $model->addError('username', Yii::t('app', 'Incorrect username or password.'));
                    $model->addError('password', Yii::t('app', 'Incorrect username or password.'));
                }
                $session->save();
            }
        }
        $model->rememberMe = false;
    }
}

