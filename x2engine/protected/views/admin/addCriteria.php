<h2>Manage Criteria</h2>
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
			'value'=>'$data->modelType." ".$data->modelField." ".$data->comparisonOperator." ".$data->modelValue',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'80%'),
		),
                array(

			'name'=>'users',
			'header'=>Yii::t('admin','Users'),
			'value'=>'UserChild::getUserLinks($data->users)',
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
        
	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button')); ?>
	</div>
<?php $this->endWidget(); ?>
</div>
