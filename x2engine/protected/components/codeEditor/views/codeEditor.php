<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2017 X2 Engine Inc.
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
 * @edition:ent
 */


Yii::app()->clientScript->registerPackage ('emailEditor');
Yii::app()->clientScript->registerPackage('CodeMirrorJS');

Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/js/ckeditor/plugins/codemirror/theme/seti.css');
Yii::app()->clientScript->registerCss('code-editor-style','
    #fileBrowser {
        width: 15%;
        overflow: auto;
    }
    #codeEditorBox {
        width: 80%;
    }
    .fileBrowserLink {
        cursor: pointer;
    }
    .cke_button_icon {
        padding-left: 6px;
    }
    .cke_button__createfile_icon:before {
        font-family: FontAwesome;
        content: "\f067";
    }
    .cke_button__savesource_icon:before {
        font-family: FontAwesome;
        content: "\f0c7";
    }
    .cke_button__codelinter_icon:before {
        font-family: FontAwesome;
        content: "\f14a";
    }
    .cke_button__toggledarktheme_icon:before {
        font-family: FontAwesome;
        content: "\f186";
    }
    .cke_button__togglevimbindings_icon:before {
        font-family: FontAwesome;
        content: "\f11c";
    }
    .cke_combopanel {
        width: 175px !important;
        height: 120px !important;
    }
');

Yii::app()->clientScript->registerScript('code-editor-variables','
if (typeof x2 === "undefined")
    x2 = {};
if (typeof x2.codeEditor === "undefined")
    x2.codeEditor = {};
x2.codeEditor.translations = {
    "no source file": '.CJSON::encode(Yii::t('admin', 'No source file loaded')).',
    "enter filename": '.CJSON::encode(Yii::t('admin', 'Please enter file to create, relative to the application directory:')).'
};
x2.codeEditor.fileTreeUrl = '.CJSON::encode($this->createAbsoluteUrl('admin/codeBrowser')).';

var lintingTimer;
if(window.codeEditor)
	window.codeEditor.destroy(true);
x2.codeEditor.instantiateCodeEditor ();
', CClientScript::POS_READY);

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/codeEditor.js');
?>

<div class="page-title">
    <h2><?php echo Yii::t('admin', 'X2Code Editor'); ?></h2>
</div>
<div class="form no-border">
	<div class="row">
		<div id="fileBrowser" class="cell">
        <?php
            $this->widget('CTreeView', array(
                'url' => array('admin/codeBrowser'),
                'persist' => 'cookie',
                'htmlOptions' => array('id' => 'filetree', 'class' => 'filetree'),
                'animated' => 'fast',
            )); 
        ?>
        </div>
		<div id="codeEditorBox" class="cell">
        <?php
            echo X2Html::textArea('text','',array('id'=>'input'));
        ?>
        </div>
    </div>
</div>

