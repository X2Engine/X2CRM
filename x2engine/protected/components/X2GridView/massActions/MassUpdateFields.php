<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
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

class MassUpdateFields extends MassAction {

    public function execute ($gvSelection) {
        if (!isset ($_POST['fields'])) {
            throw new CHttpException (400, Yii::t('app', 'Bad Request'));
        }
        $fields = $_POST['fields'];

        $modelType = X2Model::Model (Yii::app()->controller->modelClass);
        $updatedRecordsNum = 0;
        foreach ($gvSelection as $recordId) {
            $model = $modelType->findByPk ($recordId);
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
