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






Yii::app()->clientScript->registerCss('setDefaultThemeCSS',"
#default-theme-submit {
    margin-top: 23px;
}

#theme-inputs {
    margin-bottom: 15px;
}

#theme-dropdown {
    float: left;
}

#import-theme-button {
    margin-top: 1px;
}

.x2-checkbox-row {
    margin: 5px 0;
}

[for='enforceDefaultTheme'] {
    padding-left: 4px !important;
}

");

Yii::app()->clientScript->registerScript('setDefaultThemeJS',"

;(function setupThemeImport () {
    $('#import-theme-button').click (function () {
        if ($('#theme-import-form').closest ('.ui-dialog').length) {
            $('#theme-import-form').dialog ('open');
        }
        $('#theme-import-form').dialog ({
            title: '".CHtml::encode (Yii::t('admin', 'Import a Theme'))."',
            autoOpen: true,
            width: 500,
            buttons: [
                {
                    text: '".CHtml::encode (Yii::t('admin', 'Close'))."',
                    click: function () { $(this).dialog ('close'); }
                }
            ]
        });
    });
}) ();

");

?>

<div class="page-title"><h2><?php echo Yii::t('admin','Set a Default Theme'); ?></h2></div>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'set-default-theme-form',
	'enableAjaxValidation'=>false,
)); 
?>
<div class='form'>
<?php
echo X2Html::getFlashes ();
echo Yii::t('admin','Set a default theme which will automatically be set for all new users.');
echo '&nbsp;'.Yii::t('admin', 'To get started, go to {preferences} and create at least one theme.',array('{preferences}'=>CHtml::link(Yii::t('profile','Preferences'),array('/profile/settings'))));
?>
<div id='theme-inputs'>
    <br>
    <label for='theme'><?php echo Yii::t('admin', 'Theme: '); ?></label>
    <?php
    echo CHtml::dropDownList (
        'theme', $defaultTheme ? $defaultTheme : '', $themeOptions, array (
            'class' => 'x2-select',
            'id' => 'theme-dropdown',
            'style' => 'margin-right:10px;'
        ));
    ?>
    <button type='button' class='x2-button x2-small-button' id='import-theme-button'>
        <?php echo Yii::t('profile', 'Import Theme'); ?>
    </button>
    <?php
    ?>
</div>
<div class='x2-checkbox-row'>
<?php
echo CHtml::checkBox ('setDefaultTheme', (bool) $defaultTheme, array (
    'class' => 'left' 
));
echo CHtml::label (CHtml::encode (Yii::t('admin', 'Set selected as default theme')),
    'setDefaultTheme', array ('class' => ''));
?>
</div>
<div class='x2-checkbox-row'>
<?php
echo CHtml::checkBox ('enforceDefaultTheme', $enforceDefaultTheme, array (
    'class' => 'left' 
));
echo CHtml::label (CHtml::encode (Yii::t('admin', 'Enforce use of default theme')),
    'enforceDefaultTheme', array ('class' => 'left'));
echo X2Html::hint (Yii::t('admin', 'If this option is set, users will not be able to customize or change their themes. All new users and all current users will be given this theme.'), false, null, true);
?>
</div>

<?php 
echo CHtml::submitButton(Yii::t('app','Submit'),array('class'=>'x2-button', 'id'=> 'default-theme-submit')); 
?>
</div>
<?php
$this->endWidget(); 

$this->renderPartial ('application.views.profile._themeImportForm');
