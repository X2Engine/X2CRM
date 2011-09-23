<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/
?>
<h1><?php echo Yii::t('app','Administration Tools'); ?>&nbsp;&nbsp;<a href="contactUs" style="position:relative;bottom:5px;" class="x2-button"><?php echo Yii::t('admin','Contact Us');?></a></h1>
<?php echo Yii::t('app','Welcome to the administration tool set.'); ?>
<br /><br />

<div class="span-7">
	<h2><?php echo Yii::t('admin','Utilities'); ?></h2>
	<div class="form">
		<?php echo CHtml::link(Yii::t('admin','Admin Profile'),Yii::app()->request->baseUrl.'/index.php/profile/1'); ?><br /><?php echo Yii::t('admin','Administrator profile');?><br /><br />
		<?php echo CHtml::link(Yii::t('admin','Admin Account Settings'),Yii::app()->request->baseUrl.'/index.php/profile/settings/1'); ?><br /><?php echo Yii::t('admin','UI theme settings / background image');?><br /><br />
		<?php echo CHtml::link(Yii::t('admin','User Management'),Yii::app()->request->baseUrl.'/index.php/users/admin'); ?><br /><?php echo Yii::t('admin','Add and manage users');?><br /><br />
		<?php echo CHtml::link(Yii::t('admin','Manage menu items'),'manageModules'); ?><br /><?php echo Yii::t('admin','Re-order and add or remove top bar tabs');?><br /><br />
		<?php echo CHtml::link(Yii::t('admin','Send mass E-mail'),'searchContact'); ?><br /><?php echo Yii::t('admin','Send email based on X2Tags(currently only has basic function)');?><br /><br />
		<?php echo CHtml::link(Yii::t('admin','Export data'),array('export')); ?><br /><?php echo Yii::t('admin','Export data to a CSV (useful for updates when the database gets wiped)');?><br /><br />
		<?php echo CHtml::link(Yii::t('admin','Import data'),array('import')); ?><br /><?php echo Yii::t('admin','Import data from a CSV template or exported records');?><br /><br />
		<?php echo CHtml::link(Yii::t('admin','X2Touch'),Yii::app()->request->baseUrl.'/index.php/x2touch'); ?><br /><?php echo Yii::t('admin','Mobile web application');?><br /><br />
		<?php echo CHtml::link(Yii::t('admin','Set session timeout'),'setTimeout'); ?><br /><?php echo Yii::t('admin','Set time before an idle user is logged out');?><br /><br />
		<?php echo CHtml::link(Yii::t('admin','Set chat poll rate'),'setChatPoll'); ?><br /><?php echo Yii::t('admin','Adjust chat refresh rate for performance');?><br /><br />
		<?php echo CHtml::link(Yii::t('admin','Toggle default logo'),'toggleDefaultLogo'); ?><br /><?php echo Yii::t('admin','Change logo back to X2Contacts');?><br /><br />
		<?php echo CHtml::link(Yii::t('admin','Upload your logo'),'uploadLogo'); ?><br /><?php echo Yii::t('admin','Upload your own logo. 30x200 pixel image.');?>
	</div>
</div>
<div class="span-7">
	<h2><?php echo Yii::t('admin','Χ2Studio'); ?></h2> 
	<div class="form">
		<?php echo CHtml::link(Yii::t('admin','Create new module'),'createModule'); ?><br /><?php echo Yii::t('admin','Create a custom module to add to the top bar');?><br /><br />
		<?php echo CHtml::link(Yii::t('admin','Create static page'),'createPage'); ?><br /><?php echo Yii::t('admin','Add a static page to the top bar');?><br /><br />
		<?php echo CHtml::link(Yii::t('admin','Delete a module or Page'),'deleteModule'); ?><br /><?php echo Yii::t('admin','Remove a custom module or page');?><br /><br />
		<?php echo CHtml::link(Yii::t('admin','Export a module'),'exportModule'); ?><br /><?php echo Yii::t('admin','Export one of your custom modules to a .zip');?><br /><br />
		<?php echo CHtml::link(Yii::t('admin','Import a module'),'importModule'); ?><br /><?php echo Yii::t('admin','Import a .zip of a module');?><br /><br />  
		<?php echo CHtml::link(Yii::t('admin','Rename a module'),'renameModules'); ?><br /><?php echo Yii::t('admin','Change module titles on top bar');?><br /><br />
		<?php echo CHtml::link(Yii::t('admin','Gii - A Code Generation Module'),Yii::app()->request->baseUrl."/index.php/gii/"); ?><br /><?php echo Yii::t('admin','Use the Yii framework\'s code generation tools');?><br /><br />
		
		<?php //echo CHtml::link(Yii::t('app','Toggle Accounts Module'),'toggleAccounts'); ?>
		<?php //echo CHtml::link(Yii::t('app','Toggle Sales Module'),'toggleSales'); ?>
	</div>
</div>
<div class="span-7">
	<h2><?php echo Yii::t('admin','Support & Documentation'); ?></h2>
	<div class="form">
		<?php echo CHtml::link(Yii::t('admin','X2Engine'),'http://www.x2engine.com'); ?><br /><?php echo Yii::t('admin','Commercial support and hosting');?><br /><br />
		<?php echo CHtml::link(Yii::t('admin','X2Community'),'http://www.x2community.com'); ?><br /><?php echo Yii::t('admin','X2Engine Support Forums');?><br /><br />
		<?php echo CHtml::link(Yii::t('admin','Yii Framework'),'http://www.yiiframework.com/'); ?><br /><?php echo Yii::t('admin','Yii Open Source web framework');?><br /><br />
		<?php echo CHtml::link(Yii::t('app','How to use Gii'),array('howTo','guide'=>'gii')); ?><br /><br />
		<?php echo CHtml::link(Yii::t('app','How to add a database field'),array('howTo','guide'=>'model'));?>
	</div>
</div>