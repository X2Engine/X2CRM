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
class Tags extends CActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 * @return Tags the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_tags';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
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
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
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
			$links[] = CHtml::link($tag->tag,array('/search/search','term'=>$tag->tag));
		}
		if(!empty($limit) && $tagCount > $limit)
			$links[] = '...';
			
		return implode(' ',$links);
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
	
	/**
	 * Splits the provided string on commas, removes spaces, makes sure each tag has a hash
	 * @param string $str a string containing 1 or more comma-separated tags
	 * @return array the properly formatted tags
	 */
	public static function parseTags($str) {
		$tags = array();
		
		foreach(explode(',',$str) as $tag) {	// split the string
			$tag = trim($tag);						// eliminate whitespace
			if(strlen($tag) > 0) {					// eliminate empty tags
				if(substr($tag,0,1) !== '#')		// make sure they have the hash
					$tag = '#'.$tag;
				$tags[] = $tag;
			}
		}
		return $tags;
	}
}
