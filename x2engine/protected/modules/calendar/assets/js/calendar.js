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




x2.calendarManager = (function () {

function CalendarManager (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        calendar : '#calendar',   
        translations: {
        }
    };
    //this._emailInvitationDialog$ = null;
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this._init ();
}

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
CalendarManager.prototype.insertDate = function(date, view, publisherName){
    if(typeof publisherName === 'undefined'){
        publisherName = '';
    }
    
    if (typeof view === 'undefined'){
        view = $(this.calendar).fullCalendar('getView');
    }
    var form$ = x2.publisher.getForm ();

    // Preserve hours previously set in case the user is just switching
    // the day of the event:
    var newDate = {
        begin: new Date(date.getTime()),
        end: new Date(date.getTime())
    };
    var oldDate = {
        begin: form$.find ('.action-due-date').datetimepicker('getDate'),
        end: form$.find ('.action-complete-date').datetimepicker('getDate')
    };
    if(view.name == 'month' || view.name == 'basicWeek') {
        $(auxlib.keys(oldDate)).each(function(key, val){
            if(oldDate[val]) {
                newDate[val].setHours(oldDate[val].getHours())
                newDate[val].setMinutes(oldDate[val].getMinutes())
            }
        });
    }

    var dateformat = form$.data('dateformat');
    var timeformat = form$.data('timeformat');
    var ampmformat = form$.data('ampmformat');
    var region = form$.data('region');
    var monthNamesShort = form$.data('monthNamesShort');

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
    if(typeof(monthNamesShort) == 'undefined') {
        monthNamesShort = '';
    }


    form$.find ('.action-due-date').datetimepicker("destroy");
    form$.find ('.action-due-date').datetimepicker(
        jQuery.extend(
            {
                showMonthAfterYear:false
            }, 
            jQuery.datepicker.regional[region], {
                'dateFormat':dateformat,
                'timeFormat':timeformat,
                'ampm':ampmformat,
                'monthNamesShort':monthNamesShort,
                'changeMonth':true,
                'changeYear':true, 
                'defaultDate': newDate.begin
            }
        )
    );
    form$.find ('.action-due-date').datetimepicker('setDate', newDate.begin);

    form$.find ('.action-complete-date').datetimepicker("destroy");
    form$.find ('.action-complete-date').datetimepicker(
        jQuery.extend(
            {
                showMonthAfterYear:false
            }, 
            jQuery.datepicker.regional[region], {
                'dateFormat':dateformat,
                'timeFormat':timeformat,
                'ampm':ampmformat,
                'monthNamesShort':monthNamesShort,
                'changeMonth':true,
                'changeYear':true,
                'defaultDate': newDate.end
            }
        )
    );
    form$.find ('.action-complete-date').datetimepicker('setDate', newDate.end);

    form$.find ('.action-description').click ().select ().focus ();
    return false;
}


// Called by the event editor 
CalendarManager.prototype.giveSaveButtonFocus = function(){
    $('.ui-dialog-buttonpane').find ('button').removeClass ('highlight');
    $('.ui-dialog-buttonpane').find('button:contains("Save")')
    .addClass('highlight')
    .focus();
}

// Function to formate a javascript Date  object into yyyymmdd
CalendarManager.prototype.yyyymmdd = function(date) {
    var yyyy = date.getFullYear().toString();
    var mm = (date.getMonth()+1).toString(); // getMonth() is zero-based
    var dd  = date.getDate().toString();
    return yyyy +"-"+ (mm[1]?mm:"0"+mm[0]) +"-"+ (dd[1]?dd:"0"+dd[0]); // padding
};

CalendarManager.prototype.dayNumberClick = function (target) {
    var date = $(target).closest ('td').attr ('data-date').split ('-');

    $(this.calendar).fullCalendar ('gotoDate', date[0], date[1] - 1, date[2]);
    $(this.calendar).fullCalendar ('changeView', 'agendaDay');
    return false;
};

CalendarManager.prototype.updateWidgetSetting = function(setting, value){
    return $.ajax({
        url: this.widgetSettingUrl,
        data: {
            widget: 'SmallCalendar',
            setting: setting,
            value: value
        }
    });
}

//CalendarManager.prototype.emailInvitationDialog = function () {
//    this._emailInvitationDialog$ = $('#email-inviation-dialog');
//};

CalendarManager.prototype._init = function () {
    $(this.calendar).find('.day-number-link').click (function () { return false; });
};

return new CalendarManager ();

}) ();


(function () {

/**
 * Add method to layout manager to set up responsiveness of calendar title bar
 */
x2.LayoutManager.prototype.setUpCalendarTitleBarResponsiveness = function () {
    var that = this;

    function hideTitleBar (titleBar) {
        $(titleBar).css ({height: ''});
        $(titleBar).find ('.responsive-menu-items').css ({display: ''});
    }

    function showTitleBar (titleBar) {
        $(titleBar).animate ({ height: ($(titleBar).height () * 2) + 'px' }, 300);
        $(titleBar).find ('.responsive-menu-items').show ();
        $(titleBar).find ('.responsive-menu-items').css ({display: 'block'});
    }

    $('.fc-header.responsive-page-title .mobile-dropdown-button').unbind ('click').
        bind ('click', function () {

        var titleBar = $(this).parents ('.responsive-page-title');
        if ($(titleBar).find ('.responsive-menu-items').is (':visible')) {
            that._minimizeResponsiveTitleBar (titleBar);
        } else {
            auxlib.onClickOutside ($('.fc-header'), function () {
                that._minimizeResponsiveTitleBar (titleBar);
            }, true, 'setUpCalendarTitleBarResponsiveness');
            $(window).one ('resize._setUpTitleBarResponsiveness', function () {
                if ($(titleBar).find ('.responsive-menu-items').is (':visible')) {
                    that._minimizeResponsiveTitleBar (titleBar);
                }
            });
            that._expandResponsiveTitleBar (titleBar);
        }
    });

    // action history responsiveness setup
//    this.addFnToResizeQueue ((function () {
//        return function (windowWidth, contentWidth) {
//            if(contentWidth < that._publisherHalfWidthThreshold)
//                var newHistoryMode = 0; // underneath record
//            else
//                var newHistoryMode = 1 // side of record
//                
//            if(that._historyMode !== newHistoryMode) {
//                that._historyMode = newHistoryMode;
//                if(that._historyMode === 1) {
//                    $(that._halfWidthSelector).addClass('half-width');
//                } else {
//                    $(that._halfWidthSelector).removeClass('half-width');
//                }
//            }
//        };
//    }) ());

};

}) ();
