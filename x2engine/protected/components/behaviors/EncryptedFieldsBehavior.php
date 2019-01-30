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




Yii::import('application.components.behaviors.TransformedFieldStorageBehavior');
Yii::import('application.components.util.EncryptUtil');

/**
 * Behavior class for storing encrypted values in database fields.
 * 
 * @package application.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class EncryptedFieldsBehavior extends TransformedFieldStorageBehavior {

	/**
	 * If true, the stored value will be encrypted.
	 * @var bool
	 */
	protected static $encrypt = true;

	/**
	 * Encryption utility object
	 * @var EncryptUtil
	 */
	public static $encryption;

	/**
	 * If true, throws an exception if no object has been instantiated.
	 *
	 * This is to prevent generating a new key for every new usage (which would
	 * render useless any and all encrypted data; it could not be decrypted if
	 * that were the case).
	 * @var bool
	 */
	public $checkObject = true;

	/**
	 * Creates a new encryption utility object for use with this behavior.
	 * @param type $keyFile
	 * @param type $IVFile
	 */
	public static function setup($keyFile,$IVFile) {
		self::$encryption = new EncryptUtil($keyFile,$IVFile);
		if(!file_exists($keyFile)) {
			self::$encryption->saveNew(false);
		}
		self::$encrypt = true;
	}

	public static function setupUnsafe() {
		self::$encrypt = false;
	}

	/**
	 * Checks for whether a working encryption object is available before attaching.
	 * @throws Exception 
	 */
    public function attach($owner){
		if(!isset(self::$encryption) && $this->checkObject) {
			throw new Exception(
                'Cannot use '.__CLASS__.'; encryption utility object has not been instantiated.');
        }
        parent::attach($owner);
	}
	/**
	 * Encrypts the attribute for database storage.
	 * @param string $name Attribute to be transformed
	 * @return string
	 */
	public function packAttribute($name){
		return self::$encrypt ? 
            self::$encryption->encrypt($this->getOwner()->$name) : $this->getOwner()->$name;
	}

	/**
	 * Decrypts the attribute for setting/use in the interface.
	 * @param string $name Attribute to be transformed
	 * @return string
	 */
	public function unpackAttribute($name){
		if($this->getOwner()->$name) {
			return self::$encrypt ? 
                self::$encryption->decrypt($this->getOwner()->$name) : $this->getOwner()->$name;
		} else {
			return null;
        }
	}
}

?>
