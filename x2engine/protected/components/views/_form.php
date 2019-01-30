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
 * @params bool $suppressQuickCreate if true, does not display quick create buttons for lookup
 *  type fields
 * @param bool $suppressForm if true, does not wrap html in an active form widget
 * @param array $defaultsByRelatedModelType (<model type> => <array of default arguments>). Used
 *  as arguments to RelationshipsManager JS prototype for each of the quick create buttons.
 */

// Construct criteria for finding the right form layout.
$attributes = array('model' => ucfirst($modelName), 'defaultForm' => 1);
// If the $scenario variable is set in the rendering context, a special
// different form should be retrieved.
$attributes['scenario'] = isset($scenario) ? $scenario : 'Default';
$layout = FormLayout::model()->findByAttributes($attributes);

if (isset($layout)) {
    $layoutData = json_decode($layout->layout, true);
}

if (isset ($layoutData['version']) && version_compare ($layoutData['version'], '5.2') >= 0) {
    $this->widget ('FormView', array(
        'model' => $model,
    ));
} else {

Yii::app()->clientScript->registerScript('formUIScripts', "
$('.x2-layout.form-view :input').change(function() {
    $('#save-button, #save-button1, #save-button2, h2 a.x2-button').addClass('highlight');
});
", CClientScript::POS_READY);

Yii::app()->clientScript->registerScript('datePickerDefault', "
    $.datepicker.setDefaults ($.datepicker.regional['']);
", CClientScript::POS_READY);

Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/recordEdit.css');

Yii::app()->clientScript->registerScript('setFormName', "
window.formName = '$modelName';
", CClientScript::POS_HEAD);


$renderFormTags = !isset($form);

if ((isset($suppressForm) && !$suppressForm) || !isset($suppressForm)) {

    if ($renderFormTags) {
        $form = $this->beginWidget('CActiveForm', array(
            'id' => $modelName . '-form',
            'enableAjaxValidation' => false,
        ));
    }
}

echo '<em style="display:block;margin:5px;">' .
 Yii::t('app', 'Fields with <span class="required">*</span> are required.') . "</em>\n";


$suppressQuickCreate = isset($suppressQuickCreate) ? $suppressQuickCreate : false;

if (!$suppressQuickCreate) {
    $modelsWhichSupportQuickCreate = array_flip(QuickCreateRelationshipBehavior::getModelsWhichSupportQuickCreate());
    $quickCreateButtonTypes = array();
}


if (isset ($layoutData)) {


    echo '<div class="x2-layout form-view">';

    // $temp=RoleToUser::model()->findAllByAttributes(array('userId'=>Yii::app()->user->getId(),'type'=>'user'));
    // $roles=array();
    // foreach($temp as $link){
    // $roles[]=$link->roleId;
    // }
    // /* x2temp */
    // $groups=GroupToUser::model()->findAllByAttributes(array('userId'=>Yii::app()->user->getId()));
    // foreach($groups as $link){
    // $tempRole=RoleToUser::model()->findByAttributes(array('userId'=>$link->groupId, 'type'=>'group'));
    // if(isset($tempRole))
    // $roles[]=$tempRole->roleId;
    // }
    /* end x2temp */

    $fields = array();
    $fieldModels = Fields::model()->findAllByAttributes(array('modelName' => get_class($model)));
    foreach ($fieldModels as &$fieldModel)
        $fields[$fieldModel->fieldName] = $fieldModel;
    unset($fieldModel);

    echo $form->errorSummary($model) . ' ';

    $formSettings = Profile::getFormSettings($modelName);

    if (isset($layoutData['sections']) && count($layoutData['sections']) > 0) {

        $fieldPermissions = array();
        $bypassPermissions = false;

        if (!isset($specialFields))
            $specialFields = array();

        if (!Yii::app()->params->isAdmin && !empty(Yii::app()->params->roles)) {
            $fieldPermissions = $model->getFieldPermissions();
        } else {
            $bypassPermissions = true;
        }

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
                $formSettings[$i] = ($section['title'] === 'Social Media' ? 0 : 1);
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
                $htmlString .=
                        '<span class="sectionTitle" title="' . addslashes($section['title']) . '">' .
                        Yii::t(strtolower(Yii::app()->controller->id), $section['title']) .
                        '</span>';
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

                            $width = isset($col['width']) ?
                                    ' style="width:' . $col['width'] . 'px"' : '';
                            $htmlString .= "<td$width>";
                            if (isset($col['items'])) {
                                foreach ($col['items'] as &$item) {

                                    if (isset($item['name'], $item['labelType'], $item['readOnly'], $item['height'], $item['width'])) {

                                        $fieldName = preg_replace('/^formItem_/u', '', $item['name']);

                                        if (isset($fields[$fieldName])) {
                                            $field = &$fields[$fieldName];

                                            if (($field->fieldName == "company" ||
                                                    $field->fieldName == "accountName") &&
                                                    isset($hideAccount) && $hideAccount == true) {
                                                continue;
                                            }

                                            if (isset($fieldPermissions[$field->fieldName])) {
                                                if ($fieldPermissions[$field->fieldName] == 0) {
                                                    unset($item);
                                                    $htmlString .= '</div></div>';
                                                    continue;
                                                } elseif (
                                                    $fieldPermissions[$field->fieldName] == 1) {

                                                    $item['readOnly'] = true;
                                                } else {
                                                    $item['readOnly'] = false;
                                                }
                                            } else if (!$bypassPermissions) {
                                                continue;
                                            } else {
                                                $item['readOnly'] = false;
                                            }
                                            $noItems = false;

                                            $labelType = isset($item['labelType']) ?
                                                    $item['labelType'] : 'top';

                                            switch ($labelType) {
                                                case 'inline':
                                                    $labelClass = 'inlineLabel';
                                                    break;
                                                case 'none':
                                                    $labelClass = 'noLabel';
                                                    break;
                                                case 'left':
                                                    $labelClass = 'leftLabel';
                                                    break;
                                                case 'top':
                                                default:
                                                    $labelClass = 'topLabel';
                                            }

                                            /* set value of field to label if this is an inline 
                                              labeled field */
                                            if (empty($model->$fieldName) && $labelType == 'inline')
                                                $model->$fieldName = $field->attributeLabel;

                                            if ($field->type === 'text')
                                                $textFieldHeight = $item['height'] . 'px';
                                            $item['height'] = 'auto';

                                            $htmlString .= '<div class="formItem ' . $labelClass . '">';
                                            $htmlString .= $form->labelEx($model, $field->fieldName);
                                            $htmlString .=
                                                    '<div class="formInputBox" 
                                  style="width:' . $item['width'] . 'px;height:' . $item['height'] . ';">';
                                            $default = $model->$fieldName == $field->attributeLabel;
                                            if (isset($idArray)) {
                                                $htmlString .= X2Model::renderMergeInput($modelName, $idArray, $field);
                                            } else {
                                                if (isset($specialFields[$fieldName])) {
                                                    $htmlString .= $specialFields[$fieldName];
                                                } else {
                                                    $htmlString .= $model->renderInput(
                                                            $fieldName, array(
                                                        'tabindex' => isset($item['tabindex']) ?
                                                                $item['tabindex'] : null,
                                                        'disabled' => $item['readOnly'] ?
                                                                'disabled' : null,
                                                        'style' => $field->type === 'text' ?
                                                                'height: ' . $textFieldHeight : ''
                                                    ));
                                                }
                                            }
                                            $htmlString .= "</div>";

                                            if ($field->type === 'link' && !$suppressQuickCreate &&
                                                isset(
                                                    $modelsWhichSupportQuickCreate[
                                                        $field->linkType])) {
                                                $htmlString .=
                                                    '<span class="quick-create-button create-' .
                                                      $field->linkType . '">+</span>';
                                                $quickCreateButtonTypes[] = $field->linkType;
                                            }
                                        }
                                    }
                                    unset($item);
                                    $htmlString .= '</div>';
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
    }
    ?>
    </div>
    <?php
}

if ((isset($suppressForm) && !$suppressForm) || !isset($suppressForm)) {
    if ($renderFormTags) {
        echo '<div class="row buttons save-button-row">' .
        CHtml::submitButton(
                $model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save'), array('class' => 'x2-button', 'id' => 'save-button', 'tabindex' => 24)) .
        '</div>';
        $this->endWidget();
    }
}

Yii::app()->clientScript->registerScript('mask-currency', '
    $(".currency-field").maskMoney("mask");
', CClientScript::POS_READY);

if (!$suppressQuickCreate) {

    Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl() . '/js/Relationships.js');

    Yii::app()->clientScript->registerScript('_formScript', "

(function () {
    var quickCreateUrls = " .
            CJSON::encode(
                    QuickCreateRelationshipBehavior::getCreateUrlsForModels($quickCreateButtonTypes)) . ";
    var quickCreateTooltips = " .
            CJSON::encode(
                    QuickCreateRelationshipBehavior::getDialogTooltipsForModels(
                            $quickCreateButtonTypes, get_class($model))) . ";
    var quickCreateDialogTitles = " .
            CJSON::encode(
                    QuickCreateRelationshipBehavior::getDialogTitlesForModels(
                            $quickCreateButtonTypes)) . ";
    var defaultsByRelatedModelType = " .
            CJSON::encode(isset($defaultsByRelatedModelType) ? $defaultsByRelatedModelType : array())
            . ";

    $('.quick-create-button').each (function () {
        var relatedModelType = $(this).attr ('class').match (/(?:[ ]|^)create-([^ ]+)/)[1];
        new x2.RelationshipsManager ({
            element: $(this),
            modelType: '" . get_class($model) . "',
            modelId: " . (isset($model->id) ? $model->id : 'null') . ",
            relatedModelType: relatedModelType,
            createRecordUrl: quickCreateUrls[relatedModelType],
            dialogTitle: quickCreateDialogTitles[relatedModelType],
            tooltip: quickCreateTooltips[relatedModelType],
            attributeDefaults: defaultsByRelatedModelType[relatedModelType],
            lookupFieldElement: $(this).siblings ('.formInputBox').find ('input').last (),
            isViewPage: false
        });
    });

}) ();

", CClientScript::POS_READY);
}

}
?>
