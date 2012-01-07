<h3>Edit Role</h3>
<div class="form">
<?php
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/multiselect/js/ui.multiselect.js');
Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl().'/js/multiselect/css/ui.multiselect.css','screen, projection');
Yii::app()->clientScript->registerCss('multiselectCss',"
.multiselect {
	width: 460px;
	height: 200px;
}
#switcher {
	margin-top: 20px;
}
",'screen, projection');
$list=Roles::model()->findAll();
$names=array();
foreach($list as $role){
    $names[$role->name]=$role->name;
}
?>
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'role-form',
	'enableAjaxValidation'=>false,
        'action'=>'editRole',
)); ?>

	<em><?php echo Yii::t('app','Fields with <span class="required">*</span> are required.'); ?></em><br />
        
        <div class="row">
            <?php echo $form->labelEx($model,'name'); ?>
            <?php echo $form->dropDownList($model,'name',$names,array(
                'empty'=>'Select a role',
                'ajax' => array(
                'type'=>'POST', //request type
                'url'=>CController::createUrl('admin/getRole'), //url to call.
                //Style: CController::createUrl('currentController/methodToCall')
                'update'=>'#roleForm', //selector to update
                'complete'=>"function(){
                    $('.multiselect').multiselect();
                }"
                //'data'=>'js:"modelType="+$("'.CHtml::activeId($model,'modelType').'").val()' 
                //leave out the data key to pass all form values through
                ))); ?>
            <?php echo $form->error($model,'name'); ?>
        </div>
        
        <div id="roleForm">
            
        </div>
        <br />
	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Save'):Yii::t('app','Save'),array('class'=>'x2-button')); ?>
	</div>
<?php $this->endWidget(); ?>
</div>