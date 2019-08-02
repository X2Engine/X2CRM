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
 * Widget for record conversion links which leverage ModelConversionBehavior.
 * Designed to have multiple instances on the same page (which is useful if links for multiple
 * conversion targets are needed).
 */

class X2ModelConversionWidget extends X2Widget {

    /**
     * @var X2Model $model
     */
    public $model; 

    /**
     * @var string $targetClass
     */
    public $targetClass; 

    /**
     * @var string $element
     */
    public $element = '#model-conversion-widget'; 

    public $buttonSelector; 

    /**
     * @var string $JSClass
     */
    public $JSClass = 'X2ModelConversionWidget'; 

    protected $_JSClassParams;
    public function getJSClassParams () {
        if (!isset ($this->_JSClassParams)) {
            $title = X2Model::getModelTitle (get_class ($this->model), true);
            $targetClass = $this->targetClass;
            $behavior = $this->model->asa ('ModelConversionBehavior');
            $conversionFailed = 
                $behavior->conversionFailed && 
                $behavior->errorModel !== null && get_class ($behavior->errorModel) ===
                $this->targetClass;
            $this->_JSClassParams = array_merge (parent::getJSClassParams (), array (
                'buttonSelector' => $this->buttonSelector,
                'translations' => array (
                    'conversionError' => Yii::t('app', '{model} conversion failed.', array (
                        '{model}' => $title,
                    )),
                    'conversionWarning' => Yii::t('app', '{model} Conversion Warning', array (
                        '{model}' => $title,
                    )),
                    'convertAnyway' => Yii::t('app', 'Convert Anyway'),
                    'Cancel' => Yii::t('app', 'Cancel'),
                ),
                'targetClass' => $this->targetClass,
                'modelId' => $this->model->id,
                'conversionFailed' => $conversionFailed,
                'conversionIncompatibilityWarnings' => 
                    $this->model->getConversionIncompatibilityWarnings ($this->targetClass),
                'errorSummary' => 
                    $conversionFailed ? 
                        "<div class='form'>".
                            CHtml::errorSummary (
                                $this->model->asa ('ModelConversionBehavior')->errorModel,
                                Yii::t('app', '{model} conversion failed.', 
                                array (
                                    '{model}' => $title,
                                ))).
                        "</div>" : '',

            ));
        }
        return $this->_JSClassParams;
    }

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages (), array (
                'X2ModelConversionWidgetJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/X2ModelConversionWidget.js',
                    ),
                    'depends' => array (
                        'X2Widget',
                    ),
                ),
            ));
        }
        return $this->_packages;
    }

    public function run () {
        $this->registerPackages ();
        $this->instantiateJSClass ();
        $this->render ('application.components.views._x2ModelConversionWidget', array (
            'sourceModelClass' => get_class ($this->model),
            'targetModelClass' => $this->targetClass,
            'convertMultiple' => false,
        ));
    }

}

?>
