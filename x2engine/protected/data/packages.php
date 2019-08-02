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




/**
 * This file contains list of common packages used throughout the app. 
 * They will NOT be included on every page until they are registered. 
 */

// Common paths
$baseUrl  = Yii::app()->baseUrl;
$themeUrl = Yii::app()->theme->baseUrl;

return array(
    'auxlib' => array(
        'baseUrl' => $baseUrl,
        'js' => array(
            'js/auxlib.js'
        ),
        'depends' => array('jquery')
    ),

    // CKEditor package
    'ckeditor' => array (
        'baseUrl' => $baseUrl,
        'js' => array( 
            'js/ckeditor/ckeditor.js', 
            'js/ckeditor/adapters/jquery.js',
        ),
    ),

    'emailEditor' => array (
        'baseUrl' => $baseUrl,
        'js' => array (
            'js/emailEditor.js'
        ),
        'depends' => array (
            'ckeditor'
        )
    ),

    'Dropzone' => array (
        'baseUrl' => Yii::app()->baseUrl.'/js/lib/dropzone',
        'js' => array ('dropzone.js'),
        'css' => array ('dropzone.css')
    ),
    
    'tours' => array (
        'baseUrl' => $baseUrl.'/js/Tours/',
        'js' => array(
            'Tours.js',
            'Tip.js',
            'PopupTip.js',
            'BlockTip.js',
            'FlashTip.js'
        ),
        'depends' => array ('toursCSS', 'auxlib')
    ),

    'toursCSS' => array (
        'baseUrl' => $themeUrl,
        'css' => array (
            'css/tours.css'
        ),
    ),

    'CodeMirrorJS' => array (
        'baseUrl' => Yii::app()->baseUrl.'/js/lib/codemirror/', 
        'js' => array('codemirror.js', 'keymap/vim.js', 'mode/css.js', 'mode/xml.js', 'mode/php.js', 'mode/javascript.js', 'mode/sql.js', 'mode/sass.js'),
        'css' => array('codemirror.css')
    ),

    'spectrum' => array (
        'baseUrl' => Yii::app()->baseUrl.'/js/lib/spectrum/', 
        'js' => array('spectrum.js'),
        'css' => array('spectrum.css'),
    ),

    'X2CSS' => array (
        'baseUrl' => Yii::app()->baseUrl.'/js', 
        'js' => array('X2CSS.js'),
        'depends' => array ('X2Component', 'spectrum'),
    ),

    'X2Component' => array (
        'baseUrl' => Yii::app()->baseUrl.'/js', 
        'js' => array('X2Component.js'),
        'depends' => array ('auxlib'),
    ),

    'InfinityScroll' => array (
        'baseUrl' => Yii::app()->baseUrl.'/js', 
        'js' => array('InfinityScroll.js'),
        'depends' => array ('auxlib', 'X2Widget'),
    ),

    'X2Widget' => array(
        'baseUrl' => Yii::app()->baseUrl.'/js',
        'js' => array(
            'X2Widget.js',
        ),
    ),

    'multiselect' => array (
        'baseUrl' => Yii::app()->baseUrl.'/js/multiselect', 
        'js' => array('js/ui.multiselect.js'),
        'css' => array('css/ui.multiselect.css'),
        'depends' => array ('jquery.ui'),
    ),
);
