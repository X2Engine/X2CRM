<?php
/* * *********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
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
 * ******************************************************************************** */


Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl . '/css/about-page.css');

$this->layout = '//layouts/column1';
$this->pageTitle = Yii::app()->settings->appName . ' - ' . Yii::t('app', 'About');
$logo = Yii::app()->baseUrl . '/images/x2engine.png';
$poweredByLogo = Yii::app()->baseUrl . '/images/powered_by_x2engine.png';
?>
<div id='icon-container'>
    <?php
    if (ThemeGenerator::isThemed()) {
        echo X2Html::logo('about', array('id' => 'x2-about-logo'));
    } else {
        echo CHtml::image($logo, '');
    }
    echo "<br><br>";
    echo CHtml::link(CHtml::image($poweredByLogo, '',
                    array('id' => 'powered-by-logo')), 'https://www.x2crm.com',
            array('target' => '_blank'));
    ?>
</div>
<?php
Yii::app()->clientScript->registerScript('loadJqueryVersion',
        "$('#jqueryVersion').html($().jquery);", CClientScript::POS_READY);
?>

<div class='center-column-container form left' >
    <b style="font-size:16px"><?php echo Yii::t('app',
        'X2CRM | Enterprise CRM for Small Business'); ?></b><br>
    <b><?php echo Yii::t('app', 'Version') . " " . Yii::app()->params->version; ?></b><br>
    <?php
    echo Yii::t('app', 'Open Source License: {link}',
            array(
        '{link}' => CHtml::link(Yii::t('app', 'GNU Affero GPL v3'),
                Yii::app()->getBaseUrl() . '/LICENSE.txt',
                array('title' => Yii::t('app',
                    'GNU Affero General Public License version 3')
    ))));
    ?><br>
    <?php
    echo Yii::app()->dateFormatter->formatDateTime(Yii::app()->params->buildDate,
            'long', null);
    ?><br>
    <br>
    <?php
    echo Yii::t('app', 'Web: {link}',
            array('{link}' => CHtml::link('www.x2crm.com',
                'https://www.x2crm.com', array('target' => '_blank'))));
    ?><br>
    <?php
    echo Yii::t('app', 'Email: {link}',
            array('{link}' => CHtml::link('contact@x2engine.com',
                'mailto:contact@x2engine.com')));
    ?><br><br>
    <div style="clear:both">
        <a href="https://x2crm.com" target="_blank">X2Engine Inc.</a><br>
        PO Box 66752<br>
        Scotts Valley, California 95067 USA<br>
    </div>
    <div id="about-intro">
        <?php
        echo Yii::t('app',
                'X2CRM | X2Engine was founded in Scotts Valley, '
                . 'California in 2011 by John Roberts with the mission to develop '
                . 'a new open source CRM platform with enterprise capabilities '
                . 'but designed for small businesses. What makes X2CRM powerful '
                . 'is in addition to providing an enterprise scale CRM platform, '
                . 'X2CRM also includes both a marketing workflow automation tool '
                . 'and a structured sales and service process tool in one '
                . 'application. With an incredibly rich user interface in both '
                . 'web and mobile apps, you can configure X2CRM for practically '
                . 'any CRM use. ');
        ?><br><br>
        <?php
        echo Yii::t('app',
                'All X2CRM source code is fully open source under'
                . ' the {license}. X2CRM has taken years of dedicated work by '
                . 'many talented software developers to create. The funding for '
                . 'this software development is provided by {website} If you '
                . 'would like to use X2CRM under a commercial license or other '
                . 'terms please contact X2Engine Inc. at {contact}.',
                array(
            '{license}' => CHtml::link(Yii::t('app', 'GNU Affero GPL v3'),
                    Yii::app()->getBaseUrl() . '/LICENSE.txt',
                    array('title' => Yii::t('app',
                        'GNU Affero General Public License version 3'))),
            '{website}' => CHtml::link('X2Engine Inc.', 'https://www.x2crm.com',
                    array('target' => '_blank')),
            '{contact}' => CHtml::link('contact@x2engine.com',
                    'mailto:contact@x2engine.com')
        ));
        ?><br><br>
        <?php
        echo Yii::t('app',
                'For Customer Support, Training, Partner Solutions please visit: {website}',
                array('{website}' => CHtml::link('www.x2crm.com',
                    'https://www.x2crm.com', array('target' => '_blank'))));
        ?><br>
        <?php
        echo Yii::t('app', 'For X2CRM Public Forums: {community}',
                array('{community}' => CHtml::link('www.x2community.com',
                    'http://www.x2community.com', array('target' => '_blank'))));
        ?><br><br>
        <b><?php echo Yii::t('app', 'Core Team & Contributors:'); ?></b><br>
        <?php
        echo implode(', ',
                array(
            'John Roberts',
            'Jake Houser',
            'Raymond Colebaugh',
            'Steve Lance',
            'Derek Mueller',
            'Josef Bustamante',
            'Matthew Pearson',
            'Demitri Morgan',
            'Alex Rowe'
        ));
        ?><br><br>
        <b><?php echo Yii::t('app','Release Contributors');?></b><br>
        <?php 
        echo implode('<br>',array(
            'Pomazan Bogdan -- Russian Language Translations'
        ));
        ?>
    </div>
    <hr>
    <div id="about-credits">
        <!--<div class="about-list" style="height:450px;width:auto;overflow-y:scroll;border:1px solid #ddd;padding:10px;"></div>
        <hr>-->
        <h4><?php echo Yii::t('app', 'Version Info'); ?></h4>
        <ul>
            <li>X2Engine: <?php echo Yii::app()->params->version; ?></li>
            <!--<?php echo Yii::t('app', 'Build'); ?>: 1234<br>-->
            <li>Yii: <?php echo Yii::getVersion(); ?></li>
            <li>jQuery: <span id="jqueryVersion"></span></li>
            <li>PHP: <?php echo phpversion(); ?></li>
            <!--jQuery Mobile: 1.0b2<br>-->
        </ul>
        <h4><?php echo Yii::t('app', 'Code Base'); ?></h4>
        <ul>
            <li>GitHub: <a href="https://github.com/X2Engine/crm" target="_blank">https://github.com/X2Engine/crm</a></li>
            <!--BitBucket: <a href="https://bitbucket.org/X2Engine/X2Engine" target="_blank">https://bitbucket.org/X2Engine/X2Engine</a></li>-->
        </ul>

        <h4><?php echo Yii::t('app', 'Plugins/Extensions'); ?></h4>
        <ul>
            <li>CFile Class: <a href="http://www.yiiframework.com/extension/cfile" target="_blank"><?php
        echo Yii::t('app', 'Yii Extension');
        ?></a> <a href="http://www.opensource.org/licenses/mit-license.php" target="_blank" class="no-underline" title="MIT License">[MIT]</a></li>
            <li>CKEditor: <a href="http://www.ckeditor.com/" target="_blank"><?php
                    echo Yii::t('app', 'Developer');
        ?></a> <a href="http://www.gnu.org/copyleft/lesser.html" target="_blank" class="no-underline" title="Lesser GPL License">[LGPL]</a></li>
            <li>colResizable: <a href="http://www.bacubacu.com/colresizable/" target="_blank"><?php
                    echo Yii::t('app', 'Developer');
        ?></a> <a href="http://www.opensource.org/licenses/mit-license.php" target="_blank" class="no-underline" title="MIT License">[MIT]</a></li>
            <li>CSaveRelationsBehavior Class: <a href="http://www.yiiframework.com/extension/save-relations-ar-behavior/" target="_blank"><?php
                    echo Yii::t('app', 'Yii Extension');
        ?></a> <a href="http://www.opensource.org/licenses/BSD-3-Clause" target="_blank" class="no-underline" title="New BSD License">[New BSD]</a></li>
            <li>ERememberFiltersBehavior Class: <a href="http://www.yiiframework.com/extension/remember-filters-gridview/" target="_blank"><?php
                    echo Yii::t('app', 'Yii Extension');
        ?></a> <a href="http://www.opensource.org/licenses/BSD-3-Clause" target="_blank" class="no-underline" title="New BSD License">[New BSD]</a></li>
            <li>EZip Class: <a href="http://www.yiiframework.com/extension/cfile" target="_blank"><?php
                    echo Yii::t('app', 'Yii Extension');
        ?></a> <a href="http://www.opensource.org/licenses/mit-license.php" target="_blank" class="no-underline" title="MIT License">[MIT]</a></li>
            <li>FineDiff: <a href="http://www.raymondhill.net/finediff/" target="_blank"><?php
                    echo Yii::t('app', 'Developer');
        ?></a> <a href="http://www.opensource.org/licenses/mit-license.php" target="_blank" class="no-underline" title="MIT License">[MIT]</a></li>
            <li>formatCurrency: <a href="http://code.google.com/p/jquery-formatcurrency/" target="_blank"><?php
                    echo Yii::t('app', 'Developer');
                    ?></a> <a href="http://www.gnu.org/copyleft/lesser.html" target="_blank" class="no-underline" title="Lesser GPL License">[LGPL]</a></li> <li>phpMailer: <a href="http://phpmailer.worxware.com/" target="_blank"><?php
                            echo Yii::t('app', 'Developer');
                    ?></a> <a href="http://www.gnu.org/copyleft/lesser.html" target="_blank" class="no-underline" title="Lesser GPL License">[LGPL]</a></li>
            <li>FullCalendar: <a href="http://arshaw.com/fullcalendar/" target="_blank"><?php
                    echo Yii::t('app', 'Developer');
                    ?></a> <a href="http://www.opensource.org/licenses/mit-license.php" target="_blank" class="no-underline" title="MIT License">[MIT]</a></li>
            <li>Google API PHP Client: <a href="https://github.com/google/google-api-php-client" target="_blank"><?php
                    echo Yii::t('app', 'Project');
                    ?></a> <a href="http://www.apache.org/licenses/" target="_blank" class="no-underline" title="Apache License 2.0">[Apache]</a></li>
            <!--<li>JS SHA-256: <a href="http://www.webtoolkit.info/javascript-sha256.html" target="_blank"><?php
            echo Yii::t('app', 'Developer');
                    ?></a> <a href="http://www.webtoolkit.info/license" target="_blank" class="no-underline" title="License">[License]</a></li>-->
            <li>jStorage: <a href="http://www.jstorage.info/" target="_blank"><?php
                    echo Yii::t('app', 'Developer');
                    ?></a> <a href="http://www.jstorage.info/static/license.txt" target="_blank" class="no-underline" title="MIT License">[MIT]</a></li>
            <li>Modernizr: <a href="http://modernizr.com" target="_blank"><?php
                    echo Yii::t('app', 'Developer');
                    ?></a> <a href="http://modernizr.com/license/" target="_blank" class="no-underline" title="New BSD License">[New BSD]</a></li>
            <li>qTip2: <a href="http://craigsworks.com/projects/qtip2/" target="_blank"><?php
                    echo Yii::t('app', 'Developer');
                    ?></a> <a href="http://www.opensource.org/licenses/mit-license.php" target="_blank" class="no-underline" title="MIT License">[MIT]</a></li>
            <li>Spectrum: <a href="http://bgrins.github.io/spectrum/" target="_blank"><?php
                    echo Yii::t('app', 'Developer');
                    ?></a> <a href="https://github.com/bgrins/spectrum/blob/master/LICENSE" target="_blank" class="no-underline" title="MIT License">[MIT]</a></li> 
        </ul>
    </div>
    <hr>
    <div id="about-legal">
        <?php
// Yii::app()->params->edition = 'opensource';
        ?>
        <a href="https://www.x2crm.com/" target="_blank"><?php
            echo Yii::t('app', 'Powered by X2Engine');
        ?></a>. Copyright &copy; 2011-<?php echo date('Y'); ?> X2Engine Inc.<br>
        <br>
        <?php
        echo Yii::t('app', 'Released as free software under the');
        ?> <a href="<?php echo Yii::app()->getBaseUrl(); ?>/LICENSE.txt" title="GNU Affero General Public License version 3">GNU Affero GPL v3</a>.<br><br>

        <b>The interactive user interfaces in modified source and object code versions
            of this program must display Appropriate Legal Notices, as required under
            Section 5 of the GNU Affero General Public License version 3. In accordance
            with Section 7(b) of the GNU General Public License version 3, these
            Appropriate Legal Notices must retain the display of the "Powered by X2Engine"
            logo. If the display of the logo is not reasonably feasible for technical reasons,
            the Appropriate Legal Notices must display the words "Powered by X2Engine".
            X2Engine and X2Engine are trademarks of X2Engine Inc.<br><br>


            THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
            EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
            MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
        </b>
    </div><br>
</div>
<div id="about-map">
<!--<a title="<?php echo Yii::t('app', 'Our office in downtown Santa Cruz'); ?>" target="_blank" href="https://maps.google.com/maps?q=501+Mission+Street+Suite+%235+Santa+Cruz,+California+95060+USA&hl=en&sll=37.269174,-119.306607&sspn=14.636891,27.114258&t=h&hnear=501+Mission+St+%235,+Santa+Cruz,+California+95060&z=8">-->
<?php //echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/office.png','');        ?>
</a>
<?php //echo Yii::t('app','X2Engine Inc. is headquartered in beautiful Santa Cruz, California. We really enjoy meeting customers and partners whenever possible and encourage you to visit our offices when you find yourself in the San Francisco bay area.');
?>
</div>
<div class='clear'></div>












