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
 * Class intended for auto-import of {@link CActiveMock}
 * @package X2CRM.tests
 */
class CActiveRecordBehaviorTestCase extends CTestCase {

	public static function setUpBeforeClass() {
		Yii::app()->db->createCommand('DROP TABLE IF EXISTS`'.CActiveMock::MOCK_TABLE)->execute();
		Yii::app()->db->createCommand('CREATE TABLE `'.CActiveMock::MOCK_TABLE.'` (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, foo BLOB,bar TEXT, flag TINYINT NOT NULL DEFAULT 0)')->execute();
		parent::setUpBeforeClass();
	}

	public static function tearDownAfterClass(){
		Yii::app()->db->createCommand('DROP TABLE IF EXISTS`'.CActiveMock::MOCK_TABLE)->execute();
		parent::tearDownAfterClass();
	}
}

/**
 * Child class of CActiveRecord for doing mocks in tests of CActiveRecordBehavior
 * @package X2CRM.tests
 */
class CActiveMock extends CActiveRecord {

	const MOCK_TABLE = 'x2_mock_model_table';

	public function rules() {
		return array(
			array('foo,bar,flag','safe')
		);
	}

	public function tableName() {
		return self::MOCK_TABLE;
	}
}

?>
