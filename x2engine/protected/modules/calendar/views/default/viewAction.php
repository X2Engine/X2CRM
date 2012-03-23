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

<?php
$users = UserChild::getNames();
$form=$this->beginWidget('CActiveForm', array(
    'enableAjaxValidation'=>false,
));
?>

<style type="text/css">

.dialog-label {
	font-weight: bold;
	display: block;
}

.cell {
	float: left;
}

.dialog-cell {
	padding: 5px;
}

</style>

<div class="row">
	<div class="cell dialog-cell" style="float: none;">
		<?php echo $model->actionDescription; ?>
	</div>
</div>

<div class="row">
	<div class="cell dialog-cell">
		<?php echo $form->label($model,($isEvent?'startDate':'dueDate'), array('class'=>'dialog-label'));
		echo $this->formatDateTime($model->dueDate);	//format date from DATETIME

		if($isEvent) {
			echo $form->label($model, 'endDate', array('class'=>'dialog-label'));
			echo $this->formatDateTime($model->completeDate);	//format date from DATETIME
		}

		?>
		

		<?php echo $form->label($model, 'allDay', array('class'=>'dialog-label')); ?>
		<?php echo $form->checkBox($model, 'allDay', array('onChange'=>'giveSaveButtonFocus();', 'disabled'=>'disabled')); ?>
	</div>
	
	<div class="cell dialog-cell">
		<?php echo $form->label($model,'priority', array('class'=>'dialog-label')); ?>
		<?php 
		$priorityArray = array(
				'Low'=>Yii::t('actions','Low'),
				'Medium'=>Yii::t('actions','Medium'),
				'High'=>Yii::t('actions','High')
			);
		echo $priorityArray[$model->priority]; ?>
		<?php /*
		<?php echo $form->dropDownList($model,'priority',
			array(
				'Low'=>Yii::t('actions','Low'),
				'Medium'=>Yii::t('actions','Medium'),
				'High'=>Yii::t('actions','High')
			),
			array('onChange'=>'giveSaveButtonFocus();')); */
		?>
	</div>
	<div class="cell dialog-cell">
		<?php
		if($model->assignedTo == null && is_numeric($model->calendarId)) { // assigned to calendar instead of user?
		    $model->assignedTo = $model->calendarId;
		}
		?>
		<?php echo $form->label($model,'assignedTo', array('class'=>'dialog-label')); ?>
		<?php
		$assignedToArray = $users + X2Calendar::getViewableCalendarNames();
		echo $assignedToArray[$model->assignedTo];
		?>
</div>

<?php $this->endWidget(); ?>
