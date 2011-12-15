<h3>Customize Fields</h3>
This form will allow you to rename or show/hide any field on the four major models (Contacts, Actions, Sales and Accounts).<br /><br />
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'criteria-form',
	'enableAjaxValidation'=>false,
        'action'=>'customizeFields',
)); ?>

	<em><?php echo Yii::t('app','Fields with <span class="required">*</span> are required.'); ?></em><br />


	<div class="row">
            <?php echo $form->labelEx($model,'modelName'); ?>
            <?php echo $form->dropDownList($model,'modelName',array('Actions'=>'Actions','Contacts'=>'Contacts','Sales'=>'Sales','Accounts'=>'Accounts'),
                array(
                'empty'=>'Select a model',
                'ajax' => array(
                'type'=>'POST', //request type
                'url'=>CController::createUrl('admin/getAttributes'), //url to call.
                //Style: CController::createUrl('currentController/methodToCall')
                'update'=>'#dynamicFields', //selector to update
                //'data'=>'js:"modelType="+$("'.CHtml::activeId($model,'modelType').'").val()' 
                //leave out the data key to pass all form values through
                ))); ?>
            <?php echo $form->error($model,'modelName'); ?>
	</div>
        
        <div class="row">
            <?php echo $form->labelEx($model,'fieldName'); ?>
            <?php echo $form->dropDownList($model,'fieldName',array(),array('empty'=>'Select a model first','id'=>'dynamicFields')); ?>
            <?php echo $form->error($model,'fieldName'); ?>
        </div>
        <br />
        <div class="row">
            <div>
            Please enter the new name for your chosen field.<br />
            Leave blank if you don't want to change it.</div><br />
            <?php echo $form->labelEx($model,'attributeLabel'); ?>
            <?php echo $form->textField($model,'attributeLabel'); ?>
            <?php echo $form->error($model,'attributeLabel'); ?>
        </div>
        
        <div class="row">
            <?php echo $form->labelEx($model,'visible'); ?>
            <?php echo $form->dropDownList($model,'visible',array('1'=>'Show','0'=>'Hide')); ?>
            <?php echo $form->error($model,'visible'); ?>
        </div>
        
	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Save'):Yii::t('app','Save'),array('class'=>'x2-button')); ?>
	</div>
<?php $this->endWidget(); ?>
</div>