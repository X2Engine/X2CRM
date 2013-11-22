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

Yii::app()->clientScript->registerScript('feedLinkJs', "
$(document).ready(function() {
	$('.view.top-level').children().bind('click',function(e) {
		e.stopPropagation();
	});
});
",CClientScript::POS_HEAD);

$authorRecord = X2Model::model('User')->findByAttributes(array('username'=>$data->user));
if(isset($authorRecord)){
    $author = $authorRecord->name;
    $authorId = $authorRecord->id;
}else{
    $author="";
    $authorId=null;
}
$commentDataProvider=new CActiveDataProvider('Events', array(
	'criteria'=>array(
		'order'=>'timestamp ASC',
		'condition'=>"type='comment' AND associationId=$data->id",
)));
?>
<div class="view top-level">
	<div class="deleteButton">
		<?php
		if($data->user==Yii::app()->user->getName() || $data->associationId==Yii::app()->user->getId() || Yii::app()->params->isAdmin)
			echo CHtml::link('[x]',array('deletePost','id'=>$data->id,'redirect'=>Yii::app()->controller->action->id)); //,array('class'=>'x2-button') ?>
	</div>
	<?php echo CHtml::link(Yii::t('profile','Reply'),'#',array('onclick'=>"$('#addReply-".$data->id."').toggle();",'class'=>'x2-button float')); ?>

	<?php
	if($authorId != $data->associationId && $data->associationId != 0) {
		$temp=Profile::model()->findByPk($data->associationId);
		$recipient=$temp->fullName;
		$modifier=' &raquo; ';
	} else {
		$recipient='';
		$modifier='';
	}
	?>
	<?php echo CHtml::link($author,array('/profile/view','id'=>$authorId)).$modifier.CHtml::link($recipient,$data->associationId); ?> <span class="comment-age"><?php echo Formatter::timestampAge(date("Y-m-d H:i:s",$data->timestamp)); ?></span><br />
	<?php echo Media::attachmentSocialText($data->text,true,true); ?><br />
	<?php
	if(count($commentDataProvider->getData())>0){
		$this->widget('zii.widgets.CListView', array(
		'dataProvider'=>$commentDataProvider,
		'itemView'=>'../social/_view',
		'template'=>'{items}'
	));
	}

	echo CHtml::beginForm(
		'addComment',
		'get',
		array(
			'style'=>'display:none;',
			'id'=>'addReply-'.$data->id,
		));
	echo CHtml::textArea('comment','',array('style'=>'heght:40px; width:440px;display:block;clear:both;'));
	echo CHtml::hiddenField('id',$data->id);
	echo CHtml::hiddenField('redirect',Yii::app()->controller->action->id);
	echo CHtml::submitButton(Yii::t('app','Submit'),array('class'=>'x2-button float'));
	echo CHtml::endForm();


	?>
</div>
<?php


/*
<div class="view">
	<div class="deleteButton">
		<?php echo CHtml::link('[x]',array('deleteNote','id'=>$data->id)); //,array('class'=>'x2-button') ?>
		<?php //echo CHtml::link("<img src='".Yii::app()->request->baseUrl."/images/deleteButton.png' />",array("deleteNote","id"=>$data->id)); ?>
	</div>

	<b><?php echo CHtml::encode($data->getAttributeLabel('createdBy')); ?>:</b>
	<?php echo CHtml::encode($data->createdBy); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('createDate')); ?>:</b>
	<?php echo CHtml::encode($data->createDate); ?>
	<br /><br />
	<b><?php echo CHtml::encode($data->getAttributeLabel('note')); ?>:</b>
	<?php echo CHtml::encode($data->note); ?>
	<br />
</div>
*/
?>
