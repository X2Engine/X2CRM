<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/

// run silent installer with default values?
$silent = isset($_GET['silent']) || (isset($argv) && in_array('silent',$argv));

if($silent) {
	header('Location: initialize.php?silent');
	exit;
}


// scan for installed language folders
$languageDirs = scandir('./protected/messages');
$languages = array();

foreach ($languageDirs as $code) {		// look for langauges name
	$name = getLanguageName($code);		// in each item in $languageDirs
	if($name!==false)
		$languages[$code] = $name;	// add to $languages if name is found
}
$lang = isset($_GET['lang'])? strtolower($_GET['lang']) : '';	// get language setting, default to none (english)

if (array_key_exists($lang,$languages))				// is this language installed?
	$installMessageFile = "protected/messages/$lang/install.php";

$installMessages = array();

if(isset($installMessageFile) && file_exists($installMessageFile)) {	// attempt to load installer messages
	$installMessages = include($installMessageFile);					// from the chosen language
	if (!is_array($installMessages))
		$installMessages = array();						// ...or return an empty array
}

function getLanguageName($code) {	// lookup language name for the language code provided
	global $languageDirs;

	if (in_array($code,$languageDirs)) {	// is the language pack here?
		$appMessageFile = "protected/messages/$code/app.php";
		if(file_exists($appMessageFile)) {	// attempt to load 'app' messages in
			$appMessages = include($appMessageFile);					// the chosen language
			if (is_array($appMessages) and isset($appMessages['languageName']) && $appMessages['languageName']!='Template')
				return $appMessages['languageName'];							// return language name
		}
	}
	return false;	// false if languge pack wasn't there
}

// translates by looking up string in install.php language file
function installer_t($str) {
	global $installMessages;
	if(isset($installMessages[$str]) && $installMessages[$str]!='')		// if the chosen language is available
		return $installMessages[$str];									// and the message is in there, use it
	return $str;
}

$themeURL = 'themes/x2engine';

// check for submitted data (errors from initialize.php)
$dbStatus = '';

if(isset($_GET['errors'])) {

	$errorMessages = $_GET['errors'];
	
	for($i = 0; $i<count($errorMessages); $i++) {
		if($i < 0) break;
		if($errorMessages[$i] == 'DB_COULD_NOT_SELECT') {
			$dbStatus = '<img src="'.$themeURL.'/images/NOT_OK.png">'.addslashes(installer_t('Could not select database.'));
			unset($errorMessages[$i]);
			$i--;
		}
		if($i < 0) break;
		if($errorMessages[$i] == 'DB_CONNECTION_FAILED') {
			$dbStatus = '<img src="'.$themeURL.'/images/NOT_OK.png">'.addslashes(installer_t('Could not connect to host.'));
			unset($errorMessages[$i]);
			$i--;
		}
	}
}

function getField($name,$default) {
	if(isset($_GET[$name])) {
	
		if($name == 'data' && $_GET[$name] == 1)
			echo ' checked="checked"';
		else
			echo $_GET[$name];
	}
	else {
		echo $default;
	}
}
function checkCurrency($code) {
	if(isset($_GET['currency'])) {
		if($_GET['currency']==$code)
			echo ' selected="selected"';

	} else if($code=='USD')
		echo ' selected="selected"';
}


?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta charset="UTF-8" />
<meta name="language" content="en" />
<link rel="icon" href="images/favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/screen.css" media="screen, projection" />
<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/main.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/form.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/install.css" />

<script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript">

function validate(form) {
	if(form.adminPass.value == form.adminPass2.value) {
		return true;
	} else {
		alert("Passwords do not match!");
		return false;
	}
}

function changeLang(lang) {
	window.location=('install.php?lang='+lang);
} 
$(function() {
	$('#db-test-button').click(testDB);
	
	$('#currency').change(function() {
		if($('#currency').val() == 'other')
			$('#currency2').fadeIn(300);
		else
			$('#currency2').fadeOut(300);
	});
});


function testDB() {
	
	var data = $('#install').serialize()+'&testDb=1';
	
	$.ajax({
		type: "POST",
		url: "initialize.php",
		data: data,
		beforeSend: function() {
		
			$('#response-box').html('<img src="images/loading.gif">');
		
		
		},
		success: function(response) {
		
			var message = '';
			
			var okImage = '<img src="<?php echo $themeURL; ?>/images/OK.png">';
			var notOkImage = '<img src="<?php echo $themeURL; ?>/images/NOT_OK.png">';
		
			if(response.indexOf('DB_OK') > -1)
				message = okImage + '<?php echo addslashes(installer_t('Connection OK!')); ?>';
			if(response.indexOf('DB_CONNECTION_FAILED') > -1)
				message = notOkImage + '<?php echo addslashes(installer_t('Could not connect to host.')); ?>';
			if(response.indexOf('DB_COULD_NOT_SELECT') > -1)
				message = notOkImage + '<?php echo addslashes(installer_t('Could not select database.')); ?>';

			$('#response-box').html(message);
			// $(this).addClass("done");
		}
	});
	
	// alert(data);
}




</script>
</head>
<body>
	
<div id="installer-box">
<h2><?php echo installer_t('Installation Page'); ?></h2>
<?php echo installer_t('Welcome to the X2Engine application installer! We need to collect a little information before we can get your application up and running. Please fill out the fields listed below.'); ?>
<?php if(!empty($errorMessages)) { ?>
<div class="form" id="error-box">
<?php foreach($errorMessages as $message) {
	echo $message."<br />\n";
} ?>
</div>
<?php } ?>
<div class="wide form" id="install-form">
<form name="install" id="install" action="initialize.php" method="POST" onSubmit="return validate(this);"> 
	<h2><?php echo installer_t('X2Engine Application Info'); ?></h2><hr>
	<div class="row"><label for="app"><?php echo installer_t('Application Name'); ?></label><input type="text" name="app" id="app" value="<?php getField('app','X2Engine'); ?>" style="width:190px" /></div>
	<div class="row"><label for="lang"><?php echo installer_t('Default Language'); ?></label>
	<select name="lang" id="lang" onChange="changeLang(this.options[this.selectedIndex].value);" style="width:200px"><option value="">English</option>
	<?php

	foreach ($languageDirs as $code) {	// generate language dropdown
		$languageName = getLanguageName($code);	// lookup language name
		if($languageName!==false) {
			$selected = ($code == $_GET['lang'])? ' selected' : '';	// mark option selected if user has chosen this language
			echo "		<option value=\"$code\"$selected>$languageName</option>\n";	// list all available languages
		}
	} 

	// flag images are public domain from http://www.famfamfam.com/lab/icons/flags
	$flagUrl = file_exists("images/flags/$lang.png")? "images/flags/$lang.png" : "images/flags/us.png"; 

	echo '</select> <img src="'.$flagUrl.'">'; ?></div>

	<div class="row"><label for="currency"><?php echo installer_t('Currency'); ?></label>
		<select name="currency" id="currency">
			<option value="USD"<?php checkCurrency('USD'); ?>>USD</option>
			<option value="EUR"<?php checkCurrency('EUR'); ?>>EUR</option>
			<option value="GBP"<?php checkCurrency('GBP'); ?>>GBP</option>
			<option value="CAD"<?php checkCurrency('CAD'); ?>>CAD</option>
			<option value="JPY"<?php checkCurrency('JPY'); ?>>JPY</option>
			<option value="CNY"<?php checkCurrency('CNY'); ?>>CNY</option>
			<option value="CHF"<?php checkCurrency('CHF'); ?>>CHF</option>
			<option value="INR"<?php checkCurrency('INR'); ?>>INR</option>
			<option value="BRL"<?php checkCurrency('BRL'); ?>>BRL</option>
			<option value="other"<?php checkCurrency('other'); ?>><?php echo installer_t('Other'); ?></option>
		</select>
		<input type="text" name="currency2" id="currency2" style="width:120px;<?php if(!isset($_GET['currency']) || $_GET['currency']!='other') echo 'display:none;'; ?>" value="<?php getField('currency2',''); ?>" />
	</div>

	<div class="row"><label for="dummyData"><?php echo installer_t('Create sample data'); ?></label><input type='checkbox' name='data' value='1' <?php getField('data',''); ?> /><br /><br /></div>
	<div class="row"><label for="adminPass"><?php echo installer_t('Admin Password'); ?></label><input type="password" name="adminPass" id="adminPass" /></div>
	<div class="row"><label for="adminPass2"><?php echo installer_t('Confirm Password'); ?></label><input type="password" name="adminPass2" id="adminPass2" /></div>
	<div class="row"><label for="adminEmail"><?php echo installer_t('Administrator Email'); ?></label><input type="text" name="adminEmail" id="adminEmail" value="<?php getField('adminEmail',''); ?>" /></div>

	<h2><?php echo installer_t('Database Connection Info'); ?></h2><hr>
	
	<div id="db-test-box"><input type="button" id="db-test-button" class="x2-button" value="<?php echo installer_t('Test Connection'); ?>" />
	<div id="response-box"><?php echo $dbStatus; ?></div>
	</div>
	<div id="db-form-box">
		<?php echo installer_t('This beta release only supports MySQL.'); ?> <?php echo installer_t('Please create a database before install.');?><br /><br />
		<div class="row"><label for="dbHost"><?php echo installer_t('Host Name'); ?></label><input type="text" name="dbHost" id="dbHost" value="<?php getField('dbHost','localhost'); ?>" /></div>
		<div class="row"><label for="dbName"><?php echo installer_t('Database Name'); ?></label><input type="text" name="dbName" id="dbName" value="<?php getField('dbName','x2engine'); ?>" /></div>
		<div class="row"><label for="dbUser"><?php echo installer_t('Database Username'); ?></label><input type="text" name="dbUser" id="dbUser" value="<?php getField('dbUser','root'); ?>" /></div>
		<div class="row"><label for="dbPass"><?php echo installer_t('Database Password'); ?></label><input type="password" name="dbPass" id="dbPass" /></div>
	</div>
	<div class="row"><input type="submit" id="button" class="x2-button" value="<?php echo installer_t('Install'); ?>" /><br /></div>
	<a href="http://www.x2engine.com"><?php echo installer_t('For help or more information - X2Engine.com'); ?></a>
</form>
</div>
<div id="footer">
	
	
	Copyright &copy; <?php echo date('Y'); ?><a href="http://www.x2engine.com">X2Engine Inc.</a><br /> 
	<?php echo installer_t('All Rights Reserved.'); ?>
</div>
</div>  
</body>
</html>