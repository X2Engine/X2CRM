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

?>

<h2><?php echo Yii::t('module','Create New Module'); ?></h2>
<?php echo Yii::t('module','Please fill out the fields below to create a new module.'); ?><br /><br />
Extra fields should be added from the "Manage Fields" page.<br /><br />
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
	
	<br /><br /><input type="Submit" name="Submit" value="<?php echo Yii::t('app','Submit'); ?>" class="x2-button" /> 
</div>