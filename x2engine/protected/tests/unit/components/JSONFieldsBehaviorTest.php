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

Yii::import('application.models.*');
Yii::import('application.components.*');
Yii::import('application.components.util.*');

/**
 * Test for JSONFieldsBehavior.
 *
 * @package X2CRM.tests.unit.components
 * @author Demitri Morgan <demitri@x2engine.com>, Derek Mueller <derek@x2engine.com>
 */
class JSONFieldsBehaviorTest extends CActiveRecordBehaviorTestCase {

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


	public function testPackAttribute() {
		$model = new CActiveMock();
		$model->foo = $this->arrayOld;
		$jfb = new JSONFieldsBehavior();
		$jfb->transformAttributes = array('foo' => array_keys($this->arrayTemplate));
		$jfb->attach($model);
		$template = array_fill_keys(array_keys($this->arrayTemplate),null);
		$expected = ArrayUtil::normalizeToArray($template,$this->arrayOld);
		$model->raiseEvent('onBeforeSave',new CModelEvent($model));
		$this->assertEquals($expected,CJSON::decode($model->foo));
	}

	public function testUnpackAttribute() {
		$model = new CActiveMock();
		$model->foo = CJSON::encode($this->arrayOld);
		$jfb = new JSONFieldsBehavior();
		$jfb->transformAttributes = array('foo' => array_keys($this->arrayTemplate));
		$jfb->attach($model);
		$template = array_fill_keys(array_keys($this->arrayTemplate),null);
		$expected = ArrayUtil::normalizeToArray($template,$this->arrayOld);
		$model->raiseEvent('onAfterSave',new CModelEvent($model));
		$this->assertEquals($expected,$model->foo);
	}
}

?>
