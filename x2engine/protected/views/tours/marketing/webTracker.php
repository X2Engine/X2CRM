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




echo Tours::tips (array(
    array (
        "content" => "<h3>".Yii::t('marketing',"Welcome to the Web Tracker configuration!")."</h3> ".Yii::t('marketing',"The web tracker allows you to log visitor activity and interactions on your website. This page provides various configuration options to customize the behavior of your web tracker."),
        'type' => 'flash',
    ),
    array (
        'content' => Yii::t('marketing','This text box provides the HTML code to embed on your website.'),
        'target' => '#embedcode',
    ),
    array (
        'content' => Yii::t('marketing','You can instead export your web tracker JavaScript and upload it to your website. This is useful in certain cases to simplify web tracker setup under SSL.'),
        'target' => '.form > .x2-button', // tracker export button
        'highlight' => true
    ),
    array (
        'content' => Yii::t('marketing','Here you can configure the web tracker cooldown. A visitor will only have their web activity logged once within this cooldown period.'),
        'target' => '#cooldownSlider',
        'highlight' => true
    ),
    array (
        'content' => Yii::t('marketing','This option allows you to configure whether geolocation will be performed for your visitors.'),
        'target' => '#enableGeolocation',
    ),
    array (
        'content' => Yii::t('marketing','This option allows you to configure whether browser fingerprinting will be performed to attempt to match your visitor.'),
        'target' => '#enableFingerprinting',
    ),
    array (
        'content' => Yii::t('marketing','You can also configure the minimum number of browser attributes required to match a visitor. The higher this value is, the more accurate your partial matches will be.'),
        'target' => '#thresholdSlider',
        'highlight' => true
    ),
));

?>
