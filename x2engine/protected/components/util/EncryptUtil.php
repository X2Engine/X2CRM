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
 * Standalone encryption utilities class that can retrieve necessary encryption
 * key/encoding from files.
 *
 * @package X2CRM.components.util
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class EncryptUtil {

	public static $generatedValues = array('IV','key');

	private $_IV;
	/**
	 * Encryption key
	 * @var mixed
	 */
	private $_key;

	/**
	 * Whether all the necessary dependencies are installed to use encryption.
	 * @var bool
	 */
	public $canEncrypt;

	/**
	 * File for storing IV length (for encoding purposes)
	 * @var type
	 */
	public $IVFile;

	/**
	 * A file for storing an encryption key
	 * @var string
	 */
	public $keyFile;

	/**
	 * Checks dependencies.
	 * @param type $throw Throw an exception if this is set to true and dependencies are missing.
	 * @throws Exception 
	 */
	public static function dependencyCheck($throw) {
		$hasDeps = extension_loaded('openssl') && extension_loaded('mcrypt');
		if(!$hasDeps && $throw)
			throw new Exception('The "openssl" and "mcrypt" extensions are not loaded. The EncryptUtil class cannot function properly.');
		return $hasDeps;
	}

	/**
	 * Generates a new encryption key
	 *
	 * @param integer $length
	 * @return string|bool
	 */
	public static function genKey($length = 32){
		$key = openssl_random_pseudo_bytes($length, $strong);
		return ($strong ? $key : false);
	}

	public static function genIV() {
		return mcrypt_create_iv(
                mcrypt_get_iv_size(
                    MCRYPT_RIJNDAEL_256,
                    MCRYPT_MODE_ECB
                ),
                MCRYPT_RAND
            );
	}

	public function __construct($keyFile=null,$IVFile=null,$throw=true) {
		$this->canEncrypt = self::dependencyCheck($throw);
		foreach(array('keyFile','IVFile') as $arg) {
			$this->$arg = ${$arg};
		}
	}

	/**
	 * Magic getter that obtains a value for an attribute from a file, or by
	 * generating new values.
	 *
	 * The assumption is made: if no storage files are specified, the instance
	 * creates new keys for a single usage without complaining, and does not
	 * store them. Otherwise, if files are specified but do not exist, a new
	 * encryption key is generated (to be stored when {@link saveNew()} is called).
	 *
	 * @return string
	 * @throws Exception
	 */
	public function __get($name){
		if(in_array($name,self::$generatedValues)) {
			$pp = "_$name"; // Private storage property
			$sf = $name.'File'; // File for storing the property
			$gf = 'gen'.ucfirst($name); // Function for generating the property
			if(!isset($this->$pp)){
				$set = false;
				if(isset($this->$sf)){
					$file = realpath($this->$sf);
					if($file){
						$this->$pp = file_get_contents($file);
						$set = true;
					}
				}
				// Must use "$set" because the file may in some cases be empty.
				if(!(isset($this->$pp)||$set))
					$this->$pp = call_user_func("self::$gf");
			}
			return $this->$pp;
		} else
			return $this->$name;
	}

	public function __set($name, $value){
		if(in_array($name,self::$generatedValues)) {
			$pp = "_$name";
			return $this->$pp = $value;
		} else
			return $this->$name = $value;
	}

	/**
	 * Encrypts data.
	 */
	public function encrypt($data){
		if($this->key)
			return base64_encode(rtrim(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->key, $data, MCRYPT_MODE_ECB, $this->IV),"\0"));
		else
			return $data;
	}

	/**
	 * Decrypts data.
	 */
	public function decrypt($data){
		if($this->key)
			return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256,$this->key, base64_decode($data), MCRYPT_MODE_ECB, $this->IV),"\0");
		else
			return $data;
	}

	/**
	 * Generates and saves an encryption key/IV length in files specified by
	 * {@link _keyFile} and {@link _IVFile}. Throws an exception if the key
	 * couldn't be made securely.
	 * 
	 * @param type $safe
	 * @return type
	 * @throws Exception
	 */
	public function saveNew($safe=true) {
		foreach(array('key', 'IV') as $attr){
			$sf = $attr.'File';
			if(!isset($this->$sf))
				throw new Exception("Cannot save $attr; path to $sf not set.");
			$dir = dirname($this->$sf);
			if(!realpath($dir))
				throw new Exception(ucfirst($attr)." file's containing directory at $dir not found.");
			file_put_contents($this->$sf, $this->$attr);
		}
		if($safe && !$this->key)
			throw new Exception('Strength of the encryption key could not be verified.');
		return $this->key;
	}
}

?>
