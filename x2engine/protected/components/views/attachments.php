<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

Yii::app()->clientScript->registerScript('uploadExtensionCheck', "
var illegal_ext = ['exe','bat','dmg','js','jar','swf','php','pl','cgi','htaccess','py'];	// array with disallowed extensions

function checkName(el, sbm) {
	// - www.coursesweb.net
	// get the file name and split it to separe the extension
	var name = el.value;
	var ar_name = name.split('.');

	ar_ext = ar_name[ar_name.length - 1].toLowerCase();
	
	// check the file extension
	var re = 1;
	for(i in illegal_ext) {
		if(illegal_ext[i] == ar_ext) {
			re = 0;
			break;
		}
	}

	// if re is 1, the extension isn't illegal
	if(re==1) {
		// enable submit
		$(sbm).removeAttr('disabled');
	}
	else {
		// delete the file name, disable Submit, Alert message
		el.value = '';
		$(sbm).attr('disabled','disabled');
		
		var filenameError = ".json_encode(Yii::t('app','"{X}" is not an allowed filetype.')).";
		alert(filenameError.replace('{X}',ar_ext));
	}
}
",CClientScript::POS_HEAD);
?>
<div id="attachment-form-top"></div>
<div id="attachment-form"<?php if($startHidden) echo ' style="display:none;"'; ?>>
<div class="form">
<b><?php echo Yii::t('app','Attach a File'); ?></b><br />
<?php
	echo CHtml::form(array('/site/upload'),'post',array('enctype'=>'multipart/form-data','id'=>'attachment-form-form')); 
	echo CHtml::hiddenField('associationType',$this->associationType);
	echo CHtml::hiddenField('associationId',$this->associationId);
    echo CHtml::hiddenField('attachmentText','');
	echo CHtml::dropDownList('private','public',array('0'=>Yii::t('actions','Public'),'1'=>Yii::t('actions','Private')));
	echo CHtml::fileField('upload','',array('id'=>'upload','onchange'=>"checkName(this, '#submitAttach')"));
	echo CHtml::submitButton('Submit',array('id'=>'submitAttach','disabled'=>'disabled','class'=>'x2-button','style'=>'display:inline'));
	echo CHtml::endForm();
?>
</div>
</div>