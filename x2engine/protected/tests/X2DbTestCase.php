<?php

Yii::import('application.models.*');

/**
 * Prepares the application singleton for database testing.
 *
 * @author Demitri Morgan
 */
class X2DbTestCase extends CDbTestCase {
	public function __construct($name = NULL, array $data = array(), $dataName = '') {
		Yii::app()->params->admin = CActiveRecord::model('Admin')->findByPk(1);
		parent::__construct($name,$data, $dataName);
	}
}

?>
