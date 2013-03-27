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
abstract class X2FlowAction {
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
	abstract public function execute($params);
	
	/**
	 * @return array the param rules.
	 */

	abstract public function paramRules();
	
	/**
	 * Checks if all all the params are ship-shape
	 */
	public function validateRules($params) {
	
	
		$rules = self::getParamRules($this->type);
		
	
		if(isset($rules[$this->type]))
			$rules = $rules[$this->type];
		else
			throw new Exception('Unrecognized automation action: '.$this->type);	// make sure the action type is valid
		
		
		foreach($rules as $key => $val) {
		}
		
		$requiredParams = isset($rules['required'])? preg_split('/[\s,]+/',$rules['required'],null,PREG_SPLIT_NO_EMPTY) : array();
		$optionalParams = isset($rules['optional'])? preg_split('/[\s,]+/',$rules['optional'],null,PREG_SPLIT_NO_EMPTY) : array();
		$multiParams = isset($rules['multivalue'])? preg_split('/[\s,]+/',$rules['multivalue'],null,PREG_SPLIT_NO_EMPTY) : array();
		
		$params = array();
		
		// loop through this item's params and parse the values into $params
	
	
	
	
	
	
	
		// loop through this item's params and parse the values into $params
		foreach($this->actionParams as &$flowParam) {
			if(in_array($flowParam,$multiParams)) {
				if(!isset($params[$flowParam->variable]))	// if its a multivalue param, make it an array
					$params[$flowParam->variable] = array();
				$params[$flowParam->variable][] = $flowParam->parseValue();
			} else {
				$params[$flowParam->variable][] = $flowParam->parseValue();
			}
		}
		
		if(isset($params['model']) && !is_object($params['model']) || !($params['model'] instanceof X2Model))
			throw new Exception('Invalid model parameter');
		
		foreach($requiredParams as $param) {	// make sure all the required params have been provided
			if(!isset($params[$param]))
				return false;
		}
		return true;
	}

	/* 
	 * Sets model fields using the provided attributes and values.
	 * 
	 * @param CActiveRecord $model the model to set fields on
	 * @param array $params
	 * @return boolean whether or not the attributes were valid and set successfully
	 * 
	 */
	public function setModelAttributes(&$model,&$params) {
		// make sure the number of attributes and values are equal
		if(isset($params['attributes'],$params['values']) && count($params['attributes']) !== count($params['values']))
			return false;
		
		for($i=0;$i<count($params['attributes']); $i++) {	// loop through attributes and set them in the action
			if(!$model->hasAttribute($params['attributes'][$i]))	// fail if the attribute doesn't exist
				return false;
			$model->setAttribute($params['attributes'][$i],$params['values'][$i]);
		}
		return true;
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