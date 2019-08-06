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




class X2ReportForm extends X2ActiveForm {

    /**
     * @var $id
     */
    public $id = 'report-settings'; 

    /**
     * Id of report container element
     * @var string $reportContainerSelector
     */
    public $reportContainerId = 'report-container'; 

    /**
     * @var string $JSClass 
     */
    public $JSClass = 'ReportForm'; 

    protected $_packages;

    /**
     * @var string $_primaryModelTypeDropdownId
     */
    private $_primaryModelTypeDropdownId = 'primary-model-type'; 

    public function getJSObjectName () {
        return "x2.".$this->namespace.'reportForm';
    }

    /**
     * @param array 
     */
    public function getJSClassParams () {
        return array_merge(parent::getJSClassParams (), array(
            'reportContainerSelector' => '#'.$this->reportContainerId,
            'settingsFormSelector' => '#'.$this->id,
            'primaryModelTypeDropDownSelector' => '#'.$this->_primaryModelTypeDropdownId,
            'type' => $this->formModel->getReportType (),
            'translations' => array (
                'savedSettingsDialogTitle' => Yii::t('reports', 'Save Report'),
                'copyReportDialogTitle' => Yii::t('reports', 'Copy Report'),
                'cancel' => Yii::t('reports', 'Cancel'),
                'saveButton' => Yii::t('reports', 'Save'),
                'copy' => Yii::t('reports', 'Copy'),
                'proceedAnyway' => Yii::t('reports', 'Proceed Anyway'),
                'unsavedSettingsWarning' => 
                    Yii::t('reports', 'You have unsaved report settings which will be lost.'),
                ),
            )
        );
    }

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages(), array(
                'X2ReportFormJS' => array(
                    'baseUrl' => Yii::app()->controller->module->assetsUrl,
                    'js' => array(
                        'js/X2ReportForm.js',
                    ),
                    'depends' => array ('X2FormJS'),
                ),
            ));
        }
        return $this->_packages;
    }

    /**
     * @param CModel $model 
     */
    public function primaryModelTypeDropDown (CModel $model) {
        $criteria = new CDbCriteria;
        $criteria->addCondition ('name="actions"', 'OR');
        $primaryModelNames = X2Model::getModelNames ($criteria);
        return $this->dropDownList ($model, 'primaryModelType', $primaryModelNames, array (
            'id' => $this->_primaryModelTypeDropdownId
        ));
    }

    /**
     * @param CModel $formModel
     * @param string $name name of input
     * @param X2Model $model used to populate condition list attribute options
     */
    public function filterConditionList (
        CModel $formModel, $name, array $htmlOptions = array (), $attributes=null) {
        CHtml::resolveNameID ($formModel, $name, $htmlOptions);

        $primaryModelType = $formModel->primaryModelType;
        $primaryModel = $primaryModelType::model ();
        $value = $formModel->$name;
        foreach ($value as &$val) {
            list ($model, $attr, $fns, $linkField) = $formModel->getModelAndAttr ($val['name']);
            $field = $model->getField ($val['name']);
            if ($field) {
                if ($field->type === 'date') {
                    $val['value'] = Formatter::formatDate ($val['value'], 'medium');
                } elseif ($field->type === 'dateTime') {
                    $val['value'] = Formatter::formatDateTime  ($val['value']);
                } 
            }
        }

        return $this->widget ('X2ConditionList', array (
            'id' => $htmlOptions['id'],
            'name' => $htmlOptions['name'],
            'value' => $value,
            'model' => X2Model::model ($formModel->primaryModelType),
            'useLinkedModels' => true,
            'attributes' => $attributes,
        ), true);
    }

    /**
     * @param CModel $formModel
     * @param string $name name of input
     * @param array $options attribute options for pill box dropdown
     */
    public function attributePillBox (CModel $formModel, $name, array $options,
        array $htmlOptions = array ()) {

        if ($formModel->hasErrors($name)) X2Html::addErrorCss ($htmlOptions);

        CHtml::resolveNameID ($formModel, $name, $htmlOptions);
        return $this->widget ('X2PillBox', array (
            'id' => $htmlOptions['id'],
            'name' => $htmlOptions['name'],
            'htmlOptions' => $htmlOptions,
            'optionsHeader' => Yii::t('reports', 'Select an attribute:'),
            'value' => $formModel->$name,
            'translations' => array (
                'delete' => Yii::t('reports', 'Delete attribute'),
            ),
            'options' => $options,
        ), true);
    }

    /**
     * @param CModel $formModel
     * @param string $name name of input
     * @param array $options attribute options for pill box dropdown
     */
    public function sortByAttrPillBox (CModel $formModel, $name, array $options,
        array $htmlOptions = array ()) {

        if ($formModel->hasErrors($name)) X2Html::addErrorCss ($htmlOptions);

        CHtml::resolveNameID ($formModel, $name, $htmlOptions);
        return $this->widget ('SortByPillBox', array (
            'id' => $htmlOptions['id'],
            'name' => $htmlOptions['name'],
            'optionsHeader' => Yii::t('reports', 'Select an attribute:'),
            'value' => $formModel->$name,
            'htmlOptions' => $htmlOptions,
            'translations' => array (
                'delete' => Yii::t('reports', 'Delete attribute'),
                'ascending' => Yii::t('reports', 'ascending'),
                'descending' => Yii::t('reports', 'descending'),
            ),
            'options' => $options,
        ), true);
    }

    /**
     * Renders report generation submit button 
     */
    public function generateReportButton () {
        echo "<button type='submit' id='x2-generate-report-button' class='x2-button'>".
            Yii::t('reports', 'Generate')."</button>";
    }


}

?>
