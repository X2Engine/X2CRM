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




Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_actions".
 * @package application.modules.actions.models
 */
class Actions extends X2Model {

    /**
     * If we want this to be user configurable, then the height of the list view items container 
     * needs to be set dynamically. Otherwise, infinity scrolling will break.
     */
    const ACTION_INDEX_PAGE_SIZE = 20;

    const COLORS_DROPDOWN_ID = 123;

    /**
     * Setting associationType to this implies that the action is associated via the 
     * x2_action_to_record table
     */
    const ASSOCIATION_TYPE_MULTI = '__multiple__';

    public $upload;

    public $skipActionTimers = false;

    public $supportsWorkflow = false;

    /**
     * Types of actions that should be treated as emails
     * @var type
     */
    public static $emailTypes = array(
        'email', 'emailFrom','emailOpened','email_invoice', 'email_quote');

    public $verifyCode; // CAPTCHA for guests using the publisher
    public $actionDescriptionTemp = ''; // Easy way to get around action text records

    private $metaDataTemp = array (
        'eventSubtype' => null,
        'eventStatus' => null,
        
        'emailImapUid' => null,
        'emailInboxId' => null,
        'emailFolderName' => null,
        'emailUidValidity' => null,
        
        'etag'=> null,
        'remoteCalendarUrl' => null,
        
    );

    private static $_priorityLabels;

    /* static variable to allow calling findAll without actionText */ 
    private static $withActionText = true;

    /**
     * Add note to model 
     * @param X2Model $model model to which note should be added
     * @param string $note
     */
    public static function associateAction (X2Model $model, array $attributes) {
        $now = time ();
        $action = new Actions;
        $action->setAttributes (array_merge (array (
            'assignedTo' => $model->assignedTo,
            'visibility' => '1',
            'associationType' => X2Model::getAssociationType (get_class ($model)),
            'associationId' => $model->id,
            'associationName' => $model->name,
            'createDate' => $now,
            'lastUpdated' => $now,
            'completeDate' => $now,
            'complete' => 'Yes',
            'updatedBy' => 'admin',
        ), $attributes), false);
        return $action->save();
    }

    /**
     * Get names of CFormModel classes associated with action subtypes 
     * @return array
     */
    public static function getFormTypes () {
        return array_merge (
            array ('Actions'),
            array ('CalendarEventFormModel'),
            array_map (function ($type) {
                return ucfirst ($type).'FormModel';
            }, array (
                'action',
                'time',
                'event',
                'products',
                'call',
                'note',
            )));
    }

    /**
     * Returns the static model of the specified AR class.
     * @return Actions the static model class
     */
    public static function model($className = __CLASS__){
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName(){
        return 'x2_actions';
    }

    public function behaviors(){
        $behaviors =  array(
            'LinkableBehavior' => array(
                'class' => 'LinkableBehavior',
                'module' => 'actions'
            ),
            'TimestampBehavior' => array('class' => 'TimestampBehavior'),
            'FlowTriggerBehavior' => array('class' => 'FlowTriggerBehavior'),
            'TagBehavior' => array('class' => 'TagBehavior'),
            'ERememberFiltersBehavior' => array(
                'class' => 'application.components.behaviors.ERememberFiltersBehavior',
                'defaults' => array(),
                'defaultStickOnClear' => false
            ),
            'permissions' => array('class' => Yii::app()->params->modelPermissions),
            'RelatedMediaBehavior' => array(
                'class' => 'application.components.behaviors.RelatedMediaBehavior',
                'fileAttribute' => 'upload'
            ),
        );
        if(!$this->isNewRecord && $this->type==='event'){
            $emailAddresses = Yii::app()->db->createCommand()
                ->select('email')
                ->from('x2_calendar_invites')
                ->where('actionId = :id',array(':id' => $this->id))
                ->queryColumn();
            $behaviors['CalendarInviteBehavior'] = array(
                'class' => 'CalendarInviteBehavior',
                'emailAddresses' => $emailAddresses,
            );
        }
        return $behaviors;
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules(){
        return array_merge (
            $this->getBehaviorRules (),
            array(
                array('allDay', 'boolean'),
                array('associationId,associationType','requiredAssoc'),
                array('createDate, completeDate, lastUpdated, calendarId', 'numerical', 'integerOnly' => true),
                array(
                    'id,assignedTo,actionDescription,visibility,associationId,associationType,'.
                    'associationName,dueDate,priority,type,createDate,complete,reminder,'.
                    'completedBy,completeDate,lastUpdated,updatedBy,color,subject', 'safe'),
                array(
                    'verifyCode', 'captcha', 'allowEmpty' => !CCaptcha::checkRequirements(), 
                    'on' => 'guestCreate'),
                array ('notificationUsers', 'validateNotificationUsers'),
            )
        );
    }

    public function validateNotificationUsers ($attribute) {
        $value = $this->$attribute;
        return in_array ($value, array ('me', 'assigned', 'both'));
    }

    /**
     * @return array relational rules.
     */
    public function relations(){
        return array_merge(parent::relations(), array(
            'workflow' => array(self::BELONGS_TO, 'Workflow', 'workflowId'),
            'workflowStage' => array(self::BELONGS_TO, 'WorkflowStage', 'stageNumber'),
            'actionMetaData' => array(self::HAS_ONE, 'ActionMetaData', 'actionId'),
            'actionText' => array(self::HAS_ONE, 'ActionText', 'actionId'),
            'timers' => array(self::HAS_MANY,'ActionTimer','actionId'),
            'media' => array (
                self::MANY_MANY, 'Media', 'x2_actions_to_media(actionsId, mediaId)'),
            'location' => array(self::BELONGS_TO, 'Locations', 'locationId'),
            'invites' => array(self::HAS_MANY,'CalendarInvites','actionId'),
            //'assignee' => array(self::BELONGS_TO,'User',array('assignedTo'=>'username')),
        ));
    }


    public function setX2Fields(&$data, $filter = false, $bypassPermissions=false) {
        if (isset ($data['lineitem'])) {
            $this->setActionLineItems ($data['lineitem']);
        }
        return parent::setX2Fields ($data, $filter, $bypassPermissions);
    }

    /**
     * Associate this action with a record (using join table)
     * @param X2Model $model 
     * @return mixed false if model couldn't be saved, -1 if association already exists, true
     *  if successful
     * @throws CException if this action has an invalid association type
     */
    public function multiAssociateWith (X2Model $model) {
        if ($this->associationType !== self::ASSOCIATION_TYPE_MULTI) {
            throw new CException (
                'Attempting to multi-associate action with single association type');
        }
        $joinModel = ActionToRecord::model ()->findByAttributes (array (
            'actionId' => $this->id,
            'recordId' => $model->id,
            'recordType' => get_class ($model),
        ));
        if ($joinModel) return -1;
        $joinModel = new ActionToRecord; 
        $joinModel->setAttributes (array (
            'actionId' => $this->id,
            'recordId' => $model->id,
            'recordType' => get_class ($model),
        ), false);
        return $joinModel->save ();
    }

    /**
     * Retrieve the associated models
     * @return array of associated models, indexed by model type
     */
    public function getMultiassociations ($modelClass = null) {
        $multiAssociations = array();
        $attributes = array ('actionId' => $this->id);
        if (!is_null($modelClass))
            $attributes['recordType'] = $modelClass;
        $joinModels = ActionToRecord::model ()->findAllByAttributes ($attributes);
        foreach ($joinModels as $model) {
            $modelRecord = X2Model::model($model->recordType)->findByPk ($model->recordId);
            $multiAssociations[$model->recordType][] = $modelRecord;
        }
        return $multiAssociations;
    }

    /**
     * Convert an Action from a single association to multiassociation
     */
    public function convertToMultiassociation() {
        $success = true;
        if ($this->associationId) {
            $joinModel = ActionToRecord::model ()->findByAttributes (array (
                'actionId' => $this->id,
                'recordId' => $this->associationId,
                'recordType' => $this->associationType,
            ));
            if (!$joinModel) {
                $joinModel = new ActionToRecord;
                $joinModel->setAttributes (array (
                    'actionId' => $this->id,
                    'recordId' => $this->associationId,
                    'recordType' => $this->associationType,
                ), false);
                $success &= $joinModel->save ();
            }
            $this->associationId = null;
        }
        $this->associationType = self::ASSOCIATION_TYPE_MULTI;
        $success &= $this->save();
        return $success;
    }

    public function getMetaDataFieldNames() {
        return array_keys($this->metaDataTemp);
    }

    /**
     * Retrieve a list of model links, indexed by model name
     * @return array
     */
    public function getMultiassociationLinks() {
        $multiAssociationLinks = array();
        foreach ($this->getMultiassociations() as $type => $models) {
            foreach ($models as $model) {
                if ($model) {
                    $link = X2Model::getModelLink($model->id, $type);
                    if ($link)
                        $multiAssociationLinks[$type][] = $link;
                }
            }
        }
        return $multiAssociationLinks;
    }

    /**
     * Returns action type specific attribute labels
     * @return String
     */
    public function getAttributeLabel ($attribute, $short=false) {
        $label = '';
        
        if ($attribute === 'dueDate') {
            switch ($this->type) {
                case 'time':
                case 'call':
                    if ($short) 
                        $label = Yii::t('actions', 'Start');
                    else
                        $label = Yii::t('actions', 'Time Started');
                    break;
                case 'event':
                    if ($short) 
                        $label = Yii::t('actions', 'Start');
                    else
                        $label = Yii::t('actions', 'Start Date');
                    break;
                default:
                    $label = parent::getAttributeLabel ($attribute);
            }
        } else if ($attribute === 'completeDate') {
            switch ($this->type) {
                case 'time':
                case 'call':
                    if ($short)
                        $label = Yii::t('actions', 'End');
                    else
                        $label = Yii::t('actions', 'Time Ended');
                    break;
                case 'event':
                    if ($short)
                        $label = Yii::t('actions', 'End');
                    else 
                        $label = Yii::t('actions', 'End Date');
                    break;
                default:
                    $label = parent::getAttributeLabel ($attribute);
            }
        } else if ($attribute === 'actionDescription') {
            $label = Yii::t('actions', 'Action Description');
        } else if ($attribute === 'eventSubtype') {
            $label = Yii::t('actions', 'Type');
        } else if ($attribute === 'eventStatus') {
            $label = Yii::t('actions', 'Status');
        } else {
            $label = parent::getAttributeLabel ($attribute);
        }

        return $label;
    }

    public function attributeNames () {
         return array_merge (
            parent::attributeNames (), 
            array_keys ($this->metaDataTemp),
            array (
                'actionDescription',
                'notificationTime',
                'notificationUsers',
            )
        );
    }

    public function getAttributes ($names=true) {
        $attrs = parent::getAttributes ($names);
        $filter = is_array ($names);
        if (!$filter || in_array ('actionDescription', $names))
            $attrs['actionDescription'] = $this->actionDescription;
//        if (!$filter || in_array ('notificationUsers', $names))
//            $attrs['notificationUsers'] = $this->notificationUsers;
//        if (!$filter || in_array ('notificationTime', $names))
//            $attrs['notificationTime'] = $this->notificationTime;
        foreach (array_keys ($this->metaDataTemp) as $name) {
            if (!$filter || in_array ($name, $names))
                $attrs[$name] = $this->$name;
        }
        return $attrs;
    }

    public function getAttribute($name, $renderFlag = false, $makeLinks = false){
        if (in_array ($name, array_keys ($this->metaDataTemp))) {
            return $this->$name;
        } elseif ($name === 'actionDescription') {
            $model = ActionText::model ()->findByAttributes (
                array (
                    'actionId' => $this->id
                ));
            if ($model) return $model->text;
        } else {
            return parent::getAttribute ($name, $renderFlag);
        }
        return null;
    }

    public function getAssociation () {
        return self::getAssociationModel($this->associationType, $this->associationId);
    }
    
    /**
     * Includes text to an action or creates one if it doesn't already exist
     * @param string $textToBeIncluded the text to be included in the action description
     * @return 
     */
    public function includeTextToAction($textToBeIncluded){
        // No action text exists for this yet
        if(!($this->actionText instanceof ActionText)){
            $actionText = new ActionText; // Create new one
            $actionText->actionId = $this->id;
            $actionText->text = $textToBeIncluded; // A magic setter sets actionDescriptionTemp value
            $actionText->save();
        }else{ // We have an action text
            if($this->actionText->text != $textToBeIncluded){ // Only update if different
                $this->actionText->text = $textToBeIncluded;
                $this->actionText->save();
            }
        }
        return;

    }

    /**
     * Creates a photo (.png) under uploads/protected/media/[USER] given the raw data 
     * and relates it to an action. The relationship is stored in 'x2_actions_to_media'
     * @param User $profile the profile of the user that did the action
     * @param string $attachmentData raw image data (format png)
     * @param Bool $runValidation used to enable/disable X2Flow record update trigger
     * @param Array $attributes attributes for enabling/disabling X2Flow record update trigger
     * @return Bool
     */
    public function saveRaw ($profile, $attachmentData, $runValidation=true, $attributes=null) {

            // save related photo record
            $transaction = Yii::app()->db->beginTransaction ();
            try {
                // save the event
                $ret = parent::save ($runValidation, $attributes);
                if (!$ret) {
                    throw new CException (implode (';', $this->getAllErrorMessages ()));
                }
                //save the raw data to a file
                $filename = md5(uniqid(rand(), true)) . '.png';
                $fileType = 'image/png';
                $userFolderPath = implode(DIRECTORY_SEPARATOR, array(
                    Yii::app()->basePath,
                    '..',
                    'uploads',
                    'protected',
                    'media',
                    $profile->username
                ));
                // add media record for file                
                $media = new Media;
                $media->setAttributes (array (
                    'fileName' => $filename,
                    'mimetype' => $fileType,
                ), false);
                $media->createDate = time();
                $media->lastUpdated = time();
                $media->uploadedBy = $profile->username;
                $media->associationType = 'User';
                $media->associationId = $profile->id;
                $media->resolveNameConflicts();
                $associatedMedia = Yii::app()->file->set($userFolderPath.DIRECTORY_SEPARATOR.$media->fileName);
                $associatedMedia->create();
                $associatedMedia->setContents($attachmentData);  
                
                if (!$media->save () && !$associatedMedia->exists) {
                    throw new CException (implode (';', $media->getAllErrorMessages ()));
                }
                
                // relate file to action
                $join = new RelationshipsJoin ('insert', 'x2_actions_to_media');
                $join->actionsId = $this->id;
                $join->mediaId = $media->id;
                if (!$join->save ()) {
                    throw new CException (implode (';', $join->getAllErrorMessages ()));
                }
                $transaction->commit ();
                return $ret;
            } catch (CException $e) {
                $transaction->rollback ();
                return false;
            }

    }

    /**
     * Fixes up record association, parses dates (since this doesn't use 
     * {@link X2Model::setX2Fields()})
     * @return boolean whether or not to save
     */
    public function beforeSave(){
        if($this->scenario !== 'workflow'){
            $association = self::getAssociationModel($this->associationType, $this->associationId);

            if($association === null){
                $this->associationName = 'None';
                $this->associationId = 0;
            }else{
                if($association->hasAttribute('name'))
                    $this->associationName = $association->name;
                if($association->asa('TimestampBehavior') !== null) {
                    if($association->asa('changelog') !== null
                            && Yii::app()->getSuName() == 'Guest')
                        $association->disableBehavior('changelog');
                    $association->updateLastActivity();
                    $association->enableBehavior('changelog');
                }
            }

            if($this->associationName == 'None' && $this->associationType != 'none')
                $this->associationName = ucfirst($this->associationType);

            $this->dueDate = Formatter::parseDateTime($this->dueDate);
            $this->completeDate = Formatter::parseDateTime($this->completeDate);
        }
        // Whether this is a "timed" action record:
        $timed = $this->isTimedType;
        
        $timeSpent = 0;
        if(!$this->isNewRecord && $timed) {
            $timeSpent = ActionTimer::actionTimeSpent($this->id);
            // If the above value is zero, the next conditional statement will
            // be entered into, thus setting the time spent appropriately based
            // on beginning and end times.
            $this->timeSpent = $timeSpent;
        }
        
        if(empty($timeSpent) && !empty($this->completeDate) && !empty($this->dueDate) && $timed) {
            $this->timeSpent = $this->completeDate - $this->dueDate;
        }

        
        if (Yii::app()->contEd('pla') && $this->associationType === 'AnonContact') {
            $maxAnonActions = Yii::app()->settings->maxAnonActions;
            $count = Yii::app()->db->createCommand()
                ->select('COUNT(*)')
                ->where(
                    'associationType="AnonContact" AND associationId=:id',
                    array(':id'=>$this->associationId))
                ->from('x2_actions')
                ->queryScalar();
            if ($count >= $maxAnonActions) {
                // Remove the last modified Actions associated with this AnonContact
                // if the limit has been reached.
                $lastModifiedId = Yii::app()->db->createCommand()
                    ->select('id')
                    ->from('x2_actions')
                    ->where(
                        'associationType="anoncontact" AND associationId=:id',
                        array(':id'=>$this->associationId))
                    ->order('lastUpdated ASC')
                    ->queryScalar();
                X2Model::model('Actions')->deleteByPk($lastModifiedId);
            }
        }

        // Adjust pluralization on required models
        if ($this->isNewRecord && !empty($this->associationType) &&
            in_array($this->associationType, array('Opportunity', 'Product', 'Quote'))) {
                $this->associationType = X2Model::getAssociationType($this->associationType);
        }

        return parent::beforeSave();
    }

    public function beforeDelete() {
        
        if($this->type === 'event' && !empty($this->calendarId) && !empty($this->remoteCalendarUrl)){
            $calendar = X2Calendar::model()->findByPk($this->calendarId);
            if($calendar){
                $calendar->deleteAction($this);
            }
        }
        
        ActionTimer::model()->deleteAllByAttributes(array('actionId'=>$this->id));
        
        return parent::beforeDelete();
    }

    private function saveMetaData () {
        $this->includeTextToAction ($this->actionDescriptionTemp);

        if (!$this->actionMetaData instanceof ActionMetaData) {
            $metaData = new ActionMetaData;
            $metaData->actionId = $this->id;
        } else {
            $metaData = $this->actionMetaData;
        }
        foreach ($this->metaDataTemp as $name => $value) {
            $metaData->$name = $value;
        }

        if (!$metaData->save ()) {
            //AuxLib::debugLogR ($metaData->getErrors ());
        }
    }


    /** 
    * Modified findAll function that doesn't attach actionText. See {@link X2Model::findAll}.
    * @param mixed $condition query condition or criteria
    * @param array $params parameters to be bound to an SQL statement.
    * @return CActiveRecord[] list of active records satisfying the specified condition. 
    * An empty array is returned if none is found.
    */
    public function findAllWithoutActionText($condition = '', $params = array()){
        self::$withActionText = false;
        $models = $this->findAll($condition, $params);
        self::$withActionText = true;
        return $models;
    }

    public function afterFind(){
        if(self::$withActionText && $this->actionText instanceof ActionText){
            $this->actionDescriptionTemp = $this->actionText->text;
        }
        if ($this->actionMetaData instanceof ActionMetaData) {
            foreach ($this->metaDataTemp as $name => $value) {
                $this->metaDataTemp[$name] = $this->actionMetaData->$name;
            }
        }
        parent::afterFind();
    }

    private $_timerIds;
    public function setTimerIds ($timers) {
        $this->_timerIds = $timers;
        $this->skipActionTimers = true;
    }

    public function afterSave(){
        $this->saveMetaData ();

        if ($this->reminder) {
            if (!$this->isNewRecord)
                $this->deleteOldNotifications ($this->notificationUsers);
            $notifTime = $this->dueDate - ($this->notificationTime * 60);
            if ($this->complete !== 'Yes' && $notifTime >= time()) {
                // Only recreate the reminder if it hasn't happened yet or the Action is incomplete
                $this->createNotifications($this->notificationUsers, $notifTime, 'action_reminder');
            }
        }

        
        
        $this->saveActionLineItems ();
        // Create a new action timer for this action with start/end time, if it
        // is necessary, and one doesn't already exist:
        $timers = $this->getRelated('timers');
        if($this->isTimedType && $this->timeSpent > 0 && empty($timers) && 
            !$this->skipActionTimers) {

            $timer = new ActionTimer;
            $timer->userId = Yii::app()->getSuID ();
            $timer->actionId = $this->id;
            $timer->associationType = X2Model::getModelName($this->associationType);
            $timer->associationId = $this->associationId;
            $timer->timestamp = $this->dueDate;
            $timer->endtime = $this->completeDate;
            $timer->save();
        }
        

        parent::afterSave();

        $association = X2Model::getAssociationModel($this->associationType, $this->associationId);
        if($this->isNewRecord && $association && $association->hasAttribute('lastActivity')){
            $association->lastActivity = time();
            $association->update(array('lastActivity'));
            X2Flow::trigger('RecordUpdateTrigger', array(
                'model' => $association,
            ));
        }

    }

    public function requiredAssoc($attribute, $params = array()){
        // all action types but events and empty require this attribute
        if(!$this->type) {
            return !$this->hasErrors();
        }

        if($this->associationType !== self::ASSOCIATION_TYPE_MULTI &&
           (gettype ($this->type) !== 'string' || !preg_match ('/^event/', $this->type))) {

            if(empty($this->$attribute) || strtolower($this->$attribute) == 'none')
                $this->addError(
                    $attribute, 
                    Yii::t('actions', 'Association is required for actions of type {type}', array (
                        '{type}' => $this->type,
                    )));
        }
        return !$this->hasErrors();
    }

    /**
     * Creates an event for each assignee 
     */
    public function createEvents ($eventType, $timestamp) {
        $assignees = $this->getAssignees ();
        foreach ($assignees as $assignee) {
            $event = new Events;
            $event->timestamp = $timestamp;
            $event->visibility = $this->visibility;
            $event->type = $eventType;
            $event->associationType = 'Actions';
            $event->associationId = $this->id;
            if ($eventType === 'record_create') {
                if (in_array ($this->type, array ('call', 'time', 'note')))
                    $event->subtype = $this->type;
                $event->user = Yii::app()->user->getName();
                $event->save();
                break; // only create one record, not one for each assignee
            } else {
                $event->user = $assignee;
            }
            $event->save();
        }
    }

    public function createCalendarFeedEvent () {
        $event = new Events;
        $event->type = 'calendar_event';
        $event->visibility = $this->visibility;
        $event->associationType = 'Actions';
        $event->associationId = $this->id;
        $event->timestamp = $this->dueDate;
        $event->save ();
    }

    /**
     * Creates a notification for each assignee 
     * @return array the notifications created by this method
     */
    public function createNotifications (
        $notificationUsers='assigned', $createDate=null, $type='create') {

        $notifications = array ();

        if (!$createDate) $createDate = time ();

        $assignees = array ();
        switch ($notificationUsers) {
            case 'me':
                $assignees = array (Yii::app()->user->getName ());
                break;
            case 'assigned':
                $assignees = $this->getAssignees (true);
                break;
            case 'both':
                $assignees = array_unique (array_merge (
                    $this->getAssignees (true),
                    array (Yii::app()->user->getName ())));
                break;
        }
        foreach ($assignees as $assignee) {
            $notif = new Notification;
            $notif->user = $assignee;
            $notif->createdBy = (Yii::app()->params->noSession) ? 
                'API' : Yii::app()->user->getName();
            $notif->createDate = $createDate;
            $notif->type = $type;
            $notif->modelType = 'Actions';
            $notif->modelId = $this->id;
            if ($notif->save()) {
                $notifications[] = $notif;
            } else {
                //AuxLib::debugLogR ($notif->getErrors ());
            }
        }
        return $notifications;
    }

    /**
     * Creates an action reminder event.
     * Fires the onAfterCreate event in {@link X2Model::afterCreate}
     */
    public function afterCreate(){
        if($this->type === 'event')
            $this->createCalendarFeedEvent ();
        if(($this->type === 'event' || empty($this->type)) && !empty($this->calendarId) && empty($this->remoteCalendarUrl)){
            $calendar = X2Calendar::model()->findByPk($this->calendarId);
            if($calendar && $calendar->asa('syncBehavior')){
                $calendar->syncActionToCalendar($this);
            }
        }
        if(empty ($this->type) || in_array($this->type, array('call','time','note'))){
            $this->createEvents ('record_create', $this->createDate);
        }
        if(empty($this->type) && $this->complete !== 'Yes' && 
           ($this->reminder == 1 || $this->reminder == 'Yes')){

            $this->createEvents ('action_reminder', $this->dueDate);
        }
        if($this->scenario != 'noNotif' && 
           (Yii::app()->params->noSession || 
            !$this->isAssignedTo (Yii::app()->user->getName(), true))){

            $this->createNotifications ();
        }

         
        // Adjust the time according to timers given and associate all
        // timer records with this action
        if(!empty($this->_timerIds)){
            $timerIds = explode(',', $this->_timerIds);
            $params = array();
            foreach($timerIds as $id){
                $params[":timer$id"] = $id;
            }
            $wherein = '('.implode(',', array_keys($params)).')';
            Yii::app()->db->createCommand()
                ->update(
                    ActionTimer::model()->tableName(), 
                    array('actionId' => $this->id),
                    "`id` IN ".$wherein, $params);
            $timeSpent = ActionTimer::actionTimeSpent($this->id);
            if($timeSpent > 0){
                $this->timeSpent = $timeSpent;
                $this->update(array('timeSpent'));
            }
        }

        if ($this->associationType !== Actions::ASSOCIATION_TYPE_MULTI) {
            $associationModelName =  X2Model::getModelName($this->associationType);
            if (class_exists ($associationModelName, false))
                self::updateTimerTotals(
                    $this->associationId, X2Model::getModelName($this->associationType));
        }
         

        parent::afterCreate();
    }
    
    public function afterUpdate() {
        if (($this->type === 'event' || empty($this->type)) && !empty($this->calendarId) && !empty($this->remoteCalendarUrl)) {
            $calendar = X2Calendar::model()->findByPk($this->calendarId);
            if ($calendar && $calendar->asa('syncBehavior')) {
                $calendar->syncActionToCalendar($this);
            }
        }
        parent::afterUpdate();
    }

    /**
     * Deletes the action reminder event, if any
     * Fires the onAfterDelete event in {@link X2Model::afterDelete}
     */
    public function afterDelete(){
        X2Model::model('Events')->deleteAllByAttributes(array('associationType' => 'Actions', 'associationId' => $this->id, 'type' => 'action_reminder'));
         
        if ($this->quoteId && $this->type === 'products') 
            Quote::model()->deleteByPk ($this->quoteId);
         
        parent::afterDelete();
    }

    /**
     * Sets action subtype for actions of type event 
     */
    public function setEventSubtype ($value) {
        $this->metaDataTemp['eventSubtype'] = $value;
    }

    public function setEventStatus ($value) {
        $this->metaDataTemp['eventStatus'] = $value;
    }

     
    public function setEmailImapUid ($value) {
        $this->metaDataTemp['emailImapUid'] = $value;
    }

    public function setEmailInboxId ($value) {
        $this->metaDataTemp['emailInboxId'] = $value;
    }

    public function setEmailFolderName ($value) {
        $this->metaDataTemp['emailFolderName'] = $value;
    }

    public function setEmailUidValidity ($value) {
        $this->metaDataTemp['emailUidValidity'] = $value;
    }
    
    public function setEtag ($value) {
        $this->metaDataTemp['etag'] = $value;
    }
    
    public function setRemoteCalendarUrl ($value) {
        $this->metaDataTemp['remoteCalendarUrl'] = $value;
    }
    
    public function setRemoteSource ($value) {
        $this->metaDataTemp['remoteSource'] = $value;
    }

    public function setActionDescription($value){
        // Magic setter stores value in actionDescriptionTemp until saved
        $this->actionDescriptionTemp = Fields::getPurifier()->purify($value);
    }

    public function getEventSubtype () {
        return $this->metaDataTemp['eventSubtype'];
    }

    public function getEventStatus () {
        return $this->metaDataTemp['eventStatus'];
    }

     
    public function getEmailImapUid () {
        return $this->metaDataTemp['emailImapUid'];
    }

    public function getEmailInboxId () {
        return $this->metaDataTemp['emailInboxId'];
    }

    public function getEmailFolderName () {
        return $this->metaDataTemp['emailFolderName'];
    }

    public function getEmailUidValidity() {
        return $this->metaDataTemp['emailUidValidity'];
    }
    
    public function getEtag () {
        return $this->metaDataTemp['etag'];
    }
    public function getRemoteCalendarUrl () {
        return $this->metaDataTemp['remoteCalendarUrl'];
    }
    public function getRemoteSource () {
        return $this->metaDataTemp['remoteSource'];
    }
     

    public function getActionDescription(){
        // Magic getter only ever refers to actionDescriptionTemp
        return $this->actionDescriptionTemp;
    }

    /**
     * Sends email reminders to all assignees
     */
    /*public function sendEmailRemindersToAssignees () {
        $emails = User::getEmails();

        $assignees = $this->getAssignees (true);

        foreach ($assignees as $assignee) {

            if($this->associationId != 0){
                $contact = X2Model::model('Contacts')->findByPk($this->associationId);
                $name = $contact->firstName.' '.$contact->lastName;
            } else
                $name = Yii::t('actions', 'No one');
            if(isset($emails[$assignee])){
                $email = $emails[$assignee];
            }else{
                continue;
            }
            if(isset($this->type))
                $type = $this->type;
            else
                $type = Yii::t('actions', 'Not specified');
    
            $subject = Yii::t('actions', 'Action Reminder:');
            $body = Yii::t('actions', "Reminder, the following action is due today: \n Description: {description}\n Type: {type}.\n Associations: {name}.\nLink to the action: ", array('{description}' => $this->actionDescription, '{type}' => $type, '{name}' => $name))
                    .Yii::app()->controller->createAbsoluteUrl('/actions/actions/view',array('id'=>$this->id));
            $headers = 'From: '.Yii::app()->params['adminEmail'];
    
            if($this->associationType != 'none')
                $body.="\n\n".Yii::t('actions', 'Link to the {type}', array('{type}' => ucfirst($this->associationType))).': '.Yii::app()->controller->createAbsoluteUrl(str_repeat('/'.$this->associationType,2).'/view',array('id'=>$this->associationId));
            $body.="\n\n".Yii::t('actions', 'Powered by ').'<a href=http://x2engine.com>X2Engine</a>';
    
            mail($email, $subject, $body, $headers);
        }
    }*/

    /**
     * Marks the action complete and updates the record.
     * @param string $completedBy the user completing the action (defaults to currently logged in user)
     * @return boolean whether or not the action updated successfully
     */
    public function complete($completedBy = null, $notes = null){
        if($completedBy === null){
            $completedBy = Yii::app()->user->getName();
        }
        if(!is_null($notes)){
            $this->actionDescription.="\n\n".$notes;
        }

        $this->complete = 'Yes';
        $this->completedBy = $completedBy;
        $this->completeDate = time();

        $this->disableBehavior('changelog');

        if($result = $this->save()){

            X2Flow::trigger('ActionCompleteTrigger', array(
                'model' => $this,
                'user' => $completedBy
            ));

            // delete the action reminder event
            X2Model::model('Events')->deleteAllByAttributes(
                array(
                    'associationType' => 'Actions',
                    'associationId' => $this->id,
                    'type' => 'action_reminder'
                ), 'timestamp > NOW()');

            $event = new Events;
            $event->type = 'action_complete';
            $event->visibility = $this->visibility;
            $event->associationType = 'Actions';
            $event->user = Yii::app()->user->getName();
            $event->associationId = $this->id;

            // notify the admin
            if($event->save() && !Yii::app()->user->checkAccess('ActionsAdminAccess')){
                $notif = new Notification;
                $notif->type = 'action_complete';
                $notif->modelType = 'Actions';
                $notif->modelId = $this->id;
                $notif->user = 'admin';
                $notif->createdBy = $completedBy;
                $notif->createDate = time();
                $notif->save();
            }
        } else {
            $this->validate ();
            //AuxLib::debugLogR ($this->getErrors ());
        }
        $this->enableBehavior('changelog');

        return $result;
    }

    /**
     * Marks the action incomplete and updates the record.
     * @return boolean whether or not the action updated successfully
     */
    public function uncomplete(){
        $this->complete = 'No';
        $this->completedBy = null;
        $this->completeDate = null;

        $this->disableBehavior('changelog');

        if($result = $this->save()){
            X2Flow::trigger('ActionUncompleteTrigger', array(
                'model' => $this,
                'user' => Yii::app()->user->getName()
            ));
        }
        $this->enableBehavior('changelog');

        return $result;
    }

    public function getName(){
        if(!empty($this->subject)){
            return $this->subject;
        }else{
            if($this->type == 'email'){
                return Formatter::parseEmail($this->actionDescription);
            }else{
                return Formatter::truncateText($this->actionDescription, 40);
            }
        }
    }

    public function getLink($length = 30, $frame = true){
        $text = $this->name;
        if($length && mb_strlen($text, 'UTF-8') > $length)
            $text = CHtml::encode(trim(mb_substr($text, 0, $length, 'UTF-8')).'...');
        if($frame){
            return CHtml::link($text, '#', array('class' => 'action-frame-link', 'data-action-id' => $this->id));
        }else{
            return CHtml::link($text, $this->getUrl());
        }
    }

    public function frameLink () {
        return CHtml::link(
            $this->actionDescription, '#', 
            array('class' => 'action-frame-link', 'data-action-id' => $this->id));
    }

    /**
     * Queries the database for the first characters of an action description
     * @param int $length length of string to retrieve
     * @param string $overflow string to append to text if it overflows
     * @return string
     */
    public function getShortActionText($length = 30, $overflow='...'){
        $actionText = Yii::app()->db->createCommand()->
            select('SUBSTR(text, 1,'.$length.') AS text, CHAR_LENGTH(text) AS length')->
            from('x2_action_text')->
            where('actionId='.$this->id)->queryRow();
        
        if($actionText['length'] > $length)
            $actionText['text'] .= $overflow;

        return $actionText['text'];

    }

    public function getAssociationLink(){
        $model = self::getAssociationModel($this->associationType, $this->associationId);
        if($model !== null)
            return $model->getLink();
        return false;
    }

    public function getRelevantTimestamp() {
        switch($this->type) {
            case 'attachment':
                $timestamp = $this->completeDate;
                break;
            case 'email': 
            case 'emailFrom': 
            case 'email_quote': 
            case 'email_invoice': 
                $timestamp = $this->completeDate; 
                break;
            case 'emailOpened': 
            case 'emailOpened_quote': 
            case 'email_opened_invoice': 
                $timestamp = $this->completeDate; 
                break;
            case 'event': 
                $timestamp = $this->completeDate; 
                break;
            case 'note': 
                $timestamp = $this->createDate; 
                break;
            case 'quotesDeleted': 
            case 'quotes': 
                $timestamp = $this->createDate; 
                break;
            case 'time': 
                $timestamp = $this->createDate; 
                break;
            case 'webactivity': 
                $timestamp = $this->completeDate; 
                break;
            case 'workflow': 
                $timestamp = $this->completeDate; 
                break;
            default:
                $timestamp = $this->createDate;
        }
        return $timestamp;
    }

    public static function parseStatus($dueDate, $dateWidth='long', $timeWidth='short'){
        if(empty($dueDate)) // there is no due date
            return false;
        if(!is_numeric($dueDate))
            $dueDate = strtotime($dueDate); // make sure $date is a proper timestamp

        $timeLeft = $dueDate - time(); // calculate how long till due date
        if($timeLeft < 0) {
            return 
                "<span class='overdue'>".
                    Formatter::formatDueDate($dueDate, $dateWidth, $timeWidth).
                "</span>"; // overdue by X hours/etc
        } else {
            return Formatter::formatDueDate($dueDate, $dateWidth, $timeWidth);
        }
    }

    public function formatDueDate () {
        if (in_array ($this->type, array ('call', 'time', 'event'))) {
            return Formatter::formatDueDate($this->dueDate);
        } else {
            return self::parseStatus ($this->dueDate);
        }
    }

    public static function formatTimeLength($seconds){
        $seconds = abs($seconds);
        if($seconds < 60)
            return Yii::t('app', '{n} second|{n} seconds', $seconds); // less than 1 min
        if($seconds < 3600)
            return Yii::t('app', '{n} minute|{n} minutes', floor($seconds / 60)); // minutes (less than an hour)
        if($seconds < 86400)
            return Yii::t('app', '{n} hour|{n} hours', floor($seconds / 3600)); // hours (less than a day)
        if($seconds < 5184000)
            return Yii::t('app', '{n} day|{n} days', floor($seconds / 86400)); // days (less than 60 days)
        else
            return Yii::t('app', '{n} month|{n} months', floor($seconds / 2592000)); // months (more than 90 days)
    }

    public static function createCondition($filters){
        Yii::app()->params->profile->actionFilters = json_encode($filters);
        Yii::app()->params->profile->update(array('actionFilters'));
        $criteria = X2Model::model('Actions')->getAccessCriteria();
        $criteria->addCondition(
            "(type !='workflow' AND type!='email' AND type!='event' AND type!='emailFrom' AND 
              type!='attachment' AND type!='webactivity' AND type not like 'quotes%' AND 
              type!='emailOpened' AND type!='note' AND type!='call') OR type IS NULL");
        if(isset($filters['complete'], $filters['assignedTo'], $filters['dateType'], 
            $filters['dateRange'], $filters['order'], $filters['orderType'])){

            switch($filters['complete']){
                case "No":
                    $criteria->addCondition("complete='No' OR complete IS NULL");
                    break;
                case "Yes":
                    $criteria->addCondition("complete='Yes'");
                    break;
                case 'all':
                    break;
            }
            switch($filters['assignedTo']){
                case 'me':
                    list ($cond, $params) = self::model()->getAssignedToCondition (false);
                    $criteria->addCondition($cond);
                    $criteria->params = array_merge ($criteria->params, $params);
                    break;
                case 'both':
                    list ($cond, $params) = self::model()->getAssignedToCondition (true);
                    $criteria->addCondition($cond);
                    $criteria->params = array_merge ($criteria->params, $params);
                    break;
            }
            switch($filters['dateType']){
                case 'due':
                    $dateField = 'dueDate';
                    break;
                case 'create':
                    $dateField = 'createDate';
            }
            switch($filters['dateRange']){
                case 'today':
                    if($dateField == 'dueDate'){
                        $criteria->addCondition("IFNULL(dueDate, createDate) <= ".strtotime('today 11:59 PM'));
                    }else{
                        $criteria->addCondition("$dateField >= ".strtotime('today')." AND $dateField <= ".strtotime('today 11:59 PM'));
                    }
                    break;
                case 'tomorrow':
                    if($dateField == 'dueDate'){
                        $criteria->addCondition("IFNULL(dueDate, createDate) <= ".strtotime("tomorrow 11:59 PM"));
                    }else{
                        $criteria->addCondition("$dateField >= ".strtotime('tomorrow')." AND $dateField <= ".strtotime("tomorrow 11:59 PM"));
                    }
                    break;
                case 'week':
                    if($dateField == 'dueDate'){
                        $criteria->addCondition("IFNULL(dueDate, createDate) <= ".strtotime("Sunday 11:59 PM"));
                    }else{
                        $criteria->addCondition("$dateField >= ".strtotime('Monday')." AND $dateField <= ".strtotime("Sunday 11:59 PM"));
                    }
                    break;
                case 'month':
                    if($dateField == 'dueDate'){
                        $criteria->addCondition("IFNULL(dueDate, createDate) <= ".strtotime("last day of this month 11:59 PM"));
                    }else{
                        $criteria->addCondition("$dateField >= ".strtotime('first day of this month')." AND $dateField <= ".strtotime("last day of this month 11:59 PM"));
                    }
                    break;
                case 'range':
                    if(!empty($filters['start']) && !empty($filters['end'])){
                        if($dateField == 'dueDate'){
                            $criteria->addCondition("IFNULL(dueDate, createDate) >= ".strtotime($filters['start'])." AND IFNULL(dueDate, createDate) <= ".strtotime($filters['end'].' 11:59 PM'));
                        }else{
                            $criteria->addCondition("$dateField >= ".strtotime($filters['start'])." AND $dateField <= ".strtotime($filters['end']));
                        }
                    }
                    break;
            }
            switch($filters['order']){
                case 'due':
                    $orderField = "IFNULL(dueDate, createDate)";
                    break;
                case 'create':
                    $orderField = 'createDate';
                    break;
                case 'priority':
                    $orderField = 'priority';
                    break;
            }
            switch($filters['orderType']){
                case 'desc':
                    $criteria->order = "$orderField DESC";
                    break;
                case 'asc':
                    $criteria->order = "$orderField ASC";
                    break;
            }
        }
        return $criteria;
    }

    public function search($criteria = null, $pageSize=null){
        if(!$criteria instanceof CDbCriteria){
            $criteria = $this->getAccessCriteria();
            $criteria->addCondition(
                '(type = "" OR type IS NULL)');
            $criteria->addCondition(
                "assignedTo REGEXP BINARY :userNameRegex AND complete!='Yes' AND ".
                "IFNULL(dueDate, createDate) <= '".strtotime('today 11:59 PM')."'");
            $criteria->params = array_merge($criteria->params,array (
                ':userNameRegex' => $this->getUserNameRegex ()
            ));
        }

        return $this->searchBase($criteria, $pageSize);
    }

    /**
     * Today's Actions 
     */
    public function searchIndex($pageSize=null, $uniqueId=null){
        $criteria = new CDbCriteria;
        $groupIds = User::getMe()->getGroupIds ();
        list ($assignedToCondition, $params) = $this->getAssignedToCondition (); 
        if (Yii::app()->params->profile->showActions === 'overdue') {
            $dueDate = time ();
        } else {
            $dueDate = mktime (24, 0, 0);
        }
        $parameters = array(
            'condition' => 
                $assignedToCondition.
                 " AND t.dueDate < '".$dueDate."' AND 
                    (t.type=\"\" OR t.type IS NULL)", 
                'limit' => ceil(Profile::getResultsPerPage() / 2), 
            'params' => $params);
        $criteria->scopes = array('findAll' => array($parameters));
        return $this->searchBase($criteria, $pageSize);
    }

    /**
     * All My Actions
     */
    public function searchAll(){
        $criteria = new CDbCriteria;
        list ($assignedToCondition, $params) = $this->getAssignedToCondition (); 
        $condition = $assignedToCondition;
        if (Yii::app()->params->profile->showActions === 'overdue') {
            $condition = $assignedToCondition.' AND t.dueDate < '.time ();
        }
        $parameters = array(
            "condition" => $condition.' AND (t.type=\'\' OR t.type IS NULL)',
            'limit' => ceil(Profile::getResultsPerPage() / 2),
            'params' => $params);
        $criteria->scopes = array('findAll' => array($parameters));
        return $this->searchBase($criteria);
    }

    /**
     * Everyone's Actions 
     */
    public function searchAllGroup(){
        $criteria = new CDbCriteria;
        if(!Yii::app()->user->checkAccess('ActionsAdmin')){
            list ($assignedToCondition, $params) = $this->getAssignedToCondition (); 
            $criteria->addCondition(
                "(t.visibility='1' OR ".$assignedToCondition.")");
            $criteria->params = array_merge($criteria->params,$params);
        }
        if (Yii::app()->params->profile->showActions === 'overdue') {
            $criteria->addCondition('t.dueDate < '.time ());
        }
        $criteria->addCondition('(t.type=\'\' OR t.type IS NULL)');
        return $this->searchBase($criteria);
    }

    public function searchBase(
        $criteria, $pageSize=null, $showHidden=false){

        if ($pageSize === null) {
            $pageSize = Profile::getResultsPerPage ();
        }

        $this->compareAttributes($criteria);
        /*$criteria->with = 'actionText';
        $criteria->compare('actionText.text', $this->actionDescriptionTemp, true);*/
        if(!empty($criteria->order)){
            $criteria->order = $order = "t.sticky DESC, ".$criteria->order;
        }else{
            $order = 't.sticky DESC, IF(
                t.complete="No", IFNULL(t.dueDate, IFNULL(t.createDate,0)), 
                GREATEST(t.createDate, IFNULL(t.completeDate,0), IFNULL(t.lastUpdated,0))) DESC';
        }

        if ((Yii::app()->controller instanceof ActionsController) &&
            Yii::app()->controller->action->id !== 'index') {

            $dataProvider = new SmartActiveDataProvider('Actions', array(
                'sort' => array(
                    'defaultOrder' => $order,
                ),
                'pagination' => array(
                    'pageSize' => $pageSize
                ),
                'criteria' => $criteria,
                'uid' => $this->uid,
                'dbPersistentGridSettings' => $this->dbPersistentGridSettings
            ));
        } else {
            // for actions index, use CActiveDataProvider since SmartActiveDataProvider is 
            // incompatible with IasPager
            $dataProvider = new CActiveDataProvider('Actions', array(
                'sort' => array(
                    'defaultOrder' => $order,
                ),
                'pagination' => array(
                    'pageSize' => $pageSize
                ),
                'criteria' => $criteria,
            ));
        }

        return $dataProvider;
    }

    /**
     * Override parent method to exclude actionDescription
     */
    public function compareAttributes(&$criteria){
        if ($this->asa ('TagBehavior') && $this->asa ('TagBehavior')->getEnabled () && 
            $this->tags) {

            $tagCriteria = new CDbCriteria;
            $this->compareTags ($tagCriteria);
            $criteria->mergeWith ($tagCriteria);
        }

        $dbAttributes = array_flip (array_keys ($this->getMetaData ()->columns));
        foreach(self::$_fields[$this->tableName()] as &$field){
            if(isset ($dbAttributes[$field->fieldName])) {
                $this->compareAttribute ($criteria, $field);
            }
        }
    }

    /**
     * Returns a link which opens an action view dialog. Event bound in actionFrames.js. 
     * @param string $linkText The text to display in the <a> tag.
     */
    public function getActionLink ($linkText) {
        return CHtml::link(
            $linkText,
            '#',
            array(
                'class' => 'action-frame-link',
                'data-action-id' => $this->id
            )
        );
    }

    /**
     * Completes/uncompletes set of actions 
     * @param string $operation <'complete' | 'uncomplete'>
     * @param array $ids
     * @return int $updated number of actions updated successfully
     */
    public static function changeCompleteState ($operation, $ids) {
        $updated = 0;
        foreach(self::model()->findAllByPk ($ids) as $action){
            if($action === null)
                continue;

            if($action->isAssignedTo (Yii::app()->user->getName ()) ||
               Yii::app()->params->isAdmin){ // make sure current user can edit this action

                if($operation === 'complete') {
                    if ($action->complete()) $updated++;
                } elseif($operation === 'uncomplete') {
                    if ($action->uncomplete()) $updated++;
                }
            }
        }
        return $updated;
    }

    /**
     * Returns whether this is the type of action that can be time-tracked
     */
    public function getIsTimedType() {
        return $this->type == 'time' || $this->type == 'call';
    }

     // used for products actions
    /**
     * Returns dummy quote model which can be used to retrieve the products action line items.
     * @return object
     */
    private $_actionsDummyQuote;
    public function getActionsDummyQuote () {
        if (!isset ($this->_actionsDummyQuote)) {
            $quote = Quote::model ()->findByPk ($this->quoteId);
            if (!$quote) {
                $quote = new Quote;
                $quote->name = 'dummyQuote';
                $quote->type = 'dummyQuote';
            }
            $this->_actionsDummyQuote = $quote;
        }
        return $this->_actionsDummyQuote;
    }

    /**
     * Associate this action with line items.   
     * For products type actions. Allows line items to be associated with an action. In order to
     * reuse the code used in the Quotes module for handling line items, a dummy quote model is
     * used whose sole purpose is to manage the line items associated with the action.
     * @param array $lineItems
     */
    public function setActionLineItems ($lineItems) {
        $dummyQuote = $this->getActionsDummyQuote ();
        $dummyQuote->setLineItems ($lineItems);
    }

    /**
     * Save line items attached to dummy quote and associate quote with action
     */
    public function saveActionLineItems () {
        if ($this->type === 'products') {
            $dummyQuote = $this->getActionsDummyQuote ();
            if (!$dummyQuote->hasLineItemErrors && 
                $dummyQuote->save ()) {

                $dummyQuote->disableBehavior ('changelog');
                $dummyQuote->saveLineItems ();
                // use updateByPk to prevent infinite looping (this method is called from
                // afterSave, which is itself called by update and save)
                $this->updateByPk ($this->id, array (
                    'quoteId' => $dummyQuote->id,
                ));
            }
        }
    }
      

    /**
     * @return array all profiles of assignees. For assignees which are groups, all profiles of
     *  users in those groups are returned. If an assignee is included more than once,
     *  duplicate profiles are removed.
     */
    public function getProfilesOfAssignees () {
        $assignees = $this->getAssignees (true);  
        $profiles = array ();

        // prevent duplicate entries in $profiles by keeping track of included usernames
        $usernames = array (); 

        foreach ($assignees as $assignee) {
            $profile = X2Model::model('Profile')->findByAttributes(array (
                'username' => $assignee
            ));
            if ($profile) {
                $profiles[] = $profile;
            }
        }
        return $profiles;
    }
    
    /**
     * Override parent method so that action type can be set from X2Flow create action 
     */
    public function getEditableFieldNames ($suppressAttributeLabels=true) {
        $editableFieldNames = parent::getEditableFieldNames ($suppressAttributeLabels);
        if ($this->scenario === 'X2FlowCreateAction') {
            if ($suppressAttributeLabels) {
                $editableFieldNames[] = 'type';
            } else {
                $editableFieldNames['type'] = $this->getAttributeLabel ('type');
            }
        }
        return $editableFieldNames;
    }

    public static function getPriorityLabels(){
        if(!isset(self::$_priorityLabels)){
            self::$_priorityLabels = array(
                1 => Yii::t('actions', 'Low'),
                2 => Yii::t('actions', 'Medium'),
                3 => Yii::t('actions', 'High')
            );
        }
        return self::$_priorityLabels;
    }

    /**
     * Retrieve the priority label string, or return the default priority ("Low")
     * @return string Priority label
     */
    public function getPriorityLabel() {
        $priorityLabels = self::getPriorityLabels();
        $label = $priorityLabels[1];
        if (!empty($this->priority) && array_key_exists($this->priority, $priorityLabels))
            $label = $priorityLabels[$this->priority];
        return $label;
    }

    /**
     * Special override that prints priority accordingly
     * @param type $fieldName
     * @param type $makeLinks
     * @param type $textOnly
     * @param type $encode
     * @return type
     */
    public function renderAttribute(
        $fieldName, $makeLinks = true, $textOnly = true, $encode = true){

        $render = function($x)use($encode) {
            return $encode ? CHtml::encode($x) : $x;
        };

        switch ($fieldName) {
            case 'stageNumber':
                $workflowStage = $this->workflowStage;
                if ($workflowStage)
                    return $render ($workflowStage->name);
                else
                    return null;
            case 'priority':
                return $render ($this->getPriorityLabel ());
            default:
                return parent::renderAttribute($fieldName, $makeLinks, $textOnly, $encode);
        }
    }

    public function renderInlineViewLink ($text=null) {
        switch ($this->type) {
            case 'quotes':
                $quotePrint = (bool)  preg_match('/^\d+$/',$this->actionDescription);
                $objectId = $quotePrint ? $this->actionDescription : $this->id;
                if (!$text) {
                    $text = Yii::t('app', '[View quote]');
                }
                echo CHtml::link(
                    $text,
                    'javascript:void(0);',
                    array(
                        'onclick' => 'return false;',
                        'id' => $objectId,
                        'class' => $quotePrint ? 'quote-print-frame' : 'quote-frame'
                    )
                );
                break;
            case 'email':
            case 'emailFrom':
            case 'email_quote':
            case 'email_invoice':
            case 'emailOpened':
            case 'emailOpened_quote':
            case 'emailOpened_invoice':
                if (!$text) $text = Yii::t('app', '[View email]');
                echo CHtml::link (
                    $text,
                    '#', 
                    array(
                        'onclick' => 'return false;',
                        'id' => $this->id,
                        'class' => 'email-frame'
                    ));
                break;
        }
    }

    /**
     * @param type $fieldName
     * @param type $htmlOptions
     */
    public function renderInput($fieldName, $htmlOptions = array()){
        switch ($fieldName) {
            case 'color':
                $field = $this->getField ($fieldName);
                $options = Dropdowns::getItems($field->linkType, null, false); 
                $enableDropdownLegend = Yii::app()->settings->enableColorDropdownLegend;
                if ($enableDropdownLegend) {
                    $htmlOptions['options'] = array ();
                    foreach ($options as $value => $label) {
                        $brightness = X2Color::getColorBrightness ($value);
                        $fontColor = $brightness > 127.5 ? 'black' : 'white';
                        $htmlOptions['options'][$value] = array (
                            'style' => 
                                'background-color: '.$value.';
                                 color: '.$fontColor,
                        );
                    }
                }
                return CHtml::activeDropDownList($this, $field->fieldName, $options, $htmlOptions);
            case 'priority':
                return CHtml::activeDropdownList($this,'priority',self::getPriorityLabels());
            case 'associationType':
                return X2Html::activeMultiTypeAutocomplete (
                    $this, 'associationType', 'associationId', 
                    array ('calendar' => Yii::t('app', 'Select an option')) +
                        X2Model::getAssociationTypeOptions ());
            case 'reminder':
                $reminderInput = parent::renderInput (
                    $fieldName, array (
                        'class' => 'reminder-checkbox',
                    ));
                $reminderInput .= $this->renderReminderConfig($htmlOptions);
                return $reminderInput;
            default:
                return parent::renderInput($fieldName, $htmlOptions);
        }
    }

    public function renderReminderConfig($htmlOptions = array(), $model = null) {
        if (is_null($model)) $model = $this;
        $reminderConfig =
            X2Html::openTag ('div', X2Html::mergeHtmlOptions ($htmlOptions, array (
                'class' => 'reminder-config',
            ))).
            Yii::t(
                'actions',
                'Create a notification reminder for {user} {time} before this {action} '.
                    'is due',
                array(
                    '{user}' => CHtml::activeDropDownList(
                        $model,
                        'notificationUsers',
                        array(
                            'me' => Yii::t('actions', 'me'),
                            'assigned' => Yii::t('actions', 'the assigned user'),
                            'both' => Yii::t('actions', 'me and the assigned user'),
                        )
                    ),
                    '{time}' => CHtml::activeDropDownList(
                        $model, 'notificationTime',
                        array(
                            1 => Yii::t('actions','1 minute'),
                            5 => Yii::t('actions','5 minutes'),
                            10 => Yii::t('actions','10 minutes'),
                            15 => Yii::t('actions','15 minutes'),
                            30 => Yii::t('actions','30 minutes'),
                            60 => Yii::t('actions','1 hour'),
                            1440 => Yii::t('actions','1 day'),
                            10080 => Yii::t('actions','1 week')
                        )),
                    '{action}' => lcfirst(Modules::displayName(false, 'Actions')),
                )).'</div>';
        return $reminderConfig;
    }

    public function isMultiassociated() {
        return $this->associationType === self::ASSOCIATION_TYPE_MULTI;
    }

    public function renderMultiassociations($makeLinks = true) {
        if ($makeLinks) {
            $associations = $this->getMultiassociationLinks ();
            // Flatten multi-dimensional array to comma separated list of links
            $associatedModels = array_map (
                function ($elem) {return implode(', ', $elem); },
                $associations
            );
        } else {
            $associations = $this->getMultiassociations();
            $associatedModels = array();
            foreach ($associations as $type => $models) {
                $associatedModels = array_merge(
                    $associatedModels,
                    array_map (function($a) {return CHtml::encode($a->name);}, $models)
                );
            }
        }
        return implode(', ', array_values ($associatedModels));
    }

    private $_reminders;
    public function getReminders ($refresh = false) {
        if (!isset ($this->_reminders) || $refresh) {
            $this->_reminders = X2Model::model('Notification')->findAllByAttributes(array(
                'modelType' => 'Actions',
                'modelId' => $this->id,
                'type' => 'action_reminder'
            ));
        }
        return $this->_reminders;
    }

    private $_notificationUsers;
    public function setNotificationUsers ($notificationUsers) {
        $this->_notificationUsers = $notificationUsers;
    }

    public function getNotificationUsers () {
        if (!isset ($this->_notificationUsers)) {
            $reminders = $this->getReminders ();
            if(count($reminders) > 1){
                $notificationUsers = 'both';
            }else{
                $notificationUsers = 'assigned';
            }
            $this->_notificationUsers = $notificationUsers;
        }
        return $this->_notificationUsers;
    }

    private $_notificationTime;
    public function setNotificationTime ($notificationTime) {
        $this->_notificationTime = $notificationTime;
    }

    public function getNotificationTime () {
        if (!isset ($this->_notificationTime)) {
            $reminders = $this->getReminders ();
            if(count($reminders) > 0){
                $notifTime = (strtotime($this->dueDate) - $reminders[0]->createDate) / 60;
            }else{
                $notifTime = 15;
            }
            $this->_notificationTime = $notifTime;
        }
        return $this->_notificationTime;
    }

    /**
     * Overrides parent method to add models which can be linked through the association[id|type]
     * fields.
     * @return array static linked models indexed by link field name
     */
    public function getStaticLinkedModels () {
        return array_merge (parent::getStaticLinkedModels (), self::getModuleModelsByName ());
    }

    /**
     * Deletes duplicate notifications. Meant to be called before the creation of new notifications
     * @param string $notificationUsers assignee of the newly created notifications
     * TODO: unit test
     */
    private function deleteOldNotifications ($notificationUsers) {
        $notifCount = (int) X2Model::model('Notification')->countByAttributes(array(
            'modelType' => 'Actions',
            'modelId' => $this->id,
            'type' => 'action_reminder'
        ));
        if ($notifCount === 0) return;

        $notifications = X2Model::model('Notification')->findAllByAttributes(array(
            'modelType' => 'Actions',
            'modelId' => $this->id,
            'type' => 'action_reminder'
        ));

        foreach($notifications as $notification){
            if ($this->isAssignedTo ($notification->user, true) && 
               ($notificationUsers == 'assigned' || 
                $notificationUsers == 'both')){

                $notification->delete();
            }elseif($notification->user == Yii::app()->user->getName() && 
                ($notificationUsers == 'me' || 
                 $notificationUsers == 'both')){

                $notification->delete();
            }
        }

    }

}
