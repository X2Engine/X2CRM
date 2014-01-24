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
        $unauthorized = 0;
        foreach ($gvSelection as $recordId) {
            if(!ctype_digit((string) $recordId))
                throw new CHttpException(400, Yii::t('app', 'Invalid selection.'));
            try{
                if($this->controller->beforeAction('delete'))
                    $this->controller->actionDelete ($recordId);
            }catch(CHttpException $e){
                if($e->statusCode==403)
                    $unauthorized++;
                else
                    throw $e;
            }
        }
        $updatedRecordsNum = $updatedRecordsNum - $unauthorized;
        self::$successFlashes[] = Yii::t(
            'app', '{updatedRecordsNum} record'.($updatedRecordsNum === 1 ? '' : 's').
            ' deleted', array('{updatedRecordsNum}' => $updatedRecordsNum)
        );
        if($unauthorized > 0){
            self::$errorFlashes[] = Yii::t(
                'app', 'You were not authorized to delete {unauthorized} record'.
                ($unauthorized === 1 ? '' : 's'), array('{unauthorized}' => $unauthorized)
            );
        }


    }

    /**
     * Tag selected records
     */
    private function tagSelected ($gvSelection) {
        if (!isset ($_POST['tags']) || !is_array ($_POST['tags']) ||
            !isset ($_POST['modelType'])) {

//            AuxLib::printTestError ('Invalid request');
            return;
        }
        $modelType = X2Model::model ($_POST['modelType']);
        if ($modelType === null) {
//            AuxLib::printTestError ('Invalid model type');
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
    private function updateFieldsOfSelected ($gvSelection, $fields) {
        $modelType = X2Model::Model ($this->controller->modelClass);
        $updatedRecordsNum = 0;
        foreach ($gvSelection as $recordId) {
            $model = $modelType->findByPk ($recordId);
            if ($model === null || !$this->controller->checkPermissions ($model, 'edit')) {
                self::$noticeFlashes[] = Yii::t(
                    'app', 'Record {recordId} could not be updated.', array (
                        '{recordId}' => $recordId
                    )
                ).($model === null ? Yii::t('app','The record could not be found.') : Yii::t('app','You do not have sufficient permissions.'));
                continue;
            }

            if (isset($fields['associationType']) && isset($fields['associationName']) && $fields['associationType'] != 'none') {
                // If we are setting an association, lookup the association id
                $attributes = array('name' => $fields['associationName']);
                $associatedModel = X2Model::Model($fields['associationType'])->findByAttributes($attributes);
                $fields['associationId'] = $associatedModel->id;
            }

            $model->setX2Fields($fields);

            if (!$model->save()) {
                $errorMsg = $model->getErrors();
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
//                AuxLib::printTestError ('Invalid selection');
            }
        }

        $list = CActiveRecord::model('X2List')->findByPk($listId);
        $updatedRecordsNum = sizeof ($gvSelection);

        // check permissions
        if($list !== null && $this->controller->checkPermissions($list, 'edit')) {
            if ($list->removeIds($_POST['gvSelection'])) {
                self::$successFlashes[] = Yii::t(
                    'app', '{updatedRecordsNum} record'.($updatedRecordsNum === 1 ? '' : 's').
                        ' removed from list "{list}"', array (
                            '{updatedRecordsNum}' => $updatedRecordsNum,
                            '{list}' => $list->name,
                        )
                );
            } else {
                self::$errorFlashes[] = Yii::t(
                    'app', 'The selected record'.($updatedRecordsNum === 1 ? '' : 's').
                        ' could not be removed from this list');
            }
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
//                AuxLib::printTestError ('Invalid selection');
                return;
            }
        }

        $list = CActiveRecord::model('X2List')->findByPk($listId);
        $updatedRecordsNum = sizeof ($gvSelection);

        // check permissions
        if ($list !== null && $this->controller->checkPermissions ($list, 'edit')) {
            if ($list->addIds($gvSelection)) {
                self::$successFlashes[] = Yii::t(
                    'app', '{updatedRecordsNum} record'.($updatedRecordsNum === 1 ? '' : 's').
                        ' added to list "{list}"', array (
                            '{updatedRecordsNum}' => $updatedRecordsNum,
                            '{list}' => $list->name,
                        )
                );
            } else {
                self::$errorFlashes[] = Yii::t(
                    'app', 'The selected record'.($updatedRecordsNum === 1 ? '' : 's').
                        ' could not be added to this list');
            }
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
//                AuxLib::printTestError ($list->getError ());
                self::$errorFlashes[] = Yii::t(
                    'app', 'List could not be created');
            }
        } else {
//            AuxLib::printTestError ($list->getError ());
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

//            AuxLib::printTestError ('Invalid request');
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
                if (!isset ($_POST['fields'])) {
//                    AuxLib::printTestError ('Invalid request');
                    return;
                }
                $this->updateFieldsOfSelected (
                    $gvSelection, $_POST['fields']);
                break;
            case 'addToList':
                if ($this->controller->modelClass !== 'Contacts' || !isset ($_POST['listId'])) {
//                    AuxLib::printTestError ('Invalid request');
                    return;
                }
                $this->addToList ($gvSelection, $_POST['listId']);
                break;
            case 'removeFromList':
                if ($this->controller->modelClass !== 'Contacts' || !isset ($_POST['listId'])) {
//                    AuxLib::printTestError ('Invalid request');
                    return;
                }
                $this->removeFromList ($gvSelection, $_POST['listId']);
                break;
            case 'createList':
                if ($this->controller->modelClass !== 'Contacts' ||
                    !isset ($_POST['listName']) || $_POST['listName'] === '') {

//                    AuxLib::printTestError ('Invalid request');
                    return;
                }
                $this->createList ($gvSelection, $_POST['listName']);
                break;
            default:
//                AuxLib::printTestError ('Mass action not available');
                return;
        }
        self::echoFlashes ();
    }

}

?>
