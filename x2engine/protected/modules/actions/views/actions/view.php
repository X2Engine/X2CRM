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
$authParams['assignedTo']=$model->assignedTo;
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('actions','Today\'s Actions'),'url'=>array('index')),
	array('label'=>Yii::t('actions','All My Actions'),'url'=>array('viewAll')),
	array('label'=>Yii::t('actions','Everyone\'s Actions'),'url'=>array('viewGroup')),
	array('label'=>Yii::t('actions','Create Action'),'url'=>array('create','param'=>Yii::app()->user->getName().";none:0")),
	array('label'=>Yii::t('actions','View')),
	array('label'=>Yii::t('actions','Edit Action'),'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('contacts','Share Action'),'url'=>array('shareAction','id'=>$model->id)),
	array('label'=>Yii::t('actions','Delete Action'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
),$authParams);

?>
<div class="page-title icon actions">
	<h2><?php
	if($model->associationName=='none')
		echo Yii::t('actions','Action');
	else
		echo '<span class="no-bold">',Yii::t('actions','Action'),':</span> '.$model->associationName; ?>
	</h2>
</div>
<?php
$this->renderPartial('_detailView',array('model'=>$model));

if (empty($model->type) || $model->type=='Web Lead') {
	if ($model->complete=='Yes')
		echo CHtml::link(Yii::t('actions','Uncomplete'),array('/actions/actions/uncomplete','id'=>$model->id),array('class'=>'x2-button'));
	else {
?>
<?php
if(isset($associationModel) && $model->associationType=='contacts') {
    $this->actionMenu[] = array('label'=>Yii::t('app','Send Email'),'url'=>'#','linkOptions'=>array('onclick'=>'toggleEmailForm(); return false;'));
	$this->widget('InlineEmailForm',
	array(
		'attributes'=>array(
			'to'=>'"'.$associationModel->name.'" <'.$associationModel->email.'>, ',
			// 'subject'=>'hi',
			// 'redirect'=>'contacts/'.$model->id,
			'modelName'=>'Contacts',
			'modelId'=>$associationModel->id,
		),
		'startHidden'=>true,
	)
);
}

?>
<div class="form">
	<b><?php echo Yii::t('app', 'Tags'); ?></b>
	<?php $this->widget('InlineTags',array('model'=>$model)); ?>
</div>

<div class="form" id="action-form">
	<form id="complete-action" name="complete-action" action="complete/<?php echo $model->id; ?>" method="POST">
		<b><?php echo Yii::t('actions','Completion Notes'); ?></b>
		<textarea name="note" rows="4" ></textarea>
	<div class="row buttons">
		<button type="submit" name="submit" class="x2-button" value="complete"><?php echo Yii::t('actions','Complete'); ?></button>
		<button type="submit" name="submit" class="x2-button" value="completeNew"><?php echo Yii::t('actions','Complete + New Action'); ?></button>

	</div>

	</form>
</div>
<?php
	}
}

if($model->associationId!=0 && !is_null($associationModel)) {
	if($model->associationType=='contacts') {
		echo '<div class="page-title rounded-top"><h2>'.Yii::t('actions','Contact Info').'</h2></div>';
		$this->renderPartial('application.modules.contacts.views.contacts._detailViewMini',array('model'=>$associationModel,'actionModel'=>$model));
	}

	$actionHistory=new CActiveDataProvider('Actions', array(
		'criteria'=>array(
			'order'=>'(IF (completeDate IS NULL, dueDate, completeDate)) DESC, createDate DESC',
			'condition'=>'associationId='.$model->associationId.' AND associationType=\''.$model->associationType.'\''
	)));

	$this->widget('zii.widgets.CListView', array(
		'dataProvider'=>$actionHistory,
		'itemView'=>'_view',
		'htmlOptions'=>array('class'=>'action list-view'),
		'template'=> '<h3>'.Yii::t('app','History').'</h3>{summary}{sorter}{items}{pager}',
	));
}


?>
<!--<a class="x2-button" href="#" onClick="toggleForm('#action-form',400);return false;"><span><?php echo Yii::t('app','Create Action'); ?></span></a>-->
<?php /*
	$this->widget('InlineActionForm',
			array(
				'associationType'=>'contact',
				'associationId'=>$model->associationId,
				'assignedTo'=>Yii::app()->user->getName(),
				'users'=>$users,
				'startHidden'=>true
			)
	);
	*/
?>
<script>
$('#complete-button').click(function(){
    $("form#complete-action").submit();
});
</script>
