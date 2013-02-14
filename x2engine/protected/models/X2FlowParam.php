<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
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
 * This is the model class for table "x2_flow_params".
 *
 * The followings are the available columns in table 'x2_flow_params':
 * @property integer $id
 * @property integer $flowId
 * @property integer $itemId
 * @property string $type
 * @property string $value
 *
 * The followings are the available model relations:
 * @property Flow $flow
 * @property FlowItem $item
 */
class X2FlowParam extends CActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return FlowParam the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_flow_params';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		return array(
			array('flowId, itemId, type', 'required'),
			array('flowId, itemId', 'numerical', 'integerOnly'=>true),
			array('attribute', 'length', 'max'=>100),
			array('type, operator', 'length', 'max'=>40),
			array('value', 'length', 'max'=>500),
			array('id, flowId, itemId, type, operator, attribute, value', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		return array(
			'flow' => array(self::BELONGS_TO, 'X2Flow', 'flowId'),
			'item' => array(self::BELONGS_TO, 'X2FlowItem', 'itemId'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => 'ID',
			'flowId' => 'Flow',
			'itemId' => 'Item',
			'type' => 'Type',
			'operator' => 'Operator',
			'attribute' => 'Attribute',
			'value' => 'Value',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('flowId',$this->flowId);
		$criteria->compare('itemId',$this->itemId);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
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
		'+' => 'PLUS',
		'-' => 'MINUS',
		'*' => 'TIMES',
		'/' => 'DIVIDE',
		'(' => 'OPEN_PAREN',
		')' => 'CLOSE_PAREN',
	);
	protected static $_tokenRegex = array(
		'\d+\.\d+\b|^\.?\d+\b' => 'NUMBER',
		'[a-zA-Z]\w*\.[a-zA-Z]\w*' => 'VAR_COMPLEX',
		'[a-zA-Z]\w*' => 'VAR',
		'\s+' => 'SPACE',
		'.' => 'UNKNOWN',
	);
	
	protected static function tokenize($str) {
		$tokens = array();
		$offset = 0;
		while($offset < strlen($str)) {
			$token = array();
			
			$substr = substr($str,$offset);	// remaining string starting at $offset
		
			foreach(self::$_tokenChars as $char => &$name) {	// scan single-character patterns first
				if(substr($substr,0,1) === $char) {
					$tokens[] = array($name);	// add it to $tokens
					$offset++;
					continue 2;
				}
			}
			foreach(self::$_tokenRegex as $regex => &$name) {	// now loop through regex patterns
				$matches = array();
				if(preg_match('/^'.$regex.'/',$substr,$matches) === 1) {
					$tokens[] = array($name,$matches[0]);	// add it to $tokens
					$offset += strlen($matches[0]);
					continue 2;
				}
			}
			$offset++;	// no infinite looping, yo
		}
		return $tokens;
	}
	
	
	
	protected static function addNode(&$tree,$nodePath,$value) {		//if(isset($tree[$nodePath[0]]))
		if(count($nodePath) > 0)
			return self::addNode($tree[array_shift($nodePath)],$nodePath,$value);
		
		$tree[] = $value;
		return count($tree) - 1;
	}
	
	protected static function simplifyNode(&$tree,$nodePath) {	// traverse to the specified node
		if(count($nodePath) > 0)
			return self::simplifyNode($tree[array_shift($nodePath)],$nodePath);
			
		$last = count($tree) - 1;
		
		if(empty($tree[$last][1]))
			array_pop($tree);
		elseif(count($tree[$last][1]) === 1)
			$tree[$last] = $tree[$last][1][0];
	}
	
	protected static function validateExpression(&$tree) {
		// echo '1';
		for($i=0;$i<count($tree);$i++) {
			$prev = isset($tree[$i+1])? $tree[$i+1] : false;
			$next = isset($tree[$i+1])? $tree[$i+1] : false;
		
			
			switch($tree[$i][0]) {
				case 'UNKNOWN':
					return 'Unrecognized entity: "'.$tree[$i][1].'"';
					
				case 'EXPRESSION':
					$subresult = self::validateExpression($tree[$i][1]);
					if($subresult !== true)
						return $subresult; 
					break;
				
				case 'VAR':
				case 'VAR_COMPLEX':
					// if($next[0] === false || $next[0] === 'VAR')
						// return '2 variables next to each other, fool!'.$next[1];
					
					break;
				case 'COMMA':
					break;
				case 'PLUS':
					break;
				case 'MINUS':
					break;
				case 'TIMES':
					break;
				case 'DIVIDE':
					break;
					break;
				case 'NUMBER':
					break;

					
				case 'SPACE':
				default:
					break;
			}
		}
		return true;
	}
	
	
	
	
	/**
	 * @param String $str string to be parsed into an expression tree
	 * @return mixed a variable depth array containing pairs of entity 
	 * types and values, or a string containing an error message
	 */
	public static function parseExpression($str) {

		$tokens = self::tokenize($str);
		
		$tree = array();
		$nodePath = array();
		$error = false;
		$bracketLevel = 0;
		
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
		
		
		echo self::validateExpression($tree);
		
		
		
		
		
		
		
		
		
		
		
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
			
			case 'timestamp':
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
}