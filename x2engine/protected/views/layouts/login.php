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


Yii::app()->params->profile = ProfileChild::model()->findByPk(1);	// use the admin's profile since the user hasn't logged in
$jsVersion = '?'.Yii::app()->params->buildDate;

// jQuery and jQuery UI libraries
Yii::app()->clientScript->registerCoreScript('jquery');
Yii::app()->clientScript->registerCoreScript('jquery.ui');

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/layout.js'.$jsVersion);
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/backgroundImage.js'.$jsVersion);

// blueprint CSS framework
$themeURL = Yii::app()->theme->getBaseUrl();
Yii::app()->clientScript->registerCssFile($themeURL.'/css/screen.css'.$jsVersion,'screen, projection');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/print.css'.$jsVersion,'print');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/main.css'.$jsVersion,'screen, projection');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/form.css'.$jsVersion,'screen, projection');

Yii::app()->clientScript->registerScript('checkImages',"
$(document).ready(function() {
	$('#main-menu-icon, #footer-logo, #footer-logo img').css({'display':'inline','visibility':'visible','z-index':'2147483647'});
           
});
",CClientScript::POS_END);


$backgroundImg = '';
$defaultOpacity = 1;
$themeCss = '';

$checkResult = false;
$checkFiles = array(
	'themes/x2engine/images/x2footer.png'=>'1393e4af54ababdcf76fac7f075b555b',
	'themes/x2engine/images/x2-mini-icon.png'=>'153d66b514bf9fb0d82a7521a3c64f36',
);
foreach($checkFiles as $key=>$value) {
	if(!file_exists($key) || hash_file('md5',$key) != $value)
		$checkResult = true;
}
$theme2Css = '';
if($checkResult)
	$theme2Css = 'html * {background:url('.CHtml::normalizeUrl(array('site/warning')).') !important;} #bg{display:none !important;}';
	
if (!empty(Yii::app()->params->profile->pageOpacity)) {
	$defaultOpacity = Yii::app()->params->profile->pageOpacity / 100;
	Yii::app()->clientScript->registerScript('loadPageOpacity',"
		$(document).ready(function() {
			$('#page').fadeTo(0,".$defaultOpacity.");
		});
	",CClientScript::POS_HEAD);
}
	
// check for background image, use it if one is set
if(empty(Yii::app()->params->profile->backgroundImg))
	$backgroundImg = CHtml::image(Yii::app()->getBaseUrl().'/uploads/defaultBg.jpg','',array('id'=>'bg'));
else
	$backgroundImg = CHtml::image(Yii::app()->getBaseUrl().'/uploads/'.Yii::app()->params->profile->backgroundImg,'',array('id'=>'bg'));
	
if(!empty(Yii::app()->params->profile->backgroundColor)) {
	$themeCss .= 'body {background-color:#'.Yii::app()->params->profile->backgroundColor.";}\n";
	
	if(!empty($backgroundImg)) {
		$shadowRgb = 'rgb(0,0,0,0.5)';	// use a black shadow if there is an image
	} else {
		$shadowColor = X2Color::hex2rgb(Yii::app()->params->profile->backgroundColor);	// if there is no BG image, calculate a darker tone for the shadow
		
		foreach($shadowColor as &$value) {
			$value = floor(0.5*$value);
		}
		$shadowRgb = 'rgb('.implode(',',$shadowColor).')';
	}
	$themeCss .= "#page {
-moz-box-shadow: 0 0 30px $shadowRgb;
-webkit-box-shadow: 0 0 30px $shadowRgb;
box-shadow: 0 0 30px $shadowRgb;
}\n";
}
if(!empty(Yii::app()->params->profile->menuBgColor))
	$themeCss .= '#main-menu-bar {background:#'.Yii::app()->params->profile->menuBgColor.";}\n";

if(!empty(Yii::app()->params->profile->menuTextColor))
	$themeCss .= '#main-menu-bar ul a, #main-menu-bar ul span {color:#'.Yii::app()->params->profile->menuTextColor.";}\n";


Yii::app()->clientScript->registerCss('applyTheme',$themeCss,'screen',CClientScript::POS_HEAD);

Yii::app()->clientScript->registerCss('applyTheme2',$theme2Css,'screen',CClientScript::POS_HEAD);

	
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->language; ?>" lang="<?php echo Yii::app()->language; ?>">
<head>
<meta charset="UTF-8" />
<meta name="language" content="<?php echo Yii::app()->language; ?>" />

<meta name="description" content="X2Engine - Open Source Customer Relationship Management (CRM) and Sales Force Application">
<meta name="keywords" content="open source,CRM,customer relationship management,contact management,sales force,php,x2engine,x2crm">


<link rel="icon" href="<?php echo Yii::app()->getBaseUrl(); ?>/images/favicon.ico" type="image/x-icon0" />
<link rel="shortcut-icon" href="<?php echo Yii::app()->getBaseUrl(); ?>/images/favicon.ico" type="image/x-icon" />
<!--[if lt IE 8]>
<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/ie.css" media="screen, projection" />
<![endif]-->
<title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>
<body id="body-tag"  class="login">
<!--<div class="ie-shadow" style="display:none;"></div>-->
<div class="container" id="login-page">
	<?php echo $content; ?>
	<span id="login-version"><?php echo Yii::app()->params->edition=='pro'? 'PROFESSIONAL EDITION' : 'OPEN SOURCE EDITION'; ?></span>
	<br><a href="http://www.x2engine.com" id="login-x2engine">X2Engine, Inc.</a>
</div>
</body>
</html>