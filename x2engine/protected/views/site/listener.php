<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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
mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');
Yii::app()->params->profile = Profile::model()->findByPk(1);
if (empty($type)) $type = 'weblead';
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->language; ?>" lang="<?php echo Yii::app()->language; ?>">
<head>
<meta charset="UTF-8" />
<meta name="language" content="<?php echo Yii::app()->language; ?>" />
<title><?php echo CHtml::encode($this->pageTitle); ?></title>

<style type="text/css">
html {
	<?php
	/* Dear future editors:
	  The pixel height of the iframe containing this page
	  should equal the sum of height, padding-bottom, and 2x border size
	  specified in this block, else the bottom border will not be at the 
	  bottom edge of the frame. Now it is based on 325px height for weblead,
	  and 100px for weblist */

	$height = $type == 'weblist' ? 100 : 325;
	if (!empty($_GET['bs'])) {
		$border = intval(preg_replace('/[^0-9]/', '', $_GET['bs']));
	} else if (!empty($_GET['bc'])) {
		$border = 1;
	} else $border = 0;
	$padding = 36;
	$height = $height - $padding - (2 * $border);

	echo 'border: '. $border .'px solid ';
	if (!empty($_GET['bc'])) echo $_GET['bc'];
	echo ";\n";

	unset($_GET['bs']); 
	unset($_GET['bc']); 
	?>

	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;
	padding-bottom: <?php echo $padding ."px;\n"?>
	height: <?php echo $height ."px;\n"?>
}
body {
	<?php if (!empty($_GET['fg'])) echo 'color: '. $_GET['fg'] .";\n"; unset($_GET['fg']); ?>
	<?php if (!empty($_GET['bgc'])) echo 'background-color: '. $_GET['bgc'] .";\n"; unset($_GET['bgc']); ?>
	<?php 
	if (!empty($_GET['font'])) {
		echo 'font-family: '. FontPickerInput::getFontCss($_GET['font']) .";\n";
		unset($_GET['font']); 
	} else echo "font-family: Arial, Helvetica, sans-serif;\n"; 
	?>
	font-size:12px;
	width:189px;
}
input {
	border: 1px solid #AAA;
}
#contact-header{
	color:white;
	text-align:center;
	font-size: 16px;
}
#submit {
	position:absolute;
	left: 131px;
	margin-top: 7px;
}
</style>

<script>
if (!String.prototype.trim) {
	String.prototype.trim = function() {
		return this.replace(/^\s+|\s+$/g, "");
	};
}

function clearText(field){
	if (typeof field != "undefined" && field.defaultValue == field.value)
		field.value = "";
}
function validateField(field) {

	var input = document.getElementById(field);
	
	input.style.borderColor = "";
	input.style.backgroundColor = "";

	if (input.value.trim() == "" || (field == "email" && input.value.match(/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/) == null)) {
		
		input.style.borderColor = "#c00";
		input.style.backgroundColor = "#fee";
		return false;
	}
	return true;
}

function validate() {

	clearText(document.forms['<?php echo $type; ?>']['Contacts[backgroundInfo]']);


	// alert('ding!');
	var valid = true;
	var fields = ['firstName', 'lastName', 'email'];
	for (var i in fields) {
		valid = validateField(fields[i]) && valid;
	}

	return valid;
}
</script>
</head>
<body>
 
<?php
foreach(Yii::app()->user->getFlashes() as $key => $message) {
    echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
} ?>

<form name="<?php echo $type; ?>" action="<?php echo $this->createUrl($type); ?>" method="POST" onsubmit="return validate();">
	<?php if ($type == 'weblead') { ?>
	<div class="row"><b><?php echo Contacts::model()->getAttributeLabel('firstName'); ?>: *</b><br /> <input style="width:170px;" type="text" id="firstName" name="Contacts[firstName]" /><br /></div>
	<div class="row"><b><?php echo Contacts::model()->getAttributeLabel('lastName'); ?>: *</b><br /> <input style="width:170px;" type="text" id="lastName" name="Contacts[lastName]" /><br /></div>
	<?php } ?>
	<div class="row"><b><?php echo Contacts::model()->getAttributeLabel('email'); ?>: *</b><br /> <input style="width:170px;" type="text" id="email" name="Contacts[email]" /><br /></div>
	<?php if ($type == 'weblead') { ?>
	<div class="row"><b><?php echo Contacts::model()->getAttributeLabel('phone'); ?>:</b><br /> <input style="width:170px;" type="text" id="phone" name="Contacts[phone]" /><br /></div>
	<div class="row"><b>Interest:</b><br /> <textarea style="height:100px;width:170px;font-family:arial;font-size:10px;" id="backgroundInfo" name="Contacts[backgroundInfo]" onfocus="clearText(this);"><?php echo Yii::t('contacts','Enter any additional information or questions regarding your interest here.'); ?></textarea><br /></div>
	<?php } ?>
	<?php foreach ($_GET as $key=>$value) { ?>
		<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>" />
	<?php } ?>
	<input id='submit' type="submit" value="Submit" />
</form>
</body>
</html>
