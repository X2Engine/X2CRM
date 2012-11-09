<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
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
?>

<?php
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'contacts-form',
	'enableAjaxValidation'=>false,
));
?>
	<em><?php echo Yii::t('app','Fields with <span class="required">*</span> are required.'); ?></em>
	<?php
	// $isQuickCreate = true;	//signal subforms not to call beginWidget()/endWidget(), create submit buttons, etc
	//var_dump(scandir(''));
	//include('../x2engine/protected/views/contacts/_form.php');
	echo $this->renderPartial('application.components.views._form',
	array(
		'model'=>$contactModel,
		'modelName'=>'contacts',
		'users'=>$users,
		'isQuickCreate'=>true,
		'form'=>$form,
	));
	?>
		<?php echo $form->hiddenField($actionModel,'associationId'); ?>

		
		
	<h2><?php echo Yii::t('actions','Action'); ?></h2>


	<?php echo $form->errorSummary($actionModel); ?>
	
<table class="details">
	<tr>
		<td class="label" width="20%"><?php echo $form->labelEx($actionModel,'actionDescription'); ?></td>
		<td colspan="3" class="text-field"><div class="spacer"></div>
			<?php echo $form->textArea($actionModel,'actionDescription',array('rows'=>6, 'cols'=>50,'tabindex'=>23,'style'=>'width:460px;')); ?>
		</td>
	</tr>
	<tr>
		<td class="label" width="20%"><?php echo $form->labelEx($actionModel,'dueDate'); ?></td>
		<td width="30%">
		<?php
		if ($actionModel->isNewRecord)
			$actionModel->dueDate = date('Y-m-d',time()).' 23:59';	//default to tomorow for new actions
		else
			$actionModel->dueDate = date('Y-m-d H:i',$actionModel->dueDate);	//format date from DATETIME

		Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
		$this->widget('CJuiDateTimePicker',array(
			'model'=>$actionModel, //Model object
			'attribute'=>'dueDate', //attribute name
			'mode'=>'datetime', //use "time","date" or "datetime" (default)
			'options'=>array(
				'dateFormat'=>'yy-mm-dd',
			), // jquery plugin options
			'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
			'htmlOptions' => array('tabindex'=>24)
		));
		?>
		<?php echo $form->error($actionModel,'dueDate'); ?>
		</td>
		<td class="label" width="10%"><?php echo $form->labelEx($actionModel,'reminder'); ?></td>
		<td><?php echo $form->dropDownList($actionModel,'reminder',array('No'=>Yii::t('actions','No'),'Yes'=>Yii::t('actions','Yes')),array('tabindex'=>25)); ?> </td>
	</tr>
</table>

	<?php
	
	// echo $this->renderPartial('_form',
	// array(
		// 'actionModel'=>$actionModel,
		// 'users'=>$users,
		// 'isQuickCreate'=>true,
		// 'form'=>$form,
	// ));
	?>

	<div class="row buttons">
		<?php
		echo CHtml::htmlButton($actionModel->isNewRecord ? Yii::t('app','Submit Contact + Action'):Yii::t('app','Save'),
			array('type'=>'submit',
				'class'=>'x2-button',
				'id'=>'save-button1',
				'name'=>'submit',
				'value'=>'action',
				'tabindex'=>26
			)
		); ?>

		<?php
			echo CHtml::htmlButton(Yii::t('app','Submit Contact + Comment'),
				array(
					'type'=>'submit',
					'class'=>'x2-button',
					'id'=>'save-button2',
					'name'=>'submit',
					'value'=>'comment',
					'tabindex'=>27
				)
			); ?>
		<?php // echo CHtml::submitButton(Yii::t('app','Submit'),array('class'=>'x2-button','tabindex'=>25)); ?>
	</div>

<?php $this->endWidget(); ?>

<!-- form -->