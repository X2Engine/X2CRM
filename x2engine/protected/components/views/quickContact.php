<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/


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
        <?php
        $i = 0;
        foreach ($formFields as $key=>$val) {
            echo $form->textField($model,$key,array(
                'class'=> (($key === 'firstName' || $key === 'lastName') ?
                    'quick-contact-narrow' : 'quick-contact-wide'),
                'tabindex'=>100 + $i,
                'title'=>$model->getAttributeLabel($key),
                'id'=>'quick_create_'.get_class($model).'_'.$key,
            ));
            ++$i;
        }
        ?>

	</div>
</div>
<?php
echo CHtml::ajaxSubmitButton(
	Yii::t('app','Create'),
	array('/contacts/contacts/quickContact'),
	array('success'=>"function(response) {

            // clear errors
            var quickContactForm = $('#quick-contact-form');
            $(quickContactForm).find ('input').removeClass ('error');
            $(quickContactForm).find ('input').next ('.error-msg').remove ();

			if(response === '') { // success
                auxlib.createReqFeedbackBox ({
                    prevElem: $(quickContactForm).find ('.x2-button'),
                    message: '".Yii::t('app','Contact Saved')."',
                    delay: 3000,
                    classes: ['quick-create-feedback']
                });

                // reset form inputs
                $(quickContactForm).find ('input[type=\"text\"]').val ('');

                // reset placeholder text
                $(quickContactForm).find ('input[type=\"text\"]').trigger ('focus');
                $(quickContactForm).find ('input[type=\"text\"]').trigger ('blur');
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

