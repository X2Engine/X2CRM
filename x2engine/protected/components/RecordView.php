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
 * Abstract Class for rendering the Form view and Detail View
 * Some things are awkward from being retroactivly applied. 
 * STRUCTURE: 
 * With a given form layout:
 * 
 *      RenderMain                      (getMainOptions)
 *          RenderSections              (getSectionOptions)
 *              RenderSectionTitle      
 *              RenderRows              (getRowOptions)
 *                  RenderColumns       (getColumnOptions)
 *                      RenderItems     (getItemOptions)
 *                          RenderLabel 
 *                          RenderAttribute
 *
 * The get<part>Options() function return an array of html options for 
 * that section. This makes it easier to override classes you want to add on 
 * specific elements
 */
abstract class RecordView extends X2Widget {

    /**
     * @var array $htmlOptions
     */
    public $htmlOptions = array (
        'class' => 'x2-layout-island'
    ); 

    /**
     * JS Class 
     * @var string
     */
    public $JSClass = 'RecordView';

    /**
     * Model Name of the record being displayed
     * @var string
     */
    public $modelName;

    /**
     * Model obejct of the record being displayed
     * @var [type]
     */
    public $model;

    /**
     * Scenario of the form. options: Default, Inline
     * @var string
     */
    public $scenario = 'Default';

    /**
     * Fields to hide from the form
     * @var array
     */
    public $suppressFields = array();

    /**
     * Special fields to render within the view
     * @var array
     */
    public $specialFields = array();

    /**
     * The parsed JSON created from the form layout editor
     * @var array
     */
    public $layoutData;

    /**
     * The form settings that contains the saved collapsed sections
     * @var array
     */
    protected $_formSettings;

    /**
     * Array of field permissions
     * @var array
     */
    protected $_fieldPermissions;

    /**
     * Array of fields objects to extract settings from
     * @var array
     */
    protected $_fields; 

    /**
     * Renders the Attribute of a form item. 
     * For a View, this is the value of the field.
     * For a Form, this is the inpute of the field
     * @param  array $item  array of item settings
     * @param  Field $field field object being rendered
     * @return string        HTML to add to the form
     */
    public abstract function renderAttribute ($item, Fields $field);

    /**
     * Gets the layout of the form.
     * @return array Array of the form layout
     */
    public abstract function getLayoutData ();

    public function setFormSettings (array $formSettings) {
        $this->_formSettings = $formSettings;
    }

    /**
     * Inititialization
     */
    public function init () {
        parent::init();

        // Get default Model name
        if (!isset($this->modelName)) {
            $this->modelName = get_class($this->model);
        }

        // get the form layout data
        if (!isset($this->layoutData)) {
            $this->layoutData = $this->getLayoutData();
        }

        // Get the form settings (Collapsed Rows)
        if (!isset ($this->_formSettings))
            $this->_formSettings = Profile::getFormSettings ($this->modelName);

        // Get the permission for fields
        $this->_fieldPermissions = $this->getFieldPermissions();

        // populate the fields array
        $this->_fields = $this->getFields();
    }

    public function run () {
        // These are necessary variables
        if(!$this->layoutData) return;
        if(!$this->_fields) return;

        // Echo Content
        echo $this->renderMain ();

        // Register JS
        $this->registerPackages();

        // Instantiate JS
        $this->instantiateJSClass (true);

    }

    public function getPackages () {
        return array_merge (parent::getPackages (), array(
            'RecordViewJS' => array (
                'baseUrl' => Yii::app()->baseUrl,
                'js' => array (
                    'js/recordView/RecordView.js'
                ),
                'depends' => array('auxlib')
            )
        ));
    }

    public function getJSClassParams () {
        return array_merge (parent::getJSClassParams(), array(
            'modelId' => $this->model->id,
            'modelName' => $this->modelName,
        ));
    }

    /**
     * HtmlOptions for the main tag that wraps the form
     * @return array HTML Attributes
     */
    public function getMainOptions () {
        return array (
            'class' => 'x2-layout'
        );
    }

    /**
     * HtmlOptions for the .formSection tags 
     * @return array HTML Attributes
     */
    public function getSectionOptions ($section, $collapsed) {
        $visibility = $collapsed ? 'hideSection' : 'showSection';
        $collapsible = $section['collapsible'] ? 'collapsible' : '';

        return array (
            'class' => "formSection $collapsible $visibility"
        );
    }

    /**
     * HtmlOptions for the .formSectionRow tags 
     * @return array HTML Attributes
     */
    public function getRowOptions($row) {
        return array (
            'class' => 'formSectionRow'
        );
    }

    /**
     * HtmlOptions for the .formSectionColumn tags 
     * @return array HTML Attributes
     */
    public function getColumnOptions($col, $count) {
        $width = $col['width'];
        if (!preg_match('/^\d+(\.\d+)?%$/', $col['width']) && $count > 0) {
            $width = (100.0/$count).'%'; 
        }

        return array(
            'style' => "width:$width",
            'class' => "formSectionColumn"
        );
    }

    /**
     * HtmlOptions for the .formItem tags 
     * @return array HTML Attributes
     */
    public function getItemOptions ($item, Fields $field) {

        // noLabel, topLabel, leftLabel, inlineLabel
        $labelClass = $item['labelType'].'Label';
        if ($item['labelType'] == 'none')  {
            $labelClass = 'noLabel';
        }

        $inputClass = $field->type == 'text' ? " textarea" : ""; 

        return array (
            'id' => "{$field->modelName}_{$field->fieldName}_field",
            'class' => "formItem $labelClass $inputClass",
        );
    }

    /**
     * Renders the upper level tag and all sections
     * @return string HTML
     */
    public function renderMain () {
        $html = X2Html::openTag('div', $this->getMainOptions());

        $html .= $this->renderSections ();

        $html .= '</div>';
        return $html;
    }

    protected function renderSections () {
        $html = '';
        foreach($this->layoutData['sections'] as $i => $section) {

            // collapsed settings are determined via index
            $collapsed = 
                (isset($this->_formSettings[$i]) && !$this->_formSettings[$i]) ||
                (!isset ($this->_formSettings[$i]) && isset ($section['collapsedByDefault']) &&
                 $section['collapsedByDefault']);


            // append section
            $html .= $this->renderSection($section, $collapsed);
        }
        return $html;
    }

    /**
     * Renders the tag for all sections and renders the interior rows
     * @return string HTML
     */
    public function renderSection ($section, $collapsed) {
        $section = array_merge (array(
            'title' => '',
            'collapsible' => false,
            'rows' => array()
        ), $section);

        // sections are only collapsed if the section can be collapsed
        $collapsed &= $section['collapsible'];
        $html = X2Html::openTag ('div', $this->getSectionOptions($section, $collapsed));

        $html .= $this->renderSectionHeader ($section);
        $html .= X2Html::openTag ('div', array(
            'class' => 'tableWrapper',
            'style' => $collapsed ? 'display:none' : ''
        ));

        foreach($section['rows'] as $row) {
            $html .= $this->renderRow ($row);
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Renders a section header
     * @param  array $section  Section Options
     */
    public function renderSectionHeader ($section) {
        $html = X2Html::openTag('div', array(
            'class' => 'formSectionHeader'
        ));

        $html .= X2Html::tag('span', array(
            'class' => 'sectionTitle',
            'title' => addslashes($section['title'])
            ), Yii::t(strtolower(Yii::app()->controller->id), $section['title'])
        );

        // Add the collapse Icon
        if ($section['collapsible']) {
            $html .= X2Html::link (
                X2Html::fa('fa-caret-down'),
                'javascript:void(0)',
                array( 
                    'class' => 'formSectionHide'
                )
            );

            $html .= X2Html::link (
                X2Html::fa('fa-caret-right'),
                'javascript:void(0)',
                array( 
                    'class' => 'formSectionShow'
                )
            );
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Renders a .formSectionRow and interior columns
     */
    public function renderRow ($row) {
        $row = array_merge(array(
            'cols' => array(),
        ), $row);

        $html = X2Html::openTag('div', $this->getRowOptions($row));
        foreach($row['cols'] as $col) {
            $html .= $this->renderColumn($col, count($row['cols']));
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Renders .formSectionColumn and interior Items
     * Note that this is a table 
     * @param  array $col   Layout options for columns
     * @param  int $count count of columns in this row
     */
    public function renderColumn ($col, $count) {
        $col = array_merge(array(
            'width' => '',
            'items' => array()
        ), $col);

        $html = X2Html::openTag('table', $this->getColumnOptions($col, $count));
        foreach($col['items'] as &$item) {
            $html .= $this->renderItem ($item);
        }
        $html .= '</table>';

        return $html;
    }


    public function renderItem ($item) {
        $item = array_merge (array(
            'name' => '',
            'labelType' => 'left',
            'readOnly' => '',
            'height' => '',
            'tabindex' => ''
        ), $item);
        if (!is_string ($item['labelType']) ||
            !in_array (strtolower ($item['labelType']), array ('none', 'left', 'top', 'inline'))) {
            $item['labelType'] = 'left';
        }  

        // Return if field was not in fields array 
        $fieldName = preg_replace('/^formItem_/u', '', $item['name']);
        if (!isset($this->_fields[$fieldName])) {
            return;
        } 

        // Return if there is not view permission
        $field = $this->_fields[$fieldName];
        if (in_array($fieldName, $this->suppressFields) || !$this->canView($field)) {
            return;
        }

        if (!$this->canEdit($field)) {
            $item['readOnly'] = true;
        }

        $html = '';
        // Only if it is a top label can we omit this tag
        if ($item['labelType'] != 'top') {
            $html .= X2Html::openTag ('tr', $this->getItemOptions($item, $field));
        }

        // call the rendering function for this label type
        // inline, top, left, none
        $fn = 'render'.$item['labelType'].'label';

        $html .= $this->$fn($item, $field);

        $html .= '</tr>';

        return $html;
    }

    /**
     * Renders the None Label type.
     * Omits the label and increases colspan to to 2
     */
    public function renderNoneLabel ($item, Fields $field) {
        $html  = '';

        $html .= "<td class='attribute' colspan='2'>";
        $html .= $this->renderAttribute ($item, $field);
        $html .= '</td>';

        return $html;
    }

    /**
     * @see  renderNoneLabel
     */
    public function renderInlineLabel ($item, Fields $field) {
        return $this->renderNoneLabel($item, $field);
    }

    /**
     * Renders the top label type. 
     * Renders the label in it's own row, 
     * increases colspan to 2
     * @param  [type] $item  [description]
     * @param  [type] $field [description]
     * @return [type]        [description]
     */
    public function renderTopLabel ($item, Fields $field) {
        $html  = '';
        
        $html .= '<tr class="formItem topLabel">';
        $html .= "<td class='label' colspan='2'>";
        $html .= $this->renderLabel ($field);
        $html .= '</td>';
        $html .= '</tr>';

        $html .= X2Html::openTag ('tr', $this->getItemOptions($item, $field));
        $html .= $this->renderNoneLabel($item, $field);
        
        return $html;
    }

    /**
     * Renders the most common label, LeftLabel
     */
    public function renderLeftLabel ($item, Fields $field) {
        $html  = '';
        
        $html .= "<td class='label' >";
        $html .= $this->renderLabel ($field);
        $html .= '</td>';

        $html .= "<td class='attribute'>";
        $html .= $this->renderAttribute ($item, $field);
        $html .= '</td>';
        
        return $html;
    }

    /**
     * Renders the label of a field
     */
    public function renderLabel ($field) {
        return X2Html::label ($this->model->getAttributeLabel($field->fieldName), false);
    }

    /**
     * Gets the field Permissions into an aray
     * @return array array of field permissions
     */
    public function getFieldPermissions () {
        // Admin can edit all.
        if (Yii::app()->params->isAdmin || empty(Yii::app()->params->roles)) {
            return;
        }

        return $this->model->getFieldPermissions();
    }

    /**
     * Retrieves all Fields for this model
     * @return array array of Fields objects
     */
    public function getFields () {
        $fields = array();
        if (method_exists($this->model, 'getFields')) {
            $fields = $this->model->getFields(true);
        } else {
            foreach (X2Model::model('Fields')->findAllByAttributes(
                    array('modelName' => ucfirst($this->modelName))) as $fieldModel) {
                $fields[$fieldModel->fieldName] = $fieldModel;
            }
        }

        return $fields;
    }

    /**
     * Returns if the form or a specific field can be edited. 
     * If Field is empty, it returns permissions of whole form
     */
    public function canEdit(Fields $field) {
        if(Yii::app()->params->isAdmin){
            return true;
        }

        // If field is read only no one can edit
        if($field->readOnly) {
            return false;
        }
        
        // If permissions aren't set, it can be edited
        if (!isset($this->fieldPermissions[$field->fieldName])) {
            return true;
        }

        // If permissions are set to 'edit', it can be edited
        if ($this->fieldPermissions[$field->fieldName] === 2) {
            return true;
        }

        // Otherwise, it cant be edited (permissions set to 0 or 1)
        return false;
    }

    public function canView($field) {
        if(Yii::app()->params->isAdmin){
            return true;
        }
        
        // If permissions aren't set, it can be viewed
        if (!isset($this->fieldPermissions[$field->fieldName])) {
            return true;
        }

        // If permissions are set to 'view', it can be viewed
        if ($this->fieldPermissions[$field->fieldName] >= 1) {
            return true;
        }

        // Otherwise, it cant be viewed (permissions set to 0 )
        return false;
    }

}

?>
