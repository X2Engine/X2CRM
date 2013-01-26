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

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/modcoder_excolor/jquery.modcoder.excolor.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/profileSettings.js');

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
		<!--<div class="cell">
			<?php //echo $form->checkBox($model,'showSocialMedia',array('onchange'=>'js:highlightSave();')); ?> 
			<?php //echo $form->labelEx($model,'showSocialMedia',array('style'=>'display:inline;')); ?>
			<?php //echo $form->dropDownList($model,'showSocialMedia',array(1=>Yii::t('actions','Yes'),0=>Yii::t('actions','No')),array('onchange'=>'js:highlightSave();','style'=>'width:100px')); ?>
		</div>-->
	</div>
	<div class="row">
		<div class="cell">
			<?php echo $form->labelEx($model,'startPage'); ?>
			<?php echo $form->dropDownList($model,'startPage',$menuItems,array('onchange'=>'js:highlightSave();','style'=>'min-width:140px;')); ?>
		</div>
		<div class="cell">
			<?php echo $form->labelEx($model,'resultsPerPage'); ?>
			<?php echo $form->dropDownList($model,'resultsPerPage',Profile::getPossibleResultsPerPage(),array('onchange'=>'js:highlightSave();','style'=>'width:100px')); ?>
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
		</div><?php /*
		<div class="row">
			<?php echo $form->labelEx($model,'menuBgColor'); ?>
			<?php echo $form->textField($model,'menuBgColor',array('id'=>'menuBgColor')); ?>
		</div>*/ ?>
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
	<?php /*<div class="row">
		<?php echo $form->checkBox($model,'enableFullWidth'); ?> 
		<?php echo $form->labelEx($model,'enableFullWidth',array('style'=>'display:inline;')); ?>
	</div> */ ?>
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
		<?php echo CHtml::dropDownList('visibility','public',array('1'=>Yii::t('actions','Public'),'-'=>Yii::t('actions','Private'))); ?>
		<?php echo CHtml::hiddenField('associationId',Yii::app()->user->getId()); ?>
		<?php echo CHtml::hiddenField('associationType', 'bg'); ?>
		<?php echo CHtml::fileField('upload','',array('id'=>'backgroundImg','onchange'=>"checkName();")); ?>
		<?php echo CHtml::submitButton(Yii::t('app','Submit'),array('id'=>'upload-button','disabled'=>'disabled','class'=>'x2-button')); ?>
		<?php echo CHtml::endForm(); ?>
	</div>
</div>

<div class="form">
    <div class="row">
        <h3><?php   echo Yii::t('profile','Unhide Tags'); ?></h3>
        <?php   foreach($allTags as &$tag) {
                    echo '<span class="tag unhide" tag-name="'.substr($tag['tag'],1).'">'.CHtml::link($tag['tag'],array('/search/search?term=%23'.substr($tag['tag'],1)), array('class'=>'x2-link x2-tag')).' </span>';
                } 
        ?>
    </div>
</div>

<style>
.tag{
	-moz-border-radius:4px;
	-o-border-radius:4px;
	-webkit-border-radius:4px;
	border-radius:4px;
	border-style:solid;
	border-width:1px;
	border-color:gray;
	margin:2px 2px;
	display:block;
	float:left;
	padding:2px;
	background-color:#f0f0f0;
}
.tag a {
	text-decoration:none;
	color:black;
}

</style>
<script>
    $('.unhide').mouseenter(function(){
        var tag=$(this).attr('tag-name');
        var elem=$(this);
        var content='<span class="hide-link-span"><a href="#" class="hide-link" style="color:#06C;">[+]</a></span>';
        $(content).hide().delay(500).appendTo($(this)).fadeIn(500);
        $('.hide-link').click(function(e){
           e.preventDefault();
           $.ajax({
              url:'<?php echo CHtml::normalizeUrl(array('/profile/unhideTag')); ?>'+'?tag='+tag,
              success:function(){
                  $(elem).closest('.tag').fadeOut(500);
              }
           });
           
        });
    }).mouseleave(function(){
        $('.hide-link-span').remove();
    });
</script>







