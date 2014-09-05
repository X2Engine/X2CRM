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
 * Manages behavior of profile widgets as a set. Behavior of individual profile widgets is managed
 * in separate Widget prototypes.
 */

/**
 * Constructor 
 * @param dictionary argsDict A dictionary of arguments which can be used to override default values
 *  specified in the defaultArgs dictionary.
 */
function RecordViewWidgetManager (argsDict) {
    var defaultArgs = {
        cssSelectorPrefix: 'recordView', 
        widgetType: 'recordView',
        connectedContainerSelector: '', // class shared by all columns containing sortable widgets
        createProfileWidgetUrl: '',
        modelId: null,
        modelType: null
    };

	SortableWidgetManager.call (this, argsDict);	

    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._init ();
}

RecordViewWidgetManager.prototype = auxlib.create (SortableWidgetManager.prototype);

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
 * Overrides parent method. In addition to parent behavior, check if widget layout should be 
 * changed 
 */
RecordViewWidgetManager.prototype.addWidgetToHiddenWidgetsMenu = function (widgetSelector) {
    SortableWidgetManager.prototype.addWidgetToHiddenWidgetsMenu.call (this, widgetSelector);
};

/*
Private instance methods
*/

/**
 * Check if layout should be rearranged after widget is added to layout 
 */
RecordViewWidgetManager.prototype._afterShowWidgetContents = function () {
    this._hideShowHiddenProfileWidgetsText ();
    x2.profile.checkAddWidgetsColumn (); 
};

/**
 * Overrides parent method. 
 */
RecordViewWidgetManager.prototype._afterShowWidgetContents = function () {
    hideShowHiddenWidgetSubmenuDividers ();
};


/**
 * Override parent method. Add model id and type to GET params
 */
RecordViewWidgetManager.prototype._getShowWidgetContentsData = function (widgetClass) {
    var that = this;
    return {
        widgetClass: widgetClass, 
        widgetType: that.widgetType,
        modelId: that.modelId,
        modelType: that.modelType
    };
};

RecordViewWidgetManager.prototype._init = function () {
    SortableWidgetManager.prototype._init.call (this);
};
