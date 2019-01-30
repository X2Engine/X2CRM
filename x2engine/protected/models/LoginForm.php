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
 * Form model for logging into the app.
 *
 * @package application.models
 * @property UserIdentity $identity The user identity component for the current
 *  login.
 * @propoerty User $user The user model corresponding to the current login; null
 *  if no match for username/alias was found.
 */
class LoginForm extends X2FormModel {

    public $username;
    public $password;
    public $rememberMe;
    public $verifyCode;
    public $twoFactorCode;
    public $useCaptcha;
    public $sessionToken;
    private $_identity;

    /**
     * Validation rules for logins.
     * @return array
     */
    public function rules() {
        return array(
            // username and password are required
            array('username, password', 'required'),
            // rememberMe needs to be a boolean
            array('rememberMe', 'boolean'),
            // password needs to be authenticated
            array('sessionToken', 'authenticate'),
            // password needs to be authenticated
            array('password', 'authenticate'),
            // 2FA code needs to be verified if required
            array('twoFactorCode', 'verifySecondFactor'),
            // captcha needs to be filled out
            array(
                'verifyCode', 
                'captcha', 
                'allowEmpty' => !(CCaptcha::checkRequirements()), 'on' => 'loginWithCaptcha'),
            array('verifyCode', 'safe'),
        );
    }

    /**
     * Declares attribute labels.
     * @return array
     */
    public function attributeLabels(){
        return array(
            'username' => Yii::t('app', 'Username'),
            'password' => Yii::t('app', 'Password'),
            'rememberMe' => Yii::t('app', 'Remember me'),
            'verifyCode' => Yii::t('app', 'Verification Code'),
            'sessionToken' => Yii::t('app', 'Session Token'),
        );
    }

    /**
     * Authenticates the password.
     * 
     * This is the 'authenticate' validator as declared in rules().
     * @param string $attribute Attribute name
     * @param array $params validation parameters
     */
	public function authenticate($attribute, $params) {
		if (!$this->hasErrors()) {
			if (!$this->identity->authenticate()) {
                if($this->identity->errorCode === UserIdentity::ERROR_DISABLED){
                    $this->addError('username',Yii::t('app','Login for that user account has been disabled.'));
                    $this->addError('password',Yii::t('app','Login for that user account has been disabled.'));
                }else{
                    $this->addError('username', Yii::t('app', 'Incorrect username or password. Note, usernames are case sensitive.'));
                    $this->addError('password', Yii::t('app', 'Incorrect username or password. Note, usernames are case sensitive.'));
                }
            }
		}
	}

    /**
     * Verifies the 2FA code
     * 
     * This is the 'verifySecondFactor' validator as declared in rules().
     * @param string $attribute Attribute name
     * @param array $params validation parameters
     */
	public function verifySecondFactor($attribute, $params) {
        $profile = Profile::model()->findByAttributes(array(
            'username' => $this->username,
        ));
        if ($profile && isset($profile->enableTwoFactor) && isset($this->twoFactorCode)) {
            if ($profile->enableTwoFactor) {
                if (!$profile->verifyTwoFACode($this->twoFactorCode)) {
                    $this->addError('username', Yii::t('app', 'Incorrect username or password. Note, usernames are case sensitive.'));
                    $this->addError('password', Yii::t('app', 'Incorrect username or password. Note, usernames are case sensitive.'));
                }
            }
        }
    }

	/**
	 * Logs in the user using the given username and password in the model.
	 * 
	 * @param boolean $google Whether or not Google is being used for the login
	 * @return boolean whether login is successful
	 */
    public function login($google = false) {
        if(!isset($this->_identity))
            $this->getIdentity()->authenticate($google);
        if($this->getIdentity()->errorCode === UserIdentity::ERROR_NONE) {
			$duration = $this->rememberMe ? 2592000 : 0; //60*60*24*30 = 30 days
			Yii::app()->user->login($this->_identity, $duration);

			// update lastLogin time
			$user = User::model()->findByPk(Yii::app()->user->getId());
                        Yii::app()->setSuModel($user);
            $user->lastLogin = $user->login;
            $user->login = time();
            $user->update(array('lastLogin','login'));
			
            Yii::app()->session['loginTime'] = time();
			
            return true;
	}
		
        return false;
    }
    
	/**
	 * Logs in the user using the given sesson token in the model.
	 * 
	 * @param boolean $google Whether or not Google is being used for the login
	 * @return boolean whether login is successful
	 */
    public function loginSessionToken($google = false) {
        if(isset(Yii::app()->request->cookies['sessionToken'])){
            /*
             * TODO: Check referrer if its the correct server and 
             *       maybe implement a secret key to hash the cookie
             */
            $sessionToken = Yii::app()->request->cookies['sessionToken']->value;
            if(empty(Yii::app()->request->cookies['sessionToken']->value))
                return false;
            $sessionModel = X2Model::model('SessionToken')->findByPk($sessionToken); 
            if($sessionModel === null)
                return false;
            $user = User::model()->findByAlias($sessionModel->user);
            if($user === null)
                return false;
            $userCached = new UserIdentity($user->username, $user->password);
            $userCached->authenticate(true);
            if($userCached->errorCode === UserIdentity::ERROR_NONE) {
                $duration = $this->rememberMe ? 2592000 : 0; //60*60*24*30 = 30 days
                Yii::app()->user->login($userCached, $duration);

                // update lastLogin time
                $user = User::model()->findByPk(Yii::app()->user->getId());
                Yii::app()->setSuModel($user);
                $user->lastLogin = $user->login;
                $user->login = time();
                $user->update(array('lastLogin','login'));

                Yii::app()->session['loginTime'] = time();

                return true;
            }
        }
		
        return false;
    }

    /**
     * User identity component.
     * 
     * @return UserIdentity
     */
    public function getIdentity(){
        if(!isset($this->_identity)){
            $this->_identity = new UserIdentity($this->username, $this->password);
        }
        return $this->_identity;
    }

    /**
     * Returns the user model corresponding to the identity for the login
     *
     * @return User
     */
    public function getUser() {
        return $this->getIdentity()->getUserModel();
    }

    /**
     * Resolves the correct username to use for login form security and sessions
     *
     * @return type
     */
    public function getSessionUserName() {
        if((($user = $this->getUser()) instanceof User))
            return $user->username;
        return $this->username;
    }

}
