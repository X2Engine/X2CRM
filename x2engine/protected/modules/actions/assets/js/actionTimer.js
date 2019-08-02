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






if (typeof x2 == 'undefined')
    x2 = {};
if (typeof x2.actionTimer == 'undefined')
    x2.actionTimer = {};

x2.actionTimer.elements = {};

x2.actionTimer.startTime = null;

/**
 * Updates the time current object
 */
x2.actionTimer.incrementTime = function() {
    this.elapsed.hours = 0;
    this.elapsed.minutes = 0;
    this.totalElapsed.hours = 0;
    this.totalElapsed.minutes = 0;
    var now = new Date();
    if(this.startTime == null) {
        this.elapsed.seconds = 0;
        this.totalElapsed.seconds = this.initialElapsed.seconds;
    } else {
        this.elapsed.seconds = Math.round((now-this.startTime)/1000);
        this.totalElapsed.seconds = this.elapsed.seconds + this.initialElapsed.seconds;
    }
    this.normalizeTime();
};

x2.actionTimer.getElement = function(selector) {
    if(typeof this.elements[selector] == 'undefined') {
        if(typeof this.container == 'undefined')
            this.container = $('#actionTimer');
        this.elements[selector] = this.container.find(selector);
    }
    return this.elements[selector];
}

/**
 * Distributes seconds into minutes if over 60, similarly for minutes into hours.
 *
 * @param object timeObj (optional) normalize an arbitrary input object with
 *  properties hours, minutes and seconds.
 */
x2.actionTimer.normalizeTime = function(timeObj) {
    var updateSelf = typeof timeObj == 'undefined';
    var time =  updateSelf ? this.elapsed : timeObj;
    if (time.seconds > 60) {
        var overflow = time.seconds - (time.seconds % 60);
        time.minutes += overflow / 60;
        time.seconds = time.seconds % 60;
    }
    if (time.minutes > 60) {
        var overflow = time.minutes - (time.minutes % 60);
        time.hours += overflow / 60;
        time.minutes = time.minutes % 60;
    }
    if(updateSelf) {
        this.elapsed = time;
        // Also fix the total:
        this.totalElapsed = this.normalizeTime(this.totalElapsed);
    } else {
        return time;        
    }
}

/**
 * Displays time elapsed in HH:MM:SS format. If no time object is passed to it,
 * it will display the time of the currently active timer.
 * @param object timeObj
 */
x2.actionTimer.formatDisplay = function(timeObj) {
    var time = typeof timeObj == 'undefined' ? this.elapsed : timeObj;
    var displayElapsed = "";
    var pad = function(i) {
        return (i < 10) ? "0" + i : i;
    }
    displayElapsed += pad(time.hours) + ":";
    displayElapsed += pad(time.minutes) + ":";
    displayElapsed += pad(time.seconds);
    return displayElapsed;
};

/**
 * Format/return a string displaying the total time elapsed.
 */
x2.actionTimer.formatTotal = function() {
    return this.formatDisplay(this.totalElapsed);
}

/**
 * Start or resume the timer.
 */
x2.actionTimer.start = function() {
    this.startTime = new Date();
    this.getElement('#actionTimerStartButton').text(this.text['Stop']);
    this.getElement('#actionTimerStartButton').data('status', true);
    this.getElement('#actionTimerDisplay').text(this.formatDisplay());
    var that = this;
    this.displayUpdater = setInterval(function() {
        that.incrementTime();
        that.getElement('#actionTimerDisplay').text(that.formatDisplay());
        that.getElement('#actionTimerControl-total').text(that.formatTotal());
        if(that.displayInTitle) {
            window.document.title = that.oldTitle+' - '+that.formatDisplay();
        }
    }, 1000);
    this.getElement('#actionTimerLog-form').slideDown();
}

/**
 * Stop the timer and call an optional callback function after all is done.
 * @param closure callback
 */
x2.actionTimer.stop = function(callback) {
    callback = typeof callback == 'undefined' ? function(){} : callback;
    document.title = this.oldTitle;
    var that = this;
    $.ajax({
        url: that.actionUrl + "?stop=1",
        dataType: 'json',
        data: that.getElement('#actionTimerControl-form').serialize(),
        type: 'POST'
    }).done(function(response) {
        that.elapsed = {
            hours: 0,
            minutes: 0,
            seconds: 0
        };
        that.totalElapsed = {
            hours:0,
            minutes:0,
            seconds:response.timeSpent
            };
        that.initialElapsed = {
            hours: 0,
            minutes: 0,
            seconds:response.timeSpent
        };
        that.normalizeTime();
        that.getElement('#actionTimerDisplay').text(that.formatDisplay());
        if(response.error == true)
            alert(response.message);
        else {
            that.getElement('#actionTimerStartButton').text(that.text['Start']);
            that.getElement('#actionTimerStartButton').data('status', false);
            clearInterval(that.displayUpdater);
            that.getElement('#actionTimerControl-total').text(that.formatTotal());
            callback();
        }
    });
}

/**
 * Summarize time spent in the current session and call an optional callback
 */
x2.actionTimer.summarize = function (callback) {
    var callback = typeof callback == 'undefined' ? function(){} : callback;
    var that = this;
    $.ajax({
        url: that.actionUrl + "?summation=1",
        dataType: 'json',
        data: that.getElement('#actionTimerControl-form').serialize(),
        type: 'POST'
    }).done(function(data) {
        var i;
        var sum = 0;
        var currentInterval = 0;
        var timerIds = []; // IDs of timers
        var timerTypes = []; // List of activity types
        var timerTypeSums = {}; // Sum of time spent for each type of time
        var fullInterval = []; //
        // Populate the array of types and determine the interval of time logging
        for(i=0;i<data.length;i++) {
            if(typeof timerTypeSums[data[i].type] == 'undefined') {
                timerTypeSums[data[i].type] = 0;
                timerTypes.push(data[i].type);
            }
            if(typeof fullInterval[0] == 'undefined' || fullInterval[0] > data[i].timestamp)
                fullInterval[0] = data[i].timestamp;
            if(typeof fullInterval[1] == 'undefined' || fullInterval[0] > data[i].endtime)
                fullInterval[1] = data[i].endtime;
        }
        // Compute sums of time spent
        for(i=0;i<data.length;i++) {
            currentInterval = data[i].endtime - data[i].timestamp;
            timerTypeSums[data[i].type] += currentInterval;
            sum += currentInterval;
            timerIds.push(data[i].id);
        }
        var totalTimeObj = that.normalizeTime({
            seconds:sum,
            minutes:0,
            hours:0
        });
        if(typeof fullInterval[0] == 'undefined') {
            var date = new Date();
            fullInterval[0] = date.getTime();
        }
        if(typeof fullInterval[1] == 'undefined')
            fullInterval[1] = fullInterval[0];

        // Prepare the mini-publisher
        //
        // (1) Populate hidden fields:
        that.getElement('#timetrack-timespent').val(sum);
        that.getElement('#timetrack-start').val(fullInterval[0]);
        that.getElement('#timetrack-end').val(fullInterval[1]);
        that.getElement('#timetrack-timers').val(timerIds.join(','));
        // (2) Fill in the description
        var descriptionField = that.getElement('#timetrack-log-description');
        var currentDescription = descriptionField.val();
        var timeUsageDescription = '';
        for(i=0;i<timerTypes.length;i++) {
            var timeObj = that.normalizeTime({
                seconds: timerTypeSums[timerTypes[i]],
                minutes: 0,
                hours: 0
            });
            timeUsageDescription += that.text[timerTypes[i]] + ": " + that.formatDisplay(timeObj)+"\n";
        }
        descriptionField.val(timeUsageDescription.trim() + (currentDescription? "\n" + currentDescription : ""));
        callback();
    });
}

x2.actionTimer.afterSubmitLog = function () {
    this.getElement("#actionTimerLog-form").slideUp();
    this.softReset();
    x2.publisher.updates();
    $(document).trigger("newlyPublishedAction");
}

/**
 * Deletes timer records and clears the form/timer
 */
x2.actionTimer.reset = function () {
    var that = this;
    $.ajax({
        url: that.actionUrl + "?reset=1",
        dataType: 'json',
        data: that.getElement('#actionTimerControl-form').serialize(),
        type: 'POST'
    }).done(function(response) {
        that.softReset();
    });
}

/**
 * Clears the form/timer.
 */
x2.actionTimer.softReset = function () {
    document.title = this.oldTitle;
    this.elapsed = {
        hours:0,
        minutes:0,
        seconds:0
    };
    this.totalElapsed = {
        hours:0,
        minutes:0,
        seconds:0
    };
    this.initialElapsed = {
        hours:0,
        minutes:0,
        seconds:0
    }
    this.getElement('#actionTimerDisplay').text('00:00:00');
    this.getElement('#actionTimerControl-total').text('00:00:00')
    this.getElement('#actionTimerStartButton').text(this.text["Start"]);
    this.getElement('#actionTimerStartButton').data('status', false);
    this.getElement('#timetrack-log-description').val('');
    this.getElement('#actionTimerLog-form').slideUp();
    clearInterval(this.displayUpdater);
}

x2.actionTimer.submitLogForm = function (callback) {
    var callback = typeof callback == 'undefined' ? function(){} : callback;
    var that = this;
    $.ajax({
        type:'POST',
        data: [{
            name: 'x2ajax',
            value: 1
        }].concat (that.getElement('#actionTimerLog-form').serializeArray()),
        url:that.getElement ('#actionTimerLog-form').attr ('action')
    }).done(callback);
}

jQuery(document).ready(function ($) {

    x2.actionTimer.getElement('#actionTimerStartButton').bind('click',function(event) {
        if (x2.actionTimer.getElement('#actionTimerStartButton').data('status') == true) {
            // Stop timer w/o callback.
            x2.actionTimer.stop();
        }
        else {
            // Make server call to initiate timer record
            $.ajax({
                url: x2.actionTimer.actionUrl,
                dataType: 'json',
                data: $(this).parents('form').serialize(),
                type: 'POST'
            }).done(function(response) {
                if(response.error == true)
                    alert(response.message);
                else {
                    // Start timer
                    x2.actionTimer.start();
                }
            });
        }
    });

    x2.actionTimer.getElement('#timerReset').click(function () {
        x2.actionTimer.reset();
    });

    x2.actionTimer.getElement('#actionTimerLog-submit').click(function (event){
        event.preventDefault();
        if (x2.actionTimer.getElement('#actionTimerStartButton').data('status') == true){
            // Stop the timer first, then summarize, then save:
            x2.actionTimer.stop(function () {
                x2.actionTimer.summarize(function () {
                    x2.actionTimer.submitLogForm(function () {
                        x2.actionTimer.afterSubmitLog();
                    });
                });
            });
        } else {
            // Just summarize and save; currently no active timer.
            x2.actionTimer.summarize(function () {
                x2.actionTimer.submitLogForm(function () {
                    x2.actionTimer.afterSubmitLog();
                });
            });
        }
    });

    $(window).bind("beforeunload",function(){
        if(x2.actionTimer.getElement("#actionTimerStartButton").data("status")) {
            x2.actionTimer.stop();
            x2.DEBUG && console.log('Fired action timer beforeunload or blur event handler.');
        }
    });

});
