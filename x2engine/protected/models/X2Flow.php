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
 * This is the model class for table "x2_flows".
 *
 * The followings are the available columns in table 'x2_flows':
 * @property integer $id
 * @property integer $active
 * @property string $name
 * @property string $createDate
 *
 * The followings are the available model relations:
 * @property FlowItems[] $flowItems
 * @property FlowParams[] $flowParams
 */

Yii::import('application.components.x2flow.actions.*');
Yii::import('application.components.x2flow.triggers.*');

class X2Flow extends CActiveRecord {
	/**
	 * @const max number of nested calls to {@link X2Flow::trigger()}
	 */
	const MAX_TRIGGER_DEPTH = 0;
	
	/**
	 * @var the current depth of nested trigger calls
	 */
	protected static $_triggerDepth = 0;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return X2Flow the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_flows';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		return array(
			array('name, createDate, lastUpdated, triggerType, flow', 'required'),
			array('createDate, lastUpdated', 'numerical', 'integerOnly'=>true),
			array('active', 'boolean'),
			array('name', 'length', 'max'=>100),
			array('triggerType, modelClass', 'length', 'max'=>40),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, active, name, createDate, lastUpdated', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * Returns a list of behaviors that this model should behave as.
	 * @return array the behavior configurations (behavior name=>behavior configuration)
	 */
	public function behaviors() {
		return array(
			'X2TimestampBehavior' => array('class'=>'X2TimestampBehavior'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => 'ID',
			'active' => 'Active',
			'name' => 'Name',
			'triggerType' => 'Trigger',
			'modelClass' => 'Type',
			'name' => 'Name',
			'createDate' => 'Create Date',
			'lastUpdated' => 'Last Updated',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('active',$this->active);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('createDate',$this->createDate,true);
		$criteria->compare('lastUpdated',$this->lastUpdated,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Validates the JSON data in $flow.
	 * Sets createDate and lastUpdated.
	 * @return boolean whether or not to proceed to validation
	 */
	public function beforeValidate() {
		$flowData = CJSON::decode($this->flow);
		
		if($flowData === false) {
			$this->addError('flow',Yii::t('studio','Flow configuration data appears to be corrupt.'));
			return false;
		}
		if(isset($flowData['trigger']['type'])) {
			$this->triggerType = $flowData['trigger']['type'];
			if(isset($flowData['trigger']['modelClass']))
				$this->modelClass = $flowData['trigger']['modelClass'];
		} else {
			// $this->addError('flow',Yii::t('studio','You must configure a trigger event.'));
		}
		if(!isset($flowData['items']) || empty($flowData['items'])) {
			$this->addError('flow',Yii::t('studio','There must be at least one action in the flow.'));
		}
		
		$this->lastUpdated = time();
		if($this->isNewRecord)
			$this->createDate = $this->lastUpdated;
		return parent::beforeValidate();
	}
	
	
	/**
	 * Returns the current trigger stack depth {@link X2Flow::$_triggerDepth}
	 * @return int the stack depth
	 */
	public static function triggerDepth() {
		return self::$_triggerDepth;
	}
	
	/**
	 * Looks up and runs automation actions that match the provided trigger name and parameters.
	 *
	 * @param string $trigger the name of the trigger to fire
	 * @param array $params an associative array of params, usually including 'model'=>$model, 
	 * the primary X2Model to which this trigger applies.
	 * @staticvar int $triggerDepth the current depth of the call stack
	 */
	public static function trigger($triggerName,$params=array()) {
		if(self::$_triggerDepth > self::MAX_TRIGGER_DEPTH)	// ...have we delved too deep?
			return;
		
		self::$_triggerDepth++;	// increment stack depth before doing anything that might call X2Flow::trigger()
		
		$flowAttributes = array('triggerType'=>$triggerName);
		
		if(isset($params['model']))
			$flowAttributes['modelClass'] = get_class($params['model']);
		
		
		// file_put_contents('triggerLog.txt',"\n".$triggerName,FILE_APPEND);
		
		$flowTraces = array();
		
		// find all flows matching this trigger and modelClass
		foreach(CActiveRecord::model('X2Flow')->findAllByAttributes($flowAttributes) as $flow) {
			
			$error = '';	//array($flow->name);
			
			$flowData = CJSON::decode($flow->flow);	// parse JSON flow data
			// file_put_contents('triggerLog.txt',"\n".print_r($flowData,true),FILE_APPEND);
			if($flowData !== false && isset($flowData['trigger']['type'],$flowData['items'][0]['type'])) {
				
				$trigger = X2FlowTrigger::create($flowData['trigger']);
				if($trigger === null)
					$error = 'failed to load trigger class';
				if(!$trigger->validateRules($params))
					$error = 'invalid rules/params';
				if(!$trigger->check($params))
					$error = 'conditions not passed';
				
				if(empty($error)) {
					try {
						$flowTraces[] = array($flow->name,$flow->executeBranch($flowData['items'],$params));
					} catch (Exception $e) {
						// whatever.
					}
					
				} else
					$flowTraces[] = array($flow->name,$error);
			} else {
				$flowTraces[] = array($flow->name,'invalid flow data');
			}
		}
		// var_dump($flowTraces);
		// echo '<div class="flowTrace">';
		
		
		// file_put_contents('triggerLog.txt',$triggerName.":\n",FILE_APPEND);
		// file_put_contents('triggerLog.txt',print_r($flowTraces,true).":\n",FILE_APPEND);
		
		
		// var_dump($params['model']->getChanges());
		// echo '</div>';
		// if(!empty($flowTraces))
			// die();
		// $modelClass = '';
		// if(isset($params['model']))
			// $modelClass = ' ('.get_class($params['model']).' #'.$params['model']->id.')';
		
		// file_put_contents('triggerLog.txt',"\n".$triggerName.' -> '.$modelClass.' ['.implode(',',array_keys($params))."]\n",FILE_APPEND);
		// if(!empty($results))
			// file_put_contents('triggerLog.txt','	'.print_r($results,true)."\n",FILE_APPEND);
		
		
		
		
		self::$_triggerDepth--;		// this trigger call is done; decrement the stack depth
	}
	
	public function executeBranch(&$items,&$params) {
		$results = array();
		
		foreach($items as &$item) {
			if(!isset($item['type']) || !class_exists($item['type']))
				continue;
			
			if($item['type'] === 'X2FlowSwitch') {
				$switch = Trigger::create($item);
				if($switch->validateRules($params)) {
					if($switch->check($params) && isset($item['trueBranch']))
						$results[] = array($item['type'],true,$this->executeBranch($item['trueBranch']));
					elseif(isset($item['falseBranch']))
						$results[] = array($item['type'],false,$this->executeBranch($item['falseBranch']));
				}
			} else {
				$flowAction = X2FlowAction::create($item);
				$results[] = array($item['type'],$flowAction->validateRules($params) && $flowAction->execute($params));
			}
		}
		return $results;
	}
	
	/* 
	 * Parses variables in curly brackets and evaluates expressions
	 * 
	 * @param mixed $value the value as specified by 'attributes' in {@link X2FlowAction::$config}
	 * @param string $type the X2Fields type for this value
	 * @return mixed the parsed value
	 */
	public static function parseValue($value,$type,&$model=null) {
		
		if($model !== null) {
			$matches = array();
			preg_match('/^{([a-z]\w*)}$/i',trim($value),$matches);	// check for a variable
			if(isset($matches[1]) && $model->hasAttribute($matches[1]))
				return $model->renderAttribute($matches[1]);
		}
		// replaceVariables($str, &$model, $vars = array(), $encode = false)
		
		switch($type) {
			case 'boolean':
				return (bool)$value;
			case 'time':
			case 'date':
			case 'dateTime':
				if(ctype_digit((string)$value))		// must already be a timestamp
					return $value;
				if($type === 'date')
					$value = Formatter::parseDate($value);
				elseif($type === 'dateTime')
					$value = Formatter::parseDateTime($value);
				else
					$value = strtotime($value);
				return $value === false? null : $value;
			case 'link':
				$pieces = explode(':',$value);
				if(count($pieces) > 1)
					return (int)$pieces[0];
				return $value;
			default:
				return $value;
		}
	}
}