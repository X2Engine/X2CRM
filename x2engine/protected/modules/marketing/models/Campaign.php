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
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
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
 * This is the model class for table "x2_campaigns".
 *
 * The followings are the available columns in table 'x2_campaigns':
 * @property integer $id
 * @property string $assignedTo
 * @property string $name
 * @property string $description
 * @property string $fieldOne
 * @property string $fieldTwo
 * @property string $fieldThree
 * @property string $fieldFour
 * @property string $fieldFive
 * @property integer $createDate
 * @property integer $lastUpdated
 * @property string $updatedBy
 */
class Campaign extends X2Model {
	/**
	 * Returns the static model of the specified AR class.
	 * @return Campaign the static model class
	 */
	public static function model($className=__CLASS__) { return parent::model($className); }
	
	/**
	 * @return string the route to view this model
	 */
	public function getDefaultRoute() { return '/marketing'; }
	
	/**
	 * @return string the route to this model's AutoComplete data source
	 */
	public function getAutoCompleteSource() { return '/marketing/getItems'; }
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName()	{ return 'x2_campaigns'; }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, subject', 'required'),
			array('id, listId, active, launched, complete', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, listId, description, type, subject, content, active, complete, launched', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array();
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		$fields=Fields::model()->findAllByAttributes(array('modelName'=>'Campaign'));
		$arr=array();
		foreach($fields as $field){
			$arr[$field->fieldName]=Yii::t('app',$field->attributeLabel);
		}
		
		return $arr;

	}
	
	public function search() {
		// $this->active = '';
		$criteria=new CDbCriteria;
		// $condition = 'assignedTo="'.Yii::app()->user->getName().'"';
			// $parameters=array('limit'=>ceil(ProfileChild::getResultsPerPage()));
			// /* x2temp */
			// $groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId='.Yii::app()->user->getId())->queryColumn();
			// if(!empty($groupLinks))
				// $condition .= ' OR assignedTo IN ('.implode(',',$groupLinks).')';
			// /* end x2temp */
		// $parameters['condition']=$condition;
		// $criteria->scopes=array('findAll'=>array($parameters));
		
		// $criteria->addCondition('x2_checkViewPermission(visibility,assignedTo,"'.Yii::app()->user->getName().'") > 0');
		
		return $this->searchBase($criteria);
	}
}
