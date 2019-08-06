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





Yii::import('application.components.behaviors.CommonSiteControllerBehavior');

class CommonSiteControllerBehaviorTest extends X2TestCase {
    protected $testIps = array(
        '10.0.1.20',
        '10.0.4.40',
        '172.16.0.0/12',
        '10.1.0.0/16',
    );

    public function testIsBannedIp() {
        Yii::app()->settings->ipBlacklist = $this->testIps;
        $behaviorClass = new CommonSiteControllerBehavior;

        // Ensure IPs that are no banned return false
        $testAllowed = array(
            '',
            '127.0.0.1',
            '10.0.1.21',
            '10.2.1.1',
        );
        foreach ($testAllowed as $ip)
            $this->assertFalse ($behaviorClass->isBannedIp ($ip),
                "Failed to allow $ip");

        // Ensure banned IPs return true
        $testBans = array(
            '10.0.1.20',
            '172.16.0.60',
            '172.16.0.128',
            '10.1.121.10',
        );
        foreach ($testBans as $ip)
            $this->assertTrue ($behaviorClass->isBannedIp ($ip),
                "Failed to assert ban for $ip");
    }

    public function testIsWhitelistedIp() {
        Yii::app()->settings->ipWhitelist = $this->testIps;
        $behaviorClass = new CommonSiteControllerBehavior;

        // Ensure whitelisted IPs will be allowed
        $testAllowed = array(
            '10.0.1.20',
            '10.0.4.40',
            '10.1.120.5',
            '10.1.2.128',
            '172.16.12.143',
            '172.16.112.49',
        );
        foreach ($testAllowed as $ip)
            $this->assertTrue ($behaviorClass->isWhitelistedIp ($ip),
                "Failed to allow $ip");

        // Ensure IPs not whitelisted will be denied
        $testNotAllowed = array(
            '',
            '127.0.0.1',
            '10.0.1.21',
            '10.0.4.41',
            '10.2.14.4',
            '172.15.20.20',
        );
        foreach ($testNotAllowed as $ip)
            $this->assertFalse ($behaviorClass->isWhitelistedIp ($ip),
                "Failed to deny $ip");
    }

    public function testVeifyIpAccessWithBannedIp() {
        $testIps = array('10.0.1.20');
        Yii::app()->settings->accessControlMethod = 'blacklist';
        Yii::app()->settings->ipBlacklist = $testIps;
        $behaviorClass = new CommonSiteControllerBehavior;

        $this->setExpectedException ('CHttpException');
        $behaviorClass->verifyIpAccess ('10.0.1.20');
    }

    public function testVeifyIpAccessWithAllowedIp() {
        $testIps = array('10.0.1.20');
        Yii::app()->settings->accessControlMethod = 'whitelist';
        Yii::app()->settings->ipWhitelist = $testIps;
        $behaviorClass = new CommonSiteControllerBehavior;

        $behaviorClass->verifyIpAccess ('10.0.1.20');
        $this->assertTrue (true); // an exception shouldn't have been raised
    }
}
