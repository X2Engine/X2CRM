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
 * Password recovery active record model.
 *
 * @property boolean $limitReached Whether or not the requests per hour limit was reached
 * @package application.modules.users.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class PasswordReset extends CActiveRecord {

    /**
     * Password reset requests expire in one hour:
     */
    const EXPIRE_S = 3600;

    const MAX_REQUESTS = 5;

    private $_limitReached;
    private $_user;

    public function tableName() {
        return 'x2_password_reset';
    }

    public static function model($className = __CLASS__){
        return parent::model($className);
    }

    public function relations() {
        return array(
            'user' => array(self::BELONGS_TO,'User','userId')
        );
    }

    public function getIpAddr(){
        if(empty($this->ip)){
            $this->ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        }
        return $this->ip;
    }

    public function getIsExpired() {
        return $this->requested < time()-self::EXPIRE_S;
    }
    
    /**
     * Returns whether the maximum number of requests for the current IP address
     * has already been reached.
     * @return type
     */
    public function getLimitReached() {
        return $this->_limitReached = (((int)  self::model()->countByAttributes(array('ip'=>$this->ipAddr))) >= self::MAX_REQUESTS);
    }

    /**
     * Creates a password reset request.
     * 
     * Assigns a secure/unique ID to the request.
     * @param type $attributes
     */
    public function beforeSave(){
        // Clean out old requests:
        Yii::app()->db->createCommand('DELETE FROM `'.$this->tableName().'`'
                . ' WHERE requested < '.(time()-self::EXPIRE_S))
                ->execute();
        $user = $this->resolveUser();
        if($user instanceof User){
            $this->userId = $user->id;
        }
        return !$this->limitReached && parent::beforeSave();
    }

    public function insert($attributes = null){
        $this->id = EncryptUtil::secureUniqueIdHash64();
        $this->requested = time();
        $this->getIpAddr();
        return parent::insert($attributes);
    }

    public function rules() {
        return array(
            array('email','required'),
            array('email','email'),
            array('email','validUserId','on'=>'afterSave'),
        );
    }

    /**
     * Validator for checking if a user was found
     * @param type $attribute
     * @param type $params
     */
    public function validUserId($attribute,$params = array()) {
        if(empty($this->userId)) {
            $user = $this->resolveUser();
            if($user instanceof User)
                $this->userId = $user->id;
        }
        if(empty($this->userId)) {
            $this->addError('email',Yii::t('users','No user corresponding to that email address could be found.'));
        }
    }

    /**
     * Finds the user either by user or profile record (this is a sort of kludge
     * -y safeguard that can be removed when those tables are merged)
     * @return type
     */
    public function resolveUser() {
        $user = User::model()->findByAttributes(array('emailAddress' => $this->email));
        if(!($user instanceof User)){
            $profile = Profile::model()->findByAttributes(array('emailAddress' => $this->email));
            if($profile instanceof Profile) {
                $user = $profile->user;
            }
        }
        return $user;
    }
}

?>
