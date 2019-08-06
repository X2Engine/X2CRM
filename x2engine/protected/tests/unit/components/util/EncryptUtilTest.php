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




Yii::import('application.components.util.*');

/**
 * Test for the standalone encryption utilities class.
 *
 * @author Demitri Morgan <demitri@x2engine.com>
 * @package application.tests.unit.components.util
 */
class EncryptUtilTest extends FileOperTestCase {

	/**
	 * Random stuff that includes multibyte characters has been tossed into this
	 * string just to give the test a nice big set of characters to work with
	 * when verifying correctness.
	 * @var type
	 */
	public $junkToEncrypt = 'helloworldal;sЭти пользователи могут редактировать свои календаряdfjkasj8398c802930rlc851093261不支持电子邮件92356129056123906これらのメッセージの受信を停止するには、ここをクリックしてください51239512d9o8f983j23,.,אנא לחץ על כל המשתמשים החדשים שאתה רוצה להוסיף.';


	public function testEncryptDecrypt() {
		$enc = new EncryptUtil();
		$enc->key = EncryptUtil::genKey();
		$expectedValue = $this->junkToEncrypt;
		$encrypted = $enc->encrypt($expectedValue);
		$this->assertNotEquals($expectedValue,$encrypted,'Failed asserting data was encrypted.');
		$decrypted = $enc->decrypt($encrypted);
		$this->assertEquals($expectedValue,$decrypted,'Failed asserting that data was preserved in encryption/decryption.');
	}

	public function testFileSaving() {
		$this->setupTestDirs();
		$bd = $this->baseDir;
		$keyFile = "$bd/{$this->relFileList[0]}";
		$IVFile = "$bd/{$this->relFileList[1]}";
		// Remove them to test that they get properly created:
		unlink($keyFile);
		unlink($IVFile);
		$enc = new EncryptUtil($keyFile,$IVFile,true);
		// Generate/save new key & IV into files.
		$enc->saveNew();
		// Test that files got created:
		$this->assertFileExists($keyFile);
		$this->assertFileExists($IVFile);
		// Now encrypt a value with this instance:
		$expected = $this->junkToEncrypt;
		$encrypted = $enc->encrypt($expected);
		// ...Then try creating a new instance, and testing that the values
		// between instantiations are constistent
		$enc = new EncryptUtil($keyFile,$IVFile,true);
		$this->assertEquals($expected,$enc->decrypt($encrypted),'Failed asserting the encryption key and IV were properly saved and re-used.');
		$this->removeTestDirs();
	}

    public function testSecureUniqueIdHash64() {
        foreach(range(1,3) as $method) {
            foreach(range(1,2) as $hash) {
                $id = EncryptUtil::secureUniqueIdHash64($method,$hash);
                if(X2_TEST_DEBUG_LEVEL > 1) {
                    echo "\nmethod $method hash $hash $id";
                }
                $this->assertEquals(64,strlen($id));
            }
        }
    }

}

?>
