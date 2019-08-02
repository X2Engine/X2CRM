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






Yii::import ('application.modules.emailInboxes.*');
Yii::import ('application.modules.emailInboxes.controllers.*');
Yii::import ('application.modules.emailInboxes.modules.*');
Yii::import ('application.modules.emailInboxes.models.*');

class EmailInboxesPermissionsTest extends X2DbTestCase {

    public $fixtures = array (
        'inboxes' => 'EmailInboxes',
        'users' => 'User',
    );

    public function setUp () {
        $controller = new EmailInboxesController (
            'emailInboxes', new EmailInboxesModule ('emailInboxes', null));
        Yii::app()->controller = $controller;
        parent::setUp ();
    }

    private function initNonAdmin () {
        TestingAuxLib::suLogin ('testuser');
        $user = $this->users ('testUser');
        $auth = TestingAuxLib::loadAuthManagerMock ();
        $auth->setAccess ('EmailInboxesReadOnlyAccess', $user->id, array (), true);
    }

    private function initAdmin () {
        TestingAuxLib::loadX2NonWebUser ();
        TestingAuxLib::suLogin ('admin');
        $user = $this->users ('admin');
        $auth = TestingAuxLib::loadAuthManagerMock ();
        $auth = TestingAuxLib::loadAuthManagerMock ();
        $auth->setAccess ('EmailInboxesReadOnlyAccess', $user->id, array (), true);
        $auth->setAccess ('EmailInboxesAdminAccess', $user->id, array (), true);
        $auth->setAccess ('EmailInboxesAdmin', $user->id, array (), true);
    }

    private function initModuleAdmin () {
        TestingAuxLib::loadX2NonWebUser ();
        TestingAuxLib::suLogin ('testuser');
        $user = $this->users ('testUser');
        $auth = TestingAuxLib::loadAuthManagerMock ();
        $auth = TestingAuxLib::loadAuthManagerMock ();
        $auth->setAccess ('EmailInboxesReadOnlyAccess', $user->id, array (), true);
        $auth->setAccess ('EmailInboxesAdminAccess', $user->id, array (), true);
        $auth->setAccess ('EmailInboxesAdmin', $user->id, array (), true);
    }

    public function _testGetEmailInboxes ($userType) {
        $controller = Yii::app()->controller;
        $fnName = "init$userType";
        $this->$fnName ();

        $emailInboxes = Yii::app()->params->profile->getEmailInboxes ();
        foreach ($emailInboxes as $inbox) {
            $this->assertTrue ($controller->checkPermissions ($inbox, 'view'));
        }
        
        // ensure that not all email inboxes are visible, set emailInboxes profile attribute to
        // all email inboxes, then ensure that when getEmailInboxes is called, emailInboxes 
        // attribute is filtered by user permissions
        $allEmailInboxes = EmailInboxes::model ()->findAll ();
        $hasPermissionForAll = true;
        foreach ($allEmailInboxes as $inbox) {
            $hasPermissionForAll &= $controller->checkPermissions ($inbox, 'view');
        }
        if ($userType !== 'Admin')
            $this->assertFalse ((bool) $hasPermissionForAll);
        else
            $this->assertTrue ((bool) $hasPermissionForAll);
        Yii::app()->params->profile->emailInboxes = CJSON::encode (array_map (function ($inbox) {
            return $inbox->id;
        }, $allEmailInboxes));

        $emailInboxes = Yii::app()->params->profile->getEmailInboxes ();
        $this->assertTrue (count ($emailInboxes) > 0);
        foreach ($emailInboxes as $inbox) {
            $this->assertTrue ($controller->checkPermissions ($inbox, 'view'));
        }

    }

    public function testGetEmailInboxesNonAdmin () {
        $this->_testGetEmailInboxes ("NonAdmin");
    }

    public function testGetEmailInboxesModuleAdmin () {
        $this->_testGetEmailInboxes ("ModuleAdmin");
    }

    public function testGetEmailInboxesAdmin () {
        $this->_testGetEmailInboxes ("Admin");
    }

    public function testGetVisibleInboxesNonAdmin () {
        $controller = Yii::app()->controller;
        $this->initNonAdmin ();

        $visibleInboxes = EmailInboxes::model ()->getVisibleInboxes ();
        $this->assertTrue (count ($visibleInboxes) > 0);
        foreach ($visibleInboxes as $inbox) {
            $this->assertTrue ($controller->checkPermissions ($inbox, 'view'));
        }
    }

    public function testGetVisibleInboxesModuleAdmin () {
        $controller = Yii::app()->controller;
        $this->initModuleAdmin ();

        $visibleInboxes = EmailInboxes::model ()->getVisibleInboxes ();
        $this->assertTrue (count ($visibleInboxes) > 0);
        foreach ($visibleInboxes as $inbox) {
            $this->assertTrue ($controller->checkPermissions ($inbox, 'view'));
        }
    }

    public function testGetVisibleInboxesAdmin () {
        $controller = Yii::app()->controller;
        $this->initAdmin ();

        $visibleInboxes = EmailInboxes::model ()->getVisibleInboxes ();
        $this->assertTrue (count ($visibleInboxes) > 0);
        foreach ($visibleInboxes as $inbox) {
            $this->assertTrue ($controller->checkPermissions ($inbox, 'view'));
        }
    }


}

?>
