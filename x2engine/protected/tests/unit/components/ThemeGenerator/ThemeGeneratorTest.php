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
 * @package application.tests.unit.components
 */
class ThemeGeneratorTest extends X2DbTestCase {

	public $fixtures = array(
        'profiles' => array ('Profile', '.ThemeGeneratorTest'),
	);

	public $emptyColors = array (
        'background' => '',
        'text' => '',
        'link' => '',
        'content' => '',
        'highlight1' => '',
        'highlight2' => '000',
    );			

	public $normalColors= array(
        'background' => 'FF0000',
        'text' => '#000000',
        'link' => '#0000FF',
        'content' => '00AA00',
        'highlight1' => '000000',
        'highlight2' => '000000',
    );	

	public function testGeneratePalette($colors=null){

		// Test for malformed array input
		if (!$colors) {
			$colors = $this->normalColors;	
		}

		$generated = ThemeGenerator::generatePalette($colors);
		X2_TEST_DEBUG_LEVEL > 1 && print_r($generated);

		$keys = ThemeGenerator::getProfileKeys();
		$this->assertTrue(in_array('themeName', $keys));
		X2_TEST_DEBUG_LEVEL > 1 && print_r($keys);

		foreach($keys as $key) {
			if ($key == 'themeName') {
				continue;
			}

			$this->assertArrayHasKey($key, $generated);
		}

		$this->assertCount(count($keys)-1, $generated);
	}

	public function testEmptyColors() {
		$this->testGeneratePalette($this->emptyColors);
	}

    public function testGeneratePalette2 () {
        $profileDefaultThemeLegacy = $this->profiles ('adminProfile');
        $profileNoTheme = $this->profiles ('testProfile');
        $profileSelectedThemeLegacy = $this->profiles ('testProfile2');

        // ensure that keys get normalized
        $this->assertTrue (
            array_keys (
                ThemeGenerator::generatePalette ($profileDefaultThemeLegacy->theme, true)) ===
            array_keys (ThemeGenerator::generatePalette ($profileNoTheme->theme, true)) &&
            array_keys (ThemeGenerator::generatePalette ($profileNoTheme->theme, true)) ===
            array_keys (
                ThemeGenerator::generatePalette ($profileSelectedThemeLegacy->theme, true)));
    }

}

?>
