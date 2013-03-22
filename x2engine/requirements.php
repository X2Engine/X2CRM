<?php

/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
	$phpInfoContent = array();
	ob_start();
	phpinfo();
	preg_match('%^.*(<style[^>]*>.*</style>).*<body>(.*)</body>.*$%ms', ob_get_contents(), $phpInfoContent);
	ob_end_clean();
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
$curl = true; // 
$tryAccess = true; // Attempt to access the internet from the web server.
$failedWrite = false; // Whether an attempt was made to identify the UID of the PHP process by writing a file, and the writing failed
$reqMessages = array();
$rbm = installer_t("required but missing");

//////////////////////////////////////////////
// TOP PRIORITY: BIG IMPORTANT REQUIREMENTS // 
//////////////////////////////////////////////

// Check for a mismatch in directory ownership. Skip this step on Windows 
// and systems where posix functions are unavailable; in such cases there's no 
// reliable way to get the UID of the actual running process.
$uid = array_fill_keys(array('{id_own}','{id_run}'),null);
$uid['{id_own}'] = fileowner(realpath(dirname(__FILE__)));
if (function_exists('posix_geteuid')) {
	$uid['{id_run}'] = posix_geteuid();
} else {
	// Try doing it by creating a file.
	$canPutFile = @file_put_contents('helloworld.txt','Hello, world!');
	if($canPutFile && file_exists('helloworld.txt')) {
		$uid['{id_run}'] = fileowner('helloworld.txt');
		unlink('helloworld.txt');
	} else {
		$failedWrite = true;
	}
}
if ($uid['{id_own}'] != $uid['{id_run}'] && !$failedWrite) {
	$canInstall = False;
	$reqMessages[] = strtr(installer_t("PHP is running with user ID={id_run}, but this directory is owned by the system user with ID={id_own}. This will result in errors due to the directory not being writable. Please check your web server's configuration or contact the system administrator or hosting provider."), $uid);
} elseif($failedWrite) {
	$canInstall = False;
	$reqMessages[] = installer_t("This directory is not writable by PHP processes run by the webserver.");
}
// Check PHP version
if (!version_compare(PHP_VERSION, "5.3.0", ">=")) {
	$canInstall = False;
	$reqMessages[] = installer_t("Your server's PHP version") . ': ' . PHP_VERSION . '; ' . installer_t("version 5.3 or later is required");
}
// Check $_SERVER variable meets requirements of Yii
if (($message = checkServerVar()) !== '') {
	$canInstall = False;
	$reqMessages[] = installer_t($message);
}
// Check for existence of Reflection class
if (!class_exists('Reflection', false)) {
	$canInstall = False;
	$reqMessages[] = '<a href="http://php.net/manual/class.reflectionclass.php">PHP reflection class</a>: ' . $rbm;
} else if (extension_loaded("pcre")) {
	// Check PCRE library version
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
// Check for SPL extension
if (!extension_loaded("SPL")) {
	$canInstall = False;
	$reqMessages[] = '<a href="http://www.php.net/manual/book.spl.php">SPL</a>: ' . $rbm;
}
// Check for MySQL connecter
if (!extension_loaded('pdo_mysql')) {
	$canInstall = False;
	$reqMessages[] = '<a href="http://www.php.net/manual/ref.pdo-mysql.php">PDO MySQL extension</a>: ' . $rbm;
}
// Check for CType extension
if (!extension_loaded("ctype")) {
	$canInstall = False;
	$reqMessages[] = '<a href="http://www.php.net/manual/book.ctype.php">CType extension</a>: ' . $rbm;
}
// Check for multibyte-string extension
if (!extension_loaded("mbstring")) {
	$canInstall = False;
	$reqMessages[] = '<a href="http://www.php.net/manual/book.mbstring.php">Multibyte string extension</a>: ' . $rbm;
}
// Check for JSON extension:
if(!extension_loaded('json')) {
	$canInstall = False;
	$reqMessages[] = '<a href="http://www.php.net/manual/function.json-decode.php">json extension</a>: '.$rbm;
}

///////////////////////////////////////////////////////////
// MEDIUM-PRIORITY: IMPORTANT FUNCTIONALITY REQUIREMENTS //
///////////////////////////////////////////////////////////

// Check remote access methods
if (!extension_loaded("curl")) {
	$curl = false; 
	$curlMissingIssues = array(
		installer_t('Time zone widget will not work'),
		installer_t('Contact views may be inaccessible'),
		installer_t('Google integration will not work'),
		installer_t('Built-in error reporter will not work')
	);
	$reqMessages[] = '<a href="http://php.net/manual/book.curl.php">cURL</a>: ' . $rbm.'. '.installer_t('This will result in the following issues:').'<ul><li>'.implode('</li><li>',$curlMissingIssues).'</li></ul>';
}
if (!(bool)(@ini_get('allow_url_fopen'))) {
	if(!$curl) {
		$tryAccess = false;
		$canInstall = false;
		$reqMessages[] = installer_t('The PHP configuration option "allow_url_fopen" is disabled in addition to the CURL extension missing. This means there is no possible way to make HTTP requests, and thus software updates will not work.');
	} else 
		$reqMessages[] = installer_t('The PHP configuration option "allow_url_fopen" is disabled. CURL will be used for making all HTTP requests during updates.');
}
if($tryAccess) {
	if(!(bool)@file_get_contents('http://google.com')) {
		$ch = curl_init('http://google.com');
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_POST,0);
		$response = (bool)@curl_exec($ch);
		if(!$response) {
			$canInstall = false;
			$reqMessages[] = installer_t('This server is effectively cut off from the internet; no outbound routes exist for HTTP traffic. Software updates will not work.');
		}
	}
}

if ( !((bool)(@ini_get('sendmail_path')) || is_executable('/usr/sbin/sendmail') || is_executable('/var/qmail/bin/sendmail'))) {
	$mailIssues = array(
		installer_t('The "PHP Mail" method will not work because E-mail delivery in PHP is disabled.'),
		installer_t('The "Sendmail" method will not work because sendmail is not present on this system.'),
		installer_t('The "Qmail" method will not work because qmail is not present on this system.')
	);
	$mainMessage = installer_t('You will not be able to send email through X2CRM unless you have a third-party email service that supports SMTP and use the "SMTP" method of email delivery.');
	$reqMessages[] = $mainMessage . '<ul><li>'.implode('</li><li>',$mailIssues).'</li></ul>';
}

// Check the session save path:
$ssp = ini_get('session.save_path'); //'%.*;?(/.*)$%'
if(!is_writable($ssp)) {
	$reqMessages[] = strtr(installer_t('The path defined in session.save_path ({ssp}) is not writable. Uploading files via the media module will not work.'),array('{ssp}'=>$ssp));
}

////////////////////////////////////////////////////////////
// LOW PRIORITY: MISCELLANEOUS FUNCTIONALITY REQUIREMENTS //
////////////////////////////////////////////////////////////

// Check for Zip extension
if(!extension_loaded('zip')) {
	$reqMessages[] = '<a href="http://php.net/manual/book.zip.php">Zip</a>: '. $rbm.'. '.installer_t('This will result in the inability to import and export custom modules.');
}
// Check for fileinfo extension
if(!extension_loaded('fileinfo')) {
	$reqMessages[] = '<a href="http://php.net/manual/book.fileinfo.php">Fileinfo</a>: '. $rbm.'. '.installer_t('Image previews and MIME info for uploaded files in the media module will not be available.');
}
// Check for GD exension
if(!extension_loaded('gd')) {
	$reqMessages[] = '<a href="http://php.net/manual/book.image.php">GD</a>: '. $rbm.'. '.installer_t('Security captchas and will not work, and the media module will not be able to detect or display the dimensions of uploaded images.');
}

if ($standalone) {
	echo "<html><header><title>X2CRM System Requirements Check</title>{$phpInfoContent[1]}</head><body>";
	echo '<div style="width: 680px; border:1px solid #DDD; margin: 25px auto 25px auto; padding: 20px;font-family:sans-serif;">';
}


if (!$canInstall) {
	echo '<div style="color:red"><div style="width: 100%; text-align:center;"><h1>' . installer_t('Cannot install X2CRM') . "</h1></div>\n";
	echo "<strong>" . installer_t('Unfortunately, your server does not meet the minimum system requirements for installation') . "</strong><br />";
} else if (count($reqMessages)) {
	echo '<div style="width: 100%; text-align:center;"><h1>'.installer_t('Note the following:').'</h1></div>';
} else if ($standalone) {
	echo '<div style="width: 100%; text-align:center;"><h1>' . installer_t('This webserver can run X2CRM!') . '</h1></div>';
}

if(count($reqMessages)>0) {
	echo "\n<ul>";
	foreach ($reqMessages as $message) {
		echo "<li>$message</li>";
	}
	echo "</ul>\n";
	if(!$canInstall)
		echo "</div>";
	else
		echo installer_t("All other essential requirements were met.").'&nbsp;';
	echo installer_t('For more information, please refer to') . ' <a href="http://wiki.x2engine.com/wiki/Installation#Installing_Without_All_Requirements:_What_Won.27t_Work">"Installing Without All Requirements: What Won\'t Work"</a> in the X2CRM Installation Guide.';
	echo '<br /><br />';
}

if ($standalone) {
	echo $phpInfoContent[2];
	echo '</div></body></html>';
}
?>