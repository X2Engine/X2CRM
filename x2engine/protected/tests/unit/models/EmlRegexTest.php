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






Yii::import('application.models.*');
Yii::import('application.components.EmlParse');
Yii::import('application.components.util.FileUtil');

/**
 * Tests to run on the EmlRegex class. No fixtures are needed and the data is
 * pretty much static, so it needn't be a child class of CDbTestCase
 * 
 * @package application.tests.unit.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class EmlRegexTest extends X2TestCase {

	public $ignoreFilesMatching = '/(.*_.*|^\.[a-z]{3})$/';
	
	/**
	 * Test regex for each email
	 */
	public function testHeadRE() {
		// Obtain the test emails and parse them
		$patterns = X2Model::model('EmlRegex')->findAll();
		$ignoreFilesMatching = $this->ignoreFilesMatching;
		$emlFiles = array_filter(scandir(Yii::app()->basePath . FileUtil::rpath('/tests/data/email/')), function($e) use($ignoreFilesMatching) {
					return !in_array($e, array('..', '.'))&& !preg_match($ignoreFilesMatching,$e);
				});
		$emls = array();
		foreach ($emlFiles as $emlFile) {
			$rawEmail = file_get_contents(Yii::app()->basePath . FileUtil::rpath('/tests/data/email/') . $emlFile);
			$emls[$emlFile] = new EmlParse($rawEmail);
		}
		// Test each original email's raw content for the pattern. At least one of them should match.
		foreach ($emls as $emlFile => $eml) {
			if(X2_TEST_DEBUG_LEVEL > 1) echo "Using content from raw email file $emlFile:\n";
			$matched = false;
			foreach ($patterns as $pattern) {
				if(X2_TEST_DEBUG_LEVEL > 1) echo "Testing with pattern \"{$pattern->groupName}\"\n"; // : {$pattern->fwHeader}
				if ($matches = $pattern->matchHeader($eml->getBody())) {
					$matched = true;
                    if(X2_TEST_DEBUG_LEVEL > 1) echo "Forwarded header pattern {$pattern->groupName} matched.\n";
				}
			}
			if (!$matched) {
				if(X2_TEST_DEBUG_LEVEL > 1) echo "\n-----\nThe body that failed to match was:\n-----\n";
				X2_TEST_DEBUG_LEVEL > 1 && print_r($eml->getBody())."\n";
			}
			// At least one of the email patterns should match. If not, the test
			// should fail.
			$this->assertTrue($matched);
		}
	}

	/**
	 * Test title stripping down to "Developer Person" full name
	 */
	public function testFullName() {
		$correctName = array('Developer', 'Person');
		// Put any silly combination of titles (prefix/suffix) in here:
		$namesWithTitles = array(
			'Mr. Developer Person',
			'Mrs. Developer Person',
			'Developer Person, PhD',
			'Ambassador Developer Person',
			'Developer Person DDS',
			'Coach Developer Person',
			'Person, Developer'
		);
		foreach ($namesWithTitles as $name) {
			$this->assertEquals($correctName,EmlRegex::fullName($name));
		}
	}
}

?>
