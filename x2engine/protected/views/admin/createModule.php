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
?>
<h2><?php echo Yii::t('module','Create New Module'); ?></h2>
<?php echo Yii::t('module','Please fill out the fields below to create a new module.'); ?><br /><br />
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
	<label><?php echo Yii::t('module','Data fields'); ?></label>
	<select name="displayOne" disabled="disabled">
		<option value="1" selected="selected"><?php echo Yii::t('module','Show'); ?></option>
		<option value="0"><?php echo Yii::t('module','Hide'); ?></option></select> <?php echo Yii::t('module','ID'); ?><br />
	<select name="displayOne" disabled="disabled">
		<option value="1" selected="selected"><?php echo Yii::t('module','Show'); ?></option>
		<option value="0"><?php echo Yii::t('module','Hide'); ?></option></select> <?php echo Yii::t('module','Name'); ?><br />
	<select name="displayAssignedTo">
		<option value="1" selected="selected"><?php echo Yii::t('module','Show'); ?></option>
		<option value="0"><?php echo Yii::t('module','Hide'); ?></option></select> <?php echo Yii::t('module','Assigned To'); ?><br />
	<select name="displayDescription">
		<option value="1" selected="selected"><?php echo Yii::t('module','Show'); ?></option>
		<option value="0"><?php echo Yii::t('module','Hide'); ?></option></select> <?php echo Yii::t('module','Description'); ?><br />
	<select name="displayOne">
		<option value="1" selected="selected"><?php echo Yii::t('module','Show'); ?></option>
		<option value="0"><?php echo Yii::t('module','Hide'); ?></option></select> <input type="text" size="30" onFocus="toggleText(this);" onBlur="toggleText(this);" style="color:#aaa;" name="fieldOne" value="<?php echo Yii::t('module','Enter field name here'); ?>" /><br />
	<select name="displayTwo">
		<option value="1" selected="selected"><?php echo Yii::t('module','Show'); ?></option>
		<option value="0"><?php echo Yii::t('module','Hide'); ?></option></select> <input type="text" size="30" onFocus="toggleText(this);" onBlur="toggleText(this);" style="color:#aaa;" name="fieldTwo" value="<?php echo Yii::t('module','Enter field name here'); ?>" /><br />
	<select name="displayThree">
		<option value="1" selected="selected"><?php echo Yii::t('module','Show'); ?></option>
		<option value="0"><?php echo Yii::t('module','Hide'); ?></option></select> <input type="text" size="30" onFocus="toggleText(this);" onBlur="toggleText(this);" style="color:#aaa;" name="fieldThree" value="<?php echo Yii::t('module','Enter field name here'); ?>" /><br />
	<select name="displayFour">
		<option value="1" selected="selected"><?php echo Yii::t('module','Show'); ?></option>
		<option value="0"><?php echo Yii::t('module','Hide'); ?></option></select> <input type="text" size="30" onFocus="toggleText(this);" onBlur="toggleText(this);" style="color:#aaa;" name="fieldFour" value="<?php echo Yii::t('module','Enter field name here'); ?>" /><br />
	<select name="displayFive">
		<option value="1" selected="selected"><?php echo Yii::t('module','Show'); ?></option>
		<option value="0"><?php echo Yii::t('module','Hide'); ?></option></select> <input type="text" size="30" onFocus="toggleText(this);" onBlur="toggleText(this);" style="color:#aaa;" name="fieldFive" value="<?php echo Yii::t('module','Enter field name here'); ?>" /><br />
	<select name="displayCreateDate" disabled="disabled">
		<option value="1" selected="selected"><?php echo Yii::t('module','Show'); ?></option>
		<option value="0"><?php echo Yii::t('module','Hide'); ?></option></select> <?php echo Yii::t('module','Create Date'); ?><br />
	<select name="displayUpdated" disabled="disabled">
		<option value="1" selected="selected"><?php echo Yii::t('module','Show'); ?></option>
		<option value="0"><?php echo Yii::t('module','Hide'); ?></option></select> <?php echo Yii::t('module','Last Updated'); ?><br />
	<select name="displayUpdatedBy" disabled="disabled">
		<option value="1" selected="selected"><?php echo Yii::t('module','Show'); ?></option>
		<option value="0"><?php echo Yii::t('module','Hide'); ?></option></select> <?php echo Yii::t('module','Updated By'); ?><br />
	
	<br /><br /><input type="Submit" name="Submit" value="<?php echo Yii::t('app','Submit'); ?>" class="x2-button" />
</form>
</div>