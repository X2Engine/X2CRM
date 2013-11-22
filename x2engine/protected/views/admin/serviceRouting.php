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
<div class="span-16">
<div class="page-title"><h2><?php echo Yii::t('admin','Set Service Routing Options'); ?></h2></div>
<div class="form">
<?php echo Yii::t('admin','Change how service cases are assigned to users.'); ?>
<br><br>
<?php
$form=$this->beginWidget('CActiveForm', array(
		'id'=>'service-form',
		'enableAjaxValidation'=>false,
	));
?>

<?php echo $form->labelEx($admin,'serviceDistribution'); ?>
<?php echo $form->dropDownList($admin,'serviceDistribution',array(
    ''=>Yii::t('admin','Free For All'),
    'trueRoundRobin'=>Yii::t('admin','Round Robin'),
    'singleUser'=>Yii::t('admin','Single User'),
    'singleGroup'=>Yii::t('admin','Single Group'),
),array('id'=>"service-source-select"));?>

<div id="user-list" style="<?php echo (!empty($admin->serviceDistribution) && $admin->serviceDistribution=="singleUser")?"":"display:none;" ?>">
	    <label><?php echo Yii::t('admin','Selected User'); ?></label>
	<?php echo $form->dropDownList($admin,'srrId',User::getUserIds()); ?>
</div>

<div id="group-list" style="<?php echo (!empty($admin->serviceDistribution) && $admin->serviceDistribution=="singleGroup")?"":"display:none;" ?>">
	<label><?php echo Yii::t('admin','Selected Group'); ?></label>
	<?php echo $form->dropDownList($admin,'sgrrId',Groups::getNames()); ?>
</div>

    <?php echo $form->labelEx($admin,'serviceOnlineOnly'); ?>
<?php echo $form->dropDownList($admin,'serviceOnlineOnly',array(
    '0'=>Yii::t('app','No'),
    '1'=>Yii::t('app','Yes'),
)); ?>

<?php echo CHtml::submitButton(Yii::t('app','Save'),array('class'=>'x2-button'))."\n";?>
<?php $this->endWidget();?></div>
<div class="form">
<b><?php echo Yii::t('admin','Free For All');?></b><br>
<?php echo Yii::t('admin','Assigns all web leads to "Anyone" and users can re-assign to themselves.');?><br><br>
<b><?php echo Yii::t('admin','Round Robin');?></b><br>
<?php echo Yii::t('admin','Assigns leads to each user going through the list one by one.');?><br><br>
<b><?php echo Yii::t('admin','Single User');?></b><br>
<?php echo Yii::t('admin','The Single User option will assign all leads to the specified user.');?>
<br><br>
<b><?php echo Yii::t('admin','Online Only');?></b><br>
<?php echo Yii::t('admin','This option will filter your routing rule so that leads only go to a subset of the users who are logged in.');?>
<?php echo Yii::t('admin','i.e. if you set custom rules to go to 4 different users, but 2 are logged in, only those 2 will get the leads');?>
</div>
</div>
<script>
    $('#service-source-select').change(function(){
        if($('#service-source-select').val()=='singleUser'){
            $('#user-list').show();
        }else{
            $('#user-list').hide();
        }
    });

    $('#service-source-select').change(function(){
        if($('#service-source-select').val()=='singleGroup'){
            $('#group-list').show();
        }else{
            $('#group-list').hide();
        }
    });
</script>