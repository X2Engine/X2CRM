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
 * The "Enter a New Password" form model for password resetting.
 *
 * @package application.modules.users.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class PasswordResetForm extends CFormModel {

    const N_CHAR_CLASS_SECURE = 2;
    const N_CHAR_TOTAL_SECURE = 5;

    public $password;
    public $confirm;

    /**
     * User active record to be updated
     * 
     * @var User
     */
    public $userModel;

    public function attributeLabels(){
        return array(
            'password' => Yii::t('users','Password'),
            'confirm' => Yii::t('users','Confirm Password'),
        );
    }

    public function attributeNames(){
        return array('password','confirm');
    }

    public function rules() {
        $passwordResetRules = array(
            array('password,confirm','required'),
            array('password','securePassword'),
            array('confirm','compare','compareAttribute'=>'password','message'=>Yii::t('users','Passwords do not match.')),
        );
        

        $passwordRule = array('password', 'application.components.X2PasswordValidator');
        $passwordRequirements = Yii::app()->settings->passwordRequirements;
        $passwordRule['min'] = $passwordRequirements['minLength'];
        $passwordRule['requireNumeric'] = $passwordRequirements['requireNumeric'];
        $passwordRule['requireMixedCase'] = $passwordRequirements['requireMixedCase'];
        $passwordRule['requireSpecial'] = $passwordRequirements['requireSpecial'];
        $passwordRule['requireCharClasses'] = $passwordRequirements['requireCharClasses'];
        // Replace securePassword validator above with platinum X2PasswordValidator
        $passwordResetRules[1] = $passwordRule;

        
        return $passwordResetRules;
    }

    public function __construct(User $userModel,$scenario = ''){
        $this->userModel = $userModel;
        parent::__construct($scenario);
    }

    /**
     * Save the associated user model
     *
     * Also, this clears out all password resets associated with the given user,
     * if successful.
     * @return type
     */
    public function save() {
        if($this->validate()) {
            $this->userModel->password = PasswordUtil::createHash($this->password);
            PasswordReset::model()->deleteAllByAttributes(array('userId'=>$this->userModel->id));
            return $this->userModel->update(array('password'));
        }
        return false;
    }

    /**
     * Validation rule that prompts user for a more secure password
     *
     * @param type $attribute
     * @param type $params
     */
    public function securePassword($attribute,$params=array()) {
        $nClass = 0;
        if(strlen($this->$attribute) < self::N_CHAR_TOTAL_SECURE) {
            $this->addError($attribute,Yii::t('users','{attribute} is not secure enough (minimum length: {l})', array(
                        '{attribute}' => $this->getAttributeLabel($attribute),
                        '{l}' => self::N_CHAR_TOTAL_SECURE
            )));
        }
        foreach(array('[0-9]','[a-z]','[A-Z]','\W','\s') as $characterClass) {
            if(preg_match('/'.$characterClass.'/',$this->$attribute)) {
                $nClass++;
            }
        }
        if($nClass < self::N_CHAR_CLASS_SECURE){
            $this->addError($attribute, Yii::t('users', '{attribute} is not secure enough; it must contain at least {n} types of characters (upper case, lower case, number, etc)', array(
                        '{attribute}' => $this->getAttributeLabel($attribute),
                        '{n}' => self::N_CHAR_CLASS_SECURE
            )));
        }
    }
}

?>
