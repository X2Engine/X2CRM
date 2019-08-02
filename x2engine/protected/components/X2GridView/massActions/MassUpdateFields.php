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






class MassUpdateFields extends MassAction {

    protected $_label;

    /**
     * Renders the mass action dialog, if applicable
     * @param string $gridId id of grid view
     */
    public function renderDialog ($gridId, $modelName) {
        $editableFieldsFieldInfo = FormLayout::model ()->getEditableFieldsInLayout ($modelName);
        ArrayUtil::asorti ($editableFieldsFieldInfo, true);
        echo "
            <div class='mass-action-dialog x2-gridview-update-field-dialog' 
            id='".$this->getDialogId ($gridId)."' style='display: none;'>
                <span class='dialog-help-text'>".
                    Yii::t('app', 'Select a field and enter a field value')."
                </span><br/>
                <div class='update-fields-inputs-container'>";
        if (sizeof ($editableFieldsFieldInfo) !== 0) {
            $fieldNames = array_keys ($editableFieldsFieldInfo);
            $defaultField = X2Model::model ($modelName)->getField ($fieldNames[0]);
            echo "
                <select class='update-field-field-selector left'>";
            foreach ($editableFieldsFieldInfo as $editableFieldName=>$attrLabel) {
                echo "
                    <option value='".CHtml::encode ($editableFieldName)."'>".
                        CHtml::encode ($attrLabel)."</option>";
            }
            echo "
                </select>
                <span class='update-fields-field-input-container' 
                 data-type='".CHtml::encode ($defaultField->type)."'>";
            echo X2Model::model ($modelName)->renderInput ($defaultField->fieldName);
            echo "
                <br/><br/>
                </span>";
        }
        echo "
                </div>
            </div>";
    }

    /**
     * @return string label to display in the dropdown list
     */
    public function getLabel () {
        if (!isset ($this->_label)) {
            $this->_label = Yii::t('app', 'Update fields of selected');
        }
        return $this->_label;
    }

    public function getPackages () {
        return array_merge (parent::getPackages (), array (
            'X2UpdateFields' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/X2GridView/MassUpdateFields.js',
                ),
                'depends' => array ('X2MassAction'),
            ),
        ));
    }

    public function execute (array $gvSelection) {
        if (!isset ($_POST['fields'])) {
            throw new CHttpException (400, Yii::t('app', 'Bad Request'));
        }
        $fields = $_POST['fields'];

        $staticModel = X2Model::model (Yii::app()->controller->modelClass);
        $updatedRecordsNum = 0;
        foreach ($gvSelection as $recordId) {
            $model = $staticModel->findByPk ($recordId);
            if ($model === null || !Yii::app()->controller->checkPermissions ($model, 'edit')) {
                self::$noticeFlashes[] = Yii::t(
                    'app', 'Record {recordId} could not be updated.', array (
                        '{recordId}' => $recordId
                    )
                ).($model === null ? 
                    Yii::t('app','The record could not be found.') : 
                    Yii::t('app','You do not have sufficient permissions.'));
                continue;
            }

            if (isset($fields['associationType']) && isset($fields['associationName']) && 
                $fields['associationType'] !== 'none') {

                // If we are setting an association, lookup the association id
                $attributes = array('name' => $fields['associationName']);
                $associatedModel = X2Model::Model($fields['associationType'])
                    ->findByAttributes($attributes);
                $fields['associationId'] = $associatedModel->id;
            }

            $model->setX2Fields($fields);
            if (!$model->save()) {
                $errors = $model->getAllErrorMessages();
                foreach ($errors as $err) {
                    self::$noticeFlashes[] = Yii::t(
                        'app', 'Record {recordId} could not be updated: '.$err,
                        array ('{recordId}' => $recordId)
                    );
                }
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

        return $updatedRecordsNum;

    }

}
