<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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

class X2Flow extends CActiveRecord {

    /**
     * @const max number of nested calls to {@link X2Flow::trigger()}
     */
    const MAX_TRIGGER_DEPTH = 0;

    /**
     * @var the current depth of nested trigger calls
     */
    private static $_triggerDepth = 0;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return X2Flow the static model class
     */
    public static function model($className = __CLASS__){
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName(){
        return 'x2_flows';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules(){
        return array(
            array('name, createDate, lastUpdated, triggerType, flow', 'required'),
            array('createDate, lastUpdated', 'numerical', 'integerOnly' => true),
            array('active', 'boolean'),
            array('name', 'length', 'max' => 100),
            array ('flow', 'validateFlow'),
            array('triggerType, modelClass', 'length', 'max' => 40),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, active, name, createDate, lastUpdated', 'safe', 'on' => 'search'),
        );
    }

    /**
     * Helper method for validateFlow. Used to recursively traverse flow while performing
     * validation on each item.
     * @param array $items 
     */
    private function validateFlowPrime ($items) {
        $valid = true;

        foreach ($items as $item) {
            if(!isset($item['type']) || !class_exists($item['type'])) {
                continue;
            }
            if ($item['type'] === 'X2FlowSwitch') {
                if (isset ($item['type']['falseBranch'])) {
                    if (!$this->validateFlowPrime ($item['falseBranch'])) {
                        $valid = false;
                        break;
                    }
                }
                if (isset ($item['type']['trueBranch'])) {
                    if (!$this->validateFlowPrime ($item['trueBranch'])) {
                        $valid = false;
                        break;
                    }
                }
            } else {
                $valid = $this->validateFlowItem ($item);
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
    private function validateFlowItem ($config, $action=true) {
        $class = $action ? 'X2FlowAction' : 'X2FlowTrigger';
        $flowItem = $class::create ($config);
        $paramRules = $flowItem->paramRules (); 
        list ($success, $message) = $flowItem->validateOptions ($paramRules, null, true);
        if ($success === false) {
            $this->addError ('flow', $flowItem->title.': '.$message);
            return false;
        } else if ($success === X2FlowItem::VALIDATION_WARNING) {
            Yii::app()->user->setFlash (
                'notice', Yii::t('studio', $message));
        }
        return true;
    }

    /**
     * Ensure validity of specified config options
     */
    public function validateFlow ($attribute) {
        $flow = CJSON::decode ($this->$attribute);

        if(isset($flow['trigger']) && isset ($flow['items'])) {
            if (!$this->validateFlowItem ($flow['trigger'])) {
                return false;
            }
            if ($this->validateFlowPrime ($flow['items'])) {
                return true;
            } 
        } else {
            $this->addError ($attribute, Yii::t('studio', 'Invalid flow'));
            return false;
        }
    }

    public function afterSave () {
        $flow = CJSON::decode ($this->flow);
        $triggerClass = $flow['trigger']['type'];
        if ($triggerClass === 'PeriodicTrigger') {
            $trigger = X2FlowTrigger::create ($flow['trigger']);
            $trigger->afterFlowSave ($this);
        }
        parent::afterSave ();
    }

    /**
     * Returns a list of behaviors that this model should behave as.
     * @return array the behavior configurations (behavior name=>behavior configuration)
     */
    public function behaviors(){
        return array(
            'X2TimestampBehavior' => array('class' => 'X2TimestampBehavior'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels(){
        return array(
            'id' => Yii::t('admin', 'ID'),
            'active' => Yii::t('admin', 'Active'),
            'name' => Yii::t('admin', 'Name'),
            'triggerType' => Yii::t('admin', 'Trigger'),
            'modelClass' => Yii::t('admin', 'Type'),
            'name' => Yii::t('admin', 'Name'),
            'createDate' => Yii::t('admin', 'Create Date'),
            'lastUpdated' => Yii::t('admin', 'Last Updated'),
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search(){
        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('active', $this->active);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('createDate', $this->createDate, true);
        $criteria->compare('lastUpdated', $this->lastUpdated, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
            'pagination' => array (
                'pageSize' => Profile::getResultsPerPage(),
            ),
        ));
    }

    /**
     * Validates the JSON data in $flow.
     * Sets createDate and lastUpdated.
     * @return boolean whether or not to proceed to validation
     */
    public function beforeValidate(){
        $flowData = CJSON::decode($this->flow);

        if($flowData === false){
            $this->addError('flow', Yii::t('studio', 'Flow configuration data appears to be '.
                'corrupt.'));
            return false;
        }
        if(isset($flowData['trigger']['type'])){
            $this->triggerType = $flowData['trigger']['type'];
            if(isset($flowData['trigger']['modelClass']))
                $this->modelClass = $flowData['trigger']['modelClass'];
        } else{
            // $this->addError('flow',Yii::t('studio','You must configure a trigger event.'));
        }
        if(!isset($flowData['items']) || empty($flowData['items'])){
            $this->addError('flow', Yii::t('studio', 'There must be at least one action in the '.
                'flow.'));
        }

        $this->lastUpdated = time();
        if($this->isNewRecord)
            $this->createDate = $this->lastUpdated;
        return parent::beforeValidate();
    }

    /**
     * Returns the current trigger stack depth {@link X2Flow::$_triggerDepth}
     * @return int the stack depth
     */
    public static function triggerDepth(){
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
    public static function trigger($triggerName, $params = array()){
        if(self::$_triggerDepth > self::MAX_TRIGGER_DEPTH) // ...have we delved too deep?
            return;

        $triggeredAt = time ();

        if(isset($params['model']) &&
           (!is_object($params['model']) || !($params['model'] instanceof X2Model))) {
            // Invalid model provided
            return false;
        }
        
        // Communicate the event to third-party systems, if any
        ApiHook::runAll($triggerName,$params);

        // increment stack depth before doing anything that might call X2Flow::trigger()
        self::$_triggerDepth++;

        $flowAttributes = array('triggerType' => $triggerName, 'active' => 1);

        if(isset($params['model'])){
            $flowAttributes['modelClass'] = get_class($params['model']);
            $params['modelClass'] = get_class($params['model']);
        }

        // if flow id is specified, only execute flow with specified id
        if (isset ($params['flowId'])) $flowAttributes['id'] = $params['flowId'];

        $flowTraces = array();
        $flows = CActiveRecord::model('X2Flow')->findAllByAttributes($flowAttributes);

        // collect information about trigger for the trigger log.
        $triggerInfo = array (
            'triggerName' => Yii::t('studio', X2FlowItem::getTitle ($triggerName))
        );
        if (isset ($params['model']) && is_subclass_of($params['model'],'X2Model') &&
            $params['model']->asa ('X2LinkableBehavior')) {
            $triggerInfo['modelLink'] =
                Yii::t('studio', 'View record: ').$params['model']->getLink ();
        }

        // find all flows matching this trigger and modelClass
        $triggerLog;
        $flowTrace;
        $flowRetVal = null;
        foreach($flows as &$flow) {

            $triggerLog = new TriggerLog;
            $triggerLog->triggeredAt = $triggeredAt;
            $triggerLog->flowId = $flow->id;
            $triggerLog->save ();

            $flowRetArr = self::executeFlow($flow, $params, null, $triggerLog->id);
            $flowTrace = $flowRetArr['trace'];
            $flowRetVal = (isset ($flowRetArr['retVal'])) ? $flowRetArr['retVal'] : null;
            $flowRetVal = self::extractRetValFromTrace ($flowTrace);
            $flowTraces[] = $flowTrace;

            // save log for triggered flow
            $triggerLog->triggerLog =
                CJSON::encode (array_merge (array ($triggerInfo), array ($flowTrace)));
            $triggerLog->save ();
        }

        // old logging system, uncomment to enable file based logging
        /*file_put_contents('triggerLog.txt', $triggerName.":\n", FILE_APPEND);
        file_put_contents('triggerLog.txt', print_r($flowTraces, true).":\n", FILE_APPEND);*/

        self::$_triggerDepth--;  // this trigger call is done; decrement the stack depth
        return $flowRetVal;
    }

    /**
     * Traverses the trace tree and checks if the last action has a return value. If so, that 
     * value is returned. Otherwise, null is returned.
     * 
     * @param array the trace returned by executeFlow
     * @return mixed null if there's no return value, mixed otherwise
     */
    private static function extractRetValFromTrace ($flowTrace) {
        //AuxLib::debugLog ('extractRetValFromTrace');
        // trigger itself has return val

        if (sizeof ($flowTrace) === 3 && $flowTrace[0] && !is_array ($flowTrace[1])) {
            return $flowTrace[2];
        }

        // ensure that initial branch executed without errors
        if (sizeof ($flowTrace) < 2 || !$flowTrace[0] || !is_array ($flowTrace[1])) {
            return null;
        }

        // find last action
        $startOfBranchExecution = $flowTrace[1];
        $lastAction = $startOfBranchExecution[sizeof ($startOfBranchExecution) - 1];
        while (true) {
            if ($lastAction[0] === 'X2FlowSwitch') {
                $startOfBranchExecution = $lastAction[2];
                if (sizeof ($startOfBranchExecution) > 0) {
                    $lastAction = $startOfBranchExecution[sizeof ($startOfBranchExecution) - 1];
                } else {
                    return null;
                }
            } else {
                break;
            }
        }

        // if last action has return value, return it
        if (sizeof ($lastAction[1]) === 3) return $lastAction[1][2];
    }

    /**
     * Can be called to resume execution of flow that paused for the wait action. 
     * @param X2Flow &$flow the object representing the flow to run
     * @param array &$params an associative array of params, usually including 'model'=>$model,
     * @param mixed $flowPath an array of directions to a specific point in the flow. Defaults to
     *  null.
     */
    public static function resumeFlowExecution (
        &$flow, &$params, $flowPath = null, $triggerLogId=null){

        if(self::$_triggerDepth > self::MAX_TRIGGER_DEPTH) // ...have we delved too deep?
            return;

        if(isset($params['model']) &&
           (!is_object($params['model']) || !($params['model'] instanceof X2Model))) {
            // Invalid model provided
            return false;
        }

        // increment stack depth before doing anything that might call X2Flow::trigger()
        self::$_triggerDepth++;
        $result =  self::executeFlow ($flow, $params, $flowPath, $triggerLogId);
        self::$_triggerDepth--;  // this trigger call is done; decrement the stack depth
        return $result;
    }

    /**
     * Executes a flow, starting by checking the trigger, passing params to each trigger/action,
     * and calling {@link X2Flow::executeBranch()}
     *
     * @param X2Flow &$flow the object representing the flow to run
     * @param array &$params an associative array of params, usually including 'model'=>$model,
     * @param mixed $flowPath an array of directions to a specific point in the flow. Defaults to
     *  null.
     * Will skip checking the trigger conditions if not null, otherwise runs the entire flow.
     */
    private static function executeFlow(&$flow, &$params, $flowPath = null, $triggerLogId=null){
        $error = ''; //array($flow->name);

        $flowData = CJSON::decode($flow->flow); // parse JSON flow data
        // file_put_contents('triggerLog.txt',"\n".print_r($flowData,true),FILE_APPEND);

        if($flowData !== false &&
           isset($flowData['trigger']['type'], $flowData['items'][0]['type'])){

            if($flowPath === null){
                //AuxLib::debugLogR ('creating trigger');
                $trigger = X2FlowTrigger::create($flowData['trigger']);
                assert ($trigger !== null);
                if($trigger === null) {
                    $error = array (
                        'trace' => array (false, 'failed to load trigger class'));
                }
                //AuxLib::debugLogR ('validating');
                $validateRetArr = $trigger->validate($params, $flow->id);
                if (!$validateRetArr[0]) {
                    $error = $validateRetArr;
                    return array ('trace' => $error);
                } else if (sizeof ($validateRetArr) === 3) { // trigger has return value
                    return array (
                        'trace' => $validateRetArr,
                        'retVal' => $validateRetArr[2]
                    );
                }
                //AuxLib::debugLogR ('checking');
                $checkRetArr = $trigger->check($params);
                if (!$checkRetArr[0]) {
                    $error = $checkRetArr;
                } 
                //AuxLib::debugLogR ('done checking');

                if(empty($error)){
                    try{
                        //AuxLib::debugLogR ('executeBranch');
                        $flowTrace = array (true, $flow->executeBranch (
                            array(0), $flowData['items'], $params, 0, $triggerLogId));
                        //AuxLib::debugLog ('executing branch complete');
                        //AuxLib::debugLogR ($flowTrace);
                        $flowRetVal = self::extractRetValFromTrace ($flowTrace);
                        //AuxLib::debugLog ('extractRetValFromTrace ret');
                        if (!$flowRetVal) {
                            $flowRetVal = $trigger->getDefaultReturnVal ($flow->id); 
                        }
                        return array (
                            'trace' => $flowTrace,
                            'retVal' => $flowRetVal,
                        );
                    }catch(Exception $e){
                        //AuxLib::debugLogR ($e->getTrace ());
                        //AuxLib::debugLogR ($e->getMessage ());
                        return array ('trace' => array (false, $e->getMessage()));
                        // whatever.
                    }
                } else {
                    return array ('trace' => $error);
                }
            }else{ // $flowPath provided, skip to the specified position using X2Flow::traverse()
                try{
                    return array (
                        'trace' => array (
                            true, $flow->traverse(
                                $flowPath, $flowData['items'], $params, 0, $triggerLogId)));
                } catch(Exception $e) {
                    //AuxLib::debugLogR ($e->getTrace ());
                    //AuxLib::debugLogR ($e->getMessage ());
                    return array (
                        'trace' => array (false, $e->getMessage())); // whatever.
                }
            }
        }else{
            return array ('trace' => array (false, 'invalid flow data'));
        }
    }

    /**
     * Recursive method for traversing a flow tree using $flowPath, allowing us to
     * instantly skip to any point in a flow. This is used for delayed execution with X2CronAction.
     *
     * @param array $flowPath directions to the current position in the flow tree
     * @param array $flowItems the items in this branch
     * @param array &$params an associative array of params, usually including 'model'=>$model,
     * @param integer $pathIndex the position $flowPath to start at (for recursion), defaults to 0
     */
    public function traverse($flowPath, &$flowItems, &$params, $pathIndex = 0, $triggerLogId=null){

        // if it's true or false, skip directly to the next true/false fork
        if(is_bool($flowPath[$pathIndex])){
            foreach($flowItems as &$item){
                if($item['type'] === 'X2FlowSwitch'){
                    if($flowPath[$pathIndex] && isset($item['trueBranch'])){
                        if(isset($flowPath[$pathIndex + 1])) {
                            return $this->traverse(
                                $flowPath, $item['trueBranch'], $params, $pathIndex + 1,
                                $triggerLogId);
                        } else {
                            return array(
                                $item['type'], true,
                                $this->executeBranch(
                                    $flowPath, $item['trueBranch'], $params, 0, $triggerLogId)
                            );
                        }
                    } elseif(!$flowPath[$pathIndex] && isset($item['falseBranch'])){
                        if(isset($flowPath[$pathIndex + 1])) {
                            return $this->traverse(
                                    $flowPath, $item['falseBranch'], $params, $pathIndex + 1,
                                    $triggerLogId);
                        } else {
                            return array(
                                $item['type'], false,
                                $this->executeBranch(
                                    $flowPath, $item['falseBranch'], $params, 0, $triggerLogId)
                            );
                        }
                    }
                }
            }
            return false;
        } else { // we're in the final branch, so just execute it starting at the specified index
            if(isset($flowPath[$pathIndex]))
                return $this->executeBranch(
                    $flowPath, $flowItems, $params, $flowPath[$pathIndex], $triggerLogId);
        }
    }

    /**
     * Executes each action in a given branch, starting at $start.
     *
     * @param array $flowPath directions to the current position in the flow tree
     * @param array $flowItems the items in this branch
     * @param array &$params an associative array of params, usually including 'model'=>$model,
     * @param integer $start the position in the branch to start at, defaults to 0
     */
    public function executeBranch($flowPath, &$flowItems, &$params, $start = 0, $triggerLogId=null){
        $results = array();

        for($i = $start; $i < count($flowItems); $i++){
            $item = &$flowItems[$i];
            if(!isset($item['type']) || !class_exists($item['type']))
                continue;

            if($item['type'] === 'X2FlowSwitch'){
                $switch = X2FlowItem::create($item);
                $validateRetArr = $switch->validate($params, $this->id);
                if($validateRetArr[0]){

                    // flowPath only contains switch decisions and the index on the current branch
                    array_pop($flowPath);

                    // now that we're at another switch, we can throw out the previous branch index
                    // eg: $flowPath = array(true,false,3) means go to true at the first fork,
                    // go to false at the second fork, then go to item 3

                    $checkRetArr = $switch->check($params);
                    if($checkRetArr[0] && isset($item['trueBranch'])){
                        $flowPath[] = true;
                        $flowPath[] = 0; // they're now on
                        $results[] = array(
                            $item['type'], true,
                            $this->executeBranch(
                                $flowPath, $item['trueBranch'], $params, 0, $triggerLogId)
                        );
                    }elseif(isset($item['falseBranch'])){
                        $flowPath[] = false;
                        $flowPath[] = 0;
                        $results[] = array(
                            $item['type'], false,
                            $this->executeBranch(
                                $flowPath, $item['falseBranch'], $params, 0, $triggerLogId)
                        );
                    }
                }
            }else{
                $flowAction = X2FlowAction::create($item);
                if($item['type'] === 'X2FlowWait'){
                    $flowAction->flowPath = $flowPath;
                    $flowAction->flowId = $this->id;
                    $results[] = $this->validateAndExecute (
                        $item, $flowAction, $params, $triggerLogId);
                    break;
                }else{
                    $flowPath[count($flowPath) - 1]++; // increment the index in the current branch
                    $results[] = $this->validateAndExecute (
                        $item, $flowAction, $params, $triggerLogId);
                }
            }
        }
        return $results;
    }

    public function validateAndExecute ($item, $flowAction, &$params, $triggerLogId=null) {
        $logEntry;
        $validationRetStatus = $flowAction->validate($params, $this->id);
        if ($validationRetStatus[0] === true) {
            $logEntry = array ($item['type'], $flowAction->execute ($params, $triggerLogId, $this));
        } else {
            $logEntry = array ($item['type'], $validationRetStatus);
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
    public static function parseValue($value, $type, &$params = null, $renderFlag=true){
        if (is_string ($value)){
            if (strpos ($value, '=') === 0){
                // It's a formula. Evaluate it.
                $evald = X2FlowFormatter::parseFormula($value, $params);

                // Fail silently because there's not yet a good way of reporting
                // problems that occur in parseFormula --
                $value = '';
                if($evald[0])
                    $value = $evald[1];
            } else {
                // Run token replacement:
                $value = X2FlowFormatter::replaceVariables(
                    $value, $params, $type, $renderFlag, true);
            }
        }

        switch($type){
            case 'boolean':
                return (bool) $value;
            case 'time':
            case 'date':
            case 'dateTime':
                if(ctype_digit((string) $value))  // must already be a timestamp
                    return $value;
                if($type === 'date')
                    $value = Formatter::parseDate($value);
                elseif($type === 'dateTime')
                    $value = Formatter::parseDateTime($value);
                else
                    $value = strtotime($value);
                return $value === false ? null : $value;
            case 'link':
                $pieces = explode('_', $value);
                if(count($pieces) > 1)
                    return $pieces[0];
                return $value;
            case 'tags':
                return Tags::parseTags ($value);
            default:
                return $value;
        }
    }


    public static function getModelTypes($assoc=false) {
        return array_diff_key (X2Model::getModelTypes ($assoc), array_flip (array ('Fingerprint')));
    }

}
