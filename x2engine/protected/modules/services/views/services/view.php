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
 
$authParams['assignedTo']=$model->assignedTo;
$menuItems = array(
	array('label'=>Yii::t('services','All Cases'), 'url'=>array('index')),
	array('label'=>Yii::t('services','Create Case'), 'url'=>array('create')),
	array('label'=>Yii::t('services','View')),
	array('label'=>Yii::t('services','Edit Case'), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('services','Delete Case'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>Yii::t('app','Send Email'),'url'=>'#','linkOptions'=>array('onclick'=>'toggleEmailForm(); return false;')),
	array('label'=>Yii::t('app','Attach a File/Photo'),'url'=>'#','linkOptions'=>array('onclick'=>'toggleAttachmentForm(); return false;')),
	array('label'=>Yii::t('services','Create Web Form'), 'url'=>array('createWebForm')),
);

$this->actionMenu = $this->formatMenu($menuItems, $authParams);

?>
<div id="main-column" class="half-width">
<div class="page-title">
<?php //echo CHtml::link('['.Yii::t('contacts','Show All').']','javascript:void(0)',array('id'=>'showAll','class'=>'right hide','style'=>'text-decoration:none;')); ?>
<?php //echo CHtml::link('['.Yii::t('contacts','Hide All').']','javascript:void(0)',array('id'=>'hideAll','class'=>'right','style'=>'text-decoration:none;')); ?>
	<h2><?php echo Yii::t('services','Case {n}',array('{n}'=>$model->id)); ?></h2>
	<?php //if(Yii::app()->user->checkAccess('ServicesUpdate',$authParams)){ ?>
	<a class="x2-button right" href="<?php echo $this->createUrl('update',array('id'=>$model->id));?>"><?php echo Yii::t('app','Edit');?></a>
	<?php //} ?>
</div>
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'services-form',
	'enableAjaxValidation'=>false,
	'action'=>array('saveChanges','id'=>$model->id),
));
$this->renderPartial('application.components.views._detailView',array('model'=>$model,'form'=>$form,'modelName'=>'services'));

?>

<?php $childCases = Services::model()->findAllByAttributes(array('parentCase'=>$model->id)); ?>
<?php if($childCases) { ?>
	<div id="service-child-case-wrapper" class="x2-layout form-view">
	<div class="formSection showSection">
		<div class="formSectionHeader">
			<span class="sectionTitle"><?php echo Yii::t('services', 'Child Cases'); ?></span>
		</div>
		<div id="parent-case" class="tableWrapper" style="min-height: 75px; padding: 5px;">
			<?php
				$comma = false;
				foreach($childCases as $c) {
					if($comma) { // skip the first comma
						echo ", ";
					} else {
						$comma = true;
					}
					echo $c->createLink();
				}
			?>
		</div>
	</div>
	</div>
<?php } ?>

<?php 
$this->endWidget();

if($model->contactId) { // every service case should have a contact associated with it
	$contact = Contacts::model()->findByPk($model->contactId);
	if($contact) { // if associated contact exists, display mini contact view
		echo '<h2>'.Yii::t('actions','Contact Info').'</h2>';
		$this->renderPartial('application.modules.contacts.views.contacts._detailViewMini',array('model'=>$contact, 'serviceModel'=>$model));
	}
}
?>

<div class="form">
	<b><?php echo Yii::t('app', 'Tags'); ?></b>
	<?php $this->widget('InlineTags', array('model'=>$model, 'modelName'=>'services')); ?>
</div>

<div class="form">
	<b><?php echo Yii::t('workflow', 'Workflow'); ?></b>
	<?php $this->widget('WorkflowStageDetails',array('model'=>$model,'modelName'=>'services','currentWorkflow'=>$currentWorkflow)); ?>
</div>

<?php $this->widget('Attachments',array('associationType'=>'services','associationId'=>$model->id,'startHidden'=>true)); ?>

<?php
if($model->contactId) {
	$contact = Contacts::model()->findByPk($model->contactId);
	if($contact) { // if associated contact exists, setup inline email form
		$this->widget('InlineEmailForm',
			array(
				'attributes'=>array(
					'to'=>'"'.$contact->name.'" <'.$contact->email.'>, ',
					// 'subject'=>'hi',
					// 'redirect'=>'contacts/'.$model->id,
					'modelName'=>'Services',
					'modelId'=>$model->id,
				),
				'startHidden'=>true,
			)
		);
	}
}
?>

</div>
<div class="history half-width">
<?php
$this->widget('Publisher',
	array(
		'associationType'=>'services',
		'associationId'=>$model->id,
		'assignedTo'=>Yii::app()->user->getName(),
		'halfWidth'=>true
	)
);

$this->widget('History',array('associationType'=>'services','associationId'=>$model->id));
?>
</div>

<?php $this->widget('CStarRating',array('name'=>'rating-js-fix', 'htmlOptions'=>array('style'=>'display:none;'))); ?>

