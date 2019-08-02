<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




/**
 * @package application.components.x2flow
 */
abstract class X2FlowItem extends CComponent {

    const VALIDATION_WARNING = 2;

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
     * $var bool distinguishes whether cron is required for running the action properly
     */
    public $requiresCron = false;

    /**
     * @return array the param rules.
     */
    abstract public function paramRules();

    /**
     * Checks if all all the params are ship-shape
     */
    abstract public function validate(&$params=array(), $flowId=null);

    /**
     * Checks if all the config variables and runtime params are ship-shape
     * Ignores param requirements if $params isn't provided
     * @param bool $staticValidation If true, validation will include checks for warnings
     */
    public function validateOptions(&$paramRules,$params=null,$staticValidation=false) {
        $configOptions = &$this->config['options'];

        // loop through options defined in paramRules() and make sure they're all set in $config
        foreach($paramRules['options'] as &$optRule) {    
            if(!isset($optRule['name']))        // don't worry about unnamed params
                continue;
            $optName = &$optRule['name'];

            // each option must be present in $this->config and $params
            if(!isset($configOptions[$optName])) {  
                if (isset ($optRule['defaultVal'])) {
                    $configOptions[$optName] = array ();
                } else {
                    continue; // but just ignore them for future proofing
                }
            }
            // if params are provided, check them for this option name
            // if($params !== null && !isset($params[$optName]))    
                // return false;
            // this is disabled because it doesn't work if every option in $options doesn't 
            // correspond to a $param. the ultimate solution is to separate params and options 
            // completely. if a trigger/action is going to require params, it should define this 
            // separately. the reason for the way it is now is that you can set up an action with 
            // very little code. by assuming $params corresponds to $options, check() can
            // treat each option like a condition and compare it to the param.

            $option = &$configOptions[$optName];

            // set optional flag
            $option['optional'] = isset($optRule['optional']) && $optRule['optional'];
            $option['comparison'] = isset($optRule['comparison']) ? $optRule['comparison'] : true;

            // operator defaults to "=" if not set
            $defaultOperator = isset($optRule['defaultOperator']) ? $optRule['defaultOperator'] : '=';
            $option['operator'] = isset($option['operator']) ? $option['operator'] : $defaultOperator;

            // if there's a type setting, set that in the config data
            if(isset($optRule['type']))
                $option['type'] = $optRule['type'];

            // if there's an operator setting, it must be valid
            if(isset($optRule['operators']) &&
               !in_array($option['operator'], $optRule['operators'])) {

                return array (
                    false,
                    Yii::t('studio', 'Flow item validation error: Invalid operator'));
            }
            
            // value must not be empty, unless it's an optional setting
            if(!isset($option['value']) || $option['value'] === null || $option['value'] === '') {
                if(isset($optRule['defaultVal'])) { 

                    // use the default value 
                    $option['value'] = $optRule['defaultVal'];
                } elseif(!$option['optional']) {

                    // if not, fail if it was required
                    if (YII_DEBUG) {
                        return array (
                            false,
                            Yii::t('studio', 
                                'Required flow item input missing: {optName} was left blank.',
                                array ('{optName}' => $optName)));
                    } else {
                        return array (
                            false,
                            Yii::t('studio', 'Required flow item input missing'));
                    }
                }
            }
            
            if (isset ($option['type']) && isset ($option['value'])) {
                switch ($option['type']) {
                    case 'dropdown':
                        list ($success, $message) = $this->validateDropdown ($option, $optRule);
                        if (!$success) return array (false, $message);
                        break;
                    case 'email':
                        list ($success, $message) = $this->validateEmail ($option, $optRule);
                        if (!$success) return array (false, $message);
                        break;
                }
            }
        }

        return array (true, '');
    }

    public function validateEmail ($option, $optRule) {
        if (isset ($option['value']) && 
            !Formatter::isFormula ($option['value']) &&
            !Formatter::isShortcode ($option['value'])) {
            try {
                EmailDeliveryBehavior::addressHeaderToArray ($option['value']);
            } catch (CException $e) {
                return array (false, $e->getMessage ());
            }
        }
        return array (true, '');
    }

    public function validateDropdown (&$option, $optRule) {
        $name = $optRule['name'];
        if (!((isset ($option['operator']) && 
               in_array ($option['operator'], array ('list', 'notList', 'between'))) || 
              (isset ($optRule['multiple']) && $optRule['multiple'])) && 
             is_array ($option['value'])) {

            if (count ($option['value']) === 1 &&
                isset ($option['value'][0])) { // repair value if possible

                $option['value'] = $option['value'][0];
                return array (true, '');
            }

            return array (false, Yii::t('studio', 'Invalid option value for {optionName}. '.
                'Multiple values specified but only one is allowed.', array (
                    '{optionName}' => $name,
                )));
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
                return $time * 2629743;    // average seconds in a month
            default:
                return false;
        }
    }

    /**
     * @param string $name the name of the option
     * @param array $params the parameters passed to trigger ()
     * @return mixed null if the option was not set by the user, the parsed value otherwise
     */
    public function parseOption($name,&$params,$renderFlag=true) {
        $options = &$this->config['options'];
        if(!isset($options[$name]['value']))
            return null;

        $type = isset($options[$name]['type'])? $options[$name]['type'] : '';
        
        return X2Flow::parseValue($options[$name]['value'],$type,$params,$renderFlag);
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
