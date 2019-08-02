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
 * Miscellaneous additions to CValidator including option to have errors reported in the form 
 * of exceptions. Certain bad inputs will not occur during normal form submission and indicate
 * either a programming error a malicious request. In either of these cases it makes sense to 
 * throw an exception instead of adding errors to the model.
 */

abstract class X2Validator extends CValidator {

    /**
     * @var bool if true, instead of adding error messages to the model, exceptions will be thrown 
     *  with error message
     */
    public $throwExceptions = false;

    /**
     * @var CException type of exception that will get thrown if $throwExceptions is true
     */
    public $exceptionClass = 'CHttpException';

    /**
     * @var bool if true attribute will validate if it's empty 
     */
    public $allowEmpty = false;

    protected $object;

    protected $attribute;

    /**
     * A nicer-to-use version of CValidator's validateAttribute ()
     */
    abstract protected function validateValue (CModel $object, $value, $attribute);

    /**
     * Allows use of validateValue in place of CValidator validateAttribute. Also sets $object
     * and $attribute instance properties.
     */
    protected function validateAttribute ($object, $attribute) {
        $value = $object->$attribute;
        if ($this->allowEmpty && empty ($value)) return;
        $this->object = $object;
        $this->attribute = $attribute;
        return $this->validateValue ($object, $value, $attribute);
    } 

    /**
     * Adds error to model or if $throwExceptions is true, throws an exception.
     * @throws CException if validateAttribute () hasn't been called 
     */
    protected function error ($message) {
        if (!isset ($this->object) || !isset ($this->attribute)) {
            throw new CException (
                'Precondition violated: validateAttribute must be called before this method');
        }
        if ($this->throwExceptions) {
            if ($this->exceptionClass === 'CHttpException') {
                throw new $this->exceptionClass (400, $message);
            } else {
                throw new $this->exceptionClass ($message);
            }
        } else {
            $this->addError ($this->object, $this->attribute, $message);
        }
    }
}
