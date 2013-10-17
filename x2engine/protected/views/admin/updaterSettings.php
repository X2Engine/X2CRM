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
 ?>
<div class="page-title"><h2><?php echo Yii::t('admin', 'Updater Settings'); ?></h2></div>
<div class="span-24">
    <div class="form">
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
                <?php echo Yii::t('admin','Note: to update manually, if not already at the latest version: {download}, then extract its contents into a folder in the web root called "update", then go to {updater}.',array(
                    '{download}' => CHtml::link(Yii::t('admin','download the update package'),array('admin/updater','redirect'=>1)),
                    '{updater}' => CHtml::link(Yii::t('admin','the updater page'),array('admin/updater')),
                )); ?>
    </div><!-- .span-24 -->
</div><!-- .form -->