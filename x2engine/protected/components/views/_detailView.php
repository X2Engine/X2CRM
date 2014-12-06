<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * California 95067, USA. or at email address contact@x2engine.com.
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
 *****************************************************************************************/

$attributeLabels = $model->attributeLabels();

// $showSocialMedia = Yii::app()->params->profile->showSocialMedia;
// $showWorkflow = Yii::app()->params->profile->showWorkflow;

$cs = Yii::app()->getClientScript();


if ($modelName == 'contacts' || $modelName == 'opportunities') {
    $cs->registerScript('toggleWorkflow', "
    function showWorkflow() {
        $('tr#workflow-row').show();
        $('tr#workflow-toggle').hide();
    }
    function hideWorkflow() {
        $('tr#workflow-row').hide();
        $('tr#workflow-toggle').show();
    }
    ", CClientScript::POS_HEAD);
}

// $(function() {\n"
// .($showWorkflow? "showWorkflow();\n" : "hideWorkflow()\n")
// ."});",CClientScript::POS_HEAD);

$cs->registerScript('setFormName', "
window.formName = '$modelName';
", CClientScript::POS_HEAD);

$scenario = isset($scenario) ? $scenario : 'Default';
$nameLink = isset($nameLink) ? $nameLink : false;

$authParams['X2Model'] = $model;
$moduleName = X2Model::getModuleName($modelName);
$modelEdit = Yii::app()->params->isAdmin || Yii::app()->user->checkAccess(ucfirst($moduleName) . 'Update', $authParams);

// check the app cache for the data
$layoutData = Yii::app()->cache->get('form_' . $modelName . '_' . $scenario);
$fields = array();


// remove this later, once all models extend X2Models
if (method_exists($model, 'getFields')) {
    $fields = $model->getFields(true);
} else {
    foreach (X2Model::model('Fields')->findAllByAttributes(
            array('modelName' => ucfirst($modelName))) as $fieldModel) {

        $fields[$fieldModel->fieldName] = $fieldModel;
    }
}

if ($layoutData === false) {
    $layout = FormLayout::model()->findByAttributes(
            array('model' => ucfirst($modelName), 'defaultView' => 1, 'scenario' => $scenario));

    if (isset($layout)) {
        $layoutData = json_decode($layout->layout, true);
        Yii::app()->cache->set('form_' . $modelName . '_' . $scenario, $layoutData, 0);    // cache the data
    }
}

if ($layoutData !== false && isset($layoutData['sections']) && count($layoutData['sections']) > 0) {
    echo '<div class="x2-layout' . ((isset($halfWidth) && $halfWidth) ? ' half-width' : '') . '">';
    $formSettings = Profile::getFormSettings($modelName);

    $fieldPermissions = array();

    if (!Yii::app()->params->isAdmin && !empty(Yii::app()->params->roles)) {
        $rolePermissions = Yii::app()->db->createCommand()
                ->select('fieldId, permission')
                ->from('x2_role_to_permission')
                ->join('x2_fields', 'x2_fields.modelName="' . $modelName .
                        '" AND x2_fields.id=fieldId AND roleId IN (' . implode(',', Yii::app()->params->roles) . ')')
                ->queryAll();

        foreach ($rolePermissions as &$permission) {
            if (!isset($fieldPermissions[$permission['fieldId']]) ||
                    $fieldPermissions[$permission['fieldId']] < (int) $permission['permission']) {
                $fieldPermissions[$permission['fieldId']] = (int) $permission['permission'];
            }
        }
    }

    if (!isset($specialFields))
        $specialFields = array();
    if (!isset($suppressFields))
        $suppressFields = array();

    $i = 0;
    foreach ($layoutData['sections'] as &$section) {
        $noItems = true; // if no items, don't display section
        // set defaults
        if (!isset($section['title']))
            $section['title'] = '';
        if (!isset($section['collapsible']))
            $section['collapsible'] = false;
        if (!isset($section['rows']))
            $section['rows'] = array();
        if (!isset($formSettings[$i])) {
            $formSettings[$i] = 1;
        }

        $collapsed = !$formSettings[$i] && $section['collapsible'];

        $htmlString = '';

        $htmlString .= '<div class="formSection';
        if ($section['collapsible'])
            $htmlString .= ' collapsible';
        if (!$collapsed)
            $htmlString .= ' showSection';
        $htmlString .= '">';

        $htmlString .= '<div class="formSectionHeader">';
        if ($section['collapsible']) {
            $htmlString .=
                    '<a href="javascript:void(0)" class="formSectionHide">
                    '.X2Html::fa('fa-caret-down').'
                </a>';
            $htmlString .=
                    '<a href="javascript:void(0)" class="formSectionShow">
                    '.X2Html::fa('fa-caret-right').'
                </a>';
        }
        if (!empty($section['title'])) {
            $htmlString .= '<span class="sectionTitle" title="' . addslashes($section['title']) . '">' .
                    Yii::t(strtolower(Yii::app()->controller->id), $section['title']) . '</span>';
        } else {
            $htmlString .= '<span class="sectionTitle"></span>';
        }
        $htmlString .= '</div>';

        if (!empty($section['rows'])) {
            $htmlString .= '<div class="tableWrapper"';
            if ($collapsed)
                $htmlString .= ' style="display:none;"';
            $htmlString .= '><table>';

            foreach ($section['rows'] as &$row) {
                $htmlString .= '<tr class="formSectionRow">';
                if (isset($row['cols'])) {
                    foreach ($row['cols'] as &$col) {

                        $width = isset($col['width']) ? ' style="width:' . $col['width'] . 'px"' : '';
                        $htmlString .= "<td$width>";
                        if (isset($col['items'])) {
                            foreach ($col['items'] as &$item) {


                                if (isset($item['name'], $item['labelType'], $item['readOnly'], $item['height'], $item['width'])) {

                                    $fieldName = preg_replace('/^formItem_/u', '', $item['name']);
                                    if (isset($fields[$fieldName])) {
                                        $field = $fields[$fieldName];

                                        if (in_array($fieldName, $suppressFields) ||
                                                isset($fieldPermissions[$field->id]) &&
                                                $fieldPermissions[$field->id] == 0) {
                                            unset($item);
                                            $htmlString .= '</div></div>';
                                            continue;
                                        } else {
                                            $noItems = false;
                                        }

                                        $labelType = isset($item['labelType']) ? $item['labelType'] : 'top';
                                        switch ($labelType) {
                                            case 'inline': $labelClass = 'inlineLabel';
                                                break;
                                            case 'none': $labelClass = 'noLabel';
                                                break;
                                            case 'left': $labelClass = 'leftLabel';
                                                break;
                                            case 'top':
                                            default: $labelClass = 'topLabel';
                                        }
                                        $inlineEdit = $modelEdit && (!$field->readOnly && (!isset($fieldPermissions[$field->id]) || (isset($fieldPermissions[$field->id]) && $fieldPermissions[$field->id] === 2)));
                                        $htmlString .= "<div id=\"{$field->modelName}_{$field->fieldName}_field\"" .
                                                " class=\"formItem $labelClass";
                                        if ($inlineEdit) {
                                            $htmlString.= "  inline-edit";
                                        }
                                        $htmlString .= "\">";
                                        $htmlString .= CHtml::label($model->getAttributeLabel($field->fieldName), false);

                                        $class = 'formInputBox';
                                        $style = 'width:' . $item['width'] . 'px;';
                                        if ($field->type == 'text') {
                                            $class .= ' textBox';
                                            $style .= 'height:' . $item['height'] . 'px;';
                                        }

                                        if ($field->type === 'text')
                                            $textFieldHeight = $item['height'] . 'px';

                                        $htmlString .= '<div class="' . $class . '" style="' . $style . '">';
                                        if ($inlineEdit) {
                                            $htmlString .= '<span class="model-input" id="' . $field->modelName . '_' . $field->fieldName . '_field-input" style="display:none">' . $model->renderInput($field->fieldName, array(
                                                        'tabindex' => isset($item['tabindex']) ?
                                                                $item['tabindex'] : null,
                                                        'disabled' => $item['readOnly'] ?
                                                                'disabled' : null,
                                                        'style' => $field->type === 'text' ?
                                                                'height: ' . $textFieldHeight : '',
                                                    )) . '</span>';
                                            if ($field->type === 'rating') {
                                                $val = $model->$fieldName;
                                                if (empty($model->$fieldName)) {
                                                    $val = 0;
                                                }
                                                Yii::app()->clientScript->registerScript('rating-' . $fieldName, 'ratingFields["' . $field->modelName . '[' . $field->fieldName . ']"] = ' . $val, CClientScript::POS_READY);
                                            }
                                        }
                                        $htmlString .= '<span class="model-attribute" id="' . $field->modelName . '_' . $field->fieldName . '_field-field">';
                                        if (isset($specialFields[$fieldName])) {
                                            $fieldHtml = $specialFields[$fieldName];
                                        } else {
                                            if ($field->fieldName == 'name' && $nameLink && $model->asa('X2LinkableBehavior')) {
                                                $fieldHtml = $model->link;
                                            } else {
                                                $fieldHtml = $model->renderAttribute(
                                                        $field->fieldName, true, false);
                                            }
                                        }
                                        if ($fieldHtml === '') {
                                            $htmlString .= '&nbsp;';
                                        } else {
                                            $htmlString .= $fieldHtml;
                                        }
                                        $htmlString .= '</span>';
                                    }
                                }
                                unset($item);
                                $htmlString .= '</div></div>';
                            }
                        }
                        $htmlString .= '</td>';
                    }
                }
                unset($col);
                $htmlString .= '</tr>';
            }
            $htmlString .= '</table></div>';
        }
        unset($row);
        $htmlString .= '</div>';
        if (!$noItems)
            echo $htmlString;
        $i++;
    }
    echo '</div>';
}
Yii::app()->clientScript->registerScript('inline-edit-js', "
    var editFlag = false;
    var linkClick = false;
    var ratingFields = {};
    $('.model-attribute').on('click', 'a', function(event) {
        linkClick = true;
    });
    $('.inline-edit').on('click', function() {
        if(!linkClick){
            editFlag = true;
            $('.inline-edit-button').show();
            $('#' + $(this).attr('id') + '-input').height($('#' + $(this).attr('id') + '-field').height());
            $('#' + $(this).attr('id') + '-input').show();
            $('#' + $(this).attr('id') + '-field').hide();
            $('#' + $(this).attr('id') + '-input input').focus();
        }
        linkClick = false;
    });
    $('#inline-edit-cancel').on('click', function() {
        $('.inline-edit-button').hide();
        $('.model-input').hide();
        $('.model-attribute').show();
        editFlag = false;
        return false;
    });
    $('#inline-edit-save').on('click', function() {
        attributes = {};
        $('.model-input input, .model-input select, .model-input textarea').each(function() {
            attributes[$(this).attr('name')] = $(this).val();
        });
        $.each(ratingFields, function(index, value) {
            if (typeof value === 'undefined') {
                attributes[index] = '';
            } else {
                attributes[index] = value;
            }
        });
        $('.inline-edit-button').hide();
        
        $.ajax({
            url: yii.scriptUrl + '/site/ajaxSave',
            type: 'POST',
            data: {attributes: attributes, modelId: $model->id},
            success: function(data) {
                results = $.parseJSON(data);
                $.each(results, function(index, value) {
                    $('#' + index + '_field-field').html(value);
                    $('#' + index + '_field-field input[type=radio]').rating();
                    $('#' + index + '_field-field input[type=radio]').rating('readOnly', true);
                });
                $('.model-input').hide();
                $('.model-attribute').show();
                editFlag = false;
            }
        });
        return false;
    });
    $(window).bind('beforeunload', function() {
        if (editFlag) {
            return 'There are unsaved changes on this page.';
        }
    });", CClientScript::POS_END);
?>