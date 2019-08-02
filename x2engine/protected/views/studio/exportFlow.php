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






Yii::app()->clientScript->registerCss('importFlowCss',"

#export-how-to,
#export-how-to + div {
    margin-bottom: 10px;
}


");

Yii::app()->clientScript->registerScript('importFlowJS',"

$(function () {

$('#download-link').click(function(e) {
    e.preventDefault();  //stop the browser from following
    window.location.href = '".$this->createUrl(
        '/admin/downloadData',array('file'=>$_SESSION['flowExportFile']))."';
});

});
");

$this->actionMenu = array(
	array('label'=>Yii::t('studio','Manage Workflows'), 'url' => array ('flowIndex')),
	array(
        'label'=>Yii::t('studio','Create Workflow'),
        'url'=>array('flowDesigner'),
        'visible'=>Yii::app()->contEd('pro'),
    ),
    array (
        'label' => Yii::t('studio', 'All Trigger Logs'),
        'url' => array ('triggerLogs'),
        'visible' => Yii::app()->contEd('pro')
    ),
    array (
        'label' => Yii::t('studio', 'Import Workflow'), 
        'url' => array ('importFlow'),
    ),
    array (
        'label' => Yii::t('studio', 'Export Workflow'),
    )
);

?>
<div class="page-title icon x2flow">
    <h2><?php echo Yii::t('studio', 'Export Workflow'); ?></h2>
</div>
<form>
<div class='form'>
    <div id='export-how-to'>
    <?php
    echo Yii::t('studio', 'Please click the button below to begin the export. Do not close this page until the export is finished.');
    ?>
    </div>
    <div>
    <?php
    //printR($flow, true);
    echo Yii::t('studio', 'You are currently exporting ').CHtml::link (CHtml::encode ($flow->name), $this->createUrl ('/studio/flowDesigner', array ('id' => $flow->id)));
    ?>
    </div>
    <input type='hidden' name='export' value='1'>
    <input type='hidden' name='flowId' value='<?php echo $flow->id; ?>'>
    <?php
    echo CHtml::submitButton(
        Yii::t('app','Export'), 
        array(
            'id' => 'export-flow', 
            'class' => 'x2-button',
        )
    );
    ?>
    <div style="<?php echo $download ? '' : 'display:none'; ?>" id="download-link-box">
        <?php echo Yii::t('studio','Please click the link below to download the exported flow.');?><br><br>
        <a class="x2-button" id="download-link" href="#"><?php echo Yii::t('app','Download');?>!</a>
    </div>
</div>
</form>
