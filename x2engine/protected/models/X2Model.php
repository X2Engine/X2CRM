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


Yii::import('application.components.X2LinkableBehavior');

abstract class X2Model extends CActiveRecord {

	protected static $_fields = null;	// one copy of fields for all instances of this model
	
	protected function queryFields() {
		if(!isset(self::$_fields[$this->tableName()]))	// only look up fields if they haven't already been looked up
			if(get_class($this) === 'Product' || get_class($this) === 'Quote')
				self::$_fields[$this->tableName()] = Fields::model()->findAllByAttributes(array('modelName'=>get_class($this) . 's'));
			else
				self::$_fields[$this->tableName()] = Fields::model()->findAllByAttributes(array('modelName'=>get_class($this))); //Yii::app()->db->createCommand()->select('*')->from('x2_fields')->where('modelName="'.get_class($this).'"')->queryAll();
	}
	
	/**
	 * @param int $id the route to this model's AutoComplete data source
	 * @param string $class the model class
	 * @return string a link to the model, or $id if the model is invalid
	 */
	public static function getModelLink($id,$class) {
		
		$model = CActiveRecord::model($class)->findByPk($id);
		if(isset($model))
			return $model->getLink();
			// return CHtml::link($model->name,array($model->getDefaultRoute().'/'.$model->id));
		elseif(is_numeric($id))
			return '';
		else
			return $id;
	}
	
	/**
	 * Returns a list of behaviors that this model should behave as.
	 * @return array the behavior configurations (behavior name=>behavior configuration)
	 */
	public function behaviors() {
		return array(
			'X2LinkableBehavior'=>array(
				'class'=>'X2LinkableBehavior',
			)
		);
	}
	
	/**
	 * Generates validation rules for custom fields
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
	
		$this->queryFields();
			
		$fieldRules = array(
			'required'=>array(),
			'email'=>array(),
			'int'=>array(),
			'date'=>array(),
			'float'=>array(),
			'boolean'=>array(),
			'safe'=>array(),
			'search'=>array()
		);
		
		foreach(self::$_fields[$this->tableName()] as &$_field) {
		
			$fieldRules['search'][] = $_field->fieldName;
		
			switch($_field->type) {
				case 'varchar':
				case 'text':
				case 'url':
				case 'currency':
				case 'dropdown':
					$fieldRules['safe'][] = $_field->fieldName;	// these field types have no rules, but still need to be allowed
					break;
				case 'date':
					$fieldRules['int'][] = $_field->fieldName;		// date is actually an int
					break;
				default:
					$fieldRules[ $_field->type ][] = $_field->fieldName;		// otherwise use the type as the validator name
			}
			
			if($_field->required)
				$fieldRules['required'][] = $_field->fieldName;
		}

		return array(
			array(implode(',', $fieldRules['required']), 'required'),
			array(implode(',', $fieldRules['email']), 'email'),
			array(implode(',', $fieldRules['int'] + $fieldRules['date']), 'numerical', 'integerOnly'=>true ),
			array(implode(',', $fieldRules['float']), 'numerical'),
			array(implode(',', $fieldRules['boolean']), 'boolean'),
			array(implode(',', $fieldRules['safe']), 'safe'),
			array(implode(',', $fieldRules['search']),'safe','on'=>'search')
		);
	}
	
	/**
	 * Returns custom attribute values defined in x2_fields
	 * @return array customized attribute labels (name=>label)
	 * @see generateAttributeLabel
	 */
	public function attributeLabels() {
	
		$this->queryFields();
		
		$labels = array();
			
		foreach(self::$_fields[$this->tableName()] as &$_field)
			$labels[ $_field->fieldName ] = Yii::t(strtolower(get_class($this)),$_field->attributeLabel);

		return $labels;
	}

	/**
	 * Returns the text label for the specified attribute.
	 * This method overrides the parent implementation by supporting
	 * returning the label defined in relational object.
	 * In particular, if the attribute name is in the form of "post.author.name",
	 * then this method will derive the label from the "author" relation's "name" attribute.
	 * @param string $attribute the attribute name
	 * @return string the attribute label
	 * @see generateAttributeLabel
	 * @since 1.1.4
	 */
	public function getAttributeLabel($attribute) {
	
		$this->queryFields();
		
		// don't call attributeLabels(), just look in self::$_fields
		foreach(self::$_fields[$this->tableName()] as &$_field) {
			if($_field->fieldName == $attribute)
				return Yii::t(strtolower(get_class($this)),$_field->attributeLabel);
		}
		// original Yii code
		if(strpos($attribute,'.')!==false) {
			$segs=explode('.',$attribute);
			$name=array_pop($segs);
			$model=$this;
			foreach($segs as $seg) {
				$relations=$model->getMetaData()->relations;
				if(isset($relations[$seg]))
					$model=CActiveRecord::model($relations[$seg]->className);
				else
					break;
			}
			return $model->getAttributeLabel($name);
		} else
			return $this->generateAttributeLabel($attribute);
	}
	
	public function getFields($assoc = false) {
		$this->queryFields();
		if($assoc) {
			$fields = array();
			foreach(self::$_fields[$this->tableName()] as &$field)
				$fields[$field->fieldName] = $field->attributes;
				
			return $fields;
		} else {
			return self::$_fields[$this->tableName()];
		}
	}
	
	public function getField($fieldName) {
		$this->queryFields();
		$field = null;
		foreach(self::$_fields[$this->tableName()] as &$_field) {
			if($_field->fieldName == $fieldName)
				return $_field;
		}
		if(!isset($field))
			return null;
	}
	
	public function renderAttribute($fieldName,$makeLinks = true,$textOnly = true) {
		
		$field = $this->getField($fieldName);
		if(!isset($field))
			return null;

		switch($field->type) {
		
		
			case 'date':
				return empty($this->$fieldName)? ' ' : Yii::app()->controller->formatLongDate($this->$fieldName);
				
			case 'rating':
			
				if($textOnly) {
					return $this->$fieldName;
				} else {
					return Yii::app()->controller->widget('CStarRating',array(
						'model'=>$this,
						'attribute'=>$field->fieldName,
						'readOnly'=>true,
						'minRating'=>1, //minimal valuez
						'maxRating'=>5,//max value
						'starCount'=>5, //number of stars
						'cssFile'=>Yii::app()->theme->getBaseUrl().'/css/rating/jquery.rating.css',
					),true);
				}
				
			case 'assignment':
				return User::getUserLinks($this->$fieldName);
				
			case 'visibility':
				switch($this->$fieldName) {
					case '1':
						return Yii::t('app','Public'); break;
					case '0': 
						return Yii::t('app','Private'); break;
					case '2':
						return Yii::t('app','User\'s Groups'); break;
					default:
						return '';
				}
			
			case 'email':
				if(empty($this->$fieldName)) {
					return '';
				} else {
					$mailtoLabel = isset($this->name)? '"'.$this->name.'" <'.$this->$fieldName.'>' : $this->$fieldName;
					return $makeLinks? CHtml::mailto($this->$fieldName,$mailtoLabel) : $this->fieldName;
				}
			case 'url':
			
				if(!$makeLinks)
					return $this->$fieldName;
					
				if(empty($this->$fieldName)) {
					$text = '';
				} elseif(!empty($field->linkType)) {
					switch($field->linkType) {
						case 'skype':
							$text = '<a href="callto:'.$this->$fieldName.'">'.$this->$fieldName.'</a>';
							break;
						case 'googleplus':
							$text = '<a href="http://plus.google.com/'.$this->$fieldName.'">'.$this->$fieldName.'</a>';
							break;
						case 'twitter':
							$text = '<a href="http://www.twitter.com/#!/'.$this->$fieldName.'">'.$this->$fieldName.'</a>';
							break;
						case 'linkedin':
							$text = '<a href="http://www.linkedin.com/in/'.$this->$fieldName.'">'.$this->$fieldName.'</a>';
							break;
						default:
							$text = '<a href="http://www.'.$field->linkType.'.com/'.$this->$fieldName.'">'.$this->$fieldName.'</a>';
					}
				} else {
					$text = trim(preg_replace(
						array(
							'/(?(?=<a[^>]*>.+<\/a>)(?:<a[^>]*>.+<\/a>)|([^="\']?)((?:https?|ftp|bf2|):\/\/[^<> \n\r]+))/iex',
							'/<a([^>]*)target="?[^"\']+"?/i',
							'/<a([^>]+)>/i',
							'/(^|\s|>)(www.[^<> \n\r]+)/iex',
						),
						array(
							"stripslashes((strlen('\\2')>0?'\\1<a href=\"\\2\" target=\"_blank\">".$this->getAttributeLabel($fieldName)."</a>\\3':'\\0'))",
							'<a\\1 target="_blank"',
							'<a\\1 target="_blank">',
							"stripslashes((strlen('\\2')>0?'\\1<a href=\"http://\\2\" target=\"_blank\">".$this->getAttributeLabel($fieldName)."</a>\\3':'\\0'))",
						),
						$this->$fieldName
					));
				}
				return $text;
				
			case 'link':
				if(!empty($this->$fieldName) && is_numeric($this->$fieldName)) {
					$className = ucfirst($field->linkType);
					if(class_exists($className))
						$linkModel = CActiveRecord::model($className)->findByPk($this->$fieldName);
					if(isset($linkModel))
						return $makeLinks? $linkModel->createLink() : $linkModel->name;
					else
						return '';
				} else {
					return $this->$fieldName;
				}
				
			case 'boolean':
				return $textOnly? $this->fieldName : CHtml::checkbox('',$this->$fieldName,array('onclick'=>'return false;', 'onkeydown'=>'return false;'));
				
			case 'currency':
				if($this instanceof Product) // products have their own currency
					return Yii::app()->locale->numberFormatter->formatCurrency($this->$fieldName, $this->currency);
				else
					return empty($this->$fieldName)?"&nbsp;":Yii::app()->locale->numberFormatter->formatCurrency($this->$fieldName, Yii::app()->params['currency']);
					
			case 'dropdown':
				return Yii::t(strtolower(Yii::app()->controller->id),$this->$fieldName);
				
			case 'text':
				return Yii::app()->controller->convertUrls($this->$fieldName);     
				
			default:
				return $this->$fieldName;
		}
	}

	/**
	 * Sets attributes using X2Fields
	 * @param array &$data array of attributes to be set (eg. $_POST['Contacts'])
	 */
	public function setX2Fields(&$data) {
		$this->queryFields();

		foreach(self::$_fields[$this->tableName()] as &$_field) {	// now loop through fields to deal with special types
			$fieldName = $_field->fieldName;
		
			if(!$_field->readOnly && isset($data[$fieldName])) {	// skip fields that are read-only or haven't been set
			
				$value = $data[$fieldName];

				if($value == $this->getAttributeLabel($fieldName))	// eliminate placeholder values
					$value = '';
			
				if($_field->type == 'assignment' && $_field->linkType == 'multiple') {
					$value = Accounts::parseUsers($value);
				} elseif($_field->type == 'date') {
					$value = Yii::app()->controller->parseDate($value);
					if($value === false)
						$value = null;
				} elseif($_field->type == 'link' && !empty($_field->linkType)) {

					if(!empty($value) && isset($data[$fieldName.'_id'])) {	// check the ID, if provided
						$linkId = $data[$fieldName.'_id'];
						if(!empty($linkId) && CActiveRecord::model(ucfirst($_field->linkType))->countByAttributes(array('id'=>$linkId)))	// if the link model actually exists,
							$value = $linkId;																	// then use the ID as the field value
					}
					if(!empty($value) && !ctype_digit($value)) {	// if the field is sitll text, try to find the ID based on the name
					
						if($_field->linkType == 'Contacts') {
							$fullname = explode(' ', $value);
							$firstName = $fullname[0];
							$lastName = $fullname[1];
							$linkModel = CActiveRecord::model(ucfirst($_field->linkType))->findByAttributes(array('firstName'=>$firstName, 'lastName'=>$lastName));
						} else {
							$linkModel = CActiveRecord::model(ucfirst($_field->linkType))->findByAttributes(array('name'=>$value));
						}
						if(isset($linkModel))
							$value = $linkModel->id;
					}
				}
				$this->$fieldName = trim($value);
			}
		}
	}
	
	/**
	 * Base search function, includes Retrieves a list of models based on the current search/filter conditions.
	 * @param CDbCriteria $criteria the attribute name
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function searchBase($criteria) {
		$this->queryFields();
		$this->compareAttributes($criteria);

		return new SmartDataProvider(get_class($this), array(
			'sort'=>array(
				'defaultOrder'=>'lastUpdated DESC',
			),
			'pagination'=>array(
				'pageSize'=>ProfileChild::getResultsPerPage(),
			),
			'criteria'=>$criteria,
		));
	}
	
	public function compareAttributes(&$criteria) {
		$this->queryFields();
		
		foreach(self::$_fields[$this->tableName()] as &$field) {
			$fieldName = $field['fieldName'];
			switch($field['type']) {
				case 'boolean':
					$criteria->compare($fieldName,$this->compareBoolean($this->$fieldName), true);
					break;
				case 'link':
					$criteria->compare($fieldName,$this->compareLookup($field->linkType, $this->$fieldName), true);
					$criteria->compare($fieldName,$this->$fieldName, true, 'OR');
					break;
				case 'assignment':
					$criteria->compare($fieldName,$this->compareAssignment($this->$fieldName), true);
					break;
				default:
					$criteria->compare($fieldName,$this->$fieldName,true);
			}
		}
		
		if(get_class($this) == 'Contacts')
			$criteria->compare('CONCAT(firstName," ",lastName)', $this->name,true);


		return new SmartDataProvider(get_class($this), array(
			'sort'=>array(
				'defaultOrder'=>'createDate DESC',
			),
			'pagination'=>array(
				'pageSize'=>ProfileChild::getResultsPerPage(),
			),
			'criteria'=>$criteria,
		));
	}

	protected function compareLookup($linkType, $value) {
		if(is_null($value) || $value=="") return null;
		
		$linkType = ucfirst($linkType);
		
		if(class_exists($linkType)) {
			$class = new $linkType;
			$tableName = $class->tableName();
			
			if($linkType == 'Contacts')
				$linkIds = Yii::app()->db->createCommand()->select('id')->from($tableName)->where(array('like','CONCAT(firstName," ",lastName)',"%$value%"))->queryColumn();
			else
			$linkIds = Yii::app()->db->createCommand()->select('id')->from($tableName)->where(array('like','name',"%$value%"))->queryColumn();
				
			return empty($linkIds)? -1 : $linkIds;
		}
		return -1;
	}

	protected function compareBoolean($data) {
            if(is_null($data)){
                return null;
            }
		return in_array(mb_strtolower(trim($data)),array( 0, 'f', 'false', Yii::t('actions',"No") ))? 0 : 1;		// default to true unless recognized as false
	}
	
	protected function compareAssignment($data) {
		if(is_null($data))
			return null;
		$userNames = Yii::app()->db->createCommand()->select('username')->from('x2_users')->where(array('like','CONCAT(firstName," ",lastName)',"%$data%"))->queryColumn();
		$groupIds = Yii::app()->db->createCommand()->select('id')->from('x2_groups')->where(array('like','name',"%$data%"))->queryColumn();
		
		return (count($groupIds) + count($userNames) == 0)? -1 : $userNames + $groupIds;
	}

	public function createLink() {
		if(isset($this->id))
			return $this->getLink();
			// return CHtml::link($this->name,array($this->getDefaultRoute().'/'.$this->id));
		else
			return $this->name;
	}
}
