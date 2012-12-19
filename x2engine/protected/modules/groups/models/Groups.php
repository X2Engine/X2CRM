<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
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

Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_groups".
 * @package X2CRM.modules.groups.models
 */
class Groups extends X2Model {
	/**
	 * Returns the static model of the specified AR class.
	 * @return Groups the static model class
	 */
	public static function model($className=__CLASS__) { return parent::model($className); }

	/**
	 * @return string the associated database table name
	 */
	public function tableName() { return 'x2_groups'; }

	public function behaviors() {
		return array(
			'X2LinkableBehavior'=>array(
				'class'=>'X2LinkableBehavior',
				'baseRoute'=>'/groups'
			)
		);
	}
	
	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name', 'required'),
			array('name', 'length', 'max'=>259),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name', 'safe', 'on'=>'search'),
		);
	}
	
	public static function getNames() {
		
		$groupNames = array();
		$data = Yii::app()->db->createCommand()->select('id,name')->from('x2_groups')->order('name ASC')->queryAll(false);
		foreach($data as $row)
			$groupNames[$row[0]] = $row[1];
		
		return $groupNames;
		
		// $groupArray = CActiveRecord::model('Groups')->findAll();
		// $names = array();
		// foreach ($groupArray as $group) {
			// $names[$group->id] = $group->name;
		// }
		// return $names;
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
			'name' => 'Name',
		);
	}
	
	// public static function getLink($id) {
		// $groupName = Yii::app()->db->createCommand()->select('name')->from('x2_groups')->where('id='.$id)->queryScalar();

		// if(isset($groupName))
			// return CHtml::link($groupName,array('/groups/'.$id));
		// else
			// return '';
	// }

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
	
	/* inGroup
	 *
	 * Find out if a user belongs to a group
	 */
	public static function inGroup($userId, $groupId) {
		return GroupToUser::model()->exists("userId=$userId AND groupId=$groupId");
	}

	/* Looks up groups to which the specified user belongs.
	 * Uses cache to lookup/store groups.
	 * 
	 * @param Integer $userId user to look up groups for
	 * @param Boolean $cache whether to use cache
	 * @return Array array of groupIds
	 */
	public static function getUserGroups($userId,$cache=true) {
		// check the app cache for user's groups
		if($cache === true && ($userGroups = Yii::app()->cache->get('user_groups')) !== false) {
			if(isset($userGroups[$userId]))
				return $userGroups[$userId];
		} else {
			$userGroups = array();
		}
		
		$userGroups[$userId] = Yii::app()->db->createCommand()	// get array of groupIds
			->select('groupId')
			->from('x2_group_to_user')
			->where('userId=' . $userId)->queryColumn();
		
		if($cache === true)
			Yii::app()->cache->set('user_groups',$userGroups,259200); // cache user groups for 3 days
		
		return $userGroups[$userId];
	}
}