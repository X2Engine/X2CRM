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
mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');
Yii::app()->params->profile = ProfileChild::model()->findByPk(1);
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->language; ?>" lang="<?php echo Yii::app()->language; ?>">
<head>
<meta charset="UTF-8" />
<meta name="language" content="<?php echo Yii::app()->language; ?>" />
<title><?php echo CHtml::encode($this->pageTitle); ?></title>
<?php $this->renderGaCode('public'); ?> 

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

function errorize(element) {
	element.style['border-color'] = "#C00";
	element.style['background-color'] = "#FEE";
}
 
function validateField(field) {
	var input = document.forms['<?php echo $type; ?>']['Services[' + field + ']'];
	if (typeof field !== "undefined" && input.value.trim() == "" || (field == "email" && input.value.match(/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/) == null)) {
		errorize(input);
		return false; 
	}
	return true;
}

function validate() {
	var proceed = true;
	var fields = ['firstName', 'lastName', 'email', 'description'];
	for (var i=0; i<fields.length; i++) {
		if (!validateField(fields[i])) {
			proceed = false;
		}
	}
	return proceed;
}
</script>
</head>
<body>

<?php
foreach(Yii::app()->user->getFlashes() as $key => $message) {
    echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
} ?>

<form name="<?php echo $type; ?>" action="<?php echo $this->createUrl($type); ?>" method="POST">
	<div class="row"><b><?php echo Contacts::model()->getAttributeLabel('firstName'); ?>: *</b><br /> <input style="width:170px;" type="text" name="Services[firstName]" /><br /></div>
	<div class="row"><b><?php echo Contacts::model()->getAttributeLabel('lastName'); ?>: *</b><br /> <input style="width:170px;" type="text" name="Services[lastName]" /><br /></div>
	<div class="row"><b><?php echo Contacts::model()->getAttributeLabel('email'); ?>: *</b><br /> <input style="width:170px;" type="text" name="Services[email]" /><br /></div>
	<div class="row"><b><?php echo Contacts::model()->getAttributeLabel('phone'); ?>: </b><br /> <input style="width:170px;" type="text" name="Services[phone]" /><br /></div>
	<div class="row"><b><?php echo Services::model()->getAttributeLabel('description'); ?>: *</b><br /> <textarea style="height:100px;width:170px;font-family:arial;font-size:10px;" name="Services[description]" onfocus="clearText(this);"></textarea><br /></div>
	<?php foreach ($_GET as $key=>$value) { ?>
		<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>" />
	<?php } ?>
	<input id='submit' type="submit" value="Submit" onclick="clearText(document.forms['<?php echo $type; ?>']['Services[description]']); return validate();"/>
</form>
</body>
</html>
