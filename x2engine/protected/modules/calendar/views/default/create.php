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

$this->menu=array(
	array('label'=>Yii::t('calendar','Calendar'), 'url'=>array('index')),
	array('label'=>Yii::t('calendar', 'My Calendar Permissions'), 'url'=>array('myCalendarPermissions')),
	array('label'=>Yii::t('calendar','List'),'url'=>array('list')),
	array('label'=>Yii::t('calendar','Create')),
);
?>
<h2><?php echo Yii::t('calendar','Create Shared Calendar'); ?></h2>


<?php 
$form=$this->beginWidget('CActiveForm', array(
   'id'=>'calendar-form',
   'enableAjaxValidation'=>false,
));

$users = User::getNames();
unset($users['Anyone']);
unset($users['admin']);
	
echo $this->renderPartial('application.components.views._form', 
	array(
		'model'=>$model,
		'form'=>$form,
		'users'=>$users,
		'modelName'=>'calendar',
		'isQuickCreate'=>true, // let us create the CActiveForm in this file
	)
);
?>


<div class="x2-layout form-view" style="margin-bottom: 0;">
	<div class="formSection">
		<div class="formSectionHeader">
			<span class="sectionTitle"><?php echo Yii::t('calendar', 'Google'); ?></span>
		</div>
	</div>
</div>

<div class="form" style="border:1px solid #ccc; border-top: 0; padding: 0; margin-top:-1px; border-radius:0;-webkit-border-radius:0; background:#eee;">
	<table frame="border">
		<td>
			<?php if($googleIntegration) { ?>
				<?php if ($client->getAccessToken()) { ?>
					<?php echo $form->labelEx($model, 'googleCalendar'); ?>
					<?php echo $form->checkbox($model, 'googleCalendar'); ?>
					<?php echo $form->labelEx($model, 'googleCalendarName'); ?>
					<?php echo $form->dropDownList($model, 'googleCalendarId', $googleCalendarList); ?>
					<br />
					<?php echo CHtml::link(Yii::t('calendar', "Don't link to Google Calendar"), $this->createUrl('') . '?unlinkGoogleCalendar'); ?>
				<?php } else { ?>
					<?php echo CHtml::link(Yii::t('calendar', "Link to Google Calendar"), $client->createAuthUrl()); ?>
				<?php } ?>
			<?php } else { ?>
					<?php echo $form->labelEx($model, 'googleCalendar'); ?>
					<?php echo $form->checkbox($model, 'googleCalendar'); ?>
					<?php echo $form->labelEx($model, 'googleFeed'); ?>
					<?php echo $form->textField($model, 'googleFeed', array('size'=>75)); ?>
			<?php } ?>
		</td>
	</table>
</div>

<?php
echo '	<div class="row buttons">'."\n";
echo '		'.CHtml::submitButton(Yii::t('app','Create'),array('class'=>'x2-button','id'=>'save-button','tabindex'=>24))."\n";
echo "	</div>\n";
$this->endWidget();

?>
