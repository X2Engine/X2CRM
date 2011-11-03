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


$languageDirs = scandir('./protected/messages');	// scan for installed language folders

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

function installer_t($str) {	// translates by looking up string in install.php language file
	global $installMessages;
	if(isset($installMessages[$str]) && $installMessages[$str]!='')		// if the chosen language is available
		return $installMessages[$str];									// and the message is in there, use it
	return $str;
}

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta charset="UTF-8" />
<meta name="language" content="en" />
<link rel="icon" href="images/favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
<?php $themeURL = 'themes/x2engine'; ?>
<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/screen.css" media="screen, projection" />
<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/main.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/form.css" />

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
</script>
<style type="text/css">
body {
	background-color:black;
}
</style>
</head>
<body>
	
<div id="installer-box">
<h2><?php echo installer_t('Installation Page'); ?></h2>
<?php echo installer_t('Welcome to the X2Engine application installer! We need to collect a little information before we can get your application up and running. Please fill out the fields listed below.'); ?>
<div class="wide form" id="install-form">
<form name="install" action="initialize.php" method="POST" onSubmit="return validate(this);"> 
	<h2><?php echo installer_t('X2Engine Application Info'); ?></h2><hr>
	<div class="row"><label for="app"><?php echo installer_t('Application Name'); ?></label><input type="text" name="app" id="app" value="X2Engine" /></div>
	<div class="row"><label for="lang"><?php echo installer_t('Default Language'); ?></label>
	<select name="lang" id="lang" onChange="changeLang(this.options[this.selectedIndex].value);"><option value="">English</option>
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
			<option value="USD" selected="selected">USD</option>
			<option value="EUR">EUR</option>
			<option value="GBP">GBP</option>
			<option value="CAD">CAD</option>
			<option value="JPY">JPY</option>
			<option value="CNY">CNY</option>
			<option value="CHF">CHF</option>
			<option value="INR">INR</option>
			<option value="BRL">BRL</option>
		</select>
	</div>

	<div class="row"><label for="dummyData"><?php echo installer_t('Do you want dummy data?'); ?></label><input type='checkbox' name='data' value='1' /><br /><br /></div>
	<div class="row"><label for="adminPass"><?php echo installer_t('Admin Password'); ?></label><input type ="password" name="adminPass" id="adminPass" /></div>
	<div class="row"><label for="adminPass2"><?php echo installer_t('Confirm Password'); ?></label><input type ="password" name="adminPass2" id="adminPass2" /></div>
	<div class="row"><label for="adminEmail"><?php echo installer_t('Administrator Email'); ?></label><input type="text" name="adminEmail" id="adminEmail" /></div>

	<h2><?php echo installer_t('Database Connection Info'); ?></h2><hr>
	<?php echo installer_t('This beta release only supports MySQL.'); ?>
	<div class="row"><br /><label for="host"><?php echo installer_t('Host Name'); ?></label><input type="text" name="host" id="host" value="localhost" /></div><?php echo installer_t('Please create a database before install.');?>
	<div class="row"><br /><label for="db"><?php echo installer_t('Database Name'); ?></label><input type="text" name="db" id="db" value="x2engine" /></div>
	<div class="row"><label for="user"><?php echo installer_t('Database Username'); ?></label><input type="text" name="user" id="user" value="root" /></div>
	<div class="row"><label for="password"><?php echo installer_t('Database Password'); ?></label><input type="password" name="password" id="password" /></div>

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