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




if (!isset ($data)) $data = array ();
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

<?php
if ($report === null) {
?>
<a id="report-settings-save-button" class="x2-button">
   <?php echo CHtml::encode (Yii::t('reports', 'Save Report')); ?>
</a>
<a id="quick-create-list" class="x2-button">
   <?php echo CHtml::encode (Yii::t('reports', 'Create Contact List')); ?>
</a>
<?php
} else {
?>
 <span id="minimize-button" 
  title="<?php echo CHtml::encode (Yii::t('reports', 'Minimize Settings')); ?> "
  class="minimize"><i class='fa fa-lg fa-caret-down'></i>
  </span>
  <a id="report-update-button" class="x2-hint x2-button"  
 title="<?php echo CHtml::encode (Yii::t('reports', 'Save changes')); ?>">
   <?php 
   echo X2Html::fa('fa-save').' ';
   echo CHtml::encode (Yii::t('reports', 'Save')); 
   ?>
</a>
<a id="report-copy-button" 
 title="<?php echo CHtml::encode (Yii::t('reports', 'Copy this report')); ?> "
 class="x2-hint x2-button">
   <?php 
   echo X2Html::fa('fa-copy').' ';
   echo CHtml::encode (Yii::t('reports', 'Copy')); 
   ?>
 </a>

<?php
}
?>

<div class="x2-button-group">
  <a title="<?php echo Yii::t('app', 'Export to CSV') ?>"
    class="x2-hint x2-button report-export-button"><?php echo X2Html::fa('fa-download fa-lg') ?></a><a title="<?php echo Yii::t('app', 'Print Report') ?>"
    class="x2-hint x2-button report-print-button"><?php echo X2Html::fa('fa-print fa-lg') ?></a><a title="<?php echo Yii::t('app', 'Email Report') ?>"
    class="x2-hint x2-button report-email-button"><?php echo X2Html::fa('fa-envelope fa-lg') ?></a>
</div>

</div>
<?php
$this->renderPartial (
    $type.'Report', array_merge ($data, array (
        'formModel' => $formModel
    )));

if($report !== null) { 
  $this->widget('ChartDashboard', array(
          'report' => $report
      )
  );
}
?>

<div id='report-container' class='x2-layout-island' style='display: none;'>
</div>

<?php
$this->widget('InlineEmailForm',
    array(
        'startHidden'=>true,
    )
);

?>
<div id='report-settings-dialog' class='form' style='display: none;'>
    <label class='left-label' for='report-settings-name'><?php 
        echo CHtml::encode (Yii::t('reports', 'Report Name: ')); ?></label>
    <input id='report-settings-name' type='text' name='reportSettingsName' />
</div>

<?php 

if ($report !== null) {
    $this->widget('ChartCreator', array(
            'report' => $report,
            'autoOpen' => isset($_GET['chart'])
        )
    );
}
?>
