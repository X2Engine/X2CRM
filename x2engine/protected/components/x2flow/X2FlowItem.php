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
 * @package X2CRM.components.x2flow
 */
abstract class X2FlowItem {
	/**
	 * $var string the text label for this action
	 */
	public $label = '';
	/**
	 * $var string the description of this action
	 */
	public $info = '';
	/**
	 * $var array the config parameters for this action
	 */
	public $config = '';
	/**
	 * @return array the param rules.
	 */
	abstract public function paramRules();
	/**
	 * Checks if all all the params are ship-shape
	 */
	abstract public function validate(&$params=array());
	
	/**
	 * Checks if all the config variables and runtime params are ship-shape
	 * Ignores param requirements if $params isn't provided
	 */
	public function validateOptions(&$paramRules,&$params=null) {
		
		$configOptions = &$this->config['options'];
		// die(var_dump($configOptions));
		foreach($paramRules['options'] as &$optRule) {	// loop through options defined in paramRules() and make sure they're all set in $config
			if(!isset($optRule['name']))		// don't worry about unnamed params
				continue;
			$optName = &$optRule['name'];
			
			if(!isset($configOptions[$optName]))	// each option must be present in $this->config and $params
				return false;
			if($params !== null && !isset($params[$optName]))	// if params are provided, check them for this option name
				return null;
			
			$option = &$configOptions[$optName];
			// set optional flag
			$option['optional'] = isset($optRule['optional']) && $optRule['optional'];
			// operator defaults to "=" if not set
			$option['operator'] = isset($option['operator'])? $option['operator'] : '=';
			// if there's a type setting, set that in the config data
			if(isset($optRule['type']))
				$option['type'] = $optRule['type'];
			// if there's an operator setting, it must be valid
			if(isset($optRule['operator']) && !in_array($optRule['operators'],$configOptions['operator']))
				return false;
			
			// value must not be empty, unless it's an optional setting
			if(!isset($option['value']) || $option['value'] === null || $option['value'] === '') {
				if(isset($optRule['defaultVal']))		// try to use the default value
					$option[$optName] = $optRule['defaultVal'];
				elseif(!$option['optional'])
					return false;	// if not, fail if it was required
			}
		}
		return true;
	}

	/**
	 * Gets the param rules for the specified flow item
	 * @param string $type name of action class
	 * @return mixed an array of param rules, or false if the action doesn't exist
	 */
	public static function getParamRules($type) {
		$item = self::create(array('type'=>$type));
		if($item !== null)
			return $item->paramRules();
		return false;
	}

	/**
	 * Creates a flow item with the provided config data
	 * @return mixed a class extending X2FlowAction with the specified name
	 */
	public static function create($config) {
		if(isset($config['type']) && class_exists($config['type'])) {
			$item = new $config['type'];
			$item->config = $config;
			return $item;
		}
		return null;
	}
	
	/**
	 * Reformats and translates dropdown arrays to preserve sorting in {@link CJSON::encode()}
	 * @param array an associative array of dropdown options ($value => $label)
	 * @return array a 2-D array of values and labels
	 */
	public static function dropdownForJson($options) {
		$dropdownData = array();
		foreach($options as $value => &$label)
			$dropdownData[] = array($value,Yii::t('studio',$label));
		return $dropdownData;
	}
}