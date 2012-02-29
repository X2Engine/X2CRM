<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

/**
 * This is the model class for table "x2_workflow_stages".
 *
 * The followings are the available columns in table 'x2_workflow_stages':
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
			array('workflowId, requirePrevious', 'numerical', 'integerOnly'=>true),
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
	/**
	 * @return array behaviors.
	 */
	// public function behaviors(){
		// return array('CSaveRelationsBehavior' => array('class' => 'application.components.CSaveRelationsBehavior'));
	// }
	
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