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
 * This is the model class for table "x2_tags".
 *
 * @package X2CRM.models
 * @property integer $id
 * @property string $type
 * @property integer $itemId
 * @property string $taggedBy
 * @property string $tag
 * @property integer $timestamp
 * @property string $itemName
 */
class Tags extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Tags the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'x2_tags';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('type, itemId, taggedBy, tag', 'required'),
			array('itemId, timestamp', 'numerical', 'integerOnly'=>true),
			array('type, taggedBy', 'length', 'max'=>50),
			array('tag, itemName', 'length', 'max'=>250),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, type, itemId, taggedBy, tag, timestamp, itemName', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'type' => 'Type',
			'itemId' => 'Item',
			'taggedBy' => 'Tagged By',
			'tag' => 'Tag',
			'timestamp' => 'Timestamp',
			'itemName' => 'Item Name',
		);
	}
	
	/*
	 * Returns a list of all existing tags, without the # at the beginning
	 */
	public static function getAllTags() {
		$tags = Yii::app()->db->createCommand()
			->selectDistinct('tag')
			->from('x2_tags')
			->order('tag DESC')
			->queryColumn();

		foreach ($tags as &$tag) {
			$tag = substr($tag, 1);
		}

		return $tags;
	}

	public static function getTags($model,$id,$limit = 0) {
	
		if(!is_numeric($limit) || empty($limit))
			$limit = null;
	
	
		$tags = Yii::app()->db->createCommand()
			->select('tag')
			->from('x2_tags')
			->where('type="Contacts" AND itemId=:id',array(':id'=>$id))
			->order('id DESC')
			->queryColumn();
		
		if($limit !== null && sizeof($tags) > $limit) {
			$tags = array_slice($tags,0,$limit);
			$tags[] = '...';
		}
			
		return implode(' ',$tags);
	}
	
	public static function getTagLinks($model,$id,$limit = 0) {
	
		if(!is_numeric($limit) || empty($limit))
			$limit = null;
	
		$tags = Tags::model()->findAllByAttributes(
			array('type'=>$model,'itemId'=>$id),
			new CDbCriteria(array('order'=>'id DESC','limit'=>$limit))
		);
		$tagCount = Tags::model()->countByAttributes(array('type'=>$model,'itemId'=>$id));
		
		$links = array();
		foreach($tags as &$tag) {
			$links[] = CHtml::link($tag->tag,array('search/search','term'=>$tag->tag));
		}
		if(!empty($limit) && $tagCount > $limit)
			$links[] = '...';
			
		return implode(' ',$links);
	}
	
	
	
	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('itemId',$this->itemId);
		$criteria->compare('taggedBy',$this->taggedBy,true);
		$criteria->compare('tag',$this->tag,true);
		$criteria->compare('timestamp',$this->timestamp);
		$criteria->compare('itemName',$this->itemName,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}
