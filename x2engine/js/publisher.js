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

if(typeof x2 == 'undefined')
    x2 = {};

if(typeof x2.publisher == 'undefined')
    x2.publisher = {};

// Dependency: actionFrames.js
if(typeof x2.actionFrames == 'undefined')
    x2.actionFrames = {};

x2.publisher.elements = {};

x2.publisher.isCalendar = false;

/**
 * "Magic getter" method which caches jQuery objects so they don't have to be
 * looked up a second time from the DOM
 */
x2.publisher.getElement = function (selector) {
    if(typeof x2.publisher.elements[selector] == 'undefined')
        x2.publisher.elements[selector] = x2.publisher.form.find(selector);
    return x2.publisher.elements[selector];
};

/**
 * Clears the publisher of input, i.e. after each use.
 */
x2.publisher.reset = function () {
    
    // reset group checkbox (if checked)
    if(x2.publisher.getElement('#groupCheckbox').is(':checked')) {
        x2.publisher.getElement('#groupCheckbox').click(); // unchecks group checkbox, and calls ajax function to restor Assigned To to list of users
    }

    // reset textarea and dropdowns
    $('#publisher-form select, #publisher-form input[type=text], #publisher-form input[type=number], #publisher-form textarea').each(function(i) {
        $(this).val($(this).data('defaultValue'));
    });

    // reset checkboxes
    x2.publisher.getElement('#publisher-form input[type=checkbox]').each(function(i) {
        $(this).attr('checked', $(this).data('defaultValue'));
    });

    // reset save button
    x2.publisher.getElement('#save-publisher').removeClass('highlight');
};

/**
 * Change the mode of the publisher form based on a selected tab.
 *
 * @param selectedTab ID of the tab.
 */
x2.publisher.switchToTab = function (selectedTab) {
    // set field SelectedTab for use in POST request
    x2.publisher.getElement('#SelectedTab').val(selectedTab);

    // List of element IDs displayed in each tab
    var tabUsesIDs = [
        ['log-a-call', [
            // Start = due date
            ['action-start-time-label','block'],
            ['action-due-date','block'],
            // End = date completed
            ['action-end-time-label','block'],
            ['action-complete-date','block'],
            ['action-duration','block']
        ]],
        ['new-action', [
            ['action-assignment-dropdown','inline-block'],
            ['action-assigned-to-label','block'],
            ['action-due-date','inline-block'],
            ['action-due-date-label','block'],
            ['action-priority','inline-block'],
            ['action-priority-label','block'],
            ['action-visibility-label','block'],
            ['action-visibility-dropdown','inline-block']
        ]],
        ['new-comment', []],
        ['log-time-spent', [
            // Start = due date
            ['action-start-time-label','block'],
            ['action-due-date','block'],
            // End = date completed
            ['action-end-time-label','block'],
            ['action-complete-date','block'],
            ['action-duration','block']
        ]],
        ['new-event',[
            ['action-duration','block'],
            ['action-assigned-to-label','block'],
            ['action-assignment-dropdown','inline-block'],
            ['action-color-dropdown','inline-block'],
            ['action-color-label','block'],
            // Priority
            ['action-priority','inline-block'],
            ['action-priority-label','block'],
            // Start = due date
            ['action-start-date-label','block'],
            ['action-due-date','inline-block'],
            // End = date completed
            ['action-end-date-label','block'],
            ['action-complete-date','inline-block'],
            // Visibility control
            ['action-visibility-label','block'],
            ['action-visibility-dropdown','inline-block']

        ]]
    ];

    // give the container an id associated with the current tab for the purposes of tab specific
    // css
    $('.form.publisher').attr ('id', selectedTab + '-form');

    var enableIDs;
    for(var i1 = 0; i1 < tabUsesIDs.length; i1++) {
        var tabID = tabUsesIDs[i1][0];
        var usesIDs = tabUsesIDs[i1][1];
        if(selectedTab == tabID) {
            enableIDs = usesIDs;
        } else {
            for(var i2 = 0; i2 < usesIDs.length; i2++) {
                x2.publisher.getElement('#'+usesIDs[i2][0])
                    .css('display','none')
                    .each(function() {
                        if(this.nodeName == 'INPUT' || this.nodeName == 'TEXTAREA' || this.nodeName == 'SELECT') {
                            this.disabled = true;
                        }
                    });
            }
        }
    }
    for(var i2 = 0; i2 < enableIDs.length; i2++) {
        x2.publisher.getElement('#'+enableIDs[i2][0])
            .css('display',enableIDs[i2][1])
            .each(function() {
                if(this.nodeName == 'INPUT' || this.nodeName == 'TEXTAREA' || this.nodeName == 'SELECT') {
                    this.disabled = false;
                }
            });
    }
}

/**
 * Callback associated with clicking on a tab:
 */
x2.publisher.tabSelected = function (event, ui) {
    x2.publisher.switchToTab(ui.newTab.attr('aria-controls'));
}

/**
 * In the time tracking feature: update the duration fields based on current
 * values of start/end fields.
 *
 * @param thisId The ID of the time field (beginning or end time) currently being
 *  modified
 * @param otherId the ID of the time field (beginning oro end time) not currently
 *  being modified.
 * @param event The event object
 */
x2.publisher.updateActionDuration = function (thisId,otherId,event) {
    var beginObj = x2.publisher.getElement('#action-due-date');
    var endObj = x2.publisher.getElement('#action-complete-date');
    var thisObj = thisId == '#action-due-date' ? beginObj : endObj;
    var otherObj = otherId == '#action-due-date' ? beginObj : endObj;
    var durationMin = x2.publisher.getElement('#action-duration input[name="timetrack-minutes"]');
    var durationHour = x2.publisher.getElement('#action-duration input[name="timetrack-hours"]');
    if(beginObj.val()=='' || endObj.val() == '') {
        durationMin.val('');
        durationHour.val('');
    } else {
        var startTime = Math.round(beginObj.datepicker('getDate').getTime()/1000);
        var endTime = Math.round(endObj.datepicker('getDate').getTime()/1000);
        if(startTime > endTime)
            startTime = endTime;
        var seconds = endTime-startTime;
        var minutes = Math.floor(seconds/60)%60;
        var hours = Math.floor(seconds/3600);
        durationMin.val(minutes).trigger('change.zeropad');
        durationHour.val(hours).trigger('change.zeropad');
    }
};

/**
 * In the time tracking feature: update the end time field based on the duration
 */
x2.publisher.updateActionEndTime = function (event) {
    var beginObj = x2.publisher.getElement('#action-due-date');
    var endObj = x2.publisher.getElement('#action-complete-date');
    var durationMin = x2.publisher.getElement('#action-duration input[name="timetrack-minutes"]');
    var durationHour = x2.publisher.getElement('#action-duration input[name="timetrack-hours"]');
    var currentTime = new Date;
    var endTime,beginTime;
    var totalDuration,init;
    // Initialize to zero:
    if(durationMin.val() === '')
        durationMin.val(0);
    if(durationHour.val() === '')
        durationHour.val(0);
    totalDuration = parseInt(durationMin.val())*60000 + parseInt(durationHour.val())*3600000;
    // Initialize beginning date to now if unset:
    if(beginObj.val() === '')
        beginObj.datepicker('setDate',currentTime);
    // Initialize end date to beginning date if unset:
    if(endObj.val() == '') {
        endTime = beginObj.datepicker('getDate');
        endObj.datepicker('setDate',endTime);
    } else
        endTime = endObj.datepicker('getDate');
    beginTime = beginObj.datepicker('getDate');
    if(!x2.publisher.isCalendar && (endTime >= currentTime || beginTime.getTime() + totalDuration > currentTime.getTime())) {
        // Push the beginning time back so the end time doesn't go into the future:
        beginObj.datepicker('setDate',new Date(endObj.datepicker('getDate').getTime() - totalDuration));
    } else {
        // Set the end time according to the beginning time and the duration:
        endObj.datepicker('setDate',new Date(beginObj.datepicker('getDate').getTime() + totalDuration));
    }
};

/**
 *
 */
x2.publisher.updates = function () {
    if($('#calendar').length !== 0) // if we are in calendar module
        $('#calendar').fullCalendar('refetchEvents'); // refresh calendar

    if($('.list-view').length !== 0)
        $.fn.yiiListView.update($('.list-view').attr('id'));
}

$(function(){
    x2.publisher.container = $('#publisher');
    x2.publisher.form = $('#publisher-form');

    x2.publisher.getElement('#action-due-date').change(function(){
        x2.publisher.updateActionDuration('#action-due-date','#action-complete-date');
    });
    x2.publisher.getElement('#action-complete-date').change(function(){
        x2.publisher.updateActionDuration('#action-complete-date','#action-due-date');
    });
    x2.publisher.getElement('#action-duration input[name="timetrack-hours"],#action-duration input[name="timetrack-minutes"]')
        .bind('change',x2.publisher.updateActionEndTime)
        .bind('change.zeropad',function(event) {
            var intValue = parseInt(event.target.value);
            var maxValue = parseInt(event.target.getAttribute("max"));
            if(intValue < 10) {
                // Pad with zeros
                event.target.value = '0'+parseInt(event.target.value);
            }/* else if (intValue > 100 && maxValue == 99) {
                // Trim off the first digit
                event.target.value = event.target.value.substring(1);
            } else if(intValue > maxValue) {
                // Set back to zero
                event.target.value = '0';
            }*/
        });
});
