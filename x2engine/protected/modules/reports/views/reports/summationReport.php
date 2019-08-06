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






Yii::app()->clientScript->registerCssFiles('SummationReportCss', array(
    Yii::app()->controller->module->getAssetsUrl() . '/css/gridReportsGridView.css',
    Yii::app()->controller->module->getAssetsUrl() . '/css/summationReport.css',
    Yii::app()->theme->baseUrl . '/css/gridview/styles.css'), false);

Yii::app()->clientScript->registerCss('gridReportCSS',"
#content {
    border: 1px solid #e0e0e0 !important;
}
");

Yii::app()->clientScript->registerScript('automaticallyGenerateReport', "
$(function() {
    $('#x2-generate-report-button').click();
});
", CClientScript::POS_READY);
?>

<div id='content-container-inner'>
    <div class='form form2'>
        <?php
        $attributeOptions = X2Model::model($formModel->primaryModelType)
                ->getFieldsForDropdown(true, false);
        $aggregateFieldOptions = $formModel->getAggregateFieldOptions();
        $groupsOrderFieldOptions = $formModel->addAggregatesToFieldOptions(
                $aggregateFieldOptions, false, true);
        $groupsFilterFieldOptions = $formModel->addAggregatesToFieldOptions(
                $formModel->getAggregateFieldOptions(true), true);
        $form = $this->beginWidget('SummationReportForm', array(
            'reportContainerId' => 'report-container',
            'formModel' => $formModel,
        ));
        echo $form->errorSummary($formModel);
        echo $form->label($formModel, 'primaryModelType');
        echo $form->primaryModelTypeDropDown($formModel);
        ?>
        <br/>
        <br/>
        <?php
        echo $form->label($formModel, 'groupsAllFilters');
        echo $form->filterConditionList(
                $formModel, 'groupsAllFilters', array(), $groupsFilterFieldOptions);
        ?> 
        <br/>
        <?php
        echo $form->label($formModel, 'groupsAnyFilters');
        echo $form->filterConditionList(
                $formModel, 'groupsAnyFilters', array(), $groupsFilterFieldOptions);
        ?>
        <br/>
        <?php
        echo $form->label($formModel, 'groups');
        echo $form->sortByAttrPillBox($formModel, 'groups', $attributeOptions, array(
            'id' => 'group-by-pill-box',
        ));
        ?>
        <br/>
        <?php
        echo $form->label($formModel, 'aggregates');
        echo $form->aggregatesPillBox(
                $formModel, 'aggregates', $aggregateFieldOptions, array(
            'id' => 'aggregates-pill-box',
        ));
        ?>
        <br/>
        <?php
        echo $form->label($formModel, 'groupsOrderBy');
        echo $form->sortByAttrPillBox(
                $formModel, 'groupsOrderBy', $groupsOrderFieldOptions, array(
            'id' => 'group-order-by-pill-box',
        ));
        ?>
        <br/>
        <?php
        echo $form->checkBox($formModel, 'includeTotalsRow');
        echo $form->label($formModel, 'includeTotalsRow', array(
            'class' => 'right-label',
        ));
        ?>
        <br/>
        <div id='drill-down-section' class='x2-collapsible-outer collapsed'>
            <div class='x2-collapse-handle'>
                <h3><?php echo CHtml::encode(Yii::t('reports', 'Drill Down')); ?></h3>
                <span class='x2-collapse-button fa fa-caret-left right'></span>
                <span class='x2-expand-button fa fa-caret-down right' style='display: none;'></span>
            </div>
            <div class='x2-collapsible' style='display: none;'>
                <br/>
<?php
echo $form->label($formModel, 'allFilters');
echo $form->filterConditionList($formModel, 'allFilters');
?> 
                <br/>
                <?php
                echo $form->label($formModel, 'anyFilters');
                echo $form->filterConditionList($formModel, 'anyFilters');
                ?>
                <br/>
                <?php
                echo $form->label($formModel, 'drillDownColumns');
                echo $form->attributePillBox($formModel, 'drillDownColumns', $attributeOptions);
                ?>
                <br/>
                <?php
                echo $form->label($formModel, 'orderBy');
                echo $form->sortByAttrPillBox($formModel, 'orderBy', $attributeOptions, array(
                    'id' => 'order-by-pill-box',
                ));
                ?>
            </div>
        </div>
        <br/>
        <br/>
<?php
echo $form->generateReportButton();
$this->endWidget();
?>
    </div>
</div>


