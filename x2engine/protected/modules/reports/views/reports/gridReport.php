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






Yii::app()->clientScript->registerCssFile(Yii::app()->controller->module->getAssetsUrl ().
    '/css/gridReportsGridView.css');
Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/gridview/styles.css');

Yii::app()->clientScript->registerCss('gridReportCSS',"
#content {
    background: none !important;
     border: none !important;
}
#report-container {
    margin-top: 5px;
}
");

Yii::app()->clientScript->registerScript('automaticallyGenerateReport', "
$(function() {
    $('#x2-generate-report-button').click();
});
", CClientScript::POS_READY);
// render grid report settings (start closed if report is being generated, open otherwise)

?>
<div id='content-container-inner'>
<div class='form'>
<?php
$form = $this->beginWidget ('GridReportForm', array (
    'reportContainerId' => 'report-container',
    'formModel' => $formModel,
));
    echo $form->errorSummary ($formModel);
    echo $form->label ($formModel, 'primaryModelType');
    echo $form->primaryModelTypeDropDown ($formModel);
    ?>
    <div class='bs-row'>
        <span class='left'>
        <?php
        echo $form->label ($formModel, 'rowField');
        echo $form->fieldDropdown ($formModel, 'rowField', $fieldOptions);
        ?>
        </span>
    </div>
    <div class='bs-row'>
        <span class='left'>
        <?php
        echo $form->label ($formModel, 'columnField');
        echo $form->fieldDropdown ($formModel, 'columnField', $fieldOptions);
        ?>
        </span>
    </div>
    <?php
    echo $form->label ($formModel, 'cellDataType');
    echo $form->dropDownList(
        $formModel, 'cellDataType', 
        array(
            'count' => Yii::t('reports', 'Count'),
            'sum' => Yii::t('reports', 'Sum'),
            'avg' => Yii::t('reports','Average')
        ), array (
            'id' => 'cell-data-type',
        ));
    ?>
    <div id='cell-data-field-container' <?php 
     echo (empty ($formModel->cellDataType) || $formModel->cellDataType === 'count' ? 
        'style="display: none"' : ''); ?>>
        <label><?php echo Yii::t('reports','Cell Data Field');?></label>
        <?php
        echo $form->dropDownList(
            $formModel, 'cellDataField', $cellDataFieldOptions);
        ?>
    </div>
    <br/>
    <br/>
    <?php
    echo $form->label ($formModel, 'allFilters');
    echo $form->filterConditionList ($formModel, 'allFilters');
    ?> 
    <br/>
    <?php
    echo $form->label ($formModel, 'anyFilters');
    echo $form->filterConditionList ($formModel, 'anyFilters');
    ?>
    <br/>
    <div id="quick-create-list-form" style="display:none"></div>
    <?php
    echo $form->generateReportButton ();
$this->endWidget ();
?>
</div>
</div>
<!-- <div id='report-container' class='x2-layout-island' style='display: none;'> -->
<!-- </div> -->
