<?php

/* * *******************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 * ****************************************************************************** */

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
	if ($this->_identity === null) {
	    $this->_identity = new UserIdentity($this->username, $this->password);
	    $this->_identity->authenticate($google);
	}
	if ($this->_identity->errorCode === UserIdentity::ERROR_NONE) {
	    $duration = $this->rememberMe ? 2592000 : 0; //60*60*24*30 = 30 days
	    Yii::app()->user->login($this->_identity, $duration);
	    return true;
	}
	else
	    return false;
    }

}
