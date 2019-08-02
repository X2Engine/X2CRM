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




class X2IPAddressTest extends X2TestCase {

    public function testWildcardToCidr() {
        $testNetworks = array(
            '192.168.1.*' => '192.168.1.0/24',
            '172.16.2.*' => '172.16.2.0/24',
            '10.10.*.*' => '10.10.0.0/16',
            '10.*.*.*' => '10.0.0.0/8',
            '*.*.*.*' => '0.0.0.0/0',
        );
        foreach ($testNetworks as $wildcard => $cidr) {
            $this->assertEquals ($cidr, X2IPAddress::wildcardToCidr($wildcard));
        }
    }

    public function testSubnetContainsIp() {
        $validSubnetHostPairs = array(
            '192.168.1.0/24' => '192.168.1.21',
            '10.0.0.0/8' => '10.128.0.10',
            '10.0.0.0/8' => '10.0.0.100',
        );
        $invalidSubnetHostPairs = array(
            '192.168.1.0/24' => '10.0.0.21',
            '10.0.0.0/8' => '100.128.0.10',
        );
        foreach ($validSubnetHostPairs as $network => $host) {
            $this->assertTrue (X2IPAddress::subnetContainsIp($network, $host));
        }
        foreach ($invalidSubnetHostPairs as $network => $host) {
            $this->assertFalse (X2IPAddress::subnetContainsIp($network, $host));
        }
    }

    public function testIsPrivateAddress() {
        $validPrivateAddresses = array(
            '192.168.1.21',
            '192.168.99.1',
            '172.16.1.23',
            '10.128.0.10',
            '10.0.0.100',
        );
        $invalidPrivateAddresses = array(
            '193.168.1.21',
            '192.12.0.1',
            '172.15.1.23',
            '11.128.0.10',
            '9.0.0.100',
            '8.8.4.4',
        );
        foreach ($validPrivateAddresses as $ip) {
            $this->assertTrue (X2IPAddress::isPrivateAddress($ip));
        }
        foreach ($invalidPrivateAddresses as $ip) {
            $this->assertFalse (X2IPAddress::isPrivateAddress($ip));
        }
    }
}
?>
