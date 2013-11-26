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
