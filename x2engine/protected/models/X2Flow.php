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
 * @package application.models
 */
Yii::import('application.components.x2flow.X2FlowItem');
Yii::import('application.components.x2flow.X2FlowFormatter');
Yii::import('application.components.x2flow.actions.*');
Yii::import('application.components.x2flow.triggers.*');
Yii::import('application.models.ApiHook');

class X2Flow extends X2ActiveRecord {

    /**
     * @const max number of nested calls to {@link X2Flow::trigger()}
     */
    const MAX_TRIGGER_DEPTH = 0;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return X2Flow the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @var the current depth of nested trigger calls
     */
    private static $_triggerDepth = 0;

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'x2_flows';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        return array(
            array('name, createDate, lastUpdated, triggerType, flow', 'required'),
            array('createDate, lastUpdated', 'numerical', 'integerOnly' => true),
            array('active', 'boolean'),
            array('name', 'length', 'max' => 100),
            array('flow', 'validateFlow'),
            array('triggerType, modelClass', 'length', 'max' => 40),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, active, name, description, createDate, lastUpdated', 'safe', 'on' => 'search'),
        );
    }

    private $_flow;

    public function getFlow($refresh = false) {
        if (!isset($this->_flow) || $refresh) {
            $this->_flow = CJSON::decode($this->flow);
        }
        return $this->_flow;
    }

    public function setFlow(array $flow) {
        $this->_flow = $flow;
        $this->flow = CJSON::encode($flow);
    }

    /**
     * Ensure validity of specified config options
     */
    public function validateFlow($attribute) {
        $flow = CJSON::decode($this->$attribute);

        if (isset($flow['trigger']) && isset($flow['items'])) {
            if (!$this->validateFlowItem($flow['trigger'])) {
                return false;
            }
            if ($this->validateFlowPrime($flow['items'])) {
                return true;
            }
        } else {
            $this->addError($attribute, Yii::t('studio', 'Invalid flow'));
            return false;
        }
    }

    public function afterSave() {
        $flow = CJSON::decode($this->flow);
        $triggerClass = $flow['trigger']['type'];
        if ($triggerClass === 'PeriodicTrigger') {
            $trigger = X2FlowTrigger::create($flow['trigger']);
            $trigger->afterFlowSave($this);
        }
        parent::afterSave();
    }

    /**
     * Returns a list of behaviors that this model should behave as.
     * @return array the behavior configurations (behavior name=>behavior configuration)
     */
    public function behaviors() {
        return array(
            'TimestampBehavior' => array('class' => 'TimestampBehavior'),
            'ERememberFiltersBehavior' => array(
                'class' => 'application.components.behaviors.ERememberFiltersBehavior',
                'defaults' => array(),
                'defaultStickOnClear' => false
            ),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => Yii::t('admin', 'ID'),
            'active' => Yii::t('admin', 'Active'),
            'name' => Yii::t('admin', 'Name'),
            'triggerType' => Yii::t('admin', 'Trigger'),
            'modelClass' => Yii::t('admin', 'Type'),
            'name' => Yii::t('admin', 'Name'),
            'createDate' => Yii::t('admin', 'Create Date'),
            'lastUpdated' => Yii::t('admin', 'Last Updated'),
            'description' => Yii::t('admin', 'Description'),
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the 
     *  search/filter conditions.
     */
    public function search() {
        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('active', $this->active);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('description', $this->description, true);
        $criteria->compare('createDate', $this->createDate, true);
        $criteria->compare('lastUpdated', $this->lastUpdated, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => Profile::getResultsPerPage(),
            ),
        ));
    }

    /**
     * Validates the JSON data in $flow.
     * Sets createDate and lastUpdated.
     * @return boolean whether or not to proceed to validation
     */
    public function beforeValidate() {
        $flowData = CJSON::decode($this->flow);

        if ($flowData === false) {
            $this->addError('flow', Yii::t('studio', 'Flow configuration data appears to be ' .
                            'corrupt.'));
            return false;
        }
        if (isset($flowData['trigger']['type'])) {
            $this->triggerType = $flowData['trigger']['type'];
            if (isset($flowData['trigger']['modelClass']))
                $this->modelClass = $flowData['trigger']['modelClass'];
        } else {
            // $this->addError('flow',Yii::t('studio','You must configure a trigger event.'));
        }
        if (!isset($flowData['items']) || empty($flowData['items'])) {
            $this->addError('flow', Yii::t('studio', 'There must be at least one action in the ' .
                            'flow.'));
        }

        $this->lastUpdated = time();
        if ($this->isNewRecord)
            $this->createDate = $this->lastUpdated;
        return parent::beforeValidate();
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
     * @return mixed Null or return value of extractRetValFromTrace
     */
    public static function trigger($triggerName, $params = array()) {
        if (self::$_triggerDepth > self::MAX_TRIGGER_DEPTH) { // ...have we delved too deep?
            return;
        }

        $triggeredAt = time();

        if (isset($params['model']) &&
                (!is_object($params['model']) || (!($params['model'] instanceof X2Model)))) {
            // Invalid model provided
            return false;
        }

        // Communicate the event to third-party systems, if any
        ApiHook::runAll($triggerName, $params);

        // increment stack depth before doing anything that might call X2Flow::trigger()
        self::$_triggerDepth++;

        $flowAttributes = array('triggerType' => $triggerName, 'active' => 1);

        if (isset($params['model'])) {
            $flowAttributes['modelClass'] = get_class($params['model']);
            $params['modelClass'] = get_class($params['model']);
        }

        // if flow id is specified, only execute flow with specified id
        if (isset($params['flowId'])) {
            $flowAttributes['id'] = $params['flowId'];
        }

        $flows = CActiveRecord::model('X2Flow')->findAllByAttributes($flowAttributes);

        // collect information about trigger for the trigger log.
        $triggerInfo = array(
            'triggerName' => Yii::t('studio', X2FlowItem::getTitle($triggerName))
        );
        if (isset($params['model']) && (is_subclass_of($params['model'], 'X2Model')) &&
                $params['model']->asa('LinkableBehavior')) {
            $triggerInfo['modelLink'] = Yii::t('studio', 'View record: ') . $params['model']->getLink();
        }
 
        // find all flows matching this trigger and modelClass
        $triggerLog;
        $flowTrace;
        $flowRetVal = null;
        foreach ($flows as &$flow) {
            $triggerLog = new TriggerLog();
            $triggerLog->triggeredAt = $triggeredAt;
            $triggerLog->flowId = $flow->id;
            $triggerLog->save();

            $flowRetArr = self::_executeFlow($flow, $params, null, $triggerLog->id);
            $flowTrace = $flowRetArr['trace'];
            $flowRetVal = (isset($flowRetArr['retVal'])) ? $flowRetArr['retVal'] : null;
            $flowRetVal = self::extractRetValFromTrace($flowTrace);

            // save log for triggered flow
            $triggerLog->triggerLog = CJSON::encode(array_merge(array($triggerInfo), array($flowTrace)));
            $triggerLog->save();
        }

        // this trigger call is done; decrement the stack depth
        self::$_triggerDepth--;
        return $flowRetVal;
    }

    /**
     * Traverses the trace tree and checks if the last action has a return value. If so, that 
     * value is returned. Otherwise, null is returned.
     * 
     * @param array the trace returned by executeFlow
     * @return mixed null if there's no return value, mixed otherwise
     */
    public static function extractRetValFromTrace($flowTrace) {
        // trigger itself has return val

        if (sizeof($flowTrace) === 3 && $flowTrace[0] && !is_array($flowTrace[1])) {
            return $flowTrace[2];
        }

        // ensure that initial branch executed without errors
        if (sizeof($flowTrace) < 2 || !$flowTrace[0] || !is_array($flowTrace[1])) {
            return null;
        }

        // find last action
        $startOfBranchExecution = $flowTrace[1];
        $lastAction = $startOfBranchExecution[sizeof($startOfBranchExecution) - 1];
        while (true) {
            if (is_subclass_of($lastAction[0], 'MultiChildNode')) {
                $startOfBranchExecution = $lastAction[2];
                if (sizeof($startOfBranchExecution) > 0) {
                    $lastAction = $startOfBranchExecution[sizeof($startOfBranchExecution) - 1];
                } else {
                    return null;
                }
            } else {
                break;
            }
        }

        // if last action has return value, return it
        if (sizeof($lastAction[1]) === 3)
            return $lastAction[1][2];
    }

    /**
     * Can be called to resume execution of flow that paused for the wait action. 
     * @param X2Flow &$flow the object representing the flow to run
     * @param array &$params an associative array of params, usually including 'model'=>$model,
     * @param mixed $flowPath an array of directions to a specific point in the flow. Defaults to
     *  null.
     */
    public static function resumeFlowExecution(
    &$flow, &$params, $actionId = null, $triggerLogId = null) {

        if (self::$_triggerDepth > self::MAX_TRIGGER_DEPTH) // ...have we delved too deep?
            return;

        if (isset($params['model']) &&
                (!is_object($params['model']) || !($params['model'] instanceof X2Model))) {
            // Invalid model provided
            return false;
        }

        // increment stack depth before doing anything that might call X2Flow::trigger()
        self::$_triggerDepth++;
        $result = self::_executeFlow($flow, $params, $actionId, $triggerLogId);
        self::$_triggerDepth--;  // this trigger call is done; decrement the stack depth
        return $result;
    }

    /**
     * Wrapper around _executeFlow which respects trigger depth restriction 
     */
    public static function executeFlow(&$flow, &$params, $actionId = null) {
        if (self::$_triggerDepth > self::MAX_TRIGGER_DEPTH) // ...have we delved too deep?
            return;

        self::$_triggerDepth++;

        $triggerInfo = array(
            'triggerName' => Yii::t('studio', X2FlowItem::getTitle('MacroTrigger'))
        );
        if (isset($params['model']) && is_subclass_of($params['model'], 'X2Model') &&
                $params['model']->asa('LinkableBehavior')) {
            $triggerInfo['modelLink'] = Yii::t('studio', 'View record: ') .
                    $params['model']->getLink();
        }
        
        $triggerLog = new TriggerLog;
        $triggerLog->triggeredAt = time();
        $triggerLog->flowId = $flow->id;
        $triggerLog->save();
        $flowRetArr = self::_executeFlow($flow, $params, $actionId, $triggerLog->id);
        $flowTrace = $flowRetArr['trace'];
        
        // save log for triggered flow
        $triggerLog->triggerLog = CJSON::encode(
                        array_merge(array($triggerInfo), array($flowTrace)));
        $triggerLog->save();

        self::$_triggerDepth--;  // this trigger call is done; decrement the stack depth
        return $flowRetArr;
    }

    /**
     * Legacy method for cron events created in flows before 5.2. This could be removed if a 
     * migration script were written which migrated old x2_cron_event records containing flow
     * paths to the 5.2+ system which uses flow action ids.
     * 
     * Recursive method for traversing a flow tree using $flowPath, allowing us to
     * instantly skip to any point in a flow. This is used for delayed execution with X2CronAction.
     *
     * @param array $flowPath directions to the current position in the flow tree
     * @param array $flowItems the items in this branch
     * @param array &$params an associative array of params, usually including 'model'=>$model,
     * @param integer $pathIndex the position $flowPath to start at (for recursion), defaults to 0
     */
    private function traverseLegacy(
    $flowPath, &$flowItems, &$params, $pathIndex = 0, $triggerLogId = null) {

        // if it's true or false, skip directly to the next true/false fork
        if (is_bool($flowPath[$pathIndex])) {
            foreach ($flowItems as &$item) {
                if (is_subclass_of($item['type'], 'MultiChildNode')) {
                    $nodeClass = $item['type'];
                    if ($flowPath[$pathIndex] && isset($item[$nodeClass::getRightChildName()])) {
                        if (isset($flowPath[$pathIndex + 1])) {
                            return $this->traverseLegacy(
                                            $flowPath, $item[$nodeClass::getRightChildName()], $params, $pathIndex + 1, $triggerLogId);
                        } else {
                            return array(
                                $item['type'], true,
                                $this->executeBranch(
                                        $item[$nodeClass::getRightChildName], $params, $triggerLogId)
                            );
                        }
                    } elseif (!$flowPath[$pathIndex] &&
                            isset($item[$nodeClass::getLeftChildName()])) {

                        if (isset($flowPath[$pathIndex + 1])) {
                            return $this->traverseLegacy(
                                            $flowPath, $item[$nodeClass::getLeftChildName()], $params, $pathIndex + 1, $triggerLogId);
                        } else {
                            return array(
                                $item['type'], false,
                                $this->executeBranch(
                                        $item[$nodeClass::getLeftChildName()], $params, $triggerLogId)
                            );
                        }
                    }
                }
            }
            return false;
        } else { // we're in the final branch, so just execute it starting at the specified index
            if (isset($flowPath[$pathIndex])) {
                $sliced = array_slice($flowItems, $flowPath[$pathIndex]);
                return $this->executeBranch($sliced, $params, $triggerLogId);
            }
        }
    }

    /**
     * Jump to flow action with specified action id and resume flow from subsequent action
     * @param iint $actionId unique id of flow action
     * @param array $flowItems the items in this branch
     * @param array &$params an associative array of params, usually including 'model'=>$model,
     */
    private function traverse($actionId, &$flowItems, &$params, $triggerLogId = null) {
        if (is_array($actionId)) {
            return $this->traverseLegacy(
                            $actionId, $flowItems, $params, 0, $triggerLogId);
        } else {
            $i = 0;
            // depth-first search for matching action id
            foreach ($flowItems as $item) {
                if ($item['id'] === $actionId) {
                    $sliced = array_slice($flowItems, $i + 1);
                    return $this->executeBranch($sliced, $params, $triggerLogId);
                }
                if (is_subclass_of($item['type'], 'MultiChildNode')) {
                    $nodeClass = $item['type'];
                    if (isset($item[$nodeClass::getLeftChildName()])) {
                        $ret = $this->traverse(
                                $actionId, $item[$nodeClass::getLeftChildName()], $params, $triggerLogId);
                        if ($ret)
                            return $ret;
                    }
                    if (isset($item[$nodeClass::getRightChildName()])) {
                        $ret = $this->traverse(
                                $actionId, $item[$nodeClass::getRightChildName()], $params, $triggerLogId);
                        if ($ret)
                            return $ret;
                    }
                }
                $i++;
            }
        }
        return false;
    }

    /**
     * Executes each action in a given branch, starting at $start.
     *
     * @param array $flowPath directions to the current position in the flow tree
     * @param array $flowItems the items in this branch
     * @param array &$params an associative array of params, usually including 'model'=>$model,
     * @param integer $start the position in the branch to start at, defaults to 0
     */
    public function executeBranch(&$flowItems, &$params, $triggerLogId = null, $id = null) {
        $results = array();
        for ($i = 0; $i < count($flowItems); $i++) {
            $item = &$flowItems[$i];
            if (!isset($item['type']) || !class_exists($item['type']))
                continue;

            $node = X2FlowItem::create($item);
            if ($item['type'] === 'X2FlowSwitch') {
                $validateRetArr = $node->validate($params, $this->id);
                if ($validateRetArr[0]) {

                    $checkRetArr = $node->check($params);
                    if ($checkRetArr[0] && isset($item['trueBranch'])) {
                        $results[] = array(
                            $item['type'], true,
                            $this->executeBranch(
                                    $item['trueBranch'], $params, $triggerLogId, $id)
                        );
                    } elseif (isset($item['falseBranch'])) {
                        $results[] = array(
                            $item['type'], false,
                            $this->executeBranch(
                                    $item['falseBranch'], $params, $triggerLogId, $id)
                        );
                    }
                }
            } elseif ($item['type'] === 'X2FlowSplitter') {
                $validateRetArr = $node->validate($params, $this->id);
                if ($validateRetArr[0]) {
                    // right to left pre-order traversal
                    $branchVal = true;
                    if (isset($item[X2FlowSplitter::getRightChildName()])) {
                        $results[] = array(
                            $item['type'], true,
                            $this->executeBranch(
                                    $item[X2FlowSplitter::getRightChildName()], $params, $triggerLogId, $id)
                        );
                    }
                    if (isset($item[X2FlowSplitter::getLeftChildName()])) {
                        $results[] = array(
                            $item['type'], false,
                            $this->executeBranch(
                                    $item[X2FlowSplitter::getLeftChildName()], $params, $triggerLogId, $id)
                        );
                    }
                }
            } else {
                $flowAction = X2FlowAction::create($item);
                if ($item['type'] === 'X2FlowWait') {
                    $node->flowId = $this->id;
                    $results[] = $this->validateAndExecute(
                            $item, $node, $params, $triggerLogId, $id);
                    break;
                } else {
                    $results[] = $this->validateAndExecute(
                            $item, $node, $params, $triggerLogId, $id);
                }
            }
        }
        return $results;
    }

    public function validateAndExecute($item, $flowAction, &$params, $triggerLogId = null, $id = null) {
        $logEntry;
        $validationRetStatus = $flowAction->validate($params, $this->id);
        if ($validationRetStatus[0] === true) {
            $logEntry = array($item['type'], $flowAction->execute($params, $triggerLogId, $this));
        } else {
            $logEntry = array($item['type'], $validationRetStatus);
        }
        
        return $logEntry;
    }

    /*
     * Parses variables in curly brackets and evaluates expressions
     *
     * @param mixed $value the value as specified by 'attributes' in {@link X2FlowAction::$config}
     * @param string $type the X2Fields type for this value
     * @return mixed the parsed value
     */

    public static function parseValue($value, $type, &$params = null, $renderFlag = true) {
        if (is_string($value)) {
            if (strpos($value, '=') === 0) {
                // It's a formula. Evaluate it.
                $evald = X2FlowFormatter::parseFormula($value, $params);

                // Fail silently because there's not yet a good way of reporting
                // problems that occur in parseFormula --
                $value = '';
                if ($evald[0])
                    $value = $evald[1];
            } else {
                // Run token replacement:
                $value = X2FlowFormatter::replaceVariables(
                                $value, $params, $type, $renderFlag, true);
            }
        }

        switch ($type) {
            case 'boolean':
                return (bool) $value;
            case 'time':
            case 'date':
            case 'dateTime':
                if (ctype_digit((string) $value))  // must already be a timestamp
                    return $value;
                if ($type === 'date')
                    $value = Formatter::parseDate($value);
                elseif ($type === 'dateTime')
                    $value = Formatter::parseDateTime($value);
                else
                    $value = strtotime($value);
                return $value === false ? null : $value;
            case 'link':
                $pieces = explode('_', $value);
                if (count($pieces) > 1)
                    return $pieces[0];
                return $value;
            case 'tags':
                return Tags::parseTags($value);
            default:
                return $value;
        }
    }

    public static function getModelTypes($assoc = false) {
        return array_diff_key(
                X2Model::getModelTypes($assoc), array_flip(array('Fingerprint', 'Charts', 'EmailInboxes')));
    }

    /**
     * Executes a flow, starting by checking the trigger, passing params to each trigger/action,
     * and calling {@link X2Flow::executeBranch()}
     *
     * @param X2Flow &$flow the object representing the flow to run
     * @param array &$params an associative array of params, usually including 'model'=>$model,
     * @param mixed $actionId a unique id for the flow action where flow execution should start.
     *  This can also be an array of directions to the flow action (legacy option). 
     * Will skip checking the trigger conditions if not null, otherwise runs the entire flow.
     */
    private static function _executeFlow(&$flow, &$params, $actionId = null, $triggerLogId = null) {
        $error = ''; //array($flow->name);
        $flowData = $flow->getFlow();
        
        if ($flowData !== false &&
                isset($flowData['trigger']['type'], $flowData['items'][0]['type'])) {

            if ($actionId === null) {
                $trigger = X2FlowTrigger::create($flowData['trigger']);
                assert($trigger !== null);
                if ($trigger === null) {
                    $error = array(
                        'trace' => array(false, 'failed to load trigger class'));
                }
                $validateRetArr = $trigger->validate($params, $flow->id);
                if (!$validateRetArr[0]) {
                    $error = $validateRetArr;
                    return array('trace' => $error);
                } else if (sizeof($validateRetArr) === 3) { // trigger has return value
                    return array(
                        'trace' => $validateRetArr,
                        'retVal' => $validateRetArr[2]
                    );
                }
                $checkRetArr = $trigger->check($params);
                if (!$checkRetArr[0]) {
                    $error = $checkRetArr;
                }

                if (empty($error)) {
                    try {
                        $flowTrace = array(true, $flow->executeBranch(
                                    $flowData['items'], $params, $triggerLogId, $flow->id));
                        $flowRetVal = self::extractRetValFromTrace($flowTrace);
                        if (!$flowRetVal) {
                            $flowRetVal = $trigger->getDefaultReturnVal($flow->id);
                        }
                        return array(
                            'trace' => $flowTrace,
                            'retVal' => $flowRetVal,
                        );
                    } catch (Exception $e) {
                        return array('trace' => array(false, $e->getMessage()));
                        // whatever.
                    }
                } else {
                    return array('trace' => $error);
                }
            } else { // $actionId provided, skip to the specified position using X2Flow::traverse()
                try {
                    return array(
                        'trace' => array(
                            true, $flow->traverse(
                                    $actionId, $flowData['items'], $params, $triggerLogId)));
                } catch (Exception $e) {
                    return array(
                        'trace' => array(false, $e->getMessage())); // whatever.
                }
            }
        } else {
            return array('trace' => array(false, 'invalid flow data'));
        }
    }

    /**
     * Helper method for validateFlow. Used to recursively traverse flow while performing
     * validation on each item.
     * @param array $items 
     */
    private function validateFlowPrime($items) {
        $valid = true;

        foreach ($items as $item) {
            if (!isset($item['type']) || !class_exists($item['type'])) {
                continue;
            }
            if (is_subclass_of($item['type'], 'MultiChildNode')) {
                $nodeClass = $item['type'];
                if (isset($item['type'][$nodeClass::getLeftChildName()])) {
                    if (!$this->validateFlowPrime($item[$nodeClass::getLeftChildName()])) {
                        $valid = false;
                        break;
                    }
                }
                if (isset($item['type'][$nodeClass::getRightChildName()])) {
                    if (!$this->validateFlowPrime($item[$nodeClass::getRightChildName()])) {
                        $valid = false;
                        break;
                    }
                }
            } else {
                $valid = $this->validateFlowItem($item);
                if (!$valid) {
                    break;
                }
            }
        }
        return $valid;
    }

    /**
     * Validates flow item (trigger or action) 
     * @return bool true if options are valid, false otherwise
     */
    private function validateFlowItem($config, $action = true) {
        $class = $action ? 'X2FlowAction' : 'X2FlowTrigger';
        $flowItem = $class::create($config);
        $paramRules = $flowItem->paramRules();
        list ($success, $message) = $flowItem->validateOptions($paramRules, null, true);
        if ($success === false) {
            $this->addError('flow', $flowItem->title . ': ' . $message);
            return false;
        } else if ($success === X2FlowItem::VALIDATION_WARNING) {
            Yii::app()->user->setFlash(
                    'notice', Yii::t('studio', $message));
        }
        return true;
    }

}
