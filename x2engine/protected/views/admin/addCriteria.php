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
			'value'=>'"When a(n) ".CHtml::encode(mb_substr($data->modelType,0,-1,"UTF-8"))."\'s ".CHtml::encode($data->modelField)." is ".
                            (($data->comparisonOperator=="change")?"changed":CHtml::encode($data->comparisonOperator))
                            ." ".CHtml::encode($data->modelValue).", ".
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
            <?php echo $form->dropDownList($model,'modelType',Fields::getDisplayedModelNamesList(),
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
