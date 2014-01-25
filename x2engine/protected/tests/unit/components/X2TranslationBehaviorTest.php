<?php

/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
Yii::import('application.components.*');

/**
 * Test for translations auto-parse

 *
 * @package X2CRM.tests.unit.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class X2TranslationBehaviorTest extends X2TestCase {

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
		$this->assertLessThan(5, $t1-$t0);

	}

}

?>
