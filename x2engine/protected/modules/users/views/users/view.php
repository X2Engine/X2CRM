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

$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('profile','Social Feed'),'url'=>array('/site/whatsNew')),
	array('label'=>Yii::t('users','Manage Users'), 'url'=>array('admin')),
	array('label'=>Yii::t('users','Create User'), 'url'=>array('create')),
	array('label'=>Yii::t('users','Invite Users'), 'url'=>array('inviteUsers')),
	array('label'=>Yii::t('users','View User')),
	array('label'=>Yii::t('profile','View Profile'),'url'=>array('/profile/view','id'=>$model->id)),
	array('label'=>Yii::t('users','Update User'), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('users','Delete User'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('app','Are you sure you want to delete this item?'))),
));
?>
<div class="page-title icon users">
	<h2><span class="no-bold"><?php echo Yii::t('users','User:'); ?></span> <?php echo $model->firstName,' ',$model->lastName; ?></h2>
</div>
<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'baseScriptUrl'=>Yii::app()->theme->getBaseUrl().'/css/detailview',
	'attributes'=>array(
		'firstName',
		'lastName',
		'username',
		'title',
		'department',
		'officePhone',
		'cellPhone',
		'homePhone',
		'address',
		'backgroundInfo',
		'emailAddress',
		array(
			'name'=>'status',
			'type'=>'raw',
			'value'=>$model->status==1?"Active":"Inactive",
		),
	),
)); ?>
<br>
<div class="page-title rounded-top"><h2><?php echo Yii::t('users','Action History'); ?></h2></div>


<?php
foreach($actionHistory as $action) {
	$this->widget('zii.widgets.CDetailView', array(
		'data'=>$action,
		'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/detailview',
		'attributes'=>array(
			array(
				'label'=>'Action Description',
				'type'=>'raw',
				'value'=>CHtml::link(CHtml::encode($action->actionDescription),
							 array('/actions/actions/view','id'=>$action->id)),
			),
			'assignedTo',
                        array(
                                'name'=>'dueDate',
				'label'=>'Due Date',
				'type'=>'raw',
				'value'=>date("F j, Y",$action->dueDate),
			),
			array(
				'label'=>'Complete',
				'type'=>'raw',
				'value'=>CHtml::tag("b",array(),CHtml::tag("font",$htmlOptions=array('color'=>'green'),CHtml::encode($action->complete)))
			),
			'priority',
			'type',
                        array(
                                'name'=>'createDate',
				'label'=>'Create Date',
				'type'=>'raw',
				'value'=>date("F j, Y",$action->createDate),
			),
		),
	));
}
?><br /><br />
