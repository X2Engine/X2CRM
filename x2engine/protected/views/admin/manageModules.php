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




Yii::app()->clientScript->registerPackage ('multiselect');
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->getBaseUrl ().'/js/ManageMenuItemsMultiselect.js', CClientScript::POS_END);
Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl.'/css/views/admin/manageModules.css');
Yii::app()->clientScript->registerScript('renderMultiSelect',"
$(document).ready(function() {
	 $('#module-multiselect').manageMenuItemsMultiselect ({
        searchable: false,
        deletableOptions: ".CJSON::encode ($deletableOptions).",
        translations: ".CJSON::encode (array (
            'deleteModule' => Yii::t('app', 'Delete top bar link'),
            'message' => Yii::t('app', 'Are you sure you want to delete this top bar link?'),
            'title' => Yii::t('app', 'Delete top bar link?'),
            'confirm' => Yii::t('app', 'Delete'),
        ))."
    });
});
",CClientScript::POS_HEAD);
?>

<div class="page-title"><h2><?php 
    echo CHtml::encode (Yii::t('admin','Manage Menu Items')); 
?></h2></div>
<?php 
$form = $this->beginWidget ('CActiveForm', array (
	'id'=>'manage-modules',
	'enableAjaxValidation'=>false,
)); 
X2Html::getFlashes ();
?>
<div class="form" id='manage-menu-items-form-outer'>
<?php 
echo CHtml::encode (Yii::t('admin','Re-order, add, or remove top bar module links:')); 
?>
<br><br>
<?php
echo CHtml::hiddenField('formSubmit','1');
echo CHtml::dropDownList(
    'menuItems[]',
    $selectedItems,
    $menuItems,array(
        'class'=>'x2-multiselect',
        'id'=>'module-multiselect',
        'multiple'=>'multiple',
        'data-skip-auto-init'=>'1',
        'size'=>8
    )
);
?>
<br>
<div class="row buttons">
	<?php 
    echo CHtml::submitButton(Yii::t('app','Submit'),array('class'=>'x2-button')); 
    ?>
</div>
</div>
<?php 
$this->endWidget(); 
?>
