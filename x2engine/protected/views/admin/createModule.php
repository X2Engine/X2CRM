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