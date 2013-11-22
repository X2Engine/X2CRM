<?php
/*********************************************************************************
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
 ********************************************************************************/

$canEdit = $model->id==Yii::app()->user->getId() || Yii::app()->params->isAdmin;

$this->actionMenu = array(
	array('label'=>Yii::t('profile','View Profile')),
	array('label'=>Yii::t('profile','Edit Profile'), 'url'=>array('update','id'=>$model->id),'visible'=>$canEdit),
	array('label'=>Yii::t('profile','Change Settings'),'url'=>array('settings','id'=>$model->id),'visible'=>($model->id==Yii::app()->user->getId())),
	array('label'=>Yii::t('profile','Change Password'),'url'=>array('changePassword','id'=>$model->id),'visible'=>($model->id==Yii::app()->user->getId())),
	array('label'=>Yii::t('profile','Reset Widgets'),'url'=>array('resetWidgets','id'=>$model->id),'visible'=>($model->id==Yii::app()->user->getId())),
	array('label'=>Yii::t('profile','Manage Apps'),'url'=>array('manageCredentials'),'visible'=>($model->id==Yii::app()->user->getId()))
		);

Yii::app()->clientScript->registerScript('highlightButton','
$("#feed-form textarea").bind("focus blur",function(){ toggleText(this); })
	.change(function(){
		if($(this).val()=="")
			$("#save-button").removeClass("highlight");
		else
			$("#save-button").addClass("highlight");
	});
',CClientScript::POS_READY);
?>
<div class="page-title icon profile">
	<h2><span class="no-bold"><?php echo Yii::t('profile','Profile:'); ?> </span><?php echo $model->fullName; ?></h2>
</div>
<?php $this->renderPartial('_detailView',array('model'=>$model)); ?>
<?php //echo CHtml::mailto(Yii::t('profile','Send E-Mail'),$model->emailAddress,array('class'=>'x2-button')); ?>

<div class="form">
	<?php $feed=new Events; 
	
	$feed->text = Yii::t('app','Enter text here...');

	$form = $this->beginWidget('CActiveForm', array(
	'id'=>'feed-form',
	'enableAjaxValidation'=>false,
	'action'=>array('addPost','id'=>$model->id,'redirect'=>'view'),
)); ?>
	<div class="float-row">
		<?php
		if($model->allowPost==1)
			echo $form->textArea($feed,'text',array('style'=>'width:558px;height:50px;color:#aaa;display:block;clear:both;'));
		else
			echo "This user does not allow posting on their feed.";
		if($model->allowPost==1) {
			echo $form->dropDownList($feed,'visibility',array(1=>Yii::t('actions','Public'),0=>Yii::t('actions','Private')));
			echo $form->dropDownList($feed,'subtype',json_decode(Dropdowns::model()->findByPk(113)->options,true));
            echo CHtml::submitButton(Yii::t('app','Post'),array('class'=>'x2-button','id'=>'save-button'));
			echo CHtml::button(Yii::t('app','Attach A File/Photo'),array('class'=>'x2-button','type'=>'button','onclick'=>"$('#attachments').toggle();return false;"));
		}
		?>
	</div>
	<?php $this->endWidget(); ?>
</div>

<div id="attachments" style="display:none;">
<?php $this->widget('Attachments',array('associationType'=>'feed', 'associationId'=>$model->id)); ?>
</div>
<?php
$this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'../social/_viewFull',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/listview',
	// 'template'=> '<h3>'.Yii::t('profile','Feed').'</h3>{summary}{sorter}{items}{pager}',
	'template'=> '{sorter}{items}{pager}',
));
?>
