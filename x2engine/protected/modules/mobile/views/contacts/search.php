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
