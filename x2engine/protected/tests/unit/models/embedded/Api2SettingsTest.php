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






Yii::import('application.models.embedded.Api2Settings');

/**
 * @package application.tests.unit.models.embedded
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class Api2SettingsTest extends X2TestCase {

    public function testBanIp() {
        $s = new Api2Settings;
        $s->banIP('127.0.0.1');
        $s->banIP('192.168.1.1');
        $s->banIP('127.0.0.1');
        $this->assertEquals('127.0.0.1,192.168.1.1',$s->ipBlacklist);
    }

    public function testBruteforceExempt() {
        $s = new Api2Settings;
        $s->ipWhitelist = '127.0.0.1,192.168.1.1';
        $s->exemptWhitelist = false;
        $this->assertFalse($s->bruteforceExempt('192.168.1.1'));
        $this->assertFalse($s->bruteforceExempt('10.0.0.0'));
        $s->exemptWhitelist = true;
        $this->assertTrue($s->bruteforceExempt('192.168.1.1'));
        $this->assertFalse($s->bruteforceExempt('10.0.0.0'));
    }

    public function testInList() {
        $s = new Api2Settings;
        $s->ipBlacklist = '127.0.0.1,192.168.1.1,10.0.0.0';
        foreach(explode(',',$s->ipBlacklist) as $ip) {
            $this->assertTrue($s->inBlacklist($ip));
        }
    }

    public function testIsIpBlocked() {
        $s = new Api2Settings;
        $s->whitelistOnly = false;
        $s->ipBlacklist = '127.0.0.1,192.168.1.1';
        $s->ipWhitelist = '192.168.1.17';
        // Blacklisted IPs should always be blocked. Non-blacklisted IPs should
        // not be blocked if "whitelistOnly" is false.
        $this->assertTrue($s->isIpBlocked('192.168.1.1'));
        $this->assertFalse($s->isIpBlocked('192.168.1.171'));
        // Non-whitelisted IPs should be blocked if "whitelist only" is enabled
        // and the whitelist is empty:
        $s->whitelistOnly = true;
        $this->assertFalse($s->isIpBlocked('192.168.1.17'));
        $this->assertTrue($s->isIpBlocked('192.168.1.171'));
    }
}

?>
