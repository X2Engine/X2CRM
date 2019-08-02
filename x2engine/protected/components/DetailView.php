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
 * Child Class of Record View to display a form with inline
 * editing. 
 * @author Alex Rowe <alex@x2engine.com>
 */
class DetailView extends RecordView {

    public $JSClass = 'DetailView';

    /**
     * Wether to make link fields link
     * @var boolean
     */
    public $nameLink = false;

    /**
     * Whether to restrict this view to half width
     * @var boolean
     */
    public $halfWidth = false;

    /**
     * @var array $disableInlineEditingFor Names of fields for which inline editing should be 
     *  disabled
     */
    public $disableInlineEditingFor = array (); 

    public function getPackages () {
        return array_merge (parent::getPackages(), array(
            'DetailViewJS' => array(
                'baseUrl' => Yii::app()->baseUrl.'/js/recordView/',
                'js' => array(
                    'inlineEditor.js',
                    'DetailView.js'
                ),
                'depends' => array('RecordViewJS')
            )
        ));
    }

    public function getJSClassParams () {
        return array_merge (parent::getJSClassParams(), array(
            'inlineEdit' => Yii::app()->controller->checkPermissions($this->model,'edit'),
        ));
    }

    public function getTranslations () {
        return array (
            'unsavedChanges' => Yii::t('app', 'There are unsaved changes on this page')
        );
    }

    /**
     * Modified to add half-width tag and extra classes
     */
    public function getMainOptions () {
        $width = $this->halfWidth ? 'half-width' : '';

        return X2Html::mergeHtmlOptions (array (
            'class' => "x2-layout detail-view $width",
            'id' => $this->namespace . 'detail-view',
        ), $this->htmlOptions);
    }

    /**
     * Modified to add inline-edit class if necessary
     */
    public function getItemOptions($item, Fields $field) {
        $inlineEdit = $this->canEdit($field) ? ' inline-edit' : '';

        $parent = parent::getItemOptions($item, $field);
        $parent['class'] .= $inlineEdit;

        return $parent;
    }

    /**
     * Renders the attribute and invisible inline edit field
     * if user can inline-edit
     */
    public function renderAttribute ($item, Fields $field) {
        $class = '';
        $style = '';
        if ($field->type == 'text') {
            $class = 'textBox';
            $style .= 'min-height:' . $item['height'] . 'px';
        }

        $html = X2Html::openTag('div', array(
            'class' => "formInputBox $class",
            'style' => $style,
        ));

        if($this->canEdit($field)) {
            $html .= $this->renderInput ($item, $field);
        }

        $html .= X2Html::tag('span', array(
            'class' => 'model-attribute',
            'id' => $field->modelName.'_'.$field->fieldName.'_field-field'
        ));

        if (isset($this->specialFields[$field->fieldName])) {
            $html .= $this->specialFields[$field->fieldName];
        } else if ($field->fieldName == 'name' && $this->nameLink &&
            $this->model->asa('LinkableBehavior')) {
                $html .= $this->model->link;
        } else {
            $rendered = $this->model->renderAttribute ($field->fieldName, true, false);
            if (!$rendered) $rendered = '&nbsp;';
            $html .= $rendered;
            if($field->linkType == "formula") {
                $fieldName = $field->fieldName;
                $this->model->$fieldName = $rendered;
                $this->model->save();
            }
        }

        $html .= '</span>';
        $html .= '</div>';

        if ($this->canEdit($field) && !$field->readOnly) {
            $html .= $this->renderInlineButtons();
        }

        return $html;
    }

    /**
     * renders the hidden input for an attribute
     * @param  [type] $item  [description]
     * @param  [type] $field [description]
     * @return [type]        [description]
     */
    public function renderInput ($item, $field){
        $html = X2Html::openTag('span', array(
            'class' => 'model-input',
            'id' => $field->modelName.'_'.$field->fieldName.'_field-input',
            'style' => 'display:none',
        ));
        
        $html .= $this->model->renderInput($field->fieldName, array(
            'tabindex' => $item['tabindex'],
            'disabled' => $item['readOnly'] ? 'disabled' : '',
        ));

        $html .= '</span>';
        return $html;
    }


    /**
     * renders inline edit buttons in the extra column
     */
    public function renderInlineButtons () {
        $html = '<div class="inline-edit-icons">';

        $html .= CHtml::link (X2Html::fa('fa-edit'), '#', array(
            'class' => 'edit-icon active',
            'title' => Yii::t('app','Edit field'),
        ));

        $html .= CHtml::link (X2Html::fa('fa-times-circle'), '#', array(
            'class' => 'cancel-icon',
            'title' => Yii::t('app', 'Cancel changes'),
        ));

        $html .= CHtml::link (X2Html::fa('fa-check-circle'), '#', array(
            'class' => 'confirm-icon',
            'title' => Yii::t('app', 'Confirm changes'),
        ));

        $html .= '</div>';
        return $html;
    }



    public function getLayoutData() {
        $layoutData = Yii::app()->cache->get('form_' . $this->modelName . '_' . $this->scenario);
        if ($layoutData) {
            return $layoutData;
        }

        $layout = FormLayout::model()->findByAttributes(
            array(
                'model' => ucfirst($this->modelName),
                'defaultView' => 1,
                'scenario' => $this->scenario
            ));

        if(!$layout && $this->scenario === 'Inline') {
            $layout = FormLayout::model()->findByAttributes(
                array(
                    'model' => ucfirst($this->modelName),
                    'defaultView' => 1,
                    'scenario' => 'Default'
                ));
        }

        if(isset($layout)) {
            $layoutData = json_decode($layout->layout, true);
            Yii::app()->cache->set('form_' . $this->modelName . '_' . $this->scenario, $this->layoutData, 0);    // cache the data
        }

        return $layoutData;
    }

    /**
     * Added condition that scenario is not Inline. be aware
     * 'Inline' does not refer to inline-edit, but rather Inline view,
     * turned on in a quickView for example.
     */
    public function canEdit(Fields $field) {
        return !in_array ($field->fieldName, $this->disableInlineEditingFor) &&
            parent::canEdit ($field) && $this->scenario !== 'Inline';
    }
}

?>
