<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




?>
<div class="page-title"><h2><?php echo Yii::t('admin','Set Lead Routing Options'); ?></h2></div>
<div style="max-width: 630px;">
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
