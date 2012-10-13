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
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
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

$canEdit = $model->id==Yii::app()->user->getId() || Yii::app()->user->checkAccess('AdminIndex');

$this->actionMenu = array(
	array('label'=>Yii::t('profile','View Profile')),
	array('label'=>Yii::t('profile','Update Profile'), 'url'=>array('update','id'=>$model->id),'visible'=>$canEdit),
	array('label'=>Yii::t('profile','Change Settings'),'url'=>array('settings','id'=>$model->id),'visible'=>($model->id==Yii::app()->user->getId())),
	array('label'=>Yii::t('profile','Change Password'),'url'=>array('changePassword','id'=>$model->id),'visible'=>($model->id==Yii::app()->user->getId()))
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

<h2><?php echo Yii::t('profile','Profile:'); ?> <b><?php echo $model->fullName; ?></b></h2>

<?php $this->renderPartial('_detailView',array('model'=>$model)); ?>
<?php //echo CHtml::mailto(Yii::t('profile','Send E-Mail'),$model->emailAddress,array('class'=>'x2-button')); ?>

<div class="form">
	<?php $feed=new Social; 
	
	$feed->data = Yii::t('app','Enter text here...');

	$form = $this->beginWidget('CActiveForm', array(
	'id'=>'feed-form',
	'enableAjaxValidation'=>false,
	'action'=>array('addPost','id'=>$model->id,'redirect'=>'view'),
)); ?>
	<div class="float-row">
		<?php
		if($model->allowPost==1)
			echo $form->textArea($feed,'data',array('style'=>'width:558px;height:50px;color:#aaa;display:block;clear:both;'));
		else
			echo "This user does not allow posting on their feed.";
		if($model->allowPost==1) {
			echo $form->dropDownList($feed,'private',array(0=>Yii::t('actions','Public'),1=>Yii::t('actions','Private')));
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
	'template'=> '<h3>'.Yii::t('profile','Feed').'</h3>{summary}{sorter}{items}{pager}',
));
?>