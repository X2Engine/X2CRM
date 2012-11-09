<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
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

$cs = Yii::app()->clientScript;
$cs->scriptMap = array();
$baseUrl = $this->module->assetsUrl;
$cs->registerPackage('jquerymobile');

// JQuery Mobile CSS framework
//$cs->registerCssFile($baseUrl.'/css/jquery.mobile-1.0b2.css','all');
//
//$cs->registerScriptFile($baseUrl.'/js/jquery-1.6.2.js');
//$cs->registerScriptFile($baseUrl.'/js/jquery.mobile-1.0b2.js');
//
$cs->registerScriptFile($baseUrl . '/js/x2mobile.js');
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
	<div id="<?php echo $this->pageId; ?>" data-role="page" data-url="<?php echo $this->dataUrl; ?>/" data-theme="b">
		<div data-role="header" data-theme="b">
			<div  class="figure"><a href="<?php echo $this->createUrl('site/home');?>" rel="external"><img style="margin-left:20px;" src="<?php echo $this->module->getAssetsUrl() . '/css/images/x2touch-logo.png'; ?>" alt="x2engine" /></a></div>
		</div>
		<div data-role="content">
			<?php
			echo $content;
			?>
		</div>
		<div data-role="footer" data-theme="b">
			<p>&nbsp;&nbsp;&copy; <?php echo date('Y') . ' ' . CHtml::link('X2Engine Inc.', 'http://www.x2engine.com')." ";
				echo Yii::t('app', 'Rights Reserved.'); ?>
				<?php echo CHtml::link(Yii::t('mobile', 'Go to Full Site'),Yii::app()->getBaseUrl().'/index.php/site/index?mobile=false',array('rel'=>'external', 'onClick'=>'setMobileBrowserFalse()')); ?>
			</p>
		</div>
	</div>
</div>
</body>
</html>