<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
    

