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

Yii::import('application.components.TransformedFieldStorageBehavior');
Yii::import('application.components.util.EncryptUtil');

/**
 * Behavior class for storing encrypted values in database fields.
 * 
 * @package X2CRM.components
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
		if(!isset(self::$encryption) && $this->checkObject)
			throw new Exception('Cannot use '.__CLASS__.'; encryption utility object has not been instantiated.');
        parent::attach($owner);
	}
	/**
	 * Encrypts the attribute for database storage.
	 * @param string $name Attribute to be transformed
	 * @return string
	 */
	public function packAttribute($name){
		return self::$encrypt ? self::$encryption->encrypt($this->getOwner()->$name) : $this->getOwner()->$name;
	}

	/**
	 * Decrypts the attribute for setting/use in the interface.
	 * @param string $name Attribute to be transformed
	 * @return string
	 */
	public function unpackAttribute($name){
		if($this->getOwner()->$name)
			return self::$encrypt ? self::$encryption->decrypt($this->getOwner()->$name) : $this->getOwner()->$name;
		else
			return null;
	}
}

?>
