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






class MassConvertRecord extends MassAction {

    protected $_label;

    public $conversionTargets = array (
        'Contacts',
        'Opportunity'
    );

    /**
     * Renders the mass action dialog, if applicable
     * @param string $gridId id of grid view
     */
    public function renderDialog ($gridId, $modelName) {
        $conversionTargets = $this->conversionTargets;
        echo "
            <div class='mass-action-dialog form' 
            id='".$this->getDialogId ($gridId)."' style='display: none;'>
                <span class='dialog-help-text'>".
                    Yii::t('app', 'Choose a type to which to convert the selected {records}', 
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
            $this->_label = Yii::t('app', 'Convert selected', array (
            ));
        }
        return $this->_label;
    }

    public function getPackages () {
        return array_merge (parent::getPackages (), array (
            'MassConvertRecordJS' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/X2GridView/MassConvertRecord.js',
                ),
                'depends' => array ('X2MassAction'),
            ),
        ));
    }

    public function execute (array $gvSelection) {
        $sourceClass = $this->getModelClass ();
        $sourceModel = $sourceClass::model ();

        if (!$sourceModel->asa ('ModelConversionBehavior')) {
            throw new CException ('invalid model type');
        }

        $convertedRecordsNum = 0;
        $formModel = $this->getFormModel ();
        foreach ($gvSelection as $recordId) {
            $model = $sourceModel->findByPk ($recordId);
            if ($model === null || !Yii::app()->controller->checkPermissions ($model, 'edit')) {
                self::$noticeFlashes[] = Yii::t(
                    'app', 'Record {recordId} could not be converted.', array (
                        '{recordId}' => $recordId
                    )
                ).($model === null ? 
                    Yii::t('app','The record could not be found.') : 
                    Yii::t('app','You do not have sufficient permissions.'));
                continue;
            }
            $targetModel = $model->convert ($formModel->conversionTargetType, $formModel->force);
            if ($targetModel->hasErrors ()) {
                $errors = $targetModel->getAllErrorMessages();
                foreach ($errors as $err) {
                    self::$noticeFlashes[] = Yii::t(
                        'app', 'Record {recordId} could not be converted: '.$err,
                        array ('{recordId}' => $recordId)
                    );
                }
            } else {
                self::$successFlashes[] = array (
                    'message' => Yii::t(
                        'app', 'Record {link} converted', array (
                            '{link}' => $targetModel->link
                        )),
                    'encode' => false
                );
                $convertedRecordsNum++;
            }
        }

        if ($convertedRecordsNum > 0) {
            self::$successFlashes['header'] =  Yii::t(
                'app', '{convertedRecordsNum} record'.($convertedRecordsNum === 1 ? '' : 's').
                    ' converted', array ('{convertedRecordsNum}' => $convertedRecordsNum)
            );
            self::$successFlashes['fade'] = 0;
        }

        return $convertedRecordsNum;
    }

    protected function renderForm ($return=true) {
        $formModel = $this->getFormModel ();
        if ($return) ob_start ();
        $form = Yii::app()->controller->beginWidget ('CActiveForm', array (
        ));
        echo $form->dropDownList (
            $formModel, 'conversionTargetType',
            $this->getConversionTargetsOptions ()
        );
        if ($formModel->hasErrors ('conversionTargetType')) {
            echo '<br/>';
            $sourceClass = $this->getModelClass ();
            $sourceModel = $sourceClass::model ();
            echo $sourceModel->asa ('ModelConversionBehavior')->errorSummary (
                $formModel->conversionTargetType, true);
        }
        echo $form->checkBox ($formModel, 'force', array (
            'style' => 'display: none;',
        ));
        Yii::app()->controller->endWidget ();
        if ($return) {
            $html = ob_get_contents ();
            ob_end_clean ();
            return $html;
        }
    }

    private function getConversionTargetsOptions () {
        $conversionTargets = $this->conversionTargets;
        return array_combine (
            $conversionTargets,
            array_map (function ($target) use ($conversionTargets) {
                return $target::model ()->getDisplayName (false);
            }, $conversionTargets));
    }

}

/**
 * Used to validate dialog form  
 */
class MassConvertRecordFormModel extends MassActionFormModel {
    public $force = false; 
    public $conversionTargetType; 

    public function rules () {
        return array (
            array (
                'force', 'boolean'
            ),
            array (
                'force, conversionTargetType', 'required'
            ),
            array (
                'conversionTargetType', 'validateConversionTargetType'
            ),
        );
    }

    /**
     * Ensure that conversion target type is valid and that source and target are conversion  
     * compatible
     */
    public function validateConversionTargetType ($attr) {
        $value = $this->$attr;
        if (!in_array ($value, $this->massAction->conversionTargets)) {
            throw new CException ('Invalid conversion type');
        }

        $sourceClass = $this->massAction->getModelClass ();
        $sourceModel = $sourceClass::model ();
        if (!$this->force && !$sourceModel->checkConversionCompatibility ($value)) {
            $this->addError ($attr, ''); 
        }
    }
}

