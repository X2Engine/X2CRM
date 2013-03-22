<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

?>
<div class="span-16">
<div class="page-title"><h2><?php echo Yii::t('admin','Set Lead Routing Options'); ?></h2></div>
<?php echo Yii::t('admin','Change how web leads are assigned to users.'); ?>
<br><br>
<div class="form">
<?php
$form=$this->beginWidget('CActiveForm', array(
		'id'=>'lead-form',
		'enableAjaxValidation'=>false,
	));
?>

<?php echo $form->labelEx($admin,'leadDistribution'); ?>
<?php echo $form->dropDownList($admin,'leadDistribution',array(
    ''=>'Free For All',
    'trueRoundRobin'=>'Round Robin',
    'customRoundRobin'=>'Custom Round Robin',
    'singleUser'=>'Single User'
),array('id'=>"lead-source-select"));?>
    <div id="user-list" style="<?php echo (!empty($admin->leadDistribution) && $admin->leadDistribution=="singleUser")?"":"display:none;" ?>">
    <label><?php echo Yii::t('admin','Selected User'); ?></label>
<?php 
    echo $form->dropDownList($admin,'rrId',User::getUserIds());
?>
    </div>
    <?php echo $form->labelEx($admin,'onlineOnly'); ?>
<?php echo $form->dropDownList($admin,'onlineOnly',array(
    '0'=>'No',
    '1'=>'Yes',
)); ?>

<?php echo CHtml::submitButton(Yii::t('app','Save'),array('class'=>'x2-button'))."\n";?>
<?php $this->endWidget();?></div>
<div class="form">
<b>Free For All</b><br>
Assigns all web leads to "Anyone" and users can re-assign to themselves.<br><br>
<b>Even Distribution</b><br>
Assigns web leads to whomever has the lowest number of uncompleted actions, evening out the number of uncompleted actions between users.<br><br>
<b>Round Robin</b><br>
Assigns leads to each user going through the list one by one. <br><br>
<b>Custom Round Robin</b><br>
Same as above but allows you to set custom rules.  i.e. if a contact comes in with a specific value, it will be distributed to a group of users you specify.
This option will not work unless you create custom rules.<br><br>
<b>Single User</b><br>
The Single User option will assign all leads to the specified user.
<br><br><br>
<b>Online Only</b><br>
This option will filter your routing rule so that leads only go to a subset of the users who are logged in.  
i.e. if you set custom rules to go to 4 different users, but 2 are logged in, only those 2 will get the leads
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