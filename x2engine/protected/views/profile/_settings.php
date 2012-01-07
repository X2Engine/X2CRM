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

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/modcoder_excolor/jquery.modcoder.excolor.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/profileSettings.js');

Yii::app()->clientScript->registerScript('backgroundUploader',"function showAttach() {
	e=document.getElementById('attachments');
	if(e.style.display=='none')
		e.style.display='block';
	else
		e.style.display='none';
}
var ar_ext = ['png', 'jpg'];        // array with allowed extensions

function checkName() {
// - www.coursesweb.net
	// get the file name and split it to separe the extension
	var name = $('#backgroundImg').val();
	var ar_name = name.split('.');

	// check the file extension
	var re = 0;
	for(var i=0; i<ar_ext.length; i++) {
		if(ar_ext[i] == ar_name[1]) {
			re = 1;
			break;
		}
	}
	// if re is 1, the extension is in the allowed list
	if(re==1) {
		// enable submit
		$('#upload-button').attr('disabled','');
	}
	else {
		// delete the file name, disable Submit, Alert message
		$('#backgroundImg').val('');
		$('#upload-button').attr('disabled','disabled');
		alert('\".'+ ar_name[1]+ '\" is not an file type allowed for upload');
	}
}",CClientScript::POS_HEAD);

Yii::app()->clientScript->registerScript('backgroundSetDelete',"
function setBackground(filename){
	$.ajax({
		url: '".CHtml::normalizeUrl(array('profile/setBackground'))."',
		type: 'post',
		data: 'name='+filename,
		success: function(response) {
			if(response=='success') {
				if(filename=='') {
					$('#bg').hide();
				} else {
					$('#bg').attr('src','".Yii::app()->getBaseUrl().'/uploads/'."'+filename);
					$('#bg').show();
					$(window).trigger('resize');
				}
			}
		}
	});
}
function deleteBackground(id,filename) {
	$.ajax({
		url: '".CHtml::normalizeUrl(array('profile/deleteBackground'))."',
		type: 'get',
		data: 'id='+id,
		success: function(response) {
			if(response=='success') {
				$('#background_'+id).hide();
				if($('#bg').attr('src')=='".Yii::app()->getBaseUrl().'/uploads/'."'+filename)
					$('#bg').hide();
			}
		}
	});
}
",CClientScript::POS_HEAD);

$form=$this->beginWidget('CActiveForm', array(
	'id'=>'settings-form',
	'enableAjaxValidation'=>false,
)); ?>
<div class="form">
	<?php echo $form->errorSummary($model); ?>

	<div class="row" style="margin-bottom:10px;">
		<div class="cell">
			<?php echo $form->checkBox($model,'allowPost',array('onchange'=>'js:highlightSave();')); ?> 
			<?php echo $form->labelEx($model,'allowPost',array('style'=>'display:inline;')); ?>
		</div>
		<div class="cell">
			<?php echo $form->checkBox($model,'showSocialMedia',array('onchange'=>'js:highlightSave();')); ?> 
			<?php echo $form->labelEx($model,'showSocialMedia',array('style'=>'display:inline;')); ?>
			<?php //echo $form->dropDownList($model,'showSocialMedia',array(1=>Yii::t('actions','Yes'),0=>Yii::t('actions','No')),array('onchange'=>'js:highlightSave();','style'=>'width:100px')); ?>
		</div>
		<div class="cell">
			<?php echo $form->checkBox($model,'showWorkflow',array('onchange'=>'js:highlightSave();')); ?> 
			<?php echo $form->labelEx($model,'showWorkflow',array('style'=>'display:inline;')); ?>
		</div>
	</div>
	<div class="row">
		<div class="cell">
			<?php echo $form->labelEx($model,'startPage'); ?>
			<?php echo $form->dropDownList($model,'startPage',$menuItems,array('onchange'=>'js:highlightSave();','style'=>'min-width:140px;')); ?>
		</div>
		<div class="cell">
			<?php echo $form->labelEx($model,'resultsPerPage'); ?>
			<?php echo $form->dropDownList($model,'resultsPerPage',array(10=>'10',15=>'15',20=>'20',30=>'30',50=>'50'),array('onchange'=>'js:highlightSave();','style'=>'width:100px')); ?>
		</div>

	</div>
	<div class="row">
		<div class="cell">
			<?php echo $form->labelEx($model,'language'); ?>
			<?php echo $form->dropDownList($model,'language',$languages,array('onchange'=>'js:highlightSave();')); ?>
		</div>
		<div class="cell">
			<?php 
			if(!isset($model->timeZone))
				$model->timeZone="Europe/London";
			?>
			<?php echo $form->labelEx($model,'timeZone'); ?>
			<?php echo $form->dropDownList($model,'timeZone',$times,array('onchange'=>'js:highlightSave();')); ?>
		</div>
	</div>
	<div class="cell">
		<h3><?php echo Yii::t('app','Theme'); ?></h3>
		<div class="row">
			<?php echo $form->labelEx($model,'backgroundColor'); ?>
			<?php echo $form->textField($model,'backgroundColor',array('id'=>'backgroundColor')); ?>
		</div>
		<div class="row">
			<?php echo $form->labelEx($model,'menuBgColor'); ?>
			<?php echo $form->textField($model,'menuBgColor',array('id'=>'menuBgColor')); ?>
		</div>
		<div class="row">
			<?php echo $form->labelEx($model,'menuTextColor'); ?>
			<?php echo $form->textField($model,'menuTextColor',array('id'=>'menuTextColor')); ?>
		</div>
	</div>
	<div class="cell">
		<h3><?php echo Yii::t('profile','Background Image'); ?></h3>
		
		<?php
		echo CHtml::link(
			Yii::t('app','None'),
			'#',
			array(
				'onclick'=>"setBackground(''); return false;"
			)
		);
		$this->widget('zii.widgets.CListView', array(
			'dataProvider'=>$myBackgrounds,
			'template'=>'{items}{pager}',
			'itemView'=>'//media/_background',	// refers to the partial view named '_post'
			'sortableAttributes'=>array(
				'fileName',
				'createDate',
			),
		)); ?>
	</div>
	<div class="row">
		<?php echo $form->checkBox($model,'enableBgFade',array('onchange'=>'js:highlightSave();')); ?> 
		<?php echo $form->labelEx($model,'enableBgFade',array('style'=>'display:inline;')); ?>
	</div>
	<br>
	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('id'=>'save-changes','class'=>'x2-button')); ?>
	</div>
</div>
<?php $this->endWidget(); ?>
<div class="form">
	<div class="row">
		<h3><?php echo Yii::t('profile','Upload a Background'); ?></h3>
		<?php echo CHtml::form(array('site/upload','id'=>$model->id),'post',array('enctype'=>'multipart/form-data')); ?>
		<?php echo CHtml::dropDownList('type','bg',array('bg'=>Yii::t('actions','Public'),'bg-private'=>Yii::t('actions','Private'))); ?>
		<?php echo CHtml::hiddenField('associationId',Yii::app()->user->getId()); ?>
		<?php echo CHtml::fileField('upload','',array('id'=>'backgroundImg','onchange'=>"checkName();")); ?>
		<?php echo CHtml::submitButton(Yii::t('app','Submit'),array('id'=>'upload-button','disabled'=>'disabled','class'=>'x2-button')); ?>
		<?php echo CHtml::endForm(); ?>
	</div>
</div>








