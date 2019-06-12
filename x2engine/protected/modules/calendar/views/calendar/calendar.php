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




$this->noBackdrop = true;

Yii::app()->clientScript->registerCssFile($this->module->assetsUrl.'/css/calendarLayout.css');


$halfWidthThreshold = 1200; // content width past which publisher moves to the right of calendar
Yii::app()->clientScript->registerCss('calendarResponsiveCss',"

#calendar .fc-day-number > a {
    text-decoration: none;
}

#calendar,
#publisher {
    max-width: ".$halfWidthThreshold."px;
}

.responsive-page-title.fc-header {
    border-radius: 4px 4px 0 0 ;
    -moz-border-radius: 4px 4px 0 0;
    -webkit-border-radius: 4px 4px 0 0;
    -o-border-radius: 4px 4px 0 0;
}


#calendar.half-width {
    float: left;
    width: 70%;
    margin-right: 5px;
}

#publisher-outer.half-width {
    overflow: hidden;
    width: auto;
    float: none;
    padding: 0 1px 0 1px;
}
#publisher-outer.half-width > #publisher {
    padding-left: 8px;
}

#publisher-tabs-row-1 {
    float: none !important;
}
");

// register fullcalendar css and js
Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl() .'/js/fullcalendar-1.6.1/fullcalendar/fullcalendar.css');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/fullcalendar-1.6.1/fullcalendar/fullcalendar.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/fullcalendar-1.6.1/fullcalendar/gcal.js');
Yii::app()->clientScript->registerScriptFile($this->module->assetsUrl . '/js/calendar.js',
    CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl ().'/js/X2Dialog.js');


// register jquery timepicker css and js
// (used inside js dialog because CJuiDateTimePicker is a php library that won't work inside a js dialog)
//Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl() .'/protected/extensions/CJuiDateTimePicker/assets/jquery-ui-timepicker-addon.css');
//Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/protected/extensions/CJuiDateTimePicker/assets/jquery-ui-timepicker-addon.js');

$menuOptions = array(
    'index', 'create'
);
$this->insertMenu($menuOptions);

$this->calendarUsers = X2CalendarPermissions::getViewableUserCalendarNames();

// urls for ajax (and other javascript) calls
$urls = X2Calendar::getCalendarUrls();
$user = User::model()->findByPk(Yii::app()->user->getId());
$showCalendars = json_decode($user->showCalendars, true);

// fix showCalendars['groupCalendars']
if(!isset($showCalendars['groupCalendars'])){
    $showCalendars['groupCalendars'] = array();
    $user->showCalendars = json_encode($showCalendars);
    $user->save();
}

$userCalendars = isset($showCalendars['userCalendars']) ? $showCalendars['userCalendars'] : array();
//$userCalendars = $showCalendars['userCalendars'];

$checkedUserCalendars = '';
foreach($userCalendars as $user){
    if(isset($this->calendarUsers[$user])){
        $userCalendarFeed = $this->createUrl('jsonFeed', array('calendarId' => $user));
        $checkedUserCalendars .= '
        $("#calendar").fullCalendar("addEventSource",{
            url: "'.$userCalendarFeed.'"
        });';
    }
}
?>


<script type="text/javascript">


/**************************************************************
*                       Declare Calendar
**************************************************************/

$(function() {

    $('#calendar').fullCalendar({
        theme: true,
        weekMode: 'liquid',
        header: {
            left: 'title',
            center: '',
            right: 'month agendaWeek agendaDay prev,next'
        },
        eventRender: function(event, element, view) {
            // prevent rendering of duplicate events on same view
            var potentialDuplicates = 
                $.makeArray ($('[data-action-uid="' + view.name + '-action-id-' + event.id + '"]'));

            // duplicate events are fetched when:
            //  1. An event is assigned to multiple users
            //  2. An event spans multiple weeks
            //  3. An event is viewed in multiple views (day, week, month)
            // Only the first case is erroneous. 
            // We avoid removing duplicates associated with case 2 by ensuring that duplicate 
            // events in the same view are part of the same calendar.
            // We avoid removing duplicates associated with case 3 by adding the event view to the 
            // event uid. 
            for (var i in potentialDuplicates) {
                if ($(potentialDuplicates[i]).attr ('data-action-calendarAssignment') !== 
                    event.calendarAssignment) {

                    element.remove (); 
                    return;
                }
            }

            $(element).attr ('data-action-uid', view.name + '-action-id-' + event.id);
            $(element).attr ('data-action-calendarAssignment', event.calendarAssignment);

            $(element).css('font-size', '0.8em');
            /*if(view.name == 'month' || view.name == 'basicWeek')
                $(element).find('.fc-event-time').remove();*/
            if(event.associationType == 'contacts')
                element.attr('title', event.associationName);
        },
        // Day Clicked!! Scroll to Publisher and set date to the day that was clicked
        dayClick: function(date, allDay, jsEvent, view) { 
            if ($(jsEvent.target).hasClass ('day-number-link')) {
                return x2.calendarManager.dayNumberClick ($(jsEvent.target));
            }

            // value of window's scrollbar to make publisher visible
            var scrollPublisher = x2.publisher.getForm ().offset().top + 
                x2.publisher.getForm ().height() + 5 - $(window).height(); 
            if($(window).scrollTop() < scrollPublisher) {
                $('html,body').animate({ scrollTop: scrollPublisher });
            }


            x2.calendarManager.insertDate(date, view);
        },
        // drop onto a different day
        eventDrop: function(event, dayDelta, minuteDelta, allDay, revertFunc) { 
            $.post('<?php echo $urls['moveAction']; ?>', {
                id: event.id, dayChange: dayDelta, minuteChange: minuteDelta, isAllDay: allDay
            });
        },
        eventResize: function(event, dayDelta, minuteDelta, revertFunc) {
            $.post('<?php echo $urls['resizeAction']; ?>', {
                id: event.id, dayChange: dayDelta, minuteChange: minuteDelta});
        },
        eventClick: function(event) { // Event Click! Pop up a dialog with info about the event

            // prevent duplicate dialog windows
            if ($('[id="dialog-content_' + event.id + '"]').length != 0) { 
                return;
            }

            // dialog box (opened at the end of this function)
            var viewAction = $('<div></div>', {id: 'dialog-content' + '_' + event.id});  
            var focusButton = 'Close';
            var dialogWidth = 390;
            var translatedModelTitles = <?php echo CJSON::encode (
                X2Model::getTranslatedModelTitles (true));  ?>; 
            var associations = {};
            for (var associationType in x2.associationModels) {
                associations[associationType] = 
                    translatedModelTitles[x2.associationModels[associationType]];
            }

            var boxButtons =  [ // buttons on bottom of dialog
                {
                    text: '<?php echo CHtml::encode (Yii::t('app', 'Close')); ?>',
                    click: function() {
                        $(this).x2Dialog('close');
                    }
                },
            ];

            if(event.editable){

                dialogWidth = 600;
                $.post(
                    '<?php echo $urls['editAction']; ?>', {
                        'ActionId': event.id, 'IsEvent': event.type=='event'
                    }, function(data) {
                        $(viewAction).append(data);
                        //open dialog after its filled with action/event
                        viewAction.x2Dialog('open'); 
                    }
                );
                boxButtons.unshift({
                    text: '<?php echo CHtml::encode (Yii::t('app', 'Save')); ?>', // delete event
                    'class': 'save-event-button',
                    click: function() {
    //                        var description = $(eventDescription).val();
                        // delete event from database
                        $.post(
                            '<?php echo $urls['saveAction']; ?>?id=' + event.id, 
                            $(viewAction).find('form').serialize(),
                            function() {
                                $('#calendar').fullCalendar('refetchEvents');
                            }
                        ); 
    //                        event.title = description.substring(0, 30);
    //                        event.description = description;
    //                        $('#calendar').fullCalendar('updateEvent', event);
                        $(this).x2Dialog('close');
                    }
                });
                boxButtons.unshift({
                    // delete event
                    'class': 'event-delete-button',
                    html: '<span title="<?php 
                        echo CHtml::encode (Yii::t('app', 'Delete')); 
                    ?>" class="fa fa-trash fa-lg"></span>', 
                    click: function() {
                        var deleteMsg = '<?php
                            echo Yii::t('calendar','Are you sure you want to delete this {action}?',array(
                                '{action}' => lcfirst(Modules::displayName(false, "Actions"))
                            ));
                        ?>';
                        if(confirm(deleteMsg)) {
                            // delete event from database
                            $.post('<?php echo $urls['deleteAction']; ?>', {id: event.id}); 
                            $('#calendar').fullCalendar('removeEvents', event.id);
                            $(this).x2Dialog('close');
                        }
                    }
                });
                boxButtons.unshift({
                    html: '<span title="<?php 
                        echo CHtml::encode (Yii::t('app', 'Copy')); 
                    ?>" class="fa fa-copy fa-lg"></span>', 
                    'class': 'event-copy-button',
                    click: function() {
                        var dialogOuter$ = $(this).closest ('.ui-dialog');
                        dialogOuter$.find ('.event-copy-button').hide ();
                        dialogOuter$.find ('.ui-dialog-title').append ($('<span>', {
                            html: '&nbsp;<?php echo CHtml::encode (Yii::t('app', '(Copy)')); ?>'
                        }));
                        var that = this;  
                        dialogOuter$.find ('.event-delete-button').unbind ('click').
                            bind ('click', function () {
                                $(that).x2Dialog ('close');
                            });
                        dialogOuter$.find ('.save-event-button').unbind ('click').bind ('click',
                            function () {
                                $.ajax({
                                    type: 'post',
                                    url: yii.scriptUrl + '/actions/copyEvent?id=' + event.id,
                                    data: $(viewAction).find('form').serializeArray(),
                                    success: function() {
                                        $('#calendar').fullCalendar('refetchEvents');
                                    }
                                }); 
                                $(that).x2Dialog('close');
                            });
                    }
                });
                /*if (event.type === 'event') {
                    boxButtons.unshift({
                        html: '<span title="<?php 
                            echo CHtml::encode (Yii::t('app', 'Send invitation')); 
                        ?>" class="fa fa-envelope-o fa-lg"></span>', 
                        'class': 'event-email-button',
                        click: function() {
                        }
                    });
                }*/
            } else { // non-editable event/action
                $.post(
                    '<?php echo $urls['viewAction']; ?>', {
                        'ActionId': event.id, 
                        'IsEvent': event.type=='event'
                    }, function(data) {
                        $(viewAction).append(data);
                        //open dialog after its filled with action/event
                        viewAction.x2Dialog('open'); 
                    }
                );
            }

            if(event.associationType == 'calendar') { // calendar event clicked
                var boxTitle = 'Event';
            } else if(event.associationType != '' && event.associationType != 'contacts' && 
                      event.associationType != undefined) {

                if(typeof associations[event.associationType]!='undefined'){
                    var associationType=associations[event.associationType];
                }else{
                    var associationType=event.associationType;
                }

                if(event.linked) {
                    viewAction.prepend(
                        '<b><a href="' + event.associationUrl + '">' + event.associationName + 
                        '</a></b><br />');
                }

                boxButtons.unshift({  //prepend button
                    text: '<?php echo CHtml::encode (Yii::t('calendar', 'View')); ?> '+ 
                        associationType,
                    click: function() {
                        window.location = event.associationUrl;
                    }
                });

                if(event.editable && event.type != 'event') {
                    if(event.complete == 'Yes') {
                        boxButtons.unshift({  // prepend button
                            text: '<?php echo CHtml::encode (Yii::t('actions', 'Uncomplete')); ?>',
                            click: function() {
                                $.post('<?php echo $urls['uncompleteAction']; ?>', {id: event.id});
                                event.complete = 'No';
                                $(this).x2Dialog('close');
                            }
                        });
                    } else {
                        boxButtons.unshift({  // prepend button
                            html: '<span title="<?php 
                                echo CHtml::encode (Yii::t('actions', 'Complete')); 
                            ?>" class="fa fa-check fa-lg"></span>', 
                            click: function() {
                                $.post('<?php echo $urls['completeAction']; ?>', {id: event.id});
                                event.complete = 'Yes';
                                $(this).x2Dialog('close');
                            }
                        });
                    }
                }
            } else if(event.associationType == 'contacts') { 
                // action associated with a contact clicked
                if(event.type == 'event')
                    boxTitle = '<?php echo Yii::t('calendar','Contact Event') ?>';
                else
                    boxTitle = '<?php echo Yii::t('calendar','Contact Action') ?>';

                if(event.linked) {
                    viewAction.prepend(
                        '<b><a href="' + event.associationUrl + '">' + event.associationName + 
                        '</a></b><br />');
                }

                boxButtons.unshift ({
                    lineBreak: true
                });
                boxButtons.unshift({  //prepend button
                    text: '<?php echo CHtml::encode (Yii::t('contacts', 'View {contact}', array(
                        '{contact}' => Modules::displayName(false, "Contacts"),
                    ))); ?>',
                    'class': 'view-contact-button',
                    click: function() {
                        window.location = event.associationUrl;
                    }
                });
                if(event.editable && event.type != 'event') {
                    if(event.complete == 'Yes') {
                        boxButtons.unshift({  // prepend button
                            text: '<?php echo CHtml::encode (Yii::t('actions', 'Uncomplete')); ?>',
                            click: function() {
                                $.post('<?php echo $urls['uncompleteAction']; ?>', {
                                    id: event.id});
                                event.complete = 'No';
                                $(this).x2Dialog('close');
                            }
                        });
                    } else {
                        boxButtons.unshift({  // prepend button
                            text: '<?php 
                                echo CHtml::encode (Yii::t('actions', 'Complete')); ?>',
                            click: function() {
                                $.post('<?php echo $urls['completeAction']; ?>', {id: event.id});
                                event.complete = 'Yes';
                                $(this).x2Dialog('close');
                            }
                        });
                        boxButtons.unshift({  // prepend button
                            text: '<?php echo CHtml::encode (
                                Yii::t('actions', 'Complete and View {contact}', array(
                                    '{contact}' => Modules::displayName(false, "Contacts"),
                                ))); ?>',
                            click: function() {
                                $.post('<?php echo $urls['completeAction']; ?>', {id: event.id});
                                window.location = event.associationUrl;
                            }
                        });
                    }
                }
            } else { // action clicked
                var boxTitle = 'Action';
                if(event.editable) {
                    if(event.complete == 'Yes') {
                        boxButtons.unshift({  // prepend button
                            text: '<?php echo CHtml::encode (Yii::t('actions', 'Uncomplete')); ?>',
                            click: function() {
                                $.post('<?php echo $urls['uncompleteAction']; ?>', {id: event.id});
                                event.complete = 'No';
                                $(this).x2Dialog('close');
                            }
                        });
                    } else {
                        boxButtons.unshift({  // prepend button
                            text: '<?php echo CHtml::encode (Yii::t('actions', 'Complete')); ?>',
                            click: function() {
                                $.post('<?php echo $urls['completeAction']; ?>', {id: event.id});
                                event.complete = 'Yes';
                                $(this).x2Dialog('close');
                            }
                        });
                    }
                }
            }
            

            var buttonpaneHeight;
            //var textareaHeight;

            viewAction.x2Dialog({
                title: boxTitle,
                dialogClass: 'calendarViewEventDialog',
                autoOpen: false,
                resizable: true,
                height: 'auto',
                width: dialogWidth,
                show: 'fade',
                hide: 'fade',
                buttons: boxButtons,
                open: function() {
                    $('.ui-dialog-buttonpane').find('button:contains(\"' + focusButton + '\")')
                        .addClass('highlight')
                        .focus();
                    $('.ui-dialog-buttonpane').find('button').css('font-size', '0.85em');
                    $('.ui-dialog-title').css('font-size', '0.8em');
                    $('.ui-dialog-titlebar').css('padding', '0.2em 0.4em');
                    $('.ui-dialog-titlebar-close').css({
                        'height': '18px',
                        'width': '18px'
                        });
                    $(viewAction).css('font-size', '0.75em');
                },
                close: function () {
                      $('[id="dialog-content_' + event.id + '"]').remove ();
                    cleanUpDialog ();
                },
                resizeStart: function () {
                    // resize buttonpane init
                      /*var elem = $(this).parents ('.ui-dialog');
                    buttonpaneHeight = $(elem).find ('.ui-dialog-buttonpane').height ();*/

                    // resize textarea init
                    //textareaHeight = $(this).find ('textarea').height ();
                },
                resize: function (event, ui) {
                    // resize buttonpane to make room for stacked buttons
                 /*     var elem = $(this).parents ('.ui-dialog');
                    var newButtonpaneHeight = $(elem).find ('.ui-dialog-buttonpane').height ();
                    if (newButtonpaneHeight !== buttonpaneHeight) {
                         $(elem).height ($(elem).height () + (newButtonpaneHeight - buttonpaneHeight));
                    }*/

                    // resize textarea
                    /*if (ui.size !== ui.originalSize) {
                        var textarea = $(this).find ('textarea');
                        if (textarea.length !== 0) {
                            $(textarea).height (textareaHeight + (ui.size.height - ui.originalSize.height));
                        }
                    }*/
                }
            });
        },
        editable: true,
        // translate (if local not set to english)
        buttonText:     <?php echo X2Calendar::translationArray('buttonText') ?>,
        monthNames:     <?php echo X2Calendar::translationArray('monthNames') ?>,
        monthNamesShort:<?php echo X2Calendar::translationArray('monthNamesShort') ?>,
        dayNames:       <?php echo X2Calendar::translationArray('dayNames') ?>,
        dayNamesShort:  <?php echo X2Calendar::translationArray('dayNamesShort') ?>,

    });
    
    /*
    *   This section is meant to pre-render the events given to it on loading, 
    *   but causes an amount of problems due to event sources.

    $('#calendar').fullCalendar('addEventSource', <?php //echo CJSON::encode($events) ?>);

    var pushed = false;
    // Once the view is switched, erase the prerendered events and add the others
    $('.fc-button-next, .fc-button-prev').bind('click', function(event){
        //$('.fc-button-next, .fc-button-prev').unbind(event); should work but doesnt...
        if(pushed){
            return;
        }
        pushed = true;
        $('#calendar').fullCalendar('removeEventSources');

        <?php echo $checkedUserCalendars; ?>

    });*/

<?php echo $checkedUserCalendars; ?>

    });

     /* Begin: calendar export code (generates URL for third-party ical format reader) */
    x2CalendarExporter = {'users':<?php echo json_encode($userCalendars); ?>};
    // Adds or removes a user from the calendar export URL
    x2CalendarExporter.toggleUser = function(user,add) {
        // New list of user/group calendars
        var newList = [];
        var found = false;
        if(add) {
            for (var i=0;i<this.users.length;i++) {
                newList.push(this.users[i]);
                if (this.users[i] == user)
                    found = true;
            }
            if(!found)
                newList.push(user);
        } else {
            for (var i=0;i<this.users.length;i++) {
                if (this.users[i] != user)
                    newList.push(this.users[i]);
            }
        }
        this.users = newList;
        this.updateUrl();
    };
    
    x2CalendarExporter.updateUrl = function() {
        <?php
        $userModel = Yii::app()->suModel;
        if($userModel->calendarKey == '') {
            // Set a calendar key if one hasn't already been set
            $userModel->calendarKey = EncryptUtil::secureUniqueIdHash64();
            $userModel->update(array('calendarKey'));
        }
        ?>
        var url = <?php echo json_encode($this->createAbsoluteUrl('/calendar/calendar/ical',array(
            'user'=>Yii::app()->user->name,
            'key' => $userModel->calendarKey
        ))); ?>+'?calendars='+this.users.join(',');
        var container = $('#ical-export-url');
        container.find('a#ical-export-url-link').attr('href',url);
        container.find('input#ical-export-url-field').val(url);
    };

    x2CalendarExporter.updateUrl();
    /* End calendar export code */ 

    // view/hide actions associated with a user
    function toggleUserCalendarSource(user, on) {
        if(user == '')
            user = 'Anyone';
        if(on) {
            $('#calendar').fullCalendar('addEventSource', {
                url: '<?php echo $urls['jsonFeed']; ?>?calendarId=' + user
            });
        } else {
            $('#calendar').fullCalendar('removeEventSource', {
                url: '<?php echo $urls['jsonFeed']; ?>?calendarId=' + user
            });
            //This is to remove the prepopulated events
            $('#calendar').fullCalendar('removeEvents', function(event){
                if( event.calendarAssignment == user ){
                    return true;
                }
            });
        }
        // Update the calendar share/export URL:
         x2CalendarExporter.toggleUser(user,on); 

        $.post('<?php echo $urls['saveCheckedCalendar']; ?>', {
            Calendar: user, Checked: on, Type: 'user'
        });
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

    // Put the function in this scope
    function giveSaveButtonFocus() {
        return x2.calendarManager.giveSaveButtonFocus();
    }

    $(function () {
    x2.layoutManager.setUpCalendarTitleBarResponsiveness ();
    //x2.layoutManager.setHalfWidthSelector ('#calendar, #publisher-outer');
    //x2.layoutManager.setHalfWidthThreshold (<?php echo $halfWidthThreshold; ?>);
    $('#calendar .day-number-link').attr ('title', '<?php echo Yii::t('app', 'Show Day View'); ?>');
    $(window).resize ();
});

</script>

<div id="calendar">

</div>

<div id="publisher-outer">
<?php

$this->widget('Publisher', array(
    'associationType' => 'calendar',
    'tabs' => array (
        new PublisherCalendarEventTab ()
    ),
    'selectedTab' => 'new-event'
));
?>
</div>
