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




Yii::import("application.components.permissions.*");
Yii::import("application.modules.actions.models.*");
Yii::import("application.modules.users.models.*");
Yii::import("application.modules.groups.models.*");

class PermissionsTest extends X2WebTestCase {

    public $login = array(
        'username'=>'testUser2',
        'password'=>'password',
    );

    public $fixtures = array(
        'contacts' => array ('Contacts', '.X2PermissionsBehaviorTest'),
        'users' => array('User','_1'),
        'profiles' => array('Profile','_1'),
        'groups' => array('Groups','_1'),
        'groupToUser' => array('GroupToUser','_1'),
        'roles' => array('Roles','.PermissionsTest'),
        'roleToUser' => array('RoleToUser','.PermissionsTest'),
        'authAssignment' => array(':x2_auth_assignment','.PermissionsTest'),
    );

    private static $authItemChildrenAdded = array ();
    private static $defaultPermissions = array ();

    public static function setUpBeforeClass () {
        // remove default permissions
        self::$defaultPermissions = Yii::app()->db->createCommand ("
            select * from x2_auth_item_child where parent='DefaultRole' and child like 'Contacts%'
        ")->queryAll (); // save them so they can be restored after test
        Yii::app()->db->createCommand ("
            delete from x2_auth_item_child where parent='DefaultRole' and child like 'Contacts%'
        ")->execute ();

        // auth item insertion done instead of fixture to prevent table from being cleared
        Yii::app()->db->createCommand ("
            insert ignore into x2_auth_item (`name`, `type`, `description`, `bizrule`, `data`) 
                values ('role1', 2, '', NULL, 'N;')
        ")->execute ();
        Yii::app()->cache->flush();
        Yii::app()->authCache->clear ();
        parent::setUpBeforeClass ();
    }

    public static function tearDownAfterClass () {
        // restore default permissions
        if (count (self::$defaultPermissions)) {
            Yii::app()->db->createCommand ("
                insert into x2_auth_item_child (`parent`, `child`) values ".
                implode (',', array_map (function ($record) {
                    return "('{$record['parent']}','{$record['child']}')";
                }, self::$defaultPermissions))."
            ")->execute ();
        }

        // clean up fixture
        Yii::app()->db->createCommand ("
            delete from x2_auth_item where name='role1'
        ")->execute ();

        foreach (self::$authItemChildrenAdded as $record) {
            Yii::app()->db->createCommand ("
                delete from x2_auth_item_child where parent='{$record[0]}' and child='{$record[1]}'
            ")->execute ();
        }
        Yii::app()->cache->flush();
        Yii::app()->authCache->clear ();
        parent::tearDownAfterClass ();
    }

    public function addAuthItemChild ($child, $parent='role1') {
        Yii::app()->db->createCommand ("
            insert ignore into x2_auth_item_child (`parent`, `child`) values ('$parent', '$child')
        ")->execute ();
        $this->assertTrue (Yii::app()->cache->flush());
        Yii::app()->authCache->clear ();
        self::$authItemChildrenAdded[] = array ($parent, $child);
    }

    public function rmAuthItemChild ($child, $parent='role1') {
        Yii::app()->db->createCommand ("
            delete from x2_auth_item_child where parent='{$parent}' and child='{$child}'
        ")->execute ();
        $this->assertTrue (Yii::app()->cache->flush());
        Yii::app()->authCache->clear ();
    }

    /**
     * Test contact visibility for each access level
     * TODO: add tests for create/update/delete
     */
    public function testReadAccessLevels () {
        $user = $this->users ('user2');

        $contactGroupmate = $this->contacts ('contactGroupmate');
        $contactGroup = $this->contacts ('contactGroup');
        $contactAnyone = $this->contacts ('contactAnyone');
        $contactUserPrivate = $this->contacts ('contactUserPrivate');
        $contactOtherPrivate = $this->contacts ('contactOtherPrivate');
        $contactInvisible = $this->contacts ('contactInvisible');

        // private read only access
        $this->addAuthItemChild ('ContactsPrivateReadOnlyAccess');
        Contacts::model ()->asa ('permissions')->clearCache ();

        $this->openX2 ('contacts/'.$contactGroupmate->id);
        //$this->assertNoErrors ();
        $this->assertHttpResponse (403);

        $this->openX2 ('contacts/'.$contactGroup->id);
        $this->assertNoErrors ();
        $this->assertHttpOK ();

        $this->openX2 ('contacts/'.$contactAnyone->id);
        //$this->assertNoErrors ();
        $this->assertHttpResponse (403);

        $this->openX2 ('contacts/'.$contactUserPrivate->id);
        $this->assertNoErrors ();
        $this->assertHttpOK ();

        $this->openX2 ('contacts/'.$contactOtherPrivate->id);
        //$this->assertNoErrors ();
        $this->assertHttpResponse (403);

        $this->openX2 ('contacts/'.$contactInvisible->id);
        //$this->assertNoErrors ();
        $this->assertHttpResponse (403);

        $this->rmAuthItemChild ('ContactsPrivateReadOnlyAccess');

        // read only access
        $this->addAuthItemChild ('ContactsReadOnlyAccess');
        Contacts::model ()->asa ('permissions')->clearCache ();

        $this->openX2 ('contacts/'.$contactGroupmate->id);
        $this->assertNoErrors ();
        $this->assertHttpOK ();

        $this->openX2 ('contacts/'.$contactGroup->id);
        $this->assertNoErrors ();
        $this->assertHttpOK ();

        $this->openX2 ('contacts/'.$contactAnyone->id);
        $this->assertNoErrors ();
        $this->assertHttpOK ();

        $this->openX2 ('contacts/'.$contactUserPrivate->id);
        $this->assertNoErrors ();
        $this->assertHttpOK ();

        $this->openX2 ('contacts/'.$contactOtherPrivate->id);
        //$this->assertNoErrors ();
        $this->assertHttpResponse (403);

        $this->openX2 ('contacts/'.$contactInvisible->id);
        //$this->assertNoErrors ();
        $this->assertHttpResponse (403);

        $this->rmAuthItemChild ('ContactsReadOnlyAccess');

        // no access
        Contacts::model ()->asa ('permissions')->clearCache ();

        $this->openX2 ('contacts/'.$contactGroupmate->id);
        //$this->assertNoErrors ();
        $this->assertHttpResponse (403);

        $this->openX2 ('contacts/'.$contactGroup->id);
        //$this->assertNoErrors ();
        $this->assertHttpResponse (403);

        $this->openX2 ('contacts/'.$contactAnyone->id);
        //$this->assertNoErrors ();
        $this->assertHttpResponse (403);

        $this->openX2 ('contacts/'.$contactUserPrivate->id);
        //$this->assertNoErrors ();
        $this->assertHttpResponse (403);

        $this->openX2 ('contacts/'.$contactOtherPrivate->id);
        //$this->assertNoErrors ();
        $this->assertHttpResponse (403);

        $this->openX2 ('contacts/'.$contactInvisible->id);
        //$this->assertNoErrors ();
        $this->assertHttpResponse (403);

        // admin access
        $this->addAuthItemChild ('ContactsAdmin');
        $this->addAuthItemChild ('ContactsReadOnlyAccess');
        $this->addAuthItemChild ('ContactsFullAccess');
        $this->addAuthItemChild ('ContactsUpdateAccess');
        $this->addAuthItemChild ('ContactsBasicAccess');
        Contacts::model ()->asa ('permissions')->clearCache ();

        $this->openX2 ('contacts/'.$contactGroupmate->id);
        $this->assertNoErrors ();
        $this->assertHttpOK ();

        $this->openX2 ('contacts/'.$contactGroup->id);
        $this->assertNoErrors ();
        $this->assertHttpOK ();

        $this->openX2 ('contacts/'.$contactAnyone->id);
        $this->assertNoErrors ();
        $this->assertHttpOK ();

        $this->openX2 ('contacts/'.$contactUserPrivate->id);
        $this->assertNoErrors ();
        $this->assertHttpOK ();

        $this->openX2 ('contacts/'.$contactOtherPrivate->id);
        $this->assertNoErrors ();
        $this->assertHttpOK ();

        $this->openX2 ('contacts/'.$contactInvisible->id);
        $this->assertNoErrors ();
        $this->assertHttpOK ();
    }
}

?>
