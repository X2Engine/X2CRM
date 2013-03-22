<?php

Yii::import('application.models.*');

/**
 * Class for database unit testing that performs additional preparation
 * 
 * @package X2CRM.tests
 */
class X2DbTestCase extends CDbTestCase {
	public function __construct($name = NULL, array $data = array(), $dataName = '') {
		Yii::app()->params->admin = CActiveRecord::model('Admin')->findByPk(1);
		parent::__construct($name,$data, $dataName);
	}
}

?>
