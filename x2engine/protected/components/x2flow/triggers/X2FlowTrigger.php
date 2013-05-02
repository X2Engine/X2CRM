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
 * Color utilities (unused)
 *
 * @package X2CRM.components.x2flow
 */
abstract class X2FlowTrigger {
	/**
	 * $var string the text label for this action
	 */
	public $label = '';
	/**
	 * $var string the description of this action
	 */
	public $info = '';
	/**
	 * $var array the config parameters for this trigger
	 */
	public $config = '';
	/**
	 * $var string the type of notification to create
	 */
	public $notifType = '';
	/**
	 * $var string the type of event to create
	 */
	public $eventType = '';

	/**
	 * @return array the param rules.
	 */
	abstract public function paramRules();

	/**
	 * @return array all standard comparison operators
	 */
	public static function getComparisonOptions() {
		return array(
			'=' => Yii::t('studio','equals'),
			'>' => Yii::t('studio','greater than'),
			'<' => Yii::t('studio','less than'),
			'<>' => Yii::t('studio','not equal to'),
			'list' => Yii::t('studio','in list'),
			'notList' => Yii::t('studio','not in list'),
			'empty' => Yii::t('studio','empty'),
			'notEmpty' => Yii::t('studio','not empty'),
			'contains' => Yii::t('studio','contains'),
			'noContains' => Yii::t('studio','does not contain'),
			'changed' => Yii::t('studio','changed'),
		);
	}

/* 	public static function getGenericConditions() {
		return array(
			'attribute'			=> Yii::t('studio','Compare Attribute'),
			'current_user'		=> Yii::t('studio','Current User'),
			'month'				=> Yii::t('studio','Current Month'),
			'day_of_week'		=> Yii::t('studio','Day of Week'),
			'time_of_day'		=> Yii::t('studio','Time of Day'),
			'current_time'		=> Yii::t('studio','Current Timestamp'),
			'user_active'		=> Yii::t('studio','User Active?'),
			'on_list'			=> Yii::t('studio','On List'),
			'workflow_status'	=> Yii::t('studio','Workflow Status'),
			// 'current_local_time' => Yii::t('studio',''),
		);
	} */

	public static $genericConditions = array(
		'attribute'			=> 'Compare Attribute',
		'current_user'		=> 'Current User',
		'month'				=> 'Current Month',
		'day_of_week'		=> 'Day of Week',
		'day_of_month'		=> 'Day of Month',
		'time_of_day'		=> 'Time of Day',
		'current_time'		=> 'Current Timestamp',
		'user_active'		=> 'User Active?',
		'on_list'			=> 'On List',
		// 'workflow_status'	=> 'Workflow Status',
		// 'current_local_time' => ''),
	);

	public static function getGenericCondition($type) {
		switch($type) {
			case 'current_user':
				return array(
					'name'=>'user',
					'label'=>Yii::t('studio','Current User'),
					'type' => 'dropdown',
					'multiple' => 1,
					'options' => X2Model::getAssignmentOptions(false,false),
					'operators'=>array('=','<>','list','notList','contains','noContains')
				);

			case 'month':
				return array(
					'label'=>Yii::t('studio','Current Month'),
					'type' => 'dropdown',
					'multiple' => 1,
					'options' => Yii::app()->locale->monthNames,
					'operators'=>array('=','<>','list','notList','contains','noContains')
				);

			case 'day_of_week':
				return array(
					'label'=>Yii::t('studio','Day of Week'),
					'type' => 'dropdown',
					'multiple' => 1,
					'options' => Yii::app()->locale->weekDayNames,
					'operators'=>array('=','<>','list','notList','contains','noContains')
				);

			case 'day_of_month':
				$days = array_keys(array_fill(1,31,1));
				return array(
					'label'=>Yii::t('studio','Day of Month'),
					'type' => 'dropdown',
					'multiple' => 1,
					'options' => array_combine($days,$days),
					'operators'=>array('=','<>','list','notList','contains','noContains')
				);

			case 'time_of_day':
				return array(
					'label'=>Yii::t('studio','Time of Day'),
					'type'=>'time',
					'operators'=>array('<','>')
				);

			// case 'current_local_time':

			case 'current_time':
				return array(
					'label'=>Yii::t('studio','Current Timestamp'),
					'type'=>'dateTime',
					'operators'=>array('<','>')
				);

			case 'user_active':
				return array(
					'label'=>Yii::t('studio','User Active?'),
					'type' => 'dropdown',
					'options' => X2Model::getAssignmentOptions(false,false)
				);

			case 'on_list':
				return array(
					'label'=>Yii::t('studio','On List'),
					'type'=>'lookup',
					'linkType'=>'X2List',
					'linkSource'=>Yii::app()->controller->createUrl(CActiveRecord::model('X2List')->autoCompleteSource)
				);

			default:
				return false;
			// case 'workflow_status':
				// return array(
					// 'label'=>Yii::t('studio','Workflow Status'),


				// 'workflowId',
				// 'stageNumber',

					// 'started_workflow',
					// 'started_stage',
					// 'completed_stage',
					// 'completed_workflow', */
		}
	}

	/**
	 * Checks if all all the params are ship-shape
	 */
	public function validateRules(&$params) {
		$rules = $this->paramRules();
		if(!isset($rules['options'],$this->config['options']))
			return false;
		$options = &$this->config['options'];
		
		if(isset($rules['modelClass'])) {
			if(isset($params['model']) && !is_object($params['model']) || !($params['model'] instanceof X2Model))	// validate model parameter
				return false;
			$modelClass = $rules['modelClass'];
			if($modelClass === 'modelClass') {
				if(isset($options['modelClass']))
					$modelClass = $options['modelClass'];
				else
					return false;
			}
			if($modelClass !== get_class($params['model']))
				return false;
		}
		
		foreach($rules['options'] as &$opt) {	// loop through options defined in paramRules() and make sure they're all set in $config
			if(!isset($opt['name']) || isset($opt['optional']) && $opt['optional'])		// don't worry about unnamed or optional params
				continue;
			
			if(!isset($options[$opt['name']]))
				return false;
		}
		return true;
	}

	/**
	 * Tests this trigger's conditions against the provided params.
	 * @return boolean the result of the test
	 */
	public function check(&$params) {
		if(isset($this->config['conditions'])){
			foreach($this->config['conditions'] as &$condition) {

				if(!isset($condition['type']))
					continue;

				$required = isset($condition['required']);

				if(isset($condition['name']) && $required && !isset($params[$condition['name']]))	// required param missing
					return false;


				if(array_key_exists($condition['type'],self::$genericConditions)) {
					if(!self::checkCondition($condition,$params))
						return false;
				}
			}
		}
		return true;
	}

	/**
	 * @param Array $condition
	 * @param Array $params
	 * @return
	 */
	public static function checkCondition($condition,&$params) {

		$model = isset($params['model'])? $params['model'] : null;
		$operator = isset($condition['operator'])? $condition['operator'] : '=';
		// $type = isset($condition['type'])? $condition['type'] : null;
		$value = isset($condition['value'])? $condition['value'] : null;
		
		if(isset($condition['name']) && $type === null) {	// default to a doing basic value comparison
			if(!isset($params[$condition['name']]))
				return false;
			
			return self::evalComparison($params[$condition['name']],$operator,$value);
		}
		
		switch($condition['type']) {
			case 'attribute':
				if(!isset($condition['name'],$model))
					return false;
				$attr = &$condition['name'];
				if(null === $field = $model->getField($attr))
					return false;
				
				if($operator === 'changed') {
					$oldAttributes = $model->getOldAttributes();
					return !isset($oldAttributes[$attr]) || $model->getAttribute($attr) != $oldAttributes[$attr];
				}
				
				return self::evalComparison($model->getAttribute($attr),$operator,X2Flow::parseValue($value,$field->type,$model));
				
			case 'current_user':
				return self::evalComparison(Yii::app()->user->getName(),$operator,X2Flow::parseValue($value,'assignment',$model));
				
			case 'month':
				return self::evalComparison((int)date('n'),$operator,$value);	// jan = 1, dec = 12
				
			case 'day_of_month':
				return self::evalComparison((int)date('j'),$operator,$value);	// 1 through 31
				
			case 'day_of_week':
				return self::evalComparison((int)date('N'),$operator,$value);	// monday = 1, sunday = 7
			
			case 'time_of_day':	// - mktime(0,0,0)
				return self::evalComparison(time(),$operator,X2Flow::parseValue($value,'time',$model));	// seconds since midnight
				
			// case 'current_local_time':
				
			case 'current_time':
				return self::evalComparison(time(),$operator,X2Flow::parseValue($value,'dateTime',$model));
				
			case 'user_active':
				return CActiveRecord::model('Session')->exists('user=:user AND status=1',array(':user'=>X2Flow::parseValue($value,'assignment',$model)));
				
				
			case 'on_list':
				if(!isset($model,$value))
					return false;
				$list = CActiveRecord::model('X2List')->findByPk($value);		// look up specified list
				if($list === null || $list->modelName !== get_class($model))
					return false;
				$listCriteria = $list->queryCriteria(false); // don't use access rules
				$listCriteria->compare('t.id',$model->id);
				return $model->exists($listCriteria);		// see if this record is on the list
				
				
			case 'workflow_status':
				if(!isset($model,$condition['workflowId'],$condition['stageNumber']))
					return false;
				
				switch($operator) {
					case 'started_workflow':
						return CActiveRecord::model('Actions')->exists(
							'associationType=:type AND associationId=:modelId AND type="workflow" AND workflowId=:workflow',array(
								':type' => get_class($model),
								':modelId' => $model->id,
								':workflow' => $condition['workflowId'],
							));
					case 'started_stage':
						return CActiveRecord::model('Actions')->exists(
							'associationType=:type AND associationId=:modelId AND type="workflow" AND workflowId=:workflow AND stageNumber=:stage AND (completeDate IS NULL OR completeDate=0)',array(
								':type' => get_class($model),
								':modelId' => $model->id,
								':workflow' => $condition['workflowId'],
								':stageNumber' => $condition['stageNumber'],
							));
					case 'completed_stage':
						return CActiveRecord::model('Actions')->exists(
							'associationType=:type AND associationId=:modelId AND type="workflow" AND workflowId=:workflow AND stageNumber=:stage AND completeDate > 0',array(
								':type' => get_class($model),
								':modelId' => $model->id,
								':workflow' => $condition['workflowId'],
								':stageNumber' => $condition['stageNumber'],
							));
					case 'completed_workflow':
						$stageCount = CActiveRecord::model('WorkflowStage')->count('workflowId=:id',array(':id'=>$condition['workflowId']));
						$actionCount = CActiveRecord::model('Actions')->count(
							'associationType=:type AND associationId=:modelId AND type="workflow" AND workflowId=:workflow',array(
								':type' => get_class($model),
								':modelId' => $model->id,
								':workflow' => $condition['workflowId'],
							));
						return $actionCount >= $stageCount;
					default:
						return false;
				}
			return false;
		}

		// foreach($condition as $key = >$value) {


			// Record attribute (=, <, >, <>, in list, not in list, empty, not empty, contains)
			// Linked record attribute (eg. a contact's account has > 30 employees)
			// Current user
			// Current time (day of week, hours, etc)
			// Current time in record's timezone
			// Is user X logged in
			// Workflow status (in workflow X, started stage Y, completed Y, completed all)

		// }
	}

	/**
	 * @param mixed $subject the value being analyzed
	 * @param string $operator the type of comparison to be used
	 * @param mixed $value the value to compare $subject with (if applicable)
	 * @return boolean
	 */
	public static function evalComparison($subject,$operator,$value=null) {

		if(in_array($operator,array('list','notList','between'),true) && !is_array($value)) {	// $value needs to be a comma separated list
			$value = explode(',',$value);

			$len = count($value);
			for($i=0;$i<$len; $i++)
				if(($value[$i] = trim($value[$i])) === '')		// loop through the values, trim and remove empty strings
					unset($value[$i]);
		}

		switch($operator) {
			case '=':
				return $subject == $value;

			case '>':
				return $subject >= $value;

			case '<':
				return $subject <= $value;

			case 'between':
				if(count($value) !== 2)
					return false;
				return $subject >= min($value) && $subject <= max($value);

			case '<>':
			case '!=':
				return $subject != $value;

			case 'notEmpty':
				return $subject !== null && $subject !== '';

			case 'empty':
				return $subject === null || trim($subject) === '';

			case 'list':
				if(count($value) === 0)	// if the list is empty,
					return false;								// A isn't in it
				foreach($value as &$val)
					if($subject == $val)
						return true;

				return false;

			case 'notList':
				if(count($value) === 0)	// if the list is empty,
					return true;								// A isn't *not* in it
				foreach($value as &$val)
					if($subject == $val)
						return false;

				return true;

			case 'noContains':
				return stripos($subject,$value) === false;

			case 'contains':
			default:
				return stripos($subject,$value) !== false;
		}
	}

	// const T_VAR = 0;
	// const T_SPACE = 1;
	// const T_COMMA = 2;
	// const T_OPEN_BRACKET = 3;
	// const T_CLOSE_BRACKET = 4;
	// const T_PLUS = 5;
	// const T_MINUS = 6;
	// const T_TIMES = 7;
	// const T_DIVIDE = 8;
	// const T_OPEN_PAREN = 9;
	// const T_CLOSE_PAREN = 10;
	// const T_NUMBER = 11;
	// const T_ERROR = 12;

	protected static $_tokenChars = array(
		',' => 'COMMA',
		'{' => 'OPEN_BRACKET',
		'}' => 'CLOSE_BRACKET',
		'+' => 'ADD',
		'-' => 'SUBTRACT',
		'*' => 'MULTIPLY',
		'/' => 'DIVIDE',
		'%' => 'MOD',
		// '(' => 'OPEN_PAREN',
		// ')' => 'CLOSE_PAREN',
	);
	protected static $_tokenRegex = array(
		'\d+\.\d+\b|^\.?\d+\b' => 'NUMBER',
		'[a-zA-Z]\w*\.[a-zA-Z]\w*' => 'VAR_COMPLEX',
		'[a-zA-Z]\w*' => 'VAR',
		'\s+' => 'SPACE',
		'.' => 'UNKNOWN',
	);

	/**
	 * Breaks a string expression into an array of 2-element arrays (type, value)
	 * using {@link $_tokenChars} and {@link $_tokenRegex} to identify tokens
	 * @param string $str the input expression
	 * @return array a flat array of tokens
	 */
	protected static function tokenize($str) {
		$tokens = array();
		$offset = 0;
		while($offset < mb_strlen($str)) {
			$token = array();

			$substr = mb_substr($str,$offset);	// remaining string starting at $offset

			foreach(self::$_tokenChars as $char => &$name) {	// scan single-character patterns first
				if(mb_substr($substr,0,1) === $char) {
					$tokens[] = array($name);	// add it to $tokens
					$offset++;
					continue 2;
				}
			}
			foreach(self::$_tokenRegex as $regex => &$name) {	// now loop through regex patterns
				$matches = array();
				if(preg_match('/^'.$regex.'/u',$substr,$matches) === 1) {
					$tokens[] = array($name,$matches[0]);	// add it to $tokens
					$offset += mb_strlen($matches[0]);
					continue 2;
				}
			}
			$offset++;	// no infinite looping, yo
		}
		return $tokens;
	}

	/**
	 * Adds a new node at the end of the specified branch
	 * @param array &$tree the tree object
	 * @param array $nodePath array of branch indeces leading to the target branch
	 * @value array an array containing the new node's type and value
	 */
	protected static function addNode(&$tree,$nodePath,$value) {
		if(count($nodePath) > 0)
			return self::addNode($tree[array_shift($nodePath)],$nodePath,$value);

		$tree[] = $value;
		return count($tree) - 1;
	}

	/**
	 * Checks if this branch has only one node and eliminates it by moving the child node up one level
	 * @param array &$tree the tree object
	 * @param array $nodePath array of branch indeces leading to the target node
	 */
	protected static function simplifyNode(&$tree,$nodePath) {
		if(count($nodePath) > 0)													// before doing anything, recurse down the tree using $nodePath
			return self::simplifyNode($tree[array_shift($nodePath)],$nodePath);		// to get to the targeted node

		$last = count($tree) - 1;

		if(empty($tree[$last][1]))
			array_pop($tree);
		elseif(count($tree[$last][1]) === 1)
			$tree[$last] = $tree[$last][1][0];
	}

	/**
	 * Processes the expression tree and attempts to evaluate it
	 * @param array &$tree the tree object
	 * @param boolean $expression
	 * @return mixed the value, or false if the tree was invalid
	 */
/* 	protected static function parseExpression(&$tree,$expression=false) {

		$answer = 0;

		// echo '1';
		for($i=0;$i<count($tree);$i++) {
			$prev = isset($tree[$i+1])? $tree[$i+1] : false;
			$next = isset($tree[$i+1])? $tree[$i+1] : false;


			switch($tree[$i][0]) {

				case 'VAR':
				case 'VAR_COMPLEX':
					continue 2;

				case 'EXPRESSION':	// please
					$subresult = self::parseExpression($tree[$i][1],true);	// the expression itself must be valid
					if($subresult === false)
						return $subresult;

					// if($next !== false)
					break;

				case 'EXPONENT':	// excuse
					break;

				case 'MULTIPLY':	// my
					break;

				case 'DIVIDE':	// dear
					break;

				case 'MOD':
					break;


				case 'ADD':	// aunt
					break;

				case 'SUBTRACT':	// sally
					break;

				case 'COMMA':

					break;
				case 'NUMBER':
					break;


				case 'SPACE':

				case 'UNKNOWN':
					return 'Unrecognized entity: "'.$tree[$i][1].'"';

				default:
					return 'Unknown entity type: "'.$tree[$i][0].'"';
			}
		}
		return true;
	} */

	/**
	 * @param String $str string to be parsed into an expression tree
	 * @return mixed a variable depth array containing pairs of entity
	 * types and values, or a string containing an error message
	 */
	public static function parseExpressionTree($str) {

		$tokens = self::tokenize($str);

		$tree = array();
		$nodePath = array();
		$error = false;

		for($i=0;$i<count($tokens);$i++) {
			switch($tokens[$i][0]) {
				case 'OPEN_BRACKET':
					$nodePath[] = self::addNode($tree,$nodePath,array('EXPRESSION',array()));	// add a new expression node, get its offset in the current branch,
					$nodePath[] = 1;	// then move down to its 2nd element (1st element is the type, i.e. 'EXPRESSION')
					break;
				case 'CLOSE_BRACKET':
					if(count($nodePath) > 1) {
						$nodePath = array_slice($nodePath,0,-2);	// set node path to one level higher
						self::simplifyNode($tree,$nodePath);		// we're closing an expression node; check to see if its empty or only contains one thing

					} else {
						$error = 'unbalanced brackets';
					}
					break;

				case 'SPACE': break;
				default:
					self::addNode($tree,$nodePath,$tokens[$i]);
			}
		}

		if(count($nodePath) !== 0)
			$error = 'unbalanced brackets';

		if($error !== false)
			return 'ERROR: '.$error;
		else
			return $tree;
	}

	/**
	 * @param Array $vars variables to be used in this param's calculations
	 * @return
	 */
	// public static function checkCondition($vars) {
	// }


	/**
	 * Gets the param rules for the specified trigger type
	 * @param string $type name of action class
	 * @return mixed an array of param rules, or false if the trigger doesn't exist
	 */
	public static function getParamRules($type) {
		$trigger = self::create(array('type'=>$type));
		if($trigger !== null)
			return $trigger->paramRules();
		return false;
	}

	public static function getTriggerTypes() {
		$types = array();
		foreach(scandir(Yii::getPathOfAlias('application.components.x2flow.triggers')) as $file) {
			if($file === '.' || $file === '..' || $file === 'X2FlowTrigger.php' || $file === 'X2FlowSwitch.php')
				continue;
			$class = self::create(array('type'=>substr($file,0,-4)));	// remove file extension and create instance
			if($class !== null)
				$types[get_class($class)] = $class->title;
		}
		return $types;
	}

	/**
	 * Instantiates a trigger object specified by $config
	 * @return mixed a class extending X2FlowTrigger with the specified name
	 */
	public static function create($config) {
		if(isset($config['type']) && class_exists($config['type'])) {
			$trigger = new $config['type'];
			$trigger->config = $config;
			return $trigger;
		}
		return null;
	}
}