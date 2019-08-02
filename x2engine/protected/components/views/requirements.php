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
 * @file protected/components/views/requirements.php
 * @author Demitri Morgan <demitri@x2engine.com>
 *
 * Multi-role requirements-check script. Can be included as part of another page,
 * run as its own standalone script, or used to return requirements check data
 * as an array.
 */

/////////////////
// SET GLOBALS //
/////////////////
$document = '<html><header><title>X2Engine System Requirements Check</title>{headerContent}</head><body><div style="width: 680px; border:1px solid #F5F5F5; margin: 25px auto 25px auto; padding: 20px;font-family:sans-serif;">{bodyContent}</div></body></html>';
$totalFailure = array(
	"<h1>This server definitely, most certainly cannot run X2Engine.</h1><p>Not even the system requirements checker script itself could run properly on this server. It encountered the following {scenario}:</p>\n<pre style=\"overflow-x:auto;margin:5px;padding:5px;border:1px red dashed;\">\n",
	"\n</pre>"
);
$mode = php_sapi_name() == 'cli' ? 'cli' : 'web';
$responding = false;
if(!isset($thisFile))
	$thisFile = __FILE__;
if(!isset($standalone))
	$standalone = realpath($thisFile) === realpath(__FILE__);
$returnArray = (isset($returnArray)?$returnArray:false) || $mode == 'cli';
if(!$standalone){
	// Check being called/included inside another script
	$document = '{bodyContent}';
}
$tryCurl = 0;


///////////////////////////////////////////////
// LAST-DITCH EFFORT COMPATIBILITY FUNCTIONS //
///////////////////////////////////////////////
// If any errors are encountered in the actual requirements check script itself
// due to missing/disabled functions on the server itself, these functions will
// print an appropriate message for the occasion.

/**
 * Wrapper for die()
 * 
 * @global type $standalone
 */
function RIP(){
	global $standalone;
	if($standalone){
        die();
	}
}

/**
 * Error handler.
 * 
 * @global type $document
 * @global array $totalFailure
 * @global boolean $responding
 * @global type $standalone
 * @param type $no
 * @param type $st
 * @param type $fi
 * @param type $ln
 */
function handleReqError($no, $st, $fi = Null, $ln = Null){
	global $document, $totalFailure, $responding, $standalone;
    $fatal = $no === E_ERROR;
    if($no === E_ERROR){ // Ignore warnings...
        $responding = true;
        echo strtr($document, array(
            '{headerContent}' => '',
            '{bodyContent}' => str_replace('{scenario}', 'error', $totalFailure[0]."Error [$no]: $st $fi L$ln".$totalFailure[1])
        ));
        RIP();
    }
}

/**
 * Exception handler.
 *
 * @global type $document
 * @global array $totalFailure
 * @global boolean $responding
 * @global type $standalone
 * @param type $e
 */
function handleReqException($e){
    global $document, $totalFailure, $responding, $standalone;
    $responding = true;
    $message = 'Exception: "'.$e->getMessage().'" in '.$e->getFile().' L'.$e->getLine()."\n";

    foreach($e->getTrace() as $stackLevel){
        $message .= $stackLevel['file'].' L'.$stackLevel['line'].' ';
        if($stackLevel['class'] != ''){
            $message .= $stackLevel['class'];
            $message .= '->';
        }
        $message .= $stackLevel['function'];
        $message .= "();\n";
    }
    $message = str_replace('{scenario}', 'uncaught exception', $totalFailure[0].$message.$totalFailure[1]);
    echo strtr($document, array(
        '{headerContent}' => '',
        '{bodyContent}' => $message
    ));

    RIP();
}

/**
 * Shutdown function (for fatal errors, i.e. call to undefined function)
 * 
 * @global type $document
 * @global array $totalFailure
 * @global boolean $responding
 * @global type $standalone
 */
function reqShutdown(){
    global $document, $totalFailure, $responding, $standalone;
    $error = error_get_last();
    if($error != null && !$responding){
        $errno = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr = $error["message"];
        $errtype = ($errno == E_PARSE ? 'parse' : 'fatal').' error';
        $message = "PHP $errtype [$errno]: $errstr in $errfile L$errline";
        $message = str_replace('{scenario}', $errtype, $totalFailure[0].$message.$totalFailure[1]);
        echo strtr($document, array(
            '{headerContent}' => '',
            '{bodyContent}' => $message
        ));
    }
}

/**
 * Throws an exception when encountering an error for easier handling.
 * 
 * @param type $no
 * @param type $st
 * @param type $fi
 * @param type $ln
 * @throws Exception
 */
function exceptionForError($no, $st, $fi = Null, $ln = Null){
	throw new Exception("Error [$no]: $st $fi L$ln");
}

/////////////////////
// EXTRA FUNCTIONS //
/////////////////////

/**
 * Attempt to query a host name's DNS record.
 * 
 * @param type $hostname
 * @return boolean
 */
function checkDNS($hostname) {
    if(function_exists('dns_check_record')) {
        return (integer) @dns_check_record($hostname);
    } else {
        return 0;
    }
}

/**
 * Test the consistency of the $_SERVER global.
 *
 * This function, based on the similarly-named function of the Yii requirements
 * check, validates several essential elements of $_SERVER
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @parameter string $thisFile
 * @return string
 */
function checkServerVar($thisFile = null){
	$vars = array('HTTP_HOST', 'SERVER_NAME', 'SERVER_PORT', 'SCRIPT_NAME', 'SCRIPT_FILENAME', 'PHP_SELF', 'HTTP_ACCEPT', 'HTTP_USER_AGENT');
	$missing = array();
	foreach($vars as $var){
		if(!isset($_SERVER[$var]))
			$missing[] = $var;
	}
	if(!empty($missing))
		return installer_tr('$_SERVER does not have {vars}.', array('{vars}' => implode(', ', $missing)));
	if(empty($thisFile))
		$thisFile = __FILE__;

	if(realpath($_SERVER["SCRIPT_FILENAME"]) !== realpath($thisFile))
		return installer_t('$_SERVER["SCRIPT_FILENAME"] must be the same as the entry script file path.');

	if(!isset($_SERVER["REQUEST_URI"]) && isset($_SERVER["QUERY_STRING"]))
		return installer_t('Either $_SERVER["REQUEST_URI"] or $_SERVER["QUERY_STRING"] must exist.');

	if(!isset($_SERVER["PATH_INFO"]) && strpos($_SERVER["PHP_SELF"], $_SERVER["SCRIPT_NAME"]) !== 0)
		return installer_t('Unable to determine URL path info. Please make sure $_SERVER["PATH_INFO"] (or $_SERVER["PHP_SELF"] and $_SERVER["SCRIPT_NAME"]) contains proper value.');

	return '';
}

/**
 * Tells if the directory is within the open_basedir restriction
 *
 * @param type $path
 * @return int
 */
function isAllowedDir($path) {
    $basedir = trim(ini_get('open_basedir'));
    if($allowCwd = empty($basedir))
        return 1;
    $basedirs = explode(PATH_SEPARATOR,$basedir);
    foreach($basedirs as $dir){
        if(empty($dir))
            continue;
        if(strpos($path,$dir) !== false){
            $allowCwd = 1;
            break;
        }
    }
    return $allowCwd;
}

/**
 * Attempt to access a remote URL
 *
 * @global bool $tryCurl
 * @param string $url
 * @return bool Whether access succeeded
 */
function tryGetRemote($url) {
    global $tryCurl;
    if($tryCurl || !(bool) ($response = @file_get_contents($url))){
        // Function file_get_contents not available, or failed:
		$ch = @curl_init($url);
        if(!(bool) $ch)
            return 0;
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 0);
		$response = @curl_exec($ch);
		curl_close($ch);
    }
    return (int) (bool) $response;
}

// Set error handlers
if(!$returnArray){
    set_error_handler('handleReqError');
    set_exception_handler('handleReqException');
    register_shutdown_function('reqShutdown');
}
/////////////////////////////////
// X2Engine Requirements Check //
/////////////////////////////////

// Set scenario: "Cannot {scenario} X2Engine"
if(!isset($scenario))
	$scenario = 'install';

// Get PHP info
ob_start();
phpinfo();
$pi = ob_get_contents();
preg_match('%(<style[^>]*>.*</style>)%ms',$pi,$phpInfoStyle);
preg_match('%<body>(.*)</body>%ms',$pi,$phpInfoContent);
ob_end_clean();
if(count($phpInfoStyle))
	$phpInfoStyle = $phpInfoStyle[1];
else
	$phpInfoStyle = '';
if(count($phpInfoContent))
	$phpInfoContent = $phpInfoContent[1];
else
	$phpInfoStyle = '';

$phpInfoStyle .= '<style>
.hidden {display: none;}
</style>';

// Declare the function since the script is not being used from within the installer
if(!function_exists('installer_t')){
    function installer_t($msg){
		return $msg;
	}

}
if(!function_exists('installer_tr')) {
    function installer_tr($msg,$params = array()){
        return strtr($msg,$params);
    }
}

$canInstall = True;
$curl = true; //
$tryAccess = true; // Attempt to access the internet from the web server.
$reqMessages = array_fill_keys(array(1, 2, 3), array()); // Severity levels
$requirements = array_fill_keys(array('functions','classes','extensions','environment'),array());
$rbm = installer_t("required but missing");

// Sanity check:
if(!(@function_exists('function_exists') && @function_exists('extension_loaded')))
    throw new Exception(installer_t('The functions function_exist and/or extension_loaded are unavailable!').' '.installer_t('The requirements check script itself cannot run.'));

//////////////////////////////////////////////
// TOP PRIORITY: BIG IMPORTANT REQUIREMENTS //
//////////////////////////////////////////////
// Check for a mismatch in directory ownership. Skip this step on Windows
// and systems where posix functions are unavailable; in such cases there's no
// reliable way to get the UID of the actual running process.
$requirements['environment']['filesystem_ownership'] = 1;
$uid = array_fill_keys(array('{id_own}', '{id_run}'), null);
$uid['{id_own}'] = fileowner(realpath(dirname(__FILE__)));
if($requirements['extensions']['posix'] = function_exists('posix_geteuid')){
	$uid['{id_run}'] = posix_geteuid();
	if($uid['{id_own}'] !== $uid['{id_run}']){
		$reqMessages[3][] = strtr(installer_t("PHP is running with user ID={id_run}, but this directory is owned by the system user with ID={id_own}."), $uid);
        $requirements['environment']['filesystem_ownership'] = 0;
	}
} else {
    $reqMessages[1][] = installer_t('The requirements check script could not determine if local files have correct ownership because the "posix" extension is not available.');
}

$requirements['environment']['filesystem_permissions'] = 1;
// Check that the directory is writable. Print an error message one way or another.
if(!is_writable(dirname(__FILE__))){
	$reqMessages[3][] = installer_t("This directory is not writable by PHP processes run by the webserver.");
    $requirements['environment']['filesystem_permissions'] = 0;
}
if(!is_writable(__FILE__)) {
	$reqMessages[3][] = installer_t("Permissions and/or ownership of uploaded files do not permit PHP processes run by the webserver to write files.");
    $requirements['environment']['filesystem_permissions'] = 0;
}




// Check that the directive open_basedir is not arbitrarily set to some restricted 
// jail directory off in god knows where
$requirements['environment']['open_basedir'] = 1;
if(!empty($basedir)){
    if(!isAllowedDir(dirname(__FILE__))) {
    	$reqMessages[3][] = installer_t('The base directory configuration directive is set, and it does not include the current working directory.');
        $requirements['environment']['open_basedir'] = 0;
    }
}

// Check PHP version
$requirements['environment']['php_version'] = 1;
if(!version_compare(PHP_VERSION, "5.3.0", ">=")){
	$reqMessages[3][] = installer_t("Your server's PHP version").': '.PHP_VERSION.'; '.installer_t("version 5.3 or later is required");
    $requirements['environment']['php_version'] = 0;
}
// Check $_SERVER variable meets requirements of Yii
$requirements['environment']['php_server_superglobal'] = 0;
if($mode == 'web'){
    if(($message = checkServerVar($thisFile)) !== ''){
        $reqMessages[3][] = installer_t($message);
    }else{
        $requirements['environment']['php_server_superglobal'] = 1;
    }
}

// Check for existence of Reflection class
$requirements['extensions']['pcre'] = 0;
$requirements['environment']['pcre_version'] = 0;
if(!($requirements['classes']['Reflection']=class_exists('Reflection', false))){
	$reqMessages[3][] = '<a href="http://php.net/manual/class.reflectionclass.php">PHP reflection class</a>: '.$rbm;
}else if($requirements['extensions']['pcre']=extension_loaded("pcre")){
	// Check PCRE library version
	$pcreReflector = new ReflectionExtension("pcre");
	ob_start();
	$pcreReflector->info();
	$pcreInfo = ob_get_clean();
	$matches = array();
	preg_match("/([\d\.]+) \d{4,}-\d{1,2}-\d{1,2}/", $pcreInfo, $matches);
	$thisVer = $matches[1];
	$reqVer = '7.4';
	if(!($requirements['environment']['pcre_version'] = version_compare($thisVer, $reqVer) >= 0)){
		$reqMessages[3][] = strtr(installer_t("The version of the PCRE library included in this build of PHP is {thisVer}, but {reqVer} or later is required."), array('{thisVer}' => $thisVer, '{reqVer}' => $reqVer));
	}
}else{
	$reqMessages[3][] = '<a href="http://www.php.net/manual/book.pcre.php">PCRE extension</a>: '.$rbm;
}
// Check for SPL extension
if(!($requirements['extensions']['SPL']=extension_loaded("SPL"))){

	$reqMessages[3][] = '<a href="http://www.php.net/manual/book.spl.php">SPL</a>: '.$rbm;
}
// Check for MySQL connecter
if(!($requirements['extensions']['pdo_mysql']=extension_loaded('pdo_mysql'))){
	$reqMessages[3][] = '<a href="http://www.php.net/manual/ref.pdo-mysql.php">PDO MySQL extension</a>: '.$rbm;
}
// Check for CType extension
if(!($requirements['extensions']['ctype']=extension_loaded('ctype'))){
	$reqMessages[3][] = '<a href="http://www.php.net/manual/book.ctype.php">CType extension</a>: '.$rbm;
}
// Check for multibyte-string extension
if(!($requirements['extensions']['mbstring']=extension_loaded('mbstring'))){
	$reqMessages[3][] = '<a href="http://www.php.net/manual/book.mbstring.php">Multibyte string extension</a>: '.$rbm;
}
// Check for JSON extension:
if(!($requirements['extensions']['json']=extension_loaded('json'))){
	$reqMessages[3][] = '<a href="http://www.php.net/manual/function.json-decode.php">json extension</a>: '.$rbm;
}
// Check for hash:
if(!($requirements['extensions']['hash']=extension_loaded('hash'))){
	$reqMessages[3][] = '<a href="http://www.php.net/manual/book.hash.php">HASH Message Digest Framework</a>: '.$rbm;
} else {
	$algosRequired = array('sha512');
	$algosAvail = hash_algos();
	$algosNotAvail = array_diff($algosRequired,$algosAvail);
	if(!empty($algosNotAvail))
		$reqMessages[3][] = installer_t('Some hashing algorithms required for software updates are missing on this server:').' '.implode(', ',$algosNotAvail);
}

// Check the session save path:
$ssp = ini_get('session.save_path');
if(!is_writable($ssp)){
	$reqMessages[3][] = strtr(installer_t('The path defined in session.save_path ({ssp}) is not writable.'), array('{ssp}' => $ssp));
}

// Miscellaneous functions:
$requiredFunctions = array(
    'php_sapi_name',
	'mb_regex_encoding',
	'getcwd',
	'chmod',
    'hash_algos',
    'mt_rand',
    'md5'
);
$missingFunctions = array();
foreach($requiredFunctions as $function)
	if(!($requirements['functions'][$function]=function_exists($function)))
		$missingFunctions[] = $function;
if(count($missingFunctions))
	$reqMessages[3][] = installer_t('The following required PHP function(s) is/are missing or disabled: ').implode(', ',$missingFunctions);
// Check for the permissions to run chmod on files owned by the web server:
if(!in_array('chmod', $missingFunctions)) {
	set_error_handler('exceptionForError');
	try{
		// Attempt to change the permissions of the current file and then change them back again
		$fp = fileperms(__FILE__); // original permissions
		chmod(__FILE__,octdec(100700));
		chmod(__FILE__,$fp);
        $requirements['environment']['chmod'] = 1;
	}catch (Exception $e){
		$reqMessages[3][] = installer_t('PHP scripts are not permitted to run the function "chmod".');
        $requirements['environment']['chmod'] = 0;
	}
	restore_error_handler();
}

///////////////////////////////////////////////////////////
// MEDIUM-PRIORITY: IMPORTANT FUNCTIONALITY REQUIREMENTS //
///////////////////////////////////////////////////////////
// Check remote access methods
$curl = ($requirements['extensions']['curl']=extension_loaded("curl")) && function_exists('curl_init') && function_exists('curl_exec');
if(!$curl){
	$curlMissingIssues = array(
		installer_t('Time zone widget will not work'),
		installer_t('Google integration will not work'),
		installer_t('Built-in error reporter will not work'),
		installer_t('API web hooks (and thus, Zapier integration) will not work'),
		installer_t('Twitter integration will not work')
	);
	$reqMessages[2][] = '<a href="http://php.net/manual/book.curl.php">cURL</a>: '.$rbm.'. '.installer_t('This will result in the following issues:').'<ul><li>'.implode('</li><li>', $curlMissingIssues).'</li></ul>'.installer_t('Furthermore, please note: without this extension, the requirements check script could not check the outbound internet connection of this server.');
}

if(!(bool) ($requirements['environment']['allow_url_fopen']=@ini_get('allow_url_fopen'))){
	if(!$curl){
		$tryAccess = false;
		$reqMessages[2][] = installer_t('The PHP configuration option "allow_url_fopen" is disabled in addition to the CURL extension missing. This means there is no possible way to make outbound HTTP/HTTPS requests.')
            .' '.installer_t('Software updates will have to be performed using the "offline" method, and Google integration will not work.');
	} else
		$reqMessages[1][] = installer_t('The PHP configuration option "allow_url_fopen" is disabled. CURL will be used for making all HTTP requests during updates.');
}

// Check memory allocation limits
$maxMem = ini_get('memory_limit');
if(!empty($maxMem) && preg_match('/(\d+)([BKMG])/i',$maxMem,$match)) {
    $multiplier = array(
        'b' => 1,
        'k' => 1024,
        'm' => 1048576,
        'g' => 1073741824
    );
    $maxBytes = ((integer)$match[1])*$multiplier[strtolower($match[2])];
} else {
    $maxBytes = (integer) $maxMem;
}
if((bool)((int)$maxBytes+1) && $maxBytes <= 33554432) {
    $reqMessages[2][] = installer_t('The memory limit is set to 32 megabytes or lower in the PHP configuration. Please consider raising this limit. X2Engine may otherwise encounter fatal runtime errors.');
}

///////////////////////
// NETWORK DIAGNOSIS //
///////////////////////

// Re-usable messages for network diagnosis:
$updateMethodMsg = installer_t('Software updates will have to be performed using the "offline" method.');
$googleIntegrationMsg = installer_t('Google integration will not work.');
$tmpProblemMsg = installer_t('This may be a temporary problem.');
$cutOffMsg = installer_t('This server is effectively cut off from the internet.');
$firewallMsg = installer_t('This is likely because the server is behind a firewall that is preventing outbound HTTP/HTTPS requests.');

// Defaults and presets:
$tryCurl = !$requirements['environment']['allow_url_fopen'];
$requirements['environment']['updates_connection'] = 0;
$requirements['environment']['outbound_connection'] = 0;

// Full network diagnosis:
if($tryAccess){
    // There exists one remote access method, so it's worth trying. 
    // 
    // Check outbound connection:
    if($requirements['environment']['outbound_connection'] = checkDNS('google.com')){
        // At least DNS is working, and at least for google.
        if($requirements['environment']['outbound_connection'] = tryGetRemote('http://www.google.com')){
            // Can connect to Google OK. Can connect to the updates server?
            if($requirements['environment']['updates_connection'] = checkDNS('x2planet.com')){
                if(!($requirements['environment']['updates_connection'] = tryGetRemote('http://52.33.121.218/x2planet.com/installs/registry/reqCheck'))){
                    // 
                    $reqMessages[2][] = installer_t('Could not reach the updates server from this web server.')
                            .' '.$firewallMsg
                            .' '.$updateMethodMsg
                            .' '.$tmpProblemMsg;
                }
            }else{
                // No DNS for update server.
                $reqMessages[2][] = installer_t('The DNS record associated with the updates server is not available on this web server.')
                        .' '.$updateMethodMsg
                        .' '.$tmpProblemMsg;
            }
        } else {
            // Can resolve DNS but can't make web request.
            $reqMessages[2][] = $cutOffMsg
                    .' '.$firewallMsg
                    .' '.installer_t('It is also posible that no outbound network route exists.')
                    .' '.$updateMethodMsg
                    .' '.$googleIntegrationMsg;
        }
    }else{
        // DNS failed for Google! There's no outbound connection period
        $reqMessages[2][] = $cutOffMsg
        .' '.installer_t('This is due to local DNS resolution failing.')
            .' '.$updateMethodMsg
            .' '.$googleIntegrationMsg;
    }
}

if(!function_exists('dns_check_record') && !$requirements['environment']['outbound_connection']) {
    $reqMessages[1][] = installer_t('Note: the function "dns_check_record" is not available on this server, so network diagnostic messages may not be accurate.');
}



// The ability to create network sockets, essential for SMTP-based email delivery:
if(!(bool) ($requirements['environment']['fsockopen'] = function_exists('fsockopen'))) {
    $reqMessages[2][] = installer_t('The function "fsockopen" is unavailable or has been disabled on this server. X2Engine will not be able to send email via SMTP.');
}

// Check the ability to make database backups during updates:
if(!(bool) ($canBackup = $requirements['functions']['proc_open'] = function_exists('proc_open'))) {
    $reqMessages[2][] = installer_t('The function proc_open is unavailable on this system. X2Engine will not be able to control the local cron table, or perform database backups, or automatically restore a database to its backup in the event of a failed update.');
}
$requirements['environment']['shell'] = $canBackup;
if($canBackup){
	try{
		// Test for the availability of mysqldump:
        $descriptor = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );
        $testProc = proc_open('mysqldump --help', $descriptor, $pipes);
        $ret = proc_close($testProc);
        unset($pipes);
        
        if($ret === 0) {
            $prog = 'mysqldump';
        } else if($ret !== 0){
            $testProc = proc_open('mysqldump.exe --help', $descriptor, $pipes);
            $ret = proc_close($testProc);
            if($ret !== 0)
                throw new Exception(installer_t('Unable to perform database backup; the "mysqldump" utility is not available on this system.'));
            else
                $prog = 'mysqldump.exe';
        }
	}catch(Exception $e){
		$canBackup = false;
	}
    $canBackup = isset($prog);
}
if(!$canBackup && $requirements['functions']['proc_open']){
    $requirements['environment']['shell'] = 0;
	$reqMessages[1][] = installer_t('The "mysqldump" and "mysql" command line utilities are unavailable on this system. X2Engine will not be able to automatically make a backup of its database during software updates, or automatically restore its database in the event of a failed update.');
}

$giNotwork = installer_t('Google integration will not work.');
if(!function_exists('sys_get_temp_dir')){
    $message = installer_t('The function "sys_get_temp_dir" is unavailable.');
    if(isAllowedDir('/tmp')){
        if(!is_writable('/tmp')){
            $reqMessages[1][] = $msg.' '.installer_t('The directory "/tmp" is not writable.').' '.$giNotwork;
        }
    } else {
        $reqMessages[1][] = $msg.' '.installer_t('Use of the directory "/tmp" is not permitted on this system.').' '.$giNotwork;
    }
} else {
    $tmp = sys_get_temp_dir();
    if(!empty($tmp) && isAllowedDir($tmp)){
        if(!is_writable($tmp)){
            $reqMessages[1][] = installer_t('The system temporary directory, according to "sys_get_temp_dir", is not writable.').' '.$giNotwork;
        }
    }else{
        $reqMessages[1][] = installer_t('Usage of the system temporary directory, according to "sys_get_temp_dir", is either unknown or not permitted.').' '.$giNotwork;

    }
}

////////////////////////////////////////////////////////////
// LOW PRIORITY: MISCELLANEOUS FUNCTIONALITY REQUIREMENTS //
////////////////////////////////////////////////////////////
// Check encryption methods
if(!($requirements['extensions']['openssl']=extension_loaded('openssl') && $requirements['extensions']['mcrypt']=extension_loaded('mcrypt'))) {
	$reqMessages[1][] = installer_t('The "openssl" and "mcrypt" libraries are not available. If any application credentials (i.e. email account passwords) are entered into X2Engine, they  will be stored in the database in plain text (without any encryption whatsoever). Thus, if the database is ever compromised, those passwords will be readable by unauthorized parties.');
}

// Check for Zip extension
if(!($requirements['extensions']['zip']=extension_loaded('zip'))){
	$reqMessages[1][] = '<a href="http://php.net/manual/book.zip.php">Zip</a>: '.$rbm.'. '.installer_t('This will result in the inability to import and export custom modules.');
}
// Check for fileinfo extension
if(!($requirements['extensions']['fileinfo']=extension_loaded('fileinfo'))){
	$reqMessages[1][] = '<a href="http://php.net/manual/book.fileinfo.php">Fileinfo</a>: '.$rbm.'. '.installer_t('Image previews and MIME info for uploaded files in the media module will not be available.');
}
// Check for GD exension
if(!($requirements['extensions']['gd']=extension_loaded('gd'))){
	$reqMessages[1][] = '<a href="http://php.net/manual/book.image.php">GD</a>: '.$rbm.'. '.installer_t('Security captchas will not work, and the media module will not be able to detect or display the dimensions of uploaded images.');
}

// Check for IMAP extension:
if(!($requirements['extensions']['imap']=extension_loaded('imap'))){
	$reqMessages[1][] = '<a href="http://www.php.net/manual/book.imap.php">imap extension</a>: '.$rbm.'. '.installer_t('The email manager module requires the IMAP extension to function.');
}

// Check for SSH2 extension
if(!($requirements['extensions']['ssh2']=extension_loaded('ssh2'))){
	$reqMessages[1][] = '<a href="http://www.php.net/manual/book.ssh2.php">ssh2 extension</a>: '.$rbm.'. '.installer_t('The FileUtil class needs the SSH2 extension to use SSH as a file operation method.');
}
if(!($requirements['extensions']['iconv']=extension_loaded('iconv'))){
	$reqMessages[1][] = '<a href="http://www.php.net/manual/book.iconv.php">iconv extension</a>: '.$rbm.'. '.installer_t('A number of components require the iconv module for encoding and will not function properly.');
}

// Determine if there are messages to show and if installation is even possible
$hasMessages = false;
foreach($reqMessages as $severity=>$messages) {
	if((bool)count($messages))
		$hasMessages = true;
}
$canInstall = !(bool) count($reqMessages[3]);

///////////////////////////////
// END OF REQUIREMENTS CHECK //
///////////////////////////////

////////////////////
// COMPOSE OUTPUT //
////////////////////
$output = '';

if(!$canInstall){
	$output .= '<div style="width: 100%; text-align:center;"><h1>'.installer_t("Cannot $scenario X2Engine")."</h1></div>\n";
	$output .= "<strong>".installer_t('Unfortunately, your server does not meet the minimum system requirements;')."</strong><br />";
}else if($hasMessages){
	$output .= '<div style="width: 100%; text-align:center;"><h1>'.installer_t('Note the following:').'</h1></div>';
}else if($standalone){
	$output .= '<div style="width: 100%; text-align:center;"><h1>'.installer_t('This webserver can run X2Engine!').'</h1></div>';
}

$severityClasses = array(
	1 => 'minor',
	2 => 'major',
	3 => 'critical'
);
$severityStyles = array(
	1 => 'color:black',
	2 => 'color:#CF5A00',
	3 => 'color: #DD0000'
);

if($hasMessages){
	$output .= "\n<ul>";
	foreach($reqMessages as $severity => $messages){
		foreach($messages as $message){
			$output .= "<li style=\"{$severityStyles[$severity]}\">$message</li>";
		}
	}
	$output .= "</ul>\n";
	$output .= "<div style=\"text-align:center;\">Severity legend: ";
	foreach($severityClasses as $severity => $class) {
		$output .= "<span style=\"{$severityStyles[$severity]}\">$class</span>&nbsp;";
	}
	$output .= "<br />\n";
	if($canInstall)
		$output .= '<br />'.installer_t("All other essential requirements were met.").'&nbsp;';
	$output .= '</div><br />';
}

if($standalone){
	$imgData = 'iVBORw0KGgoAAAANSUhEUgAAAGkAAAAfCAYAAADk+ePmAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAADMlJREFUeNrs'.
			'W3lwVGUS/82RZBISyMkRwhESIISjEBJAgWRQbhiuci3AqsUVarfcWlZR1xL+2HJry9oqdNUtj0UFQdSwoELCLcoKBUgOi3AkkkyCYi4SSELOSTIzyWz3N/Ne3nszCVnd2qHKNNU1'.
			'731nf91fd/++7wUdiI4cOTKIfp4hfoJ4NPrJ39RAnEn8hsViuaLzGOhMZGTk1Pj4eAwZMqRfRX6mtrY2VFRUoKSkhI1lNrIHsYFmzJwpGnS5XP1a8jMFmUxISEyEwWgML7p+PVPP'.
			'IS4hIQGurq5fFNtaW/Hujh2q9zdef/2+knHUyJEwmUyj2ZNGh0dE/OI8qLy8HMOHD5fXze+JtHvvNz0EBweDjSSs5g86c+Ys6uvrSFlxuHbtKjZt2oTKykrk5OQI4Tg2r1mzBm++'.
			'+SY2b96MnNxcXLvqbnfw4EFMnjIFIdSO29fX18NsNnMcF/14zClTJuPEiRPyu9mcLr/zPOnUXlq71WoV5dkXs2VZtm/fjhdeeEGU79y5U8jgDzL6Mw/VkYEoHyJ1Rqp4tpKCT548'.
			'iY0bNwojHTp0COWUQF0eGa+SgZhabTZqXw8O0xkZGVi/fj0p9ppoW8HKT08XXsH9Fy9eLPpwO1ubTZQz79q1C7GxsfLalf0kWWLJ02rr6pBLm2MRjeMPPbloTr3kSf7gSlJqakqK'.
			'eK4nZbi6XEgkxZuCgtw7nATkZ/4tsZZg0qRJiKTQzMZKm5uGivIK0e8QeVVdbR2VzRXvCWPGiP6lpOizZ84I71m3dq2YT6rjdhHh4bIsPIeyLnbYMGFERlnKfv9v9rsnSUimoqKS'.
			'EyRxEOrq6oU8VZVVwls4X5rIqzgErVy1Cv+kXc27K4WMW0W7nxW5YuVKGZmyB0rr4ecFCxeqoC3X5eXlqdop63helkUgLDLM7t27sXLlKr/piGc1UKh4iVEE76Sfy699/A0iBgYj'.
			'elDwPdtyDikoKBC/Tc1NWEjKDAsLE4Jdzs93ly1YCKPRAIfTKZBOBBmMhZaeuX3N7du4fPkympqahPECjEaM9KzHZArGxW8u4vbtGsTEDBbti4uKEBYaJp5HKtbNctwoLZVl4TKj'.
			'MQDFxUVYunTp/0Q/P4VrqqvBh1nXHAoTP5feOZCHzDNFCA0JxG9WPABL2rhe2xcWFqKqqgoLFiy4bw+Uez/8EJYVK4S3+osYKLnDXZdvVy7+sZaSrQMGgx4GvU5wUnyMV7t/5/2A'.
			'Q18XYUjUAPx4qxE7My+h8nYTfrtmeo+T2yj5J09I7nFuf9O3FBKXL7dg6NBhfpWRHcoNwV2+IXheYRXe+TRPeEdocCAGBAfg2ccfxNTx3VdH331fi7f25yJ1Yizyi25BT4a0Ozph'.
			'a3fgVm0z3iYP+/rbm2i22eU+YTTevJTReOrRsaq5z58/j9ra2h4FnjNnDqKjo1FEIYtZIg5PISEh8ruyXuqjJZ6rrKwM5cQSjU9KQpKHH5o9W4yRnZMt6qRypszMTLkPh8xp06ap'.
			'xpbqeV6eX9vHF62ifNtTVurVk9YtmoiC0hpcKq6Wy/YevYLxo+YhKNCI8ppG/CMjB7HRYbCS14WGBJEhA5GSTMk8fTzWbvscza1u4xgN+u5Q0uHE8QulOJdfhoyXV2NoVKhbcefO'.
			'UQ4o7nEhycnJBNmjcP36dRzOypLLdfSPw5JEynqpj0T5+ZewL2MfARTvzcBGqZg+HUnjkwRQUI5joPPauHHjxXOWQuG8OZJeeVWAG4mk+gkTJggjsX6z7mGkNavXwNnZ6RM46CVP'.
			'6olffOIh4UVymCIPyThRQL92fHjkKoVCHToFVKTfzi4MjQ7FH9fNwp4jl2HQ6RAeGiR4+OAwpE8bJb8zc/jMOFkgEn5P3qz1fdFOg7ROnfqCPPCOLLOq3tOH+fz5c3iLDsaSgVjB'.
			'rEiJpffOrk6f88jja8J2VlamSmfac05f1uaCy6f+BYBxe1LPg4SYjNiyfgZez8jtTmbkXaUV9RTa9ALNcf6JIyPcbW7HK08vBDkZrlprMHBAkNznb5vnY+KYaFwuvoU/7zgrlzeR'.
			'p7GxHM5OsSCJtm3bhrHj1OCDz1HOTqeqHSuWFXXk8GH8esMGWTFK4vXV0dnnX/v2de9c8gyG54GBge7t6nZJtDMU9+jD1zgq3XjmPnXqFB6ZP18gTi/lk8zafnv37vVs7G7qpPX3'.
			'ZAd9t7V75hkTh2Hp7ARVRwflHfac+kYbhpH3dNid+OvvH0ZQAOckBx5fPBEbLFPxq/nJMFP+mZQQg7b2drFnOCdJHBJkFMb2Ugi92+12FTucDrdMinZzPciUc0yJ1epV7/Ks76sv'.
			'vxSIjWk6hbTlFouY10nwng0vmJ6NAQHda/dx+lfKyeMEe8LcB7s+8KBmV699hEEorGnXJm0+L5YPs31AL6vN41B44w6FOad7yxG1dxDyo4U2kgdtsDyAkUMGotXmVoQlLVEoQafT'.
			'iTYdJEgrIcXDZ60UPgPkcceNjBI7iGVQSsH5qeDaNZUMywhtaXf4iBEj5Gc6TuCZLVvUSnG5xNhl5eXda1m9Gh0d9j5dyag9Sf3OIIdBw4ULF8R5qrTEijEJiV6bTduPQUSnIv9E'.
			'RkXhwQcf6jHEe24c7h0zTUEGLJgZj6yzJZ796TYU2QBrF0/G7Kkj0NzSClnV9KN06eraVnxyolCERFOQ0X0jEBSAFeYk8jy7WwaFUtgzvDYKhaj2jg6V8qIIQc2aNQvZ2dki8V+5'.
			'ckVV7/Ksj71MotjY4QRe2sXzsaNHcfzYMdU8zz//J8QnjPHh3d56WrZ8uTAS0/79+/Hi1m1eeUzbL0sBeiSAMZvQZO/AgXfxPbiipglf5dxU5RkmJ4W8xBGRaGltFR7hq29e4S3s'.
			'+DwfdxraqL1LcKDRgKfXzyIvc1HodIp2fdnZop0mni1ZukwOO58eOKCG3C54jc1Kk2SDj2kNBoPPOrmPgviC2DxvnnhmSJ+bmyPKvGTu49q0DBf65kltFOLeO3gFkYNCRA7S0msf'.
			'X8SLG2YSLNd71X1+2oqz+RWqstHDBmELnbdCQ4zCuMpdI9HWrVspdGjzoMOtYI2VIiIjYDbPw4kTx0UIamxoUNVzH1YcX/0wXbp0CUm0e5lmzJyByZMnCc+VPELqo81KvvTEyl2y'.
			'ZClyyJM55/Eve7c0F9dr++3Zs0dcdSmJ04FvO7ju7UktdAjd/lGuyC0l5XUCgg8KNaljc4MNxy5879X3/cyrOErlfJCV2JI2Hi//YT5MBKpaODyqdk23UjhU2ii/Kdlud7jbasIZ'.
			'l6WZzfIO/pJAgraevz3JNySnT8seFhERidjhcQgbONCrjxcI8OFJLnFHaMLiJUvkMxrfmvfmSRw5tGtjdOfTBuiDJ310vBBl1U0YHDnAHQqMeoLNHXg4NR6nc7+X2x2/cIMQXDQS'.
			'hrsvSfO+q8YX2T+oxkqIi6Rc4MCOz3IISXXH38fmj/PyJA4d/N1IG1qYvYAByc836IsWL6GD6icCFmvr09LSxWcL6dD6/nvvYhEplr/OeoEET5++5KQuafz0dJyh8e+SBynn7/Lh'.
			'SUUEMji3KonlCFYciL2BQw8x88BXxfi26DZiY8JQeaeZDqAmNLZ04KXfmckYg1BaXgtr2V25/Ssf5eCNZx+mfKPD259dVl0Fidtta7VgLfGFbHNLi8pKyjONRPzJgD1GpU+F/Cmp'.
			'qcIQVVWVXvX8yWPtuvU0bob7rEfI8ZoGPWr7aL9O+NKT+xzk8lztrMbuD3b1WC/R31991Wuc5557HrFxcf/djcMpAgn7ThWJBG8tq0fUoGDKR2146tEUJMaF425DIx57ZCyFLYM8'.
			'YIvNgbc+zceNigbcrGoUl7N9Yf4c4T6V955g+aLXVzul3Ct93IFJdSmpKXjyyY0+7/K0h9Te5vF1o8A8cdJE8bX4p9w46D1r87pxYATNnyoSPXdSSvrLrmykJI/ApaIqCnWhaKYQ'.
			'NyE+BptWPYA7tXUyXLz+Qz2+KaiB3elCOx1oOZytmDsWe49dpdBo6NNN7/vblqGWwkRNTTX0vbSLjomBTm8QIcXpsMtw2qGBrhXlZQRiAnusNxJ642ukGzduwGG3y4bhz+VjE8cK'.
			'eN7e3qGaR5pbOX5IyACEE2jhHCORso+yXimTL/IlJ1Ml9WMj3R05Oj6cP5apdi0peEBwiIinep1e/uVbA0ZZqjMUJc7AgAAvtNKbUGoY36nOI/0kE9/Ss2Uy6+tqn4gZrP7L1S6y'.
			'foOjqU8D2Qh62jxXLmro3t6v5Z9BfI9ot3c0sJFeamluXqXX68PDwyMoNhr6tXOfGOh2jQBZz+g8d16j+IxFbOY/wOgn/xJf9HY6nTfZQBaLJUunrPT88f7UfjX5nRr4f1NIL/8R'.
			'YABtitvxQEn6dgAAAABJRU5ErkJggg==';
	$output .= '<div style="display:block;float:none;margin-left:275px; width: 150px; display:inline-block"><img style="display:block;float:none;" src="data:image/png;base64,'.$imgData.'"><br />';
    $output .= '<a style="display:block;float:none; padding:0; color: #6A6AA8;font-family:monospace;font-weight:bold; text-decoration:none;" href="javascript:void(0);" onclick="showHidePhpInfo(this);">[ + phpinfo()]</a></div><br /><br />';
    $output .= "<div id=\"phpInfoContent\" class=\"hidden\"><div style=\"font-family:monospace;text-align:center\">PHP_SAPI == \"".
            PHP_SAPI."\"</div><br />$phpInfoContent</div>";
    $output .= '
<script type="text/javascript">
function showHidePhpInfo(elt) {
    var content = document.getElementById("phpInfoContent");
    if (content.getAttribute("class") == "hidden") {
        elt.innerHTML = "[ - phpinfo()]";
        content.setAttribute("class","");
    } else {
        elt.innerHTML = "[ + phpinfo()]";
        content.setAttribute("class","hidden");
    }
}
</script>';
}

/////////
// FIN //
/////////
if(!$returnArray){
    echo strtr($document, array(
        '{headerContent}' => $phpInfoStyle,
        '{bodyContent}' => $output
    ));
    $responding = true; // We made it! No need to perform the shutdown function for fatal errors.
    restore_error_handler();
    restore_exception_handler();
}else{
    return compact('requirements','reqMessages','canInstall','hasMessages');
}
?>
