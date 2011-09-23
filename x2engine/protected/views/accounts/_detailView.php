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

$attributeLabels = AccountChild::attributeLabels();

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
		$info=preg_replace('/(^|\s)#(\w\w+)/',$template,$info);
?>
<table class="details">
	<tr>
		<td class="label" width="20%"><?php echo $attributeLabels['name']; ?></td>
		<td id="name" onclick="toggleField(this);">
			<div class="detail-field"><?php echo $model->name; ?></div>
			<div class="detail-form"><?php echo $form->textField($model,'name',array('size'=>25,'maxlength'=>40)); ?></div>
		</td>
		<td class="label" width="15%"><?php echo $attributeLabels['type']; ?></td>
		<td id="type" onclick="toggleField(this);">
			<div class="detail-field"><?php echo $model->type; ?></div>
			<div class="detail-form"><?php echo $form->textField($model,'type',array('size'=>8,'maxlength'=>40)); ?></div>
		</td>
		<td class="label" width="10%"><?php echo $attributeLabels['tickerSymbol']; ?></td>
		<td id="tickerSymbol" onclick="toggleField(this);">
			<div class="detail-field"><?php echo $model->tickerSymbol; ?></div>
			<div class="detail-form"><?php echo $form->textField($model,'tickerSymbol',array('size'=>4,'maxlength'=>10)); ?></div>
		</td>
	</tr>
	<tr>
		<td class="label">
			<?php echo $attributeLabels['description']; ?>
		</td>
		<td colspan="5" class="text-field" id="description" onclick="toggleField(this);"><div class="spacer"></div>
			<div class="detail-field"><?php echo $this->convertLineBreaks($info); ?></div>
			<div class="detail-form"><?php echo $form->textArea($model,'description',array('rows'=>4, 'cols'=>50)); ?></div>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo $attributeLabels['assignedTo']; ?></td>
		<td><?php echo $model->assignedTo; ?></td>
		<td class="label"><?php echo Yii::t('sales','Sales'); ?></td>
		<td colspan="3"><?php echo SaleChild::getSalesLinks($model->id); ?></td>
	</tr>
	<tr>
		<td class="label"><?php echo $attributeLabels['associatedContacts']; ?></td>
		<td><?php echo $model->associatedContacts; ?></td>

		<td class="label"><?php echo $attributeLabels['annualRevenue']; ?></td>
		<td colspan="3" id="annualRevenue" onclick="toggleField(this);">
			<div class="detail-field"><b><?php echo Yii::app()->locale->numberFormatter->formatCurrency($model->annualRevenue,Yii::app()->params->currency); ?></b></div>
			<div class="detail-form"><?php echo $form->textField($model,'annualRevenue',array('size'=>13,'maxlength'=>10)); ?></div>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo $attributeLabels['phone']; ?>
		<td id="phone" onclick="toggleField(this);">
			<div class="detail-field"><b><?php echo $model->phone; ?></b></div>
			<div class="detail-form"><?php echo $form->textField($model,'phone',array('size'=>25,'maxlength'=>40)); ?></div>
		</td>
		<td class="label"><?php echo $attributeLabels['employees']; ?></td>
		<td colspan="3" id="employees" onclick="toggleField(this);">
			<div class="detail-field"><b><?php echo $model->employees; ?></b></div>
			<div class="detail-form"><?php echo $form->textField($model,'employees',array('size'=>13,'maxlength'=>10)); ?></div>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo $attributeLabels['website']; ?></td>
		<td colspan="5" id="website" onclick="toggleField(this);">
		<div class="detail-field"><?php echo $model->website; ?></div>
		<div class="detail-form"><?php echo $form->textField($model,'website',array('size'=>25,'maxlength'=>40)); ?></div>
		</td>

	</tr>
</table>









