<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');


Yii::app()->params->profile = ProfileChild::model()->findByPk(1);	// use the admin's profile since the user hasn't logged in
$jsVersion = '?'.Yii::app()->params->buildDate;

// blueprint CSS framework
$themeURL = Yii::app()->theme->getBaseUrl();
Yii::app()->clientScript->registerCssFile($themeURL.'/css/screen.css'.$jsVersion,'screen, projection');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/print.css'.$jsVersion,'print');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/main.css'.$jsVersion,'screen, projection');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/form.css'.$jsVersion,'screen, projection');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/ui-elements.css'.$jsVersion,'screen, projection');


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
	$theme2Css = 'html * {background:url('.CHtml::normalizeUrl(array('/site/warning')).') !important;} #bg{display:none !important;}';

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
	<span id="login-version"><?php echo Yii::app()->params->edition=='pro'? 'PROFESSIONAL EDITION' : 'OPEN SOURCE EDITION'; ?>, VERSION <?php echo Yii::app()->params->version; ?></span>
	<br><span id="login-x2engine"><a href="http://www.x2engine.com">X2Engine, Inc.</a><?php if(Yii::app()->params->admin->edition == 'opensource'){echo '&nbsp;&bull;&nbsp;'.CHtml::link("LICENSE",Yii::app()->baseUrl.'/LICENSE.txt');} ?></span>
    </div>
</body>
</html>
