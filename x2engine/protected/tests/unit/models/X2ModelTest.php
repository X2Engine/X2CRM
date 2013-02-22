<?php

class X2ModelTest extends X2DbTestCase {
	
//	public $fixtures = array(
//		'opportunities' => 'Opportunity'
//	);
	
	public function testStrToNumeric() {
		
		$cur =  Yii::app()->locale->getCurrencySymbol(Yii::app()->params->admin->currency);
		$input = " $cur 123.45 % ";
		$this->assertEquals(123.45,X2Model::strToNumeric($input,'currency'));
		$this->assertEquals(123,X2Model::strToNumeric($input,'int'));
		$this->assertEquals(123.45,X2Model::strToNumeric($input,'float'));
		$this->assertEquals(123.45,X2Model::strToNumeric($input,'percentage'));
		$this->assertEquals(0,X2Model::strToNumeric(null,'float'));
		try {
			$input = 'cockadoodledoo';
			$value = X2Model::strToNumeric($input,'int');
			$this->assertTrue(false);
		} catch (CException $e) {
			$this->assertEquals('Invalid number format for int: "cockadoodledoo"',$e->getMessage());
		}
		try {
			$type  = 'notanint';
			$value = X2Model::strToNumeric($input,'notanint');
			$this->assertTrue(false);
		} catch (CException $e) {
			$this->assertEquals('Invalid numeric type "notanint"',$e->getMessage());
		}
		
	}
}

?>
