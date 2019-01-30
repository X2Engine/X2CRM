<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




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

    if (YII_UNIT_TESTING) {
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->getBaseUrl ().'/js/qunit/qunit-1.20.0.js', CClientScript::POS_HEAD);
        Yii::app()->clientScript->registerScriptFile(
            $this->assetsUrl.'/js/tests/functional/Main.js',
            CClientScript::POS_END);
        Yii::app()->clientScript->registerScriptFile(
            $this->assetsUrl.'/js/tests/functional/login.js',
            CClientScript::POS_END);
        $excludedFiles = array (
            'Main.js',
            //'activityFeed.js',
            'login.js'
        );
        foreach(scandir(Yii::getPathOfAlias(
            'application.modules.mobile.assets.js.tests.functional')) as $file) {

            if(!preg_match ('/\.js$/', $file) || in_array($file,$excludedFiles) || 
                isset ($includedFiles) && !in_array($file, $includedFiles)) {

                continue;
            }
            Yii::app()->clientScript->registerScriptFile(
                $this->assetsUrl.'/js/tests/functional/'.$file,
                CClientScript::POS_END);
        }
    }
}

if ($this->includeDefaultCssAssets ()) { 
    $cs->registerPackage('jqueryMobileCss');
    $cs->registerPackage('x2TouchCss');
    $cs->registerPackage('x2TouchSupplementalCss');

    if (YII_UNIT_TESTING) {
        Yii::app()->clientScript->registerCssFile(
            Yii::app()->getBaseUrl ().'/js/qunit/qunit-1.20.0.css');
        Yii::app()->clientScript->registerCssFile(
            $this->assetsUrl.'/css/functionalTests.css'); 
    }
}

if (!$this->isAjaxRequest () && !Yii::app()->params->isPhoneGap) {
    $cs->registerScriptFile($baseUrl . '/js/x2mobile.js', CClientScript::POS_READY);
}

if ($this->includeDefaultJsAssets ()) {
    Yii::app()->clientScript->registerScript('registerMain',"
        x2.Main.onPageCreate (function () {
            if (!x2.main) {
                x2.main = new x2.Main (".CJSON::encode (array (
                    'translations' => array (
                        'confirmCancel' => Yii::t('app', 'Cancel'),
                        'confirmOkay' => Yii::t('app', 'Okay'),
                    ),
                    'pageDepth' => $this->pageDepth,
                    'platform' => MobileModule::getPlatform (),
                )).");
            }
        });
    ", CClientScript::POS_HEAD);
}

$this->onPageLoad ("
    if (!x2.attachments) {
        x2.attachments = new x2.Attachments ({
            translations: ".CJSON::encode (array (
                'filetypeError' => Yii::t('app', '"{X}" is not an allowed filetype.'),
            ))."
        });
    }
    if (x2.main) {
        var updateParams = ".CJSON::encode (array (
            'pageDepth' => $this->pageDepth
        )).";
        $.extend (x2.main, updateParams);
    }
");

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

if (YII_UNIT_TESTING) {
    echo "<div id='qunit'></div>";
    echo "<div id='qunit-fixture'></div>";
}

?>

<div id="<?php echo $this->pageId.'-'.$this->getUniquePageIdSuffix (); ?>" data-role="page" 
 data-page-id="<?php echo $this->pageId; ?>"
 class='<?php 
    echo $this->pageId.' '.$this->pageClass; 
    if ($this->layoutHasTabs ()) {
        echo ' tabbed-layout';
    }
     
    if (Yii::app()->params->isPhoneGap) {
        echo ' x2-phone-gap';
        if (MobileModule::getPlatform () === 'iOS') {
            echo ' x2touch-ios';
        } else {
            echo ' x2touch-android';
        }
    } else {
     
        echo ' x2touch-browser';
     
    }
     
?> x2-remote-page' 
 data-url="<?php echo $this->dataUrl; ?>/" data-theme="a">

<div class='flashes-container'>
<?php
X2Html::getFlashes ();
?>
</div>
<form id="geoCoordsForm" action="" method="POST">
    <input type="hidden" name="geoCoords" id="geoCoords">
</form>
    <div id='header' data-role='header'>
        <a href='#<?php echo $this->pageId; ?>-panel' 
          style='display: none;' 
          class='ui-btn-left ui-btn show-left-menu-button'>
            <i class='fa fa-bars'></i>
        </a>
        <?php
        $currentUrl = Yii::app()->request->url;
        if (strpos($currentUrl, '/mobile/login') == false){
        ?>
            <a href='<?php echo Yii::app()->createAbsoluteUrl ('profile/mobileActivity'); ?>' 
              style='margin-left: 40px;' 
              class='ui-btn-left ui-btn show-left-menu-button-right' id='home-btn'>
                <i class='fa fa-home'></i>
            </a>
            <?php
            if (Yii::app()->params->isPhoneGap) {
            ?>
            <a href='<?php echo Yii::app()->createAbsoluteUrl ('profile/mobileCheckInPublisher'); ?>' 
              style='margin-left: 80px;' 
              class='ui-btn-left ui-btn show-left-menu-button-right'>
                <i class='fa fa-location-arrow'></i>
            </a>
            <?php
            }
            ?>
        <?php
        }
        ?>
        <?php
        if (MobileModule::getPlatform () === 'iOS') {
        ?>
            <div class='header-back-button'
              style='display: none;'>
                <i class='fa fa-chevron-left'></i>
                <?php
                echo CHtml::encode (Yii::t('app', 'Back'));
                ?>
            </div>
        <?php
        }
        ?>
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
        if (!Yii::app()->user->isGuest) { 
        ?>
        <div class='panel-contents'>
        <?php
        $this->widget ('application.modules.mobile.components.panel.Panel');
        ?>
        </div>
        <?php
        } 
        ?>
    </div>

<script>
if (typeof x2 === 'undefined') x2 = {};
x2.isAjaxRequest = <?php echo $this->isAjaxRequest () ? 'true' : 'false'; ?>;
 
x2.nlscCacheBuster = <?php echo CJSON::encode (Yii::app()->clientScript->getCacheBuster ()); ?>;
 
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
		<div data-role="content" class='<?php echo MobileModule::getPlatform () === 'iOS' ? '' : 'innermost-content-container'; ?>'>
            <?php
            // extra div needed for ios scrolling to work properly
            if (MobileModule::getPlatform () === 'iOS') {
                ?>
		        <div class='content-inner<?php echo MobileModule::getPlatform () === 'iOS' ? ' innermost-content-container' : ''; ?>'>
                <?php
            }
			echo $content;
            if (MobileModule::getPlatform () === 'iOS') {
                ?>
                </div>
                <?php
            }
            ?>
		</div>
</div>
</body>
</html>
