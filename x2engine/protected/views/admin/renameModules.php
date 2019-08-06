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



?>
<div class="page-title"><h2><?php echo Yii::t('admin','Rename a Module'); ?></h2></div>
<div class="form">
<?php echo Yii::t('admin','You can rename a module by selecting a module and typing the new name below.'); ?>
<br><br>
<?php
    echo CHtml::label(Yii::t('admin', 'Module:'), '');
    $moduleOptions = array_merge(array('' => Yii::t('admin', 'Please select a module')), $modules);
    X2Html::getFlashes();
    echo CHtml::form('renameModules','post',array('enctype'=>'multipart/form-data'));
    echo CHtml::dropDownList('module', '', $moduleOptions)."<br><br>";
    echo CHtml::label(Yii::t('admin', 'New Name:'), 'name');
    echo CHtml::textField('name');
    echo "<div id='itemNameField'>";
    echo CHtml::label(Yii::t('admin', 'Item Name'), 'itemName');
    echo CHtml::textField('itemName');
    echo "</div>";
    echo CHtml::submitButton(Yii::t('app','Submit'),array('class'=>'x2-button'));
    echo CHtml::endForm();
?> </div>

<?php Yii::app()->clientScript->registerScript ('renameModulesJs', "
    $('#module').change(function() {
        var selectedModule = $('#module').find(':selected').val();
        var modules = ". CJSON::encode($modules) ."
        var itemNames = ". CJSON::encode($itemNames) ."

        $('#name').val(modules[selectedModule]);
        if (typeof itemNames[selectedModule] !== 'undefined') {
            $('#itemNameField').show();
            $('#itemName').val(itemNames[selectedModule]);
        } else {
            $('#itemNameField').hide();
            $('#itenName').val('');
        }
    });

    $(function() {
        // Hide the item name field on page load if module's items cannot be renamed
        var selectedModule = $('#module').find(':selected').val();
        var itemNames = ". CJSON::encode($itemNames) ."

        if (typeof itemNames[selectedModule] === 'undefined') {
            $('#itemNameField').hide();
            $('#itenName').val('');
        }
    });
", CClientScript::POS_READY);
