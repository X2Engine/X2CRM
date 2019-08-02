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
 * Validator class for enforcing password complexity
 *
 * @property integer $min The minimum number of characters
 * @property bool $requireMixedCase Whether to require mixed case alphabetic characters
 * @property bool $requireNumeric Whether to require numeric characters
 * @property bool $requireSpecial Whether to require special characters
 * @package application.components
 * @author Raymond Colebaugh <raymond@x2engine.com>
 */
class X2PasswordValidator extends CValidator {

    public $min = 0;
    public $requireMixedCase = false;
    public $requireNumeric = false;
    public $requireSpecial = false;
    public $requireCharClasses = 1;

    /**
     * Validates the attribute of the object.
     * If there is any error, the error message is added to the object.
     * @param CModel $object the object being validated
     * @param string $attribute the attribute being validated
     */
    protected function validateAttribute ($object, $attribute) {
        $val = $object->$attribute;

        // Validate password length
        if (strlen ($val) < $this->min) {
            $object->addError ($attribute, Yii::t ('app',
                '{attribute} is not secure enough (minimum length: {l})', array(
                    '{attribute}' => $attribute,
                    '{l}' => $this->min,
                )
            ));
        }

        // Validate mixed case requirement
        if ($this->requireMixedCase && !(preg_match ('/[A-Z]/', $val) && preg_match ('/[a-z]/', $val))) {
            $object->addError ($attribute, Yii::t ('app',
                '{attribute} must contain both upper and lower case characters', array(
                    '{attribute}' => $attribute,
                )
            ));
        }

        // Validate numeric requirement
        if ($this->requireNumeric && !preg_match ('/\d/', $val)) {
            $object->addError ($attribute, Yii::t ('app',
                '{attribute} must contain numbers', array(
                    '{attribute}' => $attribute,
                )
            ));
        }

        // Validate special character requirement
        if ($this->requireSpecial && !preg_match ('/_|[^\w\d]/', $val)) {
            $object->addError ($attribute, Yii::t ('app',
                '{attribute} must contain special characters', array(
                    '{attribute}' => $attribute,
                )
            ));
        }

        // Validate number of character classes
        $nClass = 0;
        foreach(array('[0-9]','[a-z]','[A-Z]','\W','\s') as $characterClass) {
            if(preg_match('/'.$characterClass.'/',$val)) {
                $nClass++;
            }
        }
        if ($nClass < $this->requireCharClasses) {
            $object->addError ($attribute, Yii::t ('app',
                '{attribute} must contain at least {n} types of characters', array(
                    '{attribute}' => $attribute,
                    '{n}' => $this->requireCharClasses
            )));
        }
    }
}
