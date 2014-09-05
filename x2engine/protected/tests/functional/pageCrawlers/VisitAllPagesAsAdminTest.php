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


Yii::import('application.modules.contacts.models.Contacts');
Yii::import('application.modules.actions.models.Actions');
Yii::import('application.modules.accounts.models.Accounts');
Yii::import('application.tests.functional.pageCrawlers.VisitAllPagesTest');

/**
 * 
 * @package application.tests.functional.modules.contacts
 */
class VisitAllPagesAsAdminTest extends VisitAllPagesTest {

    /**
     * Account which is used to crawl the app
     * @var array
     */
    public $login = array(
        'username' => 'admin',
        'password' => 'admin',
    );

    public function testAdminOnlyPages () {
        $this->visitPages (array (
             
            'users/admin',
            'users/1',
            'users/update/id/1',
            'users/inviteUsers',
            'users/create',
            // admin
            'admin/index',
            'admin/editRoleAccess',
            'admin/manageRoles',
            'admin/manageSessions',
            'admin/setLeadRouting',
            'admin/roundRobinRules',
            'admin/workflowSettings',
            'admin/addCriteria',
            'admin/setServiceRouting',
            'studio/flowIndex',
            'studio/importFlow',
            'admin/appSettings',
            'admin/updaterSettings',
            'admin/manageModules',
            'admin/createPage',
            'admin/googleIntegration',
            'admin/toggleDefaultLogo',
            'admin/uploadLogo',
            'admin/updater',
            'admin/activitySettings',
            'admin/publicInfo',
            'admin/lockApp',
            'admin/manageActionPublisherTabs',
            'admin/x2CronSettings',
            'admin/changeApplicationName',
            'admin/setDefaultTheme',
            'admin/emailSetup',
            'admin/emailDropboxSettings',
            'admin/importModels',
            'admin/importModels?model=X2Leads',
            'admin/importModels?model=Actions',
            'admin/importModels?model=Product',
            'admin/importModels?model=Quotes',
            'admin/importModels?model=Services',
            'admin/importModels?model=Contacts',
            'admin/importModels?model=Accounts',
            'admin/exportModels',
            'admin/exportModels?model=Actions',
            'admin/export',
            'admin/import',
            'admin/rollbackImport',
            'admin/viewChangelog',
            'admin/index?translateMode=1',
            'admin/translationManager',
            'admin/manageTags',
            'admin/userViewLog',
            'admin/createModule',
            'admin/manageFields',
            'admin/manageDropDowns',
            'admin/editor',
            'admin/deleteModule',
            'admin/importModule',
            'admin/exportModule',
            'admin/renameModules',
            'admin/convertCustomModules',
        ));
    } 

}

?>
