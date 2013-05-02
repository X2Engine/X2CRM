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

<?php
if(Yii::app()->params->admin->googleIntegration) { // menu if google integration is enables has additional options
	if(Yii::app()->user->checkAccess('AdminIndex')) {
		$menuItems = array(
			array('label'=>Yii::t('calendar', 'Calendar')),
			array('label'=>Yii::t('calendar', 'My Calendar Permissions'), 'url'=>array('myCalendarPermissions')),
			array('label'=>Yii::t('calendar', 'User Calendar Permissions'), 'url'=>array('userCalendarPermissions')),
	//		array('label'=>Yii::t('calendar', 'List'),'url'=>array('list')),
	//		array('label'=>Yii::t('calendar', 'Create'), 'url'=>array('create')),
			array('label'=>Yii::t('calendar', 'Sync My Actions To Google Calendar'), 'url'=>array('syncActionsToGoogleCalendar')),
		);
	} else {
		$menuItems = array(
			array('label'=>Yii::t('calendar','Calendar')),
			array('label'=>Yii::t('calendar', 'My Calendar Permissions'), 'url'=>array('myCalendarPermissions')),
	//		array('label'=>Yii::t('calendar', 'List'),'url'=>array('list')),
	//		array('label'=>Yii::t('calendar','Create'), 'url'=>array('create')),
			array('label'=>Yii::t('calendar', 'Sync My Actions To Google Calendar'), 'url'=>array('syncActionsToGoogleCalendar')),
		);
	}
} else {
	if(Yii::app()->user->checkAccess('AdminIndex')) {
		$menuItems = array(
			array('label'=>Yii::t('calendar', 'Calendar')),
			array('label'=>Yii::t('calendar', 'My Calendar Permissions'), 'url'=>array('myCalendarPermissions')),
			array('label'=>Yii::t('calendar', 'User Calendar Permissions'), 'url'=>array('userCalendarPermissions')),
	//		array('label'=>Yii::t('calendar', 'List'),'url'=>array('list')),
	//		array('label'=>Yii::t('calendar', 'Create'), 'url'=>array('create')),
		);
	} else {
		$menuItems = array(
			array('label'=>Yii::t('calendar','Calendar')),
			array('label'=>Yii::t('calendar', 'My Calendar Permissions'), 'url'=>array('myCalendarPermissions')),
	//		array('label'=>Yii::t('calendar', 'List'),'url'=>array('list')),
	//		array('label'=>Yii::t('calendar','Create'), 'url'=>array('create')),
		);
	}
}
$this->actionMenu = $this->formatMenu($menuItems);
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


$this->calendarUsers = X2CalendarPermissions::getViewableUserCalendarNames();
$this->groupCalendars = X2Calendar::getViewableGroupCalendarNames();

//$this->sharedCalendars = X2Calendar::getViewableCalendarNames();
//$this->googleCalendars = X2Calendar::getViewableGoogleCalendarNames();
$this->calendarFilter = X2Calendar::getCalendarFilters();

// urls for ajax (and other javascript) calls
$urls = array(
	'jsonFeed'					=>	$this->createUrl('jsonFeed'), // feed to get actions from users
	'jsonFeedGroup'				=>	$this->createUrl('jsonFeedGroup'), // feed to get actions from group Calendar
	'jsonFeedShared'			=>	$this->createUrl('jsonFeedShared'), // feed to get actions from shared calendars
	'jsonFeedGoogle'			=>	$this->createUrl('jsonFeedGoogle'), // feed to get events from a google calendar
	'currentUserFeed'			=>	$this->createUrl('jsonFeed', array('user'=>Yii::app()->user->name)), // add current user actions to calendar
	'anyoneUserFeed'			=>	$this->createUrl('jsonFeed', array('user'=>'Anyone')), // add Anyone actions to calendar
	'moveAction'				=>	$this->createUrl('moveAction'),
	'moveGoogleEvent'			=>	$this->createUrl('moveGoogleEvent'),
	'resizeAction'				=>	$this->createUrl('resizeAction'),
	'resizeGoogleEvent'			=>	$this->createUrl('resizeGoogleEvent'),
	'viewAction'				=>	$this->createUrl('viewAction'),
	'saveAction'				=>	$this->createUrl('/actions/actions/quickUpdate'),
	'editAction'				=>	$this->createUrl('editAction'),
	'viewGoogleEvent'			=>	$this->createUrl('viewGoogleEvent'),
	'editGoogleEvent'			=>	$this->createUrl('editGoogleEvent'),
	'saveGoogleEvent'			=>	$this->createUrl('saveGoogleEvent'),
	'deleteGoogleEvent'			=>	$this->createUrl('deleteGoogleEvent'),
	'completeAction'			=>	$this->createUrl('completeAction'),
	'uncompleteAction'			=>	$this->createUrl('uncompleteAction'),
	'deleteAction'				=>	$this->createUrl('deleteAction'),
	'saveCheckedCalendar'		=>	$this->createUrl('saveCheckedCalendar'),
	'saveCheckedCalendarFilter'	=>	$this->createUrl('saveCheckedCalendarFilter'),
);

$user = User::model()->findByPk(Yii::app()->user->getId());
$showCalendars = json_decode($user->showCalendars, true);

// fix showCalendars['groupCalendars']
if(!isset($showCalendars['groupCalendars'])) {
	$showCalendars['groupCalendars'] = array();
	$user->showCalendars = json_encode($showCalendars);
	$user->update();
}

$userCalendars = $showCalendars['userCalendars'];
$groupCalendars = $showCalendars['groupCalendars'];
$sharedCalendars = $showCalendars['sharedCalendars'];
$googleCalendars = $showCalendars['googleCalendars'];

$editableUserCalendars = X2CalendarPermissions::getEditableUserCalendarNames();
$checkedUserCalendars = '';
foreach($userCalendars as $user) {
	if(isset($this->calendarUsers[$user])) {
		$userCalendarFeed = $this->createUrl('jsonFeed', array('user'=>$user));
		if(isset($editableUserCalendars[$user]))
			$editable = 'true';
		else
			$editable = 'false';
		$checkedUserCalendars .= '
		$("#calendar").fullCalendar("addEventSource",{
			url: "'.$userCalendarFeed.'",
			editable: '.$editable.',
		});';
	}
}

$checkedGroupCalendars = '';
foreach($groupCalendars as $groupId) {
	if(isset($this->groupCalendars[$groupId])) {
		$checkedGroupCalendars .= '
		$("#calendar").fullCalendar("addEventSource",{
			url:"'.$urls['jsonFeedGroup'].'?groupId='.$groupId.'",
			editable:true,
		});';
	}
}

/*
$editableCalendars = X2Calendar::getEditableCalendarNames();
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
	$calendar = X2Calendar::model()->findByPk($calendarId);
	if(isset($this->googleCalendars[$calendarId])) {
		if($calendar->googleCalendarId) {
			if(isset($editableCalendars[$calendarId]))
				$editable = 'true';
			else
				$editable = 'false';
			$checkedGoogleCalendars .= "
	$('#calendar').fullCalendar('addEventSource',
	   {
	   		url: '$jsonFeedGoogle?calendarId=' + '{$calendar->id}',
	   		type: 'POST',
	   		source: 'google',
	   		calendarId: '$calendarId',
	   		editable: $editable,
	   }
	);
";
		} else {
			$checkedGoogleCalendars .= "
	$('#calendar').fullCalendar('addEventSource',
		{
			url: '{$calendar->googleFeed}',
			type: 'POST',
			editable: false,
			type: 'googleFeed',
		}
	);
";
		}
	}
}
*/

// Yii::app()->clientScript->registerScript('initCalendar', "
?>
<script type="text/javascript">

/**************************************************************
 *                                                            *
 *                       Declare Calendar                     *
 *                                                            *
 *                                                            *
 **************************************************************/

$(function() {
	$('#calendar').fullCalendar({
		theme: true,
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
		dayClick: function(date, allDay, jsEvent, view) { // Day Clicked!! Scroll to Publisher and set date to the day that was clicked

			var scrollPublisher = $('#tabs').offset().top + $('#tabs').height() + 5 - $(window).height(); // value of window's scrollbar to make publisher visible
			if($(window).scrollTop() < scrollPublisher) {
			    $('html,body').animate({ scrollTop: scrollPublisher });
			}

			$('#Actions_actionDescription').focus();

			var actionDate = new Date(date.getTime());
			var eventDate = new Date(date.getTime());
			if(view.name == 'month' || view.name == 'basicWeek') {
				if($('#end-date-input').datetimepicker('getDate')) {
					var oldEventDate = $('#end-date-input').datetimepicker('getDate');
					eventDate.setHours(oldEventDate.getHours());
					eventDate.setMinutes(oldEventDate.getMinutes());
				}
			}

			var dateformat = $('#publisher-form').data('dateformat');
			var timeformat = $('#publisher-form').data('timeformat');
			var ampmformat = $('#publisher-form').data('ampmformat');
			var region = $('#publisher-form').data('region');

			if(typeof(dateformat) == 'undefined') {
				dateformat = 'M d, yy';
			}
			if(typeof(timeformat) == 'undefined') {
				timeformat = 'h:mm TT';
			}
			if(typeof(ampmformat) == 'undefined') {
				ampmformat = true
			}
			if(typeof(region) == 'undefined') {
				region = '';
			}

			$('#Actions_dueDate').datetimepicker("destroy");
			$('#Actions_dueDate').datetimepicker(jQuery.extend({showMonthAfterYear:false}, jQuery.datepicker.regional[region], {'dateFormat':dateformat,'timeFormat':timeformat,'ampm':ampmformat,'changeMonth':true,'changeYear':true, 'defaultDate': actionDate}));
			$('#Actions_dueDate').datetimepicker('setDate', actionDate);

			if($('#end-date-input').datetimepicker('getDate')) {
				$('#end-date-input').datetimepicker("destroy");
				$('#end-date-input').datetimepicker(jQuery.extend({showMonthAfterYear:false}, jQuery.datepicker.regional[region], {'dateFormat':dateformat,'timeFormat':timeformat,'ampm':ampmformat,'changeMonth':true,'changeYear':true, 'defaultDate': eventDate}));
				$('#end-date-input').datetimepicker('setDate', eventDate);
			}

		},
		eventDrop: function(event, dayDelta, minuteDelta, allDay, revertFunc) { // drop onto a different day
			if(event.source.source == 'google') // moving event from Google Calendar
				$.post('<?php echo $urls['moveGoogleEvent']; ?>?calendarId=' + event.source.calendarId, {EventId: event.id, dayChange: dayDelta, minuteChange: minuteDelta, isAllDay: allDay});
			else
				$.post('<?php echo $urls['moveAction']; ?>', {id: event.id, dayChange: dayDelta, minuteChange: minuteDelta, isAllDay: allDay});
		},
		eventResize: function(event, dayDelta, minuteDelta, revertFunc) {
			if(event.source.source == 'google') // moving event from Google Calendar
				$.post('<?php echo $urls['resizeGoogleEvent']; ?>?calendarId=' + event.source.calendarId, {EventId: event.id, dayChange: dayDelta, minuteChange: minuteDelta});
			else
				$.post('<?php echo $urls['resizeAction']; ?>', {id: event.id, dayChange: dayDelta, minuteChange: minuteDelta});
		},
		eventClick: function(event) { // Event Click! Pop up a dialog with info about the event

      if ($('[id="dialog-content_' + event.id + '"]').length != 0) { // prevent duplicate dialog windows
        return;
      }

			if(event.source.type == 'googleFeed')
				return;
			var viewAction = $('<div></div>', {id: 'dialog-content' + '_' + event.id});  // dialog box (opened at the end of this function)
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

			if(event.source.source == 'google') {
				var boxTitle = '<?php echo Yii::t('calendar', 'Google Event'); ?>';
				if(event.source.editable) {
					dialogWidth = 600;
					$.post('<?php echo $urls['editGoogleEvent']; ?>', {EventId: event.id, CalendarId: event.source.calendarId}, function(data) {
						$(viewAction).append(data);
						$(viewAction).dialog('open');
					});
					boxButtons.unshift({
					    text: '<?php echo Yii::t('app', 'Save'); ?>', // update event
					    click: function() {
					        $.post('<?php echo $urls['saveGoogleEvent']; ?>?calendarId=' + event.source.calendarId, $(viewAction).find('form').serializeArray(), function() {$('#calendar').fullCalendar('refetchEvents');}); // delete event from database
					        $(this).dialog('close');
					    	cleanUpDialog();
					    },
					});
					boxButtons.unshift({
					    text: '<?php echo Yii::t('app', 'Delete'); ?>', // delete event
					    click: function() {
					    	if(confirm('Are you sure you want to delete this action?')) {
					        	$.post('<?php echo $urls['deleteGoogleEvent']; ?>?calendarId=' + event.source.calendarId, {EventId: event.id}); // delete event from Google Calendar
					        	$('#calendar').fullCalendar('removeEvents', event.id);
					        	$(this).dialog('close');
					    		cleanUpDialog();
					        }
					    },
					});
				} else {
					$.post('<?php echo $urls['viewGoogleEvent']; ?>', {EventId: event.id, CalendarId: event.source.calendarId}, function(data) {
						$(viewAction).append(data);
						$(viewAction).dialog('open');
					});
				}
			} else {

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
						{'dateFormat': '<?php echo Formatter::formatDatePicker('medium'); ?>'}
					));
					*/
					dialogWidth = 600;
					$.post('<?php echo $urls['editAction']; ?>', {'ActionId': event.id, 'IsEvent': event.type=='event'}, function(data) {
					    $(viewAction).append(data);
					    viewAction.dialog('open'); //open dialog after its filled with action/event
					});
					boxButtons.unshift({
					    text: '<?php echo Yii::t('app', 'Save'); ?>', // delete event
					    click: function() {
	//				    	var description = $(eventDescription).val();
					        $.post('<?php echo $urls['saveAction']; ?>?id=' + event.id, $(viewAction).find('form').serialize(), function() {$('#calendar').fullCalendar('refetchEvents');}); // delete event from database
	//				        event.title = description.substring(0, 30);
	//				        event.description = description;
	//				        $('#calendar').fullCalendar('updateEvent', event);
					        $(this).dialog('close');
					    	cleanUpDialog();
					    },
					});
					boxButtons.unshift({
					    text: '<?php echo Yii::t('app', 'Delete'); ?>', // delete event
					    click: function() {
					    	if(confirm('Are you sure you want to delete this action?')) {
					        	$.post('<?php echo $urls['deleteAction']; ?>', {id: event.id}); // delete event from database
					        	$('#calendar').fullCalendar('removeEvents', event.id);
					        	$(this).dialog('close');
					    		cleanUpDialog();
					        }
					    },
					});
				} else { // non-editable event/action
					$.post('<?php echo $urls['viewAction']; ?>', {'ActionId': event.id, 'IsEvent': event.type=='event'}, function(data) {
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
								    $.post('<?php echo $urls['uncompleteAction']; ?>', {id: event.id});
								    event.complete = 'No';
								    $(this).dialog('close');
									cleanUpDialog();
								},
							});
						} else {
							boxButtons.unshift({  // prepend button
								text: '<?php echo Yii::t('actions', 'Complete'); ?>',
								click: function() {
								    $.post('<?php echo $urls['completeAction']; ?>', {id: event.id});
								    event.complete = 'Yes';
								    $(this).dialog('close');
									cleanUpDialog();
								},
							});
							boxButtons.unshift({  // prepend button
								text: '<?php echo Yii::t('actions', 'Complete and View Contact'); ?>',
								click: function() {
								    $.post('<?php echo $urls['completeAction']; ?>', {id: event.id});
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
								    $.post('<?php echo $urls['uncompleteAction']; ?>', {id: event.id});
								    event.complete = 'No';
								    $(this).dialog('close');
									cleanUpDialog();
								},
							});
						} else {
							boxButtons.unshift({  // prepend button
								text: '<?php echo Yii::t('actions', 'Complete'); ?>',
								click: function() {
								    $.post('<?php echo $urls['completeAction']; ?>', {id: event.id});
								    event.complete = 'Yes';
								    $(this).dialog('close');
									cleanUpDialog();
								},
							});
						}
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
        close: function () {
              $('[id="dialog-content_' + event.id + '"]').remove ();
        }
			});
		},
		editable: true,
		// translate (if local not set to english)
		buttonText: { // translate buttons
			today: '<?php echo CHtml::encode(Yii::t('calendar', 'today')); ?>',
			month: '<?php echo CHtml::encode(Yii::t('calendar', 'month')); ?>',
			week: '<?php echo CHtml::encode(Yii::t('calendar', 'week')); ?>',
			day: '<?php echo CHtml::encode(Yii::t('calendar', 'day')); ?>',
		},
		monthNames: [ // translate month names
			'<?php echo CHtml::encode(Yii::t('calendar', 'January')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'February')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'March')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'April')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'May')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'June')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'July')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'August')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'September')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'October')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'November')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'December')); ?>',
		],
		monthNamesShort: [ // translate short month names
			'<?php echo CHtml::encode(Yii::t('calendar', 'Jan')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'Feb')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'Mar')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'Apr')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'May')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'Jun')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'Jul')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'Aug')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'Sep')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'Oct')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'Nov')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'Dec')); ?>',
		],
		dayNames: [ // translate day names
			'<?php echo CHtml::encode(Yii::t('calendar', 'Sunday')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'Monday')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'Tuesday')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'Wednesday')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'Thursday')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'Friday')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'Saturday')); ?>',
		],
		dayNamesShort: [ // translate short day names
			'<?php echo CHtml::encode(Yii::t('calendar', 'Sun')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'Mon')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'Tue')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'Wed')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'Thu')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'Fri')); ?>',
			'<?php echo CHtml::encode(Yii::t('calendar', 'Sat')); ?>',
		]

	});
<?php echo $checkedUserCalendars; ?>
<?php echo $checkedGroupCalendars; ?>
<?php //echo $checkedSharedCalendars; ?>
<?php //echo $checkedGoogleCalendars; ?>

});

// view/hide actions associated with a user
function toggleUserCalendarSource(user, on, isEditable) {
	if(user == '')
		user = 'Anyone';
	if(on) {
		$('#calendar').fullCalendar('addEventSource',
			{
				url: '<?php echo $urls['jsonFeed']; ?>?user=' + user,
				editable: isEditable,
			}
		);
	} else {
		$('#calendar').fullCalendar('removeEventSource',
			{
				url: '<?php echo $urls['jsonFeed']; ?>?user=' + user,
				editable: isEditable,
			}
		);
	}
	$.post('<?php echo $urls['saveCheckedCalendar']; ?>', {Calendar: user, Checked: on, Type: 'user'});
}

function toggleGroupCalendarSource(groupId, on) {
	if(on) {
		$('#calendar').fullCalendar('addEventSource',
			{
				url: '<?php echo $urls['jsonFeedGroup']; ?>?groupId=' + groupId,
				editable: true,
			}
		);
	} else {
		$('#calendar').fullCalendar('removeEventSource',
			{
				url: '<?php echo $urls['jsonFeedGroup']; ?>?groupId=' + groupId,
				editable: true,
			}
		);
	}
	$.post('<?php echo $urls['saveCheckedCalendar']; ?>', {Calendar: groupId, Checked: on, Type: 'group'});
}

// view/hide actions from a shared calendar
/*
function toggleCalendarSourceShared(calendarId, on, isEditable) {
	if(on) {
		$('#calendar').fullCalendar('addEventSource',
			{
				url: '<?php echo $urls['jsonFeedShared']; ?>' + '?calendarId=' + calendarId,
				type: 'POST',
				editable: isEditable,
			}
		);
	} else {
		$('#calendar').fullCalendar('removeEventSource',
			{
				url: '<?php echo $urls['jsonFeedShared']; ?>' + '?calendarId=' + calendarId,
				type: 'POST',
				editable: isEditable,
			}
		);
	}
	$.post('<?php echo $urls['saveCheckedCalendar']; ?>', {Calendar: calendarId, Checked: on, Type: 'shared'});
}

// view/hide actions from a read/write google calendar
function toggleCalendarSourceGoogle(id, on, isEditable) {
//	$.post('<?php echo $urls['jsonFeedGoogle']; ?>' + '?calendarId=' + calendarId);
	if(on) {
		$('#calendar').fullCalendar('addEventSource',
			{
				url: '<?php echo $urls['jsonFeedGoogle']; ?>' + '?calendarId=' + id,
				type: 'POST',
				source: 'google',
				calendarId: id,
				editable: isEditable,
			}
		);
	} else {
		$('#calendar').fullCalendar('removeEventSource',
			{
				url: '<?php echo $urls['jsonFeedGoogle']; ?>' + '?calendarId=' + id,
				type: 'POST',
				source: 'google',
				calendarId: id,
				editable: isEditable,
			}
		);
	}
	$.post('<?php echo $urls['saveCheckedCalendar']; ?>', {Calendar: id, Checked: on, Type: 'google'});
}


// view/hide actions from a read only google calendar feed
function toggleCalendarSourceGoogleFeed(calendarId, on, googleFeed) {
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
	$.post('<?php echo $urls['saveCheckedCalendar']; ?>', {Calendar: calendarId, Checked: on, Type: 'google'});
}
*/

// filter calendar actions
function toggleCalendarFilter(filterName, on) {
	$.post('<?php echo $urls['saveCheckedCalendarFilter']; ?>', {Filter: filterName, Checked: on})
		.done(function() { $('#calendar').fullCalendar('refetchEvents'); } );
}

// remove id's so we can create another dialog
function cleanUpDialog() {
	$('#dialog-Actions_dueDate').remove();
	$('#dialog-Actions_startDate').remove();
	$('#dialog_actionsAssignedToDropdown').remove();
	$('#dialog_groupCheckbox').remove();
	$('body').off('click','#dialog_groupCheckbox');
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

<?php $this->widget('Publisher',
	array(
		'associationType'=>'calendar',
		'showNewEvent'=>true,
		'showLogACall'=>false,
		'showNewComment'=>false,
		'showNewAction'=>false,
	)
); ?>
