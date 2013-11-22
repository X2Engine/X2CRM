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

/**
 * This is the model class for table "x2_workflow_stages".
 *
 * @package X2CRM.modules.workflow.models
 * @property integer $id
 * @property integer $workflowId
 * @property integer $stageNumber
 * @property string $name
 * @property string $description
 * @property float $conversionRate
 * @property float $value
 * @property integer $requirePrevious
 * @property integer $requireComment
 */
class WorkflowStage extends CActiveRecord {

	public $_roles = array();
	/**
	 * Returns the static model of the specified AR class.
	 * @return WorkflowStage the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_workflow_stages';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('workflowId, stageNumber, requirePrevious', 'numerical', 'integerOnly'=>true),
			array('requireComment', 'boolean'),
			array('conversionRate, value', 'type', 'type'=>'float'),
			array('conversionRate', 'numerical', 'max'=>100, 'min'=>0),
			array('name', 'length', 'max'=>40),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, workflowId, name, description, conversionRate, value', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		return array(
			'workflow'=>array(self::BELONGS_TO, 'Workflow', 'workflowId'),
			// 'roles'=>array(self::MANY_MANY, 'Roles','x2_role_to_workflow(stageId, roleId)'),
		);
	}

	public function getRoles() {
		if(empty($this->_roles) && !empty($this->id))
			$this->_roles = Yii::app()->db->createCommand()->select('roleId')->from('x2_role_to_workflow')->where('stageId='.$this->id)->queryColumn();
		return $this->_roles;
	}
	
	public function setRoles($roles) {
		if(is_array($roles) || !empty($roles))
			$this->_roles = $roles;
	}
	
	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => 'ID',
			'workflowId' => Yii::t('workflow','Workflow'),
			// 'stageNumber' => Yii::t('workflow','Stage Number'),
			'name' => Yii::t('workflow','Stage Name'),
			'description' => Yii::t('workflow','Description'),
			'conversionRate' => Yii::t('workflow','Conversion Rate'),
			'value' => Yii::t('workflow','Value'),
			'roles' => Yii::t('workflow','Roles'),
			'requirePrevious' => Yii::t('workflow','Required Stages'),
			'requireComment' => Yii::t('workflow','Comment'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search($id) {

		$criteria = new CDbCriteria(array('condition'=>'workflowId='.$id,'order'=>'stageNumber ASC'));

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>ceil(ProfileChild::getResultsPerPage())
			),
		));
	}
}