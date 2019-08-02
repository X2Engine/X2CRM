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




x2.panel = (function () {

function Panel (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this._init ();
}

Panel.prototype.getElement = function () {
    return $.mobile.activePage.find ('.panel-contents');
};

Panel.prototype.selectItem = function (dataUrl) {
    return;
//    if (!dataUrl) return;
//    dataUrl = dataUrl.replace (/\/$/, '');
//
//    var element$ = this.getElement ();
//    element$.find ('li').removeClass ('selected');
//    element$.find ('a[href="' + dataUrl + '"]').parent ().addClass ('selected');
};

Panel.prototype.setUpItemBehavior = function () {
    var element$ = this.getElement ();

    element$.find ('a').click (function () {
       // page refresh now handled by x2touchJQueryOverrides 
//        if ($(this).hasClass ('logout-button')) { 
//            if (x2.main.isPhoneGap) { // full page refresh 
//                $.mobile.loading ('show');
//                $.ajax ({
//                    url: $(this).attr ('href'),
//                    success: function () {
//                        x2touch.API.refresh ();
//                    }
//                })
//            } else {
//                window.location = $(this).attr ('href');
//            }
//            return false;
//        }

        // refreshing charts from charts causes display problems
        if ($(this).parent ().hasClass ('selected') &&
            $.mobile.activePage.attr ('data-page-id') === 'reports-mobileChartDashboard') {

            return false;
        }
    });
};

Panel.prototype.close = function () {
    this.getElement ().closest ('.x2touch-panel').panel ('close');
};

Panel.prototype.swipedInTarget = function (start) {
    var targetWidth = 50;
    return start[0] < targetWidth;
};

Panel.prototype.configureSwipeOpen = function () {
    var that = this;
    $(document).off ('swiperight.configureSwipeOpen').
        on ('swiperight.configureSwipeOpen', function (evt) {

        if (that.swipedInTarget (evt.swipestart.coords) &&
            $.mobile.activePage && $.mobile.activePage.jqmData ('panel') !== 'open') {

            that.getElement ().closest ('.x2touch-panel').panel ('open');
        }
    });
};

Panel.prototype.configureScrollBar = function () {
    var that = this;
     
    if (x2.main.isPhoneGap) { 
        $('.x2touch-panel').on ('panelopen', function () {
            if ($(this).hasClass ('no-scrollbar')) { 
                $(this).closest ('.nano').nanoScroller ({ destroy: true });
                $(this).removeClass ('no-scrollbar');
            }
            x2.main.instantiateNano (that.getElement ().find ('.ui-listview'));
        });
    }
     
};

Panel.prototype._init = function () {
    var that = this;
    $(document).on ('pagecontainershow', function () {
        that.configureSwipeOpen ();
        that.setUpItemBehavior ();
        that.configureScrollBar ();
    });
};

return new Panel;

}) ();
