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
 * 
 * @package application.tests.unit.components.util
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class ArrayUtilTest extends X2TestCase {

	public $arrayTemplate = array(
		'this' => null,
		'that' => 1,
		'theNextThing' => 'hello',
	);

	public $arrayOld = array(
		'this' => 'here I am',
		'that' => 0,
		'deleteMe' => 'nnnyeh.'
	);

	public function testNormalizeToArray() {
		$normalized = ArrayUtil::normalizeToArray($this->arrayTemplate,$this->arrayOld);
		$tmplKeys = array_keys($this->arrayTemplate);
		$oldKeys = array_keys($this->arrayOld);
		$keepKeys = array_intersect($tmplKeys,$oldKeys);
		$newKeys = array_diff($tmplKeys,$oldKeys);
		$deleteKeys = array_diff($oldKeys,$tmplKeys);
		// All keys in the template must be present:
		foreach($tmplKeys as $key)
			$this->assertArrayHasKey($key,$normalized,"Array lost a key!");
		// All fields not specified must be removed:
		foreach($deleteKeys as $key)
			$this->assertArrayNotHasKey($key, $normalized);
		// All new fields must inherit specified default values:
		foreach($newKeys as $key)
			$this->assertEquals($this->arrayTemplate[$key],$normalized[$key],"Didn't inherit default value!");
		// All fields must retain their original values, if they didn't need to
		// be initialized with default values:
		foreach($keepKeys as $key)
			$this->assertEquals($this->arrayOld[$key],$normalized[$key],"Array value changed when it shouldn't have!");
	}

    public function testArraySearchPreg () {
        $arr = array (
            'a' => 'one',
            'b' => 'two',
        );
        $matches = ArrayUtil::arraySearchPreg ('o|t', $arr);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($matches);
        $this->assertContains ('a', $matches);
        $this->assertContains ('b', $matches);
        $arr = array (
            'a' => 'one',
            'b' => 'two',
            array (
                'c' =>'three',
            )
        );
        $matches = ArrayUtil::arraySearchPreg ('o|t', $arr);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($matches);
        $this->assertContains ('a', $matches);
        $this->assertContains ('b', $matches);
        $this->assertContains ('c', $matches);
    }

}

?>
