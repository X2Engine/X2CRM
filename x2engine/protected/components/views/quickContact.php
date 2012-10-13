<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

$model = new Contacts;
$attributeLabels = $model->attributeLabels();

$form = $this->beginWidget('CActiveForm', array(
	'id'=>'quick-contact-form',
	'action'=>'',
	'enableAjaxValidation'=>false,
	'method'=>'POST',
));

$model->firstName = CActiveRecord::model('Contacts')->getAttributeLabel('firstName');
$model->lastName = CActiveRecord::model('Contacts')->getAttributeLabel('lastName');
$model->phone = CActiveRecord::model('Contacts')->getAttributeLabel('phone');
$model->email = CActiveRecord::model('Contacts')->getAttributeLabel('email');

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