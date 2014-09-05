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
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

	SortableWidget.call (this, argsDict);	
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

/**
 * Instantiate grid settings dialog and set up behavior of grid settings widget menu option
 */
SortableWidget.prototype._setUpGridSettings = function () {
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


GridViewWidget.prototype._setUpTitleBarBehavior = function () {
    if (this.element.find ('.grid-settings-button').length) {
        this._setUpGridSettings ();
    }
    SortableWidget.prototype._setUpTitleBarBehavior.call (this);
};

