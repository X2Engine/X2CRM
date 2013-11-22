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

Yii::import('application.models.embedded.*');

/**
 * Test case for JSONEmbeddedModelFieldsBehaviorTest
 * @package X2CRM.tests.unit.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class JSONEmbeddedModelFieldsBehaviorTest extends CActiveRecordBehaviorTestCase {

	public static $iv;
	public static $key;

	/**
	 * Override that uses a specific key/iv for unit testing
	 */
	public static function setUpBeforeClass(){
		parent::setUpBeforeClass();
		EncryptedFieldsBehavior::setupUnsafe();
	}
	
	public function newModelAndBehavior($config = array()) {
		$model = new CActiveMock;
		$model->bar = 'mock';
        $model->attachBehaviors(array(
            'JSONEmbeddedModelFieldsBehavior' => array_merge(array(
                'class' => 'JSONEmbeddedModelFieldsBehavior',
                'templateAttr' => 'bar',
                'transformAttributes' => array('foo'),
                'encryptedFlagAttr' => 'flag'
            ),$config)));
        $jemfb = $model->asa('JSONEmbeddedModelFieldsBehavior');
		return array($model,$jemfb);
	}

	public function assertAttributeModelInstantiated($model,$context='') {
		$this->assertTrue($model->foo instanceof JSONEmbeddedModel,'Failed asserting instantiation of new model object for designated attribute. '.$context);
	}

	/**
	 * Test instantiation/pack
	 */
	public function testPackAttribute_new() {
		$blankModel = new EmbeddedModelMock();
		$expectedDefaults = $blankModel->attributes;
		$expected = array('embFoo'=>1,'embBar'=>4);
		$instFail = 'Failed asserting instantiation of new model object for designated attribute.';
		$encFail = 'Failed asserting a valid JSON was created for storage.';

		// Test sending nothing (no key=>value pair in attribute array with which
		// to set the embedded model's attributes). The expected behavior is a
		// model with defaults.
		list($model,$jemfb) = $this->newModelAndBehavior();
		$model->attributes = array('bar'=>'EmbeddedModelMock');
		$model->raiseEvent('onBeforeSave',new CModelEvent($model));
		$embAttr = CJSON::decode($model->foo);
		$this->assertTrue(is_array($embAttr),$encFail);
		$this->assertEquals($expectedDefaults,$embAttr,$instFail.' When: value not specified in setAttributes.');

		// Sent null? Same thing (model with defaults)
		list($model,$jemfb) = $this->newModelAndBehavior();
		$model->attributes = array('bar'=>'EmbeddedModelMock','foo'=>null);
		$model->raiseEvent('onBeforeSave',new CModelEvent($model));
		$embAttr = CJSON::decode($model->foo);
		$this->assertTrue(is_array($embAttr),$encFail);
		$this->assertEquals($expectedDefaults,$embAttr,$instFail.' When: invalid/null value specified in setAttributes.');

		// Sent an array to setAttributes? In this case, it should set the values
		// specified and let the rest use their default vaules.
		list($model,$jemfb) = $this->newModelAndBehavior();
		$model->attributes = array('bar'=>'EmbeddedModelMock','foo'=>array('embBar'=>4));
		$model->raiseEvent('onBeforeSave',new CModelEvent($model));
		$embAttr = CJSON::decode($model->foo);
		$this->assertTrue(is_array($embAttr),$encFail);
		$this->assertEquals($expected,$embAttr,'Failed asserting attributes of embedded model were set properly.');
	}

	/**
	 * Test instantiation with fixed model fields
	 */
	public function testFixedModelFields() {
		list($model,$jemfb) = $this->newModelAndBehavior(array(
            'fixedModelFields' => array('foo'=>'EmbeddedModelMock'),
            'templateAttr' => null,
        ));
        $model->instantiateField('foo');
        $this->assertTrue($model->foo instanceof EmbeddedModelMock,'Model improperly instantiated/selected');
	}

    /**
     * Test instantiation with embedded model class fields defined in array fashion
     */
    public function testTemplateAttrArray() {
        list($model,$jemfb) = $this->newModelAndBehavior(array(
            'templateAttr' => array('foo'=>'bar'),
        ));
        $model->bar = 'EmbeddedModelMock';
        $model->instantiateField('foo');
        $this->assertTrue($model->foo instanceof EmbeddedModelMock,'Model improperly instantiated/selected');
        
    }

	public function testUnpackAttribute() {
		list($model,$jemfb) = $this->newModelAndBehavior();
		$model->attributes = array('bar'=>'EmbeddedModelMock','foo'=>array('embBar'=>4));
		$model->raiseEvent('onBeforeSave',new CModelEvent($model));
		$model->raiseEvent('onAfterSave',new CModelEvent($model));
		$this->assertAttributeModelInstantiated($model,'After unpack.');
		$this->assertEquals(array('embFoo'=>1,'embBar'=>4),$model->foo->attributes, 'Failed asserting attributes of the embedded model were preserved.');
	}

	/**
	 * Tests setting attributes and automagically populating the embedded model
	 * in validation
	 */
	public function testValidation() {
		list($model,$jemfb) = $this->newModelAndBehavior();
		$expected = array('embFoo'=>1,'embBar'=>10);
		$model->attributes = array('bar'=>'EmbeddedModelMock','foo'=>array('embBar'=>10));
		$model->validate();
		$this->assertAttributeModelInstantiated($model,'After validation.');
		$this->assertTrue($model->hasErrors(), 'Failed asserting that embedded model errors caused an error to be appended to the containing model.');
		// As an added bonus: check that form input field name gets properly resolved:
		$this->assertEquals('CActiveMock[foo][embBar]',$model->foo->resolveName('embBar'));
	}

}

class EmbeddedModelMock extends JSONEmbeddedModel {

	public $embFoo = 1;
	public $embBar = 2;

	protected $_attributeLabels = array(
		'embFoo' => 'Embedded foo',
		'embBar' => 'Embedded bar'
	);

	public function modelLabel() {
		return 'Mock Embedded Model';
	}

	public function rules() {
		return array(
			array('embBar','numerical','max'=>9),
			array('embFoo,embBar','safe')
		);
	}
	public function attributeNames() {
		return array('embFoo','embBar');
	}

	public function renderInputs() {
	}

	public function detailView() {
	}
}

?>
