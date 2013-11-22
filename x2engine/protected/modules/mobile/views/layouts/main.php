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

$isGuest = Yii::app()->user->isGuest;
$cs = Yii::app()->clientScript;
$cs->scriptMap = array();
$baseUrl = $this->module->assetsUrl;
$cs->registerCoreScript('jquery');
$cs->registerPackage('jquerymobile');
$cs->registerCssFile($this->module->getAssetsUrl() . '/css/jqueryMobileCssOverrides.css');
$cs->registerCssFile($this->module->getAssetsUrl() . '/css/main.css');
$cs->registerScriptFile($baseUrl . '/js/x2mobile.js');

$jsVersion = '?'.Yii::app()->params->buildDate;
$cs->registerScriptFile(Yii::app()->getBaseUrl ().'/js/auxlib.js'.$jsVersion);
$cs->registerScriptFile(Yii::app()->getBaseUrl ().'/js/jstorage.min.js'.$jsVersion)
   ->registerScriptFile(Yii::app()->getBaseUrl ().'/js/notifications.js'.$jsVersion, 
     CClientScript::POS_BEGIN);


?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta charset="UTF-8" />
<meta name="language" content="en" />
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, maximum-scale=1"/> 
<meta name="apple-mobile-web-app-capable" content="yes"/>
<link rel="icon" href="<?php echo Yii::app()->getBaseUrl(); ?>/images/favicon.ico" type="image/x-icon0" />
<link rel="shortcut icon" href="<?php echo Yii::app()->getBaseUrl(); ?>/images/favicon.ico" type="image/x-icon" />
<title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>
<body> 
<div id="container"> 
	<div id="<?php echo $this->pageId; ?>" data-role="page" data-url="<?php echo $this->dataUrl; ?>/" data-theme="a">
		<div data-role="header" data-theme="a">
			<div  class="figure"><a href="<?php echo $this->createUrl('/mobile/site/home');?>" rel="external"><img style="margin-left:20px;" src="<?php echo $this->module->getAssetsUrl() . '/css/images/x2touch-logo.png'; ?>" alt="x2engine" /></a></div>
		</div>
		<div data-role="content">
			<?php
			echo $content;
			?>
		</div>
		<div data-role="footer" data-theme="a">
			<p>&nbsp;&nbsp;&copy; <?php echo date('Y') . ' ' . CHtml::link('X2Engine Inc.', 'http://www.x2engine.com')." ";
				echo Yii::t('app', 'Rights Reserved.'); ?>
				<?php echo CHtml::link(Yii::t('mobile', 'Go to Full Site'),Yii::app()->getBaseUrl().'/index.php/site/index?mobile=false',array('rel'=>'external', 'onClick'=>'setMobileBrowserFalse()')); ?>
			</p>
		</div>
	</div>
</div>
</body>
</html>
