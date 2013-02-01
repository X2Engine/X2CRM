<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
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
<div class="span-20 admin-screen">
<div class="page-title">
	<h2 style="padding-left:0"><?php echo Yii::t('app','Administration Tools'); ?></h2>
	<?php echo CHtml::link(Yii::t('admin','About X2EngineCRM'),array('/site/page?view=about'),array('class'=>'x2-button right')); ?>
</div>

<?php //echo Yii::t('app','Welcome to the administration tool set.'); ?>
<?php
if($admin->updateInterval == -1)
	echo Yii::t('admin','Automatic updates are currently disabled.').' '.CHtml::link(Yii::t('app','Enable Updates'),array('toggleUpdater'));

if(Yii::app()->session['versionCheck']==false && $admin->updateInterval > -1 && ($admin->updateDate + $admin->updateInterval < time()) && !in_array($admin->unique_id,array('none',Null))) {
	echo '<span style="color:red;">';
	echo Yii::t('app','A new version is available! Click here to update to version {version}',array(
		'{version}'=>Yii::app()->session['newVersion'].' '.CHtml::link(Yii::t('app','Update'),'updater',array('class'=>'x2-button'))
		));
	echo "</span>\n";
}
?>
<div class="form">
	<h2 id="admin-support"><?php echo Yii::t('admin','Support'); ?></h2>
	<div class="row">
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','X2Engine'),'http://www.x2engine.com'); ?><br><?php echo Yii::t('admin','Commercial support and hosting');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Tutorial Videos'),'http://www.x2engine.com/video-tutorials/'); ?><br><?php echo Yii::t('admin','X2Engine Support Forums');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Phone Support'),'callto:8312225333'); ?><br>831-222-5333 California PST</div>
	</div><br>
	<div class="row">
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','X2Community'),'http://www.x2community.com'); ?><br><?php echo Yii::t('admin','X2Engine Support Forums');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Yii Framework'),'http://www.yiiframework.com/'); ?><br><?php echo Yii::t('admin','Yii Open Source web framework');?></div>
    </div>
</div>
<div class="form">
	<div class="row">
		<h2 id="admin-users"><?php echo Yii::t('admin','User Management'); ?></h2>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('users','Create User'),array('/users/create')); ?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('users','Manage Users'),array('/users/admin')); ?></div>
        <?php if(Yii::app()->user->checkAccess('AdminEditRoleAccess')) { ?><div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Edit Access Rules'),'editRoleAccess'); ?><br><?php echo Yii::t('admin','Change access rules for roles');?></div><?php } ?>
	</div><br>
	<div class="row">
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('users','Invite Users'),array('/users/inviteUsers')); ?><br><?php echo Yii::t('admin','Send invitation emails to create X2Engine accounts');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Manage Roles'),'manageRoles'); ?><br><?php echo Yii::t('admin','Create and manage user roles');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('app','Groups'),array('/groups/index')); ?><br><?php echo Yii::t('admin','Create and manage user groups');?></div>
	</div><br>
    <div class="row">
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Manage Sessions'),array('manageSessions')); ?><br><?php echo Yii::t('admin','Manage user sessions.');?></div>
	</div>
</div>
<div class="form">
	<div class="row">
		<h2 id="admin-workflow"><?php echo Yii::t('admin','Web Lead Capture and Opportunity Workflows'); ?></h2>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('marketing','Web Lead Form'),array('marketing/webleadForm')); ?><br><?php echo Yii::t('admin','Create a public form to receive new contacts');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Set Lead Distribution'),'setLeadRouting'); ?><br><?php echo Yii::t('admin','Change how new web leads are distributed.');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Add Custom Lead Rules'),'roundRobinRules'); ?><br><?php echo Yii::t('admin','Manage rules for the "Custom Round Robin" lead distribution setting.');?></div>
	</div><br>
	<div class="row">
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Create Workflow'),array('/workflow/create')); ?><br><?php echo Yii::t('admin','Create a workflow for your sales process');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Manage Workflows'),array('/workflow/index')); ?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Workflow Settings'),'workflowSettings'); ?><br><?php echo Yii::t('admin','Change advanced workflow settings');?></div>
	</div><br>
	<div class="row">
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Manage Notification Criteria'),'addCriteria'); ?><br><?php echo Yii::t('admin','Manage what events will trigger user notifications.');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Service Case Web Form'),array('services/createWebForm')); ?><br><?php echo Yii::t('admin','Create a public form to receive new service cases.');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Set Service Case Distribution'),'setServiceRouting'); ?><br><?php echo Yii::t('admin','Change how service cases are distributed.');?></div>
	</div>
</div>
<div class="form">
	<div class="row">
		<h2 id="admin-settings"><?php echo Yii::t('admin','System Settings'); ?></h2>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','General Settings'),'appSettings'); ?><br><?php echo Yii::t('admin','Configure session timeout and chat poll rate.');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Updater Settings'),'updaterSettings'); ?><br><?php echo Yii::t('admin','Configure automatic updates and registration.');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Manage menu items'),'manageModules'); ?><br><?php echo Yii::t('admin','Re-order and add or remove top bar tabs');?></div>
	</div><br>
	<div class="row">
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Create static page'),'createPage'); ?><br><?php echo Yii::t('admin','Add a static page to the top bar');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Google Integration'),'googleIntegration'); ?><br><?php echo Yii::t('admin','Enter your google app settings for Calendar/Google login');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Toggle default logo'),'toggleDefaultLogo'); ?><br><?php echo Yii::t('admin','Change logo back to X2Contacts');?></div>
	</div><br>
	<div class="row">
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Upload your logo'),'uploadLogo'); ?><br><?php echo Yii::t('admin','Upload your own logo. 30x200 pixel image.');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Update X2CRM'),'updater'); ?><br><?php echo Yii::t('admin','The X2CRM remote update utility.');?></div>
		<?php if (isset(Yii::app()->params->admin->edition)): ?>
		<?php if(in_array(Yii::app()->params->admin->edition,array('opensource',Null))): ?>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Upgrade X2CRM'),'upgrader'); ?><br><?php echo Yii::t('admin','Upgrade X2CRM to Professional Edition; license key required.');?></div>
		<?php endif;
		endif; ?>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Activity Feed Settings'),'activitySettings'); ?><br><?php echo Yii::t('admin','Configure global settings for the activity feed.');?></div>
	</div>
</div>
<div class="form">
	<div class="row">
		<h2 id="admin-email"><?php echo Yii::t('admin','Email Configuration'); ?></h2>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Email Settings'),'emailSetup'); ?><br><?php echo Yii::t('admin','Configure X2Engine\'s email settings');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Create Email Campaign'),array('/marketing/create')); ?><br><?php echo Yii::t('admin','Create an email marketing campaign');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Manage Campaigns'),array('/marketing/index')); ?><br><?php echo Yii::t('admin','Manage your marketing campaigns');?></div>
	</div>
</div>
<div class="form">
	<div class="row">
		<h2 id="admin-utilities"><?php echo Yii::t('admin','Utilities'); ?></h2>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Import Contacts'),array('/contacts/importExcel')); ?><br><?php echo Yii::t('admin','Import contacts using a CSV template');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Export Contacts'),array('/contacts/export')); ?><br><?php echo Yii::t('admin','Export contacts to a CSV file');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Export All Data'),array('export')); ?><br><?php echo Yii::t('admin','Export all data (useful for making backups)');?></div>
	</div><br>
	<div class="row">
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Import All Data'),array('import')); ?><br><?php echo Yii::t('admin','Import from a global export file');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Rollback Import'),array('rollbackImport')); ?><br><?php echo Yii::t('admin','Delete all records created by a previous import.');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','View User Changelog'),'viewChangelog'); ?><br><?php echo Yii::t('admin','View a log of everything that has been changed');?></div>
		
		<!--<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Translate Mode'),array('index','translateMode'=>Yii::app()->session['translate']?0:1),array('class'=>Yii::app()->session['translate']?'x2-button clicked':'x2-button')); ?><br><?php echo Yii::t('admin','Enable translation tool on all pages.');?></div>-->
	</div>
    <div class="row">
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','X2Translations'),array('translationManager')); ?><br><?php echo Yii::t('admin','Add, remove and update message translations in the X2Contacts language packs.');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Tag Manager'),array('manageTags')); ?><br><?php echo Yii::t('admin','View a list of all used tags with options for deletion.');?></div>
    </div>
</div>
<div class="form">
	<div class="row">
		<h2 id="admin-studio"><?php echo Yii::t('admin','Χ2Studio'); ?></h2> 
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Create a Module'),'createModule'); ?><br><?php echo Yii::t('admin','Create a custom module to add to the top bar');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Manage Fields'),'manageFields'); ?><br><?php echo Yii::t('admin','Customize fields for the modules.');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Dropdown Editor'),'manageDropDowns'); ?><br><?php echo Yii::t('admin','Manage dropdowns for custom fields.');?></div>
	</div><br>
	<div class="row">
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Form Editor'),'editor'); ?><br><?php echo Yii::t('admin','Drag and drop editor for forms.');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Delete a module or Page'),'deleteModule'); ?><br><?php echo Yii::t('admin','Remove a custom module or page');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Import a module'),'importModule'); ?><br><?php echo Yii::t('admin','Import a .zip of a module');?></div>  
	</div><br />
	<div class="row">
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Export a module'),'exportModule'); ?><br><?php echo Yii::t('admin','Export one of your custom modules to a .zip');?></div>
		<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Rename a module'),'renameModules'); ?><br><?php echo Yii::t('admin','Change module titles on top bar');?></div>
	</div>
</div>
</div>
<br><br>


