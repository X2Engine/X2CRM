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




/*
Rendered by dropdown editor action
*/

Yii::app()->clientScript->registerScript('_colorDropdownFormJS',"
    // initialize value color pickers
    x2.colorPicker._initializeX2ColorPicker ();
", CClientScript::POS_END);

$i = 0;
foreach($options as $value => $label){
++$i;
?>
    <li>
        <label for='dropdown-value-<?php 
            echo $i; ?>'><?php echo Yii::t('actions', 'Color'); 
        ?></label>
        <input id='dropdown-value-<?php echo $i; ?>' class='x2-color-picker x2-color-picker-hash' 
         type="text" size="20" name="Dropdowns[values][]" value='<?php echo $value; ?>' />
        <label for='dropdown-label-<?php 
            echo $i; ?>'><?php echo Yii::t('actions', 'Label'); 
        ?></label>
        <input id='dropdown-label-<?php echo $i; ?>'type="text" size="30"  
         name="Dropdowns[labels][]" value='<?php echo $label; ?>' />
            <div class="">
                <a href="javascript:void(0)" 
                 onclick="x2.dropdownManager.moveOptionUp(this);">
                    [<?php echo Yii::t('admin', 'Up'); ?>]</a>
                <a href="javascript:void(0)" 
                 onclick="x2.dropdownManager.moveOptionDown(this);">
                    [<?php echo Yii::t('admin', 'Down'); ?>]</a>
                <a href="javascript:void(0)" 
                 onclick="x2.dropdownManager.deleteOption(this);">
                    [<?php echo Yii::t('admin', 'Del'); ?>]</a>
            </div>
            <br />
    </li>
<?php
}
echo CHtml::activeCheckbox (
    Yii::app()->settings, 'enableColorDropdownLegend',
    array (
        'class' => 'left-checkbox',
    )
);
echo CHtml::activeLabel (
    Yii::app()->settings, 'enableColorDropdownLegend');
?>
<li id='color-dropdown-option-template' style='display: none;'>
    <label for='dropdown-value-<?php 
        echo $i; ?>'><?php echo Yii::t('actions', 'Color'); 
    ?></label>
    <input disabled='disabled' id='dropdown-value-<?php echo $i; ?>' 
     class='x2-color-picker-hash' type="text" size="20" name="Dropdowns[values][]"
    />
    <label for='dropdown-label-<?php 
        echo $i; ?>'><?php echo Yii::t('actions', 'Label'); 
    ?></label>
    <input disabled='disabled' id='dropdown-label-<?php echo $i; ?>'type="text" size="30"  
     name="Dropdowns[labels][]" />
        <div class="">
            <a href="javascript:void(0)" 
             onclick="x2.dropdownManager.moveOptionUp(this);">
                [<?php echo Yii::t('admin', 'Up'); ?>]</a>
            <a href="javascript:void(0)" 
             onclick="x2.dropdownManager.moveOptionDown(this);">
                [<?php echo Yii::t('admin', 'Down'); ?>]</a>
            <a href="javascript:void(0)" 
             onclick="x2.dropdownManager.deleteOption(this);">
                [<?php echo Yii::t('admin', 'Del'); ?>]</a>
        </div>
        <br />
</li>
