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

$menuItems = array(
    array('label' => Yii::t('app', 'Main Menu'), 'url' => array('/mobile/site/home')),
);

$this->widget('MenuList', array(
    'id' => 'main-menu',
    'items' => $menuItems
));

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
        !in_array (
            $field->fieldName, 
            array ('firstName', 'lastName', 'phone', 'email', 'visibility'))) {
        $formFields[$field->fieldName] = 
            X2Model::model('Contacts')->getAttributeLabel($field->fieldName);
    }
}

foreach ($formFields as $key=>$val) {
    if ($model->getAttribute ($key) === null)  {

        // set placeholder text
        $model->setAttribute ($key, $val);
    }
}

$noErrors = count ($model->getErrors ()) === 0;

$form = $this->beginWidget('CActiveForm', array(
	'id'=>'quick-contact-form',
    'action'=>'',
	'enableAjaxValidation'=>false,
	'method'=>'POST',
));

?>

<div class="form thin">
    <div class="row x2-mobile-narrow-input-row">
        <?php 
        $i = 0;
        foreach ($formFields as $key=>$val) {
            echo '<div class="input-error-container">';
            echo $form->textField($model,$key,array(
                'class'=> 'x2-mobile-narrow-input',
                'tabindex'=>100 + $i,
                'onfocus'=>'toggleText(this);',
                'onblur'=>'toggleText(this);',
                'title'=>$model->getAttributeLabel($key)
            )); 
            echo $form->error($model,$key);
            if (!$noErrors && !$form->error($model, $key))
                echo '<div class="err-msg-placeholder"></div>';
            echo '</div>';
            echo ' ';
            ++$i;
        }
        echo '<div style="clear: left"></div>';
        ?>
	</div>
</div>

<?php
echo CHtml::submitButton(
    Yii::t('app','Create'),
	array(
        'class'=>'x2-button'
    )
);
$this->endWidget();
?>

<script>

    function toggleText(field) {
        if (field.defaultValue==field.value) {
            field.value = ''
            field.style.color = 'black'
        } else if (field.value=='') {
            field.value = field.defaultValue
            field.style.color = '#aaa'
        }
    }
    
</script>
    

