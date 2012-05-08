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
?><h2>Manage Criteria</h2>
Manage notification criteria.

<?php

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'criteria-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		array(

			'name'=>'modelValue',
			'header'=>Yii::t('admin','Condition'),
			'value'=>'"When a(n) ".substr($data->modelType,0,-1)."\'s ".$data->modelField." is ".
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
<br />

<h2>Add Criteria for Notifications</h2>
To add a condition which will trigger notifications, please fill out the form below.<br /><br />


<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'criteria-form',
	'enableAjaxValidation'=>false,
)); ?>

	<em><?php echo Yii::t('app','Fields with <span class="required">*</span> are required.'); ?></em><br />


	<div class="row">
            <?php echo $form->labelEx($model,'modelType'); ?>
            <?php echo $form->dropDownList($model,'modelType',array('Actions'=>'Actions','Contacts'=>'Contacts','Sales'=>'Sales','Accounts'=>'Accounts','Docs'=>'Docs'),
                array(
                'empty'=>'Select a model',
                'ajax' => array(
                'type'=>'POST', //request type
                'url'=>$this->createUrl('admin/getAttributes'), //url to call.
                //Style: CController::createUrl('currentController/methodToCall')
                'update'=>'#'.CHtml::activeId($model,'modelField'), //selector to update
                //'data'=>'js:"modelType="+$("'.CHtml::activeId($model,'modelType').'").val()' 
                //leave out the data key to pass all form values through
                ))); ?>
            <?php echo $form->error($model,'modelType'); ?>
	</div>
        
        <div class="row">
            <?php echo $form->labelEx($model,'modelField'); ?>
            <?php echo $form->dropDownList($model,'modelField',array(),array('empty'=>'Select a model first')); ?>
            <?php echo $form->error($model,'modelField'); ?>
        </div>
        
        <div class="row">
            <?php echo $form->labelEx($model,'comparisonOperator'); ?>
            <?php echo $form->dropDownList($model,'comparisonOperator',array('='=>'=','<'=>'<','>'=>'>','change'=>'On Change'),array('empty'=>'Select a comparison operator.')); ?>
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
            <?php echo $form->dropDownList($model,'type',array('notification'=>'Notification','action'=>'Action','assignment'=>'Assignment Change')); ?>
            <?php echo $form->error($model,'type'); ?>
        </div>
        
	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button')); ?>
	</div>
<?php $this->endWidget(); ?>
</div>
