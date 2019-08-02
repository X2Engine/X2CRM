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
 * The data needed to identity a user.
 * 
 * It contains the authentication method that checks if the provided data can 
 * identity the user.
 * @package application.components
 */
class UserIdentity extends CUserIdentity {

    const ERROR_DISABLED = 3;

    private $_id;
    private $_name;
    private $_userModel;

    public function authenticate($google = false) {
        $user = $this->getUserModel();
        $isRealUser = $user instanceof User;

        if ($isRealUser) {
            $this->username = $user->username;
            if ((integer) $user->status === User::STATUS_INACTIVE) {
                $this->errorCode = self::ERROR_DISABLED;
                return false;
            }
        }

        if (!$isRealUser) { // username not found
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        } elseif ($google) { // Completely bypasses password-based authentication
            $this->errorCode = self::ERROR_NONE;
            $this->_id = $user->id;
            return true;
        } else {
            if ($user->status == 0) {
                // User has been disabled
                $this->errorCode = self::ERROR_DISABLED;
                return false;
            }
            $reEncrypt = false;
            $isValid = false;
            if(PasswordUtil::validatePassword($this->password, $user->password)){
                $isValid = true;
            } else if (PasswordUtil::slowEquals(md5($this->password), $user->password)){
                //Oldest format
                $isValid = true;
                $reEncrypt = true;
            } else if (PasswordUtil::slowEquals(crypt($this->password, '$5$rounds=32678$' . $user->password), '$5$rounds=32678$' . $user->password)){
                //Old format
                $isValid = true;
                $reEncrypt = true;
            }
            if($isValid){
                $this->errorCode = self::ERROR_NONE;
                $this->_id = $user->id;
                if ($reEncrypt) {
                    $user->password = PasswordUtil::createHash($this->password);
                    $user->update(array('password'));
                }
            }else{
                $this->errorCode = self::ERROR_PASSWORD_INVALID;
            }
        }

        return $this->errorCode === self::ERROR_NONE;
    }

    public function getId() {
        return $this->_id;
    }

    public function getUserModel() {
        if (!isset($this->_userModel)) {
            $this->_userModel = User::model()->findByAlias($this->username);
        }
        return $this->_userModel;
    }
    
}
