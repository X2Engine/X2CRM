<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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

$attributeLabels = $model->attributeLabels();

Yii::app()->clientScript->registerScript('updateWorkflow',"

function startWorkflowStage(workflowId,stageNumber) {
	$.ajax({
		url: '" . CHtml::normalizeUrl(array('workflow/startStage')) . "',
		type: 'GET',
		data: 'workflowId='+workflowId+'&stageNumber='+stageNumber+'&modelId=".$model->id."&type=sales',
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
		data: 'workflowId='+workflowId+'&stageNumber='+stageNumber+'&modelId=".$model->id."&type=sales',
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
		data: 'workflowId='+workflowId+'&stageNumber='+stageNumber+'&modelId=".$model->id."&type=sales',
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
	$('#save-changes').addClass('highlight'); //css('background','yellow');
}
",CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScript('stopEdit','
	$(document).ready(function(){
		$("td#description a").click(function(e){
			e.stopPropagation();
		});
	});
');

$fields=Fields::model()->findAllByAttributes(array('modelName'=>'Sales'));
$nonCustom=array();
$custom=array();
foreach($fields as $field){
    if($field->custom==0){
        $nonCustom[$field->fieldName]=$field;
    }else{
        $custom[$field->fieldName]=$field;
    }
}

?>
<div class="form no-border">
<table class="details">
	<tr>
                <?php if($nonCustom['name']->visible==1){ ?>
		<td class="label" width="20%"><?php echo $attributeLabels['name']; ?></td>
		<td colspan="3" id="name" onclick="showField(this,true)">
			<div class="detail-field"><?php echo $model->name; ?></div>
			<div class="detail-form"><?php echo $form->textField($model,'name',array('size'=>48,'maxlength'=>40)); ?></div>
		</td>
                <?php } ?>
	</tr>
	<tr>
                <?php if($nonCustom['description']->visible==1){ ?>
		<td class="label">
			<?php echo $attributeLabels['description']; ?>
		</td>
		<td colspan="3" class="text-field" id="description" onclick="toggleField(this)"><div class="spacer"></div>
			<div class="detail-field"><?php echo $this->convertUrls($model->description); 
				// replace any CR or LF characters with <br />, maximum of 2 in a row
			?></div>
			<div class="detail-form"><?php echo $form->textArea($model,'description',array('rows'=>6, 'cols'=>50)); ?></div>
		</td>
                <?php } ?>
	</tr>
	<tr>
                <?php if($nonCustom['associatedContacts']->visible==1){ ?>
		<td class="label" width="20%"><?php echo CHtml::link($attributeLabels['associatedContacts'],array('addContact', 'id'=>$model->id)); ?></td>
		<td><?php echo $model->associatedContacts; ?></td>
                <?php } ?>
                <?php if($nonCustom['accountName']->visible==1){ ?>
		<td class="label"><?php echo ($model->accountId==0)? $attributeLabels['accountName'] : CHtml::link($attributeLabels['accountName'],array('accounts/view','id'=>$model->accountId)); ?></td>
		<td colspan="3" id="accountName" onclick="showField(this,true);">
			<div class="detail-field"><b><?php echo $model->accountName; ?></b></div>
			<div class="detail-form"><?php echo $form->hiddenField($model, 'accountName');
				$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
				'name'=>'companyAutoComplete',
				'value'=>$model->accountName,
				'source' => $this->createUrl('contacts/getTerms'),
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
                <?php } ?>
	</tr>
	<?php $workflowList = Workflow::getList(); ?>
	<tr id="workflow-row">
		<td class="label"><?php echo Yii::t('workflow','Workflow'); ?></td>
		<td colspan="3" id="workflow">
			<div class="detail-field" style="width:170px; text-align:center;margin-bottom:5px;" onclick="showField($('#workflow').get(),false);"><?php echo $workflowList[$currentWorkflow]; ?></div>
			<div class="detail-form" style="width:170px; text-align:center;margin-bottom:5px;">
			<?php
			echo CHtml::dropDownList('workflowId',$currentWorkflow,$workflowList,	//$model->workflow
				array(
					'ajax' => array(
						'type'=>'GET', //request type
						'url'=>CHtml::normalizeUrl(array('workflow/getWorkflow','modelId'=>$model->id,'type'=>'sales')), //url to call.
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
			$workflowStatus = Workflow::getWorkflowStatus($currentWorkflow,$model->id,'sales');	// true = include dropdowns
			echo Workflow::renderWorkflow($workflowStatus);
		?></div></td>
	</tr>
	<tr>
                <?php if($nonCustom['assignedTo']->visible==1){ ?>
		<td class="label" width="20%"><?php echo CHtml::link($attributeLabels['assignedTo'],array('addUser', 'id'=>$model->id)); ?></td>
		<td><?php echo $model->assignedTo; ?></td>
		<?php } ?>
                <?php if($nonCustom['expectedCloseDate']->visible==1){ ?>
		<td class="label" width="25%" ><?php echo $attributeLabels['expectedCloseDate']; ?></td>
		<td id="expectedCloseDate" onclick="showField(this,true);">
			<div class="detail-field"><b><?php $model->expectedCloseDate=empty($model->expectedCloseDate)? '' : date('Y-m-d',$model->expectedCloseDate);
												echo $model->expectedCloseDate; ?></b></div>
			<div class="detail-form"><?php Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
			$this->widget('CJuiDateTimePicker',array(
				'model'=>$model, //Model object
				'attribute'=>'expectedCloseDate', //attribute name
				'mode'=>'datetime', //use "time","date" or "datetime" (default)
				'options'=>array(
					'dateFormat'=>'yy-mm-dd',
				), // jquery plugin options
				'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
			));?> </div>
		</td>
                <?php } ?>
	</tr>
	<tr>
                <?php if($nonCustom['quoteAmount']->visible==1){ ?>
		<td class="label"><?php echo $attributeLabels['quoteAmount']; ?>
		<td id="quoteAmount" onclick="showField(this,true);">
			<div class="detail-field"><b><?php echo Yii::app()->locale->numberFormatter->formatCurrency($model->quoteAmount,Yii::app()->params->currency); ?></b></div>
			<div class="detail-form"><?php echo $form->textField($model,'quoteAmount'); ?></div>		
		</td>
                <?php } ?>
                <?php if($nonCustom['leadSource']->visible==1){ ?>
		<td class="label"><?php echo $attributeLabels['leadSource']; ?></td>
		<td id="leadSource" onclick="showField(this,true);">
			<div class="detail-field"><?php echo Yii::t('sales',$model->leadSource); ?></div>
			<div class="detail-form"><?php echo $form->dropDownList($model,'leadSource',array(
					'Website'=>Yii::t('sales','Website'), 
					'Cold Call'=>Yii::t('sales','Cold Call'), 
					"E-Mail"=>Yii::t('sales','E-Mail'), 
					"Store"=>Yii::t('sales','Store')
					)); ?></div>
		</td>
                <?php } ?>
	</tr>
	<tr>
                <?php if($nonCustom['salesStage']->visible==1){ ?>
		<td class="label"><?php echo $attributeLabels['salesStage']; ?></td>
		<td id="salesStage" onclick="showField(this,true);">
			<div class="detail-field"><b><?php echo Yii::t('sales',$model->salesStage); ?></b></div>
			<div class="detail-form"><?php echo $form->dropDownList($model,'salesStage',array(
					'Working'=>Yii::t('sales','Working'),
					'Won'=>Yii::t('sales','Won'),
					'Lost'=>Yii::t('sales','Lost'))
				); ?></div>
		</td>
		<?php } ?>
                <?php if($nonCustom['probability']->visible==1){ ?>
		<td class="label"><?php echo $attributeLabels['probability']; ?></td>
		<td id="probability" onclick="showField(this,true);">
			<div class="detail-field"><b><?php echo $model->probability; ?></b></div>
			<div class="detail-form"><?php echo $form->textField($model,'probability'); ?></div>
		</td>
                <?php } ?>
	</tr>
        <?php 
        
            foreach($custom as $fieldName=>$field){
                
                if($field->visible==1){ 
		echo "<tr>
                <td class=\"label\"><b>".$attributeLabels[$fieldName]."</b></td>
		<td id=\"$fieldName\" onclick=\"toggleField(this);\" colspan=\"5\">
			<div class=\"detail-field\">".$model->$fieldName."</div>
			<div class=\"detail-form\">
			".$form->textField($model,$fieldName,array('size'=>'70'))."
			</div>
		</td>
                </tr>";
                }
            }
        
        ?>
</table>
</div>