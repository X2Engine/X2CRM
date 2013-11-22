<?php
/***********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 **********************************************************************************/


class X2GridViewMassActionAction extends CAction {

    // used to hold success, warning, and error messages
    private static $successFlashes = array ();
    private static $noticeFlashes = array ();
    private static $errorFlashes = array ();


    /**
     * Echoes flashes in the flash arrays 
     */
    private static function echoFlashes () {
        echo CJSON::encode (array (
            'notice' => self::$noticeFlashes,
            'success' => self::$successFlashes,
            'error' => self::$errorFlashes
        ));
    }

    /**
     * Delete selected records 
     */
    private function deleteSelected ($gvSelection) {
        $_GET['ajax'] = true; // prevent controller delete action from redirecting
        $updatedRecordsNum = sizeof ($gvSelection);
        foreach ($gvSelection as $recordId) {
            if(!ctype_digit((string) $recordId))
                throw new CHttpException(400, Yii::t('app', 'Invalid selection.'));
            $this->controller->actionDelete ($recordId);
        }
        self::$successFlashes[] = Yii::t(
            'app', '{updatedRecordsNum} record'.($updatedRecordsNum === 1 ? '' : 's').
                ' deleted', array ('{updatedRecordsNum}' => $updatedRecordsNum)
        );

    }

    /**
     * Tag selected records
     */
    private function tagSelected ($gvSelection) {
        if (!isset ($_POST['tags']) || !is_array ($_POST['tags']) || 
            !isset ($_POST['modelType'])) {

            AuxLib::printTestError ('Invalid request');
            return;
        }
        $modelType = X2Model::model ($_POST['modelType']);
        if ($modelType === null) {
            AuxLib::printTestError ('Invalid model type');
            return;
        }

        $updatedRecordsNum = 0;
        $tagsAdded = 0;
        foreach ($gvSelection as $recordId) {
            $model = $modelType->findByPk ($recordId);
            if ($model === null || !$this->controller->checkPermissions ($model, 'edit')) continue;
            $recordUpdated = false;
            foreach ($_POST['tags'] as $tag) {
                if (!$model->addTags ($tag)) {
                    self::$noticeFlashes[] = Yii::t(
                        'app', 'Record {recordId} could not be tagged with {tag}. This record '.
                            'may already have this tag.', array (
                            '{recordId}' => $recordId, '{tag}' => $tag
                        )
                    );
                } else {
                    $tagsAdded++;
                    $recordUpdated = true;
                }
            }
            if ($recordUpdated) $updatedRecordsNum++;
        }

        if ($updatedRecordsNum > 0) {
            self::$successFlashes[] = Yii::t(
                'app', '{tagsAdded} tag'.($tagsAdded === 1 ? '' : 's').
                    ' added to {updatedRecordsNum} record'.($updatedRecordsNum === 1 ? '' : 's'), 
                    array (
                        '{updatedRecordsNum}' => $updatedRecordsNum,
                        '{tagsAdded}' => $tagsAdded
                    )
            );
        }

    }

    /**
     * Update fields of selected records 
     */
    private function updateFieldsOfSelected ($gvSelection, $fieldName, $fieldVal) {
        $modelType = X2Model::Model ($this->controller->modelClass);
        $updatedRecordsNum = 0;
        foreach ($gvSelection as $recordId) {
            $model = $modelType->findByPk ($recordId);
            if ($model === null || !$this->controller->checkPermissions ($model, 'edit')) {
                self::$noticeFlashes[] = Yii::t(
                    'app', 'Record {recordId} could not be updated', array (
                        '{recordId}' => $recordId
                    )
                );
                continue;
            }

            $field = array ($fieldName => $fieldVal);
            $model->setX2Fields ($field);

            if (!$model->save ()) { 
                $errorMsg = $model->getError ($fieldName);
                self::$noticeFlashes[] = Yii::t(
                    'app', 'Record {recordId} could not be updated'.
                        ($errorMsg ? (': '.$errorMsg) : ''),
                    array ('{recordId}' => $recordId)
                );
                continue;
            }
            $updatedRecordsNum++;
        }
        if ($updatedRecordsNum > 0) {
            self::$successFlashes[] = Yii::t(
                'app', '{updatedRecordsNum} record'.($updatedRecordsNum === 1 ? '' : 's').
                    ' updated', array ('{updatedRecordsNum}' => $updatedRecordsNum)
            );
        }

    }

    /**
     * Add selected records to list with given id 
     */
    public function removeFromList($gvSelection, $listId){
        foreach($gvSelection as $contactId) {
            if(!ctype_digit((string) $contactId)) {
                AuxLib::printTestError ('Invalid selection');
            }
        }

        $list = CActiveRecord::model('X2List')->findByPk($listId);
        $updatedRecordsNum = sizeof ($gvSelection);

        // check permissions
        if($list !== null && $this->controller->checkPermissions($list, 'edit')) {
            $list->removeIds($_POST['gvSelection']);
            self::$successFlashes[] = Yii::t(
                'app', '{updatedRecordsNum} record'.($updatedRecordsNum === 1 ? '' : 's').
                    ' removed from list "{list}"', array (
                        '{updatedRecordsNum}' => $updatedRecordsNum,
                        '{list}' => $list->name,
                    )
            );
        } else {
            self::$errorFlashes[] = Yii::t(
                'app', 'You do not have permission to modify this list');
        }
    }

    /**
     * Add selected records to list with given id 
     */
    public function addToList($gvSelection, $listId){
        foreach($gvSelection as &$contactId) {
            if(!ctype_digit((string) $contactId)) {
                AuxLib::printTestError ('Invalid selection');
                return;
            }
        }

        $list = CActiveRecord::model('X2List')->findByPk($listId);
        $updatedRecordsNum = sizeof ($gvSelection);

        // check permissions
        if ($list !== null && $this->controller->checkPermissions ($list, 'edit')) {
            $list->addIds($gvSelection);
            self::$successFlashes[] = Yii::t(
                'app', '{updatedRecordsNum} record'.($updatedRecordsNum === 1 ? '' : 's').
                    ' added to list "{list}"', array (
                        '{updatedRecordsNum}' => $updatedRecordsNum,
                        '{list}' => $list->name,
                    )
            );
        } else {
            self::$errorFlashes[] = Yii::t(
                'app', 'You do not have permission to modify this list');
        }
    }

    /**
     * Create new list with given name and add selected contacts to it 
     */
    public function createList ($gvSelection, $listName) {
        foreach($gvSelection as &$contactId){
            if(!ctype_digit((string) $contactId))
                throw new CHttpException(400, Yii::t('app', 'Invalid selection.'));
        }

        $list = new X2List;
        $list->name = $_POST['listName'];
        $list->modelName = 'Contacts';
        $list->type = 'static';
        $list->assignedTo = Yii::app()->user->getName();
        $list->visibility = 1;
        $list->createDate = time();
        $list->lastUpdated = time();

        $itemModel = X2Model::model('Contacts');
        if($list->save()){ // if the list is valid save it so we can get the ID
            $count = 0;
            foreach($gvSelection as &$itemId){

                if($itemModel->exists('id="'.$itemId.'"')){ // check if contact exists
                    $item = new X2ListItem;
                    $item->contactId = $itemId;
                    $item->listId = $list->id;
                    if($item->save()) // add all the things!
                        $count++;
                }
            }
            $list->count = $count;
            if($list->save()) {
                self::$successFlashes[] = Yii::t(
                    'app', '{count} record'.($count === 1 ? '' : 's').
                        ' added to new list "{list}"', array (
                            '{count}' => $count,
                            '{list}' => $list->name,
                        )
                );
            } else {
                AuxLib::printTestError ($list->getError ());
                self::$errorFlashes[] = Yii::t(
                    'app', 'List could not be created');
            }
        } else {
            AuxLib::printTestError ($list->getError ());
            self::$errorFlashes[] = Yii::t(
                'app', 'List could not be created');
        }
    }

    /**
     * Execute specified mass action on specified records
     */
    public function run(){
        if (!isset ($_POST['massAction']) || !isset ($_POST['gvSelection']) ||
            !is_array ($_POST['gvSelection'])) {

            AuxLib::printTestError ('Invalid request');
            return;
        }

        $massAction = $_POST['massAction'];
        $gvSelection = $_POST['gvSelection'];
        switch ($massAction) {
            case 'delete':
                $this->deleteSelected ($gvSelection);
                break;
            case 'tag':
                $this->tagSelected ($gvSelection);
                break;
            case 'updateFields':
                if (!isset ($_POST['fieldName']) || !isset ($_POST['fieldVal'])) {
                    AuxLib::printTestError ('Invalid request');
                    return;
                }
                $this->updateFieldsOfSelected (
                    $gvSelection, $_POST['fieldName'], $_POST['fieldVal']);
                break;
            case 'addToList':
                if ($this->controller->modelClass !== 'Contacts' || !isset ($_POST['listId'])) {
                    AuxLib::printTestError ('Invalid request');
                    return;
                }
                $this->addToList ($gvSelection, $_POST['listId']);
                break;
            case 'removeFromList':
                if ($this->controller->modelClass !== 'Contacts' || !isset ($_POST['listId'])) {
                    AuxLib::printTestError ('Invalid request');
                    return;
                }
                $this->removeFromList ($gvSelection, $_POST['listId']);
                break;
            case 'createList':
                if ($this->controller->modelClass !== 'Contacts' || 
                    !isset ($_POST['listName']) || $_POST['listName'] === '') {

                    AuxLib::printTestError ('Invalid request');
                    return;
                }
                $this->createList ($gvSelection, $_POST['listName']);
                break;
            default:
                AuxLib::printTestError ('Mass action not available');
                return;
        }
        self::echoFlashes ();
    }

}

?>
