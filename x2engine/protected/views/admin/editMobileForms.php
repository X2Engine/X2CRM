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




//Yii::app()->clientScript->registerCssFile(
    //Yii::app()->theme->baseUrl.'/css/views/admin/editMobileForms.css');
Yii::app()->clientScript->registerCss('editMobileForms',"
#admin-edit-mobile-forms  .layout-container {
    margin-bottom: 5px;
}

");

Yii::app()->clientScript->registerPackage ('multiselect');

Yii::app()->clientScript->registerScript('adminCreatePageJS',"
;(function () {

var form$ = $('#admin-edit-mobile-forms');
var fieldSelectionContainer$ = form$.find ('.field-selection-container');
form$.find ('.model-name-selector').change (function () {
    fieldSelectionContainer$.hide ();
    fieldSelectionContainer$.find ('.layout-container').empty ();
    $.ajax ({
        url: yii.scriptUrl + '/admin/getMobileLayouts',
        type: 'get',
        data: {
            modelName: form$.find ('select[name=\"EditMobileFormsFormModel[modelName]\"]').val ()
        }, 
        dataType: 'json',
        success: function (data) {
            _(['defaultForm', 'defaultView']).forEach (function (layoutName) {
                var layout = data[layoutName];
                var multiselect$ = $('<select>', {
                    'class': 'x2-multiselect',
                    multiple: 'multiple',
                    name: 'EditMobileFormsFormModel[' + layoutName + '][]'
                });
                for (var j in layout) {
                    multiselect$.append ($('<option>', {
                        value: j,
                        text: layout[j],
                        selected: true
                    }));
                }
                for (var j in data[layoutName + 'Unselected']) {
                    multiselect$.append ($('<option>', {
                        value: j,
                        text: data[layoutName + 'Unselected'][j],
                        selected: false
                    }));
                }
                fieldSelectionContainer$.find (
                    '.' + (layoutName === 'defaultForm' ? 
                        'form' : 'view') + '-layout-container').append (multiselect$);
            }).value ();
            x2.forms.initializeMultiselects ();
            fieldSelectionContainer$.show ();
        }
    });
});

}) ();
", CClientScript::POS_END);

?>
<div class='page-title'>
<h2><?php echo Yii::t('admin','Edit Mobile Forms'); ?></h2>
</div>
<div class='admin-form-container form' id='admin-edit-mobile-forms'>
    <?php
    $form = $this->beginWidget ('X2ActiveForm', array (
            'formModel' => $model,
            'instantiateJSClassOnInit' => false,
        ));
        X2Html::getFlashes ();
        echo $form->errorSummary ($model);
        echo $form->label ($model, 'modelName');
        echo $form->dropDownList($model, 'modelName', $recordTypes, array(
            'class' => 'model-name-selector',
        ));
        ?>
        <div class='field-selection-container' <?php
            echo $model->defaultView || $model->defaultForm ? '' : 'style="display: none;';
        ?>>
            <?php
            echo $form->label ($model, 'defaultView');
            ?>
            <div class='view-layout-container layout-container'>
            <?php
            if ($model->defaultView) {
                list ($selected, $unselected) = MobileLayouts::getFieldOptions (
                    $model->defaultView, $model->modelName);
                echo $form->dropDownList ($model, 'defaultView', array_merge (
                    $selected, $unselected
                ), array (
                    'style' => 'display: none;', 'multiple' => 'multiple',
                    'class' => 'x2-multiselect'));
            }
            ?>
            </div>
            <?php
            echo $form->label ($model, 'defaultForm');
            ?>
            <div class='form-layout-container layout-container'>
            <?php
            if ($model->defaultForm) {
                list ($selected, $unselected) = MobileLayouts::getFieldOptions (
                    $model->defaultForm, $model->modelName);
                echo $form->dropDownList ($model, 'defaultForm', array_merge (
                    $selected, $unselected
                ), array (
                    'style' => 'display: none;','multiple' => 'multiple',
                    'class' => 'x2-multiselect'));
            }
            ?>
            </div>
        </div>
        <?php
        echo CHtml::submitButton(Yii::t('admin', "Save"), array(
            'class' => 'x2-button',
        ));
    $this->endWidget ();
    ?>
</div>
