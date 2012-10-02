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

<?php $users = User::getNames(); ?>
<?php $form = $this->beginWidget('CActiveForm', array('id'=>'publisher-form')); ?>

<div id="tabs">
	<ul>
		<?php if($showLogACall) { ?><li><a href="#log-a-call"><?php echo Yii::t('actions','Log A Call'); ?></a></li><?php } ?>
		<?php if($showNewAction) { ?><li><a href="#new-action"><b>+</b><?php echo Yii::t('actions','Action'); ?></a></li><?php } ?>
		<?php if($showNewComment) { ?><li><a href="#new-comment"><b>+</b><?php echo Yii::t('actions','Comment'); ?></a></li><?php } ?>
		<?php if($showNewEvent) { ?><li><a href="#new-event"><b>+</b><?php echo Yii::t('actions','Event'); ?></a></li><?php } ?>
	</ul>
	<div class="form">
		<div class="row">
			<?php echo CHtml::ajaxSubmitButton(Yii::t('app','Save'),
				array('/actions/PublisherCreate'),
				array(
					'beforeSend'=>"function() {
						if($('#Actions_actionDescription').val() == '') {
							alert('". Yii::t('Actions', 'Please enter a description.') ."');
							return false;
						} else {
							// show saving... icon
							\$('.publisher-text').animate({opacity: 0.0});
							\$('#publisher-saving-icon').animate({opacity: 1.0});
						}
						
						return true; // form is sane: submit!
					 }",
					 'success'=>"function() { publisherUpdates(); resetPublisher();
							\$('.publisher-text').animate({opacity: 1.0});
							\$('#publisher-saving-icon').animate({opacity: 0.0}); }",
					'type'=>'POST',
				),
				array('id'=>'save-publisher', 'class'=>'x2-button'));
			?>
			<div class="text-area-wrapper" style="margin-right:75px;">
				<?php echo $form->textArea($model,'actionDescription',array('rows'=>3, 'cols'=>40)); ?>
			</div>
		</div>
		<?php echo CHtml::hiddenField('SelectedTab', ''); // currently selected tab ?>
		
		<?php echo $form->hiddenField($model,'associationType'); ?>
		<?php echo $form->hiddenField($model,'associationId'); ?>

		<div id="action-event-panel">
			<div class="row">
				<div class="cell">
					<?php echo $form->label($model,'dueDate', array('id'=>'due-date-label')); ?>

					<?php // label for New Event ?>
					<?php echo CHtml::label(Yii::t('Actions', 'Start Date'), 'Actions_dueDate', array('id'=>'start-date-label', 'style'=>'display: none;')); ?>

					<?php
					$model->dueDate = $this->controller->formatDateEndOfDay(time());	//default to tomorow for new actions
					Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
					$this->widget('CJuiDateTimePicker',array(
						'model'=>$model, //Model object
						'attribute'=>'dueDate', //attribute name
						'mode'=>'datetime', //use "time","date" or "datetime" (default)
						'options'=>array(
							'dateFormat'=> $this->controller->formatDatePicker('medium'),
							'timeFormat'=> $this->controller->formatTimePicker(),
							'ampm'=> $this->controller->formatAMPM(),
							'changeMonth'=>true,
							'changeYear'=>true
						), // jquery plugin options
						'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
						'htmlOptions'=>array('onClick'=>"$('#ui-datepicker-div').css('z-index', '20');"), // fix datepicker so it's always on top
					));

					echo CHtml::label(Yii::t('Actions', 'End Date'), 'Actions_completeDate', array('id'=>'end-date-label', 'style'=>'display: none;'));

					$model->dueDate = $this->controller->formatDateTime(time());
					Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
					$this->widget('CJuiDateTimePicker', array(
						'model'=>$model, //Model object
						'attribute'=>'completeDate', //attribute name
						'mode'=>'datetime', //use "time","date" or "datetime" (default)
						'options'=>array(
							'dateFormat'=> $this->controller->formatDatePicker('medium'),
							'timeFormat'=> $this->controller->formatTimePicker(),
							'ampm'=> $this->controller->formatAMPM(),
							'changeMonth'=>true,
							'changeYear'=>true,
						), // jquery plugin options
						'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
						'htmlOptions'=>array(
							'onClick'=>"$('#ui-datepicker-div').css('z-index', '20');", // fix datepicker so it's always on top
							'style'=>'display: none;',
							'id'=>'end-date-input',
						), 
					));
					?>
					<?php echo $form->label($model, 'allDay'); ?>
					<?php echo $form->checkBox($model, 'allDay'); ?>
				</div>
				<div class="cell">
					<?php echo $form->label($model,'priority'); ?>
					<?php echo $form->dropDownList($model, 'priority', array(
						'Low'=>Yii::t('actions','Low'),
						'Medium'=>Yii::t('actions','Medium'),
						'High'=>Yii::t('actions','High')));
					?>

					<?php echo $form->label($model, 'color'); ?>
					<?php echo $form->dropDownList($model, 'color', Actions::getColors()); ?>
				</div>
				<?php /* Assinged To */ ?>
				<div class="cell">

					<?php /* Users */ ?>
					<?php echo $form->label($model,'assignedTo'); ?>
					<?php echo $form->dropDownList($model,'assignedTo',$users,array('id'=>'actionsAssignedToDropdown')); ?>

					<?php /* Groups */
						echo "<br />";
						$url=$this->controller->createUrl('/groups/getGroups');
						echo "<label>".Yii::t('app','Group?')."</label>";
						echo CHtml::checkBox('group','',array(
							'id'=>'groupCheckbox',
							'ajax'=>array(
								'type'=>'POST', //request type
								'url'=>$url, //url to call.
								//Style: CController::createUrl('currentController/methodToCall')
								'update'=>'#actionsAssignedToDropdown', //selector to update
								'data'=>'js:{checked: $(this).attr("checked")=="checked"}',
								'complete'=>'function(){
									if($("#groupCheckbox").attr("checked")!="checked"){
										$("#groupCheckbox").attr("checked","checked");
										$("#Actions_visibility option[value=\'2\']").remove();
									} else {
										$("#groupCheckbox").removeAttr("checked");
										$("#Actions_visibility").append(
											$("<option></option>").val("2").html("User\'s Groups")
										);

									}
								}'
							)
						));
					?>
				</div>
				<div class="cell">
					<?php echo $form->label($model,'visibility'); ?>
					<?php $model->visibility = 1; // default visibility = public ?>
					<?php echo $form->dropDownList($model,'visibility',array(0=>Yii::t('actions','Private'), 1=>Yii::t('actions','Public'), 2=>Yii::t('actions', "User's Group"))); ?> 
				</div>
				<div class="cell">
					<?php echo $form->label($model,'reminder'); ?>
					<?php echo $form->dropDownList($model,'reminder',array('No'=>Yii::t('actions','No'),'Yes'=>Yii::t('actions','Yes'))); ?> 
				</div>
			</div>
		</div>
		<div id="log-a-call">
		</div>
		<div id="new-action">
		</div>
		<div id="new-comment">
		</div>
		<div id="new-event">
		</div>
	</div>
</div>

<?php 

$this->endWidget();

// save default values of fields for when the publisher is submitted and then reset
Yii::app()->clientScript->registerScript('defaultValues',"
$(function() {
	
	// turn on jquery taps for the publisher
	$('#tabs').tabs({
		select: function(event, ui) { tabSelected(event, ui); }, 
	}); 
	
	if($('#tabs .ui-tabs-selected').length !== 0) { // if publisher is present (prevents a javascript error if publisher is not present)
		var selected = $('#tabs .ui-tabs-selected').children().attr('href').substring(1);
		$('#SelectedTab').val(selected); // save the selected tab as POST data
		if(selected == 'log-a-call' || selected == 'new-comment') {
			$('#action-event-panel').css('display', 'none');
		}
	}

	$('#publisher-form select, #publisher-form input[type=text], #publisher-form textarea').each(function(i) {
		$(this).data('defaultValue', $(this).val());
	});
	
	$('#publisher-form input[type=checkbox]').each(function(i) {
		$(this).data('defaultValue', $(this).is(':checked'));
	});
	
	// highlight save button when something is edited in the publisher
	$('#publisher-form input, #publisher-form select, #publisher-form textarea').focus(function(){
		$('#save-publisher').addClass('highlight');
		// $('#publisher-form textarea').animate({'height':80},300);
		$('#publisher-form textarea').height(80);
		
		
		$(document).unbind('click.publisher').bind('click.publisher',function(e) {
			if(!$(e.target).parents().is('#publisher-form, .ui-datepicker')
				&& $('#publisher-form textarea').val()=='') {
				$('#save-publisher').removeClass('highlight');
				$('#publisher-form textarea').animate({'height':22},300);
			}
		});
		
	});

	
	// highlight save button when user starts typing in Description
	$('#Actions_actionDescription').keydown(function() {
		$('#save-publisher').addClass('highlight');
	});
	
	// position the saving icon for the publisher (which starts invisible)
	// var publisherLabelCenter = parseInt($('.publisher-label').css('width'), 10)/2;
	// var halfIconWidth = parseInt($('#publisher-saving-icon').css('width'), 10)/2;
	// var iconLeft = publisherLabelCenter - halfIconWidth;
	// $('#publisher-saving-icon').css('left', iconLeft + 'px');
	
});");


 ?>
