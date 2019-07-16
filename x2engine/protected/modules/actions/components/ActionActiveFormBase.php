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




abstract class ActionActiveFormBase extends X2ActiveForm {

    public $JSClass = 'ActionActiveFormBase';  

    public function init () {
        $this->action = Yii::app()->createUrl ('/actions/actions/create');
        $this->htmlOptions = X2Html::mergeHtmlOptions ($this->htmlOptions, array (
            'class' => 'action-subtype-form',
        ));
        return parent::init ();
    }

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages (), array(
                'ActionActiveFormBaseCss' => array(
                    'baseUrl' => Yii::app ()->getModule ('actions')->assetsUrl,
                    'css' => array(
                        'css/actionForms.css',
                    ),
                ),
                'ActionActiveFormBaseJS' => array(
                    'baseUrl' => Yii::app ()->getModule ('actions')->assetsUrl,
                    'js' => array(
                        'js/ActionActiveFormBase.js',
                    ),
                    'depends' => array ('X2FormJS'),
                ),
            ));
        }
        return $this->_packages;
    }

    public function getJSClassParams () {
        return array_merge (parent::getJSClassParams (), array (
            'ajaxForm' => true,
        ));
    }

    public function submitButton (array $htmlOptions=array ()) {
        $html = '<div class="row button-row">';
        $htmlOptions = X2Html::mergeHtmlOptions ($htmlOptions, array (
            'class' => 'x2-button',
        ));
        $html .=  CHtml::hiddenField('geoCoords', '');
        $html .=  X2Html::submitButton (Yii::t('actions', 'Save'), $htmlOptions);
        $html .= '</div>';
        return $html;
    }

    public function dateRangeInput (
        CModel $model, $attributeA, $attributeB, array $options = array ()) {

        return $this->widget ('ActiveDateRangeInput', array (
            'model' => $model,
            'startDateAttribute' => $attributeA,
            'endDateAttribute' => $attributeB,
            'namespace' => get_class ($this->formModel).$this->namespace,
            'options' => $options,
        ), true);
    }

    public function renderInput (CModel $model, $attribute, array $htmlOptions=array ()) {
        $action = new Actions;
        $action->setAttributes ($model->getAttributes (), false);
        $defaultOptions = array (
            'id' => $this->resolveId ($attribute),
        );
        $htmlOptions = X2Html::mergeHtmlOptions ($defaultOptions, $htmlOptions);
        return preg_replace (
            '/Actions(\[[^\]]*\])/', get_class ($this->formModel) . '$1', 
            $action->renderInput ($attribute, $htmlOptions));
    }

}

?>
