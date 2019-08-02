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
 * Manages behavior of grid widgets
 */

/**
 * Constructor 
 * @param dictionary argsDict A dictionary of arguments which can be used to override default values
 *  specified in the defaultArgs dictionary.
 */
function GridViewWidget (argsDict) {
    var defaultArgs = {
        showHeader: true,
        hideFullHeader: false,
        compactResultsPerPage: false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
	SortableWidget.call (this, argsDict);	
    this.gridElem$ = this.element.find ('.x2-gridview');
}

GridViewWidget.prototype = auxlib.create (SortableWidget.prototype);


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

GridViewWidget.prototype.refresh = function () {
    this._refreshGrid ();
};

GridViewWidget.prototype._gridElement = function () {
    return this.element.find ('.x2-gridview');
};

GridViewWidget.prototype._refreshGrid = function () {
    var that = this;
    x2[that.widgetType + 'WidgetManager'].refreshWidget (this.getWidgetKey ());
};

/**
 * Instantiate grid settings dialog and set up behavior of grid settings widget menu option
 */
GridViewWidget.prototype._setUpGridSettings = function () {
    var that = this;
    var settingsDialog$ = $('#grid-settings-dialog-' + this.getWidgetKey ());          
    settingsDialog$.dialog ({
        title: this.translations['Grid Settings'],
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
                text: this.translations['Save'],
                click: function () {
                    var elem$ = $(this);
                    that.setProperty (
                        'dbPersistentGridSettings',
                        $(this).find ('[name="dbPersistentGridSettings"]').is (':checked') ? 1 : 0,
                        function () { elem$.dialog ('close'); });
                }
            }
        ]
    });
    this.element.find ('.grid-settings-button').click (function () {
        settingsDialog$.dialog ('open');
    });
};

GridViewWidget.prototype.toggleHeader = function (show) {
    if (this.hideFullHeader) {
        this.element.find ('.items').first ().toggle(show);
    } else {
        this.element.find ('.items').first ().toggle(true);
        this.element.find ('.page-title, tr.filters').toggle(show);
    }        
}

GridViewWidget.prototype._setUpShowHeaderButton = function () {
    var that = this;

    that.toggleHeader (that.showHeader);
    this.element.find ('.widget-settings-menu-content .hide-settings').click (function () {
        that.showHeader = !that.showHeader;
        that.setProperty ('showHeader', that.showHeader ? 1 : 0);
        that.toggleHeader (that.showHeader);
    });
    this.afterGridRefresh ();
}

GridViewWidget.prototype.afterGridRefresh = function () {
    var that = this;
    this._gridElement ().on ('x2.GridViewMassActionsManager.checkUIShow', function (evt, shown) {
        if (!that.hideFullHeader && !that.showHeader)
            that.toggleHeader (shown); 
    });
};

GridViewWidget.prototype._setUpTitleBarBehavior = function () {
    if (this.element.find ('.grid-settings-button').length) {
        this._setUpGridSettings ();
    }
    SortableWidget.prototype._setUpTitleBarBehavior.call (this);
};

GridViewWidget.prototype._setUpSettingsBehavior = function () {
    // detach the CGridView summary and move it to the widget settings menu
    if (this.compactResultsPerPage) {
        var settingsMenu$ = $(this.elementSelector + ' .widget-settings-menu-content');
        settingsMenu$.find ('.results-per-page-container').empty ().append (
            this.contentContainer.find ('.summary').detach ());
        settingsMenu$.find ('.results-per-page-container .summary').children ().show ();

    }

    SortableWidget.prototype._setUpSettingsBehavior.call (this);
};

GridViewWidget.prototype._setUpPageSizeSelection = function () {
    var that = this;
    if (this.compactResultsPerPage) {
        var settingsMenu$ = $(this.elementSelector + ' .widget-settings-menu-content');
        settingsMenu$.find ('.results-per-page-container select').change (function () {
            that.setProperty ('resultsPerPage', $(this).val ());
        });
    }
};


GridViewWidget.prototype._init = function () {
    SortableWidget.prototype._init.call (this);
    this._setUpShowHeaderButton ();
    this._setUpPageSizeSelection ();
};
