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
$this->menu=array(
	array('label'=>Yii::t('calendar','Calendar')),
	array('label'=>Yii::t('calendar', 'My Calendar Permissions'), 'url'=>array('myCalendarPermissions')),
	array('label'=>Yii::t('calendar', 'List'),'url'=>array('list')),
	array('label'=>Yii::t('calendar','Create'), 'url'=>array('create')),
);
?>
<div id="calendar">

</div>
<?php

// register fullcalendar css and js
Yii::app()->clientScript->registerCssFile(Yii::app()->theme->getBaseUrl() .'/css/fullcalendar/fullcalendar.css');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/fullcalendar/fullcalendar.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/fullcalendar/gcal.js');

// register jquery timepicker css and js
// (used inside js dialog because CJuiDateTimePicker is a php library that won't work inside a js dialog)
//Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl() .'/protected/extensions/CJuiDateTimePicker/assets/jquery-ui-timepicker-addon.css');
//Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/protected/extensions/CJuiDateTimePicker/assets/jquery-ui-timepicker-addon.js');



$this->calendarUsers = Calendar::getViewableUserCalendarNames();
$this->sharedCalendars = Calendar::getViewableCalendarNames();
$this->googleCalendars = Calendar::getViewableGoogleCalendarNames();
$this->calendarFilter = Calendar::getCalendarFilters();

// urls for ajax (and other javascript) calls
$jsonFeed = $this->createUrl('jsonFeed'); // feed to get actions from users
$jsonFeedShared = $this->createUrl('jsonFeedShared'); // feed to get actions from shared calendars
$currentUserFeed = $this->createUrl('jsonFeed', array('user'=>Yii::app()->user->name)); // add current user actions to calendar
$anyoneUserFeed = $this->createUrl('jsonFeed', array('user'=>'Anyone')); // add Anyone actions to calendar
$moveAction = $this->createUrl('moveAction');
$resizeAction = $this->createUrl('resizeAction');
$saveAction = $this->createUrl('actions/quickUpdate');
$completeAction = $this->createUrl('completeAction');
$uncompleteAction = $this->createUrl('uncompleteAction');
$deleteAction = $this->createUrl('deleteAction');
$saveCheckedCalendar = $this->createUrl('saveCheckedCalendar');
$saveCheckedCalendarFilter = $this->createUrl('saveCheckedCalendarFilter');

$user = UserChild::model()->findByPk(Yii::app()->user->getId());
$showCalendars = json_decode($user->showCalendars, true);
$userCalendars = $showCalendars['userCalendars'];
$sharedCalendars = $showCalendars['sharedCalendars'];
$googleCalendars = $showCalendars['googleCalendars'];

$editableUserCalendars = Calendar::getEditableUserCalendarNames();
$checkedUserCalendars = '';
foreach($userCalendars as $user) {
	if(isset($this->calendarUsers[$user])) {
		$userCalendarFeed = $this->createUrl('jsonFeed', array('user'=>$user));
		if(isset($editableUserCalendars[$user]))
			$editable = 'true';
		else
			$editable = 'false';
		$checkedUserCalendars .= "
	$('#calendar').fullCalendar('addEventSource', 
		{
			url: '$userCalendarFeed',
			type: 'POST',
			editable: $editable,
		}
	);
";
	}
}

$editableCalendars = Calendar::getEditableCalendarNames();
$checkedSharedCalendars = '';
foreach($sharedCalendars as $calendarId) {
	if(isset($this->sharedCalendars[$calendarId])) {
		$calendarFeed = $this->createUrl('jsonFeedShared', array('calendarId'=>$calendarId));
		if(isset($editableCalendars[$calendarId]))
			$editable = 'true';
		else
			$editable = 'false';
		$checkedSharedCalendars .= "
	$('#calendar').fullCalendar('addEventSource', 
		{
			url: '$calendarFeed',
			type: 'POST',
			editable: $editable,
		}
	);
";
	}
}

$checkedGoogleCalendars = '';
foreach($googleCalendars as $calendarId) {
	$calendar = Calendar::model()->findByPk($calendarId);
	if(isset($this->googleCalendars[$calendarId])) {
		$checkedGoogleCalendars .= "
	$('#calendar').fullCalendar('addEventSource', 
		{
			url: '{$calendar->googleFeed}',
			type: 'POST',
			editable: false,
			type: 'google',
		}
	);
";
	}
}


// Yii::app()->clientScript->registerScript('initCalendar', "
?>
<script type="text/javascript">
$(function() {
	$('#calendar').fullCalendar({
		weekMode: 'liquid',
		header: {
			left: 'title',
			center: '',
			right: 'month basicWeek agendaDay prev,next'
		},
		eventRender: function(event, element, view) {
			$(element).css('font-size', '0.8em');
			if(view.name == 'month' || view.name == 'basicWeek')
				$(element).find('.fc-event-time').remove();
			if(event.associationType == 'contacts')
				element.attr('title', event.associationName);
		},
		dayClick: function(date, allDay, jsEvent, view) {
			if(view.name == 'month') {
				$('#calendar').fullCalendar('changeView', 'agendaDay');
				$('#calendar').fullCalendar('gotoDate', date);
			}
		},
		eventDrop: function(event, dayDelta, minuteDelta, allDay, revertFunc) {
			$.post('<?php echo $moveAction; ?>', {id: event.id, dayChange: dayDelta, minuteChange: minuteDelta, isAllDay: allDay});
		},
		eventResize: function(event, dayDelta, minuteDelta, revertFunc) {			
			$.post('<?php echo $resizeAction; ?>', {id: event.id, dayChange: dayDelta, minuteChange: minuteDelta});
		},
		eventClick: function(event) { // event was clicked
		
			if(event.source.type == 'google')
				return;
			var viewAction = $('<div></div>', {id: 'dialog-content'});  // dialog box (opened at the end of this function)
			var focusButton = 'Close';
			var dialogWidth = 390;
			
			var boxButtons =  [ // buttons on bottom of dialog
			    {
			    	text: '<?php echo Yii::t('app', 'Close'); ?>', 
			    	click: function() {
			    		$(this).dialog('close');
			    		
			    		// remove unique id's so we can open the dialog more then once
						cleanUpDialog();
			    	}
			    },
			];
			
			if(event.source.editable) {
				/*
				var eventDescription = $('<textarea></textarea>', {
					rows: 3,
					style: 'width: 95%;',
				}).val(event.description)
					.change(function() {
						$('.ui-dialog-buttonpane').find('button')
							.css('background', '')
							.css('color', '');
						$('.ui-dialog-buttonpane').find('button:contains(\"Save\")')
			    			.css('background', '#579100')
			    			.css('color', 'white')
			    			.focus();
					});
				var dueDate = $('<input>', {
					id: 'dialog_dueDate',
					name: 'dueDate',
					type: 'text',
				});
				var boxContent = eventDescription;
				viewAction.html(boxContent);
				viewAction.append($('<label style=\"font-weight: bold;\"><?php echo Yii::t('actions', 'Due Date'); ?></label>'));
				viewAction.append(dueDate);
				$(dueDate).datetimepicker($.extend(
					{showMonthAfterYear:false},
					$.datepicker.regional['<?php echo (Yii::app()->language == 'en'? '':Yii::app()->getLanguage()); ?>'],
					{'dateFormat': '<?php echo $this->formatDatePicker('medium'); ?>'}
				));
				*/
				dialogWidth = 600;
				$.post('editAction', {'ActionId': event.id, 'IsEvent': event.type=='event'}, function(data) { 
					$(viewAction).append(data);
					viewAction.dialog('open'); //open dialog after its filled with action/event
				});
				boxButtons.unshift({
					text: '<?php echo Yii::t('app', 'Save'); ?>', // delete event
					click: function() {
	//					var description = $(eventDescription).val();
					    $.post('<?php echo $saveAction; ?>?id=' + event.id, $(viewAction).find('form').serialize(), function() {$('#calendar').fullCalendar('refetchEvents');}); // delete event from database
	//				    event.title = description.substring(0, 30);
	//				    event.description = description;
	//				    $('#calendar').fullCalendar('updateEvent', event);
					    $(this).dialog('close');
						cleanUpDialog();
					},
				});
				boxButtons.unshift({
					text: '<?php echo Yii::t('app', 'Delete'); ?>', // delete event
					click: function() {
						if(confirm('Are you sure you want to delete this action?')) {
					    	$.post('<?php echo $deleteAction; ?>', {id: event.id}); // delete event from database
					    	$('#calendar').fullCalendar('removeEvents', event.id);
					    	$(this).dialog('close');
							cleanUpDialog();
					    }
					},
				});
			} else { // non-editable event/action
				$.post('viewAction', {'ActionId': event.id, 'IsEvent': event.type=='event'}, function(data) { 
					$(viewAction).append(data);
					viewAction.dialog('open'); //open dialog after its filled with action/event
				});
			}
			
			if(event.associationType == 'calendar') { // calendar event clicked
				var boxTitle = 'Event';
			} else if(event.associationType == 'contacts') { // action associated with a contact clicked
				if(event.type == 'event')
					boxTitle = 'Contact Event';
				else
					boxTitle = 'Contact Action';
				viewAction.prepend('<b><a href="' + event.associationUrl + '">' + event.associationName + '</a></b><br />');
				boxButtons.unshift({  //prepend button
					text: '<?php echo Yii::t('contacts', 'View Contact'); ?>',
					click: function() {
						window.location = event.associationUrl;
					},
				});
				if(event.source.editable && event.type != 'event') {
					if(event.complete == 'Yes') {
						boxButtons.unshift({  // prepend button
							text: '<?php echo Yii::t('actions', 'Uncomplete'); ?>',
							click: function() {
							    $.post('<?php echo $uncompleteAction; ?>', {id: event.id});
							    event.complete = 'No';
							    $(this).dialog('close');
								cleanUpDialog();
							},
						});
					} else {
						boxButtons.unshift({  // prepend button
							text: '<?php echo Yii::t('actions', 'Complete'); ?>',
							click: function() {
							    $.post('<?php echo $completeAction; ?>', {id: event.id});
							    event.complete = 'Yes';
							    $(this).dialog('close');
								cleanUpDialog();
							},
						});
						boxButtons.unshift({  // prepend button
							text: '<?php echo Yii::t('actions', 'Complete and View Contact'); ?>',
							click: function() {
							    $.post('<?php echo $completeAction; ?>', {id: event.id});
								window.location = event.associationUrl;
							},
						});
					}
				}
			} else { // action clicked
				var boxTitle = 'Action';
				if(event.source.editable) {
					if(event.complete == 'Yes') {
						boxButtons.unshift({  // prepend button
							text: '<?php echo Yii::t('actions', 'Uncomplete'); ?>',
							click: function() {
							    $.post('<?php echo $uncompleteAction; ?>', {id: event.id});
							    event.complete = 'No';
							    $(this).dialog('close');
								cleanUpDialog();
							},
						});
					} else {
						boxButtons.unshift({  // prepend button
							text: '<?php echo Yii::t('actions', 'Complete'); ?>',
							click: function() {
							    $.post('<?php echo $completeAction; ?>', {id: event.id});
							    event.complete = 'Yes';
							    $(this).dialog('close');
								cleanUpDialog();
							},
						});
					}
				}
			}
			viewAction.dialog({
				title: boxTitle, 
				autoOpen: false,
				resizable: true,
				width: dialogWidth,
				show: 'fade',
				hide: 'fade',
				buttons: boxButtons,
				open: function() {
				    $('.ui-dialog-buttonpane').find('button:contains(\"' + focusButton + '\")')
				    	.css('background', '#579100')
				    	.css('color', 'white')
				    	.focus();
				    $('.ui-dialog-buttonpane').find('button').css('font-size', '0.85em');
				    $('.ui-dialog-title').css('font-size', '0.8em');
				    $('.ui-dialog-titlebar').css('padding', '0.2em 0.4em');
				    $(viewAction).css('font-size', '0.75em');
				},
			});
			
		},
		editable: true,
		// translate (if local not set to english)
		buttonText: { // translate buttons
			today: '<?php echo Yii::t('calendar', 'today'); ?>',
			month: '<?php echo Yii::t('calendar', 'month'); ?>',
			week: '<?php echo Yii::t('calendar', 'week'); ?>',
			day: '<?php echo Yii::t('calendar', 'day'); ?>',
		},
		monthNames: [ // translate month names
			'<?php echo Yii::t('calendar', 'January'); ?>',
			'<?php echo Yii::t('calendar', 'February'); ?>',
			'<?php echo Yii::t('calendar', 'March'); ?>',
			'<?php echo Yii::t('calendar', 'April'); ?>',
			'<?php echo Yii::t('calendar', 'May'); ?>',
			'<?php echo Yii::t('calendar', 'June'); ?>',
			'<?php echo Yii::t('calendar', 'July'); ?>',
			'<?php echo Yii::t('calendar', 'August'); ?>',
			'<?php echo Yii::t('calendar', 'September'); ?>',
			'<?php echo Yii::t('calendar', 'October'); ?>',
			'<?php echo Yii::t('calendar', 'November'); ?>',
			'<?php echo Yii::t('calendar', 'December'); ?>',
		],
		monthNamesShort: [ // translate short month names
			'<?php echo Yii::t('calendar', 'Jan'); ?>',
			'<?php echo Yii::t('calendar', 'Feb'); ?>',
			'<?php echo Yii::t('calendar', 'Mar'); ?>',
			'<?php echo Yii::t('calendar', 'Apr'); ?>',
			'<?php echo Yii::t('calendar', 'May'); ?>',
			'<?php echo Yii::t('calendar', 'Jun'); ?>',
			'<?php echo Yii::t('calendar', 'Jul'); ?>',
			'<?php echo Yii::t('calendar', 'Aug'); ?>',
			'<?php echo Yii::t('calendar', 'Sep'); ?>',
			'<?php echo Yii::t('calendar', 'Oct'); ?>',
			'<?php echo Yii::t('calendar', 'Nov'); ?>',
			'<?php echo Yii::t('calendar', 'Dec'); ?>',
		],
		dayNames: [ // translate day names
			'<?php echo Yii::t('calendar', 'Sunday'); ?>',
			'<?php echo Yii::t('calendar', 'Monday'); ?>',
			'<?php echo Yii::t('calendar', 'Tuesday'); ?>',
			'<?php echo Yii::t('calendar', 'Wednesday'); ?>',
			'<?php echo Yii::t('calendar', 'Thursday'); ?>',
			'<?php echo Yii::t('calendar', 'Friday'); ?>',
			'<?php echo Yii::t('calendar', 'Saturday'); ?>',
		],
		dayNamesShort: [ // translate short day names
			'<?php echo Yii::t('calendar', 'Sun'); ?>',
			'<?php echo Yii::t('calendar', 'Mon'); ?>',
			'<?php echo Yii::t('calendar', 'Tue'); ?>',
			'<?php echo Yii::t('calendar', 'Wed'); ?>',
			'<?php echo Yii::t('calendar', 'Thu'); ?>',
			'<?php echo Yii::t('calendar', 'Fri'); ?>',
			'<?php echo Yii::t('calendar', 'Sat'); ?>',
		]

	});
<?php echo $checkedUserCalendars; ?>
<?php echo $checkedSharedCalendars; ?>
<?php echo $checkedGoogleCalendars; ?>

});

// view/hide actions associated with a user
function toggleCalendarSource(user, on, isEditable) {
	if(user == '')
		user = 'Anyone';
	if(on) {
		$('#calendar').fullCalendar('addEventSource', 
			{
				url: '<?php echo $jsonFeed; ?>' + '?user=' + user,
				type: 'POST',
				editable: isEditable,
			}
		);
	} else {
		$('#calendar').fullCalendar('removeEventSource', 
			{
				url: '<?php echo $jsonFeed; ?>' + '?user=' + user,
				type: 'POST',
				editable: isEditable,
			}
		);
	}
	$.post('<?php echo $saveCheckedCalendar; ?>', {Calendar: user, Checked: on, Type: 'user'});
}

// view/hide actions from a shared calendar
function toggleCalendarSourceShared(calendarId, on, isEditable) {
	if(on) {
		$('#calendar').fullCalendar('addEventSource', 
			{
				url: '<?php echo $jsonFeedShared; ?>' + '?calendarId=' + calendarId,
				type: 'POST',
				editable: isEditable,
			}
		);
	} else {
		$('#calendar').fullCalendar('removeEventSource', 
			{
				url: '<?php echo $jsonFeedShared; ?>' + '?calendarId=' + calendarId,
				type: 'POST',
				editable: isEditable,
			}
		);
	}
	$.post('<?php echo $saveCheckedCalendar; ?>', {Calendar: calendarId, Checked: on, Type: 'shared'});
}

// view/hide actions from a google calendar
function toggleCalendarSourceGoogle(calendarId, on, googleFeed) {
	if(on) {
		$('#calendar').fullCalendar('addEventSource', 
			{
				url: googleFeed,
				type: 'POST',
				editable: false,
				type: 'google'
			}
		);
	} else {
		$('#calendar').fullCalendar('removeEventSource', 
			{
				url: googleFeed,
				type: 'POST',
				editable: false,
				type: 'google'
			}
		);
	}
	$.post('<?php echo $saveCheckedCalendar; ?>', {Calendar: calendarId, Checked: on, Type: 'google'});
}

// filter calendar actions
function toggleCalendarFilter(filterName, on) {
	$.post('<?php echo $saveCheckedCalendarFilter; ?>', {Filter: filterName, Checked: on})
		.success(function() { $('#calendar').fullCalendar('refetchEvents'); } );
}

// remove id's so we can create another dialog
function cleanUpDialog() {
	$('#dialog-Actions_dueDate').remove();
	$('#dialog-Actions_startDate').remove();
	$('#dialog_actionsAssignedToDropdown').remove();
	$('#dialog_groupCheckbox').remove();
	$('#dialog_Actions_visibility').remove();
}

// the user has edited something in the dialog, so hilight 'Save' so user remembers to save and not just close the dialog
function giveSaveButtonFocus() {
$('.ui-dialog-buttonpane').find('button')
    .css('background', '')
    .css('color', '');
$('.ui-dialog-buttonpane').find('button:contains("Save")')
    .css('background', '#579100')
    .css('color', 'white')
    .focus();
}

</script>

<?php
//",CClientScript::POS_HEAD);

?>
<br />
<?php
$this->widget('InlineActionForm',
	array(
		'associationType'=>'calendar',
		'associationId'=>'',
		'assignedTo'=>Yii::app()->user->getName(),
		'users'=>UserChild::getNames(),
		'inCalendar'=>true,
		'startHidden'=>false,
		'showLogACall'=>false,
		'showNewComment'=>false,
	)
);

?>

