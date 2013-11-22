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

Yii::import('application.components.util.*');

/**
 * Test for the standalone encryption utilities class.
 *
 * @author Demitri Morgan <demitri@x2engine.com>
 * @package X2CRM.tests.unit.components.util
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

}

?>
