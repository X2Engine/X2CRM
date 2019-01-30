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




mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

$preferences = Yii::app()->params->profile ? Yii::app()->params->profile->theme : array();
$jsVersion = '?' . Yii::app()->params->buildDate;

// blueprint CSS framework
$themeURL = Yii::app()->theme->getBaseUrl();
Yii::app()->clientScript->registerCssFile($themeURL . '/css/screen.css' . $jsVersion, 'screen, projection');
Yii::app()->clientScript->registerCssFile($themeURL . '/css/print.css' . $jsVersion, 'print');
Yii::app()->clientScript->registerCssFile($themeURL . '/css/main.css' . $jsVersion, 'screen, projection');
Yii::app()->clientScript->registerCssFile($themeURL . '/css/form.css' . $jsVersion, 'screen, projection');
Yii::app()->clientScript->registerCssFile($themeURL . '/css/ui-elements.css' . $jsVersion, 'screen, projection');

if (AuxLib::getIEVer() < 9) {
    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/lib/aight/aight.js');
}
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/lib/jquery-migrate-1.2.1.js');


$backgroundImg = '';
$defaultOpacity = 1;
$themeCss = '';

$checkResult = false;
$checkFiles = array(
    'themes/x2engine/images/x2footer.png' => '1393e4af54ababdcf76fac7f075b555b',
    'themes/x2engine/images/x2-mini-icon.png' => '153d66b514bf9fb0d82a7521a3c64f36',
);
foreach ($checkFiles as $key => $value) {
    if (!file_exists($key) || hash_file('md5', $key) != $value) {
        $checkResult = true;
    }
}
$theme2Css = '';
if ($checkResult) {
    $theme2Css = 'html * {background:url(' . CHtml::normalizeUrl(array('/site/warning')) . ') !important;} #bg{display:none !important;}';
}

Yii::app()->clientScript
        ->registerCss('applyTheme2', $theme2Css, 'screen', CClientScript::POS_HEAD)
        ->registerCssFile(Yii::app()->theme->getBaseUrl() . '/css/login.css')
        ->registerCssFile(Yii::app()->theme->getBaseUrl() . '/css/fontAwesome/css/font-awesome.css')
        ->registerScriptFile(Yii::app()->getBaseUrl() . '/js/auxlib.js')
        ->registerScriptFile(Yii::app()->getBaseUrl() . '/js/X2Forms.js');
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->language; ?>" lang="<?php echo Yii::app()->language; ?>">
    <head>
        <meta charset="UTF-8" />
        <meta name="language" content="<?php echo Yii::app()->language; ?>" />

        <meta name="description" content="X2Engine - Open Source Sales CRM - Sales Software" />
        <meta name="keywords" content="X2Engine,X2CRM,open source sales CRM,sales software" />
        <meta name="viewport" content="width=device-width, initial-scale=0.8, user-scalable=no" />

        <link rel="icon" href="<?php echo Yii::app()->getFavIconUrl(); ?>" type="image/x-icon" />
        <link rel="shortcut-icon" href="<?php echo Yii::app()->getFavIconUrl(); ?>" type="image/x-icon" />
        <link rel="icon" href="<?php echo Yii::app()->getFavIconUrl(); ?>" type="image/x-icon" />
        <link rel="shortcut-icon" href="<?php echo Yii::app()->getFavIconUrl(); ?>" type="image/x-icon" />

        <title><?php echo CHtml::encode($this->pageTitle); ?></title>
    </head>
    <?php
    echo X2Html::openBodyTag($preferences, array(
        'id' => 'body-tag',
        'class' => 'login',
    ));
    ?>
    <div class="ie-shadow" style="display:none;"></div>
    <?php echo $content; ?>
    <div class='background'>
        <div class='stripe-container'>
            <div class='stripe small' style="float:left"></div>
            <div class='stripe' style="float:left"></div>
            <div class='stripe small' style="float:right"></div>
            <div class='stripe' style="float:right"></div>
        </div>
    </div>

    <?php
    LoginThemeHelper::render()
    ?>
</body>
</html>
