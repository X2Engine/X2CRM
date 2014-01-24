<?php

/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
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

/**
 * Provides an inline form for sending email from a view page.
 *
 * @property X2Model $targetModel The model of the form.
 * @property integer $template The default template to use when opening the form.
 * @property string $templateType The class of template. Different templates are meant for different models and scenarios.
 * @property InlineEmail $model The form model for email handling and delivery
 * @property array $insertableAttributes Can be manually set to specify insertable attributes for this scenario. Otherwise, {@link InlineEmail::getInsertableAtributes()} will be used.
 * @property string $type Type; null is default (plain email). Specifies which list of templates to fetch.
 * @property bool $startHidden If true, it will not be visible on page load
 * @property string $specialFields Extra HTML to render inside the form.
 * @property bool $postReplace If true, variable replacement will be run on non-template user input.
 * @property bool $skipEvent If true, no event record will be created.
 * @package X2CRM.components
 */
class InlineEmailForm extends X2Widget {

    public $attributes;

    public $template = null;

    public $templateType = 'email';

    public $model;

    public $targetModel;

    public $contactFlag = 1;

    public $insertableAttributes;

    public $errors = array();

    public $startHidden = false;

    public $specialFields = '';

    public $postReplace = 0;

    public $skipEvent = 0;

    public function init(){

        // Prepare the model for initially displayed input:
        $this->model = new InlineEmail();
        if(isset($this->targetModel))
            $this->model->targetModel = $this->targetModel;
        // Bring in attributes set in the configuration:
        $this->model->attributes = $this->attributes;
        if(empty($this->template)){
            if(empty($this->model->message))
                $this->model->message = InlineEmail::emptyBody();
            $this->model->insertSignature();
        }else{
            // Fill in the body with a template:
            $this->model->scenario = 'template';
            $this->model->prepareBody();
        }

        // If insertable attributes aren't set, use the inline email model's getInsertableAttributes() method to generate them.
        if((bool) $this->model->targetModel && !isset($this->insertableAttributes)){
            $this->insertableAttributes = $this->model->insertableAttributes;
        }

        // Load resources:
        Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/ckeditor/ckeditor.js');
        Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/ckeditor/adapters/jquery.js');
        Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/emailEditor.js');
        Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/inlineEmailForm.js', CClientScript::POS_BEGIN);
        if(!empty($this->insertableAttributes)){
            Yii::app()->clientScript->registerScript('setInsertableAttributes', 'x2.insertableAttributes = '.CJSON::encode($this->insertableAttributes).';', CClientScript::POS_HEAD);
        }
        Yii::app()->clientScript->registerScript('storeOriginalInlineEmailMessage', 'x2.inlineEmailOriginalBody = $("#email-message").val();', CClientScript::POS_READY); //'.CJSON::encode($this->model->message).';',CClientScript::POS_READY);
        Yii::app()->clientScript->registerScript('toggleEmailForm', ($this->startHidden ? "window.hideInlineEmail = true;\n" : "window.hideInlineEmail = false;\n"), CClientScript::POS_HEAD);

        parent::init();
    }

    public function run(){
        // First get user credentials:
        $this->render('application.components.views.inlineEmailForm', array(
            'type' => $this->templateType,
            'specialFields' => $this->specialFields,
        ));
    }

}
