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

Yii::app()->clientScript->registerScript('detailVewFields', "
function toggleField(field){
	$('#'+field.id+' .detail-field').hide();
	$('#'+field.id+' .detail-form').show();
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
$template="<a href=".$this->createUrl('search/search?term=%23\\2')."> #\\2</a>";
		$info=$model->description;
		$info=preg_replace('/(^|\s)#(\w\w+)/',$template,$info);
                
$fields=Fields::model()->findAllByAttributes(array('modelName'=>'Accounts'));
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
<table class="details">
	<tr>
                <?php if($nonCustom['name']->visible==1){ ?>
		<td class="label" width="20%"><?php echo $attributeLabels['name']; ?></td>
		<td id="name" onclick="toggleField(this);">
			<div class="detail-field"><?php echo $model->name; ?></div>
			<div class="detail-form"><?php echo $form->textField($model,'name',array('size'=>25,'maxlength'=>40)); ?></div>
		</td>
                <?php } ?>
                <?php if($nonCustom['type']->visible==1){ ?>
		<td class="label" width="15%"><?php echo $attributeLabels['type']; ?></td>
		<td id="type" onclick="toggleField(this);">
			<div class="detail-field"><?php echo $model->type; ?></div>
			<div class="detail-form"><?php echo $form->textField($model,'type',array('size'=>8,'maxlength'=>40)); ?></div>
		</td>
                <?php } ?>
                <?php if($nonCustom['tickerSymbol']->visible==1){ ?>
		<td class="label" width="10%"><?php echo $attributeLabels['tickerSymbol']; ?></td>
		<td id="tickerSymbol" onclick="toggleField(this);">
			<div class="detail-field"><?php echo $model->tickerSymbol; ?></div>
			<div class="detail-form"><?php echo $form->textField($model,'tickerSymbol',array('size'=>4,'maxlength'=>10)); ?></div>
		</td>
                <?php } ?>
	</tr>
	<tr>
                <?php if($nonCustom['description']->visible==1){ ?>
		<td class="label">
			<?php echo $attributeLabels['description']; ?>
		</td>
		<td colspan="5" class="text-field" id="description" onclick="toggleField(this);"><div class="spacer"></div>
			<div class="detail-field"><?php echo $this->convertLineBreaks($info); ?></div>
			<div class="detail-form"><?php echo $form->textArea($model,'description',array('rows'=>4, 'cols'=>50)); ?></div>
		</td>
                <?php } ?>
	</tr>
	<tr>
                <?php if($nonCustom['assignedTo']->visible==1){ ?>
		<td class="label"><?php echo $attributeLabels['assignedTo']; ?></td>
		<td><?php echo $model->assignedTo; ?></td>
                <?php } ?>
		<td class="label"><?php echo Yii::t('opportunities','Opportunities'); ?></td>
		<td colspan="3"><?php echo Opportunity::getOpportunityLinks($model->id); ?></td>
                
	</tr>
	<tr>
                <?php if($nonCustom['associatedContacts']->visible==1){ ?>
		<td class="label"><?php echo $attributeLabels['associatedContacts']; ?></td>
		<td><?php echo $model->associatedContacts; ?></td>
                <?php } ?>
                <?php if($nonCustom['annualRevenue']->visible==1){ ?>
		<td class="label"><?php echo $attributeLabels['annualRevenue']; ?></td>
		<td colspan="3" id="annualRevenue" onclick="toggleField(this);">
			<div class="detail-field"><b><?php echo Yii::app()->locale->numberFormatter->formatCurrency($model->annualRevenue,Yii::app()->params->currency); ?></b></div>
			<div class="detail-form"><?php echo $form->textField($model,'annualRevenue',array('size'=>13,'maxlength'=>10)); ?></div>
		</td>
                <?php } ?>
	</tr>
	<tr>
                <?php if($nonCustom['phone']->visible==1){ ?>
		<td class="label"><?php echo $attributeLabels['phone']; ?>
		<td id="phone" onclick="toggleField(this);">
			<div class="detail-field"><b><?php echo $model->phone; ?></b></div>
			<div class="detail-form"><?php echo $form->textField($model,'phone',array('size'=>25,'maxlength'=>40)); ?></div>
		</td>
                <?php } ?>
                <?php if($nonCustom['employees']->visible==1){ ?>
		<td class="label"><?php echo $attributeLabels['employees']; ?></td>
		<td colspan="3" id="employees" onclick="toggleField(this);">
			<div class="detail-field"><b><?php echo $model->employees; ?></b></div>
			<div class="detail-form"><?php echo $form->textField($model,'employees',array('size'=>13,'maxlength'=>10)); ?></div>
		</td>
                <?php } ?>
	</tr>
	<tr>
                <?php if($nonCustom['website']->visible==1){ ?>
		<td class="label"><?php echo $attributeLabels['website']; ?></td>
		<td colspan="5" id="website" onclick="toggleField(this);">
		<div class="detail-field"><?php echo $model->website; ?></div>
		<div class="detail-form"><?php echo $form->textField($model,'website',array('size'=>25,'maxlength'=>40)); ?></div>
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









