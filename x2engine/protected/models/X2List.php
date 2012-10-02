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
 * This is the model class for table "x2_contact_lists".
 *
 * @package X2CRM.models
 */
class X2List extends CActiveRecord {

	private $_itemModel = null;
	private $_itemFields = array();
	private $_itemAttributeLabels = array();
	
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * Returns the name of the associated database table
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_lists';
	}
	
	public function behaviors() {
		return array(
			'X2LinkableBehavior'=>array(
				'class'=>'X2LinkableBehavior',
				'baseRoute'=>'/contacts/list',
				'autoCompleteSource'=>'/contacts/getLists',
			)
		);
	}

	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, createDate, lastUpdated, modelName', 'required'),
			array('id, count, visibility, createDate, lastUpdated', 'numerical', 'integerOnly'=>true),
			array('name, modelName', 'length', 'max'=>100),
			array('description', 'length', 'max'=>250),
			array('assignedTo, type, logicType', 'length', 'max'=>20),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, assignedTo, name, modelName, count, visibility, description, type, createDate, lastUpdated', 'safe', 'on'=>'search'),
		);
	}

	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'listItems'=>array(self::HAS_MANY, 'X2ListItem', 'listId')
		);
	}

	public function attributeLabels() {
		return array(
			'id' => 'ID',
			'assignedTo' => Yii::t('contacts','Owner'),
			'name' => Yii::t('contacts','Name'),
			'description' => Yii::t('contacts','Description'),
			'type' => Yii::t('contacts','Type'),
			'logicType' => Yii::t('contacts','Logic Type'),
			'modelName' => Yii::t('contacts','Record Type'),
			'visibility' => Yii::t('contacts','Visibility'),
			'count' => Yii::t('contacts','Members'),
			'createDate' => Yii::t('contacts','Create Date'),
			'lastUpdated' => Yii::t('contacts','Last Updated'),
		);
	}
	
	public function getDefaultRoute() {
		return '/contacts/list';
	}

	public function createLink() {
		if(isset($this->id))
			return CHtml::link($this->name,array($this->getDefaultRoute().'/'.$this->id));
		else
			return $this->name;
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('logicType',$this->logicType,true);
		$criteria->compare('modelName',$this->modelName,true);
		$criteria->compare('createDate',$this->createDate,true);
		$criteria->compare('lastUpdated',$this->lastUpdated,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
	
	// get fields for listed model
	public function getItemFields() {
		if(empty($this->_itemFields)) {
			if(!isset($this->_itemModel) && class_exists($this->modelName))
				$this->_itemModel = new $this->modelName;
			$this->_itemFields = $this->_itemModel->fields;
		}
		return $this->_itemFields;
	}
	
	// get attribute labels for listed model
	public function getItemAttributeLabels() {
		if(empty($this->_itemAttributeLabels)) {
			if(!isset($this->_itemModel) && class_exists($this->modelName))
				$this->_itemModel = new $this->modelName;
			$this->_itemAttributeLabels = $this->_itemModel->attributeLabels();
		}
		return $this->_itemAttributeLabels;
	}

	public static function load($id) {
        if(Yii::app()->user->getName()!='admin'){
            $condition = 't.visibility="1" OR t.assignedTo="Anyone"  OR t.assignedTo="'.Yii::app()->user->getName().'"';
                    /* x2temp */
                    $groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId='.Yii::app()->user->getId())->queryColumn();
                    if(!empty($groupLinks))
                        $condition .= ' OR t.assignedTo IN ('.implode(',',$groupLinks).')';

                    $condition .= 'OR (t.visibility=2 AND t.assignedTo IN 
                        (SELECT username FROM x2_group_to_user WHERE groupId IN
                            (SELECT groupId FROM x2_group_to_user WHERE userId='.Yii::app()->user->getId().')))';
        }else{
            $condition='';
        }
		return self::model()->with('listItems')->findByPk((int)$id, $condition);
	}

	/**
	 * Returns a CDbCriteria to retrieve all models specified by the list
	 * @return CDbCriteria Criteria to retrieve all models in the list
	 */
	public function queryCriteria() {
		$search=new CDbCriteria;
		if($this->type == 'dynamic') {
			$logicMode = $this->logicType;
			$criteria = CActiveRecord::model('X2ListCriterion')->findAllByAttributes(array('listId'=>$this->id,'type'=>'attribute'));
			foreach ($criteria as $criterion) {
				//if this criterion is for a date field, we perform its comparisons differently
				$dateType = false;
				//for each field in a model, make sure the criterion is in the same format
				foreach (CActiveRecord::model($this->modelName)->fields as $field) {
					if ($field->fieldName == $criterion->attribute) {
						switch($field->type) {
							case 'date': 
								if (ctype_digit($criterion->value) || (substr($criterion->value, 0, 1)=='-' && ctype_digit(substr($criterion->value, 1))))
									$criterion->value = (int)$criterion->value;
								else
									$criterion->value = strtotime($criterion->value);
								$dateType = true; 
								break;
							case 'link': 
								if (!ctype_digit($criterion->value)) $criterion->value = Fields::getLinkId($field->linkType,$criterion->value); break;
							case 'boolean': 
							case 'visibility':
								$criterion->value = in_array(strtolower($criterion->value),array('1','yes','y','t','true','True'))? 1 : 0; break;
						}
						break;
					}
				}
			
				if($criterion->attribute == 'tags' && $criterion->value) {
					$tags = explode(',',preg_replace('/\s?,\s?/',',',trim($criterion->value)));	//remove any spaces around commas, then explode to array
					for($i=0; $i<count($tags); $i++) {
						if(empty($tags[$i])) {
							unset($tags[$i]);
							$i--;
							continue;
						} else {
							if($tags[$i][0] != '#')
								$tags[$i] = '#'.$tags[$i];
							$tags[$i] = 'x2_tags.tag = "'.$tags[$i].'"';
						}
					}
					$tagConditions = implode(' OR ',$tags);
					
					$search->distinct = true;
					$search->join = 'JOIN x2_tags ON (x2_tags.itemId=t.id AND x2_tags.type="' . $this->modelName . '" AND ('.$tagConditions.'))';
				} else if ($dateType) {
					//assume for now that any dates in a criterion are at midnight of that day
					$thisDay = $criterion->value;
					$nextDay = $criterion->value + 86400;
					switch($criterion->comparison) {
						case '=':
							$subSearch = new CDbCriteria();
							$subSearch->compare($criterion->attribute, '>='.$thisDay, false, 'AND');
							$subSearch->compare($criterion->attribute, '<'.$nextDay, false, 'AND');
							$search->mergeWith($subSearch, $logicMode);
							break;
						case '<>':
							$subSearch = new CDbCriteria();
							$subSearch->compare($criterion->attribute, '<'.$thisDay, false, 'OR');
							$subSearch->compare($criterion->attribute, '>='.$nextDay, false, 'OR');
							$search->mergeWith($subSearch, $logicMode);
							break;
						case '>':
							$search->compare($criterion->attribute, '>='.$nextDay, true, $logicMode); break;
						case '<':
							$search->compare($criterion->attribute, '<'.$thisDay, true, $logicMode); break;
						case 'notEmpty':
							$search->addCondition($criterion->attribute.' IS NOT NULL AND '.$criterion->attribute.'!=""',$logicMode); break;
						case 'empty':
							$search->addCondition('('.$criterion->attribute.'="" OR '.$criterion->attribute.' IS NULL)',$logicMode); break;
						//the following comparitors are not supported for dates
						//case 'list':
						//case 'notList':
						//case 'noContains':
						//case 'contains':
					}
				} else {
					switch($criterion->comparison) {
						case '=':
							$search->compare($criterion->attribute,$criterion->value,false,$logicMode); break;
						case '>':
							$search->compare($criterion->attribute,'>='.$criterion->value,true,$logicMode); break;
						case '<':
							$search->compare($criterion->attribute,'<='.$criterion->value,true,$logicMode); break;
						case '<>':	// must test for != OR is null, because both mysql and yii are stupid
							$search->addCondition('('.$criterion->attribute.' IS NULL OR '.$criterion->attribute.'!='.CDbCriteria::PARAM_PREFIX.CDbCriteria::$paramCount.')',$logicMode);
							$search->params[CDbCriteria::PARAM_PREFIX.CDbCriteria::$paramCount++] = $criterion->value;
							break;
						case 'notEmpty':
							$search->addCondition($criterion->attribute.' IS NOT NULL AND '.$criterion->attribute.'!=""',$logicMode); break;
						case 'empty':
							$search->addCondition('('.$criterion->attribute.'="" OR '.$criterion->attribute.' IS NULL)',$logicMode); break;
						case 'list':
							$search->addInCondition($criterion->attribute,explode(',',$criterion->value),$logicMode); break;
						case 'notList':
							$search->addNotInCondition($criterion->attribute,explode(',',$criterion->value),$logicMode); break;
						case 'noContains':
							$search->compare($criterion->attribute,'<>'.$criterion->value,true,$logicMode); break;
						case 'contains':
						default:
							$search->compare($criterion->attribute,$criterion->value,true,$logicMode);
					}
				}
			}
		} else {
			$search->join = 'JOIN x2_list_items ON t.id = x2_list_items.contactId';
			$search->addCondition('x2_list_items.listId='.$this->id);
		}
        $condition = 'visibility="1" OR assignedTo="Anyone"  OR assignedTo="'.Yii::app()->user->getName().'"';
            /* x2temp */
            $groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId='.Yii::app()->user->getId())->queryColumn();
            if(!empty($groupLinks))
                $condition .= ' OR assignedTo IN ('.implode(',',$groupLinks).')';

            $condition .= 'OR (visibility=2 AND assignedTo IN 
                (SELECT username FROM x2_group_to_user WHERE groupId IN
                    (SELECT groupId FROM x2_group_to_user WHERE userId='.Yii::app()->user->getId().')))';
        if(Yii::app()->user->getName()!='admin')
            $search->addCondition($condition);
		return $search;
	}

	/**
	 * Returns a CDbCommand to retrieve all records in the list
	 * @return CDbCommand Command to retrieve all records in the list
	 */
	public function queryCommand() {
		$tableSchema = CActiveRecord::model($this->modelName)->getTableSchema();
		return $this->getCommandBuilder()->createFindCommand($tableSchema, $this->queryCriteria());
	}

	/**
	 * Returns a data provider for all the models in the list
	 * @return CActiveDataProvider A data provider serving out all the models in the list
	 */
	public function dataProvider($pageSize=null, $sort=null) {
		if (!isset($sort)) $sort = array();
		return new CActiveDataProvider($this->modelName, array(
			'criteria' => $this->queryCriteria(),
			'pagination'=>array(
				'pageSize'=>isset($pageSize)? $pageSize : ProfileChild::getResultsPerPage(),
			),
			'sort' => $sort
		));
	}

	/**
	 * Returns an array data provider for all models in the list,
	 * including the list_item status columns
	 * @return CSqlDataProvider
	 */
	public function statusDataProvider($pageSize=null) {
		$tbl = CActiveRecord::model($this->modelName)->tableName();
		$lstTbl = X2ListItem::model()->tableName();
		if ($this->type == 'dynamic') {
			$criteria = $this->queryCriteria();
			$count = CActiveRecord::model($this->modelName)->count($criteria);
			$sql = $this->getCommandBuilder()->createFindCommand($tbl, $criteria)->getText();
			$params = $criteria->params;
		} else { //static type lists
			$count = X2ListItem::model()->count('listId=:listId', array('listId'=>$this->id));
			$sql = "SELECT t.*, c.* FROM {$lstTbl} as t LEFT JOIN {$tbl} as c ON t.contactId=c.id WHERE t.listId=:listId;";
			$params = array('listId'=>$this->id);
		}
		return new CSqlDataProvider($sql, array(
			'totalItemCount'=>$count,
			'params'=>$params,
			'pagination'=>array(
				'pageSize'=>isset($pageSize)? $pageSize : ProfileChild::getResultsPerPage(),
			),
			'sort'=>array(
				//messing with attributes may cause columns to become unsortable
				'attributes'=>array('name','email','phone','address'),
				'defaultOrder'=>'lastUpdated DESC',
			),
		));
	}

	/**
	 * Returns the count of items in the list that have the specified status
	 * (i.e. sent, opened, clicked, unsubscribed)
	 * @return integer
	 */
	public function statusCount($type) {
		$whitelist = array('sent', 'opened', 'clicked', 'unsubscribed');
		if (!in_array($type, $whitelist)) {
			return 0;
		}
		
		$lstTbl = X2ListItem::model()->tableName();
		$count = Yii::app()->db->createCommand('SELECT COUNT(*) FROM '. $lstTbl .' WHERE listId = :listid AND '. $type .' > 0')
				->queryScalar(array('listid'=>$this->id));
		return $count;
	}

	/**
	 * Creates, saves, and returns a duplicate static list containing the same items.
	 * @return X2List
	 */
	public function staticDuplicate() {
		$dup = new X2List();
		$dup->attributes = $this->attributes;
		$dup->id = null;
		$dup->type = 'static';
		$dup->createDate = $dup->lastUpdated = time();
		$dup->isNewRecord = true;
		if (!$dup->save()) return;

		$count=0;
		$values = '';
		if ($this->type == 'dynamic') {
			//get all contact ids, generate sql to create list items from them
			$itemIds = $this->queryCommand()->select('id')->queryColumn();
			foreach($itemIds as $id) {
				if ($count !== 0) $values .= ',';
				$values .= '(NULL,'. $id .','. $dup->id .',0)';
				$count++;
			}
		} else { //static type lists
			//generate sql to replicate list items
			foreach($this->listItems as $listItem) {
				if ($count !== 0) $values .= ',';
				$values .= '('. (empty($listItem->emailAddress) ? 'NULL' : "'".$listItem->emailAddress."'") .','
					. (empty($listItem->contactId) ? 'NULL' : $listItem->contactId) .','. $dup->id .','. $listItem->unsubscribed .')';
				$count++;
			}
		}
		$sql = 'INSERT into x2_list_items (emailAddress, contactId, listId, unsubscribed) VALUES ' . $values . ';';
		$dup->count = $count;

		$transaction = Yii::app()->db->beginTransaction();
		try {
			Yii::app()->db->createCommand($sql)->execute();
			if (!$dup->save()) throw new Exception(array_shift(array_shift($dup->getErrors())));
			$transaction->commit();
		} catch (Exception $e) {
			$transaction->rollBack();
			$dup->delete();
			$dup = null;
		}
		return $dup;
	}
	
	public static function getRoute($id) {
		if($id=='all')
			return array('/contacts/index');
		else if ($id=='new')
			return array('/contacts/newContacts');
		else if (empty($id) || $id=='my')
			return array('/contacts/myContacts');
		else
			return array('/contacts/list/'.$id);
	}
}
