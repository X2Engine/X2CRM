<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
abstract class X2FlowItem extends CComponent {

    /**
     * "Cache" of instantiated triggers, for reference purposes
     */
    protected static $_instances;

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
     * $var bool Distinguishes whether cron is required for running the action properly.
     */
    public $requiresCron = false;
	/**
	 * @return array the param rules.
	 */
	abstract public function paramRules();
	/**
	 * Checks if all all the params are ship-shape
	 */
	abstract public function validate(&$params=array(), $flowId);

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
				continue;							// but just ignore them for future proofing

			// if($params !== null && !isset($params[$optName]))	// if params are provided, check them for this option name
				// return false;
			// this is disabled because it doesn't work if every option in $options doesn't correspond to a $param.
			// the ultimate solution is to separate params and options completely. if a trigger/action is going to
			// require params, it should define this separately. the reason for the way it is now is that you can
			// set up an action with very little code. by assuming $params corresponds to $options, check() can
			// treat each option like a condition and compare it to the param.


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
				return array (
                    false,
                    Yii::t('studio', 'Flow item validation error'));

			// value must not be empty, unless it's an optional setting
			if(!isset($option['value']) || $option['value'] === null || $option['value'] === '') {
				if(isset($optRule['defaultVal'])) {		// try to use the default value
					$option[$optName] = $optRule['defaultVal'];
				} elseif(!$option['optional']) {
					// if not, fail if it was required
				    return array (
                        false,
                        Yii::t('studio', 'Required flow item input missing'));
                }
			}
		}
		return array (true, '');
	}

	/**
	 * Gets the param rules for the specified flow item
	 * @param string $type name of action class
	 * @return mixed an array of param rules, or false if the action doesn't exist
	 */
	public static function getParamRules($type) {
		$item = self::create(array('type'=>$type));
		if($item !== null) {
			$paramRules = $item->paramRules();
            $paramRules['class'] = get_class ($item);
            return $paramRules;
        }
		return false;
	}

	/**
	 * Gets the title property of the specified flow item
	 * @param string $type name of action class
	 * @return string the title property, or '' if the type is invalid of if the class
     *  associated with the type doesn't have a title property
     */
    public static function getTitle ($type) {
		$item = self::create(array('type'=>$type));
        $title = '';
		if ($item !== null && property_exists ($item, 'title')) {
            $title = $item->title;
        }
        return $title;
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
			$dropdownData[] = array($value,$label);
		return $dropdownData;
	}

	/**
	 * Calculates a time offset from a number and a unit
	 * @param int $time the number of time units to add
	 * @param string $unit the unit of time
	 * @return mixed the calculated timestamp, or false if the $unit is invalid
	 */
	public static function calculateTimeOffset($time,$unit) {
		switch($unit) {
            case 'secs':
                return $time;
			case 'mins':
				return $time * 60;
			case 'hours':
				return $time * 3600;
			case 'days':
				return $time * 86400;
			case 'months':
				return $time * 2629743;	// average seconds in a month
			default:
				return false;
		}
	}

	/*
	 *
	 */
	public function parseOption($name,&$params) {
		$options = &$this->config['options'];
		if(!isset($options[$name]['value']))
			return null;

		$type = isset($options[$name]['type'])? $options[$name]['type'] : '';
        
		return X2Flow::parseValue($options[$name]['value'],$type,$params);
	}

    /**
     * Generalized mass-instantiation method.
     *
     * Loads and instantiates all X2Flow items of a given type (i.e. actions,
     * triggers).
     * 
     * @param type $type
     * @param type $excludeClasses
     * @return type
     */
    public static function getInstances($type,$excludeClasses=array()) {
        if(!isset(self::$_instances))
            self::$_instances = array();
        if(!isset(self::$_instances[$type])) {
            $excludeFiles = array();
            foreach($excludeClasses as $class) {
                $excludedFiles[] = "$class.php";
            }
            $excludedFiles[] = '.';
            $excludedFiles[] = '..';

            self::$_instances[$type] = array();
            foreach(scandir(Yii::getPathOfAlias('application.components.x2flow.'.$type)) as $file) {
                if(!preg_match ('/\.php$/', $file) || in_array($file,$excludedFiles)) {
                    continue;
                }
                $class = self::create(array('type'=>substr($file,0,-4)));
                if($class !== null)
                    self::$_instances[$type][] = $class;
            }
        }
        return self::$_instances[$type];
    }


}
