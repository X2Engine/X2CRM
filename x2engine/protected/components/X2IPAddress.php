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




/**
 * X2IPAddress helper component for useful IP address methods
 */

class X2IPAddress {
    /**
     * Convert a network using wildcard notation (192.168.1.*) to CIDR
     * @param string $network The network IP address
     * @return string The network address in CIDR notation, or NULL if it cannot be converted
     */
    public static function wildcardToCidr($network) {
        $cidrNetwork = array();
        $octets = explode('.', $network);
        $prefix = 0;
        foreach ($octets as $octet) {
            if ($octet === '*') {
                $cidrNetwork = array_pad ($cidrNetwork, 4, '0');
                $cidrNetwork = implode('.', $cidrNetwork);
                $cidrNetwork .= "/${prefix}";
                return $cidrNetwork;
            } else {
                $cidrNetwork[] = $octet;
            }
            $prefix += 8;
            if ($prefix > 32)
                break;
        }
    }

    /**
     * @param string $network The network IP address, in CIDR notation
     * @param string $host The host IP address
     */
    public static function subnetContainsIp($network, $host) {
        list($subnet, $prefix) = explode('/', $network);

        // Convert addresses to decimal
        $subnetLong = ip2long($subnet);
        $hostLong = ip2long($host);

        // Now calculate the subnet the host belongs to, given the prefix that we know
        $hostsSubnet = $hostLong & ~((1 << (32 - $prefix)) - 1);

        // Return whether the subnets match
        return $hostsSubnet === $subnetLong;
    }

    /**
     * Test whether an IP address is an RFC1918 private address
     * @param string $ip IP Address
     * @return bool is private IP address
     */
    public static function isPrivateAddress($ip) {
        return (self::subnetContainsIp('10.0.0.0/8', $ip) ||
            self::subnetContainsIp('172.16.0.0/12', $ip) ||
            self::subnetContainsIp('192.168.0.0/16', $ip)
        );
    }
}
?>
