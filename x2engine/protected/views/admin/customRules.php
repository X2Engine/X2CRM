<h2>Manage Lead Routing</h2>
Manage routing criteria. This setting is only required if lead distribution is set to "Custom Round Robin"

<?php

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'routing-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		array(

			'name'=>'value',
			'header'=>Yii::t('admin','Criteria'),
			'value'=>'$data->field."=".$data->value',
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
			'value'=>'CHtml::link("Delete","deleteRouting/$data->id")',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'80%'),
		),
		
	),
));
?>
<br />

<h2>Add Criteria for Lead Routing</h2>
To add a condition which will affect how leads are distributed, please fill out the form below.<br /><br />


<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'routing-form',
	'enableAjaxValidation'=>false,
)); ?>

	<em><?php echo Yii::t('app','Fields with <span class="required">*</span> are required.'); ?></em><br />
        
        <div class="row">
            <?php echo $form->labelEx($model,'field'); ?>
            <?php echo $form->dropDownList($model,'field',CActiveRecord::model('ContactChild')->attributeLabels()); ?>
            <?php echo $form->error($model,'field'); ?>
        </div>
        
        <div class="row">
            <?php echo $form->labelEx($model,'value'); ?>
            <?php echo $form->textField($model,'value'); ?>
            <?php echo $form->error($model,'value'); ?>
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
