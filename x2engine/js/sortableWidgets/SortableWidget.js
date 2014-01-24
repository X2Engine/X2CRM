/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
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
 * Manages behavior of a sortable widget
 */

SortableWidget.sortableWidgets = []; // instances of SortableWidget

/**
 * Constructor 
 * @param dictionary argsDict A dictionary of arguments which can be used to override default values
 *  specified in the defaultArgs dictionary.
 */
function SortableWidget (argsDict) {
    var defaultArgs = {
        widgetClass: '', // the name of the associated widget class
        setPropertyUrl: '', // the url used to call the set profile widget property action
        profileId: null, // the id of the profile associated with this widget
        widgetType: '', // (profile)
        DEBUG: x2.DEBUG && false,
        enableResizing: false
    };

    auxlib.applyArgs (this, defaultArgs, argsDict);

    this.elementSelector = '#' + this.widgetClass + '-widget-container';
    this.element = $(this.elementSelector); // the widget container

    // the widget content container (excludes the top bar)
    this.contentContainer = $('#' + this.widgetClass + '-widget-content-container');

    SortableWidget.sortableWidgets.push (this);

    this._init ();
}

/*
Public static methods
*/

/**
 * @return boolean True if all widgets are hidden, false otherwise 
 */
SortableWidget.allWidgetsHidden = function () {
    for (var i in SortableWidget.sortableWidgets) {
        if (SortableWidget.sortableWidgets[i].element.is (':visible'))
            return false;
    }
    return true;
};

/**
 * Calls turnOnSortingMode () methods of all instantiated sortable widgets except excludedWidget
 * @param object excludedWidget instance of SortableWidget
 */
SortableWidget.turnOnSortingMode = function (excludedWidget) {
    for (var i in SortableWidget.sortableWidgets) {
        if (SortableWidget.sortableWidgets[i] !== excludedWidget)
            SortableWidget.sortableWidgets[i]._turnOnSortingMode ();
    }
};

/**
 * Calls turnOffSortingMode () methods of all instantiated sortable widgets except excludedWidget
 * @param object excludedWidget instance of SortableWidget
 */
SortableWidget.turnOffSortingMode = function (excludedWidget) {
    for (var i in SortableWidget.sortableWidgets) {
        if (SortableWidget.sortableWidgets[i] !== excludedWidget)
            SortableWidget.sortableWidgets[i]._turnOffSortingMode ();
    }
};

/**
 * @param object elem jQuery object corresponding to a widget container
 * @return mixed return value of getWidgetByClass ()
 */
SortableWidget.getWidgetFromWidgetContainer = function (elem) {
    var widgetClass = $(elem).attr ('id').replace (/-widget-container/, '');
    var widget = SortableWidget.getWidgetByClass (widgetClass);
    return widget;
};

/**
 * @param string widgetClass
 * @return mixed sortable widget instance if instance with specified class is found, null otherwise 
 */
SortableWidget.getWidgetByClass = function (widgetClass) {
    for (var i in SortableWidget.sortableWidgets) {
        if (SortableWidget.sortableWidgets[i].widgetClass === widgetClass)
            return SortableWidget.sortableWidgets[i];
    }
    return null;
};

/**
 * Call refresh method for each widget instance 
 */
SortableWidget.refreshWidgets = function () {
    for (var i in SortableWidget.sortableWidgets) {
        SortableWidget.sortableWidgets[i].refresh ();
    }
};

/*
Private static methods
*/

/*
Public instance methods
*/

/**
 * Calls an action which sets a property of the profile widget layout JSON attribute
 *
 * @param string key the name of the JSON property
 * @param string value the value to set the JSON property to
 * @param function callback if set, called after the ajax request returns
 */
SortableWidget.prototype.setProperty = function (key, value, callback) {
    $.ajax ({
        url: this.setPropertyUrl,
        type: 'POST',
        data: {
            widgetClass: this.widgetClass,
            key: key,
            value: value,
            widgetType: this.widgetType
        },
        success: function (data) {
            if (data === 'success') {
                if (typeof callback !== 'undefined') callback ();
            }
        }
    });
};

/**
 * Change widget label 
 * @param string newLabel 
 */
SortableWidget.prototype.changeLabel = function (newLabel) {
    var that = this; 
    this.setProperty ('label', newLabel, function () {
        that.element.find ('.widget-title').text (newLabel);
    });
};

/**
 * Call this to ensure that widget is rendered properly 
 */
SortableWidget.prototype.refresh = function () {};

SortableWidget.prototype.reinit = function () {
    this._init ();
};

/**
 * Should be called when widget drag starts 
 */
SortableWidget.prototype.onDragStart = function () {
    if (this._settingsBehaviorEnabled) // hide settings menu
        this.popupDropdownMenu.close ();
};

/**
 * Should be called when widget drag stops 
 */
SortableWidget.prototype.onDragStop = function () {};

/*
Private instance methods
*/

/**
 * Called by _setUpMinimizationBehavior after widget is maximized. Can be overridden in child
 * prototype.
 */
SortableWidget.prototype._afterMaximize = function () {};

/**
 * Sets up behavior of the minimization/maximization button
 */
SortableWidget.prototype._setUpMinimizationBehavior = function () {
    var that = this;
    that.DEBUG && console.log ('_setUpMinimizationBehavior');
    $(this.element).find ('.widget-minimize-button').unbind ('click.widgetMinimize');
    $(this.element).find ('.widget-minimize-button').bind ('click.widgetMinimize', 
        function (evt) {

        evt.preventDefault ();
        that.DEBUG && console.log ('click'); 
        var minimize = $(that.contentContainer).is (':visible');
        that.setProperty ('minimized', (minimize ? 1 : 0), function () {
            if (minimize) {
                $(that.contentContainer).slideUp ();
                $(that.element).find ('.widget-minimize-button').
                    children ().first ().show ();
                $(that.element).find ('.widget-minimize-button').
                    children ().last ().hide ();
            } else {
                $(that.contentContainer).slideDown ();
                that._afterMaximize ();
                $(that.element).find ('.widget-minimize-button').
                    children ().first ().hide ();
                $(that.element).find ('.widget-minimize-button').
                    children ().last ().show ();
            }
        });
    });
};

/**
 * Sets up behavior of the close button
 */
SortableWidget.prototype._setUpCloseBehavior = function () {
    var that = this;
    $(this.element).find ('.widget-close-button').unbind ('click.widgetClose');
    $(this.element).find ('.widget-close-button').bind ('click.widgetClose', function (evt) {
         
        evt.preventDefault ();
        that.DEBUG && console.log ('close'); 

        that.setProperty ('hidden', 1, function () {
            $(that.element).hide ();
            that._tearDownWidget ();
            that.contentContainer.children ().remove ();
            x2[that.widgetType + 'WidgetManager'].addWidgetToHiddenWidgetsMenu (that.element);
        });
    });
};

/**
 * override in child prototype 
 */
SortableWidget.prototype._tearDownWidget = function () {};

/**
 * Hides/shows title bar buttons on mouseleave/mouseover 
 */
SortableWidget.prototype._setUpTitleBarBehavior = function () {
    var that = this; 
    that._cursorInWidget = false;
    if ($(this.element).find ('.widget-minimize-button').length ||
        $(this.element).find ('.widget-close-button').length) {

        $(this.element).mouseover (function () {
            that._cursorInWidget = true;
            $(that.element).find ('.submenu-title-bar .x2-icon-button').show ();
        });
        $(this.element).mouseleave (function () {
            that._cursorInWidget = false;
            if (!(that._settingsBehaviorEnabled &&
                  $(that.elementSelector  + ' .widget-settings-menu-content').is (':visible'))) {
                $(that.element).find ('.submenu-title-bar .x2-icon-button').hide ();
            }
        });
    }
};

/**
 * Instantiates popup dropdown menu. Expects {settingsMenu} to be in the widget template
 */
SortableWidget.prototype._setUpSettingsBehavior = function () {
    var that = this; 
    this.popupDropdownMenu = new PopupDropdownMenu ({
        containerElemSelector: this.elementSelector + ' .widget-settings-menu-content',
        openButtonSelector: this.elementSelector + ' .widget-settings-button',
        onClose: function () {
            if (!that._cursorInWidget)
                $(that.element).find ('.submenu-title-bar .x2-icon-button').hide ();
        }
    });
};

SortableWidget.prototype._turnOnSortingMode = function () {};

SortableWidget.prototype._turnOffSortingMode = function () {};

/**
 * called by _setUpResizeBehavior () 
 */
SortableWidget.prototype._onResize = function () {};

/**
 * called by _setUpResizeBehavior () 
 */
SortableWidget.prototype._afterStop = function () {};

/**
 * Sets up widget resize behavior 
 */
SortableWidget.prototype._setUpResizeBehavior = function () {
    var that = this; 
    $(this.contentContainer).resizable ({
        handles: 's', 
        minHeight: 50,
        start: function () {
            /* 
            Make the handle bigger to prevent iframe from triggeing mouseleave event.
            Also prevents widget controls from being hidden during resize.
            */
            resizeHandle.css ({ 
                'height': '1000px',
                'position': 'relative',
                'top' : '-500px'
            });
        },
        stop: function () {
            resizeHandle.css ({
                'height': '',
                'position': '',
                'top': '',
            });
            that._afterStop ();
            that.setProperty ('height', that._iframeElem.attr ('height'));
        },
        resize: function () { that._resizeEvent (); }
    });
    var resizeHandle = that.contentContainer.find ('.ui-resizable-handle');
};

/**
 * Detects presence of UI elements (and sets properties accordingly), calls their setup methods
 */
SortableWidget.prototype._callUIElementSetupMethods = function () {
    if ($(this.element).find ('.widget-minimize-button').length) {
        this._setUpMinimizationBehavior ();
        this._minimizeBehaviorEnabled = true;
    } else {
        this._minimizeBehaviorEnabled = false;
    }

    if ($(this.element).find ('.widget-close-button').length) {
        this._setUpCloseBehavior ();
        this._closeBehaviorEnabled = true;
    } else {
        this._closeBehaviorEnabled = false;
    }

    if ($(this.element).find ('.widget-settings-button').length) {
        this._setUpSettingsBehavior ();
        this._settingsBehaviorEnabled = true;
    } else {
        this._settingscloseBehaviorEnabled = false;
    }

    if (this.enableResizing) {
        this._setUpResizeBehavior ();
    }
};

/**
 * Sets up the widget 
 */
SortableWidget.prototype._init = function () {
    var that = this;
    that.DEBUG && console.log ('SortableWidget: _init');
    that.DEBUG && console.log ('this = ');
    that.DEBUG && console.log (this);

    that._setUpTitleBarBehavior ();
    that._callUIElementSetupMethods ();
};
