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
 * Manages behavior of record view widgets as a set. Behavior of individual record view widgets is 
 * managed in separate Widget prototypes.
 */

x2.RecordViewWidgetManager = (function () {

/**
 * Constructor 
 * @param dictionary argsDict A dictionary of arguments which can be used to override default values
 *  specified in the defaultArgs dictionary.
 */
function RecordViewWidgetManager (argsDict) {
    var defaultArgs = {
        cssSelectorPrefix: '', 
        widgetType: 'recordView',
        connectedContainerSelector: '', // class shared by all columns containing sortable widgets
        modelId: null,
        modelType: null
    };

    auxlib.applyArgs (this, defaultArgs, argsDict);
	TwoColumnSortableWidgetManager.call (this, argsDict);	
}

RecordViewWidgetManager.prototype = auxlib.create (TwoColumnSortableWidgetManager.prototype);

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
 * Check if layout should be rearranged after widget is added to layout 
 */
RecordViewWidgetManager.prototype._afterShowWidgetContents = function (widget$) {
    //this._hideShowHiddenProfileWidgetsText ();
    //x2.profile.checkAddWidgetsColumn (); 
    hideShowHiddenWidgetSubmenuDividers ();
    widget$.data ('x2-widget').afterRefresh ();
};

/**
 * Override parent method. Add model id and type to GET params
 */
RecordViewWidgetManager.prototype._getShowWidgetContentsData = function (widgetClass) {
    var that = this;
    var data = {
        widgetClass: widgetClass, 
        widgetType: that.widgetType,
        modelId: that.modelId,
        modelType: that.modelType
    };

    // brittle kludge to check type of widget being updated/shown
    var widgetClassName = widgetClass.replace (/_$/, '');
    if (x2.TransactionalViewWidget &&
        x2[widgetClassName] && 
        x2[widgetClassName] instanceof x2.TransactionalViewWidget) {
        data.relationships = x2.TransactionalViewWidget.relationships ? 1 : 0;
    }
    return data;
};

RecordViewWidgetManager.prototype._setUpRecordViewTypeToggleBehavior = function () {
    var menuItem$ = $('#view-record-action-menu-item');
    var that = this;
    menuItem$.find ('.journal-view-checkbox').click (function () {
        var enable = $(this).is (':checked') ? 1 : 0; 
        var publisher$ = $('#PublisherWidget-widget-container-');
        if (enable) {
            if (!publisher$.children ().length) {  
                that._showWidgetContents (publisher$.data ('x2-widget').getWidgetKey ());
            } else {
                publisher$.show ();
            }
        } else {
            $('#PublisherWidget-widget-container-').hide ();
        }
        auxlib.saveMiscLayoutSetting ('enableJournalView', enable); 
    });
    menuItem$.find ('.transactional-view-checkbox').click (function () {
        var enable = $(this).is (':checked') ? 1 : 0; 
        if (enable) {
            $('.transactional-view-widget').each (function () {
                if (!$(this).children ().length) {  
                    if ($(this).data ('x2-widget'))
                        that._showWidgetContents ($(this).data ('x2-widget').getWidgetKey ());
                } else {
                    $(this).show ();
                }
            });
        } else {
            $('.transactional-view-widget').hide ();
        }
        auxlib.saveMiscLayoutSetting (
            'enableTransactionalView', enable); 
    });

    var prevMode = $('#record-view-type-menu').is (':visible');
    $('#view-record-action-menu-item > span').click (function () {
        auxlib.saveMiscLayoutSetting ('viewModeActionSubmenuOpen', !prevMode ? 1 : 0);
        prevMode = !prevMode;
    });
//    auxlib.onClickOutside ($('#view-record-action-menu-item'), function () {
//        if ($('#record-view-type-menu').is (':visible')) {
//            $(this).children ('span').click ();
//            $('#record-view-type-menu').hide (); 
//        }
//    });
};

RecordViewWidgetManager.prototype._activate = function () {
    $(this._widgetsBoxSelector + ',' + this._widgetsBoxSelector2).addClass ('sortable-widget-drag');
    TwoColumnSortableWidgetManager.prototype._activate.apply (this, arguments);
};

RecordViewWidgetManager.prototype._deactivate = function () {
    $(this._widgetsBoxSelector + ',' + this._widgetsBoxSelector2).
        removeClass ('sortable-widget-drag');
    TwoColumnSortableWidgetManager.prototype._activate.apply (this, arguments);
};

RecordViewWidgetManager.prototype._init = function () {
    this._hiddenWidgetsMenuSelector = '#x2-hidden-recordView-widgets-menu';
    this._hiddenWidgetsMenuItemSelector = 
        '.x2-hidden-widgets-menu-item.' + this.widgetType + '-widget';
    this._setUpRecordViewTypeToggleBehavior ();
    this._widgetsBoxSelector = '#' + this.cssSelectorPrefix + 'widgets-container-2';
    this._widgetsBoxSelector2 = '#' + this.cssSelectorPrefix + 'widgets-container-inner';

    SortableWidgetManager.prototype._init.call (this);
};

return RecordViewWidgetManager;

}) ();
