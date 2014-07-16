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
 * Prototype for publisher tab with hours and minutes time fields 
 */

if(typeof x2 == 'undefined')
    x2 = {};
if(typeof x2.publisher == 'undefined')
    x2.publisher = {};

x2.PublisherTimeTab = (function () {

function PublisherTimeTab (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

	x2.PublisherTab.call (this, argsDict);	
}

PublisherTimeTab.prototype = auxlib.create (x2.PublisherTab.prototype);

/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/

/*
Private instance methods
*/


/**
 * In the time tracking feature: update the duration fields based on current
 * values of start/end fields.
 * @param Bool startDateChanged If true, it indicates that the start date field is the field being
 *   updated. If false, it indicates that the end date field is the field being updated.
 * @param event The event object
 */
PublisherTimeTab.prototype._updateActionDuration = function (startDateChanged, event) {
    var beginObj = auxlib.getElement(this._elemSelector + ' .action-due-date');
    var endObj = auxlib.getElement(this._elemSelector + ' .action-complete-date');
    var thisObj = startDateChanged ? beginObj : endObj;
    var otherObj = !startDateChanged ? beginObj : endObj;
    var durationMin = auxlib.getElement(
        this._elemSelector + ' .action-duration input[name="timetrack-minutes"]');
    var durationHour = auxlib.getElement(
        this._elemSelector + ' .action-duration input[name="timetrack-hours"]');
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
PublisherTimeTab.prototype._updateActionEndTime = function (event) {
    var beginObj = auxlib.getElement(this._elemSelector + ' .action-due-date');
    var endObj = auxlib.getElement(this._elemSelector + ' .action-complete-date');
    var durationMin = auxlib.getElement(
        this._elemSelector + ' .action-duration input[name="timetrack-minutes"]');
    var durationHour = auxlib.getElement(
        this._elemSelector + ' .action-duration input[name="timetrack-hours"]');
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
    if(x2.publisher._selectedTab.id != 'new-event' &&
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

PublisherTimeTab.prototype._init = function () {
    var that = this;

    $(function () {
        auxlib.getElement(that._elemSelector + ' .action-due-date').change(function(){
            that._updateActionDuration(true);
        });
        auxlib.getElement(that._elemSelector + ' .action-complete-date').change(function(){
            that._updateActionDuration(false);
        });
        auxlib.getElement(
            that._elemSelector + ' .action-duration input[name="timetrack-hours"],' + 
            that._elemSelector + ' .action-duration input[name="timetrack-minutes"]')
            .bind('change', function (evt) { that._updateActionEndTime (evt); })
            .bind('change.zeropad',function(event) {
                var intValue = parseInt(event.target.value, 10);
                var maxValue = parseInt(event.target.getAttribute("max"), 10);
                if(intValue < 10) {
                    // Pad with zeros
                    event.target.value = '0'+parseInt(event.target.value, 10);
                }/* else if (intValue > 100 && maxValue == 99) {
                    // Trim off the first digit
                    event.target.value = event.target.value.substring(1);
                } else if(intValue > maxValue) {
                    // Set back to zero
                    event.target.value = '0';
                }*/
            });
    });

    x2.PublisherTab.prototype._init.call (this);
};

return PublisherTimeTab;

}) ();

