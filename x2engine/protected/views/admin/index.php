<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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

Yii::app()->clientScript->registerCss('adminCss',"

.page-title h2 {
    line-height: 36px !important;
}

#content {
    border: none;
    background: none;
}

/*@media (max-width: 950px) {
    #content {
        width: auto !important;
    }
}

@media (max-width: 790px) {
    #content > .admin-screen {
        width: auto !important;
        margin-right: 0 !important;
    }
    .admin-screen .cell {
        margin-top: 7px !important;
    }
    .admin-screen .form h2 {
        line-height: normal !important;
        min-height: 48px;
        padding-top: 13px;
    }
    .admin-screen .page-title .x2-button {
        margin-bottom: 4px !important;
    }
}*/

.admin-screen .page-title {
    margin-bottom: 5px;
}

");


ThemeGenerator::removeBackdrop();

$admin = &Yii::app()->settings;

$editionStart = function($edition) {
    ob_start();
    if(!Yii::app()->contEd($edition)) {
        echo '<div title="'.Yii::t('admin','This feature is only available in {edition}',array('{edition}'=>'X2Engine '.Yii::app()->editionLabels[$edition])).'" class="only-in-edition edition-'.$edition.'">';
    }
};

$editionEnd = function($edition) {
    if(!Yii::app()->contEd($edition)){
        echo '</div><!-- .only-in-edition.edition-'.$edition.' -->';
    }
    $section = ob_get_contents();
    ob_end_clean();
    echo $section;
};


?>
<div class="span-20 admin-screen">
<div class="page-title x2-layout-island">
    <h2 style="padding-left:0"><?php echo Yii::t('app','Administration Tools'); ?></h2>
    <?php
    
    echo CHtml::link(Yii::t('admin','About X2Engine'),array('/site/page','view'=>'about'),array('class'=>'x2-button right'));
    ?>
</div>

<?php //echo Yii::t('app','Welcome to the administration tool set.'); ?>
<?php
if(Yii::app()->session['versionCheck']==false && $admin->updateInterval > -1 && ($admin->updateDate + $admin->updateInterval < time()) && !in_array($admin->unique_id,array('none',Null))) {
    echo '<span style="color:red;">';
    echo Yii::t('app','A new version is available! Click here to update to version {version}',array(
        '{version}'=>Yii::app()->session['newVersion'].' '.CHtml::link(Yii::t('app','Update'),'updater',array('class'=>'x2-button'))
        ));
    echo "</span>\n";
}
?>
<div class="form x2-layout-island">
    <h2 id="admin-support"><?php echo Yii::t('admin','Support'); ?></h2>
    <div class="row">
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','X2Engine Inc'),'http://www.x2engine.com'); ?><br><?php echo Yii::t('admin','Commercial support and hosting');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','User Reference Guide'),'http://www.x2engine.com/reference_guide/'); ?><br><?php echo Yii::t('admin','X2Engine Reference');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Phone Support'),'callto:8312225333'); ?><br>831-222-5333 California PST</div>
    </div><br>
    <div class="row">
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','X2Community'),'http://www.x2community.com'); ?><br><?php echo Yii::t('admin','X2Engine Support Forums');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Yii Framework'),'http://www.yiiframework.com/'); ?><br><?php echo Yii::t('admin','Yii Open Source web framework');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','System Diagram'),array('/site/page','view'=>'systemdiagram')); ?><br><?php echo Yii::t('admin','X2Engine 3.0 system diagram');?></div>
    </div>
</div>
<div class="form x2-layout-island">
    <div class="row">
        <h2 id="admin-users"><?php echo Yii::t('admin','User Management'); ?></h2>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('users','Create User'),array('/users/users/create')); ?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('users','Manage Users'),array('/users/users/admin')); ?></div>
        <div class="cell span-6"><?php $editionStart('pro'); ?><?php echo CHtml::link(Yii::t('admin','Edit Access Rules'),array('/admin/editRoleAccess')); ?><br><?php echo Yii::t('admin','Change access rules for roles');?><?php $editionEnd('pro'); ?></div>
    </div><br>
    <div class="row">
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('users','Invite Users'),array('/users/users/inviteUsers')); ?><br><?php echo Yii::t('admin','Send invitation emails to create X2Engine accounts');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Manage Roles'),array('/admin/manageRoles')); ?><br><?php echo Yii::t('admin','Create and manage user roles');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('app','Groups'),array('/groups/groups/index')); ?><br><?php echo Yii::t('admin','Create and manage user groups');?></div>
    </div><br>
    <div class="row">
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Manage Sessions'),array('/admin/manageSessions')); ?><br><?php echo Yii::t('admin','Manage user sessions.');?></div>
        <?php if(Yii::app()->settings->sessionLog){ ?>
            <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','View Session Log'),array('/admin/viewSessionLog')); ?><br><?php echo Yii::t('admin','View a log of user sessions with timestamps and statuses.');?></div>
        <?php } ?>
    </div>
</div>
<div class="form x2-layout-island">
    <div class="row">
        <h2 id="admin-workflow"><?php echo Yii::t('admin','Web Lead Capture and Opportunity Processes'); ?></h2>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('marketing','Web Lead Form'),array('/marketing/marketing/webleadForm')); ?><br><?php echo Yii::t('admin','Create a public form to receive new contacts');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Set Lead Distribution'),array('/admin/setLeadRouting')); ?><br><?php echo Yii::t('admin','Change how new web leads are distributed.');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Add Custom Lead Rules'),array('/admin/roundRobinRules')); ?><br><?php echo Yii::t('admin','Manage rules for the "Custom Round Robin" lead distribution setting.');?></div>
    </div><br>
    <div class="row">
        <div class="cell span-6"><?php $editionStart('pro'); ?><?php echo CHtml::link(Yii::t('admin','Web Tracker Setup'),array('/marketing/marketing/webTracker')); ?><br><?php echo Yii::t('admin','Configure and embed visitor tracking on your website');?><?php $editionEnd('pro'); ?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Manage Processes'),array('/workflow/workflow/index')); ?><br><?php echo Yii::t('admin','Create and manage processes');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Process Settings'),array('/admin/workflowSettings')); ?><br><?php echo Yii::t('admin','Change advanced process settings');?></div>
    </div><br>
    <div class="row">
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Manage Notification Criteria'),array('/admin/addCriteria')); ?><br><?php echo Yii::t('admin','Manage what events will trigger user notifications.');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Service Case Web Form'),array('/services/services/createWebForm')); ?><br><?php echo Yii::t('admin','Create a public form to receive new service cases.');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Set Service Case Distribution'),array('/admin/setServiceRouting')); ?><br><?php echo Yii::t('admin','Change how service cases are distributed.');?></div>
    </div><br>
    
    <div class="row">
        <?php $editionStart('pro'); ?>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','X2Flow'),array('/studio/flowIndex')); ?><br><?php echo Yii::t('admin','Program X2Engine with custom automation directives using a visual design interface.');?></div>
        <?php $editionEnd('pro'); ?>
        <?php $editionStart('pla'); ?>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Import Flow'),array('/studio/importFlow')); ?><br><?php echo Yii::t('admin','Import automation flows created using the X2Flow design studio.');?></div>
        <?php $editionEnd('pla'); ?>
    </div>

</div>
<div class="form x2-layout-island">
    <div class="row">
        <h2 id="admin-settings"><?php echo Yii::t('admin','System Settings'); ?></h2>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','General Settings'),array('/admin/appSettings')); ?><br><?php echo Yii::t('admin','Configure session timeout and chat poll rate.');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Updater Settings'),array('/admin/updaterSettings')); ?><br><?php echo Yii::t('admin','Configure automatic updates and registration.');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Manage Menu Items'),array('/admin/manageModules')); ?><br><?php echo Yii::t('admin','Re-order and add or remove top bar tabs');?></div>
    </div><br>
    <div class="row">
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Create Static Page'),array('/admin/createPage')); ?><br><?php echo Yii::t('admin','Add a static page to the top bar');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Google Integration'),array('/admin/googleIntegration')); ?><br><?php echo Yii::t('admin','Enter your google app settings for Calendar/Google login');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Twitter Integration'),array('/admin/twitterIntegration')); ?><br><?php echo Yii::t('admin','Enter your Twitter app settings for twitter widget');?></div>
    </div><br>
    <div class="row">
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Restore Default Logo'),array('/admin/toggleDefaultLogo')); ?><br><?php echo Yii::t('admin','Change logo back to the X2Engine default logo');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Upload your logo'),array('/admin/uploadLogo')); ?><br><?php echo Yii::t('admin','Upload your own logo. 30px height image.');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Update X2Engine'),array('/admin/updater')); ?><br><?php echo Yii::t('admin','The X2Engine remote update utility.');?></div>
    </div><!-- .row --><br />
    <div class="row">
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Activity Feed Settings'),array('/admin/activitySettings')); ?><br><?php echo Yii::t('admin','Configure global settings for the activity feed.');?></div>
        <div class="cell span-6">
            <?php echo CHtml::link(Yii::t('admin','Public Info Settings'),array('/admin/publicInfo')); ?><br><?php echo Yii::t('admin','Miscellaneous settings that control publicly-visible data.'); ?>
        </div>
        <div class="cell span-6">
        <?php $editionStart('pro'); ?>
            <?php echo CHtml::link(Yii::t('admin','Lock or Unlock X2Engine'),array('/admin/lockApp')); ?><br><?php echo Yii::t('admin','Set X2Engine into maintenance mode, where only administrators can access it.');?>
        <?php $editionEnd('pro'); ?>
        </div><!-- .cell.span-6 -->
    </div><!-- .row -->
    <div class="row">
        <?php $editionStart('pro'); ?>
        <div class="cell span-6">
            <?php echo CHtml::link(Yii::t('admin','Manage Action Publisher Tabs'),array('/admin/manageActionPublisherTabs')); ?><br><?php echo Yii::t('admin','Enable or disable tabs in the action publisher.');?>
        </div><!-- .cell.span-6 -->
        <?php $editionEnd('pro'); ?>
        <?php $editionStart('pro'); ?>
        <div class="cell span-6">
            <?php echo CHtml::link(Yii::t('admin', 'Cron Table'), array('/admin/x2CronSettings')); ?><br><?php echo Yii::t('admin', 'Control the interval at which X2Engine will check for and run scheduled tasks.'); ?>
        </div>
        <?php $editionEnd('pro'); ?>
        <?php if (Yii::app()->edition != 'pla') { ?>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Upgrade X2Engine'),array('/admin/updater','scenario'=>'upgrade')); ?><br><?php echo Yii::t('admin','Upgrade X2Engine to get exclusive features and service. License key and registration info required.');?></div>
        <?php } ?>
    </div><!-- .row -->
    <div class="row">
        <?php $editionStart('pla'); ?>
        <div class="cell span-6">
            <?php echo CHtml::link(Yii::t('admin', 'Set a Default Theme'), array('/admin/setDefaultTheme')); ?><br><?php echo Yii::t('admin', 'Set a deafult theme which will automatically be set for all new users.'); ?>
        </div>
        <div class="cell span-6">
            <?php echo CHtml::link(Yii::t('admin', 'REST API'), array('/admin/api2Settings')); ?><br><?php echo Yii::t('admin', 'Advanced API security and access control settings.'); ?>
        </div>
        <?php $editionEnd('pla'); ?>
    </div><!-- .row -->
</div>

<div class="form x2-layout-island">
    <div class="row">
        <h2 id="admin-email"><?php echo Yii::t('admin','Email Configuration'); ?></h2>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Email Settings'),array('/admin/emailSetup')); ?><br><?php echo Yii::t('admin','Configure X2Engine\'s email settings');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Create Email Campaign'),array('/marketing/marketing/create')); ?><br><?php echo Yii::t('admin','Create an email marketing campaign');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Manage Campaigns'),array('/marketing/marketing/index')); ?><br><?php echo Yii::t('admin','Manage your marketing campaigns');?></div>
    </div>
    <?php $editionStart('pro'); ?>
    <br />
    <div class="row">
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Email Capture'),array('/admin/emailDropboxSettings')); ?><br><?php echo Yii::t('admin','Settings for the "email dropbox", which allows X2Engine to receive and record email.');?></div>
    </div>
    <?php $editionEnd('pro'); ?>
</div>

<div class="form x2-layout-island">
    <div class="row">
        <h2 id="admin-utilities"><?php echo Yii::t('admin','Utilities'); ?></h2>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Import Records'),array('/admin/importModels')); ?><br><?php echo Yii::t('admin','Import records using a CSV template');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Export Records'),array('/admin/exportModels')); ?><br><?php echo Yii::t('admin','Export records to a CSV file');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Export All Data'),array('/admin/export')); ?><br><?php echo Yii::t('admin','Export all data (useful for making backups)');?></div>
    </div><br>
    <div class="row">
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Import All Data'),array('/admin/import')); ?><br><?php echo Yii::t('admin','Import from a global export file');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Rollback Import'),array('/admin/rollbackImport')); ?><br><?php echo Yii::t('admin','Delete all records created by a previous import.');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','View User Changelog'),array('/admin/viewChangelog')); ?><br><?php echo Yii::t('admin','View a log of everything that has been changed');?></div>

        <!--<div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Translate Mode'),array('/admin/index','translateMode'=>Yii::app()->session['translate']?0:1),array('class'=>Yii::app()->session['translate']?'x2-button clicked':'x2-button')); ?><br><?php echo Yii::t('admin','Enable translation tool on all pages.');?></div>-->
    </div><br>
    <div class="row">
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','X2Translations'),array('/admin/translationManager')); ?><br><?php echo Yii::t('admin','Add, remove and update message translations in the X2Engine language packs.');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Tag Manager'),array('/admin/manageTags')); ?><br><?php echo Yii::t('admin','View a list of all used tags with options for deletion.');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','User View History'),array('/admin/userViewLog')); ?><br><?php echo Yii::t('admin','See a history of what records users have viewed.');?></div>
    </div>

    <br />
    <div class="row">
    <?php $editionStart('pla'); ?>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Advanced Security Settings'),array('/admin/securitySettings')); ?><br><?php echo Yii::t('admin','Configure IP access control and failed login penalties to help prevent unauthorized access to the system');?></div>
    <?php $editionEnd('pla'); ?>
    <?php $editionStart('pro'); ?>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Revert Merges'),array('/admin/undoMerge')); ?><br><?php echo Yii::t('admin','Revert record merges which users have performed in the app.'); ?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','X2Packager'),array('/admin/packager')); ?><br><?php echo Yii::t('admin','Import and Export packages to easily share and use system customizations');?></div>
    </div>
    <?php $editionEnd('pro'); ?>
</div>
<div class="form x2-layout-island">
    <div class="row">
        <h2 id="admin-studio"><?php echo Yii::t('admin','Χ2Studio'); ?></h2>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Create a Module'),array('/admin/createModule')); ?><br><?php echo Yii::t('admin','Create a custom module to add to the top bar');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Manage Fields'),array('/admin/manageFields')); ?><br><?php echo Yii::t('admin','Customize fields for the modules.');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Dropdown Editor'),array('/admin/manageDropDowns')); ?><br><?php echo Yii::t('admin','Manage dropdowns for custom fields.');?></div>
    </div><br>
    <div class="row">
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Form Editor'),array('/admin/editor')); ?><br><?php echo Yii::t('admin','Drag and drop editor for forms.');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Delete a module or Page'),array('/admin/deleteModule')); ?><br><?php echo Yii::t('admin','Remove a custom module or page');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Import a module'),array('/admin/importModule')); ?><br><?php echo Yii::t('admin','Import a .zip of a module');?></div>
    </div><br />
    <div class="row">
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Export a module'),array('/admin/exportModule')); ?><br><?php echo Yii::t('admin','Export one of your custom modules to a .zip');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Rename a module'),array('/admin/renameModules')); ?><br><?php echo Yii::t('admin','Change module titles on top bar');?></div>
        <div class="cell span-6"><?php echo CHtml::link(Yii::t('admin','Convert Modules'),array('/admin/convertCustomModules')); ?><br><?php echo Yii::t('admin','Convert your custom modules to be compatible with the latest version');?></div>
    </div>
</div>
</div>
<br><br>
<style>
    .cell a{
        text-decoration:none;
    }
</style>
