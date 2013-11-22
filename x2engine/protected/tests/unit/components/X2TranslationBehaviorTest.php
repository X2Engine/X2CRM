<?php

/* * *******************************************************************************
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
 * ****************************************************************************** */
Yii::import('application.components.*');

/**
 * Test for translations auto-parse

 *
 * @package X2CRM.tests.unit.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class X2TranslationBehaviorTest extends CTestCase {

	public function instantiate(){
		$component = new CComponent();
		$component->attachBehavior('messageParser',array('class'=>'X2TranslationBehavior'));
		return $component;
	}

	/**
	 * Cursory test of regex
	 */
	public function testREGEX() {
		preg_match(X2TranslationBehavior::REGEX,"Yii::t(  'app' ,     'Everything\\'s \"fine\".'   );",$matches);
		$this->assertEquals('app',$matches['module']);
		$this->assertEquals('Everything\\\'s "fine".',$matches['message']);
		$this->assertEquals('',$matches['openquote1']);
		$this->assertEquals('',$matches['openquote2']);
		preg_match(X2TranslationBehavior::REGEX,'Yii::t(  "app" ,     "Everything\'s \"fine\"."   );',$matches);
		$this->assertEquals('"',$matches['openquote1']);
		$this->assertEquals('"',$matches['openquote2']);
		$this->assertEquals('app',$matches['module']);
		$this->assertEquals('Everything\'s \"fine\".',$matches['message']);
	}

	public function testParseFile() {
		$cmpb = $this->instantiate();
		$msgFile = Yii::app()->basePath.'/tests/data/messageparser/modules1.php';
		// Note that the assertions in this method must correspond to the above file
		$messages = $cmpb->parseFile($msgFile);
		$expected = array(
			'app' =>
				array(
					'This and that thing\\\'s thing' => '',
					'multiple' => '',
					'messages' => '',
					'on' => '',
					'the' => '',
					'same' => '',
					'line' => '',
					'message with params and {stuff}' => '',
					'Manage Apps' => ''
				),
			'admin' =>
				array(
					'Messages (with parentheses)' => '',
					'This and that \"thing\"' => '',
					'Define how the system sends email by default.' => '',
					'Note that this will not supersede other email settings. Usage of these particular settings is a legacy feature. Unless this web server also serves as your company\\\'s primary mail server, it is recommended to instead use "{ma}" to set up email accounts for system usage instead.' => '',
					'Configure how X2CRM sends email when responding to new service case requests.' => '',
				),
			'profile' => array(
				'Manage Passwords for Third-Party Applications' => '',
			),
			'users' =>
				array(
					"multiline\nmessage" => '',
			),
			'install' =>
			array(
				'installer message' => '',
				'installer message with {p}' => '',
				'Weekdays' => '',
			),
		);
		$this->assertEquals($expected,$messages);
	}

	public function testFileList(){
		$cmpb = $this->instantiate();
		$t0 = time();
		$fl = $cmpb->fileList();
		$t1 = time();
		// Should not take super-long
		$this->assertLessThan(2, $t1-$t0);

	}

}

?>
