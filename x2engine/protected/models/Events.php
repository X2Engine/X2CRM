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
 * This is the model class for table "x2_events".
 * @package application.models
 */
class Events extends X2ActiveRecord {

    public $photo;
    public $audio;
    public $video;

    /**
     * Returns the static model of the specified AR class.
     * @return Imports the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'x2_events';
    }

    public function attributeNames () {
         return array_merge (
            parent::attributeNames (), 
            array (
                'photo',
                'audio',
                'video'
            )
        );
    }

    public function relations() {
        $relationships = array();
        $relationships = array_merge($relationships, array(
            'children' => array(
                self::HAS_MANY, 'Events', 'associationId', 
                'condition' => 'children.associationType="Events"'),
            'profile' => array(self::HAS_ONE, 'Profile', 
                array ('username' => 'user')),
            'userObj' => array(self::HAS_ONE, 'User', 
                array ('username' => 'user')),
            'media' => array (
                self::MANY_MANY, 'Media', 'x2_events_to_media(eventsId, mediaId)'),
            // only use this if $this->type === 'media'
            'legacyMedia' => array (
                self::HAS_ONE, 'Media', array ('id' => 'associationId')),
            'location' => array(self::BELONGS_TO, 'Locations', 'locationId'),
        ));
        return $relationships;
    }

    public function scopes () {
        return array (  
            'comments' => array (
                'condition' => 'associationType="Events" and associationId=:id',
                'params' => array (':id' => $this->id)
            )
        );
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
        );
    }

    public function behaviors(){
        $behaviors = array(
            'JSONFieldsBehavior' => array (
                'class' => 'application.components.behaviors.JSONFieldsBehavior',
                'transformAttributes' => array (
                    'recordLinks',
                ),
            ),
        );
        return $behaviors;
    }

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
                $userFolderPath = implode(DIRECTORY_SEPARATOR, array(
                    Yii::app()->basePath,
                    '..',
                    'uploads',
                    'protected',
                    'media',
                    $profile->username
                ));
                // if user folder doesn't exit, try to create it
                if (!(file_exists($userFolderPath) && is_dir($userFolderPath))) {
                    if (!@mkdir($userFolderPath, 0777, true)) { // make dir with edit permission
                        throw new CHttpException(500, "Couldn't create user folder $userFolderPath");
                    }
                }

                // add media record for file                
                $media = new Media;
                $media->setAttributes (array (
                    'fileName' => $filename,
                    'mimetype' => 'image/png',
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

                // relate file to event
                $join = new RelationshipsJoin ('insert', 'x2_events_to_media');
                $join->eventsId = $this->id;
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
    
    public function save ($runValidation=true, $attributes=null) {
        if ($this->photo || $this->audio || $this->video) {

            // save related photo record
            $transaction = Yii::app()->db->beginTransaction ();
            try {
                // save the event
                $ret = parent::save ($runValidation, $attributes);
                if (!$ret) {
                    throw new CException (implode (';', $this->getAllErrorMessages ()));
                }

                // add media record for file
                $media = new Media; 
                if ($this->photo) {
                    $media->setAttributes (array (
                        'fileName' => $this->photo->getName (),
                        'mimetype' => $this->photo->type,
                    ), false);
                } else if ($this->audio) {
                    $media->setAttributes (array (
                        'fileName' => $this->audio->getName (),
                        'mimetype' => $this->audio->type,
                    ), false);                    
                } else if ($this->video) {
                    $media->setAttributes (array (
                        'fileName' => $this->video->getName (),
                        'mimetype' => $this->video->type,
                    ), false);                     
                }
                $media->resolveNameConflicts ();
                if (!$media->save ()) {
                    throw new CException (implode (';', $media->getAllErrorMessages ()));
                }

                // save the file
                if ($this->photo) {
                    $tempName = $this->photo->getTempName ();
                } else if ($this->audio) {
                    $tempName = $this->audio->getTempName ();
                } else if ($this->video) {
                    $tempName = $this->video->getTempName ();
                }
                $username = Yii::app()->user->getName ();
                if (!FileUtil::ccopy(
                    $tempName, 
                    "uploads/protected/media/$username/{$media->fileName}")) {

                    throw new CException ();
                }

                // relate file to event
                $join = new RelationshipsJoin ('insert', 'x2_events_to_media');
                $join->eventsId = $this->id;
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
        } else {
            return parent::save ($runValidation, $attributes);
        }
    }

    public function getAssociation () {
        $modelName = X2Model::model2 ($this->associationType);
        if ($modelName) {
            return $modelName::model ()->findByPk ($this->associationId);
        }
    }

    public function renderFrameLink ($htmlOptions, $text=null) {
        if (Yii::app()->params->isMobileApp &&
            !LinkableBehavior::isMobileLinkableRecordType ($this->associationType)) {

            return Events::parseModelName ($this->associationType);
        }
        $association = $this->getAssociation ();
        if (!$association) {
            return Events::parseModelName ($this->associationType);
        }
        $htmlOptions = array_merge ($htmlOptions, array (
            'class' => 'action-frame-link',
            'data-action-id' => $this->associationId
        ));
        if ($association instanceof Actions &&
            in_array ($association->type, array ('note', 'time', 'call'))) {

            $name = Yii::t('app', 'comment');
            $htmlOptions['data-action-type'] = $association->type;
            $htmlOptions['data-text-only'] = 1;
        } else {
            $name = Events::parseModelName($this->associationType);
        }
        return CHtml::link(
            $text ? $text : $name,
            '#', $htmlOptions
        );
    }

    public function getRecipient () {
        $recipUser = Yii::app()->db->createCommand()
                ->select('username')
                ->from('x2_users')
                ->where('id=:id', array(':id' => $this->associationId))
                ->queryScalar();
        $recipient = '';
        if ($this->user != $recipUser && $this->associationId != 0) {
            if (Yii::app()->user->getId() == $this->associationId) {
                $recipient = 
                    CHtml::link(
                        Yii::t('app', 'You'), 
                        Yii::app()->params->profile->getUrl ());
            } else {
                $recipient = User::getUserLinks($recipUser);
            }
        }
        return $recipient;
    }

    /**
     * Parse an associationType field and resolve the model name
     * @param string $model Model type to resolve
     * @return string Model's name
     */
    public static function parseModelName($model) {
         
        // This can be removed once there's an easy way to specify item names for non-custom 
        // modules
        if ($model === 'EmailInboxes') {
            return Yii::t('emailInboxes', 'email inbox');
        }
         


        $customModule = Modules::model()->findByAttributes(array(
            'custom' => 1,
            'name' => $model,
            'moduleType'=>'module',
        ));
        if ($customModule) {
            //$model = $customModule->title;
            $model = Modules::itemDisplayName($customModule->name);
            $model = strtolower($model);
        } else {
            switch ($model) {
                case 'Product':
                    $model .= 's'; break;
                case 'Quote':
                    $model .= 's'; break;
                case 'Opportunity':
                    $model = str_replace('y', 'ies', $model); break;
            }
            $requestedModel = $model;
            $model = Modules::displayName(false, ucfirst($model));
            $model = strtolower($model);
            if (empty($model)) {
                // If the model type couldn't be resolved, check for special cases
                // of models without a dedicated module
                if ($requestedModel === 'AnonContact')
                    $model = 'anonymous contact';
                else if ($requestedModel === 'Campaign')
                    $model = 'campaign';
            }
        }
        return Yii::t('app', $model);
    }

    public function getText(array $params = array(), array $htmlOptions = array()) {
        $mediaId = Yii::app()->db->createCommand()
                ->select('mediaId')
                ->from('x2_events_to_media')
                ->where('eventsId=:eventsId', array(':eventsId' => $this->id))
                ->queryScalar();
        $params['media'] = Media::model()->findByAttributes(array('id' => $mediaId));
        $params['recipient'] = User::model()->findByAttributes(array('id' => $this->associationId));
        $params['profileRecipient'] = Profile::model()->findByPk($this->associationId);
        return EventTextFormatter::getText($this, $params, $htmlOptions);
    }

    public static $eventLabels = array(
        'feed' => 'Social Posts',
        'comment' => 'Comment',
        'record_create' => 'Records Created',
        'record_deleted' => 'Records Deleted',
        'action_reminder' => 'Action Reminders',
        'action_complete' => 'Actions Completed',
        'calendar_event' => 'Calendar Events',
        'case_escalated' => 'Cases Escalated',
        'email_opened' => 'Emails Opened',
        'email_sent' => 'Emails Sent',
        'notif' => 'Notifications',
        'weblead_create' => 'Webleads Created',
        'web_activity' => 'Web Activity',
        'workflow_complete' => 'Process Complete',
        'workflow_revert' => 'Process Reverted',
        'workflow_start' => 'Process Started',
        'doc_update' => 'Doc Updates',
        'email_from' => 'Email Received',
        'media' => 'Media',
        'voip_call' => 'VOIP Call',
        'topic_reply' => 'Topic Replies',
    );

    public static function parseType($type) {
        if (array_key_exists($type, self::$eventLabels))
            $type = self::$eventLabels[$type];

        return Yii::t('app', $type);
    }

    /**
     * Delete expired events (expiration defined by "Event Deletion Time" admin setting
     */
    public static function deleteOldEvents() {
        $dateRange = Yii::app()->settings->eventDeletionTime;
        if (!empty($dateRange)) {
            $dateRange = $dateRange * 24 * 60 * 60;
            $deletionTypes = json_decode(Yii::app()->settings->eventDeletionTypes, true);
            if (!empty($deletionTypes)) {
                $deletionTypes = "('" . implode("','", $deletionTypes) . "')";
                $time = time() - $dateRange;
                X2Model::model('Events')->deleteAll(
                        'lastUpdated < ' . $time . ' AND type IN ' . $deletionTypes);
            }
        }
    }

    /**
     * @param User $user
     * @return bool true if event is visible to user, false otherwiser 
     */
    public function isVisibleTo ($user) {
        if (Yii::app()->params->isAdmin) return true;
        if (!$user) return false;

        $assignedUser = null;
        if ($this->associationType === 'User') {
            $assignedUser = User::model ()->findByPk ($this->associationId);
        }
        switch (Yii::app()->settings->historyPrivacy) { 
            case 'group':
                if (in_array ($this->user, Groups::getGroupmates ($user->id)) ||
                    ($this->associationType === 'User' && 
                     $assignedUser &&
                     in_array ($assignedUser->username, Groups::getGroupmates ($user->id)))) {

                    return true;
                }
                // fall through
            case 'user':
                if ($this->user === $user->username ||
                    ($this->associationType === 'User' && 
                     ($this->associationId === $user->id))) {

                    return true;
                }
                break;
            default: // default history privacy (public or assigned)
                return ($this->user === $user->username || $this->visibility ||
                    $this->associationType === 'User' && $this->associationId === $user->id);
        }
        return false;
    }

    /**
     * @param Profile $profile Profile to filter events by. Used for profile feeds other than
     *  the current user's
     * @return CDbCriteria Events access criteria based on history privacy admin setting
     */
    public function getAccessCriteria (Profile $profile=null) {
        $criteria = new CDbCriteria;

        // ensures that condition string can be appended to other conditions
        $criteria->addCondition ('TRUE');
        if (!Yii::app()->params->isAdmin) {
            $criteria->params[':getAccessCriteria_username'] = Yii::app()->user->getName ();
            $criteria->params[':getAccessCriteria_userId'] = Yii::app()->user->getId ();
            $userCondition = '
                user=:getAccessCriteria_username OR
                associationType="User" AND associationId=:getAccessCriteria_userId
            ';
            if (Yii::app()->settings->historyPrivacy == 'user') {
                $criteria->addCondition ($userCondition);
            } elseif (Yii::app()->settings->historyPrivacy == 'group') {
                $criteria->addCondition ("
                    $userCondition OR
                    user IN (
                        SELECT DISTINCT b.username 
                        FROM x2_group_to_user a JOIN x2_group_to_user b 
                        ON a.groupId=b.groupId 
                        WHERE a.username=:getAccessCriteria_username
                    ) OR (
                        associationType='User' AND associationId in (
                            SELECT DISTINCT b.id
                            FROM x2_group_to_user a JOIN x2_group_to_user b
                            ON a.groupId=b.groupId
                            WHERE a.userId=:getAccessCriteria_userId
                        )
                    )");
            } else { // default history privacy (public or assigned)
                $criteria->addCondition ("
                    $userCondition OR visibility=1
                ");
            }
        }

        if ($profile) {
            $criteria->params[':getAccessCriteria_profileUsername'] = $profile->username;
            /* only show events associated with current profile which current user has
              permission to see */
            $criteria->addCondition ("user=:getAccessCriteria_profileUsername");
            if (!Yii::app()->params->isAdmin) {
                $criteria->addCondition ("visibility=1");
            }
        }
        return $criteria;
    }

    /**
     * Checks permissions for this event
     * TODO: add unit test
     */
    private $_permissions;
    public function checkPermissions ($action=null, $refresh = false) {
        if (!isset ($this->_permissions) || $refresh) {
            if (!Yii::app()->params->isAdmin) {
                $username = Yii::app()->user->getName ();
                $userId = Yii::app()->user->getId ();
                $userCondition = '
                    user=:getAccessCriteria_username OR
                    associationType="User" AND associationId=:getAccessCriteria_userId
                ';
                $edit = false;
                $view = $this->user === $username ||
                    strtolower ($this->associationType) === 'user' && 
                    $this->associationId == $userId;
                if (Yii::app()->settings->historyPrivacy == 'user') {
                } elseif (Yii::app()->settings->historyPrivacy == 'group') {
                    $view |= in_array (
                        strtolower ($this->user), 
                        Yii::app()->db->createCommand ("
                            SELECT LOWER(DISTINCT b.username)
                            FROM x2_group_to_user a JOIN x2_group_to_user b 
                            ON a.groupId=b.groupId 
                            WHERE a.username=:username
                        ")->queryColumn (array (':username' => $username))) ||
                        $this->associationType==='User' && 
                        in_array (
                            $this->associationId, 
                            Yii::app()->db->createCommand ("
                                SELECT DISTINCT b.id
                                FROM x2_group_to_user a JOIN x2_group_to_user b
                                ON a.groupId=b.groupId
                                WHERE a.userId=:userId
                            ")->queryColumn (array (':userId' => $userId)));
                } else { // default history privacy (public or assigned)
                    $view |= $this->visibility;
                }

                $edit = $view && $this->type === 'feed' && $this->user === $username;
                $delete = $view && $this->type === 'feed' && 
                    ($this->user === $username || $this->associationId == $userId);
            } else {
                $view = $edit = $delete = true;
            }
            $this->_permissions = array (
                'view' => $view,
                'edit' => $edit,
                'delete' => $delete,
            );
        } else {
            extract ($this->_permissions);
        }

        if (!$action) 
            return array('view' => (bool)$view, 'edit' => (bool)$edit, 'delete' => (bool)$delete);
        switch ($action) {
            case 'view':
                return (bool)$view;
            case 'edit':
                return (bool)$edit;
            case 'delete':
                return (bool)$delete;
            default:
                return false;
        }
    }

    /**
     * Returns events filtered with feed filters. Saves filters in session.
     * @param Profile $profile
     * @param bool $privateProfile whether to display public or private profile
     * @param array $filters
     * @param bool $filtersOn
     * @return array
     *  'dataProvider' => <object>
     *  'lastUpdated' => <integer>
     *  'lastId' => <integer>
     */
    public static function getFilteredEventsDataProvider(
        $profile, $privateProfile, $filters, $filtersOn) {

        $params = array(':username' => Yii::app()->user->getName ());
        $accessCriteria = Events::model ()->getAccessCriteria (!$privateProfile ? $profile : null);

        $visibilityCondition = '';
        if ($filtersOn || isset($_SESSION['filters'])) {
            if ($filters) {
                unset($_SESSION['filters']);
            } else {
                $filters = $_SESSION['filters'];
                $filters = array_map(function ($n) {
                    return implode(',', $n);
                }, $filters);
                $filters['default'] = false;
            }
            $parsedFilters = Events::parseFilters($filters, $params);

            $visibilityFilter = $parsedFilters['filters']['visibility'];
            $userFilter = $parsedFilters['filters']['users'];
            $typeFilter = $parsedFilters['filters']['types'];
            $subtypeFilter = $parsedFilters['filters']['subtypes'];

            $default = $filters['default'];
            $_SESSION['filters'] = array(
                'visibility' => $visibilityFilter,
                'users' => $userFilter,
                'types' => $typeFilter,
                'subtypes' => $subtypeFilter
            );
            if ($default == 'true') {
                Yii::app()->params->profile->defaultFeedFilters = json_encode(
                    $_SESSION['filters']);
                Yii::app()->params->profile->save();
            }
            $visibilityCondition .= $parsedFilters['conditions']['visibility'];
            $userCondition = $parsedFilters['conditions']['users'];
            $typeCondition = $parsedFilters['conditions']['types'];
            $subtypeCondition = $parsedFilters['conditions']['subtypes'];

            $condition = "(associationType is null or associationType!='Events') AND 
                (type!='action_reminder' " .
                "OR user=:username) AND " .
                "(type!='notif' OR user=:username)" .
                $visibilityCondition . $userCondition . $typeCondition . $subtypeCondition;
            $_SESSION['feed-condition'] = $condition;
            $_SESSION['feed-condition-params'] = $params;
        } else {
            $params[':profileId'] = Yii::app()->user->id;
            $params[':userName'] = Yii::app()->user->getName();
            $condition = "(associationType='User' AND associationId=:profileId AND "
                    . "visibility=1 OR (visibility=0 AND (user=:userName AND associationId=:profileId)))"
                    . " OR (associationType is null or associationType!='Events') AND " .
                    "(type!='action_reminder' OR user=:username) " .
                    "AND (type!='notif' OR user=:username)" .
                    $visibilityCondition;
        }

        $condition.= " AND timestamp <= " . time();
        $condition .= ' AND ('.$accessCriteria->condition.')';

        if (!isset($_SESSION['lastEventId'])) {
            $lastId = Yii::app()->db->createCommand()
                ->select('id')
                ->from('x2_events')
                ->where($condition, array_merge ($params, $accessCriteria->params))
                ->order('timestamp DESC, id DESC')
                ->limit(1)
                ->queryScalar();                          
            $_SESSION['lastEventId'] = $lastId;
        } else {
            $lastId = $_SESSION['lastEventId'];
        }
            $lastTimestamp = Yii::app()->db->createCommand()
                ->select('MAX(timestamp)')
                ->from('x2_events')
                ->where($condition, array_merge ($params, $accessCriteria->params))
                ->order('timestamp DESC, id DESC')
                ->limit(1)
                ->group('id')
                ->queryScalar();            
        if (empty($lastTimestamp)) {
            $lastTimestamp = 0;
        }
        if (isset($_SESSION['lastEventId'])) {
            if (!Yii::app()->params->isMobileApp) {
                $condition.=" AND id <= :lastEventId AND sticky = 0";
            } else {
                $condition.=" AND id <= :lastEventId";
            } 
            $params[':lastEventId'] = $_SESSION['lastEventId'];
        }


        $paginationClass = 'CPagination';
         
        if (Yii::app()->params->isPhoneGap) {
            $paginationClass = 'MobilePagination';
        }

        $dataProvider = new CActiveDataProvider('Events', array(
                'criteria' => array(
                'condition' => $condition,
                'order' => 'sticky DESC, timestamp DESC, id DESC',
                'params' => array_merge ($params, $accessCriteria->params),
             ),
            'pagination' => array(
                'class' => $paginationClass,
                'pageSize' => 20
            ),
        ));            

        return array(
            'dataProvider' => $dataProvider,
            'lastTimestamp' => $lastTimestamp,
            'lastId' => $lastId
        );
    }

    private static function parseFilters($filters, &$params) {
        unset($filters['filters']);
        $visibility = $filters['visibility'];
        $visibility = str_replace('Public', '1', $visibility);
        $visibility = str_replace('Private', '0', $visibility);
        $visibilityFilter = explode(",", $visibility);
        if ($visibility != "") {
            $visibilityParams = AuxLib::bindArray($visibilityFilter, 'visibility');
            $params = array_merge($params, $visibilityParams);
            $visibilityCondition = " AND visibility NOT IN (" . 
                implode(',', array_keys($visibilityParams)) . ")";
        } else {
            $visibilityCondition = "";
            $visibilityFilter = array();
        }

        $users = $filters['users'];
        if ($users != "") {
            $users = explode(",", $users);
            $users[] = '';
            $users[] = 'api';
            $userFilter = $users;
            if (sizeof($users)) {
                $usersParams = AuxLib::bindArray($users, 'users');
                $params = array_merge($params, $usersParams);
                $userCondition = " AND (user NOT IN (" . 
                    implode(',', array_keys($usersParams)) . ")";
            } else {
                $userCondition = "(";
            }
            if (!in_array('Anyone', $users)) {
                $userCondition.=" OR user IS NULL)";
            } else {
                $userCondition.=")";
            }
        } else {
            $userCondition = "";
            $userFilter = array();
        }

        $types = $filters['types'];
        if ($types != "") {
            $types = explode(",", $types);
            $typeFilter = $types;
            $typesParams = AuxLib::bindArray($types, 'types');
            $params = array_merge($params, $typesParams);
            $typeCondition = " AND (type NOT IN (" . 
                implode(',', array_keys($typesParams)) . ") OR important=1)";
        } else {
            $typeCondition = "";
            $typeFilter = array();
        }
        $subtypes = $filters['subtypes'];
        if (is_array($types) && $subtypes != "") {
            $subtypes = explode(",", $subtypes);
            $subtypeFilter = $subtypes;
            if (sizeof($subtypes)) {
                $subtypeParams = AuxLib::bindArray($subtypes, 'subtypes');
                $params = array_merge($params, $subtypeParams);
                $subtypeCondition = " AND (
                    type!='feed' OR subtype NOT IN (" . 
                        implode(',', array_keys($subtypeParams)) . ") OR important=1)";
            } else {
                $subtypeCondition = "";
            }
        } else {
            $subtypeCondition = "";
            $subtypeFilter = array();
        }
        $ret = array(
            'filters' => array(
                'visibility' => $visibilityFilter,
                'users' => $userFilter,
                'types' => $typeFilter,
                'subtypes' => $subtypeFilter,
            ),
            'conditions' => array(
                'visibility' => $visibilityCondition,
                'users' => $userCondition,
                'types' => $typeCondition,
                'subtypes' => $subtypeCondition,
            ),
            'params' => $params,
        );
        return $ret;
    }

    /**
     * TODO: merge this method with getFilteredEventsDataProvider, and remove reliance on SESSION, 
     *  regenerating condition on each request instead of storing it
     * @param int $lastEventId
     * @param string $lastTimestamp
     * @param object $profile The current user's profile model
     * @param object $profileId The profile model for which events are being requested.
     */
    public static function getEvents(
        $lastEventId, $lastTimestamp, $limit = null, $profile = null, $privateProfile = true) {

        $user = Yii::app()->user->getName();
        $criteria = new CDbCriteria();
        $prefix = ':getEvents'; // to prevent name collisions with feed-condition-params
        $sqlParams = array(
            $prefix . 'username' => $user,
            $prefix . 'maxTimestamp' => time(),
        );
        $parameters = array('order' => 'sticky DESC, timestamp DESC, id DESC');        
        if (!is_null($limit) && is_numeric($limit)) {
            $parameters['limit'] = $limit;
        }

        $sqlParams[$prefix . 'id'] = $lastEventId;
        $sqlParams[$prefix . 'timestamp'] = $lastTimestamp;
        $accessCriteria = Events::model ()->getAccessCriteria (!$privateProfile ? $profile : null);
        if (isset($_SESSION['feed-condition']) && isset($_SESSION['feed-condition-params'])) {
            $sqlParams = array_merge($sqlParams, $_SESSION['feed-condition-params']);
            $condition = $_SESSION['feed-condition'] . " AND 
                (`type`!='action_reminder' OR `user`=" . $prefix . "username) AND 
                (`type`!='notif' OR `user`=" . $prefix . "username) AND 
                (id > " . $prefix . "id AND (timestamp > " . $prefix . "timestamp AND timestamp < " . $prefix . "maxTimestamp))";
        } else {
            $condition = '(id > ' . $prefix . 'id AND (timestamp > ' . $prefix . 'timestamp AND timestamp < ' . $prefix . 'maxTimestamp)) AND 
                 (`associationType` is null or `associationType`!="Events")' . " AND 
                 (`type`!='action_reminder' OR `user`=" . $prefix . "username) AND 
                 (`type`!='notif' OR `user`=" . $prefix . "username)";
        }

        $sqlParams = array_merge($sqlParams, $accessCriteria->params);
        $condition .= " AND ($accessCriteria->condition)";

        $parameters['condition'] = $condition;
        $parameters['params'] = $sqlParams;
        $criteria->scopes = array('findAll' => array($parameters));

        return array(
            'events' => X2Model::model('Events')->findAll($criteria),
        );
    }

    public static function generateFeedEmail($filters, $userId, $range, $limit, $eventId, $deleteKey) {
        $image = Yii::app()->getAbsoluteBaseUrl(true).'/images/x2engine.png';

        $msg = "<div id='wrap' style='width:6.5in;height:9in;margin-top:auto;margin-left:auto;margin-bottom:auto;margin-right:auto;'><html><body><center>";
        $msg .= '<table border="0" cellpadding="0" cellspacing="0" height="100%" id="top-activity" style="background: white; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; font-weight: normal; font-style: normal; font-size: 14px; line-height: 1; color: #222222; position: relative; -webkit-font-smoothing: antialiased;background-color:#FAFAFA;height:25% !important; margin:0; padding:0; width:100% !important;" width="100%">'
                . "<tbody><tr><td align=\"center\" style=\"padding-top:20px;\" valign=\"top\">"
                . '<table border="0" cellpadding="0" cellspacing="0" id="templateContainer" style="border: 1px solid #F5F5F5;background-color:#FFFFFF;" width="600"><tbody>';
        $msg .= '<tr>
                    <td align="center" valign="top"><!-- // Begin Template Header \\ -->
			<table border="0" cellpadding="0" cellspacing="0" id="templateHeader" width="600">
                            <tbody>
				<tr>
                                    <td class="headerContent" style="color:#202020;font-weight:bold;line-height:100%;padding:0;text-align:center;vertical-align:middle;font-family: inherit;font-weight: normal;font-size: 14px;margin-bottom: 17px"><img id="headerImage campaign-icon" src="' . $image .'" style="border:0; height:auto; line-height:100%; outline:none; text-decoration:none;max-width:600px;" /></td>
                                </tr>
                                <tr>
                                    <td style="color:#202020;font-weight:bold;padding:5px;text-align:center;vertical-align:middle;font-family: inherit;font-weight: normal;font-size: 14px;"><h2>' . Yii::t('profile', 'Activity Feed Report') . '</h2></td>
                                </tr>
                            </tbody>
			</table>
                    <hr width="60%"></td><!-- // End Template Header \\ -->
		</tr>';

        $msg.='<tr><td align="center" valign="top"><!-- // Begin Template Body \\ -->'
                . '<table border="0" cellpadding="0" cellspacing="0" id="templateBody" width="600"><tbody><tr>'
                . '<td valign="top"><!-- // Begin Module: Standard Content \\ -->'
                . '<table border="0" cellpadding="20" cellspacing="0" width="100%"><tbody>';

        $params = array();

        $userRecord = X2Model::model('Profile')->findByPk($userId);
        $params[':username'] = $userRecord->username;

        $parsedFilters = Events::parseFilters($filters, $params);

        $visibilityCondition = $parsedFilters['conditions']['visibility'];
        $accessCriteria = Events::model ()->getAccessCriteria ();
        $userCondition = $parsedFilters['conditions']['users'];
        $typeCondition = $parsedFilters['conditions']['types'];
        $subtypeCondition = $parsedFilters['conditions']['subtypes'];

        $condition = "type!='comment' AND (type!='action_reminder' " .
                "OR user=:username) AND " .
                "(type!='notif' OR user=:username)" .
                $visibilityCondition . $userCondition . $typeCondition . $subtypeCondition . 
                ' AND ('.$accessCriteria->condition.')';
        switch ($range) {
            case 'daily':
                $timeRange = 24 * 60 * 60;
                break;
            case 'weekly':
                $timeRange = 7 * 24 * 60 * 60;
                break;
            case 'monthly':
                $timeRange = 30 * 24 * 60 * 60;
                break;
            default:
                $timeRange = 24 * 60 * 60;
                break;
        }
        $condition .= " AND timestamp BETWEEN " . (time() - $timeRange) . " AND " . time();

        $topTypes = Yii::app()->db->createCommand()
                ->select('type, COUNT(type)')
                ->from('x2_events')
                ->where($condition, array_merge ($params, $accessCriteria->params))
                ->group('type')
                ->order('COUNT(type) DESC')
                ->limit(5)
                ->queryAll();

        $topUsers = Yii::app()->db->createCommand()
                ->select('user, COUNT(user)')
                ->from('x2_events')
                ->where($condition, array_merge ($params, $accessCriteria->params))
                ->group('user')
                ->order('COUNT(user) DESC')
                ->limit(5)
                ->queryAll();

        $msg .= "<tr><td style='text-align:center;'>";
        $msg .= "<div>" . Yii::t('profile', "Here's your {range} update on what's been going on in X2Engine!", array(
                    '{range}' => Yii::t('profile', $range))) . "</div><br>"
                . "<div>Time Range: <em>" . Formatter::formatDateTime(time() - $timeRange) . "</em> to <em>" . Formatter::formatDateTime(time()) . "</em></div>";
        $msg .= "</tr></td>";

        $msg .= "<tr><td><table width='100%'><tbody>";
        $msg .= "<tr><th>" . Yii::t('profile', "Top Activity") . "</th><th>" . Yii::t('profile', "Top Users") . "</th></tr>";
        for ($i = 0; $i < 5; $i++) {
            $msg .= "<tr><td style='text-align:center;'>";
            if (isset($topTypes[$i])) {
                $type = Events::parseType($topTypes[$i]['type']);
                $count = $topTypes[$i]['COUNT(type)'];
                $msg .= $count . " " . $type;
            }
            $msg .= "</td><td style='text-align:center;'>";
            if (isset($topUsers[$i]) && $topUsers[$i]['COUNT(user)'] > 0) {
                $username = User::getUserLinks($topUsers[$i]['user'], false, true);
                $count = $topUsers[$i]['COUNT(user)'];
                $msg .= $count . " " . Yii::t('profile', "events from") . " " . $username . ".";
            }
            $msg .= "</td></tr>";
        }
        $msg .= "</tbody></table></td></tr>";
        $msg .= "<tr><td style='text-align:center'><hr width='60%'>";
        $msg .= "<tr><td style='text-align:center;'>" .
                Yii::t('profile', "Here's the {limit} most recent items on the Activity Feed.", array('{limit}' => $limit))
                . "</td></tr>";
        $msg .= "</td></tr>";
        $msg .= "<tr><td style='text-align:center'><hr width='60%'><table><tbody>";
        $events = new CActiveDataProvider('Events', array(
            'criteria' => array(
                'condition' => $condition,
                'order' => 'timestamp DESC',
                'params' => array_merge ($params, $accessCriteria->params),
            ),
            'pagination' => array(
                'pageSize' => $limit
            ),
        ));

        foreach ($events->getData() as $event) {
            $msg .= "<tr>";
            $avatar = Yii::app()->db->createCommand()
                ->select('avatar')
                ->from('x2_profile')
                ->where('username=:user', array(':user' => $event->user))
                ->queryScalar();
            $avatarImg = null;
            if (!empty($avatar) && file_exists($avatar)) {
                $avatarImg = Profile::renderAvatarImage($userId, 45, 45);
            } else {
                $dimensionLimit = 45;
                $avatarImg = X2Html::x2icon('profile-large',
                                array(
                            'class' => 'avatar-image default-avatar',
                            'style' => "font-size: ${dimensionLimit}px",
                ));
            }
            $typeFile = $event->type;
            if (in_array($event->type, array('email_sent', 'email_opened'))) {
                if (in_array($event->subtype, array('quote', 'invoice')))
                    $typeFile .= "_{$event->subtype}";
            }
            if ($event->type == 'record_create') {
                switch ($event->subtype) {
                    case 'call':
                        $typeFile = 'voip_call';
                        break;
                    case 'time':
                        $typeFile = 'log_time';
                        break;
                }
            }
            if (file_exists(Yii::app()->getAbsoluteBaseUrl() . '/themes/x2engine/images/eventIcons/' . $typeFile . '.png')) {
                $imgFile = Yii::app()->getAbsoluteBaseUrl() . '/themes/x2engine/images/eventIcons/' . $typeFile . '.png';
                $img = CHtml::image($imgFile, '',
                                array(
                            'style' => 'width:45px;height:45px;float:left;margin-right:5px;',
                ));
            }
            if (!empty($avatar) && file_exists($avatar)) {
                $img = $avatarImg;
            }

            $msg .= "<td>" . $img . "</td>";
            $msg .= "<td style='text-align:left'><span class='event-text'>" . $event->getText(array('requireAbsoluteUrl' => true), array('style' => 'text-decoration:none;')) . "</span></td>";
            $msg .= "</tr>";
        }
        $msg .= "</tbody></table></td></tr>";

        $msg .= "<tr><td style='text-align:center'><hr width='60%'><table><tbody>";
        $msg .= Yii::t('profile', "To stop receiving this report, ") . CHtml::link(Yii::t('profile', 'click here'), Yii::app()->getAbsoluteBaseUrl() . '/index.php/profile/deleteActivityReport?id=' . $eventId . '&deleteKey=' . $deleteKey);
        $msg .= "</tbody></table></td></tr>";

        $msg .= '</tbody></table></td></tr></tbody></table></td></tr>';

        $msg .= "<tbody></table></td></tr></tbody></table></center></body></html></div>";

        return $msg;
    }

    public function isTypeFeed () {
        return $this->type === 'feed' || $this->type === 'structured-feed';
    }

    protected function beforeSave() {
        if (empty($this->timestamp))
            $this->timestamp = time();
        $this->lastUpdated = time();
        if (!empty($this->user) && $this->isNewRecord) {
            $eventsData = X2Model::model('EventsData')->findByAttributes(array('type' => $this->type, 'user' => $this->user));
            if (isset($eventsData)) {
                $eventsData->count++;
            } else {
                $eventsData = new EventsData;
                $eventsData->user = $this->user;
                $eventsData->type = $this->type;
                $eventsData->count = 1;
            }
            $eventsData->save();
        }
        if ($this->type == 'record_deleted') {
            $this->text = preg_replace('/(<script(.*?)>|<\/script>)/', '', $this->text);
        }
        return parent::beforeSave();
    }

    protected function beforeDelete() {
        if (!empty($this->children)) {
            foreach ($this->children as $child) {
                $child->delete();
            }
        }
        return parent::beforeDelete();
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => Yii::t('admin', 'ID'),
            'type' => Yii::t('admin', 'Type'),
            'text' => Yii::t('admin', 'Text'),
            'associationType' => Yii::t('admin', 'Association Type'),
            'associationId' => Yii::t('admin', 'Association ID'),
        );
    }

    /**
     * Render a list of links to associated records
     */
    public function renderRecordLinks($htmlOptions = array()) {
        $modelLinks = array();
        if (!empty($this->recordLinks) && is_array($this->recordLinks)) {
            foreach ($this->recordLinks as $link) {
                if (isset($link[0]) && isset($link[1])) {
                    $model = X2Model::model($link[0])->findByPk($link[1]);
                    if ($model) {
                        $modelLinks[] = array('content' => $model->getLink());
                    }
                }
            }
            return X2Html::ul($modelLinks, $htmlOptions);
        }
    }
}
