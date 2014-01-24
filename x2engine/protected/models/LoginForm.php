<?php

/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
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
 * @package X2CRM.models
 */
class LoginForm extends CFormModel {

    public $username;
    public $password;
    public $rememberMe;
    public $verifyCode;
    public $useCaptcha;
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
	    array('password', 'authenticate'),
	    // captcha needs to be filled out
	    array('verifyCode', 'captcha', 'allowEmpty' => !(CCaptcha::checkRequirements()), 'on' => 'loginWithCaptcha'),
	    array('verifyCode', 'safe'),
	);
    }

    /**
     * Declares attribute labels.
     * @return array
     */
    public function attributeLabels() {
	return array(
	    'username' => Yii::t('app', 'Username'),
	    'password' => Yii::t('app', 'Password'),
	    'rememberMe' => Yii::t('app', 'Remember me'),
	    'verifyCode' => Yii::t('app', 'Verification Code'),
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
			$this->_identity = new UserIdentity($this->username, $this->password);
			if (!$this->_identity->authenticate())
			$this->addError('password', Yii::t('app', 'Incorrect username or password.'));
		}
	}

	/**
	 * Logs in the user using the given username and password in the model.
	 * 
	 * @param boolean $google Whether or not Google is being used for the login
	 * @return boolean whether login is successful
	 */
    public function login($google = false) {
		if($this->_identity === null) {
			$this->_identity = new UserIdentity($this->username, $this->password);
			$this->_identity->authenticate($google);
		}
		if($this->_identity->errorCode === UserIdentity::ERROR_NONE) {
			$duration = $this->rememberMe ? 2592000 : 0; //60*60*24*30 = 30 days
			Yii::app()->user->login($this->_identity, $duration);

			// update lastLogin time
			$user = User::model()->findByPk(Yii::app()->user->getId());
			$user->lastLogin = $user->login;
			$user->login = time();
			$user->update(array('lastLogin','login'));
			
			Yii::app()->session['loginTime'] = time();
			
			return true;
		}
		
		return false;
	}

}
