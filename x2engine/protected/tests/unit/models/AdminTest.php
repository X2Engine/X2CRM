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
    
    public function testLocationSettings() {
        $admin = Yii::app()->settings;
        $admin->locationTrackingSwitch = 1;
        $this->assertEquals(1,$admin->locationTrackingSwitch);
        
        //Test vincentyGreatCircleDistance formula
        $admin->locationTrackingDistance = 5;
        //$earthRadius Mean earth radius in [km]
        $distanceFromTwoPoints = LocationUtil::vincentyGreatCircleDistance(36.9914, 122.0609, 37.3875, 122.0575, $earthRadius = 6371);
        $this->assertGreaterThan($admin->locationTrackingDistance,$distanceFromTwoPoints);
        $distanceFromTwoPointsEq = LocationUtil::vincentyGreatCircleDistance(37.3875, 123.0575, 37.3875, 123.114, $earthRadius = 6371);
        // testing if it's within .01 km of 5 km; the standard of error is about 10.006900992 m 
        $this->assertGreaterThan(4.99,$distanceFromTwoPointsEq);
        $this->assertLessThan(5.01,$distanceFromTwoPointsEq);
        
        $admin->locationTrackingFrequency = 5;
        $minutes = 1000 * 60 * $admin->locationTrackingFrequency; // in miliseconds
        $this->assertEquals(300000,$minutes);
        
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
    

    /**
     * Ensure that we can retrieve license key info with valid license keys
     */
    public function testGetLicenseKeyInfoValid () {
        $url = X2_TESTING_UPDATE_SERVER . '/installs/registry/getLicenseKeyInfo';
        foreach (array (false, true) as $useCurl) {
            foreach (array (VALID_LICENSE_KEY_PRO, VALID_LICENSE_KEY_PLA) as $key) {
                X2_TEST_DEBUG_LEVEL > 1 && 
                    /**/println (
                        'getting info for '.$key.' '.($useCurl ? '' : 'not ').'using curl');
                AppFileUtil::$neverCurl = !$useCurl;
                AppFileUtil::$alwaysCurl = $useCurl;
                $this->assertEquals ($useCurl, AppFileUtil::tryCurl ($url));
                Yii::app()->settings->unique_id = $key;
                $this->assertTrue (Yii::app()->cache2->flush ());
                $licenseKey = Yii::app()->settings->getLicenseKeyInfo (true);
                X2_TEST_DEBUG_LEVEL > 1 && print_r ($licenseKey);
                $this->assertTrue (isset ($licenseKey['dateExpires']));
                $this->assertTrue (isset ($licenseKey['maxUsers']));
                $this->assertTrue (is_numeric ($licenseKey['dateExpires']));
                $this->assertTrue (is_numeric ($licenseKey['maxUsers']));

                // now with caching
                Yii::app()->settings->unique_id = null;
                $licenseKey = Yii::app()->settings->getLicenseKeyInfo (true);
                X2_TEST_DEBUG_LEVEL > 1 && print_r ($licenseKey);
                $this->assertTrue (isset ($licenseKey['dateExpires']));
                $this->assertTrue (isset ($licenseKey['maxUsers']));
                $this->assertTrue (is_numeric ($licenseKey['dateExpires']));
                $this->assertTrue (is_numeric ($licenseKey['maxUsers']));
            }
        }
    }

    /**
     * Ensure that both valid and invalid license keys renders without error 
     */
    public function testLicenseKeyInfoRendering () {
        $url = X2_TESTING_UPDATE_SERVER . '/installs/registry/getLicenseKeyInfo';
        foreach (array (false, true) as $useCurl) {
            foreach (array ('invalid', VALID_LICENSE_KEY_PRO, VALID_LICENSE_KEY_PLA) as $key) {
                X2_TEST_DEBUG_LEVEL > 1 && 
                    /**/println (
                        'rendering info for '.$key.' '.($useCurl ? '' : 'not ').'using curl');
                AppFileUtil::$neverCurl = !$useCurl;
                AppFileUtil::$alwaysCurl = $useCurl;
                $this->assertEquals ($useCurl, AppFileUtil::tryCurl ($url));
                Yii::app()->settings->unique_id = $key;
                $this->assertTrue (Yii::app()->cache2->flush ());
                $this->assertTrue (
                    is_string (
                        $oldDate = Yii::app()->settings->renderProductKeyExpirationDate (true)));
                $this->assertTrue (
                    is_string ($oldMaxUsers = Yii::app()->settings->renderMaxUsers (true)));
                    $this->assertEquals (1, preg_match ('/strong/', $oldMaxUsers));
                    $this->assertEquals (1, preg_match ('/strong/', $oldDate));
                    $this->assertEquals (0, preg_match ('/error-text/', $oldDate));
                    Yii::app()->settings->unique_id = 'invalid';

                // now with caching
                $this->assertEquals (
                    $oldDate, Yii::app()->settings->renderProductKeyExpirationDate (true));
                $this->assertEquals (
                    $oldMaxUsers,Yii::app()->settings->renderMaxUsers (true));
            }
        }
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
