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

Yii::import('application.components.util.*');

/**
 * 
 * @package X2CRM.tests.unit.components.util
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class ArrayUtilTest extends CTestCase {

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

}

?>
