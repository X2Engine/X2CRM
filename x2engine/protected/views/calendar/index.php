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
<div id="calendar">

</div>
<?php
Yii::app()->clientScript->registerCssFile(Yii::app()->theme->getBaseUrl() .'/css/fullcalendar/fullcalendar.css');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/fullcalendar/fullcalendar.js');

$this->calendarUsers = UserChild::getNames();


$jsonFeed = $this->createUrl('jsonFeed');
$currentUserFeed = $this->createUrl('jsonFeed', array('user'=>Yii::app()->user->name)); // add current user actions to calendar
$anyoneUserFeed = $this->createUrl('jsonFeed', array('user'=>'Anyone')); // add Anyone actions to calendar
$moveAction = $this->createUrl('moveAction');
$resizeAction = $this->createUrl('resizeAction');
$saveAction = $this->createUrl('saveAction');
$completeAction = $this->createUrl('completeAction');
$uncompleteAction = $this->createUrl('uncompleteAction');
$deleteAction = $this->createUrl('deleteAction');

Yii::app()->clientScript->registerScript('initCalendar', "
$(function() {
	$('#calendar').fullCalendar({
		weekMode: 'liquid',
		header: {
			left: 'title',
			center: '',
			right: 'month basicWeek agendaDay prev,next'
		},
		dayClick: function(date, allDay, jsEvent, view) {
			if(view.name == 'month') {
				$('#calendar').fullCalendar('changeView', 'agendaDay');
				$('#calendar').fullCalendar('gotoDate', date);
			}
		},
		eventDrop: function(event, dayDelta, minuteDelta, allDay, revertFunc) {
			$.post('".$moveAction."', {id: event.id, dayChange: dayDelta, minuteChange: minuteDelta, isAllDay: allDay});
		},
		eventResize: function(event, dayDelta, minuteDelta, revertFunc) {
			$.post('".$resizeAction."', {id: event.id, dayChange: dayDelta, minuteChange: minuteDelta});
		},
		eventClick: function(event) { // event was clicked
		
			var viewAction = $('<div></div>')  // dialog box (opened at the end of this function)
			
			var boxButtons =  [ // buttons on bottom of dialog
			    {
			    	text: '".Yii::t('app', 'Close')."', 
			    	click: function() {
			    		$(this).dialog('close');
			    	}
			    },
			];
			
			if(event.source.editable) {
				var boxContent = $('<textarea></textarea>', {
					rows: 3,
					style: 'width: 95%;',
				}).val(event.description);
				viewAction.html(boxContent);
				boxButtons.unshift({
					text: '".Yii::t('app', 'Save')."', // delete event
					click: function() {
						var description = $(boxContent).val();
					    $.post('".$saveAction."', {id: event.id, actionDescription: description}); // delete event from database
					    event.title = description;
					    $('#calendar').fullCalendar('updateEvent', event);
					    $(this).dialog('close');
					},
				});
				boxButtons.unshift({
					text: '".Yii::t('app', 'Delete')."', // delete event
					click: function() {
					    $.post('".$deleteAction."', {id: event.id}); // delete event from database
					    $('#calendar').fullCalendar('removeEvents', event.id);
					    $(this).dialog('close');
					},
				});
			} else {
				viewAction.html(event.description);
			}
			
			if(event.associationType == 'calendar') { // calendar event clicked
				var boxTitle = 'Event';
			} else if(event.associationType == 'contacts') { // action associated with a contact clicked
				if(event.type == 'event')
					boxTitle = 'Contact Event';
				else
					boxTitle = 'Contact Action';
				viewAction.prepend('<b>' + event.associationName + '</b><br />');
				boxButtons.unshift({  //prepend button
					text: '".Yii::t('contacts', 'View Contact')."',
					click: function() {
						window.location = event.associationUrl;
					},
				});
				if(event.source.editable && event.type != 'event') {
					if(event.complete == 'Yes') {
						boxButtons.unshift({  // prepend button
							text: '".Yii::t('actions', 'Uncomplete')."',
							click: function() {
							    $.post('".$uncompleteAction."', {id: event.id});
							    event.complete = 'No';
							    $(this).dialog('close');
							},
						});
					} else {
						boxButtons.unshift({  // prepend button
							text: '".Yii::t('actions', 'Complete')."',
							click: function() {
							    $.post('".$completeAction."', {id: event.id});
							    event.complete = 'Yes';
							    $(this).dialog('close');
							},
						});
						boxButtons.unshift({  // prepend button
							text: '".Yii::t('actions', 'Complete and View Contact')."',
							click: function() {
							    $.post('".$completeAction."', {id: event.id});
								window.location = event.associationUrl;
							},
						});
					}
				}
			} else { // action clicked
				var boxTitle = 'Action';
				if(event.complete == 'Yes') {
					boxButtons.unshift({  // prepend button
						text: '".Yii::t('actions', 'Uncomplete')."',
						click: function() {
						    $.post('".$uncompleteAction."', {id: event.id});
						    event.complete = 'No';
						    $(this).dialog('close');
						},
					});
				} else {
					boxButtons.unshift({  // prepend button
						text: '".Yii::t('actions', 'Complete')."',
						click: function() {
						    $.post('".$completeAction."', {id: event.id});
						    event.complete = 'Yes';
						    $(this).dialog('close');
						},
					});
				}
			}
			viewAction.dialog({
			    	title: boxTitle, 
			    	autoOpen: false,
			    	resizable: true,
			    	width: 390,
			    	buttons: boxButtons,
			    });
			viewAction.dialog('open');
			
		},
		editable: true,
		// translate (if local not set to english)
		buttonText: { // translate buttons
			today: '".Yii::t('calendar', 'today')."',
			month: '".Yii::t('calendar', 'month')."',
			week: '".Yii::t('calendar', 'week')."',
			day: '".Yii::t('calendar', 'day')."',
		},
		monthNames: [ // translate month names
			'".Yii::t('calendar', 'January')."',
			'".Yii::t('calendar', 'February')."',
			'".Yii::t('calendar', 'March')."',
			'".Yii::t('calendar', 'April')."',
			'".Yii::t('calendar', 'May')."',
			'".Yii::t('calendar', 'June')."',
			'".Yii::t('calendar', 'July')."',
			'".Yii::t('calendar', 'August')."',
			'".Yii::t('calendar', 'September')."',
			'".Yii::t('calendar', 'October')."',
			'".Yii::t('calendar', 'November')."',
			'".Yii::t('calendar', 'December')."',
		],
		monthNamesShort: [ // translate short month names
			'".Yii::t('calendar', 'Jan')."',
			'".Yii::t('calendar', 'Feb')."',
			'".Yii::t('calendar', 'Mar')."',
			'".Yii::t('calendar', 'Apr')."',
			'".Yii::t('calendar', 'May')."',
			'".Yii::t('calendar', 'Jun')."',
			'".Yii::t('calendar', 'Jul')."',
			'".Yii::t('calendar', 'Aug')."',
			'".Yii::t('calendar', 'Sep')."',
			'".Yii::t('calendar', 'Oct')."',
			'".Yii::t('calendar', 'Nov')."',
			'".Yii::t('calendar', 'Dec')."',
		],
		dayNames: [ // translate day names
			'".Yii::t('calendar', 'Sunday')."',
			'".Yii::t('calendar', 'Monday')."',
			'".Yii::t('calendar', 'Tuesday')."',
			'".Yii::t('calendar', 'Wednesday')."',
			'".Yii::t('calendar', 'Thursday')."',
			'".Yii::t('calendar', 'Friday')."',
			'".Yii::t('calendar', 'Saturday')."',
		],
		dayNamesShort: [ // translate short day names
			'".Yii::t('calendar', 'Sun')."',
			'".Yii::t('calendar', 'Mon')."',
			'".Yii::t('calendar', 'Tue')."',
			'".Yii::t('calendar', 'Wed')."',
			'".Yii::t('calendar', 'Thu')."',
			'".Yii::t('calendar', 'Fri')."',
			'".Yii::t('calendar', 'Sat')."',
		]
	});
		
	$('#calendar').fullCalendar('addEventSource', 
		{
			url: '".$currentUserFeed."',
			type: 'POST',
			error: function(jqXHR, textStatus, errorThrown) { alert('error:' + textStatus); },
			editable: true,
		}
	);
	$('#calendar').fullCalendar('addEventSource', 
		{
			url: '".$anyoneUserFeed."',
			type: 'POST',
			error: function(jqXHR, textStatus, errorThrown) { alert('error:' + textStatus); },
			editable: true,
		}
	);
});

function toggleCalendarSource(user, on) {
	if(user == '')
		user = 'Anyone';
	if(on) {
		$('#calendar').fullCalendar('addEventSource', 
			{
				url: '".$jsonFeed."' + '?user=' + user,
				type: 'POST',
				error: function(jqXHR, textStatus, errorThrown) { alert('error:' + textStatus); },
				editable: ((user == '".Yii::app()->user->name."' || user == 'Anyone' || '".Yii::app()->user->name."' == 'admin')? true : false),
			}
		);
	} else {
		$('#calendar').fullCalendar('removeEventSource', 
			{
				url: '".$jsonFeed."' + '?user=' + user,
				type: 'POST',
				error: function(jqXHR, textStatus, errorThrown) { alert('error:' + textStatus); },
				editable: ((user == '".Yii::app()->user->name."' || user == 'Anyone' || '".Yii::app()->user->name."' == 'admin')? true : false),
			}
		);
	}
}

",CClientScript::POS_HEAD);

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
