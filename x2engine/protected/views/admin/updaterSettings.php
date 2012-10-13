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
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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