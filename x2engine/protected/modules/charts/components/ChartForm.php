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






Yii::import ('application.modules.reports.components.views.*');

class ChartForm extends X2ActiveForm {

    /**
     * @var string $JSClass The js Class to be instantiated
     */
    public $JSClass = 'ChartForm'; // ChartForm

    /**
     * @var string View file to render
     */
    public $viewFile; // chartForm

    /**
     * @var CActiveForm Form object to render attributes for 
     */
    public $formModel; // ChartFormModel

    /**
     * @var string type of chart this form is for
     */
    public $chartType; // bar, timeSeries

    /**
     * @var Reports Report object this is tied to 
     */
    public $report;

    /**
     * @var string assetsUrl to retrive assets for
     */
    protected $_assetsUrl;

    /**
     * @see getPackages()
     */
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages (), array (
                'ChartFormJS' => array(
                    'baseUrl' => $this->_assetsUrl,
                    'js' => array(
                        'js/ChartForm.js',
                    ),
                    'css' => array(
                        'css/ChartForm.css',
                    ),
                    'depends' => array ( 'X2FormJS'),
                ),
                // No chart has used JS yet. 
                // $this->JSClass.'JS' => array(
                //     'baseUrl' => $this->_assetsUrl,
                //     'js' => array(
                //         'js/'.$this->JSClass.'.js',
                //     ),
                //     'depends' => array ('ChartFormJS')
                // ),
            ));
        }
        return $this->_packages;
    }


    /**
     * Retrieved a help item from the formModel
     * @param string $field Attribute name to get help from
     * @return string Help item text
     */
    public function getHelp($field) {
        $helpItems = $this->formModel->getHelpItems();
        if (isset($helpItems[$field])) {
            return $helpItems[$field];
        }

        return '';
    }

    /**
     * @see init()
     */
    public function init(){
        $this->_assetsUrl = Yii::app()->getModule('reports')->assetsUrl;

        $this->id = $this->formName;
        // $this->JSClass = $this->formName;
        $this->viewFile = lcfirst($this->formName);

        $formModel = $this->getFormModelName();
        $this->formModel = new $formModel($this->report->type);
        $this->formModel->reportId = $this->report->id;
        parent::init();
    }

    /**
     * @see run()
     */
    public function render($viewFile, $data=null, $return=false) {
        if (!$viewFile) {
            $viewFile = $this->viewFile;
        }

        parent::render($viewFile, $data, $return);

        echo $this->hiddenField($this->formModel, 'reportId');
        echo X2Html::tag('span', 
            array(
                'id' => 'submit-button',
                'class' => 'x2-button'
            )
            , Yii::t('charts', 'submit')) ;

    }

    /**
     * Renders HTMl for this models errors
     * @return string HTML of errors
     */
    public function printErrors() {
        $errors = '';
        $errorsArray = $this->formModel->getErrors();
        foreach ($errorsArray as $value) {
             foreach($value as $error){
                $errors .= X2Html::openTag('div', array(
                        'class' =>'row error' )
                ).$error.'</div>';
             }       
        }

        return $errors;
    }

    /**
     * Helper method to retrieve the formModel name of this instances
     * Chart Type
     * @return string This formModels class name
     */
    public function getFormModelName() {
        return Charts::toFormModelName($this->chartType);
    }

    /**
     * Helper method to retrieve the form name of this instances
     * Chart Type
     * @return string This forms class name
     */
    public function getFormName() {
        return Charts::toFormName($this->chartType);
    }

    /**
     * Generates an axis selector. 
     * An axis selector has a hidden field and a visible text field
     * @param string $field  Field name of formModel to supply input for
     * @param string $axis  What type of selection it is, currently only 'column'
     * @see ChartCreator
     */
    public function axisSelector($field, $axis = 'column') {
        $attr = $this->formModel->attributes;

        $content = X2Html::textField ($field, '', 
            array (
                'class' => 'axis-selector',
                'axis' => $axis,
                'placeholder' => Yii::t('charts','click to select'),
                'readonly' => true,
            )
        );

        $content .= X2Html::fa('fa-times-circle clear-field');

        // Help icon with tips
        $content .= X2Html::hint($this->getHelp($field));

        // Hidden field for the actual attributeName of the report
        $content .= $this->hiddenField($this->formModel, $field, 
            array (
                'class' => 'axis-selector-hidden'
            )
        );
        return $this->row ($content, $field);

    }

    /**
     * Helper function to generate a row with label on the form
     * @param string $content HTML string to be rendered on the row
     * @param field $field to generate a label for 
     * @param array $htmlOptions Array of extra options 
     * @return String string of generated HTML 
     */
    public function row($content, $field=null, $htmlOptions=null) {
        $row = CHtml::openTag('div', array('class' => 'row'));

        if ($field != null) {
            $label = $this->label ($this->formModel, $field);
            $row .= $label;
        }

        $row .= $content;
        $row .= '</div>';
        return $row;
    }

    
}

?>
