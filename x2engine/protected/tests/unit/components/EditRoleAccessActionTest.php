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






class EditRoleAccessActionTest extends X2DbTestCase {

    // skipped since it nukes the auth item data 
    protected static $skipAllTests = true;

//    public $fixtures = array (
//        'authItemChildren' => array (':x2_auth_item_child', '.EditRoleAccessActionTest'),
//    );

    private static $accessSettings = array (
        'accounts-admin' => '1',
        'actions-admin' => '1',
        'calendar-create' => '1',
        'calendar-delete' => '1',
        'contacts-view-privacy' => 'all',
        'contacts-update-privacy' => 'all',
        'contacts-delete-privacy' => 'all',
        'users-update' => '1',
        'users-delete' => '1',
        'docs-view-privacy' => 'all',
        'docs-update' => '1',
        'docs-update-privacy' => 'private',
        'docs-delete-privacy' => 'all',
        'groups-update' => '1',
        'groups-delete' => '1',
        'x2Leads-view-privacy' => 'all',
        'x2Leads-update-privacy' => 'all',
        'x2Leads-delete-privacy' => 'all',
        'marketing-view-privacy' => 'all',
        'marketing-update-privacy' => 'all',
        'marketing-delete' => '1',
        'marketing-delete-privacy' => 'all',
        'media-create' => '1',
        'media-update' => '1',
        'opportunities-view-privacy' => 'all',
        'opportunities-update' => '1',
        'opportunities-update-privacy' => 'private',
        'opportunities-delete-privacy' => 'all',
        'products-view-privacy' => 'all',
        'products-update' => '1',
        'products-update-privacy' => 'all',
        'products-delete-privacy' => 'all',
        'quotes-view-privacy' => 'all',
        'quotes-update-privacy' => 'all',
        'quotes-delete' => '1',
        'quotes-delete-privacy' => 'all',
        'reports-admin' => '1',
        'workflow-update' => '1',
        'services-view-privacy' => 'all',
        'services-update-privacy' => 'all',
        'services-delete' => '1',
        'services-delete-privacy' => 'all',
        'bugReports-view-privacy' => 'all',
        'bugReports-update-privacy' => 'all',
        'bugReports-delete' => '1',
        'bugReports-delete-privacy' => 'private',
        'yt0' => 'Save',
    );

    public function testEditRole () {
        $action = new EditRoleAccessAction (null, null);
        $getTaskAuthItemNames = TestingAuxLib::setPublic (
            $action, 'getTaskAuthItemNames');
        $authItems = $getTaskAuthItemNames ();
        $getAccessGroupsByModuleName = TestingAuxLib::setPublic (
            $action, 'getAccessGroupsByModuleName');
        $accessGroups = $getAccessGroupsByModuleName ($authItems);
        $editRole = TestingAuxLib::setPublic ($action, 'editRole');
        $role = 'DefaultRole';
        $editRole ($role, self::$accessSettings, false, $accessGroups);
        $action->clearAuthCache ();

        // Ensure that all specified access settings were saved to the db 
        $auth = Yii::app()->authManager;
        $accessSettings = self::$accessSettings;
        unset ($accessSettings['yt0']);
        $addedItemChildren = array ();
        foreach ($accessSettings as $item => $value) {
            $pieces = explode('-', $item);
            if (count ($pieces) === 2) {
                $moduleName = ucfirst($pieces[0]);
                $settingName = $pieces[1];
                switch ($settingName) {
                    case 'view':
                        $access = 'ReadOnly';
                        break;
                    case 'create':
                        $access = 'Basic';
                        break;
                    case 'delete':
                        $access = 'Full';
                        break;
                    case 'update':
                        $access = 'Update';
                        break;
                    case 'admin':
                        $access = 'Admin';
                        break;
                    default:
                        throw new CException ('invalid access level: '.$pieces[1]);
                }
                X2_TEST_DEBUG_LEVEL > 1 && println ($role . ': ' . $moduleName . $access . 'Access');
                if (isset ($accessSettings[$pieces[0].'-'.$pieces[1].'-privacy'])) {
                    $privacy = $accessSettings[$pieces[0].'-'.$pieces[1].'-privacy'];
                    if ($privacy === 'private') {
                        $addedItemChildren[$moduleName . 'Private' . $access . 'Access'] = true;
                        $this->assertTrue (
                            $auth->hasItemChild (
                                $role, $moduleName . 'Private' . $access . 'Access'));
                    } else {
                        $addedItemChildren[$moduleName . $access . 'Access'] = true;
                        $this->assertTrue (
                            $auth->hasItemChild ($role, $moduleName . $access . 'Access'));
                    }
                } else {
                    $addedItemChildren[$moduleName . $access . 'Access'] = true;
                    $this->assertTrue (
                        $auth->hasItemChild ($role, $moduleName . $access . 'Access'));
                }
            }
        }

        // Ensures that if admin access is was set for a given module, all other forms of access 
        // were granted
        $permissionNames = EditRoleAccessAction::$permissionNames;
        foreach ($accessSettings as $item => $value) {
            $pieces = explode('-', $item);
            $moduleName = ucfirst($pieces[0]);
            $settingName = $pieces[1];
            if ($settingName === 'admin') {
                $access = 'Admin';
                $accessGroup = $accessGroups[$pieces[0]];
                foreach ($permissionNames as $name => $keyword) {
                    $accessName = $keyword . 'Access';
                    if (in_array($accessName, $accessGroup)) {
                        X2_TEST_DEBUG_LEVEL > 1 && println ($role . ': ' .  $moduleName . '' . $accessName);
                        $addedItemChildren[$moduleName . $accessName] = true;
                        $this->assertTrue (
                            $auth->hasItemChild ($role, $moduleName . $accessName));

                    }
                }
            }
        }

        // now remove all permissions that were added and assert that none remain.
        // this assertion is done to ensure that no permissions were mistakenly added
        foreach (array_keys ($addedItemChildren) as $child) {
            $auth->removeItemChild ($role, $child);
        }
        $this->assertEquals (0, Yii::app()->db->createCommand ('
            SELECT COUNT(*) 
            FROM x2_auth_item_child
            WHERE parent=:parent AND child LIKE "%Access"
        ')->queryScalar (array (':parent' => $role)));
    }

}

?>
