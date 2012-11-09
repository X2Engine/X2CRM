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
//
?>

<?php
 $form=$this->beginWidget('CActiveForm', array(
   'id'=>'quotes-form',
   'enableAjaxValidation'=>false,
));
?>

<span style="font-weight:bold; font-size: 2em;"><?php echo Yii::t('quotes', 'Print Options'); ?></span><br /><br />

<?php echo Yii::t('quotes', 'Include Logo'); ?>
<?php echo CHtml::checkBox('includeLogo', false); ?>
<br />
<?php echo CHtml::image(Yii::app()->baseUrl . '/' . Yii::app()->params['logo'], 'No Logo Found'); ?>

<table style="width:100%;">
	<tbody>
		<tr>
			<td><b><?php echo $model->name; ?></b></td>
			<td style="text-align:right;font-weight:bold;">
				<span><?Php echo Yii::t('quotes', 'Quote'); ?> # <?php echo $model->id; ?></span><br />
				<span><?php echo Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('long'), time()); ?></span>
			</td>
		</tr>
	</tbody>
</table><br />

<?php echo $model->productTable(true);

$defaultIncludeNotes = !empty($model->description);
?>

<div>
	<span style="font-weight: bold; font-size: 0.8em;"><?php echo $form->labelEx($model,'description'); ?></span>
	<?php echo CHtml::checkBox('includeNotes', $defaultIncludeNotes); ?>
	<br />
	<?php echo $form->textArea($model,'description',array('rows'=>6, 'cols'=>50)); ?>
</div>

<br />

<?php echo CHtml::submitButton(Yii::t('quotes','Print'), array('class'=>'x2-button')); ?>

<?php $this->endWidget(); ?>