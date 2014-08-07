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

};

}) ();

x2.calendarManager = (function () {

function CalendarManager (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
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

CalendarManager.prototype.dayNumberClick = function (target) {
    var date = $(target).closest ('td').attr ('data-date').split ('-');

    $('#calendar').fullCalendar ('gotoDate', date[0], date[1] - 1, date[2]);
    $('#calendar').fullCalendar ('changeView', 'agendaDay');
    return false;
};

CalendarManager.prototype._init = function () {
    $('.day-number-link').click (function () { return false; });
};

return new CalendarManager ();

}) ();

