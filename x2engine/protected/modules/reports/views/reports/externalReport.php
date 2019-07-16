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




if (!isset ($report)) $report = null;

if ($report) $this->setPageTitle($report->name);

Yii::app()->clientScript->registerCssFile(Yii::app()->controller->module->getAssetsUrl ().
    '/css/reports.css');
Yii::app()->clientScript->registerX2Flashes ();

$this->insertMenu(true, $report);
?>

<div class="reports-page-title reports icon page-title">
<h2>
<?php
if ($report === null) {
    echo CHtml::encode (Yii::t('reports', '{type} Report',array(
        '{type}'=>Reports::prettyType ($type),
    )));
} else {
    echo CHtml::encode ($report->name);
}
?>
</h2>
<?php if ($report) { ?>
      <a id="report-update-button" class="x2-hint x2-button"
     title="<?php echo CHtml::encode (Yii::t('reports', 'Save Changes')); ?>">
       <?php
       echo X2Html::fa('fa-save').' ';
       echo CHtml::encode (Yii::t('reports', 'Save'));
       ?>
    </a>
<?php } else { ?>
        <a id="report-settings-save-button" class="x2-button">
           <?php echo CHtml::encode (Yii::t('reports', 'Save Report')); ?>
        </a>
<?php } ?>
</div>

<div id='content-container-inner'>

    <div class='form form2'>
        <p>
        <?php
        echo Yii::t('reports', 'Please supply the full path to the report on the Jasper Server, '.
            'e.g., /Reports/MyReport. This can be found by right clicking on your report in the '.
            'Jasper Server and selecting "Properties."');
        ?></p><?php

        $form = $this->beginWidget ('X2ReportForm', array (
            'reportContainerId' => 'report-container',
            'formModel' => $formModel,
        ));

        echo $form->label ($formModel, 'reportPath');
        echo $form->textfield ($formModel, 'reportPath');

        $this->endWidget ();
        ?>
    </div>

    <?php if ($report !== null && $reportPath !== null) {
        $externalUrl = Reports::getExternalReportUrl($reportPath);
        if ($externalUrl) {
        ?>
            <iframe src="<?php echo $externalUrl; ?>" width="100%" height="800px"></iframe>
        <?php } else { ?>
            <p>
                <?php echo Yii::t('reports', 'Failed to load external report. Please check your Jasper Server credential details.'); ?>
            <p>
        <?php } ?>
    <?php } ?>
    </div>

    <div id='report-settings-dialog' class='form' style='display: none;'>
        <label class='left-label' for='report-settings-name'><?php
            echo CHtml::encode (Yii::t('reports', 'Report Name: ')); ?></label>
        <input id='report-settings-name' type='text' name='reportSettingsName' />
    </div>
</div>
