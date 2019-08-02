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
 * @file protected/integration/MTA/emailImport.php
 *
 * A script file that provides an alternate means to submit an email to X2Engine via
 * the API action.
 */

///////////////////////////
// Configuration details //
///////////////////////////
// Set this to the IP address or domain name of the server
$host = '';
// Set this to the protocol (use "https://" for an SSL-enabled web server)
$proto = 'http://';
// Set this to the URI on the web server of X2Engine, without the trailing slash.
// So, if the login URL is "http://example.com/X2Engine/index.php/site/login",
// this variable should be "/X2Engine"
$baseUri = '';
// Leave this null if the host specified by $host will resolve correctly.
// Otherwise, if in an environment where (for instance) the domain does not resolve
// properly, and the IP address must be used, but the CRM is on a specifically-named
// virtual host on a shared IP, set this to the domain name of that host, and set
// $host to the IP address of the web server.
$hostName = '';
$data = array(
	'user' => '',
	'userKey' => '',
);

// Obtain raw email as piped to this script from the MTA
$socket = fopen("php://stdin", 'r');
$data['email'] = stream_get_contents($socket);
fclose($socket);

// Run the CURL request to import the email:
$ch = curl_init("$proto$host$baseUri/index.php/api/dropbox");
curl_setopt($ch,CURLOPT_POST,1);
curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
if(!empty($hostName))
	curl_setopt($ch,CURLOPT_HTTPHEADER,array("Host: $hostName"));

curl_exec($ch);

?>
