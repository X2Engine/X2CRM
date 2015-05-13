<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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

/**
 * 
 * @package application.tests.functional.modules.contacts
 */
abstract class VisitAllPagesTest extends X2WebTestCase {

    public $autoLoginOnlyOnce = true;

    /**
     * @param array $pages array of URIs 
     */
    protected function visitPages ($pages, $testXss = false) {
        foreach ($pages as $page) {
            print ('visiting page ' .$page."\n");
		    $this->openX2($page);
            $this->assertNoPHPErrors ($page);
            if ($testXss)
                $this->assertElementNotPresent ('css=.TESTX2INJECTION');
        }
    }

	public function testPages () {
        $this->visitPages ( $this->allPages );
	}

    public $allPages = array(
        // contacts
        'contacts/index',
        'contacts/id/1195',
        'contacts/update/id/1195',
        'contacts/shareContact/id/1195',
        //'contacts/viewRelationships/id/1195',
        'contacts/lists',
        'contacts/myContacts',
        'contacts/createList',
        // accounts
        'accounts/index',
        'accounts/update/id/1',
        'accounts/1',
        'accounts/create',
        'accounts/shareAccount/id/1',
         
        // marketing
        'marketing/index',
        'marketing/create',
        'marketing/5',
        'marketing/update/id/5',
        'weblist/index',
        'weblist/view?id=18',
        'weblist/update?id=18',
        'marketing/webleadForm',
         
        // leads
        'x2Leads/index',
        'x2Leads/create',
        'x2Leads/1',
        'x2Leads/update/id/1',
        'x2Leads/delete/id/1',
        // opportunities
        'opportunities/index',
        'opportunities/51',
        'opportunities/create',
        'opportunities/51',
        'opportunities/update/id/51',
        // services
        'services/index',
        'services/3',
        'services/create',
        'services/update/id/3',
        'services/createWebForm',
        // actions
        'actions/index',
        'actions/create',
        'actions/1',
        'actions/update/id/1',
        'actions/shareAction/id/1',
        'actions/viewGroup',
        'actions/viewAll',
        // calendar
        'calendar/index',
        'calendar/myCalendarPermissions',
        'calendar/userCalendarPermissions',
        'calendar/userCalendarPermissions/id/1',
        // docs
        'docs/index',
        'docs/create',
        'docs/createEmail',
        'docs/createQuote',
        'docs/1',
        'docs/update/id/1',
        'docs/changePermissions/id/1',
        'docs/exportToHtml/id/1',
        // workflow
        'workflow/index',
        'workflow/create',
        'workflow/1?perStageWorkflowView=true',
        'workflow/1?perStageWorkflowView=false',
        'workflow/update/id/1',
        // products
        'products/index',
        'products/1',
        'products/create',
        'products/update/id/1',
        //'site/printRecord/1?modelClass=Product&pageTitle=Product%3A+Semiconductor',
        // quotes
        'quotes/index',
        'quotes/indexInvoice',
        'quotes/1',
        'quotes/convertToInvoice/id/1',
        'quotes/create',
        'quotes/update/id/1',
         
        // charts
        //'charts/leadVolume',
        //'charts/marketing',
        //'charts/pipeline',
        //'charts/sales',
        // media
        'media/index',
        'media/1',
        'media/upload',
        'media/update/id/1',
        // groups
        'groups/index',
        'groups/1',
        'groups/update/id/1',
        'groups/create',
        // bug reports
        'bugReports/index',
        'bugReports/create',
        // site
        'site/viewNotifications',
        'site/page?view=iconreference',
        'site/page?view=about',
        'site/bugReport',
        // profile
        'profile/profiles',
        'profile/activity',
        'profile/1',
        'profile/1?publicProfile=1',
        'profile/update/1',
        'profile/settings/1',
        'profile/changePassword/1',
        'profile/manageCredentials'

    );

    public $adminPages = array(
         
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
         
         
    );

}

?>
