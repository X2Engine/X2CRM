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

Yii::import ('application.modules.contacts.models.*');
Yii::import ('application.modules.accounts.models.*');

/**
 * @package application.tests.unit.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class AdminTest extends X2DbTestCase {

    private static $_appFileUtilState = array ();
    private static $_adminState = array ();

    public static function referenceFixtures () {
        return array (
            'contacts' => 'Contacts',
            'tags' => 'Tags',
        );
    }

    public static function setUpBeforeClass () {
        self::$_appFileUtilState['alwaysCurl'] = AppFileUtil::$alwaysCurl;
        self::$_appFileUtilState['neverCurl'] = AppFileUtil::$neverCurl;
        self::$_adminState['unique_id'] = Yii::app()->settings->unique_id;
        return parent::setUpBeforeClass ();
    }

    public static function tearDownAfterClass () {
        AppFileUtil::$alwaysCurl = self::$_appFileUtilState['alwaysCurl'];
        AppFileUtil::$neverCurl = self::$_appFileUtilState['neverCurl'];
        Yii::app()->settings->unique_id = self::$_adminState['unique_id'];
        Yii::app()->settings->save ();
        return parent::tearDownAfterClass ();
    }

    public function testCountEmail() {
        $admin = Yii::app()->settings;
        $admin->emailCount = 0;
        $admin->emailInterval = 2;
        $admin->update(array('emailCount','emailInterval'));
        $now = time();
        // This should register five emails as having been sent:
        $admin->countEmail(5);
        $this->assertEquals($now,$admin->emailStartTime);
        $this->assertEquals(5,$admin->emailCount);
        // One more:
        $admin->countEmail();
        $this->assertEquals(6,$admin->emailCount);
        sleep(3);
        // After the interval ends, the count should be reset
        $now = time();
        $admin->countEmail(3);
        $this->assertEquals($now,$admin->emailStartTime);
        $this->assertEquals(3,$admin->emailCount);
    }

    public function testEmailCountWillExceedLimit() {
        $admin = Yii::app()->settings;
        $admin->emailCount = 0;
        $admin->emailInterval = 5;
        $admin->emailBatchSize = 5;
        $admin->update(array('emailCount','emailInterval','emailBatchSize'));
        $admin->countEmail(4);
        // One more won't kill us
        $this->assertFalse($admin->emailCountWillExceedLimit(1));
        // Two more will exceed the limit
        $this->assertTrue($admin->emailCountWillExceedLimit(2));
    }

     

    public function testDisableAutomaticRecordTagging () {
        Yii::app()->db->createCommand ("delete from x2_tags where 1");
        $admin = Yii::app()->settings;
        $admin->disableAutomaticRecordTagging = true;
        $this->assertUpdates ($admin, array ('disableAutomaticRecordTagging'));
        $contact = new Contacts;
        $contact->setAttributes (array (
            'firstName' => 'test',
            'lastName' => 'test',
            'visibility' => 1,
            'backgroundInfo' => '#tag0 #tag1 #tag2', 
        ));
        $this->assertSaves ($contact);
        $this->assertEquals (0, (int) Yii::app()->db->createCommand ("
            select count(*) from x2_tags
        ")->queryScalar ());

        $admin->disableAutomaticRecordTagging = false;
        $this->assertUpdates ($admin, array ('disableAutomaticRecordTagging'));
        $this->assertSaves ($contact);
        $this->assertEquals (array ('#tag0', '#tag1', '#tag2'), Yii::app()->db->createCommand ()
            ->select ('tag')
            ->from ('x2_tags')
            ->order ('tag asc')
            ->queryColumn ());
    }
}

?>
