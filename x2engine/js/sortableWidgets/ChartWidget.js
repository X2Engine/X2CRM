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




/**
 * Manages behavior of a chart widget
 */

/**
 * Constructor 
 * @param dictionary argsDict A dictionary of arguments which can be used to override default values
 *  specified in the defaultArgs dictionary.
 */
function ChartWidget (argsDict) {
    var defaultArgs = {
        chartType: '',
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

	SortableWidget.call (this, argsDict);	
}

ChartWidget.prototype = auxlib.create (SortableWidget.prototype);


/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/

ChartWidget.prototype.afterSort = function () {
    this.refresh ();
};

ChartWidget.prototype.refresh = function () {
    var that = this;
    x2[this.chartType + that.widgetUID].chart.replot ();
};

/*
Private instance methods
*/

/**
 * Overrides parent method. Chart must be replotted after widget is maximized.
 */
ChartWidget.prototype._afterMaximize = function () {
    var that = this;
    if (typeof x2[this.chartType + that.widgetUID].chart !== 'undefined')
        x2[this.chartType + that.widgetUID].chart.replot ();
};

ChartWidget.prototype._tearDownWidget = function () {
    var that = this;
    if (typeof x2[this.chartType + that.widgetUID].chart !== 'undefined')
        x2[this.chartType + that.widgetUID].chart.tearDown ();
    delete x2[this.chartType + that.widgetUID].chart;
};

/**
 * Enables chart subtype selection. 
 */
ChartWidget.prototype._setUpSubtypeSelection = function () {
    var that = this; 
    this.element.find ('.chart-subtype-selector').on ('change', function (evt) {
        var selectedSubType = $(this).val ();
        x2[that.chartType + that.widgetUID].chart.setChartSubtype (
            selectedSubType, true, false, true);    
        that.setProperty ('chartSubtype', selectedSubType);
    });
};

/**
 * Instantiates popup dropdown menu. Expects {settingsMenu} to be in the widget template
 */
ChartWidget.prototype._setUpSettingsBehavior = function () {
    var that = this; 
    this.popupDropdownMenu = new PopupDropdownMenu ({
        containerElemSelector: this.elementSelector + ' .chart-controls-container',
        openButtonSelector: this.elementSelector + ' .widget-settings-button',
        onClickOutsideSelector: 
            this.elementSelector + ' .widget-settings-button, ' +
            this.elementSelector + ' .chart-controls-container, ' +
            '.ui-datepicker-header, .ui-multiselect-header, .ui-multiselect-checkboxes',
        autoClose: false,
        defaultOrientation: 'left',
        onClose: function () {
            if (!that._cursorInWidget)
                $(that.element).find ('.submenu-title-bar .x2-icon-button').hide ();
        },
        css: {
            'max-width': '100%'
        }
    });
};

ChartWidget.prototype._init = function () {
    SortableWidget.prototype._init.call (this);
    this._setUpSubtypeSelection ();
};

