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
<div class="page-title"><h2><?php echo Yii::t('admin','Set Lead Routing Options'); ?></h2></div>
<div class="form">
<?php echo Yii::t('admin','Change how web leads are assigned to users.'); ?>
<br><br>

<?php
$form=$this->beginWidget('CActiveForm', array(
		'id'=>'lead-form',
		'enableAjaxValidation'=>false,
	));
?>

<?php echo $form->labelEx($admin,'leadDistribution'); ?>
<?php echo $form->dropDownList($admin,'leadDistribution',array(
    ''=>Yii::t('admin','Free For All'),
    'trueRoundRobin'=>Yii::t('admin','Round Robin'),
    'customRoundRobin'=>Yii::t('admin','Custom Round Robin'),
    'singleUser'=>Yii::t('admin','Single User')
),array('id'=>"lead-source-select"));?>
    <div id="user-list" style="<?php echo (!empty($admin->leadDistribution) && $admin->leadDistribution=="singleUser")?"":"display:none;" ?>">
    <label><?php echo Yii::t('admin','Selected User'); ?></label>
<?php
    echo $form->dropDownList($admin,'rrId',User::getUserIds());
?>
    </div>
    <?php echo $form->labelEx($admin,'onlineOnly'); ?>
<?php echo $form->dropDownList($admin,'onlineOnly',array(
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
<b><?php echo Yii::t('admin','Custom Round Robin');?></b><br>
<?php echo Yii::t('admin','Same as above but allows you to set custom rules.  i.e. if a contact comes in with a specific value, it will be distributed to a group of users you specify.');?>
<?php echo Yii::t('admin','This option will not work unless you create custom rules.');?><br><br>
<b><?php echo Yii::t('admin','Single User');?></b><br>
<?php echo Yii::t('admin','The Single User option will assign all leads to the specified user.');?>
<br><br>
<b><?php echo Yii::t('admin','Online Only');?></b><br>
<?php echo Yii::t('admin','This option will filter your routing rule so that leads only go to a subset of the users who are logged in.');?>
<?php echo Yii::t('admin','i.e. if you set custom rules to go to 4 different users, but 2 are logged in, only those 2 will get the leads');?>
</div>
</div>
<script>
    $('#lead-source-select').change(function(){
        if($('#lead-source-select').val()=='singleUser'){
            $('#user-list').show();
        }else{
            $('#user-list').hide();
        }
    });
</script>