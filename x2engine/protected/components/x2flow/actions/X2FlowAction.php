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
 *
 * @package application.components.x2flow
 */
abstract class X2FlowAction extends X2FlowItem {

    public $trigger = null;

    /**
     * Action arrays
     */
    private static $anyModelActions = array(
        "Remote API Call", "Create Action", "Send SMS", "Wait", "Push Web Content",
        "Post to Activity Feed", "Create Notification", "Email", "Complete Process Stage", 
        "Revert Process Stage", "Start Process Stage"
    );

    private static $recordModelActions = array(
        "Change Record", "Add Comment", "Create Record", "Create Action for Record",
        "Delete Record", "Email Contact", "Add to List", "Remove from List",
        "Add to Newsletter","Assign Record", "Edit Tags", "Update Record"
    );

    private static $processModelActions = array(
        
    );
    
    /**
     * Gets array of trigger text that pass specific model
     * 
     * @return type
     */
    public static function getAnyModelActions() {
        return self::$anyModelActions;
    }
    
    public static function getRecordModelActions() {
        return self::$recordModelActions;
    }

    public static function getProcessModelActions() {
        return self::$processModelActions;
    }

    /**
     * Runs the automation action with provided params.
     * @return boolean the result of the execution
     */
    abstract public function execute(&$params);

    public function paramRules () {
        return array (
            'id' => null
        );
    }

    /**
     * Checks if all the config variables and runtime params are ship-shape
     * Ignores param requirements if $params isn't provided
     * Returns an array with two elements. The first element indicates whether an error occured,
     * the second contains a log message.
     */
    public function validate(&$params=array(), $flowId=null) {
        $paramRules = $this->paramRules();
        if(!isset($paramRules['options'],$this->config['options']))
            return array (false, Yii::t('admin', "Flow item validation error"));

        if(isset($paramRules['modelRequired'])) {
            if(!isset($params['model']))    // model not provided when required
                return array (false, Yii::t('admin', "Flow item validation error"));
            if($paramRules['modelRequired'] != 1 && 
                    !in_array(get_class($params['model']), $paramRules['modelRequired'])) {

                // model is not the correct type
                return array (false, Yii::t('admin', "Flow item validation error"));
            }
        }
        return $this->validateOptions($paramRules);
    }

    /**
     * @return mixed either a string containing the notification type for this flow's trigger, or null
     */
    public function getNotifType() {
        if($this->trigger !== null && !empty($this->trigger->notifType))
            return $this->trigger->notifType;
        return null;
    }
    /**
     * @return mixed either a string containing the notification type for this flow's trigger, or null
     */
    public function getEventType() {
        if($this->trigger !== null && !empty($this->trigger->eventType))
            return $this->trigger->eventType;
        return null;
    }

    /**
     * Sets model fields using the provided attributes and values.
     *
     * @param CActiveRecord $model the model to set fields on
     * @param array $attributes an associative array of attributes
     * @param array $params the params array passed to X2Flow::trigger()
     * @return boolean whether or not the attributes were valid and set successfully
     *
     */
    public function setModelAttributes(&$model,&$attributeList,&$params) {
        $data = array ();
        foreach($attributeList as &$attr) {
            if(!isset($attr['name'],$attr['value']))
                continue;

            if(null !== $field = $model->getField($attr['name'])) {
                // first do variable/expression evaluation, // then process with X2Fields::parseValue()
                $type = $field->type;
                $value = $attr['value'];
                if(is_string($value)){
                    if(strpos($value, '=') === 0){
                        $evald = X2FlowFormatter::parseFormula($value, $params);
                        if(!$evald[0])
                            return false;
                        $value = $evald[1];
                    } elseif($params !== null){

                        if(is_string($value) && isset($params['model'])){
                            $value = X2FlowFormatter::replaceVariables(
                                $value, $params, $type);
                        }
                    }
                }

                $data[$attr['name']] = $value;
            }
        }
        if (!isset ($model->scenario)) 
            $model->setScenario ('X2Flow');
        $model->setX2Fields ($data);

        if ($model instanceof Actions && isset($data['complete'])) {
            switch($data['complete']) {
                case 'Yes':
                    $model->complete();
                    break;
                case 'No':
                    $model->uncomplete();
                    break;
            }
        }

        return true;
    }

    /**
     * Gets all action types.
     *
     * Optionally limits actions to a list with a property matching a value.
     * @param string $queryProperty The property of each action to test
     * @param mixed $queryValue The value to match actions against
     */
    public static function getActionTypes($queryProperty=False,$queryValue=False) {
        $types = array();
        foreach(self::getActionInstances() as $class) {
            $include = true;
            if($queryProperty)
                $include = $class->$queryProperty == $queryValue;
            if($include)
                $types[get_class($class)] = $class->title;
        }
        ksort($types);
        return $types;
    }

    public static function getActionInstances() {
        return self::getInstances('actions',array(__CLASS__, 'BaseX2FlowWorkflowStageAction', 'BaseX2FlowEmail'));
    }
}
