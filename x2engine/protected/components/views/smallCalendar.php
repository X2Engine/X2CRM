<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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

/**
* This file renders the SmallCalendar widget
* The code, is messy as most is taken from the calendar module, 
* Both could use a refactoring, for there is code duplication.
**/


Yii::app()->clientScript->registerCss('calendarFormatting','
    #small-calendar .fc-view-month .fc-event{
        display: none;
    }


    #small-calendar{
        border: none;
    }

    #small-calendar .fc-header-title h2{
        background-image: none;
        padding-left: 15px;
    }

    #widget_SmallCalendar .portlet-content{
        padding: 0px;
    }

    #widget_SmallCalendar #header-title{
        display: inline;
    }

    #small-calendar .fc-header{
        border-radius: 0px;
    }

    #small-calendar .fc-header-title {
        display:none;
    }   

    #small-calendar .fc-button{
        float: left;
    }

    #small-calendar .fc-header-left{
        padding-left:3px;
        padding-top:2px;
        width: 100%;
    }

    #small-calendar .fc-day{
        height: 0px !important;
        background-color: white; 
        cursor: pointer; cursor: hand; 
    }

    #small-calendar .fc-day:hover{
        background: #CACACA;
    }

    #small-calendar .day-number-link{
        font-size: 14pt;
        text-decoration: none;
    }

    #small-calendar .fc-day-number{
        float: none;
        text-align: center;
        padding-top: 10px;
        // margin-top:18px;
    }


    #small-calendar .fc-view-month .fc-today{
        border: 1px solid #4078C3;
        background: #82A4D1;
    }

    #small-calendar .day-number-link:hover{
        /* color: #666666; */
    }

    #small-calendar .fc-today .day-number-link{
        color: #FFFFFF;
    }

    #small-calendar .ui-widget-content {
        border-bottom: none;
    }

    #small-calendar .ui-widget-header {
        border-left: none;
    }

    #small-calendar .fc-first {
        border-left: none;
    }

    #small-calendar .fc-last {
        border-right: none;
    }

    #small-calendar .fc-week.fc-last .fc-day{
        border-bottom: none;
    }

    #small-calendar .fc-agenda-axis{
        border-left: none;
        border-bottom: none;
    }

    #small-calendar .fc-day-content .spacer{
        display:none;
    }

    #small-calendar .fc-event-indicator{
        width:8px;
        height:8px;
        background: #4078C3;
        margin: auto;
        border-radius: 5px;
        // float:left;
        display:inline-block;
    }

    #small-calendar .fc-indicator-container{
        height: 10px !important;
        display: block !important;
        text-align: center;
        padding: 2px;
        padding-top: 0px;
        overflow: hidden;
    }

    #small-calendar .x2-button-group{
        float:left;
        margin-right: -15px;
    }

    #small-calendar .x2-button-group .x2-button {
        padding-left: 7px;
        padding-right: 7px;
        display: inline;
    }

    #small-calendar #add-button{
        margin: 3px;
        float: right;
        margin-left: 0px;
    }

    #small-calendar .fc-header-space {
        display: none;
    }

    #small-publisher {
        padding: 0px;
    }

    #small-publisher #publisher-tabs-row-1{
        display: none !important;
    } 

    #small-publisher .form {
        border: none !important;
    }

    .ui-datepicker {
        z-index: 1200 !important;
    }


    ');



Yii::app()->clientScript->registerScript("smallCalendarJS", "


// Put the function in this scope
function giveSaveButtonFocus() {
    return x2.calendarManager.giveSaveButtonFocus();
}

$(function(){
    x2.calendarManager.calendar = '#small-calendar';
    // Initialize the calendar, ensure that only one is present
    if($('#small-calendar .fc-content').length > 0)
        return;

    // Singleton class to render the inidcators on the calendar
    var indicator = {

        dayIndicators: [],

        /**
        *  Adds an event indicator to the calendar
        *  @param event event full calendar event to be added
        *  @param view view the full calendar current view
        */
        addEvent: function(event, view){
            if (view.name !== 'month')
                return;

            var eventStart = new Date(event.start.valueOf());

            // This is to only show indicators for the current month +/- a margin
            var viewStart = new Date(view.start);
            var viewEnd = new Date(view.end);
            viewStart.setDate( viewStart.getDate() - 7 )
            viewEnd.setDate( viewEnd.getDate() + 14 )

            // If event starts before the view, move it up.
            if( event.start.valueOf() < viewStart.valueOf() ){
                eventStart = viewStart;
            }

            // put this function in the scope for readability
            yyyymmdd = x2.calendarManager.yyyymmdd; 

            // Add array of dates and colors for the indicators
            var dates = [yyyymmdd(eventStart)];

            //Handing if an event spans more than One day
            if(event.end){
                var eventEnd = new Date(event.end.valueOf() - 1000);

                if( event.end.valueOf() > viewEnd.valueOf() ){
                    eventEnd = viewEnd;
                }

                var dateEnd = yyyymmdd(eventEnd);

                //If the event start is after then end, just display one blip at the end
                if( eventStart.valueOf() > eventEnd.valueOf() ){
                    dates = [dateEnd]
                } else {
                    // For every day in the event, 
                    // add the next day to the dates array
                    var newDate = new Date(eventStart);
                    for(var i = 0; i < 42; i++){
                        if(dates[ dates.length - 1 ] == dateEnd)
                            break;


                        newDate.setDate( newDate.getDate() + 1 );

                        dates.push( yyyymmdd(newDate) );

                    }
                }
            }

            
            var indicator_count = {};
            // For each date in the array create an event indicator
            for(var i in dates){
                
                // If it is already in the array, do not add it again
                var contained = false;
                for( var j in this.dayIndicators ){
                    if (this.dayIndicators[j].date === dates[i]) {
                        //otherwise 
                        if (this.dayIndicators[j].color == event.color){
                            contained = true;
                            this.dayIndicators[j].count++;
                            break;
                        } 
                    }
                }

                if(!contained)
                    this.dayIndicators.push({date: dates[i], color: event.color, count: 1});
            }
        },

        render: function(){
            //Remove previous indicators
            $('#small-calendar .fc-day .fc-indicator-container').children().remove();

            for(var i in this.dayIndicators){
                // if (i>5) continue;

                var event = this.dayIndicators[i];

                var dayContainer = '#small-calendar .fc-day[data-date=\"'+event.date+'\"] .fc-indicator-container';

                $('<div></div>').appendTo( $(dayContainer) ).
                addClass('fc-event-indicator').
                css('background-color', event.color).
                attr('event-color', event.color).
                attr('title', event.count+' event'+ (event.count > 1 ? 's' : '' ));
            }
        }

    };

    var minimizeElement = $('#widget_SmallCalendar #widget-dropdown .portlet-minimize');
    
    $('#small-calendar').fullCalendar({
        theme: true,
        header: {
            left: 'title',
            center: '',
            right: 'month agendaDay prev,next'
        },
        eventRender: function(event, element, view) {
            indicator.addEvent(event, view);
        },

        dayClick: function(date, allDay, jsEvent, view) {
                if( view.name == 'agendaDay') {
                    x2.calendarManager.insertDate(date, view);
                }

                if( view.name == 'month') {
                    $('#small-calendar').fullCalendar ('gotoDate', date);
                    $('#small-calendar').fullCalendar ('changeView', 'agendaDay');
                } 
            // if ($(jsEvent.target).hasClass ('day-number-link')) {
                // }
        },

        viewRender: function(view){
            indicator.dayIndicators = [];

            var title = $('#widget_SmallCalendar #widget-dropdown');
            title.html('');
            title.append('<div id=\"header-title\">'+view.title+'</div>');
            title.append(minimizeElement);

            $('#small-calendar .x2-button').removeClass('disabled-link');
            $('#small-calendar .fc-button-'+view.name).addClass('disabled-link');
        },

        eventAfterAllRender: function(view){
            indicator.render();
        },

        eventDrop: function(event, dayDelta, minuteDelta, allDay, revertFunc) { 
            $.post('$urls[moveAction]', {
                    id: event.id, dayChange: dayDelta, minuteChange: minuteDelta, isAllDay: allDay
                });
        },
        eventResize: function(event, dayDelta, minuteDelta, revertFunc) {
            $.post('$urls[resizeAction]', {
               id: event.id, dayChange: dayDelta, minuteChange: minuteDelta});
        },
        eventClick: function(event){
            if ($('[id=\"dialog-content_' + event.id + '\"]').length != 0) { 
                return;
            }

            var boxButtons =  [ // buttons on bottom of dialog
                {
                    text: '".CHtml::encode (Yii::t('app', 'Close'))."',
                    click: function() {
                        $(this).dialog('close');
                    }
                }
            ];
            
            var viewAction = $('<div></div>', {id: 'dialog-content' + '_' + event.id}); 

            if(event.editable){
                var boxTitle = '".Yii::t('calendar', 'Edit Calendar Event')."';
                boxButtons.unshift({
                            text: '".CHtml::encode (Yii::t('app', 'Save'))."', // delete event
                            click: function() {
                                // delete event from database
                                $.post(
                                    '$urls[saveAction]?id=' + event.id, 
                                    $(viewAction).find('form').serialize(),
                                    function() {
                                        $('#small-calendar').fullCalendar('refetchEvents');
                                    }
                                ); 
                                $(this).dialog('close');
                            }
                        });
                boxButtons.unshift({
                    text: '".CHtml::encode (Yii::t('app', 'Delete'))."', // delete event
                    click: function() {
                        if(confirm('".Yii::t("calendar","Are you sure you want to delete this event?")."')) {
                            // delete event from database
                            $.post('$urls[deleteAction]', {id: event.id}); 
                            $('#small-calendar').fullCalendar('removeEvents', event.id);
                            $(this).dialog('close');
                        }
                    }
                });
                $.post(
                    '$urls[editAction]', {
                        'ActionId': event.id, 'IsEvent': event.type=='event'
                    }, function(data) {
                        $(viewAction).append(data);
                        //open dialog after its filled with action/event
                        viewAction.dialog('open'); 
                    }
                );
            } else {
                var boxTitle = '".Yii::t('calendar', 'View Calendar Event')."';
                $.post(
                    '$urls[viewAction]', {
                        'ActionId': event.id, 
                        'IsEvent': event.type=='event'
                    }, function(data) {
                        $(viewAction).append(data);
                        //open dialog after its filled with action/event
                        viewAction.dialog('open'); 
                    }
                );
            }


            // Dialog box that pops up when 
            // an event is clicked. 
            viewAction.dialog({
                title: boxTitle,
                dialogClass: 'calendarViewEventDialog',
                autoOpen: false,
                resizable: true,
                height: 'auto',
                width: 300,
                position: {my: 'right-12', at: 'left bottom', of: '#small-calendar'}, 
                show: 'fade',
                hide: 'fade',
                buttons: boxButtons,
                open: function() {
                    $('.ui-dialog-buttonpane').find('button:contains(\"".Yii::t('app', 'Close')."\")')
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
                      $('[id=\"dialog-content_' + event.id + '\"]').remove ();
                    // cleanUpDialog ();
                },
                resizeStart: function () {
                },
                resize: function (event, ui) {
                }
            });
        },
        editable: true,
        // translate (if local not set to english)
        buttonText:     ".X2Calendar::translationArray('buttonText').",
        monthNames:     ".X2Calendar::translationArray('monthNames').",
        monthNamesShort:".X2Calendar::translationArray('monthNamesShort').",
        dayNames:       ".X2Calendar::translationArray('dayNames').",
        dayNamesShort:  ".X2Calendar::translationArray('dayNamesShort').",

    });



    // Initialize calendar sources 
    // By fetching the checked user calendars
    var calendars = $showCalendars;
    var urls = [];

    for (var i in calendars.userCalendars){
        urls.push('$urls[jsonFeed]?user='+calendars.userCalendars[i]);
    }

    for (var i in calendars.groupCalendars){
        urls.push('$urls[jsonFeedGroup]?groupId='+calendars.groupCalendars[i]);
    }

    // Add the event sources to the calendar
    for (var i in urls){
        $('#small-calendar').fullCalendar('addEventSource', {url: urls[i]});      
    }

    // Make header a link to the full calendar 
    // $('#small-calendar .fc-header-title h2').wrap('<a href=\"$urls[index]\"></a>').
    // attr('title', 'Go to full calendar');
     
    // New event button Opens dialog
    $('#small-calendar-container #add-button').click(function(evt){
        $('#small-publisher').dialog('open');
    });

    var headerRight = $('#small-calendar .fc-header-right').hide();
    var headerLeft = $('#small-calendar .fc-header-left').append($('<div class=\"x2-button-group\" ></div>'));

    headerLeft.find('.x2-button-group').append(headerRight.children());
    $('#small-calendar #add-button').appendTo(headerLeft).show();

    // remove the hash that scrolls to teh top of the page
    $('#small-calendar .day-number-link').attr('href','javascript:;');

    var savedForm;
    var savedTab;
    /**
    * Dialog for the pop up publisher
    */
    $('#small-publisher').dialog({
        title: 'New Calendar Event',
        dialogClass: 'calendarViewEventDialog',
        autoOpen: false,
        resizable: true,
        height: 'auto',
        width: 400,
        position: {my: 'right-12', at: 'left center', of: '#small-calendar'}, 
        show: 'fade',
        hide: 'fade',
        open: function() {
            // if(typeof x2.publisher._selectedTab !== 'undefined')
            savedTab = x2.publisher._selectedTab.id;
            savedForm = x2.publisher._form;

            x2.publisher.switchToTab('new-event');
            x2.publisher._selectedTab._form = $('#small-publisher .form');
            var view = $('#small-calendar').fullCalendar('getView');

            if( view.name === 'agendaDay')
                x2.calendarManager.insertDate(view.start);

            $('#small-publisher').show();
            $('#small-publisher #event-action-description').focus();
            $('#small-publisher #event-action-description').removeAttr('disabled');
            $('#small-publisher input').removeAttr('disabled');

        },
        close: function () {
            // x2.publisher.reset();
            // if(typeof savedTab !== 'undefined')
            x2.publisher._form = savedForm;
            x2.publisher.switchToTab(savedTab);
        },
        resizeStart: function () {
        },
        resize: function (event, ui) {
        }
    });
    
});


", CClientScript::POS_HEAD);
?>

<div id='small-calendar-container'>
    <div id='small-calendar'>
            <span style='display:none;' class="x2-button fc-button highlight" id="add-button" type='button' />
            <?php echo Yii::t('apps','Add Event') ?>
            </span>
    </div>
    <div id='small-publisher' style="display:none">
        <?php
        // if echoTabRow is already present, there are two publishers
        $doublePublisher = function_exists('echoTabRow');
        $this->widget('Publisher', array(
            'associationType' => 'calendar',
            'tabs' => array (
                new PublisherEventTab ()
            )
        ));
        ?>
    </div>
</div>



<script type="text/javascript">
// Closes dialog when a different publisher is selected
(function(tabSelected) {
  x2.publisher.tabSelected = function (event, ui) {
    $('#small-publisher').dialog('close');
    tabSelected.call(this, event, ui);
  };
}(x2.publisher.tabSelected));

//Refetches after the dialog is submitted
(function(updates){
x2.Publisher.prototype.updates = function () {
        $('#small-calendar').fullCalendar('refetchEvents'); // refresh calendar
        $('#small-publisher').dialog('close');
        updates.call(this);
    };
}(x2.Publisher.prototype.updates));

// Switches to the log-a-call tab since
// creating the new event publisher switches tabs
$(function() { 
    x2.publisher.switchToTab(auxlib.keys(x2.publisher._tabs)[0]); 
});

// This section must only be used when there are two publishers
// Changes the form that the submit button submits
// Publisher modifications that need to be called after the publisher is created
<?php 
    if ($doublePublisher) {
?>
        $('#small-publisher #save-publisher').unbind('click');
        $('#small-publisher #save-publisher').click (function (evt) {
            var that = x2.publisher;
            that._form=$('#small-publisher #publisher-form');
            evt.preventDefault ();
            if (!that.beforeSubmit ()) {
                return false;
            }
            that._selectedTab.submit (that, that._form);
            return false;
        });
    
<?php
    }
?>
</script>
