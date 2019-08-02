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




Yii::app()->clientScript->registerCss('manageDropDownsCSS',"

#dropdowns-grid-container {
    padding-left: 9px;
    padding-right: 9px;
}

#dropdowns-grid-page-title {
    margin-bottom: -5px;
    border-bottom: none !important;
    border-radius: 4px 4px 0 0;
    -moz-border-radius: 4px 4px 0 0;
    -webkit-border-radius: 4px 4px 0 0;
    -o-border-radius: 4px 4px 0 0;
}

");

Yii::app()->clientScript->registerScript('manageDropdownsJS', "


x2.dropdownManager = (function () {

function DropdownManager (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this._init ();
}

DropdownManager.prototype.deleteOption = function (elem) {
    $(elem).closest('li').remove();
};

DropdownManager.prototype.moveOptionUp = function (elem) {
    var prev = $(elem).closest('li').prev('li').detach ();
    if(prev.length>0) {
        $(elem).closest('li').after (prev);
    }
};

DropdownManager.prototype.moveOptionDown = function (elem) {
    var next = $(elem).closest('li').next('li').detach ();
    if(next.length>0) {
        $(elem).closest('li').before (next);
    }
};

/**
 * Color options allow separate values and labels and have color picker inputs
 */
DropdownManager.prototype._addColorOption = function () {
    var newOption = $('#color-dropdown-option-template').clone ();
    $(newOption).find ('[disabled=\"disabled\"]').each (function () {
        $(this).removeAttr ('disabled');
    });
    $('#dropdown-options [name=\"Admin[enableColorDropdownLegend]\"]').first ().before (newOption);
    newOption.removeAttr ('id');
    newOption.show ();
    x2.colorPicker.setUp (newOption.find ('input').first (), false);
};

DropdownManager.prototype.addOption = function () {
    if ($('#color-dropdown-option-template').length) {
        this._addColorOption ();
    } else {
        $('#dropdown-options .multi-checkbox-label').before (' \
        <li>\
            <input type=\"text\" size=\"30\" name=\"Dropdowns[options][]\" />\
            <div>\
                <a href=\"javascript:void(0)\" onclick=\"x2.dropdownManager.moveOptionUp(this);\">[".
                    Yii::t('admin','Up')."]</a>\
                <a href=\"javascript:void(0)\" onclick=\"x2.dropdownManager.moveOptionDown(this);\">[".
                    Yii::t('admin','Down')."]</a>\
                <a href=\"javascript:void(0)\" onclick=\"x2.dropdownManager.deleteOption(this);\">[".
                    Yii::t('admin','Del')."]</a>\
            </div><br />\
        </li>');
    }
};

/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/

/*
Private instance methods
*/

DropdownManager.prototype._setUpEditPageBehavior = function () {
    $('#edit-dropdown-dropdown-selector').change (function () {
        if ($(this).val () !== '') {
            $('#edit-dropdown-form .dropdown-config').show ();
        } else {
            $('#edit-dropdown-form .dropdown-config').hide ();
        }
    });
};

DropdownManager.prototype._init = function () {
    this._setUpEditPageBehavior ();
};

return new DropdownManager ();
 
}) ();

",CClientScript::POS_READY);


?>
<div class="page-title"><h2><?php echo Yii::t('admin', 'Dropdown List'); ?></h2></div>
<div class="form">
    <div style="width:600px;">
        <?php echo Yii::t('admin', 'Manage all dropdowns.  These can be linked to fields via Field Management.  Any default dropdowns can also be edited here to change the available options throughout the application.  Deleting default dropdowns may cause issues with pre-existing forms.') ?>
    </div>
</div>
<div id="dropdowns-grid-container">
<?php
$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'fields-grid',
    'baseScriptUrl' => Yii::app()->request->baseUrl.
        '/themes/'.Yii::app()->theme->name.'/css/gridview',
    'template' => '<div id="dropdowns-grid-page-title" class="page-title"><h2>'.
        CHtml::encode (Yii::t('admin', 'Dropdowns')).'</h2>'.'{summary}</div>{items}{pager}',
    'dataProvider' => $dataProvider,
    'columns' => array(
        'name',
        'options',
    ),
));
?>
<br>
<a href="#" onclick="$('#createDropdown').toggle();$('#deleteDropdown').hide();$('#editDropdown').hide();return false;" class="x2-button"><?php echo Yii::t('admin', 'Create Dropdown'); ?></a>
<a href="#" onclick="$('#deleteDropdown').toggle();$('#createDropdown').hide();$('#editDropdown').hide();return false;" class="x2-button"><?php echo Yii::t('admin', 'Delete Dropdown'); ?></a>
<a href="#" onclick="$('#editDropdown').toggle();$('#createDropdown').hide();$('#deleteDropdown').hide();return false;" class="x2-button"><?php echo Yii::t('admin', 'Edit Dropdown'); ?></a>
<br>
<br>
</div>
<div id="createDropdown" style="display:none;">
    <?php
    $this->renderPartial('createDropdown', array(
        'model' => $model,
    ));
    ?>
</div>
<div id="deleteDropdown" style="display:none;">
<?php

$customDropdowns = Dropdowns::model()->findAll('id>=1000');
usort ($customDropdowns, function ($a, $b) {
    return strcmp ($a->name, $b->name);
});
$this->renderPartial('deleteDropdown', array(
    'dropdowns' => $customDropdowns,
));
?>
</div>
<div id="editDropdown" style="display:none;">
<?php
$this->renderPartial('editDropdown', array(
    'model' => $model,
));
?>
</div>
