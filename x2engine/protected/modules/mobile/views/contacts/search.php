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

?>
<h1><?php echo Yii::t('mobile','Search for a Contact');?></h1>
<?php
$form = $this->beginWidget('CActiveForm', array(
	'id'=>'search-contact-form',
	'action'=>'',
	'enableAjaxValidation'=>false,
	'method'=>'POST',
));
$attributeLabels = $model->attributeLabels();
$model->firstName = $attributeLabels['firstName'];
$model->lastName = $attributeLabels['lastName'];

?>

<div class="form thin">
	<div class="row x2-mobile-narrow-input-row">
		<?php echo $form->textField($model,'firstName',array('maxlength'=>40,'tabindex'=>100,'onfocus'=>'toggleText(this);','onblur'=>'toggleText(this);','class'=>'x2-mobile-narrow-input')); ?>
		<?php echo $form->error($model,'firstName'); ?>

		<?php echo $form->textField($model,'lastName',array('maxlength'=>40,'tabindex'=>101,'onfocus'=>'toggleText(this);','onblur'=>'toggleText(this);','class'=>'x2-mobile-narrow-input')); ?>
		<?php echo $form->error($model,'lastName'); ?>
	</div>
</div>
<?php
echo CHtml::ajaxSubmitButton(
	Yii::t('app','Search'),
	array('/mobile/contacts/viewAll'),
	array('success'=>"function(response) {
			if(response!='') {
				alert('".Yii::t('app','Contact Saved')."');
				$('#quick-contact-form').html(response);
			}
		}",
	),
	array('class'=>'x2-button')
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
