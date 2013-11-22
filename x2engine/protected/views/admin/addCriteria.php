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

?>
<div class="page-title"><h2><?php echo Yii::t('admin','Manage Notification Criteria'); ?></h2></div>

<?php

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'criteria-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		array(

			'name'=>'modelValue',
			'header'=>Yii::t('admin','Condition'),
			'value'=>'"When a(n) ".mb_substr($data->modelType,0,-1,"UTF-8")."\'s ".$data->modelField." is ".
                            (($data->comparisonOperator=="change")?"changed":$data->comparisonOperator)
                            ." ".$data->modelValue.", ".
                            (($data->type==\'notification\')?"notify":($data->type=="action"?"create an action for":($data->type=="assignment"?"assign to":"")))
                            ." ".User::getUserLinks($data->users)',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'80%'),
		),
                array(

			'name'=>'delete',
			'header'=>Yii::t('admin','Delete'),
			'value'=>'CHtml::link("Delete","deleteCriteria/$data->id")',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'80%'),
		),

	),
));
?>
<br>
<div class="page-title"><h2><?php echo Yii::t('admin','Add Criteria for Notifications');?></h2></div>


<div class="form">
<?php echo Yii::t('admin',"To add a condition which will trigger notifications, please fill out the form below.");?><br><br>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'criteria-form',
	'enableAjaxValidation'=>false,
)); ?>

	<em><?php echo Yii::t('app','Fields with <span class="required">*</span> are required.'); ?></em><br>


	<div class="row">
            <?php echo $form->labelEx($model,'modelType'); ?>
            <?php echo $form->dropDownList($model,'modelType',Admin::getModelList(),
                array(
                'empty'=>Yii::t('admin','Select a model'),
                'ajax' => array(
                'type'=>'POST', //request type
                'url'=>$this->createUrl('/admin/getAttributes',array('criteria'=>1)), //url to call.
                //Style: CController::createUrl('currentController/methodToCall')
                'update'=>'#'.CHtml::activeId($model,'modelField'), //selector to update
                //'data'=>'js:"modelType="+$("'.CHtml::activeId($model,'modelType').'").val()'
                //leave out the data key to pass all form values through
                ))); ?>
            <?php echo $form->error($model,'modelType'); ?>
	</div>

        <div class="row">
            <?php echo $form->labelEx($model,'modelField'); ?>
            <?php echo $form->dropDownList($model,'modelField',array(),array('empty'=>Yii::t('admin','Select a model first'))); ?>
            <?php echo $form->error($model,'modelField'); ?>
        </div>

        <div class="row">
            <?php echo $form->labelEx($model,'comparisonOperator'); ?>
            <?php echo $form->dropDownList($model,'comparisonOperator',array('='=>'=','<'=>'<','>'=>'>','change'=>'On Change'),array('empty'=>Yii::t('admin','Select a comparison operator.'))); ?>
            <?php echo $form->error($model,'comparisonOperator'); ?>
        </div>

        <div class="row">
            <?php echo $form->labelEx($model,'modelValue'); ?>
            <?php echo $form->textField($model,'modelValue'); ?>
            <?php echo $form->error($model,'modelValue'); ?>
        </div>

        <div class="row">
            <?php echo $form->labelEx($model,'users'); ?>
            <?php echo $form->dropDownList($model,'users',$users,array('multiple'=>'multiple','size'=>7)); ?>
            <?php echo $form->error($model,'users'); ?>
        </div>

        <div class="row">
            <?php echo $form->labelEx($model,'type'); ?>
            <?php echo $form->dropDownList($model,'type',array('notification'=>Yii::t('admin','Notification'),'action'=>Yii::t('admin','Action'),'assignment'=>Yii::t('admin','Assignment Change'))); ?>
            <?php echo $form->error($model,'type'); ?>
        </div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button')); ?>
	</div>
<?php $this->endWidget(); ?>
</div>
