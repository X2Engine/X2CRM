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
//
?>
<html>

  <head>
    <script src='<?php echo Yii::app()->baseUrl . '/framework/web/js/source/jquery.min.js'; ?>'></script>
    <script src='<?php echo Yii::app()->baseUrl . '/framework/web/js/source/jui/js/jquery-ui.min.js'; ?>'></script>
	<meta charset="UTF-8">
  </head>

  <header>
		
		<title><?php $type = $model->type == 'invoice' ? Yii::t('quotes', 'Invoice:') : Yii::t('quotes', 'Quote:');
echo "$type #{$model->id}"; ?></title>
	</header>
	<body>
		<?php
		$form = $this->beginWidget('CActiveForm', array(
			'id' => 'quotes-form',
			'enableAjaxValidation' => false,
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
						<span><?php echo $type; ?> &#35; <?php echo $model->id; ?></span><br />
						<span><?php echo Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('long'), time()); ?></span>
					</td>
				</tr>
			</tbody>
		</table><br />

		<?php
		//echo $model->productTable(true);


    

		$defaultIncludeNotes = !empty($model->description);
		?>

		<div>
			<span style="font-weight: bold; font-size: 0.8em;"><?php echo $form->labelEx($model, 'description'); ?></span>
			<?php echo CHtml::checkBox('includeNotes', $defaultIncludeNotes); ?>
			<br />
			<?php echo $form->textArea($model, 'description', array('rows' => 6, 'cols' => 50)); ?>
		</div>

		<br />

		<?php echo CHtml::submitButton(Yii::t('quotes', 'Print'), array('class' => 'x2-button')); ?>

		<?php $this->endWidget(); ?>


	</body>
</html>
