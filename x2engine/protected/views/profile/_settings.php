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
<div class="form">
<?php
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
				if(isset($model->timeZone)){
					
				}else{
					$model->timeZone="Europe/London";
				}
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
		<div class="row buttons">
			<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('id'=>'save-changes','class'=>'x2-button')); ?>
		</div>
	</div>
<?php $this->endWidget(); ?>
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
		)); ?><br />
	</div>
</div>
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








