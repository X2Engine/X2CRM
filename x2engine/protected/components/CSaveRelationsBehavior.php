<?php
/**
 * CSaveRelationsBehavior class file.
 *
 * @author Alban Jubert <alban.jubert@trinidev.fr>
 * @link http://www.trinidev.fr/
 * @version 1.0.3
 */

/*
 * The CSaveRelationBehavior enables ActiveRecord model to save HasMany and ManyMany 
 * relational active records along with the main model
 * 
 * Requirements:
 * Yii Framework 1.0.4 or later
 * 
 * Installation:
 * Extract the release file under `protected/components`
 * 
 * Usage:
 * - Add the following code to the models you wish to add this behavior to:
 * public function behaviors(){
 * 		return array('CSaveRelationsBehavior' => array('class' => 'application.components.CSaveRelationsBehavior'));
 * }
 * In your controler, to save the relations data, simply call
 * $model->setRelationRecords('relationName',$data);
 * $model->save();
 * - For ManyMany relations, $data is either an array of foreign key values (ie. array(2,5,43)) or
 * an array of associative arrays giving the composite foreign keys values of the related model
 * (ie. array(array('pk1'=>2,'pk2'=>'fr'),array('pk1'=>5,'pk2'=>'en'))
 * You will typically get this data from some checkboxes values listing the ids of the related model
 * - For HasMany relations, $data should be set as an array of associative arrays giving the attributes values
 * of the related model (ie. array(array('id'=>123, 'name'=>'someValue', 'visible'=>true),
 * array('id'=>456, 'name'=>'someOtherValue', 'visible'=>false));
 * You can get this data by using the tabular input technique within the form of the main model 
 * (http://www.yiiframework.com/doc/guide/form.table)
 * 
 * In both cases, the foreign keys related to the main model will automatically be populated
 * with its primary key(s) value(s).
 * Most of the time, you will call the setRelationRecords that way:
 * $model->setRelationRecords('relationName',is_array(@$_POST['ModelName']) ? $_POST['ModelName'] : array());
 * 
 * By default, the behavior will handle the save operation in a transactional way
 * so that if anything goes wrong during the save of some related data
 * your relational integrity will not be affected.
 * If you prefer to handle this yourself, you can set the 'transactional' property of the behavior to false.
 * Also, if any error occurs during the save process, the hasError property will be set to true.
 * 
 * Additional features:
 * - $model->addSaveRelation('relationName'[,'customErrorMessage'])
 * You can use this method to force the save of some relation. 
 * You can also set the error message of the relation by using the second parameter (see setSaveRelationMessage below)
 * - $model->removeSaveRelation('relationName')
 * Simply do the oposite
 * - $model->setSaveRelationMessage('relationName','customErrorMessage')
 * Set the message to be shown in the error summary of the main model
 */

class CSaveRelationsBehavior extends CActiveRecordBehavior {
	
	public $relations = array();
	public $transactional = true;
	public $hasError = false;
	public $deleteRelatedRecords = true;
	private $transaction;
	
	private function initSaveRelation($relation){
		$model = $this->owner;
		if(!array_key_exists($relation,$model->relations())) 
			throw new CDbException('CSaveRelatedBehavior could not find the "'.$relation.'" relation in the model.');
		if(!array_key_exists($relation,$this->relations)) {
			Yii::trace("Init {$relation} relation",'application.components.CSaveRelatedBehavior');
			$this->relations[$relation]=array();
		}
	}
	
	public function setRelationRecords($relation,$data=null,$merge=false) {
		// TODO - Make fewer SQL requests to validate and load related models data
		$this->addSaveRelation($relation);
		$model = $this->owner;
		$activeRelation = $model->getActiveRelation($relation);
		if($activeRelation instanceOf CHasManyRelation || $activeRelation instanceOf CManyManyRelation) {
			if(!$merge) $model->{$relation} = array();
			$relationClassName = $activeRelation->className;
			$relationForeignKey = $activeRelation->foreignKey;
			$criteria = array();
			if($activeRelation instanceOf CManyManyRelation) {
				$schema = $model->getCommandBuilder()->getSchema();
				preg_match('/^\s*(.*?)\((.*)\)\s*$/',$relationForeignKey,$matches);
				$joinTable=$schema->getTable($matches[1]);
				$fks=preg_split('/[\s,]+/',$matches[2],-1,PREG_SPLIT_NO_EMPTY);
				$relModel = new $relationClassName;
				$pks = array();
				$fkDefined=true;
				foreach($fks as $i=>$fk) {
					if(isset($joinTable->foreignKeys[$fk])) {
						list($tableName,$pk)=$joinTable->foreignKeys[$fk];
						if($schema->compareTableNames($relModel->tableSchema->rawName,$tableName)) {
							$pks[] = $pk;
						}
					}
					else {
						$fkDefined=false;
						break;
					}
				}
				if(!$fkDefined) {
					$pks = array();
					foreach($fks as $i=>$fk)
					{
						if($i<count($model->tableSchema->primaryKey))
						{
							$pks[] = is_array($model->tableSchema->primaryKey) ? $model->tableSchema->primaryKey[$i] : $model->tableSchema->primaryKey;
						}
					}
				}
				if(!is_null($data)) {
					foreach($data as $key=>$value) {
						$relobj = null;
						$relModel = new $relationClassName;
						if(is_array($value)) {
							foreach($pks as $pk) {
								$criteria[$pk] = $value[$pk];
							}
						}
						else {
							$criteria[$pks[0]] = $value;
						}
						$relobj = $relModel->findByAttributes($criteria);
						if(!($relobj instanceof $relationClassName)) $relobj = new $relationClassName;
						$relobj->attributes = $value;
						$model->addRelatedRecord($relation,$relobj,$key);
					}
				}
			}
			else {
				$fks=preg_split('/[\s,]+/',$relationForeignKey,-1,PREG_SPLIT_NO_EMPTY);
				if(!is_null($data)) {
					foreach($data as $key=>$value) {
						$relobj = null;
						if(!$model->isNewRecord) {
							$criteria = array();
							$relModel = new $relationClassName;
							$relationPrimaryKeys = $relModel->tableSchema->primaryKey;
							if(is_array($value)) {
								if(is_array($relationPrimaryKeys)) {
									foreach($relationPrimaryKeys as $relationPrimaryKey){
										if(!in_array($relationPrimaryKey,$fks)) {
											if(isset($value[$relationPrimaryKey])) $criteria[$relationPrimaryKey] = $value[$relationPrimaryKey];
										}
										else {
											$criteria[$relationPrimaryKey] = $model->primaryKey;
										}
									}
								}
								else{
									if(!in_array($relationPrimaryKeys,$fks)) {
										if(isset($value[$relationPrimaryKeys])) $criteria[$relationPrimaryKeys] = $value[$relationPrimaryKeys];
									}
									else {
										$criteria[$relationPrimaryKeys] = $model->primaryKey;
									}
								}
							}
							else {
								$criteria = array($relationPrimaryKeys=>$value);
							}
							if(count($criteria)) $relobj = $relModel->findByAttributes($criteria);
						}
						if(!($relobj instanceof $relationClassName)) $relobj = new $relationClassName;
						foreach($value as $prop=>$val) $relobj->{$prop} = $val;
						$model->addRelatedRecord($relation,$relobj,$key);
					}
				}
			}
		}
	}
	
	public function addSaveRelation($relation,$message=null){
		$this->initSaveRelation($relation);
		$this->relations[$relation] = CMap::mergeArray($this->relations[$relation],array('save'=>true));
		if(!is_null($message)) $this->setSaveRelationMessage($relation,$message);
	}
	
	public function removeSaveRelation($relation){
		$model = $this->owner;
		if(!array_key_exists($relation,$model->relations())) 
			throw new CDbException('CSaveRelatedBehavior could not find the "'.$relation.'" relation in the model.');
		if(array_key_exists($relation,$this->relations)) {
			Yii::trace("Removing {$relation} relation to save",'application.components.CSaveRelatedBehavior');
			$this->relations[$relation] = CMap::mergeArray($this->relations[$relation],array('save'=>false));
		}
	}
	
	public function setRelationScenario($relation,$scenario){
		$this->initSaveRelation($relation);
		$this->relations[$relation] = CMap::mergeArray($this->relations[$relation],array('scenario'=>$scenario));	
	}
	
	public function setSaveRelationMessage($relation,$message) {
		$this->initSaveRelation($relation);
		$this->relations[$relation] = CMap::mergeArray($this->relations[$relation],array('message'=>$message));
	}
	
	public function beforeValidate($event) {
		$model = $this->owner;
		foreach($this->relations as $relation=>$params) {
			if(isset($params['save']) && $params['save']==true) {
				$activeRelation = $model->getActiveRelation($relation);
				$validRelation = true;
				if(!$activeRelation instanceOf CManyManyRelation) {
					foreach($model->{$relation} as $relatedRecord) {
						if(isset($params['scenario'])) $relatedRecord->scenario = $params['scenario'];
						$validRelation = $validRelation && $relatedRecord->validate();
					}
					if(!$validRelation) 
						$model->addError($relation,isset($params['message']) ? $params['message'] : "An error occured during the save of {$relation}");				
				}
				$this->relations[$relation]['valid'] = $validRelation;
			}
		}
	}
	
	public function beforeSave($event) {
		$model = $this->owner;
		$valid =  true;
		foreach($this->relations as $relation=>$params) {
			if(isset($params['save']) && $params['save']==true) {
				$valid = $valid && $this->relations[$relation]['valid'];
			}
		}
		if($valid && $this->transactional && !$model->dbConnection->currentTransaction) {
			Yii::trace("beforeSave start transaction",'application.components.CSaveRelatedBehavior');
			$this->transaction=$model->dbConnection->beginTransaction();
		}
		$event->isValid = $valid;
	}
	
	public function afterSave($event) {
		$model = $this->owner;
		try{
			foreach($this->relations as $relation=>$params) {
				if(isset($params['save']) && $params['save']==true) {
					Yii::trace("saving {$relation} related records.",'application.components.CSaveRelatedBehavior');
					$activeRelation = $model->getActiveRelation($relation);
					$relationClassName = $activeRelation->className;
					$relationForeignKey = $activeRelation->foreignKey;
					$keysToKeep = array();
					if($activeRelation instanceOf CManyManyRelation) {
						// ManyMany relation : save relation to the many to many relation table
						$schema = $model->getCommandBuilder()->getSchema();
						preg_match('/^\s*(.*?)\((.*)\)\s*$/',$relationForeignKey,$matches);
						$joinTable=$schema->getTable($matches[1]);
						$fks=preg_split('/[\s,]+/',$matches[2],-1,PREG_SPLIT_NO_EMPTY);
						$fksFieldNames = array();
						$fksParamNames = array();
						foreach($fks as $fk) {
							$fksFieldNames[] = $schema->quoteColumnName($fk);
							$fksParamNames[] = ':'.$fk;
						}
						$sql="INSERT IGNORE INTO ".$joinTable->rawName." (".implode(', ',$fksFieldNames).") VALUES(".implode(', ',$fksParamNames).")";
						$baseParams = array();
						$baseCriteriaCondition = array();
						reset($fks);
						foreach($fks as $i=>$fk) {
							if(isset($joinTable->foreignKeys[$fk])) {
								list($tableName,$pk)=$joinTable->foreignKeys[$fk];
								if($schema->compareTableNames($model->tableSchema->rawName,$tableName)) {
									$baseCriteriaCondition[$fk] = $baseParams[':'.$fk] = $model->{$pk};
								}
							}
						}
						$relModel = new $relationClassName;
						foreach($model->{$relation} as $idx=>$relatedRecord) {
							$relParams = array();
							reset($fks);
							foreach($fks as $i=>$fk) {
								if(isset($joinTable->foreignKeys[$fk])) {
									list($tableName,$pk)=$joinTable->foreignKeys[$fk];
									if($schema->compareTableNames($relModel->tableSchema->rawName,$tableName)) {
										$keysToKeep[$fk][] = $relParams[':'.$fk] = $relatedRecord->{$pk};
									}
								}
							}
							$model->getCommandBuilder()->createSqlCommand($sql,$baseParams+$relParams)->execute();
						}
						// Delete removed records
						$criteria = new CDbCriteria;
						$criteria->addColumnCondition($baseCriteriaCondition);
						foreach($keysToKeep as $fk=>$values)
							$criteria->addInCondition($fk,$values,'AND NOT');
						$model->getCommandBuilder()->createDeleteCommand($joinTable->name,$criteria)->execute();
					}
					else {
						// HasMany relation : save related models
						foreach($model->{$relation} as $relatedRecord) {
							if($relatedRecord->isNewRecord) {
								if(is_array($relationForeignKey)) {
									foreach($relationForeignKey as $fk) {
										$relatedRecord->{$fk} = $model->primaryKey[$fk];
									}
								}
								else {
									$relatedRecord->{$relationForeignKey} = $model->primaryKey;
								}
							}
							if($relatedRecord->save()) {
								$relationPrimaryKeys = $relatedRecord->tableSchema->primaryKey;
								if(is_array($relationPrimaryKeys)) {
									foreach($relationPrimaryKeys as $relationPrimaryKey){
										if($relationPrimaryKey!=$relationForeignKey) $keysToKeep[$relationPrimaryKey][] = $relatedRecord->{$relationPrimaryKey};
									}
								}
								else{
									$keysToKeep[$relationPrimaryKeys][] = $relatedRecord->{$relationPrimaryKeys};
								}
							}
							else {
								throw new CException("Invalid related record");
							}
						}
						$relatedRecord = new $relationClassName;
						$criteria = new CDbCriteria;
						$criteria->addColumnCondition(array($relationForeignKey=>$model->primaryKey));
						foreach($keysToKeep as $fk=>$values)
							$criteria->addInCondition($fk,$values,'AND NOT');
						$relatedRecord->deleteAll($criteria);
					}
				}
			}
			unset($relation);
			if($this->transactional && $this->transaction) $this->transaction->commit();
		}
		catch(Exception $e)
		{
			Yii::trace("An error occured during the save operation for related records : ".$e->getMessage(),'application.components.CSaveRelatedBehavior');
			$this->hasError = true;
			if(isset($relation)) $model->addError($relation,isset($this->relations[$relation]['message']) ? $this->relations[$relation]['message'] : "An error occured during the save of {$relation}");
			if($this->transactional && $this->transaction) $this->transaction->rollBack();
		}
	}
	
	public function beforeDelete($event) {
		$model = $this->owner;
		if($this->transactional && !$model->dbConnection->currentTransaction) {
			Yii::trace("beforeDelete start transaction",'application.components.CSaveRelatedBehavior');
			$this->transaction=$model->dbConnection->beginTransaction();
		}
	}
	
	public function afterDelete($event) {
		if($this->deleteRelatedRecords) {
			$model = $this->owner;
			try{
				foreach($model->relations() as $relation=>$params) {
					$activeRelation = $model->getActiveRelation($relation);
					if(is_object($activeRelation) && ($activeRelation instanceOf CManyManyRelation || $activeRelation instanceOf CHasManyRelation || $activeRelation instanceOf CHasOneRelation)) {
						Yii::trace("deleting {$relation} related records.",'application.components.CSaveRelatedBehavior');
						$relationClassName = $activeRelation->className;
						$relationForeignKey = $activeRelation->foreignKey;
						if($activeRelation instanceOf CManyManyRelation) {
							// ManyMany relation : delete related records from the many to many relation table
							$schema = $model->getCommandBuilder()->getSchema();
							preg_match('/^\s*(.*?)\((.*)\)\s*$/',$relationForeignKey,$matches);
							$joinTable=$schema->getTable($matches[1]);
							$fks=preg_split('/[\s,]+/',$matches[2],-1,PREG_SPLIT_NO_EMPTY);
							$baseParams = array();
							$baseCriteriaCondition = array();
							reset($fks);
							foreach($fks as $i=>$fk) {
								if(isset($joinTable->foreignKeys[$fk])) {
									list($tableName,$pk)=$joinTable->foreignKeys[$fk];
									if($schema->compareTableNames($model->tableSchema->rawName,$tableName)) {
										$baseCriteriaCondition[$fk] = $baseParams[':'.$fk] = $model->{$pk};
									}
								}
							}
							// Delete records
							$criteria = new CDbCriteria;
							$criteria->addColumnCondition($baseCriteriaCondition);
							$model->getCommandBuilder()->createDeleteCommand($joinTable->name,$criteria)->execute();
						}
						else {
							// HasMany & HasOne relation : delete related records
							$relatedRecord = new $relationClassName;
							$criteria = new CDbCriteria;
							$criteria->addColumnCondition(array($relationForeignKey=>$model->primaryKey));
							$relatedRecord->deleteAll($criteria);
						}
					}
				}
				unset($relation);
				if($this->transactional && $this->transaction) $this->transaction->commit();
			}
			catch(Exception $e)
			{
				Yii::trace("An error occured during the delete operation for related records : ".$e->getMessage(),'application.components.CSaveRelatedBehavior');
				$this->hasError = true;
				if(isset($relation)) $model->addError($relation,"An error occured during the delete operation of {$relation}");
				if($this->transactional && $this->transaction) $this->transaction->rollBack();
			}
		}
	}
}