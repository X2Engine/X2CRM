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
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
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
 * This is the model class for table "x2_notifications".
 * 
 * @package X2CRM.models
 */
class Notification extends CActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Notification the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_notifications';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('type', 'required'),
			array('modelId, createDate', 'numerical', 'integerOnly'=>true),
			array('viewed', 'boolean'),
			// array('record, modelType, fieldName', 'length', 'max'=>250),
			array('user, createdBy, comparison, type', 'length', 'max'=>20),
			array('type, value', 'length', 'max'=>250),
			// array('text', 'safe'),
			array('id, user, viewed, createDate, type, comparison, modelType, modelId, fieldName', 'safe', 'on'=>'search'),	//text, record, 
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => 'ID',
			// 'text' => 'Text',
			// 'record' => 'Record',
			'user' => 'User',
			'createdBy' => 'Created By',
			'viewed' => 'Viewed',
			'createDate' => 'Create Date',
			'type' => 'Type',
			'comparison' => 'Comparison',
			'value' => 'Value',
			'modelType' => 'Model Type',
			'modelId' => 'Model ID',
			'fieldName' => 'Field Name',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		// $criteria->compare('text',$this->text,true);
		// $criteria->compare('record',$this->record,true);
		$criteria->compare('user',$this->user,true);
		$criteria->compare('createdBy',$this->createdBy,true);
		$criteria->compare('viewed',$this->viewed);
		$criteria->compare('createDate',$this->createDate,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('comparison',$this->comparison,true);
		// $criteria->compare('value',$this->value,true);
		$criteria->compare('modelType',$this->modelType,true);
		$criteria->compare('modelId',$this->modelId,true);
		$criteria->compare('fieldName',$this->fieldName,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	
	public function getMessage() {
        
		if(empty($this->modelId) || empty($this->modelType))	// skip if there is no association
			$record = null;
		else {
			if(class_exists($this->modelType))
				$record = CActiveRecord::model($this->modelType)->findByPk($this->modelId);
			else
				return $this->modelType;
		}
		
		if(!isset($record) && $this->type!='lead_failure'){
			// return var_dump($this->attributes);
			return null;
        }
		$passive = $this->createdBy == 'API' || empty($this->createdBy);
        
		switch($this->type) {
            
			case 'action_complete':
				if($passive)
					return Yii::t('actions','Action completed: {action}',array('{action}'=>$record->getLink(30)));
				else
					return Yii::t('actions','{user} completed an action: {action}',array('{user}'=>User::getUserLinks($record->completedBy),'{action}'=>$record->getLink(20)));

			// case 'workflow_complete':
				// if($passive)
					// return Yii::t('actions','Stage {n}: {stage} was completed for {record}',array('{record}'=>$record->getLink()));
				// else
					// return Yii::t('actions','{user} completed stage {n}: {stage} was completed for {record}',array('{record}'=>$record->getLink()));

			case 'create':
				return Yii::t('app','New record assigned to you: {link}.',array('{link}'=>$record->getLink()));

			case 'change':
				if($this->comparison == 'change') {

					$msg = $passive? '{record}\'s {field} was changed to {value}' : '{user} changed {record}\'s {field} to {value}';

					return Yii::t('app',$msg,array(
						'{field}'=>$record->getAttributeLabel($this->fieldName),
						'{value}'=>$record->renderAttribute($this->fieldName,true,true),
						'{record}'=>$record->getLink(),
						'{user}'=>User::getUserLinks($this->createdBy)
					));
				
				} else {
				// > < =
					$msg = $passive? '{record}\'s {field} was changed to {value}' : '{user} changed {record}\'s {field} to {value}';

					return Yii::t('app',$msg,array(
						'{field}'=>$record->getAttributeLabel($this->fieldName),
						'{value}'=>$record->renderAttribute($this->fieldName,true,true),
						'{record}'=>$record->getLink(),
						'{user}'=>User::getUserLinks($this->createdBy)
					));
				
				
				}

			case 'lead_failure':
				return Yii::t('app','A lead failed to come through Lead Capture. Check {link} to recover it.',array(
					'{link}'=>CHtml::link(Yii::t('app','here'),Yii::app()->controller->createUrl('/contacts/cleanFailedLeads'))
				));
			case 'assignment':
				if($passive)
					return Yii::t('app','You have been assigned a record: {record}',array('{record}'=>$record->getLink()));
				else
					return Yii::t('app','{user} assigned a record to you: {record}',array('{user}'=>User::getUserLinks($this->createdBy),'{record}'=>$record->getLink()));

			case 'delete':
				if($passive)
					return Yii::t('app','Record deleted: {record}',array('{record}'=>$this->modelType.' '.$this->modelId));
				else
					return Yii::t('app','{user} deleted a record: {record}',array('{user}'=>User::getUserLinks($this->createdBy),'{record}'=>$this->modelType.' '.$this->modelId));

			case 'update':
				if($passive)
					return Yii::t('app','Record updated: {record}',array('{record}'=>$record->getLink()));
				else
					return Yii::t('app','{user} updated a record: {record}',array('{user}'=>User::getUserLinks($this->createdBy),'{record}'=>$record->getLink()));

			case 'dup_discard':
				if($passive)
					return Yii::t('app','A record has been marked as a duplicate and hidden to everyone but the admin: {record}',
						array('{record}'=>$record->getLink()));
				else
					return Yii::t('app','{user} marked a record as a duplicate. This record is hidden to everyone but the admin: {record}',
						array('{user}'=>User::getUserLinks($this->createdBy),'{record}'=>$record->getLink()));

			case 'email_clicked':
				return Yii::t('app','{record} clicked an email link: {campaign}',array('{record}'=>$record->getLink(),'{campaign}'=>$this->value));
			
			case 'email_opened':
				return Yii::t('app','{record} opened an email: {campaign}',array('{record}'=>$record->getLink(),'{campaign}'=>$this->value));
			
			case 'email_unsubscribed':
				return Yii::t('app','{record} unsubscribed from a campaign: {campaign}',array('{record}'=>$record->getLink(),'{campaign}'=>$this->value));

			case 'social_post':
				return Yii::t('app','{user} posted on {link}',array('{user}'=>User::getUserLinks($this->createdBy),'{link}'=>$record->getLink()));
				
			case 'social_comment':
				return Yii::t('app','{user} replied on {link}',array('{user}'=>User::getUserLinks($this->createdBy),'{link}'=>$record->getLink()));

			case 'voip_call':
				
				return Yii::t('app','Incoming call from <b>{phone}</b> ({record})',array('{record}'=>$record->getLink(),'{phone}'=>$this->value));

			default:
				return null;

		}
	}
}