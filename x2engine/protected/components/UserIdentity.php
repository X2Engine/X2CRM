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
 * The data needed to identity a user.
 * 
 * It contains the authentication method that checks if the provided data can 
 * identity the user.
 * @package X2CRM.components
 */
class UserIdentity extends CUserIdentity {

	private $_id;
	private $_name;

	public function authenticate($google=false) {
		$user = X2Model::model('User')->findByAttributes(array('username' => $this->username));

		if(isset($user))
			$this->username = $user->username;
		if ($user === null || $user->status < 1) {				// username not found, or is disabled
			$this->errorCode = self::ERROR_USERNAME_INVALID;
		} elseif($google) {
			$this->errorCode = self::ERROR_NONE;
			$this->_id = $user->id;
			return !$this->errorCode;
		} else {
			$isMD5 = (strlen($user->password) == 32);
			if($isMD5)
				$isValid = ($user->password == md5($this->password));	// if 32 characters, it's an MD5 hash
			else
				$isValid = (crypt($this->password,'$5$rounds=32678$'.$user->password) == '$5$rounds=32678$'.$user->password);	// otherwise, 2^15 rounds of sha256
		
			if($isValid) {
				$this->errorCode = self::ERROR_NONE;
				$this->_id = $user->id;
				//$this->setState('lastLoginTime', $user->lastLoginTime); //not yet set up
				
				if(version_compare(PHP_VERSION,'5.3') >= 0) {	// regenerate a more secure hash and nonce
					$nonce = '';
					for($i = 0; $i<16; $i++)	// generate a random 16 character nonce with the Mersenne Twister
						$nonce .= substr('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789./', mt_rand(0, 63), 1); 

					$user->password = substr(crypt($this->password,'$5$rounds=32678$'.$nonce),16);
					$user->update(array('password'));
				}
				
			} else {
				$this->errorCode = self::ERROR_PASSWORD_INVALID;
			}
		}

		return !$this->errorCode;
	}
	
	public function getId() {
		return $this->_id;
	}
}
