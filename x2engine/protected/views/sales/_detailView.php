<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/

$attributeLabels = SaleChild::attributeLabels();

Yii::app()->clientScript->registerScript('detailVewFields', "
function toggleField(field){
	$('#'+field.id+' .detail-field').hide();
	$('#'+field.id+' .detail-form').show();
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

$template="<a href=".$this->createUrl('search/search?term=%23\\2')."> #\\2</a>";
$info=$model->description;
$info=mb_ereg_replace('(^|\s)#(\w\w+)',$template,$info);
?>
<table class="details">
	<tr>
		<td class="label" width="20%"><?php echo $attributeLabels['name']; ?></td>
		<td colspan="3" id="name" onclick="toggleField(this)">
			<div class="detail-field"><?php echo $model->name; ?></div>
			<div class="detail-form"><?php echo $form->textField($model,'name',array('size'=>48,'maxlength'=>40)); ?></div>
		</td>
	</tr>
	<tr>
		<td class="label">
			<?php echo $attributeLabels['description']; ?>
		</td>
		<td colspan="3" class="text-field" id="description" onclick="toggleField(this)"><div class="spacer"></div>
			<div class="detail-field"><?php echo $this->convertLineBreaks($info); 
				// replace any CR or LF characters with <br />, maximum of 2 in a row
			?></div>
			<div class="detail-form"><?php echo $form->textArea($model,'description',array('rows'=>6, 'cols'=>50)); ?></div>
		</td>
	</tr>
	<tr>
		<td class="label" width="20%"><?php echo CHtml::link($attributeLabels['associatedContacts'],array('addContact', 'id'=>$model->id)); ?></td>
		<td><?php echo $model->associatedContacts; ?></td>
		<td class="label"><?php echo ($model->accountId==0)? $attributeLabels['accountName'] : CHtml::link($attributeLabels['accountName'],array('accounts/view','id'=>$model->accountId)); ?></td>
		<td colspan="3" id="accountName" onclick="toggleField(this);">
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
	</tr>
	<tr>
		<td class="label" width="20%"><?php echo CHtml::link($attributeLabels['assignedTo'],array('addUser', 'id'=>$model->id)); ?></td>
		<td><?php echo $model->assignedTo; ?></td>
		
		<td class="label" width="25%" ><?php echo $attributeLabels['expectedCloseDate']; ?></td>
		<td id="expectedCloseDate" onclick="toggleField(this);">
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
	</tr>
	<tr>
		<td class="label"><?php echo $attributeLabels['quoteAmount']; ?>
		<td id="quoteAmount" onclick="toggleField(this);">
			<div class="detail-field"><b><?php echo Yii::app()->locale->numberFormatter->formatCurrency($model->quoteAmount,Yii::app()->params->currency); ?></b></div>
			<div class="detail-form"><?php echo $form->textField($model,'quoteAmount'); ?></div>		
		</td>
					
		<td class="label"><?php echo $attributeLabels['leadSource']; ?></td>
		<td id="leadSource" onclick="toggleField(this);">
			<div class="detail-field"><?php echo Yii::t('sales',$model->leadSource); ?></div>
			<div class="detail-form"><?php echo $form->dropDownList($model,'leadSource',array(
					'Website'=>Yii::t('sales','Website'), 
					'Cold Call'=>Yii::t('sales','Cold Call'), 
					"E-Mail"=>Yii::t('sales','E-Mail'), 
					"Store"=>Yii::t('sales','Store')
					)); ?></div>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo $attributeLabels['salesStage']; ?></td>
		<td id="salesStage" onclick="toggleField(this);">
			<div class="detail-field"><b><?php echo Yii::t('sales',$model->salesStage); ?></b></div>
			<div class="detail-form"><?php echo $form->dropDownList($model,'salesStage',array(
					'Working'=>Yii::t('sales','Working'),
					'Won'=>Yii::t('sales','Won'),
					'Lost'=>Yii::t('sales','Lost'))
				); ?></div>
		</td>
		
		<td class="label"><?php echo $attributeLabels['probability']; ?></td>
		<td id="probability" onclick="toggleField(this);">
			<div class="detail-field"><b><?php echo $model->probability; ?></b></div>
			<div class="detail-form"><?php echo $form->textField($model,'probability'); ?></div>
		</td>
	</tr>
</table>