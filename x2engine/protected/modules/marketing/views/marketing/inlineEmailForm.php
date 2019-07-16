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




 
$staticLinkModel = Contacts::model();
$this->widget('InlineEmailForm', array(
    'attributes' => array(
        'subject' => $model->subject,
        'message' => $model->content,
        'modelName' => 'Contacts',
        'modelId' => null,
        'credId' => $model->sendAs
    ),
    'postReplace' => 1,
    'skipEvent' => 1,
    'template' => Fields::id ($model->template),
    'insertableAttributes' => array(),
    'startHidden' => true,
    'associationType' => 'Contacts',
    'type' => 'testCampaignEmail',
    'specialFields' => 
        '<div class="row">'.
            CHtml::hiddenField ('InlineEmail[campaignId]', $model->id).
            CHtml::label(
                Yii::t('contacts','{module}', array(
                    '{module}' => Modules::displayName(false, "Contacts")
                )),
                'Contacts[name]',
                array('class'=>'x2-email-label')
            ).$this->widget('zii.widgets.jui.CJuiAutoComplete', 
                array(
                    'model' => Contacts::model(), // dummy
                    'attribute' => 'name', // dummy
                    'source' => $linkSource = Yii::app()->controller->createUrl(
                        $staticLinkModel->autoCompleteSource),
                    'options' => array(
                        'minLength' => '1',
                        'select' => 'js:function( event, ui ) {
                            $("#InlineEmail_modelId").val(ui.item.id);
                            $(this).val(ui.item.value);
                            $(this).data ("prev-val", $(this).val ());
                            return false;
                        }',
                        'change' => 'js:function (evt, ui) {
                            if ($(this).data ("prev-val") !== $(this).val ()) {
                                $("#InlineEmail_modelId").val("");
                            }
                            $(this).data ("prev-val", $(this).val ());
                        }',
                        'create' => 'js:function(event, ui) {
                            $(this).data( "uiAutocomplete" )._renderItem = function(ul,item) {
                                $(ul).addClass ("test-email-contact-ai");
                                return $("<li>").data("item.autocomplete",item).append(x2.forms.renderContactLookup(item)).appendTo(ul);
                            };
                        }'
                    ),
                    'htmlOptions' => array(
                        'style'=>'max-width:200px;',
                        'name'=>'InlineEmail[recordName]',
                    )
                ), true).
            X2Html::hint2 (Yii::t(
                'marketing',
                'The {contact} you enter here will be used for variable replacement, ' .
                'i.e. for "John Doe" the token {firstName} will get replaced with ' .
                '"John"', array(
                    '{contact}' => Modules::displayName(false, "Contacts"),
                )
            )).'</div>',
));
?>
