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
 * Manages behavior of profile widgets as a set. Behavior of individual profile widgets is managed
 * in separate Widget prototypes.
 */

/**
 * Constructor 
 * @param dictionary argsDict A dictionary of arguments which can be used to override default values
 *  specified in the defaultArgs dictionary.
 */
function TwoColumnSortableWidgetManager (argsDict) {
    var defaultArgs = {
        connectedContainerSelector: '', // class shared by all columns containing sortable widgets
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

	SortableWidgetManager.call (this, argsDict);	
}

TwoColumnSortableWidgetManager.prototype = auxlib.create (SortableWidgetManager.prototype);

/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/

/**
 * Override parent method. In addition to parent behavior, check if widget layout should be changed 
 */
TwoColumnSortableWidgetManager.prototype.addWidgetToHiddenWidgetsMenu = function (widgetSelector) {
    SortableWidgetManager.prototype.addWidgetToHiddenWidgetsMenu.call (this, widgetSelector);
    //x2.profile.checkRemoveWidgetsColumn ();
};

/**
 * Checks if either of the widget containers are empty and adds/removes css class as appropriate
 * Used to ensure that empty widget boxes can be dragged into
 */
TwoColumnSortableWidgetManager.prototype.padEmptyWidgetBoxes = function () {
    var that = this; 
    that._padEmptyWidgetBox (this._widgetsBoxSelector);
    that._padEmptyWidgetBox (this._widgetsBoxSelector2);
};

/**
 * Checks if either of the widget containers are empty and removes css classes. Undoes changes made
 * by padEmptyWidgetBoxes ().
 */
TwoColumnSortableWidgetManager.prototype.unpadEmptyWidgetBoxes = function () {
    var that = this; 
    $(this._widgetsBoxSelector).removeClass ('empty-widget-container');
    $(this._widgetsBoxSelector2).removeClass ('empty-widget-container');
};

/*
Private instance methods
*/

/**
 * Checks if the specified widget container is empty and adds/removes css class as appropriate
 * @param string widgetBoxSelector jQuery selector for widget box
 */
TwoColumnSortableWidgetManager.prototype._padEmptyWidgetBox = function (widgetBoxSelector) {
    var foundVisible = false;

    $(widgetBoxSelector).children ().each (function () {
        if ($(this).is (':visible')) foundVisible = true;
    });
    if (!foundVisible) {
        $(widgetBoxSelector).addClass ('empty-widget-container');
    } else {
        $(widgetBoxSelector).removeClass ('empty-widget-container');
    }
};

/**
 * Returns an array of widget class names in the order that the corresponding widgets are in in the
 * layout.
 * @return array widgetOrder An array of strings where each string corresponds to a widget class
 */
TwoColumnSortableWidgetManager.prototype._getWidgetOrder = function () {
    var that = this;
    var widgetOrder = [];
    var widgetClass;
    $(this._widgetsBoxSelector).children (this._widgetContainerSelector).each (function () {
        widgetClass = that.getWidgetKey (this);
        widgetOrder.push (widgetClass);
    });
    $(this._widgetsBoxSelector2).children (this._widgetContainerSelector).each (function () {
        widgetClass = that.getWidgetKey (this);
        widgetOrder.push (widgetClass);
    });
    return widgetOrder;
};

TwoColumnSortableWidgetManager.prototype._activate = function (thisWidget) {
    var that = this;
    that.padEmptyWidgetBoxes ();            
    thisWidget.onDragStart ();
    SortableWidget.turnOnSortingMode (thisWidget); // custom iframe fix
};

TwoColumnSortableWidgetManager.prototype._deactivate = function (thisWidget) {
    var that = this;
    that.unpadEmptyWidgetBoxes ();
    thisWidget.onDragStop ();
    SortableWidget.turnOffSortingMode (thisWidget);
};

/**
 * Makes the widgets sortable. Overrides parent method to allow widgets to be dragged between
 * columns.
 */
TwoColumnSortableWidgetManager.prototype._setUpSortability = function () {
    var that = this;
    this._startedSortUpdate = false;
    $(this._widgetsBoxSelector + ',' + this._widgetsBoxSelector2).sortable ({
        items: that._widgetContainerSelector,
        connectWith: that.connectedContainerSelector,
        tolerance: 'pointer',
        activate: function (event, ui) {
            // event gets triggered twice, only perform udpates once
            if (that._startedSortUpdate) return;
            that._startedSortUpdate = true;

            that._activate (SortableWidget.getWidgetFromWidgetContainer (ui.item));
        },
        deactivate: function (event, ui) {
            // event gets triggered twice, only perform udpates once
            if (!that._startedSortUpdate) return;

            that._deactivate (SortableWidget.getWidgetFromWidgetContainer (ui.item));
            that._startedSortUpdate = false;
        },
        update: function (event, ui) {

            // save sort order
            $.ajax ({
                url: that.setSortOrderUrl,
                type: "POST",
                data: {
                    widgetOrder: that._getWidgetOrder (),
                    widgetType: that.widgetType
                },
                success: function (data) {
                }
            });

            // update container number
            var currContainer = $(ui.item).parents (that.connectedContainerSelector)[0]
            var containerNumber = 
                (currContainer === $(that._widgetsBoxSelector)[0] ? 1 : 2);
            var widget = SortableWidget.getWidgetFromWidgetContainer (ui.item);
            widget.setProperty ('containerNumber', containerNumber);
            widget.afterSort ();
            //widget.refresh ();
        },
        handle: this._widgetHandleSelector
    });
};

TwoColumnSortableWidgetManager.prototype._setUpAddProfileWidgetMenu = function () {
};


TwoColumnSortableWidgetManager.prototype._afterCloseWidget = function () {
};

/**
 * Check if layout should be rearranged after widget is added to layout 
 */
TwoColumnSortableWidgetManager.prototype._afterShowWidgetContents = function () {
    //x2.profile.checkAddWidgetsColumn (); 
};

TwoColumnSortableWidgetManager.prototype._init = function () {
    this._widgetsBoxSelector2 = '#' + this.cssSelectorPrefix + 'widgets-container-2';
    SortableWidgetManager.prototype._init.call (this);
};
