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

class MassDelete extends MassAction {

    protected $requiresPasswordConfirmation = true;

    public function execute (array $gvSelection) {
        if (!isset ($_POST['modelType'])) {

            throw new CHttpException (400, Yii::t('app', 'Bad request.'));
            return;
        }
        $_GET['ajax'] = true; // prevent controller delete action from redirecting

        $updatedRecordsNum = sizeof ($gvSelection);
        $unauthorized = 0;
        $failed = 0;
        foreach ($gvSelection as $recordId) {

            // controller action permissions only work for the module's main model
            if (X2Model::getModelName (Yii::app()->controller->module->name) === 
                $_POST['modelType']) {

                if(!ctype_digit((string) $recordId))
                    throw new CHttpException(400, Yii::t('app', 'Invalid selection.'));
                try{
                    //$_GET['id'] = $recordId; // only users who can delete all records can
                    // call this action, so we don't need to check the assignedTo field
                    if(Yii::app()->controller->beforeAction('delete'))
                        Yii::app()->controller->actionDelete ($recordId);
                    //unset ($_GET['id']);
                }catch(CHttpException $e){
                    if($e->statusCode === 403)
                        $unauthorized++;
                    else
                        throw $e;
                }
            } else if (Yii::app()->params->isAdmin) {
                // at the time of implementing this, the only model types that this applies to
                // are AnonContact and Fingerprint, both of which can only be deleted by admin users

                if (class_exists ($_POST['modelType'])) {
                    $model = X2Model::model ($_POST['modelType'])->findByPk ($recordId);
                    if (!$model || !$model->delete ()) {
                        $failed++;
                    }
                } else {
                    $failed++;
                }
            } else {
                $unauthorized++;
            }
        }
        $updatedRecordsNum = $updatedRecordsNum - $unauthorized - $failed;
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
        return $updatedRecordsNum;
    }

}
