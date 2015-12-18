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
 * Behavior to define the set of methods necessary to merge two or more records
 * into a single record. Should always be implemented on records which have
 * X2DuplicateBehavior but is more general in its application.
 */
class X2MergeableBehavior extends CActiveRecordBehavior {
    
    /**
     * Fields which should not be set from old records in a merge.
     * @var array 
     */
    public $restrictedFields = array(
        'id',
        'nameId',
    );
    
    

    /**
     * Set the createDate value of $this->owner to the oldest createDate of the duplicates
     * @param array $duplicates
     */
    public function setMergedCreateDate($duplicates) {
        foreach ($duplicates as $oldModel) {
            if (!empty($oldModel->createDate) && (empty($this->owner->createDate) || $oldModel->createDate < $this->owner->createDate)) {
                $this->owner->createDate = $oldModel->createDate;
            }
        }
    }

    /**
     * Wrapper for mergeRelatedRecords to merge each duplicate into the new record
     * and hide the duplicate.
     * @param array $duplicates Records to be merged into $this->owner
     * @param boolean $logMerge Whether to log merge information so it can be undone
     */
    public function massMergeRelatedRecords($duplicates, $logMerge) {
        foreach ($duplicates as $oldModel) {
            $this->owner->mergeRelatedRecords($oldModel, $logMerge);
            if ($oldModel->hasAttribute('visibility')) {
                $oldModel->visibility = 0;
            }
            if ($oldModel->hasAttribute('assignedTo')) {
                $oldModel->assignedTo = 'Anyone';
            }
            if ($oldModel->hasAttribute('doNotCall')) {
                $oldModel->doNotCall = 1;
            }
            if ($oldModel->hasAttribute('doNotEmail')) {
                $oldModel->doNotEmail = 1;
            }
            $oldModel->update();
        }
    }

    /**
     * Transfers all events, notifications, and actions, tags, relationships, and 
     * lookup fields related to the given $model to $this->owner. 
     */
    public function mergeRelatedRecords(X2Model $model, $logMerge = false) {

        $mergeData = array();
        
        $ret = $this->owner->mergeActions($model, $logMerge);
        if ($logMerge && !empty($ret)) {
            $mergeData['data']['actions'] = $ret;
        }
        
        $ret = $this->owner->mergeEvents($model, $logMerge);
        if ($logMerge && !empty($ret)) {
            $mergeData['data']['events'] = $ret;
        }
         
        $ret = $this->owner->mergeNotifications($model, $logMerge);
        if ($logMerge && !empty($ret)) {
            $mergeData['data']['notifications'] = $ret;
        }
            
        $ret = $this->owner->mergeTags($model, $logMerge);
        if ($logMerge && !empty($ret)) {
            $mergeData['data']['tags'] = $ret;
        }
        
        $ret = $this->owner->mergeRelationships($model, $logMerge);
        if ($logMerge && !empty($ret)) {
            $mergeData['data']['relationships'] = $ret;
        }
        
        $ret = $this->owner->mergeLinkFields($model, $logMerge);
        if ($logMerge && !empty($ret)) {
            $mergeData['data']['linkFields'] = $ret;
        }
        
        if ($logMerge) {
            $mergeData['assignedTo'] = $model->assignedTo;
            $mergeData['visibility'] = $model->visibility;
            Yii::app()->db->createCommand()
                    ->insert('x2_merge_log', array(
                        'modelType' => get_class($model),
                        'modelId' => $model->id,
                        'mergeModelId' => $this->owner->id,
                        'mergeData' => json_encode($mergeData),
                        'mergeDate' => time(),
            ));
        }
    }

    /**
     * Transfers all related Actions from $model to $this->owner
     */
    public function mergeActions(X2Model $model, $logMerge = false) {
        $ret = array();
        $associationType = X2Model::getAssociationType(get_class($model));
        $tartgetAssociationType = X2Model::getAssociationType(get_class($this->owner));
        if ($logMerge) {
            $ids = Yii::app()->db->createCommand()
                    ->select('id')
                    ->from('x2_actions')
                    ->where(
                            'associationType = :type AND associationId = :id', array(':type' => $associationType, ':id' => $model->id))
                    ->queryColumn();
            $ret = $ids;
        }

        X2Model::model('Actions')->updateAll(array(
            'associationType' => $tartgetAssociationType,
            'associationId' => $this->owner->id,
                ), 'associationType = :type AND associationId = :id', array(
            ':type' => $associationType,
            ':id' => $model->id
        ));

        return $ret;
    }

    /**
     * Transfers all related Events from $model to $this->owner
     */
    public function mergeEvents(X2Model $model, $logMerge = false) {
        $ret = array();
        if ($logMerge) {
            $ids = Yii::app()->db->createCommand()
                    ->select('id')
                    ->from('x2_events')
                    ->where(
                            'associationType = :type AND associationId = :id', array(':type' => get_class($model), ':id' => $model->id))
                    ->queryColumn();
            $ret = $ids;
        }
        X2Model::model('Events')->updateAll(
                array(
            'associationId' => $this->owner->id,
            'associationType' => get_class ($this->owner),
                ), 'associationType = :type AND associationId = :id', array(':type' => get_class($model), ':id' => $model->id));
        return $ret;
    }

    /**
     * Transfers all related Notifications from $model to $this->owner
     */
    public function mergeNotifications(X2Model $model, $logMerge = false) {
        $ret = array();
        $modelType = get_class($model);
        $targetModelType = get_class($this->owner);
        if ($logMerge) {
            $ids = Yii::app()->db->createCommand()
                    ->select('id')
                    ->from('x2_notifications')
                    ->where(
                            'modelType = :type and modelId = :id', array(':type' => $modelType, ':id' => $model->id))
                    ->queryColumn();
            $ret = $ids;
        }
        X2Model::model('Notification')
                ->updateAll(array(
                    'modelId' => $this->owner->id,
                    'modelType' => $targetModelType,
                        ), 'modelType = :type AND modelId = :id', array(
                    ':type' => $modelType,
                    ':id' => $model->id
        ));
        return $ret;
    }

    /**
     * Transfers all tags from $model to $this->owner
     */
    public function mergeTags(X2Model $model, $logMerge = false) {
        $ret = array();
        if ($logMerge) {
            $ret = $model->getTags();
        }
        $this->owner->disableTagTriggers();
        $this->owner->addTags($model->getTags());
        $this->owner->enableTagTriggers();
        $model->clearTags();
        return $ret;
    }

    /**
     * Transfers all relationships of $model to $this->owner 
     */
    public function mergeRelationships(X2Model $model, $logMerge = false) {
        $ret = array();
        $modelType = get_class($model);
        $targetModelType = get_class($this->owner);
        if ($logMerge) {
            $firstIds = Yii::app()->db->createCommand()
                    ->select('id')
                    ->from('x2_relationships')
                    ->where(
                            'firstType = :type AND firstId = :id', array(':type' => $modelType, ':id' => $model->id))
                    ->queryColumn();
            if (!empty($firstIds)) {
                $ret['first'] = $firstIds;
            }

            $secondIds = Yii::app()->db->createCommand()
                    ->select('id')
                    ->from('x2_relationships')
                    ->where(
                            'secondType = :type AND secondId = :id', array(':type' => $modelType, ':id' => $model->id))
                    ->queryColumn();
            if (!empty($secondIds)) {
                $ret['second'] = $secondIds;
            }
        }
        Relationships::model()->updateAll(array(
            'firstId' => $this->owner->id,
            'firstType' => $targetModelType,
                ), 'firstType = :type AND firstId = :id', array(
            ':type' => $modelType,
            ':id' => $model->id,
        ));
        Relationships::model()->updateAll(array(
            'secondId' => $this->owner->id,
            'secondType' => $targetModelType,
                ), 'secondType = :type AND secondId = :id', array(
            ':type' => $modelType,
            ':id' => $model->id,
        ));
        return $ret;
    }

    /**
     * Transfers link fields pointing to $model to $htis->owner
     */
    public function mergeLinkFields(X2Model $model, $logMerge = false) {
        $ret = array();

        $linkFields = Fields::model()
                ->findAllByAttributes(array('type' => 'Link', 'linkType' => get_class($model)));
        foreach ($linkFields as $field) {
            if ($logMerge) {
                $ids = Yii::app()->db->createCommand()
                        ->select('id')
                        ->from(X2Model::model($field->modelName)->tableName())
                        ->where($field->fieldName . ' = :id', array(':id' => $model->nameId))
                        ->queryColumn();
                if (!empty($ids)) {
                    $ret[$field->modelName]['field'] = $field->fieldName;
                    $ret[$field->modelName]['ids'] = $ids;
                }
            }
            Yii::app()->db->createCommand()->update(
                    X2Model::model($field->modelName)->tableName(), array(
                $field->fieldName => $this->owner->nameId,
                    ), $field->fieldName . ' = :id', array(':id' => $model->nameId));
        }
        return $ret;
    }
    
    /**
     * Undo a merge based on merge log data stored during the merge process. Should
     * only be called by models which have a corresponding merge log entry. Most helper
     * functions are simpler than their merge counterparts because related records can be
     * transferred between different record types, but actual merges (which are capable
     * of being undone) can only be performed on records of the same type.
     */
    public function revertMerge() {
        $mergeLog = Yii::app()->db->createCommand()
            ->select('*')
            ->from('x2_merge_log')
            ->where(
                'mergeModelId = :id AND modelType = :type', 
                array(':id' => $this->owner->id, ':type' => get_class($this->owner)))
            ->queryAll();
        if (!empty($mergeLog)) {
            foreach ($mergeLog as $log) {
                $mergeData = json_decode($log['mergeData'], true);
                $model = X2Model::model($log['modelType'])->findByPk($log['modelId']);
                if (isset($model) && !empty($mergeData)) {
                    $model->assignedTo = $mergeData['assignedTo'];
                    $model->visibility = $mergeData['visibility'];
                    $model->save();
                    if (isset($mergeData['data']) && !empty($mergeData['data'])) {
                        foreach ($mergeData['data'] as $key => $data) {
                            switch ($key) {
                                case 'actions':
                                    $this->owner->unmergeActions($model->id, $data);
                                    break;
                                case 'events':
                                    $this->owner->unmergeEvents($model->id, $data);
                                    break;
                                case 'notifications':
                                    $this->owner->unmergeNotifications($model->id, $data);
                                    break;
                                case 'tags':
                                    $this->owner->unmergeTags($model->id, $data);
                                    break;
                                case 'relationships':
                                    $this->owner->unmergeRelationships($model->id, $data);
                                    break;
                                case 'linkFields':
                                    $this->owner->unmergeLinkFields($model->id, $data);
                                    break;
                            }
                        }
                    }
                }
            }
            $mergeLog = Yii::app()->db->createCommand()
                ->delete(
                    'x2_merge_log', 'mergeModelId = :id AND modelType = :type', 
                    array(':id' => $this->owner->id, ':type' => get_class($this->owner)));
            $this->owner->delete();
        }
    }

    public function unmergeActions($id, $actionIds) {
        X2Model::model('Actions')->updateByPk($actionIds, array('associationId' => $id));
    }

    public function unmergeEvents($id, $eventIds) {
        X2Model::model('Events')->updateByPk($eventIds, array('associationId' => $id));
    }

    public function unmergeNotifications($id, $notifIds) {
        X2Model::model('Notification')->updateByPk($notifIds, array('modelId' => $id));
    }

    public function unmergeTags($id, $tags) {
        $this->owner->removeTags($tags);
        $model = X2Model::model(get_class($this->owner))->findByPk($id);
        $model->addTags($tags);
    }

    public function unmergeRelationships($id, $data) {
        if (isset($data['first'])) {
            X2Model::model('Relationships')->updateByPk($data['first'], array('firstId' => $id));
        }
        if (isset($data['second'])) {
            X2Model::model('Relationships')->updateByPk($data['second'], array('secondId' => $id));
        }
    }

    public function unmergeLinkFields($id, $data) {
        $model = X2Model::model(get_class($this->owner))->findByPk($id);
        foreach ($data as $modelName => $fieldData) {
            X2Model::model($modelName)
                ->updateByPk($fieldData['ids'], array($fieldData['field'] => $model->nameId));
        }
    }

}
