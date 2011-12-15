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
?><?php
Yii::app()->clientScript->registerScript('customModuleFields', "
function deleteStage(object) {
	$(object).closest('li').remove();
}

function moveStageUp(object) {
	var prev = $(object).closest('li').prev();
	if(prev.length>0) {
		prev.before('<li>'+$(object).closest('li').html()+'</li>');
		$(object).closest('li').remove();
	}
}
function moveStageDown(object) {
	var next = $(object).closest('li').next();
	if(next.length>0) {
		next.after('<li>'+$(object).closest('li').html()+'</li>');
		$(object).closest('li').remove();
	}
}

function addStage() {
	$('#workflow-stages ol').append(' \
	<li>\
	<select name=\"CustomFields[visible][]\">\
		<option value=\"1\" selected=\"selected\">".Yii::t('module','Show')."</option>\
		<option value=\"0\">".Yii::t('module','Hide')."</option></select>\
                <input type=\"text\" size=\"30\" onFocus=\"toggleText(this);\" onBlur=\"toggleText(this);\" style=\"color:#aaa;\" name=\"CustomFields[fieldName][]\" value=\"".Yii::t('module','Enter field name here')."\" />\
                <input type=\"text\" size=\"30\" onFocus=\"toggleText(this);\" onBlur=\"toggleText(this);\" style=\"color:#aaa;\" name=\"CustomFields[attributeLabel][]\" value=\"".Yii::t('module','Enter field label here')."\" />\
        <div class=\"cell\">\
            <a href=\"javascript:void(0)\" onclick=\"moveStageUp(this);\">[".Yii::t('workflow','Up')."]</a>\
            <a href=\"javascript:void(0)\" onclick=\"moveStageDown(this);\">[".Yii::t('workflow','Down')."]</a>\
            <a href=\"javascript:void(0)\" onclick=\"deleteStage(this);\">[".Yii::t('workflow','Del')."]</a>\
        </div><br />\
	</li>');
}

",CClientScript::POS_HEAD);
?>

<h2><?php echo Yii::t('module','Create New Module'); ?></h2>
<?php echo Yii::t('module','Please fill out the fields below to create a new module.'); ?><br /><br />
Field names must be one word, preferably camel cased (i.e. firstName, lastName, expectedCloseDate) and labels can be anything.<br /><br />
<div class="form">
<?php if(!empty($errors)) { ?>
	<div class="errorSummary"><p><?php echo Yii::t('yii','Please fix the following input errors:'); ?></p>
		<ul><?php foreach($errors as $error) { ?>
			<li><?php echo $error; ?></li><?php } ?>
		</ul>
	</div><br />
<?php } ?>	
<form id="newModule" method="POST" action="createModule">
	<div class="row">
		<div class="cell" style="width:200px;"><label for="title"><?php echo Yii::t('module','Module Title'); ?> <span class="required">*</span></label><?php echo Yii::t('module','The name for your new module'); ?><br /><input type="text" size="30" onFocus="toggleText(this);" onBlur="toggleText(this);" style="color:#aaa;" name="title" id="title" /></div>
		<div class="cell"><label for="recordName"><?php echo Yii::t('module','Item Name'); ?></label><?php echo Yii::t('module','(Optional) What to call individual records, e.g. "Create new X"'); ?><br /><input type="text" size="30" onFocus="toggleText(this);" onBlur="toggleText(this);" style="color:#aaa;" name="recordName" id="recordName" /></div>
	</div>
	<div class="row">
		<div class="cell"><label for="moduleName"><?php echo Yii::t('module','DB Table Name'); ?></label><?php echo Yii::t('module','Optional (alphanumeric only, must start with a letter)'); ?><br /><input type="text" size="30" onFocus="toggleText(this);" onBlur="toggleText(this);" style="color:#aaa;" name="moduleName" id="moduleName" /><br /></div>
	</div>
        <label><span style="margin-right:40px;">Visibility</span><span style="margin-right:140px;">Field Name</span><span>Field Label</span></label><br />
	<select name="displayId" disabled="disabled" style="margin-right:20px;">
		<option value="1" selected="selected"><?php echo Yii::t('module','Show'); ?></option>
		<option value="0"><?php echo Yii::t('module','Hide'); ?></option></select> <span style="margin-right:184px;">id</span><span><?php echo Yii::t('module','ID'); ?></span><br />
	<select name="displayName" disabled="disabled" style="margin-right:20px;">
		<option value="1" selected="selected"><?php echo Yii::t('module','Show'); ?></option>
		<option value="0"><?php echo Yii::t('module','Hide'); ?></option></select> <span style="margin-right:161px;">name</span><span><?php echo Yii::t('module','Name'); ?></span><br />
	<select name="displayAssignedTo" style="margin-right:20px;">
		<option value="1" selected="selected"><?php echo Yii::t('module','Show'); ?></option>
		<option value="0"><?php echo Yii::t('module','Hide'); ?></option></select> <span style="margin-right:127px;">assignedTo</span><span><?php echo Yii::t('module','Assigned To'); ?></span><br />
	<select name="displayDescription" style="margin-right:20px;">
		<option value="1" selected="selected"><?php echo Yii::t('module','Show'); ?></option>
		<option value="0"><?php echo Yii::t('module','Hide'); ?></option></select> <span style="margin-right:132px;">description</span><span><?php echo Yii::t('module','Description'); ?></span><br />
	
              
	<div id="workflow-stages">
	<?php
	if(empty($stages))
		$stages[] = new WorkflowStage;	// start with at least 1 blank row
	?><ol><?php
	foreach($stages as &$stage) { ?>
	<li>
            <select name="CustomFields[visible][]">
		<option value="1" selected="selected"><?php echo Yii::t('module','Show'); ?></option>
		<option value="0"><?php echo Yii::t('module','Hide'); ?></option></select> 
                <input type="text" size="30" onFocus="toggleText(this);" onBlur="toggleText(this);" style="color:#aaa;" name="CustomFields[fieldName][]" value="<?php echo Yii::t('module','Enter field name here'); ?>" />
                <input type="text" size="30" onFocus="toggleText(this);" onBlur="toggleText(this);" style="color:#aaa;" name="CustomFields[attributeLabel][]" value="<?php echo Yii::t('module','Enter field label here'); ?>" />
	<div class="cell">
			<a href="javascript:void(0)" onclick="moveStageUp(this);">[<?php echo Yii::t('workflow','Up'); ?>]</a>
			<a href="javascript:void(0)" onclick="moveStageDown(this);">[<?php echo Yii::t('workflow','Down'); ?>]</a>
			<a href="javascript:void(0)" onclick="deleteStage(this);">[<?php echo Yii::t('workflow','Del'); ?>]</a>
        </div>
        <br />
        </li>
	<?php 
	}
	?>
	</ol>
	</div>
	<a href="javascript:void(0)" onclick="addStage();" class="add-workflow-stage">[<?php echo Yii::t('workflow','Add'); ?>]</a>

                
        <select name="displayCreateDate" disabled="disabled" style="margin-right:20px;">
		<option value="1" selected="selected"><?php echo Yii::t('module','Show'); ?></option>
		<option value="0"><?php echo Yii::t('module','Hide'); ?></option></select> <span style="margin-right:133px;">createDate</span><span><?php echo Yii::t('module','Create Date'); ?></span><br />
	<select name="displayUpdated" disabled="disabled" style="margin-right:20px;">
		<option value="1" selected="selected"><?php echo Yii::t('module','Show'); ?></option>
		<option value="0"><?php echo Yii::t('module','Hide'); ?></option></select> <span style="margin-right:130px;">lastUpdated</span><span><?php echo Yii::t('module','Last Updated'); ?></span><br />
	<select name="displayUpdatedBy" disabled="disabled" style="margin-right:20px;">
		<option value="1" selected="selected"><?php echo Yii::t('module','Show'); ?></option>
		<option value="0"><?php echo Yii::t('module','Hide'); ?></option></select> <span style="margin-right:137px;">updatedBy</span><span><?php echo Yii::t('module','Updated By'); ?></span><br />
	
	<br /><br /><input type="Submit" name="Submit" value="<?php echo Yii::t('app','Submit'); ?>" class="x2-button" /> 
</div>