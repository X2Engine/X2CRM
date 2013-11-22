<?php

/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

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
