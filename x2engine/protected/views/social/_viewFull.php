<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




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
