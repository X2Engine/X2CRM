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






class MassAddRelationship extends MassAction {

    /**
     * Renders the mass action dialog, if applicable
     * @param string $gridId id of grid view
     */
    public function renderDialog ($gridId, $modelName) {
        echo "
            <div class='mass-action-dialog form' 
            id='".$this->getDialogId ($gridId)."' style='display: none;'>
                <span class='dialog-help-text'>".
                    Yii::t('app', 'Add a relationship from each of the selected {records} '.
                        'to the following record: ', 
                        array ('{records}' => lcfirst ($this->getModelDisplayName ())))."
                </span><br/>";
        echo $this->renderForm ();
        echo "
            </div>";
    }

    /**
     * @return string label to display in the dropdown list
     */
    public function getLabel () {
        if (!isset ($this->_label)) {
            $this->_label = Yii::t('app', 'Add relationships', array (
            ));
        }
        return $this->_label;
    }

    public function getPackages () {
        return array_merge (parent::getPackages (), array (
            'MassAddRelationshipJS' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/X2GridView/MassAddRelationship.js',
                ),
                'depends' => array ('X2MassAction'),
            ),
        ));
    }

    public function execute (array $gvSelection) {
        $formModel = $this->getFormModel ();
        $association = $formModel->getAssociation ();
        if (!Yii::app()->controller->checkPermissions ($association, 'view')) $this->denied ();

        $relationshipsAdded = 0;
        $staticModel = X2Model::model (Yii::app()->controller->modelClass);
        foreach ($gvSelection as $recordId) {
            $model = $staticModel->findByPk ($recordId);
            if ($model === null || !Yii::app()->controller->checkPermissions ($model, 'edit')) {
                self::$noticeFlashes[] = Yii::t(
                    'app', 'Relationship could not be added to record {recordId}', array (
                        '{recordId}' => $recordId
                    )
                ).($model === null ? 
                    Yii::t('app','The record could not be found.') : 
                    Yii::t('app','You do not have sufficient permissions.'));
                continue;
            }
            
            $result = $association->createRelationship($model);
            if (is_array($result)) {
                self::$noticeFlashes[] = Yii::t(
                    'app', 'Relationship could not be saved for record {recordId}: {errors}', 
                        array (
                            '{recordId}' => $recordId,
                            '{errors}' => implode (' ', $result),
                        )
                );
            } else if($result) {
                $relationshipsAdded++;
            }
        }

        if ($relationshipsAdded > 0) {
            self::$successFlashes[] = Yii::t(
                'app', '{relationshipsAdded} relationships'.($relationshipsAdded === 1 ? '' : 's').
                    ' added', array ('{relationshipsAdded}' => $relationshipsAdded)
            );
        }

        return $relationshipsAdded;
    }

    protected function renderForm ($return=true) {
        $formModel = $this->getFormModel ();
        if ($return) ob_start ();
        $form = Yii::app()->controller->beginWidget ('X2ActiveForm', array (
            'instantiateJSClassOnInit' => false
        ));
        $formModel->clearErrors ('associationId');
        echo $form->errorSummary ($formModel);
        echo $form->multiTypeAutocomplete (
            $formModel, 'associationType', 'associationId', 
            Relationships::getRelationshipTypeOptions ());
        Yii::app()->controller->endWidget ();
        if ($return) {
            $html = ob_get_contents ();
            ob_end_clean ();
            return $html;
        }
    }

}

/**
 * Used to validate dialog form  
 */
class MassAddRelationshipFormModel extends MassActionFormModel {
    public $associationType = 'Contacts'; 
    public $associationId; 
    public $associationName; // used for error summary only

    private $_association;
    public function getAssociation () {
        if (!isset ($this->_association)) {
            $associationType = $this->associationType;
            $this->_association = $associationType::model ()->findByPk ($this->associationId);
        }
        return $this->_association;
    }

    public function rules () {
        return array (
            array (
                'associationType, associationId', 'required'
            ),
            array (
                'associationType', 'validateAssociationType'
            ),
            array (
                'associationId', 'numerical', 'integerOnly' => true
            ),
            array (
                'associationId', 'validateAssociationId'
            ),
        );
    }

    public function validateAssociationType ($attr) {
        $value = $this->$attr;
        if (!is_subclass_of ($value, 'X2Model')) {
            throw new CException ('invalid association type');
        }
    }

    public function validateAssociationId ($attr) {
        if (!$this->getAssociation ()) { 
            $this->addError ('associationName', Yii::t('app', 'Record not found' ));
        }
    }
}

