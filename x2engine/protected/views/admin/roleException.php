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
<div class="page-title"><h2><?php echo Yii::t('admin','Add Exception'); ?></h2></div>
<div class="form">
<div style="width:500px">
    <?php echo Yii::t('admin',"Adding an exception will alter a Role's behavior while the contact is on a particular workflow stage.  You can change which fields are editable by whom to be dependent on where a contact is in workflow this way.") ?>
</div><br>
<?php
//Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/multiselect/js/ui.multiselect.js');
//Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl().'/js/multiselect/css/ui.multiselect.css','screen, projection');
//Yii::app()->clientScript->registerCss('multiselectCss',"
//.multiselect {
//	width: 460px;
//	height: 200px;
//}
//#switcher {
//	margin-top: 20px;
//}
//",'screen, projection');
$list=Roles::model()->findAll();
$names=array();
foreach($list as $role){
    $names[$role->name]=$role->name;
}
?>
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'roleException-form',
	'enableAjaxValidation'=>false,
        'action'=>'roleException',
)); ?>

        <label><?php echo Yii::t('workflow','Workflow'); ?></label>
        <?php echo CHtml::dropDownList('workflow','',$workflows,array(
        'empty'=>'Select a workflow',
                'ajax' => array(
                'type'=>'POST', //request type
                'url'=>CController::createUrl('/admin/getWorkflowStages'), //url to call.
                //Style: CController::createUrl('currentController/methodToCall')
                'update'=>'#workflowStages', //selector to update
                //'data'=>'js:"modelType="+$("'.CHtml::activeId($model,'modelType').'").val()'
                //leave out the data key to pass all form values through
                ))); ?>
        <label><?php echo Yii::t('workflow','Workflow Stage'); ?></label>
        <?php echo CHtml::dropDownList('workflowStages','',array(),array('id'=>'workflowStages','empty'=>'Select a workflow first'));?>
        <div class="row">
            <label>Role Name</label>
            <?php echo $form->dropDownList($model,'name',$names,array(
                'empty'=>Yii::t('admin','Select a role'),
                'ajax' => array(
                'type'=>'POST', //request type
                'url'=>CController::createUrl('/admin/getRole'), //url to call.
                //Style: CController::createUrl('currentController/methodToCall')
                'update'=>'#roleFormTwo', //selector to update
                'complete'=>"function(){
                    $('.multiselect').multiselect();
                    $('#users').hide();
                }"
                //'data'=>'js:"modelType="+$("'.CHtml::activeId($model,'modelType').'").val()'
                //leave out the data key to pass all form values through
                ))); ?>
            <?php echo $form->error($model,'name'); ?>
        </div>

        <div id="roleFormTwo">

        </div>
        <br />
	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Save'):Yii::t('app','Save'),array('class'=>'x2-button')); ?>
	</div>
<?php $this->endWidget(); ?>
</div>
