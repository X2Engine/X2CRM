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





Yii::app()->clientScript->registerCss ('quickCreateCss', "
    #quick-contact-form .quick-contact-narrow {
        color: #aaa;
        width: 68px;
        margin-right: 3px;
    }
    #quick-contact-form .quick-contact-wide {
        color: #aaa;
        width: 150px;
        margin-right: 3px;
    }
    .quick-create-feedback {
        margin-top: 17px;
    }
    #quick-contact-form label {
        color: #aaa;
        float: left;
        margin-top: 3px;
        margin-right: 3px;
    }
");

// default fields
$formFields = array (
    'firstName' => X2Model::model('Contacts')->getAttributeLabel('firstName'),
    'lastName' => X2Model::model('Contacts')->getAttributeLabel('lastName'),
    'phone' => X2Model::model('Contacts')->getAttributeLabel('phone'),
    'email' => X2Model::model('Contacts')->getAttributeLabel('email')
);

// get required fields not in default set
foreach ($model->getFields () as $field) {
    if ($field->required && 
        !in_array ($field->fieldName, 
            array ('firstName', 'lastName', 'phone', 'email', 'visibility'))) {
        $formFields[$field->fieldName] = 
            X2Model::model('Contacts')->getAttributeLabel($field->fieldName);
    }
}

// set placeholder text
$model->setAttributes ($formFields, false);

$form = $this->beginWidget('CActiveForm', array(
	'id'=>'quick-contact-form',
	'enableAjaxValidation'=>false,
	'method'=>'POST',
));

?>

<div class="form thin">
	<div id='quick-contact-form-contents-container' class="row inlineLabel">
        <?php $this->renderContactFields ($model); ?>
	</div>
</div>
<?php
Yii::app()->clientScript->registerScript('blur-datepickers', '
    $("#quick-contact-form").find("input.hasDatepicker").val("").blur();
');

echo CHtml::ajaxSubmitButton(
	Yii::t('app','Create'),
	array('/contacts/contacts/quickContact'),
	array('success'=>"function(response) {

            // clear errors
            var quickContactForm = $('#quick-contact-form');
            $(quickContactForm).find ('input').removeClass ('error');
            $(quickContactForm).find ('input').next ('.error-msg').remove ();
            $(quickContactForm).find ('.star-rating-control').parent('span').next ('.error-msg').remove ();

			if(response === '') { // success
                auxlib.createReqFeedbackBox ({
                    prevElem: $(quickContactForm).find ('.x2-button'),
                    message: '".Yii::t('app','Contact Saved')."',
                    delay: 3000,
                    classes: ['quick-create-feedback']
                });

                var textFields = $(quickContactForm).find ('input[type=\"text\"]');
                var textFieldsWithoutDatepicker = textFields.not('.hasDatepicker');

                // reset form inputs
                textFields.val ('');
                $(quickContactForm).find ('.star-rating-control').rating('select');
                $(quickContactForm).find ('input[type=\"checkbox\"]').removeAttr('checked');

                // reset placeholder text
                $(textFieldsWithoutDatepicker).focus ();
                $(textFields).blur ();
			} else { // failure, display errors
                var errors = JSON.parse (response);
                var selector;
                for (var i in errors) {
                    selector = '#quick_create_Contacts_' + i;
                    $(selector).after ($('<div>', {
                        'class': 'error-msg',
                        text: errors[i]
                    }));
                    $(selector).addClass ('error');
                }
            }
		}",
	),
	array('class'=>'x2-button left')
);
$this->endWidget();
?>

