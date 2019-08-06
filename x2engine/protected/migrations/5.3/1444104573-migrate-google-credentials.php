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
 * Migrate google credentials from admin table to credentials table.
 */

/**
 * Copy of Encrypt util with an irrelevant method removed
 */
class EncryptUtilTmp {

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

$migrateGoogleCredentials = function () {

    // retrieve existing Google credentials
    $clientId = null;
    $clientSecret = null;
    $admin = Yii::app()->db->createCommand ("
        select * from x2_admin where id=1;
    ")->queryRow ();

    if (isset ($admin['googleClientId'])) {
        $clientId = $admin['googleClientId'];
        Yii::app()->db->createCommand ("
            alter table x2_admin 
            drop column googleClientId;
        ")->execute ();
    }
    if (isset ($admin['googleClientSecret'])) {
        $clientSecret = $admin['googleClientSecret'];
        Yii::app()->db->createCommand ("
            alter table x2_admin 
            drop column googleClientSecret;
        ")->execute ();
    }
    if (isset ($admin['googleAPIKey'])) {
        Yii::app()->db->createCommand ("
            alter table x2_admin 
            drop column googleAPIKey;
        ")->execute ();
    }

    // check if it's possible to encrypt the credentials 
    $key = implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'config','encryption.key'));
    $iv = implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'config','encryption.iv'));
    $encryption = new EncryptUtilTmp ($key, $iv, false);
    if (!$encryption->canEncrypt) {
        // server doesn't meet requirements. There's nothing that can be done. Credentials will
        // be lost and will need to be re-entered by admin user.
        return;
    }
    if (!file_exists ($key) || !file_exists ($iv)) {
        try {
            $encryption->saveNew();
        } catch (Exception $e) {
            // Encryption failed. There's nothing that can be done. Credentials will
            // be lost and will need to be re-entered by admin user.
            return;
        }
    }

    // manually insert encrypted credentials into credentials table
    $attributes = CJSON::encode (array (
        'clientId' => $clientId,
        'clientSecret' => $clientSecret,
    ));
    $encryptedAttributes = $encryption->encrypt ($attributes);
    $googleProject = array (
        'name' => 'Google project',
        'userId' => -1,
        'private' => 1,
        'isEncrypted' => 1,
        'modelClass' => 'GoogleProject',
        'createDate' => time (),
        'lastUpdated' => time (),
        'auth' => $encryptedAttributes,
    );
    if (Yii::app()->db->createCommand ()->insert ("x2_credentials", $googleProject)) {
        // update admin table foreign key
        $credId = Yii::app()->db->createCommand ("
            select id
            from x2_credentials
            where name='Google project'
        ")->queryScalar ();
        if ($credId !== false) {
            Yii::app()->db->createCommand ()->update ("x2_admin", array (
                'googleCredentialsId' => $credId,
            ), 'id=1');
        }
    }
};

$migrateGoogleCredentials ();


?>
