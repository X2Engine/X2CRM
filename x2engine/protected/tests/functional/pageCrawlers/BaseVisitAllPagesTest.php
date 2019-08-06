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




Yii::import('application.modules.contacts.models.Contacts');
Yii::import('application.modules.actions.models.*');
Yii::import('application.modules.accounts.models.Accounts');

/**
 * 
 * @package application.tests.functional.modules.contacts
 */
abstract class BaseVisitAllPagesTest extends X2WebTestCase {
    
    public static function referenceFixtures(){
        return array(
            'actions' => array ('Actions', '.VisitAllPagesTest'),
             
            'flows' => array ('X2Flow', '.VisitAllPagesTest'),
            'triggerLogs' => 'TriggerLog',
             
        );
    }
    
    public $fixtures = array(
        'accounts' => 'Accounts',
        'contacts' => 'Contacts',
        'campaign' => 'Campaign',
        'x2Leads' => 'X2Leads',
        'opportunities' => 'Opportunity',
        'services' => 'Services',
        'docs' => 'Docs',
        'workflow' => 'Workflow',
        'products' => 'Product',
        'quotes' => 'Quote',
        'media' => 'Media',
        'groups' => 'Groups',
    );

    public static function setUpBeforeClass () {
        Yii::app()->db->createCommand ("
            insert ignore into x2_auth_item_child (`parent`, `child`) values 
                ('DefaultRole', 'ReportsReadOnlyAccess'),
                ('DefaultRole', 'GroupsBasicAccess'),
                ('DefaultRole', 'GroupsUpdateAccess'),
                ('DefaultRole', 'GroupsFullAccess');
        ")->execute ();
        Yii::app()->db->createCommand('ALTER TABLE x2_contacts ADD COLUMN badField VARCHAR(255);');
        Yii::app()->db->createCommand('INSERT INTO x2_fields (id, modelName, fieldName, attributeLabel, type) '
                .'VALUES (-999, "Contacts", "badField", "Bad\'s Field", "varchar");');
        Yii::app()->authCache->clear ();
        return parent::setUpBeforeClass ();
    }

    public static function tearDownAfterClass () {
        Yii::app()->db->createCommand ("
            delete from x2_auth_item_child where
                parent='DefaultRole' and child='ReportsReadOnlyAccess' or
                parent='DefaultRole' and child='GroupsFullAccess' or
                parent='DefaultRole' and child='GroupsUpdateAccess' or
                parent='DefaultRole' and child='GroupsBasicAccess';
        ")->execute ();
        Yii::app()->db->createCommand('DELETE FROM x2_fields WHERE id = -999');
        Yii::app()->db->createCommand('ALTER TABLE x2_contacts DROP COLUMN badField;');
        Yii::app()->authCache->clear ();
        return parent::tearDownAfterClass ();
    }
    
    public $allPages = array(
        'contacts/index',
        'contacts/id/67890',
        'contacts/update/id/67890',
        'contacts/shareContact/id/67890',
        'contacts/lists',
        'contacts/myContacts',
        'contacts/createList',

        'accounts/index',
        'accounts/update/id/1',
        'accounts/1',
        'accounts/create',
        'accounts/shareAccount/id/1',

        'marketing/index',
        'marketing/create',
        'marketing/5',
        'marketing/update/id/5',

         
        'weblist/index',
        'weblist/view?id=18',
         

        'x2Leads/index',
        'x2Leads/create',
        'x2Leads/1',
        'x2Leads/update/id/1',

        'opportunities/index',
        'opportunities/51',
        'opportunities/create',
        'opportunities/51',
        'opportunities/update/id/51',

        'services/index',
        'services/3',
        'services/create',
        'services/update/id/3',

        'actions/index',
        'actions/create',
        'actions/1',
        'actions/update/id/1',
        'actions/shareAction/id/1',
        'actions/viewGroup',
        'actions/viewAll',

        'calendar/index',
        'calendar/create',

        'docs/index',
        'docs/create',
        'docs/createEmail',
        'docs/createQuote',
        'docs/1',
        'docs/update/id/1',
        'docs/exportToHtml/id/1',

        'workflow/index',
        'workflow/create',
        'workflow/1?perStageWorkflowView=true',
        'workflow/1?perStageWorkflowView=false',
        'workflow/update/id/1',

        'products/index',
        'products/5',
        'products/create',
        'products/update/id/5',

        'quotes/index',
        'quotes/indexInvoice',
        'quotes/1',
        'quotes/convertToInvoice/id/1',
        'quotes/create',
        'quotes/update/id/1',

         
        'reports/gridReport',
        'reports/leadPerformance',
        'reports/savedReports',
        'reports/workflow',
        'reports/activityReport',
        'reports/chartDashboard',
        'reports/rowsAndColumnsReport',
        'reports/summationReport',
         

        'media/index',
        'media/1',
        'media/upload',
        'media/update/id/1',

        'groups/index',
        'groups/1',
        'groups/update/id/1',
        'groups/create',

        'bugReports/index',
        'bugReports/create',

        'site/viewNotifications',
        'site/page?view=iconreference',
        'site/page?view=about',
        'site/printRecord/3?modelClass=Services&pageTitle=Service+Case%3A+70',
        'site/bugReport',
        //'site/printRecord/1?modelClass=Product&pageTitle=Product%3A+Semiconductor',

        'profile/profiles',
        'profile/activity',
        'profile/1?publicProfile=1',
        'profile/update/1',
        'profile/settings/1',
        'profile/changePassword/1',
        'profile/manageCredentials',
        'profile/1'
    );

    public $adminPages = array(
        'services/createWebForm',

        'calendar/update?id=1',
        
         
        'weblist/update?id=18',
         

        'marketing/webleadForm',
         
        'marketing/webTracker',
         

         
        'accounts/accountsReport',
         

         
        // studio
        'studio/flowIndex',
        'studio/flowDesigner',
        'studio/triggerLogs',
         
        'studio/importFlow',
        'studio/exportFlow?flowId=1',
         
        'studio/flowDesigner/1',
         
        'users/admin',
        'users/1',
        'users/update/id/1',
        'users/inviteUsers',
        'users/create',
        'users/userMap',

        'admin/activitySettings',
        'admin/addCriteria',
        'admin/appSettings',       
        'admin/changeApplicationName',
        'admin/convertCustomModules',
        'admin/createModule',
        'admin/createPage',
        'admin/deleteModule',
        'admin/editor',
        
        'admin/editRoleAccess',
        'admin/emailDropboxSettings',
        
        'admin/emailSetup',
        'admin/export',
        'admin/exportModels',
        'admin/exportModels?model=Actions',
        'admin/exportModule',
         
        'admin/flowSettings',
         
        'admin/googleIntegration',
        'admin/import',
        'admin/importModels',
        'admin/importModels?model=Accounts',
        'admin/importModels?model=Actions',
        'admin/importModels?model=Contacts',
        'admin/importModels?model=Product',
        'admin/importModels?model=Quotes',
        'admin/importModels?model=Services',
        'admin/importModels?model=X2Leads',
        'admin/importModule',
        'admin/index',
        
        'admin/lockApp',
        'admin/manageActionPublisherTabs',
        
        'admin/manageDropDowns',
        'admin/manageFields',
        'admin/manageModules',
        'admin/manageRoles',
        'admin/manageSessions',
        'admin/manageTags',
         
        'admin/packager',
         
        'admin/publicInfo',
        'admin/renameModules',
        'admin/rollbackImport',
        'admin/roundRobinRules',
        
        'admin/setDefaultTheme',
        'admin/userLocationHistory',
        'admin/locationSettings',
        'admin/manageUserCount',
        'admin/setLeadRouting',
        'admin/setServiceRouting',
        'admin/translationManager',
        'admin/updater',
        'admin/updaterSettings',
        'admin/uploadLogo',
        'admin/userViewLog',
        'admin/viewChangelog',
        'admin/workflowSettings',
         
        'admin/x2CronSettings',
        'admin/flowSettings',
         
         
        'admin/securitySettings',
        'admin/api2Settings',
         
    );
    
    /**
     * @param array $pages array of URIs 
     */
    protected function visitPages ($pages) {
        foreach ($pages as $page) {
            X2_TEST_DEBUG_LEVEL > 1 && print ('visiting page ' .$page."\n");
            $this->openX2($page);
            $this->assertNoErrors ($page);
        }
    }
}

