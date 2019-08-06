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





x2.chartManager = (function () { 

/**
 * Manages all charts on page 
 */
function ChartManager (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        charts: [] // jqplot chart instances
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

ChartManager.prototype.addChart = function (chart) {
    this.charts.push (chart);
};

/*
Private instance methods
*/

/**
 * Replots stacked bar width, changing width of bars based on layout type 
 */
ChartManager.prototype._replotStackedBarChart = function (elem) {
    $.map (elem.series, function (elem) {
        elem.barWidth = x2.layoutManager.isMobileLayout () ? 80 : 100;
    });
    elem.replot ({resetAxes: false}); 
};

/**
 * Replots all charts 
 */
ChartManager.prototype._replotCharts = function () {
    var that = this;
    $.map (that.charts, function (elem, index) { 
        if (elem.stackSeries) {
            that._replotStackedBarChart (elem);
        } else {
            elem.replot ({resetAxes: false}); 
        }
    });
};

/**
 * Calls replots on window resize, with delay
 */
ChartManager.prototype._setUpResizeBehavior = function () {
    var that = this;
    var timeout;

    $(window).on ('resize', function () {
        if (timeout) clearTimeout (timeout);                    
        timeout = setTimeout (function () {
            that._replotCharts ();
        }, 200);
    });
};

ChartManager.prototype._init = function () {
    this._setUpResizeBehavior ();
};

return new ChartManager ();

}) ();

