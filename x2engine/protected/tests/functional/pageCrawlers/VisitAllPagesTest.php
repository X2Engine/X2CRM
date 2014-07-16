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

/**
 * 
 * @package application.tests.functional.modules.contacts
 */
abstract class VisitAllPagesTest extends X2WebTestCase {


    public static function setUpBeforeClass () {
        /* x2tempstart */ 
        // quick way of getting leads data until we extend reference fixtures to web test
        // cases
        Yii::app()->db->createCommand ("
        DELETE from x2_x2leads where id in (1, 2, 3, 4, 5);
        INSERT INTO x2_x2leads values 
            (1, 'test', 'test_1', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
            (2, 'test', 'test_2', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
            (3, 'test', 'test_3', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
            (4, 'test', 'test_4', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
            (5, 'test', 'test_5', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        ")->execute ();
        /* x2tempend */
    }

    /**
     * visits page and checks for php errors
     * @param string $page URI of page
     */
    protected function assertNoPHPErrors ($page) {
		$this->openX2($page);
		$this->assertElementNotPresent('css=.xdebug-error');
		$this->assertElementNotPresent('css=#x2-php-error');
    }

    /**
     * @param array $pages array of URIs 
     */
    protected function visitPages ($pages) {
        foreach ($pages as $page) {
            print ('visiting page ' .$page."\n");
            $this->assertNoPHPErrors ($page);
        }
    }

	public function testContactPages () {
        $this->visitPages (array (
            'contacts/index',
            'contacts/id/1195',
            'contacts/update/id/1195',
            'contacts/shareContact/id/1195',
            'contacts/viewRelationships/id/1195',
            'contacts/lists',
            'contacts/myContacts',
            'contacts/createList',
            'contacts/googleMaps',
            'contacts/savedMaps',
        ));
	}

	public function testAccountPages () {
        $this->visitPages (array (
            'accounts/index',
            'accounts/update/id/1',
            'accounts/1',
            'accounts/create',
            'accounts/shareAccount/id/1',
            'accounts/accountsReport',
        ));
	}

	public function testMarketingPages () {
        $this->visitPages (array (
            'marketing/index',
            'marketing/create',
            'marketing/5',
            'marketing/update/id/5',
            'weblist/index',
            'weblist/view?id=18',
            'weblist/update?id=18',
            'marketing/webleadForm',
             
        ));
	}

	public function testLeadsPages () {
        $this->visitPages (array (
            'x2Leads/index',
            'x2Leads/create',
            'x2Leads/1',
            'x2Leads/update/id/1',
            'x2Leads/delete/id/1',
        ));
	}

	public function testOpportunitiesPages () {
        $this->visitPages (array (
            'opportunities/index',
            'opportunities/51',
            'opportunities/create',
            'opportunities/51',
            'opportunities/update/id/51',
        ));
	}

	public function testServicesPages () {
        $this->visitPages (array (
            'services/index',
            'services/3',
            'services/create',
            'services/update/id/3',
            'services/servicesReport',
            'services/createWebForm',
        ));
	}

	public function testActionsPages () {
        $this->visitPages (array (
            'actions/index',
            'actions/create',
            'actions/1',
            'actions/update/id/1',
            'actions/shareAction/id/1',
            'actions/viewGroup',
            'actions/viewAll',
        ));
	}

	public function testCalendarPages () {
        $this->visitPages (array (
            'calendar/index',
            'calendar/myCalendarPermissions',
            'calendar/userCalendarPermissions',
            'calendar/userCalendarPermissions/id/1',
        ));
	}

	public function testDocsPages () {
        $this->visitPages (array (
            'docs/index',
            'docs/create',
            'docs/createEmail',
            'docs/createQuote',
            'docs/1',
            'docs/update/id/1',
            'docs/changePermissions/id/1',
            'docs/exportToHtml/id/1',
        ));
	}

	public function testWorkflowPages () {
        $this->visitPages (array (
            'workflow/index',
            'workflow/create',
            'workflow/1?perStageWorkflowView=true',
            'workflow/1?perStageWorkflowView=false',
            'workflow/update/id/1',
        ));
	}

	public function testProductsPages () {
        $this->visitPages (array (
            'products/index',
            'products/1',
            'products/create',
            'products/update/id/1',
            //'site/printRecord/1?modelClass=Product&pageTitle=Product%3A+Semiconductor',
        ));
	}

	public function testQuotesPages () {
        $this->visitPages (array (
            'quotes/index',
            'quotes/indexInvoice',
            'quotes/1',
            'quotes/convertToInvoice/id/1',
            'quotes/create',
            'quotes/update/id/1',
        ));
	}

     

	public function testChartsPages () {
        $this->visitPages (array (
            'charts/leadVolume',
            'charts/marketing',
            'charts/pipeline',
            'charts/sales',
        ));
	}

	public function testMediaPages () {
        $this->visitPages (array (
            'media/index',
            'media/1',
            'media/upload',
            'media/update/id/1',
        ));
	}

	public function testGroupsPages () {
        $this->visitPages (array (
            'groups/index',
            'groups/1',
            'groups/update/id/1',
            'groups/create',
        ));
	}

	public function testBugReportsPages () {
        $this->visitPages (array (
            'groups/bugReports',
            'groups/create',
        ));
	}

	public function testSitePages () {
        $this->visitPages (array (
            'site/viewNotifications',
            'site/page?view=iconreference',
            'site/page?view=about',
            'site/bugReport',
        ));
    }

	public function testProfilePages () {
        $this->visitPages (array (
            'profile/profiles',
            'profile/activity',
            'profile/1',
            'profile/1?publicProfile=1',
            'profile/update/1',
            'profile/settings/1',
            'profile/changePassword/1',
            'profile/manageCredentials'
        ));
	}

}

?>
