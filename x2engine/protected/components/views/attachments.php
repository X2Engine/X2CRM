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

Yii::app()->clientScript->registerScript('uploadExtensionCheck', "
var illegal_ext = ['exe','bat','dmg','js','jar','swf'];	// array with disallowed extensions

function checkName(el, sbm) {
	// - www.coursesweb.net
	// get the file name and split it to separe the extension
	var name = el.value;
	var ar_name = name.split('.');
	
	var ar_ext = ar_name[ar_name.length - 1];
	ar_ext = ar_ext.toLowerCase();
	
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
		document.getElementById(sbm).disabled = false;
	}
	else {
		// delete the file name, disable Submit, Alert message
		el.value = '';
		document.getElementById(sbm).disabled = true;
		
		var filenameError = '".Yii::t('app','"{X}" is not an allowed filetype.')."';
		alert(filenameError.replace('{X}',ar_name[1]));
	}
}
",CClientScript::POS_HEAD);
?>
<div class="form">
<b><?php echo Yii::t('app','Attach a File'); ?></b><br />
<?php
	echo CHtml::form(Yii::app()->request->baseUrl.'/index.php/site/upload','post',array('enctype'=>'multipart/form-data')); 
	echo CHtml::hiddenField('type',$this->type);
	echo CHtml::hiddenField('associationId',$this->associationId);
	echo CHtml::fileField('upload','',array('id'=>'upload','onchange'=>"checkName(this, 'submitAttach')"));
	echo CHtml::submitButton('Submit',array('id'=>'submitAttach','disabled'=>'disabled','class'=>'x2-button','style'=>'display:inline'));
	echo CHtml::endForm();
?>
</div>

<script>

--></script>