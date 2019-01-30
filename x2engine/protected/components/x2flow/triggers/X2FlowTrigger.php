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
 * Color utilities (unused)
 *
 * @package application.components.x2flow
 */
abstract class X2FlowTrigger extends X2FlowItem {

    /**
     * $var string the type of notification to create
     */
    public $notifType = '';

    /**
     * $var string the type of event to create
     */
    public $eventType = '';

    /**
     * Gets array of triggers that pass certain model
     * 
     * @var type 
     */
    private static $anyModelTriggers = array(
        "Newsletter Email Clicked", "Newsletter Email Opened", "Splitter",
        "Unsubscribed from Newsletter", "Periodic Trigger", "Conditional Switch",
        "Campaign Web Activity (no contact available)", 
        "User Signed In", "User Signed Out"
    );
    private static $actionModelTriggers = array(
        "Action Completed", "Action Overdue", "Action Marked Incomplete"
    );
    private static $userModelTriggers = array(
        "Macro Executed", "User Signed In", "User Signed Out"
    );
    private static $processModelTriggers = array(
        "Process Stage Completed", "Process Completed", "Process Stage Reverted",
        "Process Stage Started", "Process Started"
    );
    private static $recordModelTriggers = array(
        "Campaign Email Clicked", "Campaign Email Opened", "Email Opened",
        "Unsubscribed from Campaign", "Campaign Web Activity", "Inbound Email",
        "Outbound Email", "Record Created", "Record Deleted", "Tag Added",
        "Tag Removed", "Record Updated", "Record Viewed", "New Web Lead",
        "Targeted Content Requested", "Contact Web Activity", "Macro Executed",
        "Voip Inbound"
    );

    /**
     * Gets array of trigger text that pass specific model
     * 
     * @return type
     */
    public static function getAnyModelTriggers() {
        return self::$anyModelTriggers;
    }
    
    public static function getActionModelTriggers() {
        return self::$actionModelTriggers;
    }

    public static function getUserModelTriggers() {
        return self::$userModelTriggers;
    }

    public static function getProcessModelTriggers() {
        return self::$processModelTriggers;
    }

    public static function getRecordModelTriggers() {
        return self::$recordModelTriggers;
    }

    /**
     * @return array all standard comparison operators
     */
    public static function getFieldComparisonOptions() {
        return array(
            '=' => Yii::t('app', 'equals'),
            '>' => Yii::t('app', 'greater than'),
            '<' => Yii::t('app', 'less than'),
            '>=' => Yii::t('app', 'greater than or equal to'),
            '<=' => Yii::t('app', 'less than or equal to'),
            '<>' => Yii::t('app', 'not equal to'),
            'list' => Yii::t('app', 'in list'),
            'notList' => Yii::t('app', 'not in list'),
            'empty' => Yii::t('app', 'empty'),
            'notEmpty' => Yii::t('app', 'not empty'),
            'contains' => Yii::t('app', 'contains'),
            'noContains' => Yii::t('app', 'does not contain'),
            'changed' => Yii::t('app', 'changed'),
            'before' => Yii::t('app', 'before'),
            'after' => Yii::t('app', 'after'),
        );
    }

    public static $genericConditions = array(
        'attribute' => 'Compare Attribute',
        'workflow_status' => 'Process Status',
        'current_user' => 'Current User',
        'month' => 'Current Month',
        'day_of_week' => 'Day of Week',
        'day_of_month' => 'Day of Month',
        'time_of_day' => 'Time of Day',
        'current_time' => 'Current Time',
        'user_active' => 'User Logged In',
        'on_list' => 'On List',
        'has_tags' => 'Has Tags',
        'email_open' => 'Email Opened',
    );

    public static function getGenericConditions() {
        return array_map(function($term) {
            return Yii::t('studio', $term);
        }, self::$genericConditions);
    }

    public static function getGenericCondition($type) {
        switch ($type) {
            case 'current_user':
                return array(
                    'name' => 'user',
                    'label' => Yii::t('studio', 'Current User'),
                    'type' => 'dropdown',
                    'multiple' => 1,
                    'options' => X2Model::getAssignmentOptions(false, false),
                    'operators' => array('=', '<>', 'list', 'notList')
                );

            case 'month':
                return array(
                    'label' => Yii::t('studio', 'Current Month'),
                    'type' => 'dropdown',
                    'multiple' => 1,
                    'options' => Yii::app()->locale->monthNames,
                    'operators' => array('=', '<>', 'list', 'notList')
                );

            case 'day_of_week':
                return array(
                    'label' => Yii::t('studio', 'Day of Week'),
                    'type' => 'dropdown',
                    'multiple' => 1,
                    'options' => Yii::app()->locale->weekDayNames,
                    'operators' => array('=', '<>', 'list', 'notList')
                );

            case 'day_of_month':
                $days = array_keys(array_fill(1, 31, 1));
                return array(
                    'label' => Yii::t('studio', 'Day of Month'),
                    'type' => 'dropdown',
                    'multiple' => 1,
                    'options' => array_combine($days, $days),
                    'operators' => array('=', '<>', 'list', 'notList')
                );

            case 'time_of_day':
                return array(
                    'label' => Yii::t('studio', 'Time of Day'),
                    'type' => 'time',
                    'operators' => array('before', 'after')
                );

            case 'current_time':
                return array(
                    'label' => Yii::t('studio', 'Current Time'),
                    'type' => 'dateTime',
                    'operators' => array('before', 'after')
                );

            case 'user_active':
                return array(
                    'label' => Yii::t('studio', 'User Logged In'),
                    'type' => 'dropdown',
                    'options' => X2Model::getAssignmentOptions(false, false)
                );

            case 'on_list':
                return array(
                    'label' => Yii::t('studio', 'On List'),
                    'type' => 'link',
                    'linkType' => 'X2List',
                    'linkSource' => Yii::app()->controller->createUrl(
                            CActiveRecord::model('X2List')->autoCompleteSource)
                );
            case 'has_tags':
                return array(
                    'label' => Yii::t('studio', 'Has Tags'),
                    'type' => 'tags',
                );
            case 'email_open':
                return array(
                    'label' => Yii::t('studio', 'Email Opened'),
                    'type' => 'dropdown',
                    'options' => array(),
                );
            default:
                return false;
        }
    }

    /**
     * Can be overridden in child class to give flow a default return value
     */
    public function getDefaultReturnVal($flowId) {
        return null;
    }

    /**
     * Can be overridden in child class to extend behavior of validate method
     */
    public function afterValidate(&$params, $defaultErrMsg = '', $flowId) {
        return array(false, Yii::t('studio', $defaultErrMsg));
    }

    /**
     * Checks if all all the params are ship-shape
     */
    public function validate(&$params = array(), $flowId = null) {
        $paramRules = $this->paramRules();
        if (!isset($paramRules['options'], $this->config['options'])) {
            return $this->afterValidate(
                            $params, YII_DEBUG ?
                            'invalid rules/params: trigger passed options when it specifies none' :
                            'invalid rules/params', $flowId);
        }
        $config = &$this->config['options'];

        if (isset($paramRules['modelClass'])) {
            $modelClass = $paramRules['modelClass'];
            if ($modelClass === 'modelClass') {
                if (isset($config['modelClass'], $config['modelClass']['value'])) {
                    $modelClass = $config['modelClass']['value'];
                } else {
                    return $this->afterValidate(
                                    $params, YII_DEBUG ?
                                    'invalid rules/params: ' .
                                    'trigger requires model class option but given none' :
                                    'invalid rules/params', $flowId);
                }
            }
            if (!isset($params['model'])) {
                return $this->afterValidate(
                                $params, YII_DEBUG ?
                                'invalid rules/params: trigger requires a model but passed none' :
                                'invalid rules/params', $flowId);
            }
            if ($modelClass !== get_class($params['model'])) {
                return $this->afterValidate(
                                $params, YII_DEBUG ?
                                'invalid rules/params: required model class does not match model passed ' .
                                'to trigger' :
                                'invalid rules/params', $flowId);
            }
        }
        return $this->validateOptions($paramRules, $params);
    }

    /**
     * Default condition processor for main config panel. Checks each option against the key in 
     * $params of the same name, using an operator if provided (defaults to "=")
     * 
     * @return array (error status, message)
     */
    public function check(&$params) {
        foreach ($this->config['options'] as $name => &$option) {
            // modelClass is a special case, ignore it
            if ($name === 'modelClass') {
                continue;
            }

            // if it's optional and blank, forget about it
            if ($option['optional'] && ($option['value'] === null ||
                    $option['value'] === '')) {
                continue;
            }

            $value = $option['value'];

            if (isset($option['type'])) {
                $value = X2Flow::parseValue($value, $option['type'], $params);
            }

            if (isset($option['comparison']) && !$option['comparison']) {
                continue;
            }

            if (!static::evalComparison($params[$name], $option['operator'], $value)) {
                if (is_string($value) && is_string($params[$name]) &&
                        is_string($option['operator'])) {

                    return array(
                        false,
                        Yii::t('studio', 'The following condition did not pass: ' .
                                '{name} {operator} {value}', array(
                            '{name}' => $params[$name],
                            '{operator}' => $option['operator'],
                            '{value}' => (string) $value,
                        ))
                    );
                } else {
                    return array(
                        false,
                        Yii::t('studio', 'Condition failed')
                    );
                }
            }
        }

        return $this->checkConditions($params);
    }

    /**
     * Tests this trigger's conditions against the provided params.
     * @return array (error status, message)
     */
    public function checkConditions(&$params) {
        if (isset($this->config['conditions'])) {
            foreach ($this->config['conditions'] as &$condition) {
                if (!isset($condition['type'])) {
                    $condition['type'] = '';
                }
                $required = isset($condition['required']) && $condition['required'];

                // required param missing
                if (isset($condition['name']) && $required && !isset($params[$condition['name']])) {
                    if (YII_DEBUG) {
                        return array(false, Yii::t('studio', 'a required parameter is missing'));
                    } else {
                        return array(false, Yii::t('studio', 'conditions not passed'));
                    }
                }

                if (array_key_exists($condition['type'], self::$genericConditions)) {
                    if (!self::checkCondition($condition, $params))
                        return array(
                            false,
                            Yii::t('studio', 'conditions not passed')
                        );
                }
            }
        }
        return array(true, '');
    }

    /**
     * Used to check workflow status condition
     * @param Array $condition
     * @param Array $params
     * @return bool true for success, false otherwise
     */
    public static function checkWorkflowStatusCondition($condition, &$params) {
        if (!isset($params['model']) ||
                !isset($condition['workflowId']) ||
                !isset($condition['stageNumber']) ||
                !isset($condition['stageState'])) {

            return false;
        }

        $model = $params['model'];
        $workflowId = $condition['workflowId'];
        $stageNumber = $condition['stageNumber'];
        $stageState = $condition['stageState'];
        $modelId = $model->id;
        $type = lcfirst(X2Model::getModuleName(get_class($model)));

        $workflowStatus = Workflow::getWorkflowStatus($workflowId, $modelId, $type);
        if (!isset($workflowStatus['stages'][$stageNumber])) {
            return false;
        }

        $passed = false;
        switch ($stageState) {
            case 'completed':
                $passed = Workflow::isCompleted($workflowStatus, $stageNumber);
                break;
            case 'started':
                $passed = Workflow::isStarted($workflowStatus, $stageNumber);
                break;
            case 'notCompleted':
                $passed = !Workflow::isCompleted($workflowStatus, $stageNumber);
                break;
            case 'notStarted':
                $passed = !Workflow::isStarted($workflowStatus, $stageNumber);
                break;
            default:
                return false;
        }
        return $passed;
    }

    /**
     * @param Array $condition
     * @param Array $params
     * @return bool true for success, false otherwise
     */
    public static function checkCondition($condition, &$params) {
        if ($condition['type'] === 'workflow_status') {
            return self::checkWorkflowStatusCondition($condition, $params);
        }

        $model = isset($params['model']) ? $params['model'] : null;
        $operator = isset($condition['operator']) ? $condition['operator'] : '=';
        // $type = isset($condition['type'])? $condition['type'] : null;
        $value = isset($condition['value']) ? $condition['value'] : null;

        // default to a doing basic value comparison
        if (isset($condition['name']) && $condition['type'] === '') {
            if (!isset($params[$condition['name']])) {
                return false;
            }

            return self::evalComparison($params[$condition['name']], $operator, $value);
        }

        switch ($condition['type']) {
            case 'attribute':
                if (!isset($condition['name'], $model)) {
                    return false;
                }
                $attr = &$condition['name'];
                if (null === $field = $model->getField($attr)) {
                    return false;
                }

                if ($operator === 'changed') {
                    return $model->attributeChanged($attr);
                }

                if ($field->type === 'link') {
                    list ($attrVal, $id) = Fields::nameAndId($model->getAttribute($attr));
                } else {
                    $attrVal = $model->getAttribute($attr);
                }

                return self::evalComparison(
                                $attrVal, $operator, X2Flow::parseValue($value, $field->type, $params), $field);

            case 'current_user':
                return self::evalComparison(Yii::app()->user->getName(), $operator, X2Flow::parseValue($value, 'assignment', $params));
            case 'month':
                return self::evalComparison((int) date('n'), $operator, $value);    // jan = 1, dec = 12
            case 'day_of_month':
                return self::evalComparison((int) date('j'), $operator, $value); // 1 through 31
            case 'day_of_week':
                return self::evalComparison((int) date('N'), $operator, $value); // monday = 1, sunday = 7
            case 'time_of_day':    // - mktime(0,0,0)
                return self::evalComparison(time(), $operator, X2Flow::parseValue($value, 'time', $params)); // seconds since midnight
            case 'current_time':
                return self::evalComparison(time(), $operator, X2Flow::parseValue($value, 'dateTime', $params));
            case 'user_active':
                return CActiveRecord::model('Session')->exists(
                                'user=:user AND status=1', array(
                            ':user' => X2Flow::parseValue($value, 'assignment', $params)));
            case 'on_list':
                if (!isset($model, $value)) {
                    return false;
                }
                $value = X2Flow::parseValue($value, 'link');

                // look up specified list
                if (is_numeric($value)) {
                    $list = CActiveRecord::model('X2List')->findByPk($value);
                } else {
                    $list = CActiveRecord::model('X2List')->findByAttributes(
                            array('name' => $value));
                }

                return ($list !== null && $list->hasRecord($model));
            case 'has_tags':
                if (!isset($model, $value))
                    return false;
                $tags = X2Flow::parseValue($value, 'tags');
                return $model->hasTags($tags, 'AND');
            case 'workflow_status':
                if (!isset($model, $condition['workflowId'], $condition['stageNumber']))
                    return false;

                switch ($operator) {
                    case 'started_workflow':
                        return CActiveRecord::model('Actions')->exists(
                                        'associationType=:type AND associationId=:modelId AND type="workflow" AND workflowId=:workflow', array(
                                    ':type' => get_class($model),
                                    ':modelId' => $model->id,
                                    ':workflow' => $condition['workflowId'],
                        ));
                    case 'started_stage':
                        return CActiveRecord::model('Actions')->exists(
                                        'associationType=:type AND associationId=:modelId AND type="workflow" AND workflowId=:workflow AND stageNumber=:stage AND (completeDate IS NULL OR completeDate=0)', array(
                                    ':type' => get_class($model),
                                    ':modelId' => $model->id,
                                    ':workflow' => $condition['workflowId'],
                                    ':stageNumber' => $condition['stageNumber'],
                        ));
                    case 'completed_stage':
                        return CActiveRecord::model('Actions')->exists(
                                        'associationType=:type AND associationId=:modelId AND type="workflow" AND workflowId=:workflow AND stageNumber=:stage AND completeDate > 0', array(
                                    ':type' => get_class($model),
                                    ':modelId' => $model->id,
                                    ':workflow' => $condition['workflowId'],
                                    ':stageNumber' => $condition['stageNumber'],
                        ));
                    case 'completed_workflow':
                        $stageCount = CActiveRecord::model('WorkflowStage')->count('workflowId=:id', array(':id' => $condition['workflowId']));
                        $actionCount = CActiveRecord::model('Actions')->count(
                                'associationType=:type AND associationId=:modelId AND type="workflow" AND workflowId=:workflow', array(
                            ':type' => get_class($model),
                            ':modelId' => $model->id,
                            ':workflow' => $condition['workflowId'],
                        ));
                        return $actionCount >= $stageCount;
                }
                return false;
            case 'email_open':
                if (isset($params['sentEmails'], $params['sentEmails'][$value])) {
                    $trackEmail = TrackEmail::model()->findByAttributes(array('uniqueId' => $params['sentEmails'][$value]));
                    return $trackEmail && !is_null($trackEmail->opened);
                }
                return false;
        }
        return false;

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

    protected static function parseArray($operator, $value) {
        $expectsArray = array('list', 'notList', 'between');

        // $value needs to be a comma separated list
        if (in_array($operator, $expectsArray, true) && !is_array($value)) {
            $value = explode(',', $value);

            $len = count($value);
            for ($i = 0; $i < $len; $i++) {
                // loop through the values, trim and remove empty strings
                if (($value[$i] = trim($value[$i])) === '')
                    unset($value[$i]);
            }
        }
        return $value;
    }

    /**
     * @param mixed $subject if applicable, the value to compare $subject with (value of model 
     *  attribute)
     * @param string $operator the type of comparison to be used
     * @param mixed $value the value being analyzed (specified in config menu)
     * @return boolean
     */
    public static function evalComparison($subject, $operator, $value = null, Fields $field = null) {
        $value = self::parseArray($operator, $value);

        switch ($operator) {
            case '=':
                // check for multiselect dropdown
                if ($field && $field->type === 'dropdown') {
                    $dropdown = $field->getDropdown();
                    if ($dropdown && $dropdown->multi) {
                        $subject = StringUtil::jsonDecode($subject, false);
                        AuxLib::coerceToArray($subject);
                        AuxLib::coerceToArray($value);
                        return $subject === $value;
                    }
                    // check for muti-assignment field
                } else if ($field && $field->type === 'assignment' &&
                        $field->linkType === 'multiple') {

                    $subject = explode(Fields::MULTI_ASSIGNMENT_DELIM, $subject);
                    AuxLib::coerceToArray($subject);
                    AuxLib::coerceToArray($value);
                    return $subject === $value;
                }

                // this case occurs when dropdown or assignment fields are changed from multiple
                // to single selection, and flow conditions are left over from before the change 
                // was made
                if (is_array($value)) {
                    AuxLib::coerceToArray($subject);
                }
                return $subject == $value;

            case '>':
                return $subject > $value;

            case '<':
                return $subject < $value;

            case '>=':
                return $subject >= $value;

            case '<=':
                return $subject <= $value;

            case 'between':
                if (count($value) !== 2)
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
                if (count($value) === 0)    // if the list is empty,
                    return false;                                // A isn't in it
                foreach ($value as &$val)
                    if ($subject == $val)
                        return true;

                return false;

            case 'notList':
                if (count($value) === 0)    // if the list is empty,
                    return true;                                // A isn't *not* in it
                foreach ($value as &$val)
                    if ($subject == $val)
                        return false;

                return true;

            case 'noContains':
                return stripos($subject, $value) === false;

            case 'contains':
            default:
                return stripos($subject, $value) !== false;
        }
    }

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
        while ($offset < mb_strlen($str)) {
            $token = array();

            $substr = mb_substr($str, $offset);    // remaining string starting at $offset

            foreach (self::$_tokenChars as $char => &$name) {    // scan single-character patterns first
                if (mb_substr($substr, 0, 1) === $char) {
                    $tokens[] = array($name);    // add it to $tokens
                    $offset++;
                    continue 2;
                }
            }
            foreach (self::$_tokenRegex as $regex => &$name) {    // now loop through regex patterns
                $matches = array();
                if (preg_match('/^' . $regex . '/u', $substr, $matches) === 1) {
                    $tokens[] = array($name, $matches[0]);    // add it to $tokens
                    $offset += mb_strlen($matches[0]);
                    continue 2;
                }
            }
            $offset++;    // no infinite looping, yo
        }
        return $tokens;
    }

    /**
     * Adds a new node at the end of the specified branch
     * @param array &$tree the tree object
     * @param array $nodePath array of branch indeces leading to the target branch
     * @value array an array containing the new node's type and value
     */
    protected static function addNode(&$tree, $nodePath, $value) {
        if (count($nodePath) > 0)
            return self::addNode($tree[array_shift($nodePath)], $nodePath, $value);

        $tree[] = $value;
        return count($tree) - 1;
    }

    /**
     * Checks if this branch has only one node and eliminates it by moving the child node up one level
     * @param array &$tree the tree object
     * @param array $nodePath array of branch indeces leading to the target node
     */
    protected static function simplifyNode(&$tree, $nodePath) {
        if (count($nodePath) > 0)                                                    // before doing anything, recurse down the tree using $nodePath
            return self::simplifyNode($tree[array_shift($nodePath)], $nodePath);        // to get to the targeted node

        $last = count($tree) - 1;

        if (empty($tree[$last][1]))
            array_pop($tree);
        elseif (count($tree[$last][1]) === 1)
            $tree[$last] = $tree[$last][1][0];
    }

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

        for ($i = 0; $i < count($tokens); $i++) {
            switch ($tokens[$i][0]) {
                case 'OPEN_BRACKET':
                    $nodePath[] = self::addNode($tree, $nodePath, array('EXPRESSION', array()));    // add a new expression node, get its offset in the current branch,
                    $nodePath[] = 1;    // then move down to its 2nd element (1st element is the type, i.e. 'EXPRESSION')
                    break;
                case 'CLOSE_BRACKET':
                    if (count($nodePath) > 1) {
                        $nodePath = array_slice($nodePath, 0, -2);    // set node path to one level higher
                        self::simplifyNode($tree, $nodePath);        // we're closing an expression node; check to see if its empty or only contains one thing
                    } else {
                        $error = 'unbalanced brackets';
                    }
                    break;

                case 'SPACE': break;
                default:
                    self::addNode($tree, $nodePath, $tokens[$i]);
            }
        }

        if (count($nodePath) !== 0)
            $error = 'unbalanced brackets';

        if ($error !== false)
            return 'ERROR: ' . $error;
        else
            return $tree;
    }

    /**
     * Gets all X2Flow trigger types.
     *
     * Optionally constrains the list to those with a property matching a value.
     * @param string $queryProperty The property of each trigger to test
     * @param mixed $queryValue The value to match trigger against
     */
    public static function getTriggerTypes($queryProperty = False, $queryValue = False) {
        $types = array();
        foreach (self::getTriggerInstances() as $class) {
            $include = true;
            if ($queryProperty)
                $include = $class->$queryProperty == $queryValue;
            if ($include)
                $types[get_class($class)] = Yii::t('studio', $class->title);
        }
        return $types;
    }

    /**
     * Gets X2Flow trigger title.
     * 
     * @param string $triggerType The trigger class name
     * @return string the empty string or the title of the trigger with the given class name
     */
    public static function getTriggerTitle($triggerType) {
        foreach (self::getTriggerInstances() as $class) {
            if (get_class($class) === $triggerType) {
                return Yii::t('studio', $class->title);
            }
        }
        return '';
    }

    public static function getTriggerInstances() {
        return self::getInstances('triggers', array(__CLASS__, 'X2FlowSwitch', 'X2FlowSplitter', 'BaseTagTrigger', 'BaseWorkflowStageTrigger', 'BaseWorkflowTrigger', 'BaseUserTrigger', 'MultiChildNode'));
    }

}
