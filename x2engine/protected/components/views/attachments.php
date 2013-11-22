<?php
/* * *******************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 * ****************************************************************************** */

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

		var filenameError = ".json_encode(Yii::t('app', '"{X}" is not an allowed filetype.')).";
		alert(filenameError.replace('{X}',ar_ext));
	}
}
", CClientScript::POS_HEAD);
?>
<div id="attachment-form-top"></div>
<div id="attachment-form"<?php if($startHidden) echo ' style="display:none;"'; ?>>
    <div class="form">
        <b><?php echo Yii::t('app', 'Attach a File'); ?></b><br />
        <?php
        echo CHtml::form(array('/site/upload'), 'post', array('enctype' => 'multipart/form-data', 'id' => 'attachment-form-form'));
        echo "<div class='row'>";
        echo CHtml::hiddenField('associationType', $this->associationType);
        echo CHtml::hiddenField('associationId', $this->associationId);
        echo CHtml::hiddenField('attachmentText', '');
        echo CHtml::dropDownList('private', 'public', array('0' => Yii::t('actions', 'Public'), '1' => Yii::t('actions', 'Private')));
        echo CHtml::fileField('upload', '', array('id' => 'upload', 'onchange' => "checkName(this, '#submitAttach')"));
        echo CHtml::submitButton(Yii::t('app','Submit'), array('id' => 'submitAttach', 'disabled' => 'disabled', 'class' => 'x2-button', 'style' => 'display:inline'));
        echo "</div>";
        if(Yii::app()->params->admin->googleIntegration){
            $auth = new GoogleAuthenticator();
            if($auth->getAccessToken()){
                echo "<div class='row'>";
                echo CHtml::label(Yii::t('app','Save to Google Drive?'), 'drive');
                echo CHtml::checkBox('drive');
                echo "</div>";
            }
        }
        echo CHtml::endForm();
        ?>
    </div>
</div>
