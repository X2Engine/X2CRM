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

// Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/multiselect/js/jquery-1.3.2.min.js');
// Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/multiselect/js/jquery-ui-1.7.1.custom.min.js');
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


Yii::app()->clientScript->registerScript('renderMultiSelect',"
$(document).ready(function() {
	 $('.multiselect').multiselect();
});
",CClientScript::POS_HEAD);
$selected=array();
$unselected=array();
$fields=Fields::model()->findAllBySql("SELECT * FROM x2_fields ORDER BY modelName ASC");
foreach($fields as $field){
        $unselected[$field->id]=$field->modelName." - ".$field->attributeLabel;
}
$users=User::getNames();
unset($users['']);
unset($users['Anyone']);
unset($users['admin']);
/* x2temp */
$groups=Groups::model()->findAll();
foreach($groups as $group){
    $users[$group->id]=$group->name;
}
/* end x2temp */
?>
<div class="page-title"><h2><?php echo Yii::t('admin','Add Role'); ?></h2></div>
<div class="form">
<div style="width:600px">
    <?php echo Yii::t('admin','Roles allow you to control which fields are editable on a record and by whom.  To add a role, enter the name, a list of users, and a list of fields they are allowed to view or edit.  Any field not included will be assumed to be unavailable to users of that Role.') ?>
</div>


<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'role-form',
	'enableAjaxValidation'=>false,
        'action'=>'manageRoles',
));
?>
<div class="row">
        <?php echo $form->labelEx($model,'name'); ?>
        <?php echo $form->textField($model,'name'); ?>
        <?php echo $form->error($model,'name'); ?>
</div>
<div id="addRole">
        <?php echo $form->labelEx($model,'users'); ?>
        <?php echo $form->dropDownList($model,'users',$users,array('class'=>'multiselect','multiple'=>'multiple','size'=>7)); ?>
        <?php echo $form->error($model,'users'); ?>

    <label><?php echo Yii::t('admin','View Permissions'); ?></label>
    <?php
    echo CHtml::dropDownList('viewPermissions[]',$selected,$unselected,array('class'=>'multiselect','multiple'=>'multiple', 'size'=>8));
    ?>

<br>

    <label><?php echo Yii::t('admin','Edit Permissions'); ?></label>
    <?php
    echo CHtml::dropDownList('editPermissions[]',$selected,$unselected,array('class'=>'multiselect','multiple'=>'multiple', 'size'=>8));
    ?>
</div>

<br>
<div class="row buttons">
	<?php echo CHtml::submitButton(Yii::t('app','Submit'),array('class'=>'x2-button')); ?>
</div>
<?php $this->endWidget(); ?>
</div>

