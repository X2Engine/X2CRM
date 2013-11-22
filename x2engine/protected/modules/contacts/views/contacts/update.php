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

$authParams['assignedTo'] = $model->assignedTo;
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('contacts','All Contacts'),'url'=>array('index')),
	array('label'=>Yii::t('contacts','Lists'),'url'=>array('lists')),
	array('label'=>Yii::t('contacts','Create Contact'),'url'=>array('create')),
	array('label'=>Yii::t('contacts','View'),'url'=>array('view', 'id'=>$model->id)),
    array('label'=>Yii::t('contacts','Edit Contact')),
    array('label'=>Yii::t('contacts','Save Contact'),'url'=>'#','linkOptions'=>array('onclick'=>"$('#save-button').click();return false;")),
	array('label'=>Yii::t('contacts','Share Contact'),'url'=>array('shareContact','id'=>$model->id)),
	array('label'=>Yii::t('contacts','Delete Contact'),'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>Yii::t('app', 'Quick Create'), 'url'=>array('/site/createRecords', 'ret'=>'contacts'), 'linkOptions'=>array('id'=>'x2-create-multiple-records-button', 'class'=>'x2-hint', 'title'=>Yii::t('app', 'Create a Contact, Account, and Opportunity.'))),
),$authParams);

?>
<?php
	if (!IS_ANDROID && !IS_IPAD) {
		echo '
<div class="page-title-placeholder"></div>
<div class="page-title-fixed-outer">
	<div class="page-title-fixed-inner">
		';
	}
?>
		<div class="page-title icon contacts">
			<h2><span class="no-bold"><?php echo Yii::t('app','Update:'); ?></span> <?php echo $model->name; ?></h2>
			<?php echo CHtml::link(Yii::t('app','Save'),'#',array('class'=>'x2-button highlight right','onclick'=>'$("#save-button").click();return false;')); ?>
		</div>
<?php
	if (!IS_ANDROID && !IS_IPAD) {
		echo '
	</div>
</div>
		';
	}
?>
<?php echo $this->renderPartial('application.components.views._form', array('model'=>$model, 'users'=>$users,'modelName'=>'contacts')); ?>
<?php
$createAccountUrl = $this->createUrl('/accounts/accounts/create');
Yii::app()->clientScript->registerScript('create-account', "
	$(function() {
		$('.create-account').data('createAccountUrl', '$createAccountUrl');
		$('.create-account').qtip({content: 'Create a new Account for this Contact.'});
		// init create action button
		$('.create-account').initCreateAccountDialog();
	});
");
?>
