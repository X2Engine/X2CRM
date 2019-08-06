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




Yii::import('application.models.embedded.*');

/**
 * Test case for JSONEmbeddedModelFieldsBehaviorTest
 * @package application.tests.unit.components
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
