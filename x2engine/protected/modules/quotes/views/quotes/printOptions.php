<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/



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
