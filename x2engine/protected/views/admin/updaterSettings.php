<div class="span-16">
	<div class="form">
		<h2><?php echo Yii::t('admin', 'Updater Settings'); ?></h2>
		<?php
		$form = $this->beginWidget('CActiveForm', array(
			'id' => 'settings-form',
			'enableAjaxValidation' => false,
				));
		?><?php
		Yii::app()->clientScript->registerScriptfile(Yii::app()->baseUrl.'/js/webtoolkit.sha256.js');
		$updatesForm = new UpdatesForm(
						array(
							'x2_version' => Yii::app()->params['version'],
							'unique_id' => $model->unique_id,
							'formId' => 'settings-form',
							'submitButtonId' => 'save-button',
							'statusId' => 'error-box',
							'themeUrl' => Yii::app()->theme->baseUrl,
							'serverInfo' => True,
							'edition' => $model->edition,
							'titleWrap' => array('<span style="display: block;font-size: 11px;font-weight: bold;">', '</span>'),
							'receiveUpdates' => isset($_POST['receiveUpdates']) ? $_POST['receiveUpdates'] : 0,
						),
						'Yii::t',
						array('install')
		);
		$this->renderPartial('stayUpdated', array('form' => $updatesForm));
		?>
		<input type="hidden" id="adminEmail" name="adminEmail" value="<?php echo $model->emailFromAddr; ?>" />
		<input type="hidden" id="language" name="language" value="<?php echo Yii::app()->language; ?>" />
		<input type="hidden" id="currency" name="currency" value="<?php echo $model->currency; ?>" />
		<input type="hidden" id="timezone" name="timezone" value="<?php echo Yii::app()->params['profile']->timeZone; ?>" />

		<?php
		echo $form->labelEx($model, 'updateInterval');
		echo $form->dropDownList($model, 'updateInterval', array(
			'0' => Yii::t('admin', 'Every Login'),
			'86400' => Yii::t('admin', 'Daily'),
			'604800' => Yii::t('admin', 'Weekly'),
			'2592000' => Yii::t('admin', 'Monthly'),
			'-1' => Yii::t('admin', 'Never'),
		));
		?>
		<div id="error-box" class="form" style="display:none"></div>
		<?php echo CHtml::submitButton(Yii::t('app', 'Save'), array('class' => 'x2-button', 'id' => 'save-button')) . "\n"; ?>
		<?php $this->endWidget(); ?>
	</div><!-- .form -->
</div><!-- .span-16 -->