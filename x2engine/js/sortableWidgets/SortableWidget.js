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
        deleteWidgetUrl: '',
        widgetClass: '', // the name of the associated widget class
        setPropertyUrl: '', // the url used to call the set profile widget property action
        settingsModelName: null, // The name of the model with the settings field
        settingsModelId: null,   // The id of the model with the settings field
        profileId: null, // the id of the profile associated with this widget
        widgetType: '', // (profile)
        widgetUID: null, 
        DEBUG: x2.DEBUG && false,
        enableResizing: false,
        translations: {},
        urls: {},
        hasError: false
    };

    auxlib.applyArgs (this, defaultArgs, argsDict);
    this.elementSelector = '#' + this.widgetClass + '-widget-container-' + this.widgetUID;

    x2.Widget.call (this, $.extend ({}, argsDict, {
        element: this.elementSelector 
    }));

    this.element = $(this.elementSelector); // the widget container

    // the widget content container (excludes the top bar)
    this.contentContainer = $('#' + this.widgetClass + '-widget-content-container-'+this.widgetUID);
    if (this.enableResizing) {
        this.resizableContainer = this.contentContainer;
        this.resizeHandles = null;
    }

    this._settingsMenuContentSelector = this.elementSelector  + ' .widget-settings-menu-content';

    SortableWidget.sortableWidgets.push (this);

    this._init ();
}

SortableWidget.prototype = auxlib.create (x2.Widget.prototype);

SortableWidget.getParentType = function (widgetType) {
    if ($.inArray (widgetType, ['data', 'profile', 'recordView']) >= 0) {
        return widgetType;
    } else {
        return 'recordView';
    }
};

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
    var widgetKey = $(elem).attr ('id').replace (/-widget-container-(\w+)?$/, '_$1');

    var widget = SortableWidget.getWidgetByKey (widgetKey);
    return widget;
};

/**
 * @return string key which uniquely identifies widget
 */
SortableWidget.prototype.getWidgetKey = function () {
    return this.widgetClass + '_' + this.widgetUID;
};

/**
 * @param string widgetKey
 * @return mixed sortable widget instance if instance with specified class is found, null otherwise 
 */
SortableWidget.getWidgetByKey = function (widgetKey) {
    for (var i in SortableWidget.sortableWidgets) {
        if (SortableWidget.sortableWidgets[i].widgetClass + '_' +
            SortableWidget.sortableWidgets[i].widgetUID === 
            widgetKey)

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

SortableWidget.prototype.afterSort = function () {};

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
            widgetUID: this.widgetUID,
            widgetType: this.widgetType,
            settingsModelName: this.settingsModelName,
            settingsModelId: this.settingsModelId,
        },
        success: function (data) {
            if (data.trim() === 'success') {
                if (typeof callback !== 'undefined') callback ();
            }
        }
    });
};

SortableWidget.prototype.setProperties = function (props, callback) {
    $.ajax ({
        url: this.setPropertyUrl,
        type: 'POST',
        data: {
            widgetClass: this.widgetClass,
            props: props,
            widgetUID: this.widgetUID,
            widgetType: this.widgetType,
            settingsModelName: this.settingsModelName,
            settingsModelId: this.settingsModelId,
        },
        success: function (data) {
            if (data.trim() === 'success') {
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

SortableWidget.prototype.afterRefresh = function () {};

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
        that.DEBUG && console.log (that.contentContainer); 
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

SortableWidget.prototype.getParentType = function () {
    return SortableWidget.getParentType (this.widgetType);
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
            // remove sort item class to prevent sort jitter
            $(that.element).removeClass (
                x2[that.getParentType () + 'WidgetManager'].getWidgetContainerSelector ().
                replace (/\./, ''));
            x2[that.getParentType () + 'WidgetManager'].addWidgetToHiddenWidgetsMenu (that.element);
            $(that.element).children ().remove ();
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
                  $(that._settingsMenuContentSelector).is (':visible'))) {
                $(that.element).find ('.submenu-title-bar .x2-icon-button').hide ();
            }
        });
    }

    if (this.element.find ('.relabel-widget-button').length) {
        this._setUpWidgetRelabelling ();
    }
    if (this.element.find ('.delete-widget-button').length) {
        this._setUpWidgetDeletion ();
    }
};

/**
 * Sets up behavior of widget deletion settings menu button
 */
SortableWidget.prototype._setUpWidgetDeletion = function () {
    var that = this;
    var deletionDialog$ = $('#delete-widget-dialog-' + this.widgetUID);          
    deletionDialog$.dialog ({
        title: this.translations['Are you sure you want to delete this widget?'],
        autoOpen: false,
        width: 500,
        buttons: [
            {
                text: this.translations['Cancel'],
                click: function () {
                    $(this).dialog ('close');
                }
            },
            {
                text: this.translations['Delete'],
                'class': 'urgent',
                click: function () {
                    $.ajax ({
                        url: that.deleteWidgetUrl,
                        data: {
                            widgetLayoutName: that.widgetType,
                            widgetKey: that.widgetClass + '_' + that.widgetUID,
                            settingsModelName: that.settingsModelName,
                            settingsModelId: that.settingsModelId,
                        },
                        type: 'POST',
                        success: function (data) {
                            if (data.trim() === 'success') {
                                $(that.element).remove ();
                                delete that;
                                deletionDialog$.dialog ('close');
                                x2[that.widgetType + 'WidgetManager'].
                                    afterDelete (that.element);
                            }
                        }
                    });
                }
            }
        ]
    });
    this.element.find ('.delete-widget-button').click (function () {
        deletionDialog$.dialog ('open');
    });
};

/**
 * Sets up behavior of widget rename settings menu button
 */
SortableWidget.prototype._setUpWidgetRelabelling = function () {
    var that = this;
    var relabellingDialog$ = $('#relabel-widget-dialog-' + this.widgetUID);          
    relabellingDialog$.dialog ({
        title: this.translations['Rename Widget'],
        autoOpen: false,
        width: 500,
        buttons: [
            {
                text: this.translations['Cancel'],
                click: function () {
                    $(this).dialog ('close');
                }
            },
            {
                text: this.translations['Rename'],
                'class': 'widget-rename-submit-button',
                click: function () {
                    that.setProperty (
                        'label', relabellingDialog$.find ('.new-widget-name').val (), function () {

                        that.element.find ('.widget-title').html (
                            relabellingDialog$.find ('.new-widget-name').val ()
                        );
                        relabellingDialog$.dialog ('close');
                    });
                }
            }
        ]
    });
    relabellingDialog$.find ('.new-widget-name').keydown (function () {
        relabellingDialog$.closest ('.ui-dialog').find ('.widget-rename-submit-button').
            addClass ('highlight'); 
    });
    this.element.find ('.relabel-widget-button').click (function () {
        relabellingDialog$.dialog ('open');
    });
};

/**
 * Instantiates popup dropdown menu. Expects {settingsMenu} to be in the widget template
 */
SortableWidget.prototype._setUpSettingsBehavior = function () {
    var that = this; 
    this.popupDropdownMenu = new PopupDropdownMenu ({
        containerElemSelector: this.elementSelector + ' .widget-settings-menu-content',
        openButtonSelector: this.elementSelector + ' .widget-settings-button',
        defaultOrientation: 'left',
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
SortableWidget.prototype._afterStop = function () {
    var that = this; 
    that.setProperty ('height', that.element.height ());
};

/**
 * Sets up widget resize behavior 
 */
SortableWidget.prototype._setUpResizeBehavior = function () {
    var that = this; 
    var handles = this.resizeHandles ? this.resizeHandles : 's';

    var handle$ = null;

    if ($(this.resizableContainer).hasClass ('ui-resizable')) { // we're refreshing
        // Remove the handle jQuery classes, destroy the widget, and then add the classes back.
        // This prevents jQuery from removing the handle elements when the widget is destroyed.
        if (this.resizeHandles) {
            var handleElems = [];
            for (var i in handles) {
                var classes = $(handles[i]).attr ('class')
                handleElems.push ({
                    elem$: handles[i],
                    classes: classes
                });
                $(handles[i]).attr ('class', '');
            }
        }
        $(this.resizableContainer).resizable ('destroy');
        for (var i in handleElems) {
           handleElems[i].elem$.attr ('class', handleElems[i].classes); 
        }
    }
    $(this.resizableContainer).resizable ({
        handles: handles, 
        minHeight: 50,
        start: function () {
            /* 
            Make the handle bigger to prevent iframe from triggeing mouseleave event.
            Also prevents widget controls from being hidden during resize.
            */
            if (handle$)
                handle$.css ({ 
                    'height': '1000px',
                    'position': 'relative',
                    'top' : '-500px'
                });
        },
        stop: function () {
            if (handle$)
                handle$.css ({
                    'height': '',
                    'position': '',
                    'top': '',
                });
            that._afterStop ();
        },
        resize: function () { that._resizeEvent (); }
    });
    if (!this.resizeHandles)
        handle$ = that.resizableContainer.find ('.ui-resizable-handle');
};

SortableWidget.prototype._resizeEvent = function () {};

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
 * Returns a dictionary of variables needed to identifiy this widget's layout
 */
SortableWidget.prototype.ajaxIdentity = function(argsDict) {
    var defaultDict =  {
        widgetUID: this.widgetUID,
        widgetClass: this.widgetClass,
        settingsModelName: this.settingsModelName,
        settingsModelId: this.settingsModelId,
        widgetType: this.widgetType
    };

    for (var i in argsDict) {
        defaultDict[i] = argsDict[i];
    }

    return defaultDict;
}

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
