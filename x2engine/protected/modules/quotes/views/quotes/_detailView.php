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

Yii::app()->clientScript->registerScript('updateWorkflow',"

function startWorkflowStage(workflowId,stageNumber) {
	$.ajax({
		url: '" . CHtml::normalizeUrl(array('workflow/startStage')) . "',
		type: 'GET',
		data: 'workflowId='+workflowId+'&stageNumber='+stageNumber+'&modelId=".$model->id."&type=quotes',
		success: function(response) {
			if(response!='')
				$('#workflow-diagram').html(response);
		}
	});
}

function completeWorkflowStage(workflowId,stageNumber) {
	$.ajax({
		url: '" . CHtml::normalizeUrl(array('workflow/completeStage')) . "',
		type: 'GET',
		data: 'workflowId='+workflowId+'&stageNumber='+stageNumber+'&modelId=".$model->id."&type=quotes',
		success: function(response) {
			if(response!='')
				$('#workflow-diagram').html(response);
		}
	});
}

function revertWorkflowStage(workflowId,stageNumber) {
	$.ajax({
		url: '" . CHtml::normalizeUrl(array('workflow/revertStage')) . "',
		type: 'GET',
		data: 'workflowId='+workflowId+'&stageNumber='+stageNumber+'&modelId=".$model->id."&type=quotes',
		success: function(response) {
			if(response!='')
				$('#workflow-diagram').html(response);
		}
	});
}
",CClientScript::POS_HEAD);

Yii::app()->clientScript->registerScript('detailVewFields', "
function showField(field,focus){
	// $(field).css('background','red');
	$(field).find('.detail-field').hide();
	$(field).find('.detail-form').show();
	if(focus)
		$(field).find('input').focus();
	highlightSave();
}
function highlightSave() {
	$('#save-changes').css('background','yellow');
}
",CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScript('stopEdit','
	$(document).ready(function(){
		$("td#description a").click(function(e){
			e.stopPropagation();
		});
	});
');

$relationships = Relationships::model()->findAllByAttributes(
	array(
		'firstType'=>'quotes', 
		'firstId'=>$model->id, 
		'secondType'=>'contacts'
	)
);
$associatedContacts = array();
foreach($relationships as $relationship) {
	$contact = X2Model::model('Contacts')->findByPk($relationship->secondId);
	$associatedContacts[] = CHtml::link($contact->name, array('/contacts/view', 'id'=>$contact->id));
}
$associatedContacts = implode(', ', $associatedContacts);

?>


<div class="x2-layout">
	<div class="formSection">
		<div class="formSectionHeader">
			<span class="sectionTitle">Basic Information</span>
		</div>
		<div class="tableWrapper">
			<tbody>
				<tr class="formSectionRow">
					<td style="width:300px">
						<div class="formItem leftLabel">
							<label><?php echo $attributeLabels['name']; ?></label>
							<div class="formInputBox" style="width:200px"><?php echo $model->name; ?></div>
						</div>
					</td>
					<td style="width:293px">
					
					</td>
				</tr>
			</tbody>
		</div>
	</div>
</div>

<div class="form no-border">
<table class="details">
	<tr>
		<td class="label" width="20%"><?php echo $model->getAttributeLabel('name'); ?></td>
		<td colspan="3" id="name" onclick="showField(this,true)">
			<div class="detail-field"><?php echo $model->name; ?></div>
			<div class="detail-form"><?php echo $form->textField($model,'name',array('size'=>48,'maxlength'=>40)); ?></div>
		</td>
	</tr>
	<tr>
		<td class="label" width="20%"><?php echo CHtml::link($model->getAttributeLabel('associatedContacts'),array('addContact', 'id'=>$model->id)); ?></td>
		<td><?php echo $associatedContacts; ?></td>
		<td class="label"><?php echo ($model->accountId==0)? $model->getAttributeLabel('accountName') : CHtml::link($model->getAttributeLabel('accountName'),array('accounts/view','id'=>$model->accountId)); ?></td>
		<td colspan="3" id="accountName" onclick="showField(this,true);">
			<div class="detail-field"><b><?php echo $model->accountName; ?></b></div>
			<div class="detail-form"><?php echo $form->hiddenField($model, 'accountName');
				$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
				'name'=>'companyAutoComplete',
				'value'=>$model->accountName,
				'source' => $this->createUrl('/contacts/getTerms'),
				'htmlOptions'=>array('size'=>35,'maxlength'=>100,'tabindex'=>3),
				'options'=>array(
					'minLength'=>'2',
					'select'=>'js:function( event, ui ) {
						$("#'.CHtml::activeId($model,'accountId').'").val(ui.item.id);
						$(this).val(ui.item.value);
						$("#'.CHtml::activeId($model,'accountName').'").val(ui.item.value);
						return false;
					}',
				),
			));
			echo $form->hiddenField($model, 'accountId');?></div>
		</td>
	</tr>
	<?php $workflowList = Workflow::getList(); ?>
	<tr id="workflow-row">
		<td class="label" width="20%"><?php echo $model->getAttributeLabel('status'); ?></td>
		<td id="status" onclick="showField(this,true);">
			<div class="detail-field"><?php echo Yii::t('quotes',$model->status); ?></div>
			<div class="detail-form"><?php echo $form->dropDownList($model, 'status', Quote::statusList()); ?></div>
		</td>
		<td class="label"><?php echo Yii::t('workflow','Workflow'); ?></td>
		<td colspan="3" id="workflow">
			<div class="detail-field" style="width:170px; text-align:center;margin-bottom:5px;" onclick="showField($('#workflow').get(),false);"><?php echo $workflowList[$currentWorkflow]; ?></div>
			<div class="detail-form" style="width:170px; text-align:center;margin-bottom:5px;">
			<?php
			echo CHtml::dropDownList('workflowId',$currentWorkflow,$workflowList,	//$model->workflow
				array(
					'ajax' => array(
						'type'=>'GET', //request type
						'url'=>CHtml::normalizeUrl(array('workflow/getWorkflow','modelId'=>$model->id,'type'=>'quotes')), //url to call.
						//Style: CController::createUrl('currentController/methodToCall')
						'update'=>'#workflow-diagram', //selector to update
						//'data'=>'js:javascript statement' 
						//leave out the data key to pass all form values through
				))
			); 
			?>
			</div>
			<div id="workflow-diagram">
			<?php
			$workflowStatus = Workflow::getWorkflowStatus($currentWorkflow,$model->id,'quotes');	// true = include dropdowns
			echo Workflow::renderWorkflow($workflowStatus);
		?></div></td>
	</tr>
	<tr>
		<td class="label">
			<?php echo $model->getAttributeLabel('description'); ?>
		</td>
		<td colspan="3" class="text-field" id="description" onclick="showField(this,true)"><div class="spacer"></div>
			<div class="detail-field"><?php echo $this->convertUrls($model->description); 
				// replace any CR or LF characters with <br />, maximum of 2 in a row
			?></div>
			<div class="detail-form"><?php echo $form->textArea($model,'description',array('rows'=>6, 'cols'=>50)); ?></div>
		</td>
	</tr>
	<tr>
		<td class="label" width="20%"><?php echo CHtml::link($model->getAttributeLabel('assignedTo'),array('addUser', 'id'=>$model->id)); ?></td>
		<td><?php echo $model->assignedTo; ?></td>
		<td class="label" width="25%" ><?php echo $model->getAttributeLabel('expirationDate'); ?></td>
		<td id="expirationDate" onclick="showField(this,true);">
			<div class="detail-field">
				<b>
					<?php 
					$model->expirationDate = $this->formatDate($model->expirationDate);
					echo $model->expirationDate;
					?>
				</b>
			</div>
			<div class="detail-form"><?php Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
			$this->widget('CJuiDateTimePicker',array(
				'model'=>$model, //Model object
				'attribute'=>'expirationDate', //attribute name
				'mode'=>'date', //use "time","date" or "datetime" (default)
				'options'=>array(
					'dateFormat'=>$this->formatDatePicker(),
					'changeMonth'=>true,
					'changeYear'=>true,
				), // jquery plugin options
				'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
			));?> </div>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo $model->getAttributeLabel('salesStage'); ?></td>
		<td id="salesStage" onclick="showField(this,true);">
			<div class="detail-field"><b><?php echo Yii::t('quotes',$model->salesStage); ?></b></div>
			<div class="detail-form"><?php echo $form->dropDownList($model,'salesStage',array(
					'Working'=>Yii::t('quotes','Working'),
					'Won'=>Yii::t('quotes','Won'),
					'Lost'=>Yii::t('quotes','Lost'))
				); ?></div>
		</td>
		<td class="label"><?php echo $model->getAttributeLabel('probability'); ?></td>
		<td id="probability" onclick="showField(this,true);">
			<div class="detail-field"><b><?php echo $model->probability; ?></b></div>
			<div class="detail-form"><?php echo $form->textField($model,'probability'); ?></div>
		</td>
	</tr>
	<tr>
		<td class="label">
			<?php echo $model->getAttributeLabel('products'); ?>
		</td>
		<td colspan="3"><div class="spacer"></div>
<?php
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>"quote-products-grid",
	'baseScriptUrl'=>Yii::app()->theme->getBaseUrl().'/css/gridview',
	'summaryText'=>'',
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		array(
			'name'=>'name',
			'header'=>Yii::t('product','Line Item'),
			'value'=>'$data["name"]',
			'type'=>'raw',
		),
		array(
			'name'=>'unit',
			'header'=>Yii::t('product','Unit Price'),
			'value'=>'Yii::app()->locale->numberFormatter->formatCurrency($data["unit"],"'.$model->currency.'")',
			'type'=>'raw',
		),
		array(
			'name'=>'quantity',
			'header'=>Yii::t('product','Quantity'),
			'value'=>'$data["quantity"]',
			'type'=>'raw',
		),
		array(
			'name'=>'adjustment',
			'header'=> Yii::t('product', 'Adjustment'),
			'value'=>'$data["adjustment"]',
			'type'=>'raw',
			'footer'=>'<b>Total</b>',
		),
		array(
			'name'=>'price',
			'header'=>Yii::t('product', "Price"),
			'value'=>'Yii::app()->locale->numberFormatter->formatCurrency($data["price"],"'.$model->currency.'")',
			'type'=>'raw',
			'footer'=>'<b>'. Yii::app()->locale->numberFormatter->formatCurrency($total,$model->currency) .'</b>',
		),
	),
));
?>
		</td>
	</tr>
</table>
</div>