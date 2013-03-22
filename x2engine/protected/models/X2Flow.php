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
class X2Flow extends CActiveRecord {
	/**
	 * @const max number of nested calls to {@link X2Flow::trigger()}
	 */
	const MAX_TRIGGER_DEPTH = 1;
	
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
	 * Sets createDate and lastUpdated
	 */
	public function beforeValidate() {
		$this->lastUpdated = time();
		if($this->isNewRecord)
			$this->createDate = $this->lastUpdated;
		return parent::beforeValidate();
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		return array(
			array('name, createDate, lastUpdated', 'required'),
			array('createDate, lastUpdated', 'numerical', 'integerOnly'=>true),
			array('active', 'boolean'),
			array('name', 'length', 'max'=>100),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, active, name, createDate, lastUpdated', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		return array(
			'flowItems' => array(self::HAS_MANY, 'X2FlowItem', 'flowId'),
			'flowParams' => array(self::HAS_MANY, 'X2FlowParam', 'flowId'),
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
	public static function trigger($trigger,$params=array()) {
		if(self::$_triggerDepth < self::MAX_TRIGGER_DEPTH) {	// ...have we delved too deep?
			self::$_triggerDepth++;	// increment stack depth before doing anything that might call X2Flow::trigger()
			
			$modelClass = '';
			if(isset($params['model']))
				$modelClass = ' ('.get_class($params['model']).' #'.$params['model']->id.')';
			
			$data = array();
			foreach($params as $key => $p) {
				if(is_array($p) || is_object($p))
					$data[] = $key;
				else
					$data[] = $key.' => '.$p;
			}
			
			
			// file_put_contents('triggerLog.txt',$trigger.' -> '.$modelClass.' ['.implode(',',$data)."]\n",FILE_APPEND);
			
			
			
			// $triggerAttributes = array('type'=>$trigger);
			
			// if(isset($params['model']))
				// $triggerAttributes['modelClass'] = get_class($params['model']);
			
			
			// $triggers = CActiveRecord::model('X2FlowItem')->findAllByAttributes($triggerAttributes);
			
			
			
			
			
			
			self::$_triggerDepth--;		// this call is done; decrement the stack depth
		}
	}
	
	
	
	
	
	
	
}