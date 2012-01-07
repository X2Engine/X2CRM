<h3>Edit Dropdown</h3>
<div class="form">
<?php
$list=Dropdowns::model()->findAll();
$names=array();
foreach($list as $dropdown){
    $names[$dropdown->name]=$dropdown->name;
}
?>
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'criteria-form',
	'enableAjaxValidation'=>false,
        'action'=>'editDropdown',
)); ?>

	<em><?php echo Yii::t('app','Fields with <span class="required">*</span> are required.'); ?></em><br />
        
        <div class="row">
            <?php echo $form->labelEx($model,'name'); ?>
            <?php echo $form->dropDownList($model,'name',$names,array(
                'empty'=>'Select a dropdown',
                'ajax' => array(
                'type'=>'POST', //request type
                'url'=>CController::createUrl('admin/getDropdown'), //url to call.
                //Style: CController::createUrl('currentController/methodToCall')
                'update'=>'#options', //selector to update
                //'data'=>'js:"modelType="+$("'.CHtml::activeId($model,'modelType').'").val()' 
                //leave out the data key to pass all form values through
                ))); ?>
            <?php echo $form->error($model,'name'); ?>
        </div>
        
        <div id="workflow-stages">
            <label>Dropdown Options</label>
            <ol id="options">
            
            </ol>
        </div>
        <a href="javascript:void(0)" onclick="addStage();" class="add-workflow-stage">[<?php echo Yii::t('workflow','Add'); ?>]</a>
        
	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Save'):Yii::t('app','Save'),array('class'=>'x2-button')); ?>
	</div>
<?php $this->endWidget(); ?>
</div>