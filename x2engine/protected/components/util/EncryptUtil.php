<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

/**
 * Standalone encryption utilities class.
 *
 * @package X2CRM.components.util
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class EncryptUtil {

	/**
	 * Whether all the necessary dependencies are installed to use encryption.
	 * @var bool
	 */
	public $canEncrypt;

	public static $generatedValues = array('IV','key');

	private $_IV;
	/**
	 * Encryption key
	 * @var mixed
	 */
	private $_key;

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
					} else {
						throw new Exception(ucfirst($name).' file not found.');
					}
				}
				if(!(isset($this->$pp)||$set)) // must use "$set" because the file may in some cases be empty
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
			return rtrim(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->key, $data, MCRYPT_MODE_ECB, $this->IV),"\0");
		else
			return $data;
	}

	/**
	 * Decrypts data.
	 */
	public function decrypt($data){
		if($this->key)
			return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256,$this->key, $data, MCRYPT_MODE_ECB, $this->IV),"\0");
		else
			return $data;
	}

	/**
	 * Generates and saves an encryption key/IV length in files specified by
	 * {@link _keyFile} and {@link _IVFile}
	 * 
	 * @param type $safe
	 * @return type
	 * @throws Exception
	 */
	public function saveNew($safe=true) {
		foreach(array('key', 'IV') as $att){
			if(!isset($this->_keyFile))
				throw new Exception('Cannot save key; path to key file not set.');
			$file = realpath($this->keyFile);
			if(!$file)
				throw new Exception("Specified key file at {$this->keyFile} not found.");
			if($safe && !$this->getKey(true))
				throw new Exception('Strength of the encryption key could not be verified.');
			file_put_contents($file, $this->key);
			return $this->key;
		}
	}
}

?>
