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

Yii::import ('application.modules.mobile.components.ThemeGenerator.*');


$isGuest = Yii::app()->user->isGuest;
$cs = Yii::app()->clientScript;
$cs->useAbsolutePaths = true;
$cs->scriptMap = array();
$baseUrl = $this->assetsUrl;
if ($this->includeDefaultJsAssets ()) { 
    $cs->registerCoreScript('jquery');
    $cs->registerPackage('jqueryMobileJs');
    $cs->registerPackage('x2TouchJs');
    $cs->registerPackage('x2TouchSupplementalJs');
     
}
if ($this->includeDefaultCssAssets ()) { 
    $cs->registerPackage('jqueryMobileCss');
    $cs->registerPackage('x2TouchCss');
    $cs->registerPackage('x2TouchSupplementalCss');
         
}

if (!$this->isAjaxRequest ()) {
    $cs->registerScriptFile($baseUrl . '/js/x2mobile.js', CClientScript::POS_READY);
}

if (!$this->isAjaxRequest ()) {
    // Needed since jQM doesn't trigger the page load/show event for pages that are included in 
    // the original document. 
    // Allows initial page load and ajax page loads to be handled with the same events. 
    Yii::app()->clientScript->registerScript('jqueryPageLoadEventFix',"
        $(function () {
            //$(document).trigger ('pagecontainerload');
            //$(document).trigger ('pagecontainerbeforeshow');
            //$(document).trigger ('pagecontainershow');
        });
    ", CClientScript::POS_END);
}

$this->onPageLoad ("function () {
    if (!x2.attachments) {
        x2.attachments = new x2.Attachments ({
            translations: ".CJSON::encode (array (
                'filetypeError' => Yii::t('app', '"{X}" is not an allowed filetype.'),
            ))."
        });
    }
}");

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<?php
if (!$this->isAjaxRequest ()) {
?>
<meta charset="UTF-8" />
<meta name="language" content="en" />
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, maximum-scale=1"/> 
<meta name="apple-mobile-web-app-capable" content="yes"/>
<link rel="icon" href="<?php echo Yii::app()->getBaseUrl(); ?>/images/favicon.ico" type="image/x-icon0" />
<link rel="shortcut icon" href="<?php echo Yii::app()->getBaseUrl(); ?>/images/favicon.ico" type="image/x-icon" />
<title><?php echo CHtml::encode($this->headerTitle); ?></title>
<?php
}
?>
</head>
<body class='mobile-body<?php 
     
?>'> 
<?php

//if (Yii::app()->user->isGuest) {
//    MobileLoginThemeHelper::init();
//    MobileLoginThemeHelper::render();
//} else {
//    ThemeGenerator::render();
//}

?>

<div id="<?php echo $this->pageId.'-'.$this->getUniquePageIdSuffix (); ?>" data-role="page" 
 data-page-id="<?php echo $this->pageId; ?>"
 class='<?php echo $this->pageId.' '.$this->pageClass; ?> x2-remote-page' 
 data-url="<?php echo $this->dataUrl; ?>/" data-theme="a">

<div class='flashes-container'>
<?php
X2Html::getFlashes ();
?>
</div>

    <div id='header' data-role='header'>
        <a href='#<?php echo $this->pageId; ?>-panel' style='display: none;' 
          class='ui-btn-left ui-btn show-left-menu-button'>
            <i class='fa fa-bars'></i>
        </a>
        <h1 class='page-title'>
        <?php
        echo CHtml::encode ($this->headerTitle);
        ?>
        </h1>
        <a href='#settings-menu' data-rel='popup' data-transition='pop' 
         class='ui-btn-right ui-btn show-settings-menu'>
            <i class='fa fa-ellipsis-v'></i>
        </a>
        <div class='header-content-center'>
        </div>
        <div class='header-content-right'>
        </div>
    </div>

    <div data-role='panel' data-display='push' class='x2touch-panel no-scrollbar' 
     id='<?php echo $this->pageId; ?>-panel'>
        <?php
        //if ($this->includeDefaultJsAssets ()) { 
        if (!Yii::app()->user->isGuest) { 
        ?>
        <div class='panel-contents'>
        <?php
        $this->widget ('application.modules.mobile.components.panel.Panel');
        ?>
        </div>
        <?php
        } else { // only the recent items need to be refreshed on ajax request
        ?>
        <div class='refresh-content' data-refresh-selector='.panel-recent-item'>
        <?php
        $panel = $this->createWidget ('application.modules.mobile.components.panel.Panel');
        echo $panel->renderItems (function ($section) {
            return $section === 'recentItems';
        });
        ?>
        </div>
        <?php
        }
        ?>
    </div>
<script>
if (typeof x2 === 'undefined') x2 = {};
x2.isAjaxRequest = <?php echo $this->isAjaxRequest () ? 'true' : 'false'; ?>;
 
x2.csrfToken = <?php echo CJSON::encode (Yii::app()->request->getCsrfToken ()); ?>;
</script>
<?php
if ($this->isAjaxRequest ()) {
    // when the page is being updated via ajax, scripts must be registered inside body so that 
    // they're fetched by jQuery mobile
    $cs = Yii::app()->clientScript;
    $assets = $cs->renderX2TouchAssets ();
    echo $assets;
}
?>
		<div data-role="content">
			<?php
			echo $content;
			?>
		</div>
        <?php
        if (false) {
        ?>
		<div id='footer' data-role="footer" data-theme="a">
			<p>&nbsp;&nbsp;&copy;<?php 
                echo date('Y') . ' ' . CHtml::link('X2Engine Inc.', 'http://www.x2engine.com')." ";
				echo Yii::t('app', 'Rights Reserved.'); 
                 
                    echo '&nbsp;';
                    echo CHtml::link(
                        Yii::t('mobile', 'Go to Full Site'),
                        Yii::app()->getBaseUrl().'/index.php/site/index?mobile=false',
                        array(
                            'rel'=>'external',
                            'onClick'=>'setMobileBrowserFalse()',
                            'class'=>'full-site-link',
                        )); 
                 
                ?>
			</p>
            <div id='logo-container'>
            <?php
            //echo CHtml::image(Yii::app()->params->x2Power,'',array('id'=>'powered-by-x2engine')); 
            ?>
            </div>
		</div>
        <?php
        }
        ?>
</div>
</body>
</html>
