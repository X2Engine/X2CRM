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






class MassDelete extends MassAction {

    public $hasButton = true; 

    protected $requiresPasswordConfirmation = true;

    protected $_label;

    /**
     * Renders the mass action dialog, if applicable
     * @param string $gridId id of grid view
     */
    public function renderDialog ($gridId, $modelName) {
        echo "
            <div class='mass-action-dialog' id='".$this->getDialogId ($gridId)."'
             style='display: none;'>
                <span>".
                    Yii::t('app', 'Are you sure you want to delete all selected records?')."
                    <br/>".
                    Yii::t('app', 'This action cannot be undone.')."
                </span>
            </div>";
    }

    /**
     * Renders the mass action button, if applicable
     */
    public function renderButton () {
        if (!$this->hasButton) return;
        
        echo "
            <a href='#' title='".CHtml::encode ($this->getLabel ())."'
             data-mass-action='".get_class ($this)."'
             data-allow-multiple='".($this->allowMultiple ? 'true' : 'false')."'
             class='fa fa-trash fa-lg mass-action-button x2-button mass-action-button-".
                get_class ($this)."'>
            </a>";
    }

    /**
     * @return string label to display in the dropdown list
     */
    public function getLabel () {
        if (!isset ($this->_label)) {
            $this->_label = Yii::t('app', 'Delete selected');
        }
        return $this->_label;
    }

    public function getPackages () {
        return array_merge (parent::getPackages (), array (
            'X2MassDelete' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/X2GridView/MassDelete.js',
                ),
                'depends' => array ('X2MassAction'),
            ),
        ));
    }

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
                    $_GET['id'] = $recordId; // only users who can delete all records can
                    // call this action, so we don't need to check the assignedTo field
                    if(Yii::app()->controller->beforeAction('delete'))
                        Yii::app()->controller->actionDelete ($recordId);
                    unset ($_GET['id']);
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
        if ($updatedRecordsNum) {
            self::$successFlashes[] = Yii::t(
                'app', '{n} record deleted|{n} records deleted', array($updatedRecordsNum)
            );
        }
        if($unauthorized > 0){
            self::$errorFlashes[] = Yii::t(
                'app', 'You were not authorized to delete {n} record|You were not authorized to delete {n} records', array($unauthorized)
            );
        } 
        return $updatedRecordsNum;
    }

}
