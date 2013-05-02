<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

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
			'listItems'=>array(self::HAS_MANY, 'X2ListItem', 'listId'),
			'campaign'=>array(self::HAS_ONE, 'Campaign', 'listId'),
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
	 * When a list is deleted, remove its entries in x2_list_items
	 */
	public function afterDelete() {
		CActiveRecord::model('X2ListItem')->deleteAllByAttributes(array('listId'=>$this->id)); // delete all the things!
		
		parent::afterDelete();
	}
	
	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria = new CDbCriteria;

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
        // if(Yii::app()->user->checkAccess('AdminIndex')) {
            // $condition = 't.visibility="1" OR t.assignedTo="Anyone"  OR t.assignedTo="'.Yii::app()->user->getName().'"';
			// /* x2temp */
			// $groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId='.Yii::app()->user->getId())->queryColumn();
			// if(!empty($groupLinks))
				// $condition .= ' OR t.assignedTo IN ('.implode(',',$groupLinks).')';

			// $condition .= 'OR (t.visibility=2 AND t.assignedTo IN 
				// (SELECT username FROM x2_group_to_user WHERE groupId IN
					// (SELECT groupId FROM x2_group_to_user WHERE userId='.Yii::app()->user->getId().')))';
		// } else {
			// $condition='';
		// }
		return self::model()->with('listItems')->findByPk((int)$id,X2Model::model('Contacts')->getAccessCriteria());
	}

	/**
	 * Returns a CDbCriteria to retrieve all models specified by the list
	 * @return CDbCriteria Criteria to retrieve all models in the list
	 */
	public function queryCriteria($useAccessRules=true) {
		$search = new CDbCriteria;

		if($this->type == 'dynamic') {
			$logicMode = $this->logicType;
			$criteria = X2Model::model('X2ListCriterion')->findAllByAttributes(array('listId'=>$this->id,'type'=>'attribute'));
			foreach ($criteria as $criterion) {
				//if this criterion is for a date field, we perform its comparisons differently
				$dateType = false;
				//for each field in a model, make sure the criterion is in the same format
				foreach (X2Model::model($this->modelName)->fields as $field) {
					if ($field->fieldName == $criterion->attribute) {
						switch($field->type) {
							case 'date': 
							case 'dateTime':
								if (ctype_digit((string)$criterion->value) || (substr($criterion->value, 0, 1)=='-' && ctype_digit((string)substr($criterion->value, 1))))
									$criterion->value = (int)$criterion->value;
								else
									$criterion->value = strtotime($criterion->value);
								$dateType = true; 
								break;
							case 'link': 
								if (!ctype_digit((string)$criterion->value)) $criterion->value = Fields::getLinkId($field->linkType,$criterion->value); break;
							case 'boolean': 
							case 'visibility':
								$criterion->value = in_array(strtolower($criterion->value),array('1','yes','y','t','true'))? 1 : 0; break;
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
        // $condition = 'visibility="1" OR assignedTo="Anyone"  OR assignedTo="'.Yii::app()->user->getName().'"';
            // /* x2temp */
            // $groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId='.Yii::app()->user->getId())->queryColumn();
            // if(!empty($groupLinks))
                // $condition .= ' OR assignedTo IN ('.implode(',',$groupLinks).')';

            // $condition .= 'OR (visibility=2 AND assignedTo IN 
                // (SELECT username FROM x2_group_to_user WHERE groupId IN
                    // (SELECT groupId FROM x2_group_to_user WHERE userId='.Yii::app()->user->getId().')))';
        // if(Yii::app()->user->getName()!='admin')
            // $search->addCondition($condition);
			
		if($useAccessRules) {
			$accessCriteria = X2Model::model('Contacts')->getAccessCriteria();	// record-level access control for Contacts
			$accessCriteria->mergeWith($search,'AND');
			
			return $accessCriteria;
		} else {
			return $search;
		}
	}

	/**
	 * Returns a CDbCommand to retrieve all records in the list
	 * @return CDbCommand Command to retrieve all records in the list
	 */
	public function queryCommand() {
		$tableSchema = X2Model::model($this->modelName)->getTableSchema();
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
	 * Generates an array of links for the VCR controls based on the specified dataprovider and current ID
	 * @param CActiveDataProvider $dataProvider the data provider of the most recent gridview
	 * @param Integer $id the ID of the current record
	 * @return Array array of VCR links and stats
	 */
	public static function getVcrLinks(&$dataProvider,$modelId) {
	
	
		$criteria = $dataProvider->criteria;
		
		$tableSchema = X2Model::model($dataProvider->modelClass)->getTableSchema();
		if($tableSchema === null)
			return false;
		
		// for the first query, find the current ID's row number in the list
		$criteria->select = 't.id';
		
		foreach(explode(',',$criteria->order) as $token) {		// we also need any columns that are being used in the sort
			$token = preg_replace('/\s|asc|desc/i','',$token);	// so loop through $criteria->order and extract them
			if($token !== '' && $token !== 'id' && $token!='t.id'){
                if(strpos($token,'.')!=1){
                    $criteria->select .= ',t.'.$token;
                }else{
                    $criteria->select .= ','.$token;
                }
            }
		}
		
		// always include "id DESC" in sorting (for order consistency with SmartDataProvider)
		if(!preg_match('/\bid\b/',$criteria->order)) {
			if(!empty($criteria->order))
				$criteria->order .= ',';
			$criteria->order .= 't.id DESC';
		}
		
		// get search conditions (WHERE, JOIN, ORDER BY, etc) from the criteria
		$searchConditions = Yii::app()->db->getCommandBuilder()->createFindCommand($tableSchema,$criteria)->getText();
        
		$rowNumberQuery = Yii::app()->db->createCommand(
			'SELECT r-1 FROM (SELECT *,@rownum:=@rownum + 1 AS r FROM ('.$searchConditions.') t1, (SELECT @rownum:=0) r) t2 WHERE t2.id='.$modelId
		);
		// attach params from $criteria to this query
		$rowNumberQuery->params = $criteria->params;
		$rowNumber = $rowNumberQuery->queryScalar();
		
		if($rowNumber === false) {	// the specified record isn't in this list
			return false;
		} else {
			
			$criteria->select = '*';	// need to select everything to be sure ORDER BY will work
			
			if($rowNumber == 0) {	// if we're on the first row, get 2 items, otherwise get 3
				$criteria->offset = 0;
				$criteria->limit = 2;
				$vcrIndex = 0;
			} else {
				$criteria->offset = $rowNumber - 1;
				$criteria->limit = 3;
				$vcrIndex = 1;		// index of current record in $vcrModels
			}
			
			$vcrModels = Yii::app()->db->getCommandBuilder()->createFindCommand($tableSchema,$criteria)->queryAll();
			$count = $dataProvider->getTotalItemCount();
			
			$vcrData = array();
			$vcrData['index'] = $rowNumber + 1;
			$vcrData['count'] = $dataProvider->getTotalItemCount();
			
			/* if($vcrIndex > 0)		// there's a record before the current one
				$vcrData['prev'] = '<li class="prev">'.CHtml::link('<',array('view/'.$vcrModels[0]['id']),array('title'=>$vcrModels[0]['name'],'class'=>'x2-button')).'</li>';
			else
				$vcrData['prev'] = '<li class="prev">'.CHtml::link('<','javascript:void(0);',array('class'=>'x2-button disabled')).'</li>';
			
			if(count($vcrModels) - 1 > $vcrIndex)	// there's a record after the current one
				$vcrData['next'] = '<li class="next">'.CHtml::link('>', array('view/'.$vcrModels[$vcrIndex+1]['id']), array('title'=>$vcrModels[$vcrIndex+1]['name'],'class'=>'x2-button')).'</li>';
			else
				$vcrData['next'] = '<li class="next">'.CHtml::link('>','javascript:void(0);',array('class'=>'x2-button disabled')).'</li>';
			*/
			if($vcrIndex > 0)		// there's a record before the current one
				$vcrData['prev'] = CHtml::link('<',array('view/'.$vcrModels[0]['id']),array('title'=>$vcrModels[0]['name'],'class'=>'x2-button'));
			else
				$vcrData['prev'] = CHtml::link('<','javascript:void(0);',array('class'=>'x2-button disabled'));
			
			if(count($vcrModels) - 1 > $vcrIndex)	// there's a record after the current one
				$vcrData['next'] = CHtml::link('>', array('view/'.$vcrModels[$vcrIndex+1]['id']), array('title'=>$vcrModels[$vcrIndex+1]['name'],'class'=>'x2-button'));
			else
				$vcrData['next'] = CHtml::link('>','javascript:void(0);',array('class'=>'x2-button disabled'));

			return $vcrData;
		}
	}

	/**
	 * Returns an array data provider for all models in the list,
	 * including the list_item status columns
	 * @return CSqlDataProvider
	 */
	public function statusDataProvider($pageSize=null) {
		$tbl = X2Model::model($this->modelName)->tableName();
		$lstTbl = X2ListItem::model()->tableName();
		if ($this->type == 'dynamic') {
			$criteria = $this->queryCriteria();
			$count = X2Model::model($this->modelName)->count($criteria);
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
	 * Return a SQL data provider for a list of emails in a campaign
	 * includes associated contact info with each email
	 * @return CSqlDataProvider
	 */
	public function campaignDataProvider($pageSize=null) {
		$conditions = X2Model::model('Campaign')->getAccessCriteria()->condition;
		$params = array('listId'=>$this->id);
		$sql = Yii::app()->db->createCommand()
			->select('list.*, t.*')
			->from(X2ListItem::model()->tableName().' as list')
			->leftJoin(X2Model::model($this->modelName)->tableName().' t', 'list.contactId=t.id')
			->where('list.listId=:listId AND ('.$conditions.')',array(':listId'=>$this->id))
            ->group('t.id')
			->getText();
			
		return new CSqlDataProvider($sql, array(
			'params'=>$params,
			'pagination'=>array(
				'pageSize'=>!empty($pageSize)? $pageSize : ProfileChild::getResultsPerPage(),
			),
			'sort'=>array(
				//messing with attributes may cause columns to become unsortable
				'attributes'=>array('name','email','phone','address','opened'
				),
				'defaultOrder'=>'opened DESC',
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
	
	/**
	 * Adds the specified ID(s) to this list (if they're not already in there)
	 * @param mixed $ids a single integer or an array of integer IDs
	 */
	public function addIds($ids) {
		if($this->type !== 'static')
			return false;
			
		$ids = (array)$ids;
		
		$existingIds = Yii::app()->db->createCommand()
			->select('contactId')
			->from('x2_list_items')
			->where('listId='.$this->id.' AND contactId IN('.implode(',',$ids).')')		// intersection of $ids and the IDs already in this list
			->queryColumn();
		
		foreach($ids as $id) {
			if(in_array($id,$existingIds))
				continue;
			$listItem = new X2ListItem();
			$listItem->listId = $this->id;
			$listItem->contactId = $id;
			$listItem->save();
		}
		
		$this->count = CActiveRecord::model('X2ListItem')->countByAttributes(array('listId'=>$this->id));
		return $this->update(array('count'));
	}

	/**
	 * Removes the specified ID(s) from this list
	 * @param mixed $ids a single integer or an array of integer IDs
	 */
	public function removeIds($ids) {
		if($this->type !== 'static')
			return false;
		
		$criteria = new CDbCriteria();
		$criteria->compare('listId',$list->id);
		$criteria->addInCondition('contactId',(array)$ids);
		
		// delete all the things!
		if(CActiveRecord::model('X2ListItem')->deleteAll($criteria)) {
			$this->count = CActiveRecord::model('X2ListItem')->countByAttributes(array('listId'=>$this->id));
			return $this->update(array('count'));
		}
	}

	/**
	 * Uses {@link queryCriteria()} to test whether this list contains the specified record ID
	 * @param integer $id the ID of the record
	 * @return boolean whether or not record is in this list
	 */
	public function hasRecord($id) {
		$criteria = $this->queryCriteria(false);	// don't use access rules
		$criteria->compare('id',$id);
		
		return X2Model::model($this->modelName)->exists($criteria);
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
