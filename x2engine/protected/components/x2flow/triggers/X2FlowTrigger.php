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
abstract class X2Trigger {
	/**
	 * $var string the text label for this action
	 */
	public $label = '';
	/**
	 * $var string the description of this action
	 */
	public $info = '';

	/**
	 * Runs the automation action with provided params.
	 * @return boolean the result of the execution
	 */
	abstract public function checkParams();
	
	/**
	 * @return array the param rules.
	 */

	abstract public function paramRules();
	
	/**
	 * Checks if all all the params are ship-shape
	 */
	public function validateRules($params) {
	
	
		
	}

	/**
	 * @param mixed $subject the value being analyzed
	 * @param string $operator the type of comparison to be used
	 * @param mixed $value the value to compare $subject with (if applicable)
	 * @return boolean
	 */
	public static function evalComparison($subject,$operator,$value=null) {
	
		if($operator === 'list' || $operator === 'notList' || $operator === 'between') {	// $value needs to be a comma separated list
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
	
	
	public function parseValue() {
	
	
		return self::parseVariables();
	
	
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
	public function check(&$model) {
		
		// if(!is_array($vars))
			// return false;
			
		// if(!($model instanceof Contacts))
				// return false;
					
		switch($this->type) {

			case 'attribute':
				if(!$model->hasAttribute($this->attribute))
					return false;
				return self::evalComparison($model->getAttribute($this->attribute),$this->operator,$this->parseValue());
				
			case 'current_user':
				return self::evalComparison(Yii::app()->user->getName(),$this->operator,$this->parseValue());
				
			case 'month':
				return self::evalComparison((int)date('n'),$this->operator,$this->parseValue());	// jan = 1, dec = 12
				
			case 'day_of_week':
				return self::evalComparison((int)date('N'),$this->operator,$this->parseValue());	// monday = 1, sunday = 7
				
			case 'time_of_day':
				return self::evalComparison(time() - mktime(0,0,0),$this->operator,$this->parseValue());	// seconds since midnight
				
			case 'current_local_time':
			
			case 'current_time':
				return self::evalComparison(time());
			
			case 'user_active':
				return CActiveRecord::model('Session')->exists('user=:user AND status=1',array(':user'=>$this->value));
				
				
			case 'on_list':
				$list = CActiveRecord::model('X2List')->findByPk($this->value);		// look up specified list
				if($list === null)
					return false;
				$listCriteria = $list->queryCriteria(false); // don't use access rules
				$listCriteria->compare('t.id',$model->id);
				return $model->exists($listCriteria);		// see if this record is on the list
				
			
			case 'workflow_status':
			
				switch($this->operator) {
				
					case 'started_workflow':
					
						// X2Model::model('Actions')->exists(
					case 'started_stage':
					
					case 'completed_workflow':
					
					case 'completed_stage':

				}
			
				break;
		}
		
		// foreach($vars as $key = >$value) {
			
			
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
	 * Runs the automation action with provided params.
	 * @return mixed a class extending X2FlowAction with the specified name
	 */
	public static function create($name) {
		if(class_exists($name))
			return new $name;
		return null;
	}
}