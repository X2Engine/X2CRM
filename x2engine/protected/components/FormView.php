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
 * Child Class for a form record view 
 * 
 * @see RecordView
 * @package application.components
 * 
 * @author Alex Rowe <alex@x2engine.com>
 */
class FormView extends RecordView {

    public $htmlOptions = array (
        'class' => ''
    ); 

    /**
     * CActiveForm Object optionally passed to the widget
     * @var CActiveForm Object
     */
    public $form;

    /**
     * Js Class name
     * @var string
     */
    public $JSClass = 'FormView';

    /**
     * Models names that support quick create
     * @var array => string
     */
    public $quickCreateModels = array();

    /**
     * An array of IDs of a current duplication.
     * If set, the form input will become dropdowns of 
     * the different possible inputs
     * @var array
     */
    public $idArray;

    /**
     * [$defaultsByRelatedModelType description]
     * @var array
     */
    public $defaultsByRelatedModelType = array();

    /**
     * If true, quick create buttons will not render
     * Typicall true if this form is a quick create form
     * @var boolean
     */
    public $suppressQuickCreate = false;

    /**
     * Contains quick create button types
     * @var array 
     */
    private $_quickCreateButtonTypes = array();

    public function init() {
        parent::init();

        $this->quickCreateModels = array_flip (
            QuickCreateRelationshipBehavior::getModelsWhichSupportQuickCreate());
    }

    public function run () {

        // If form is empty, we will not add the form wrappers
        if (!empty($this->form)) {
            parent::run();
            return;
        }

        $this->form = $this->beginWidget('CActiveForm', array(
            'id' => $this->modelName . '-form',
            'enableAjaxValidation' => false,
        ));

        parent::run ();

        echo X2Html::tag('div', array(
            'class' => 'row buttons save-button-row'
        ));

        $text  = $this->model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save');
        echo X2Html::submitButton ($text, array(
                'class' => 'x2-button', 
                'id' => 'save-button', 
                'tabindex' => 24
            ));
        echo '</div>';
        $this->endWidget();
    }

    public function getJSClassParams() {
        return array_merge(parent::getJSClassParams(), array(
            'quickCreate' => array (
                'urls' => QuickCreateRelationshipBehavior::getCreateUrlsForModels(
                    $this->_quickCreateButtonTypes),
                'tooltips' => QuickCreateRelationshipBehavior::getDialogTooltipsForModels(
                    $this->_quickCreateButtonTypes, get_class($this->model)),
                'dialogTitles' => QuickCreateRelationshipBehavior::getDialogTitlesForModels(
                    $this->_quickCreateButtonTypes),
                'defaults' => $this->defaultsByRelatedModelType
            ),
            'translations' => array (
                'title' => Yii::t('app', 'Discard unsaved changes?'),
                'message' => 
                    Yii::t('app', 'You have unsaved changes to this record. Discard changes?'),
                'cancel' => Yii::t('app', 'Cancel'),
                'confirm' => Yii::t('app', 'Yes'),
            )
        ));
    }

    public function getPackages () {
        $packages  = array_merge(parent::getPackages(), array(
            'RecordEditCss' => array (
                'baseUrl' => Yii::app()->theme->baseUrl,
                'css' => array (
                    'css/recordEdit.css'
                )
            ),
            'FormViewJS' => array (
                'baseUrl' => Yii::app()->baseUrl,
                'js' => array (
                    'js/recordView/FormView.js'
                ),
                'depends' => array ('RecordViewJS')
            )
        ));

        if(!$this->suppressQuickCreate) {
            $packages = array_merge($packages, array(
                'RelationshipJS' => array (
                    'baseUrl' => Yii::app()->baseUrl,
                    'js' => array (
                        'js/Relationships.js'
                    ),
                    'depends' => array ('X2Widget')
                )
            ));
        }

        return $packages;
    }

    public function getMainOptions () {
        return array (
            'class' => 'x2-layout form-view',
            'id' => $this->namespace . 'form-view',
        );
    }

    public function renderMain () {
        $html = X2Html::openTag('div', $this->getMainOptions());

        $html .= $this->form->errorSummary ($this->model);

        $html .= $this->renderSections ();

        $html .= '</div>';
        return $html;
    }


    public function renderAttribute ($item, Fields $field) {
        $fieldName = preg_replace('/^formItem_/u', '', $item['name']);
        
        $html = X2Html::openTag('div', array(
            'class' => "formInputBox"
        ));

        if (isset($this->idArray)) {
            $html .= X2Model::renderMergeInput ($this->modelName, $this->idArray, $field);
        } else if (isset($this->specialFields[$fieldName])) {
            $html .= $this->specialFields[$fieldName];
        } else {
            $html .= $this->model->renderInput($fieldName, array(
                'tabindex' => $item['tabindex'],
                'disabled' => $item['readOnly'],
                'style' => $item['height'],
                'id' => $this->namespace.X2Html::resolveId ($this->model, $fieldName),
            ));
       }

       $html .= '</div>';

       $html .= $this->renderExtra($field);

       return $html;
    }

    /**
     * Renders the quick create/ Field help icons
     */
    public function renderExtra ($field) {
        $html = '';
        if ($field->type === 'link' && !$this->suppressQuickCreate &&
            isset($this->quickCreateModels[$field->linkType])) {
            $html .= X2Html::tag ('span', array (
                'class' => "quick-create-button create-$field->linkType"
            ), '+');

            $this->_quickCreateButtonTypes[] = $field->linkType;
        }
        
        if (!empty($field->description)) {
            $html .= X2Html::hint($field->description);
        }

        return $html;
    }

    public function getLayoutData() {
        $attributes = array(
            'model' => ucfirst($this->modelName),
            'defaultForm' => 1,
            'scenario' => $this->scenario
        );

        $layout = FormLayout::model()->findByAttributes($attributes);
        if ($layout)
            return CJSON::decode($layout->layout);
    }

    public function renderLabel ($field) {
        return CHtml::activeLabelEx ($this->model, $field->fieldName);
    }
}

?>
