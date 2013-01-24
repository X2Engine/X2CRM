<?php

/* * *******************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 * ****************************************************************************** */

////////////////////////
// Requirements Check //
////////////////////////
/**
 * Server variable requirements checker, derived from the Yii requirements checker.
 * 
 * @license http://www.yiiframework.com/license
 * @return string
 */
$standalone = False;

if (!function_exists('installer_t')) {
	$standalone = True;
	// Declare the function since the script is not being used from within the installer
	function installer_t($msg) {
		return $msg;
	}

}

function checkServerVar() {
	global $thisFile;
	$vars = array('HTTP_HOST', 'SERVER_NAME', 'SERVER_PORT', 'SCRIPT_NAME', 'SCRIPT_FILENAME', 'PHP_SELF', 'HTTP_ACCEPT', 'HTTP_USER_AGENT');
	$missing = array();
	foreach ($vars as $var) {
		if (!isset($_SERVER[$var]))
			$missing[] = $var;
	}
	if (!empty($missing))
		return installer_t('$_SERVER does not have {vars}.', array('{vars}' => implode(', ', $missing)));
	if (!isset($thisFile))
		$thisFile = __FILE__;
	if (realpath($_SERVER["SCRIPT_FILENAME"]) !== realpath($thisFile))
		return installer_t('$_SERVER["SCRIPT_FILENAME"] must be the same as the entry script file path.');

	if (!isset($_SERVER["REQUEST_URI"]) && isset($_SERVER["QUERY_STRING"]))
		return installer_t('Either $_SERVER["REQUEST_URI"] or $_SERVER["QUERY_STRING"] must exist.');

	if (!isset($_SERVER["PATH_INFO"]) && strpos($_SERVER["PHP_SELF"], $_SERVER["SCRIPT_NAME"]) !== 0)
		return installer_t('Unable to determine URL path info. Please make sure $_SERVER["PATH_INFO"] (or $_SERVER["PHP_SELF"] and $_SERVER["SCRIPT_NAME"]) contains proper value.');

	return '';
}

$canInstall = True;
$reqMessges = array();
$rbm = installer_t("required but missing");

// Step 0: check for a mismatch in directory ownership. Skip this step on Windows 
// and systems where posix functions are unavailable; in such cases there's no 
// reliable way to get the UID of the actual running process.
if (function_exists('posix_geteuid')) {
	$uid = array();
	$uid['{id_own}'] = fileowner(realpath(dirname(__FILE__)));
	$uid['{id_run}'] = posix_geteuid();
	if ($uid['{id_own}'] != $uid['{id_run}']) {
		$canInstall = False;
		$reqMessages[] = strtr(installer_t("Directory ownership mismatch. PHP is running with user ID={id_run}, but this directory is owned by the system user with ID={id_own}. Please check your web server configuration or contact the system administrator or hosting provider."), $uid);
	}
}
if (!version_compare(PHP_VERSION, "5.3.0", ">=")) {
	$canInstall = False;
	$reqMessages[] = installer_t("Your server's PHP version") . ': ' . PHP_VERSION . '; ' . installer_t("version 5.3 or later is required");
}
if (($message = checkServerVar()) !== '') {
	$canInstall = False;
	$reqMessages[] = installer_t($message);
}
if (!class_exists('Reflection', false)) {
	$canInstall = False;
	$reqMessages[] = '<a href="http://php.net/manual/class.reflectionclass.php">PHP reflection class</a>: ' . $rbm;
} else if (extension_loaded("pcre")) {
	$pcreReflector = new ReflectionExtension("pcre");
	ob_start();
	$pcreReflector->info();
	$pcreInfo = ob_get_clean();
	$matches = array();
	preg_match("/([\d\.]+) \d{4,}-\d{1,2}-\d{1,2}/", $pcreInfo, $matches);
	$thisVer = $matches[1];
	$reqVer = '7.4';
	if (version_compare($thisVer, $reqVer) < 0) {
		$canInstall = False;
		$reqMessages[] = strtr(installer_t("The version of the PCRE library included in this build of PHP is {thisVer}, but {reqVer} or later is required."), array('{thisVer}' => $thisVer, '{reqVer}' => $reqVer));
	}
} else {
	$canInstall = False;
	$reqMessages[] = '<a href="http://www.php.net/manual/book.pcre.php">PCRE extension</a>: ' . $rbm;
}
if (!extension_loaded("SPL")) {
	$canInstall = False;
	$reqMessages[] = '<a href="http://www.php.net/manual/book.spl.php">SPL</a>: ' . $rbm;
}
if (!extension_loaded("curl")) {
	$canInstall = False;
	$reqMessages[] = '<a href="http://php.net/manual/book.curl.php">cURL</a>: ' . $rbm;
}
if (!extension_loaded('pdo_mysql')) {
	$canInstall = False;
	$reqMessages[] = '<a href="http://www.php.net/manual/ref.pdo-mysql.php">PDO MySQL extension</a>: ' . $rbm;
}
if (!extension_loaded("ctype")) {
	$canInstall = False;
	$reqMessages[] = '<a href="http://www.php.net/manual/book.ctype.php">CType extension</a>: ' . $rbm;
}
if (!extension_loaded("mbstring")) {
	$canInstall = False;
	$reqMessages[] = '<a href="http://www.php.net/manual/book.mbstring.php">Multibyte string extension</a>: ' . $rbm;
}
if (!ini_get('allow_url_fopen')) {
	$canInstall = False;
	$reqMessages[] = installer_t('The PHP configuration option "allow_url_fopen" is disabled. Software updates will not work.');
}

if ($standalone)
	echo '<div style="width: 680px; border:1px solid #DDD; margin: 25px auto 25px auto; padding: 20px;font-family:sans-serif;">';

if (!$canInstall) {
	echo '<div style="color:red"><div style="width: 100%; text-align:center;"><h1>' . installer_t('Cannot install X2CRM') . "</h1></div>\n";
	echo "<strong>" . installer_t('Unfortunately, your server does not meet the minimum system requirements for installation') . "</strong><br />\n<ul>";
	foreach ($reqMessages as $message) {
		echo "<li>$message</li>";
	}
	echo "</ul>" . installer_t('For more information, please refer to') . ' <a href="http://wiki.x2engine.com/wiki/Installation#Installing_Without_All_Requirements:_What_Won.27t_Work">"Installing Without All Requirements: What Won\'t Work"</a> in the X2CRM Installation Guide.</div><br />';
} else if ($standalone)
	echo '<div style="width: 100%; text-align:center;"><h1>' . installer_t('This webserver can run X2CRM!') . '</h1></div>';

if ($standalone) {
	phpinfo();
	echo '</div>';
}
?>