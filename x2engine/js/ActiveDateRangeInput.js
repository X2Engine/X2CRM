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




x2.ActiveDateRangeInput = (function () {

function ActiveDateRangeInput (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        decrementStartDate: false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.Widget.call (this, argsDict);

    this._init ();
}

ActiveDateRangeInput.prototype = auxlib.create (x2.Widget.prototype);

/**
 * In the time tracking feature: update the duration fields based on current
 * values of start/end fields.
 * @param Bool startDateChanged If true, it indicates that the start date field is the field being
 *   updated. If false, it indicates that the end date field is the field being updated.
 * @param event The event object
 */
ActiveDateRangeInput.prototype._updateActionDuration = function (startDateChanged, event) {
    var beginObj = this.element$.find ('.action-due-date');
    var endObj = this.element$.find ('.action-complete-date');
    var thisObj = startDateChanged ? beginObj : endObj;
    var otherObj = !startDateChanged ? beginObj : endObj;
    var durationMin = this.element$.find ('.action-duration input[name="timetrack-minutes"]');
    var durationHour = this.element$.find ('.action-duration input[name="timetrack-hours"]');
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
ActiveDateRangeInput.prototype._updateActionEndTime = function (event) {
    var beginObj = this.element$.find ('.action-due-date');
    var endObj = this.element$.find ('.action-complete-date');
    var durationMin = this.element$.find ('.action-duration input[name="timetrack-minutes"]');
    var durationHour = this.element$.find ('.action-duration input[name="timetrack-hours"]');
    var currentTime = new Date;
    var endTime, 
        beginTime, 
        totalDuration,
        init;

    // Initialize to zero:
    if(durationMin.val() === '')
        durationMin.val(0);
    if(durationHour.val() === '')
        durationHour.val(0);

    totalDuration = parseInt(durationMin.val(), 10)*60000 + 
        parseInt(durationHour.val(), 10)*3600000;

    // Initialize beginning date to now if unset:
    if(beginObj.val() === '')
        beginObj.datepicker('setDate',currentTime);

    // Initialize end date to beginning date if unset:
    if(endObj.val() == '') {
        endTime = beginObj.datepicker('getDate');
        endObj.datepicker('setDate',endTime);
    } else {
        endTime = endObj.datepicker('getDate');
    }
    beginTime = beginObj.datepicker('getDate');
    if (this.decrementStartDate &&
       (endTime >= currentTime || beginTime.getTime() + totalDuration > currentTime.getTime())) {

        // Push the beginning time back so the end time doesn't go into the future:
        beginObj.datepicker(
            'setDate',new Date(endObj.datepicker('getDate').getTime() - totalDuration));
    } else {
        // Set the end time according to the beginning time and the duration:
        endObj.datepicker(
            'setDate',new Date(beginObj.datepicker('getDate').getTime() + totalDuration));
    }
};

ActiveDateRangeInput.prototype._init = function () {
    var that = this;

    $(function () {
        that.element$.find ('.action-due-date').change(function(){
            that._updateActionDuration(true);
        });
        that.element$.find ('.action-complete-date').change(function(){
            that._updateActionDuration(false);
        });
        that.element$.find (
            '.action-duration input[name="timetrack-hours"],' + 
            ' .action-duration input[name="timetrack-minutes"]')
            .bind('change', function (evt) { that._updateActionEndTime (evt); })
            .bind('change.zeropad',function(event) {
                var intValue = parseInt(event.target.value, 10);
                var maxValue = parseInt(event.target.getAttribute("max"), 10);
                if(intValue < 10) {
                    // Pad with zeros
                    event.target.value = '0'+parseInt(event.target.value, 10);
                }
            });
    });
};

return ActiveDateRangeInput;

}) ();
