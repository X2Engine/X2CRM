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

// Yii::app()->clientScript->registerScript('formEditor', "

// ",CClientScript::POS_READY);

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/x2formEditor.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/colResizable-1.3.min.js');

if(isset($layoutModel) && !empty($layoutModel->layout)) {
	Yii::app()->clientScript->registerScript('loadForm','
	loadFormJson(\''.preg_replace('/\\"/u','\\\\"',addcslashes($layoutModel->layout,"'\\")).'\');
	',CClientScript::POS_READY);
}

// version navigation
Yii::app()->clientScript->registerScript('formVersionNav',"
$('#modelList').change(function() {
	if(window.layoutChanged && !confirm('".addslashes(Yii::t('admin','Leave without saving changes?'))."'))
		$(this).val('".$modelName."');
	else
		window.location.href = '".CHtml::normalizeUrl(array('editor'))."?model='+$(this).val();
});
$('#versionList').change(function(e) {
	if(window.layoutChanged && !confirm('".addslashes(Yii::t('admin','Leave without saving changes?'))."'))
		$(this).val('".$id."');
	else
		window.location.href = '".CHtml::normalizeUrl(array('editor'))."?model='+$('#modelList').val()+'&id='+$(this).val();
});
$('#newLayoutButton').click(function() {
	if(!window.layoutChanged || confirm('".addslashes(Yii::t('admin','Leave without saving changes?'))."')) {
		var layoutName = prompt('".addslashes(Yii::t('admin','Please enter a name for the new layout.'))."');
		if(layoutName != null && layoutName != '')
			window.location.href = '".CHtml::normalizeUrl(array('createFormLayout'))."?model='+$('#modelList').val()+'&newLayout=1&layoutName='+encodeURI(layoutName);
	}
});
$('#copyLayoutButton').click(function() {
	if(!window.layoutChanged || confirm('".addslashes(Yii::t('admin','Leave without saving changes?'))."')) {
		var layoutName = prompt('".addslashes(Yii::t('admin','Please enter a name for the new layout.'))."');
		if(layoutName != null && layoutName != '') {
			$('#layoutHiddenField').val(generateFormJson());
			$('#formEditorForm').attr('action','".CHtml::normalizeUrl(array('createFormLayout'))."?model='+$('#modelList').val()+'&newLayout=1&layoutName='+encodeURI(layoutName));
			$('#formEditorForm').unbind('submit').submit();
		}
	}
		// window.location.href = '".CHtml::normalizeUrl(array('deleteFormLayout'))."?id='+$('#versionList').val();
});
$('#deleteVersionButton').click(function() {
	if(confirm('".addslashes(Yii::t('admin','Are you sure you want to delete this layout?'))."'))
		window.location.href = '".CHtml::normalizeUrl(array('deleteFormLayout'))."?id='+$('#versionList').val();
});
",CClientScript::POS_READY);

?>
<h2><?php echo Yii::t('admin','Form Editor'); ?></h2>
<div style="width:600px;">
    <?php echo Yii::t('admin','Add a form row and drag and drop fields from the field list. Click save when finished.'); ?><br /><br />
    <?php echo Yii::t('admin','Each module can have multiple layouts, but only one view and one form can be active at any given time.');?>
    <?php echo Yii::t('admin','To choose which layout is used, select either "Default View" or "Default Form" or both depending on how you want the layout to be used.');?>
    
</div>
<br><br>
<?php
echo CHtml::beginForm(array('editor','id'=>$id),'post',array('id'=>'formEditorForm'));
echo CHtml::hiddenField('layout','',array('id'=>'layoutHiddenField'));
?>
<div class="form">
	<div class="row">
		<div class="cell">
			<?php echo CHtml::label(Yii::t('admin','Model'),'modelList'); ?>
			<?php echo CHtml::dropDownList('model',$modelName,$modelList,array(
				'id'=>'modelList'
			)); ?> 
		</div>
		<?php if(!empty($modelName)) { ?>
		<div class="cell">
			<?php echo CHtml::label(Yii::t('admin','Version'),'versionList'); ?>
			<?php echo CHtml::dropDownList('id',$id,$versionList,array(
				'id'=>'versionList'
			)); ?>
		</div>
		<div class="cell" style="padding-top:11px;">
			<?php echo CHtml::button(Yii::t('admin','New'),array('id'=>'newLayoutButton','class'=>'x2-button small float')); ?>
		</div>
		<?php } ?>
		
		<?php if(count($versionList) > 1 && !empty($id)) { ?>
		<div class="cell" style="padding-top:11px;">
			<?php echo CHtml::button(Yii::t('admin','Copy'),array('id'=>'copyLayoutButton','class'=>'x2-button small float')); ?>
		</div>
		<div class="cell" style="padding-top:11px;">
			<?php echo CHtml::button(Yii::t('admin','Delete'),array('id'=>'deleteVersionButton','class'=>'x2-button small float')); ?>
		</div>
		<div class="cell">
			<?php echo CHtml::label(Yii::t('admin','Default View'),'defaultView'); ?>
			<?php echo CHtml::checkbox('defaultView',$defaultView); ?>
		</div>
		<div class="cell">
			<?php echo CHtml::label(Yii::t('admin','Default Form'),'defaultForm'); ?>
			<?php echo CHtml::checkbox('defaultForm',$defaultForm); ?>
		</div>
		<div class="cell right" style="padding-top:11px;">
			<?php echo CHtml::button(Yii::t('admin','Preview Mode'),array('id'=>'borderToggleButton','class'=>'x2-button right')); ?>
			<?php echo CHtml::submitButton(Yii::t('admin','Save'), array('class'=>'x2-button highlight right','style'=>'margin-right:5px;','id'=>'saveButton')); ?>
		</div>
		<?php } ?>
	</div>
</div>
<?php echo CHtml::endForm(); ?>
<?php if(!empty($modelName)) { ?>
<div id="fieldListBox">
<div id="fieldListTitle"><?php echo Yii::t('admin','Field List'); ?></div>
<div id="editorFieldList" class="formSortable">
	<?php
	
	// get list of all fields, sort by attribute label alphabetically
	$fields = Fields::model()->findAllByAttributes(array('modelName'=>$modelName),new CDbCriteria(array('order'=>'attributeLabel ASC')));
	foreach($fields as &$field) {
		$type = '';
		switch($field->type) {
			case 'email':
				$type = 'emailIcon';
				break;
			case 'phone':
				$type = 'phoneIcon';
				break;
			case 'boolean':
				$type = 'booleanIcon';
				break;
			case 'dropdown':
				$type = 'dropdownIcon';
				break;
			case 'date':
				$type = 'dateIcon';
				break;
			case 'text':
				$type = 'textIcon';
				break;
			case 'percentage':
				$type = 'percentageIcon';
				break;
			default:
				$type = 'varcharIcon';
		}

		echo '<div class="formItem topLabel" id="formItem_'.$field->fieldName.'"><div class="formTabOrder"></div><label class="'.$type.'">'.$field->attributeLabel.'</label>';
		echo '<div class="formInputBox">';
		if($field->type == 'text') {
			echo CHtml::textArea($modelName.'_'.$field->fieldName,'', array(
					'title'=>$field->attributeLabel,
			));
		}elseif($field->type=='dropdown'){
			$dropdown=Dropdowns::model()->findByPk($field->linkType);
			echo CHtml::dropDownList($modelName.'_'.$field->fieldName,'',json_decode($dropdown->options), array(
					'title'=>$field->attributeLabel,
			));
		}elseif($field->type=='boolean'){
			echo '<div class="checkboxWrapper">';
			echo CHtml::checkBox($modelName.'_'.$field->fieldName,false,array(
				'title'=>$field->attributeLabel,
			)).'</div>';
		}elseif($field->type=='assignment'){
			echo CHtml::dropDownList($field->fieldName,'', array('Users'), array(
					'title'=>$field->attributeLabel,
			));
		}elseif($field->type=='visibility'){
			echo CHtml::dropDownList($field->fieldName,'',array(1=>'Public',0=>'Private',2=>'User\'s Groups'), array(
					'title'=>$field->attributeLabel,
			));
		} else {
			echo CHtml::textField($modelName.'_'.$field->fieldName,'', array(
					'title'=>$field->attributeLabel,
			));
		}
		echo '</div></div>';
	} ?>
</div>
</div>
<?php } ?>
<?php if(!empty($id)) { ?>
<div class="formContainer span-15">
<div class="x2-layout form-view editMode" id="formEditor">
	<div id="formEditorControls">
		<a href="javascript:void(0)" id="addRow" class="x2-button"><?php echo Yii::t('admin','Add Row'); ?></a> 
		<a href="javascript:void(0)" id="addCollapsibleRow" class="x2-button"><?php echo Yii::t('admin','Add Collapsible'); ?></a>
		
		<span class="formItemOptions">
			<label for="readOnly"><?php echo Yii::t('admin','Read-only'); ?></label>
			<select id="readOnly">
				<option value="0"><?php echo Yii::t('app','No'); ?></option>
				<option value="1"><?php echo Yii::t('app','Yes'); ?></option>
				<option value="mixed" disabled="disabled">---</option>
			</select>
			<label for="labelType"><?php echo Yii::t('admin','Label Position'); ?></label>
			<select id="labelType">
				<option value="left"><?php echo Yii::t('admin','Left'); ?></option>
				<option value="top"><?php echo Yii::t('admin','Top'); ?></option>
				<option value="inline"><?php echo Yii::t('admin','Inline'); ?></option>
				<option value="none"><?php echo Yii::t('admin','None'); ?></option>
				<option value="mixed" disabled="disabled">---</option>
			</select>
			<!--<a href="javascript:void(0)" id="setTabOrder" class="x2-button"><?php echo Yii::t('admin','Tab Order'); ?></a>-->
		</span>
	</div>
</div>
</div>
<?php } ?>