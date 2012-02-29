<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

$admin = &Yii::app()->params->admin;
?>
<h1><?php echo Yii::t('app','Administration Tools'); ?> <a href="contactUs" style="position:relative;bottom:5px;" class="x2-button"><?php echo Yii::t('admin','Contact Us');?></a></h1>
<?php echo Yii::t('app','Welcome to the administration tool set.'); ?>
<br>
<?php
if($admin->updateInterval == -1)
	echo Yii::t('admin','Automatic updates are currently disabled.').' '.CHtml::link(Yii::t('app','Enable Updates'),array('toggleUpdater'));
// else
	
	
	?>

<?php
if(Yii::app()->session['versionCheck']==false && $admin->updateInterval > -1 && ($admin->updateDate + $admin->updateInterval < time())) {
	echo '<span style="color:red;">';
	echo Yii::t('app','A new version is available! Click here to update to version {version}',array(
		'{version}'=>Yii::app()->session['newVersion'].' '.CHtml::link(Yii::t('app','Update'),'updater',array('class'=>'x2-button'))
		));
	echo "</span>\n";
}
?>
<br><br>
<div class="span-7">
	<h2><?php echo Yii::t('admin','Utilities'); ?></h2>
	<div class="form">
		<?php echo CHtml::link(Yii::t('admin','Send mass E-mail'),'searchContact'); ?><br><?php echo Yii::t('admin','Send email based on X2Tags(currently only has basic function)');?><br><br>
		<?php echo CHtml::link(Yii::t('admin','Export data'),array('export')); ?><br><?php echo Yii::t('admin','Export data to a CSV (useful for updates when the database gets wiped)');?><br><br>
		<?php echo CHtml::link(Yii::t('admin','Import data'),array('import')); ?><br><?php echo Yii::t('admin','Import data from a CSV template or exported records');?><br><br>
		<?php echo CHtml::link(Yii::t('admin','Toggle default logo'),'toggleDefaultLogo'); ?><br><?php echo Yii::t('admin','Change logo back to X2Contacts');?><br><br>
		<?php echo CHtml::link(Yii::t('admin','Upload your logo'),'uploadLogo'); ?><br><?php echo Yii::t('admin','Upload your own logo. 30x200 pixel image.');?><br>
	</div>
	<h2><?php echo Yii::t('admin','App Settings'); ?></h2>
	<div class="form">
		<?php echo CHtml::link(Yii::t('admin','General Settings'),'appSettings'); ?><br><?php echo Yii::t('admin','Configure automatic updates, session timeout and chat poll rate.');?><br><br>
		<?php echo CHtml::link(Yii::t('admin','Manage menu items'),'manageModules'); ?><br><?php echo Yii::t('admin','Re-order and add or remove top bar tabs');?><br><br>
		<?php echo CHtml::link(Yii::t('admin','User Management'),Yii::app()->request->baseUrl.'/index.php/users/admin'); ?><br><?php echo Yii::t('admin','Add and manage users');?><br><br>
		<?php echo CHtml::link(Yii::t('admin','Role Management'),'manageRoles'); ?><br /><?php echo Yii::t('admin','Create and manage user roles');?><br /><br />
		<?php echo CHtml::link(Yii::t('admin','Manage Notification Criteria'),'addCriteria'); ?><br><?php echo Yii::t('admin','Manage what events will trigger user notifications.');?><br><br>
		<?php echo CHtml::link(Yii::t('admin','Add Custom Lead Rules'),'roundRobinRules'); ?><br><?php echo Yii::t('admin','Manage rules for the "Custom Round Robin" lead distribution setting.');?><br><br>
		<?php echo CHtml::link(Yii::t('admin','Email Configuration'),'emailSetup'); ?><br><?php echo Yii::t('admin','Configure X2Engine\'s email settings');?><br><br>
		<?php echo CHtml::link(Yii::t('admin','Set Lead Distribution'),'setLeadRouting'); ?><br><?php echo Yii::t('admin','Change how new web leads are distributed.');?><br><br>
	</div>
</div>
<div class="span-7">
	<h2><?php echo Yii::t('admin','Χ2Studio'); ?></h2> 
	<div class="form">
		<?php echo CHtml::link(Yii::t('admin','Dropdown Editor'),'manageDropDowns'); ?><br><?php echo Yii::t('admin','Manage dropdowns for custom fields.');?><br><br>
		<?php echo CHtml::link(Yii::t('admin','Form Editor'),'editor'); ?><br><?php echo Yii::t('admin','Drag and drop editor for forms.');?><br><br>
		<?php echo CHtml::link(Yii::t('admin','Manage Fields'),'manageFields'); ?><br><?php echo Yii::t('admin','Customize fields for the modules.');?><br><br>
		<?php echo CHtml::link(Yii::t('admin','Create new module'),'createModule'); ?><br><?php echo Yii::t('admin','Create a custom module to add to the top bar');?><br><br>
		<?php echo CHtml::link(Yii::t('admin','Create static page'),'createPage'); ?><br><?php echo Yii::t('admin','Add a static page to the top bar');?><br><br>
		<?php echo CHtml::link(Yii::t('admin','Delete a module or Page'),'deleteModule'); ?><br><?php echo Yii::t('admin','Remove a custom module or page');?><br><br>
		<?php echo CHtml::link(Yii::t('admin','Export a module'),'exportModule'); ?><br><?php echo Yii::t('admin','Export one of your custom modules to a .zip');?><br><br>
		<?php echo CHtml::link(Yii::t('admin','Import a module'),'importModule'); ?><br><?php echo Yii::t('admin','Import a .zip of a module');?><br><br>  
		<?php echo CHtml::link(Yii::t('admin','Rename a module'),'renameModules'); ?><br><?php echo Yii::t('admin','Change module titles on top bar');?><br><br>
		<?php echo CHtml::link(Yii::t('admin','Gii - A Code Generation Module'),Yii::app()->request->baseUrl.'/index.php/gii/'); ?><br><?php echo Yii::t('admin','Use the Yii framework\'s code generation tools');?><br><br>
		<?php echo CHtml::link(Yii::t('admin','X2Translations'),array('translationManager')); ?><br><?php echo Yii::t('admin','Add, remove and update message translations in the X2Contacts language packs.');?><br>
		<?php //echo CHtml::link(Yii::t('app','Toggle Accounts Module'),'toggleAccounts'); ?>
		<?php //echo CHtml::link(Yii::t('app','Toggle Sales Module'),'toggleSales'); ?>
	</div>
</div>
<div class="span-7">
	<h2><?php echo Yii::t('admin','Miscellaneous'); ?></h2>
	<div class="form">
		<?php echo CHtml::link(Yii::t('admin','Admin Profile'),Yii::app()->request->baseUrl.'/index.php/profile/1'); ?><br><?php echo Yii::t('admin','Administrator profile');?><br><br>
		<?php echo CHtml::link(Yii::t('admin','Admin Account Settings'),Yii::app()->request->baseUrl.'/index.php/profile/settings/1'); ?><br><?php echo Yii::t('admin','UI theme settings / background image');?><br><br>
		<?php echo CHtml::link(Yii::t('admin','X2Touch'),Yii::app()->request->baseUrl.'/index.php/x2touch'); ?><br><?php echo Yii::t('admin','Mobile web application');?><br><br>
		<?php echo CHtml::link(Yii::t('admin','View User Changelog'),'viewChangelog'); ?><br><?php echo Yii::t('admin','View a log of everything that has been changed');?>
	</div>
	<h2><?php echo Yii::t('admin','Support & Documentation'); ?></h2>
	<div class="form">
		<?php echo CHtml::link(Yii::t('admin','X2Engine'),'http://www.x2engine.com'); ?><br><?php echo Yii::t('admin','Commercial support and hosting');?><br><br>
		<?php echo CHtml::link(Yii::t('admin','X2Community'),'http://www.x2community.com'); ?><br><?php echo Yii::t('admin','X2Engine Support Forums');?><br><br>
		<?php echo CHtml::link(Yii::t('admin','Yii Framework'),'http://www.yiiframework.com/'); ?><br><?php echo Yii::t('admin','Yii Open Source web framework');?><br><br>
	</div>
</div>