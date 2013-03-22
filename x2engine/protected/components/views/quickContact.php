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

$model = new Contacts;
$attributeLabels = $model->attributeLabels();

$form = $this->beginWidget('CActiveForm', array(
	'id'=>'quick-contact-form',
	'action'=>'',
	'enableAjaxValidation'=>false,
	'method'=>'POST',
));

$model->firstName = X2Model::model('Contacts')->getAttributeLabel('firstName');
$model->lastName = X2Model::model('Contacts')->getAttributeLabel('lastName');
$model->phone = X2Model::model('Contacts')->getAttributeLabel('phone');
$model->email = X2Model::model('Contacts')->getAttributeLabel('email');

?>
<div class="form thin">
	<div class="row inlineLabel">
		<?php echo $form->textField($model,'firstName',array('maxlength'=>40,'tabindex'=>100,'style'=>'color:#aaa;width:68px;','title'=>$model->getAttributeLabel('firstName'))); ?>
		<?php echo $form->error($model,'firstName'); ?>

		<?php echo $form->textField($model,'lastName',array('maxlength'=>40,'tabindex'=>101,'style'=>'color:#aaa;width:68px;','title'=>$model->getAttributeLabel('lastName'))); ?>
		<?php echo $form->error($model,'lastName'); ?>

		<?php echo $form->textField($model,'phone',array('maxlength'=>40,'tabindex'=>102,'style'=>'color:#aaa;width:150px;','title'=>$model->getAttributeLabel('phone'))); ?>
		<?php echo $form->error($model,'phone'); ?>

		<?php echo $form->textField($model,'email',array('maxlength'=>100,'tabindex'=>103,'style'=>'color:#aaa;width:150px;','title'=>$model->getAttributeLabel('email'))); ?>
		<?php echo $form->error($model,'email'); ?>
	</div>
</div>
<?php
echo CHtml::ajaxSubmitButton(
	Yii::t('app','Create'),
	array('/contacts/quickContact'),
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