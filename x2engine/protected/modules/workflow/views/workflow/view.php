<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/
$user=isset($_GET['users'])?$_GET['users']:''; 
Yii::app()->clientScript->registerScript('getWorkflowStage',"

function getStageMembers(stage) {
	$.ajax({
		url: '" . CHtml::normalizeUrl(array('/workflow/workflow/getStageMembers')) . "',
		type: 'GET',
		data: 'workflowId=".$model->id."&stage='+stage+'&modelId=".$model->id."&type=contacts&start=".$this->formatDate($dateRange['start'])."&end=".$this->formatDate($dateRange['end'])."&range=".$dateRange['range']."&user=".$user."',
		success: function(response) {
			if(response!='')
				$('#workflow-gridview').html(response);
            $.ajax({
                url: '" . CHtml::normalizeUrl(array('/workflow/workflow/getStageValue')) . "',
                data: 'workflowId=".$model->id."&stageId='+stage+'"."&user=".$user."',
                success: function(response) {
                    $('#data-summary-box').html(response);
                }
            });
		}
	});
}
",CClientScript::POS_HEAD);
$isAdmin = (Yii::app()->user->checkAccess('AdminIndex'));
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('workflow','All Workflows'), 'url'=>array('index')),
	array('label'=>Yii::t('app','Create'), 'url'=>array('create'), 'visible'=>$isAdmin),
	array('label'=>Yii::t('app','View')),
	array('label'=>Yii::t('workflow','Edit Workflow'), 'url'=>array('update', 'id'=>$model->id), 'visible'=>$isAdmin),
	array('label'=>Yii::t('workflow','Delete Workflow'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('app','Are you sure you want to delete this item?')), 'visible'=>$isAdmin),
));

?>

<h2><?php echo Yii::t('workflow','Workflow:'); ?> <b><?php echo $model->name; ?></b></h2>
<div style="margin-bottom:30px;width:300px;float:left;">
<?php

$workflowStatus = Workflow::getWorkflowStatus($model->id);	// true = include dropdowns
echo Workflow::renderWorkflowStats($workflowStatus);
?>
</div>

<div class="form" style="clear:none;">
	<h2><?php echo Yii::t('workflow', 'Workflow Status'); ?></h2>
	<?php $form = $this->beginWidget('CActiveForm', array(
		'action'=>'view',
		'id'=>'dateRangeForm',
		'enableAjaxValidation'=>false,
		'method'=>'get',
        'htmlOptions'=>array(
            'style'=>'width:400px;float:left;'
        )
	)); ?>
	<div class="row">
		<div class="cell">
			<?php echo CHtml::label(Yii::t('charts', 'Start Date'),'startDate'); ?>
			<?php
			Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
			
			$this->widget('CJuiDateTimePicker',array(
				'name'=>'start',
				// 'value'=>$startDate,
				'value'=>$this->formatDate($dateRange['start']),
				// 'title'=>Yii::t('app','Start Date'),
				// 'model'=>$model, //Model object
				// 'attribute'=>$field->fieldName, //attribute name
				'mode'=>'date', //use "time","date" or "datetime" (default)
				'options'=>array(
					'dateFormat'=>$this->formatDatePicker(),
					'changeMonth'=>true,
					'changeYear'=>true,

				), // jquery plugin options
				'htmlOptions'=>array('id'=>'startDate','width'=>20),
				'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
			));
			?>
		</div>
		<div class="cell">
			<?php echo CHtml::label(Yii::t('charts', 'End Date'),'startDate'); ?>
			<?php
			$this->widget('CJuiDateTimePicker',array(
				'name'=>'end',
				'value'=>$this->formatDate($dateRange['end']),
				// 'value'=>$endDate,
				'mode'=>'date', //use "time","date" or "datetime" (default)
				'options'=>array(
					'dateFormat'=>$this->formatDatePicker(),
					'changeMonth'=>true,
					'changeYear'=>true,
				),
				'htmlOptions'=>array('id'=>'endDate','width'=>20),
				'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
			));
			?>
		</div>
		<div class="cell">
			<?php echo CHtml::label(Yii::t('charts', 'Date Range'),'range'); ?>
			<?php
			echo CHtml::dropDownList('range',$dateRange['range'],array(
				'custom'=>Yii::t('charts','Custom'),
				'thisWeek'=>Yii::t('charts','This Week'),
				'thisMonth'=>Yii::t('charts','This Month'),
				'lastWeek'=>Yii::t('charts','Last Week'),
				'lastMonth'=>Yii::t('charts','Last Month'),
				// 'lastQuarter'=>Yii::t('charts','Last Quarter'),
				'thisYear'=>Yii::t('charts','This Year'),
				'lastYear'=>Yii::t('charts','Last Year'),
								'all'=>Yii::t('charts','All Time'),
				
			),array('id'=>'dateRange'));
			?>
		</div>
	</div>
	<div class="row">
        <div class="cell">
            <?php echo CHtml::label(Yii::t('workflow','User'), 'users');?>
            <?php echo CHtml::dropDownList('users',$user,array_merge(array(''=>Yii::t('app','All')),User::getNames())); ?>
        </div>
        <?php echo CHtml::hiddenField('id',$model->id); ?>
		<div class="cell">
			<?php echo CHtml::submitButton(Yii::t('charts','Go'),array('name'=>'','class'=>'x2-button','style'=>'margin-top:13px;')); ?>
		</div>
	</div>
	<?php $this->endWidget();?>
    <div id="data-summary-box" style="float:right;">
        
    </div>
</div>

<div id="workflow-gridview">
<?php
if(isset($viewStage)){
	echo Yii::app()->controller->actionGetStageMembers($model->id,$viewStage,$this->formatDate($dateRange['start']),$this->formatDate($dateRange['end']),$dateRange['range'],$user);
}else {
$this->widget('zii.widgets.grid.CGridView', array(
	// 'id'=>'docs-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '{items}{pager}',
	'dataProvider'=>CActiveRecord::model('WorkflowStage')->search($model->id),
	// 'filter'=>$model,
	'columns'=>array(
		array(
			'name'=>'stageNumber',
			'header'=>'#',
			'headerHtmlOptions'=>array('style'=>'width:8%;'),
		),
		array(
			'name'=>'name',
			// 'value'=>'CHtml::link($data->title,array("view","id"=>$data->name))',
			'type'=>'raw',
			// 'htmlOptions'=>array('width'=>'30%'),
		),
		array(
			'name'=>'requirePrevious',
			'value'=>'Yii::t("app",($data->requirePrevious? "Yes" : "No"))',
			'type'=>'raw',
			'headerHtmlOptions'=>array('style'=>'width:15%;'),
		),
		array(
			'name'=>'requireComment',
			'value'=>'Yii::t("app",($data->requireComment? "Yes" : "No"))',
			'type'=>'raw',
			'headerHtmlOptions'=>array('style'=>'width:15%;'),
		),
		array(
			'name'=>'conversionRate',
			// 'value'=>'User::getUserLinks($data->createdBy)',
			// 'type'=>'raw',
			'headerHtmlOptions'=>array('style'=>'width:15%;'),
		),
		array(
			'name'=>'value',
			// 'value'=>'User::getUserLinks($data->createdBy)',
			// 'type'=>'raw',
			'headerHtmlOptions'=>array('style'=>'width:15%;'),
		),
	),
));
}
?>
</div>


