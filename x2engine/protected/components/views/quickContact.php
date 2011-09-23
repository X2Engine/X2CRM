<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/x2forms.js');

$prof=Profile::model()->findByPk(Yii::app()->user->getId());
// if($prof->widgets=='1'){
// Yii::app()->clientScript->registerScript('updateChat', "

// ",CClientScript::POS_HEAD);
// }


$model = new ContactChild;
$attributeLabels = ContactChild::attributeLabels();

$form = $this->beginWidget('CActiveForm', array(
	'id'=>'quick-contact-form',
	'action'=>'',
	'enableAjaxValidation'=>false,
	'method'=>'POST',
));

$model->firstName = $attributeLabels['firstName'];
$model->lastName = $attributeLabels['lastName'];
$model->phone = $attributeLabels['phone'];
$model->email = $attributeLabels['email'];

?>
<div class="form thin">
	<div class="row">
		<?php echo $form->textField($model,'firstName',array('maxlength'=>40,'tabindex'=>100,'onfocus'=>'toggleText(this);','onblur'=>'toggleText(this);','style'=>'color:#aaa;width:68px;')); ?>
		<?php echo $form->error($model,'firstName'); ?>

		<?php echo $form->textField($model,'lastName',array('maxlength'=>40,'tabindex'=>101,'onfocus'=>'toggleText(this);','onblur'=>'toggleText(this);','style'=>'color:#aaa;width:68px;')); ?>
		<?php echo $form->error($model,'lastName'); ?>

		<?php echo $form->textField($model,'phone',array('maxlength'=>40,'tabindex'=>102,'onfocus'=>'toggleText(this);','onblur'=>'toggleText(this);','style'=>'color:#aaa;width:150px;')); ?>
		<?php echo $form->error($model,'phone'); ?>

		<?php echo $form->textField($model,'email',array('maxlength'=>100,'tabindex'=>103,'onfocus'=>'toggleText(this);','onblur'=>'toggleText(this);','style'=>'color:#aaa;width:150px;')); ?>
		<?php echo $form->error($model,'email'); ?>
	</div>
</div>
<?php
echo CHtml::ajaxSubmitButton(
	Yii::t('app','Create'),
	array('contacts/quickContact'),
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