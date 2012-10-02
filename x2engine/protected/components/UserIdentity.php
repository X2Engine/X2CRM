<?php
/*********************************************************************************
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
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
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
		$user = CActiveRecord::model('User')->findByAttributes(array('username' => $this->username));

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
				
				if($isMD5 && version_compare(phpversion(),'5.3') == 1) {	// regenerate a more secure hash and nonce
					$nonce = '';
					for($i = 0; $i<16; $i++)	// generate a random 16 character nonce with the Mersenne Twister
						$nonce .= substr('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789./', mt_rand(0, 63), 1); 

					$user->password = substr(crypt($this->password,'$5$rounds=32678$'.$nonce),16);
					$user->save();
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











