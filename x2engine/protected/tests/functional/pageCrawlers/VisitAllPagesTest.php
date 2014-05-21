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

	public function testProfilePages () {
        $this->visitPages (array (
            'profile/1',
            'profile/1?publicProfile=1',
            'profile/update/1',
            'profile/settings/1',
            'profile/changePassword/1',
            'profile/manageCredentials'
        ));
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
            'marketing/webTracker',
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
}

?>
