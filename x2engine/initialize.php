<?php 
/*********************************************************************************
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
 ********************************************************************************/

// Test the connection and exit:
if(isset($_POST['testDb'])) {
	
	$con = @mysql_connect($_POST['dbHost'],$_POST['dbUser'],$_POST['dbPass']);
	
	if($con !== false) {
		if($selectDb = @mysql_select_db($_POST['dbName'],$con))
			echo 'DB_OK';
		else
			echo 'DB_COULD_NOT_SELECT';
			
		@mysql_close($con);
	} else
		echo 'DB_CONNECTION_FAILED';
	exit;
}

////////////////////
// Global Objects //
////////////////////
$response = array();
$config = array();
$editions = require_once(dirname(__FILE__).'/protected/data/editions.php'); // Add editions as necessary
$stageLabels = require_once(dirname(__FILE__).'/protected/data/installStageLabels.php');
$enabledModules = require_once(dirname(__FILE__).'/protected/data/enabledModules.php');
// Run the silent installer with default values?
$silent = isset($_GET['silent']) || (isset($argv) && in_array('silent',$argv));

////////////////////////////////
// Load Install Configuration //
////////////////////////////////
include(dirname(__FILE__).'/protected/config/X2Config.php');
$x2Version = $version;
$userData = '';
if($silent) {
	if(file_exists('installConfig.php')){
		require('installConfig.php');
    } else
		die(installer_t('Error: Installer config file not found.'));
} else {
	$host = $_POST['dbHost'];
	$db = $_POST['dbName'];
	$user = $_POST['dbUser'];
	$pass = $_POST['dbPass'];
	$app = $_POST['app'];	
	
	$currency = $_POST['currency'];
	$currency2 = strtoupper($_POST['currency2']);
	if($currency == 'other')
		$currency = $currency2;
	if(empty($currency))
		$currency = 'USD';
	
	$lang = $_POST['language'];
	$timezone = $_POST['timezone'];
	
	$adminEmail = $_POST['adminEmail'];
	$adminPassword = $_POST['adminPass'];
	$adminPassword2 = $_POST['adminPass2'];
	$firstName = $_POST['firstName'];
	$lastName = $_POST['lastName'];
	$dummy_data = (isset($_POST['dummy_data']) && $_POST['dummy_data']==1)? 1 : 0;
	$receiveUpdates = (isset($_POST['receiveUpdates']) && $_POST['receiveUpdates']==1)? 1 : 0;
	$userData .= "&unique_id={$_POST['unique_id']}";
	$userData .= "&dbHost=$host&dbName=$db&dbUser=$user&app=$app&currency=".$_POST['currency']."&currency2=$currency2&language=$lang&adminEmail=$adminEmail&dummy_data=$dummy_data&receiveUpdates=$receiveUpdates&timezone=".urlencode($timezone);
	$webLeadUrl=$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	$unique_id = isset($_POST['unique_id']) ? $_POST['unique_id'] : 'none';
}

// Deterine edition info
if (!empty($_POST['edition'])) {
	$edition = $_POST['edition'];
} else {
	$edition = 'opensource';
	foreach ($editions as $ed)
		if (file_exists("initialize_$ed.php"))
			$edition = $ed;
}

// Determine if dummy data is to be installed
$dummy_data = isset($dummy_data) ? $dummy_data : (isset($dummyData) ? $dummyData : 0);

// Generate API Key
$apiKey=substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',16)),0,16);

// Set up language & translations:
if(empty($lang))
	$lang='en';
$installMessageFile = "protected/messages/$lang/install.php";
$installMessages = array();
if(isset($installMessageFile) && file_exists($installMessageFile)) {	// attempt to load installer messages
	$installMessages = include($installMessageFile);					// from the chosen language
	if (!is_array($installMessages))
		$installMessages = array();						// ...or return an empty array
}

// Timezone
if(empty($timezone))
	$timezone='UTC';
date_default_timezone_set($timezone);

// EMail address for sending
if(!empty($adminEmail))
	$bulkEmail = $adminEmail;
else
	$bulkEmail = 'contact@'.preg_replace('/^www\./','',$_SERVER['HTTP_HOST']);

// Generate config file content:
$gii = 1;
if ($gii == '1') {
	$gii = "array(\n\t'class'=>'system.gii.GiiModule',\n\t'password'=>'".str_replace("'","\\'",$adminPassword)."', \n\t/* If the following is removed, Gii defaults to localhost only. Edit carefully to taste: */\n\t 'ipFilters'=>false,\n)";
} else {
	$gii = "array(\n\t'class'=>'system.gii.GiiModule',\n\t'password'=>'password',\n\t/* If the following is removed, Gii defaults to localhost only. Edit carefully to taste: */\n\t 'ipFilters'=>array('127.0.0.1', '::1'),\n)";
}
$webLeadUrl = substr($webLeadUrl, 0, -15);
$stats = isset($userData) ? $userData : "language=$lang&currency=$currency&x2_version=$x2Version&dummy_data=$dummy_data&php_version=$phpVersion&db_type=$dbType&GD_support=$GDSupport&user_agent=$browser&timezone=$timezone&unique_id=$unique_id";

$X2Config = "<?php\n\$appName='$app';\n\$email='$adminEmail';\n\$host='$host';\n\$user='$user';\n\$pass='$pass';\n\$dbname='$db';\n\$version='$x2Version';\n\$buildDate=$buildDate;\n\$updaterVersion='2.0';\n";
$X2Config .= (empty($lang)) ? '$language=null;' : "\$language='$lang';";

// Save config values to be inserted in the database:
$time = time();
foreach (array('adminEmail', 'adminPassword', 'apiKey', 'currency', 'time', 'unique_id', 'edition', 'bulkEmail', 'lang', 'timezone') as $property)
	if (isset(${$property}))
		$config[":$property"] = ${$property};


///////////////////////
// Declare Functions //
///////////////////////
/*
 * Translation function
 */
function installer_t($str) {	// translates by looking up string in install.php language file
	global $installMessages;
	if(isset($installMessages[$str]) && $installMessages[$str]!='')		// if the chosen language is available
		return $installMessages[$str];									// and the message is in there, use it
	return $str;
}

/**
 * Translation function wrapper (for using parameters)
 */
function installer_tr($str,$params) {
	return strtr(installer_t($str),$params);
}

/**
 * Redirect to the installer with errors.
 */
function outputErrors() {
	global $response,$userData,$silent;
	if (!$silent) {
		if (!isset($_GET['stage'])) {
			if (isset($response['errors'])) {
				foreach ($response['errors'] as &$error)
					$error = urlencode($error);  // url encode errors

				if (count($response['errors']) > 0) {
					$errorData = implode('&errors%5B%5D=', $response['errors']);
					$url = preg_replace('/initialize/', 'install', $_SERVER['REQUEST_URI']);
					print_r($errors);
					header("Location: $url?errors%5B%5D=" . $errorData . $userData);
					die();
				}
			}
		}
	}
}

/**
 * Add an error message to the response array.
 * 
 * @global type $response
 * @param type $message 
 */
function addError($message) {
	global $response;
	if(!isset($response['errors'])) {
		$response['errors'] = array();
	}
	$response['errors'][] = $message;
}

$sqlError = '';
function addSqlError($message) {
	global $sqlError;
	if(empty($sqlError))
		$sqlError = $message;
}

/**
 * Backwards-compatible wrapper function for adding validation errors.
 * 
 * @param type $attr
 * @param type $error 
 */
function addValidationError($attr,$error) {
	global $response,$silent;
	if(isset($_GET['stage']) || $silent) {
		if(!isset($response['errors']))
			$response['errors'] = array();
		$response['errors'][$attr] = installer_t($error);
	} else {
		// Slip the validation error into the GET parameters as [attribute]--[errormessage]
		$response['errors'][] = "$attr--$error";
	}
}

function respond($message,$error=Null) {
	global $response,$silent;
	if($error)
		$response['globalError'] = $error;
	if($silent) {
		echo "$message\n";
	} else if(isset($_GET['stage'])) {
		header('Content-Type: application/json');
		$response['message'] = $message;
		echo json_encode($response);
		exit(0);
	}
}

/**
 * Wrapper for "die"
 */
function RIP($message) {
	global $silent, $response;
	if($silent) {
		die($message."\n");
	} else {
		$response['failed'] = 1;
		respond($message);
	}
}

/**
 * Installs a named module
 * 
 * @global PDO $dbo
 * @param type $module 
 */
function installModule($module,$respond = True) {
	global $dbo;
	$moduleName = installer_t($module);
	$install = file_exists($regFile = dirname(__FILE__) . "/protected/modules/$module/register.php");
	if ($install) {
		$install = require_once($regFile);
		foreach ($install['install'] as $sql) {
			// Install a module.
			// For each element in the register script's "install" array, if it's a 
			// string, treat it as a path to an SQL script. Otherwise, if an array,
			// treat as a list of SQL statements.
			if (is_string($sql)) {
				if (file_exists($sql)) {
					try {
						$statement = $dbo->prepare(file_get_contents($sql));
						$statement->execute() or RIP(installer_tr("Error installing module \"{module}\"; SQL from file {file} failed with message ",array('{file}'=>$sql,'{module}'=>$moduleName)). implode(',',$statement->errorInfo()));
					} catch (PDOException $e) {
						RIP(installer_tr("Could not install {module}; ",array('{module}'=>$moduleName)) . $e->getMessage());
					}
				} else {
					RIP("Error installing $module; file does not exist: $sql");
				}
			} else if (is_array($sql)) {
				foreach ($sql as $sqlcom) {
					$statement = $dbo->prepare($sqlcom);
					$statement->execute() or RIP(installer_tr("Error installing {module}. SQL statement {sql}... failed;", array('{sql}'=>substr($sqlcom,0,10),'{module}'=>$moduleName)). implode(',',$statement->errorInfo()));
				}
			}
		}
		if($respond)
			respond(installer_tr("Module \"{module}\" installed.", array('{module}'=>$moduleName)));
	}
}

/**
 * Runs a named piece of the installation.
 * 
 * @param $stage The named stage of installation.
 */
function installStage($stage) {
	global $editions, $silent, $dbo,$config, $stageLabels, $response,$webLeadUrl,$write,$X2Config,$enabledModules;
	if ($stage == 'validate') {
		// Perform ad-hoc validation.
		if (empty($_POST['adminEmail']) || !preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $_POST['adminEmail']))
			addValidationError('adminEmail', 'Please enter a valid email address.');
		if ($_POST['adminPass'] == '')
			addValidationError('adminPass', 'Admin password cannot be blank.');
		if (!isset($_POST['adminPass2']))
			addValidationError('adminPass2', 'Please confirm the admin password.');
		else if ($config[':adminPassword'] != $_POST['adminPass2'])
			addValidationError('adminPass2', 'Admin passwords did not match.');
		if(!empty($response['errors'])) {
			respond(installer_t('Please correct the following errors:'));
		}
	} else if ($stage == 'module') {
		if (isset($_GET['module'])) {
			// Install only a named module
			installModule($_GET['module']);
		} else {
			// Install all modules:
			foreach ($enabledModules as $module)
				installModule($module,$silent);
		}
	} else if ($stage == 'config') {
		// Configure with initial data and write files

		$contents = file_get_contents('webLeadConfig.php');
		$contents = preg_replace('/\$url=\'\';/', "\$url='$webLeadUrl'", $contents);
		$contents = preg_replace('/\$user=\'\';/', "\$user='api'", $contents);
		$contents = preg_replace('/\$password=\'\';/', "\$password='{$config[':apiKey']}'", $contents);
		file_put_contents('webLeadConfig.php', $contents);

		$filename = 'protected/config/X2Config.php';
		$handle = fopen($filename, 'w') or RIP(installer_t('Could not create configuration file.'));

		fwrite($handle, $X2Config);
		fclose($handle);
		
		$config[':adminPassword'] = md5($config[':adminPassword']);
		try {
			$installConf = $dbo->prepare(file_get_contents(dirname(__FILE__) . '/protected/data/config.sql'));
			$installConf->execute($config) or die(installer_t('Error applying initial configuration').': ' . implode(',', $installConf->errorInfo()));
		} catch (Exception $e) {
			die($e->getMessage());
		}
		
	} else if ($stage == 'finalize') {
		/**
		 * Look for additional initialization files and perform final tasks
		 */
		foreach ($editions as $ed) // Add editional prefixes as necessary
			if (file_exists("initialize_$ed.php"))
				include("initialize_$ed.php");
	} else {
		// Look for a named SQL file and run it:
		if (file_exists($sqlFile = dirname(__FILE__) . "/protected/data/$stage.sql")) {
			$statement = $dbo->prepare(file_get_contents($sqlFile));
			try {
				$statement->execute() or RIP(installer_tr('Could not {stage}. SQL from {file} failed',array('{stage}'=>$stageLabels[$stage],'{file}'=>$sqlFile)).'; ' . implode(',', $statement->errorInfo()));
			} catch (PDOException $e) {
				die($e->getMessage());
			}
			// Hunt for init SQL files associated with other editions:
			foreach ($editions as $ed) {
				if (file_exists($sqlFile = dirname(__FILE__) . "/protected/data/$stage-$ed.sql")) {
					$statement = $dbo->prepare(file_get_contents($sqlFile));
					try {
						$statement->execute() or RIP(installer_tr('Could not {stage}. SQL from {file} failed',array('{stage}'=>$stageLabels[$stage],'{file}'=>$sqlFile)).'; ' . implode(',', $statement->errorInfo()));
					} catch (PDOException $e) {
						RIP(installer_tr("Could not {stage}",array('{stage}'=>$stageLabels[$stage])).'; ' . $e->getMessage());
					}
				}
			}
		}
	}
	if(in_array($stage,array_keys($stageLabels)) && $stage != 'finalize')
		respond(installer_tr("Completed: {stage}",array('{stage}'=>$stageLabels[$stage])));
}

// Translate response messages
foreach(array_keys($stageLabels) as $stage) {
	$stageLabels[$stage] = installer_t($stageLabels[$stage]);
}

// App name:
$app = mysql_escape_string($app);
if (!$silent) {
	if (!isset($_GET['stage'])) {
		if (!empty($adminEmail) || !preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $adminEmail))
			addValidationError('adminEmail', 'Please enter a valid email address.');

		if ($adminPassword == '')
			addValidationError('adminPass', 'Admin password cannot be blank.');

		if (isset($adminPassword2) && $adminPassword != $adminPassword2)
			addValidationError('adminPass2', 'Admin passwords did not match.');
	}
}

// Establish database connection
try {
	$dbo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
	$con = @mysql_connect($host,$user,$pass) or addError('DB_CONNECTION_FAILED');
	@mysql_select_db($db,$con) or addError('DB_COULD_NOT_SELECT');
} catch (PDOException $e) {
	// Database connection failed. Send validation errors.
	foreach(array('dbHost'=>'Host Name','dbName'=>'Database Name','dbUser'=>'Username','dbPass'=>'Password') as $attr=>$label) {
		if(empty($_POST[$attr])) {
			addValidationError($attr,installer_tr('{attr}: cannot be blank',array('{attr}'=>installer_t($label))));
		} else {
			addValidationError($attr,installer_tr('{attr}: please check that it is correct',array('{attr}'=>installer_t($label))));
		}
	}
	respond(installer_t('Database connection error'),htmlentities($e->getMessage()));
}


$complete = (isset($_POST['complete']) ? $_POST['complete']==1 : False);

if(!$complete)
	outputErrors();

// Install everything all at once:
if (($silent || !isset($_GET['stage']))&& !$complete) {
	// Install core schema/data, modules, and configure:
	foreach (array('core', 'RBAC', 'timezoneData', 'module', 'config', 'finalize') as $component)
		installStage($component);
} else if (isset($_GET['stage'])) {
	installStage($_GET['stage']);
}

if (!$complete || $silent) {
	if ($dummy_data) {
		include("dummydata.php");
		
	}

	mysql_close($con);

	if (!empty($sqlError))
		$errors[] = 'MySQL Error: ' . $sqlError;
	outputErrors();


	$GDSupport = function_exists('gd_info') ? '1' : '0';
	$browser = urlencode(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
	$phpVersion = urlencode(phpversion());
	$x2Version = urlencode($x2Version);
	$timezone = urlencode($timezone);
	$dbType = urlencode('MySQL');

	respond('Installation complete.');
}

// Generate splash page
if(!$silent || $complete):
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta charset="UTF-8" />
<meta name="language" content="en" />
<title><?php echo installer_t('Installation Complete'); ?></title>
<?php $themeURL = 'themes/x2engine'; ?>
<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/screen.css" media="screen, projection" />
<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/main.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/form.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/install.css" />
<link rel="icon" href="images/favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
<style type="text/css">
body {
	background-color:#fff;
	padding-top:50px;
}
</style>
<script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="js/backgroundImage.js"></script>
</head>
<body>
<!--<img id="bg" src="uploads/defaultBg.jpg" alt="">-->
<div id="installer-box" style="padding-top:20px;">
	<h1><?php echo installer_t('Installation Complete!'); ?></h1>
	<div id="install-form" class="wide form">
		<ul>
			<li><?php echo installer_t('Able to connect to database'); ?></li>
			<li><?php echo installer_t('Dropped old X2Engine tables (if any)'); ?></li>
			<li><?php echo installer_t('Created new tables for X2Engine'); ?></li>
			<li><?php echo installer_t('Created login for admin account'); ?></li>
			<li><?php echo installer_t('Created config file'); ?></li>
		</ul>
		<h2><?php echo installer_t('Next Steps'); ?></h2>
		<ul>
			<li><?php echo installer_t('Log in to app'); ?></li>
			<li><?php echo installer_t('Create new users'); ?></li>
			<li><?php echo installer_t('Set up Cron Job to deal with action reminders (see readme)'); ?></li>
			<li><?php echo installer_t('Set location'); ?></li>
			<li><?php echo installer_t('Explore the app'); ?></li>
		</ul>
		<h3><a class="x2-button" href="index.php"><?php echo installer_t('Click here to log in to X2Engine'); ?></a></h3><br />
		<?php echo installer_t('X2Engine successfully installed on your web server!  You may now log in with username "admin" and the password you provided during the install.'); ?><br /><br />
	</div>
<a href="http://www.x2engine.com"><?php echo installer_t('For help or more information - X2Engine.com'); ?></a><br /><br />
<div id="footer">
	<div class="hr"></div>
	<!--<img src="images/x2engine_big.png">-->
	Copyright &copy; <?php echo date('Y'); ?><a href="http://www.x2engine.com">X2Engine Inc.</a><br />
	<?php echo installer_t('All Rights Reserved.'); ?>
	<img style="height:0;width:0" src="http://x2planet.com/installs/registry/activity?<?php echo $stats; ?>">	
</div>
</div>
</body>
</html>
<?php
endif;

if ($complete || $silent) {
// delete install files (including self)
	if (file_exists('install.php'))
		unlink('install.php');
	if (file_exists('installConfig.php'))
		unlink('installConfig.php');
	if (file_exists('initialize_pro.php'))
		unlink('initialize_pro.php');
	unlink(__FILE__);
}
?>
