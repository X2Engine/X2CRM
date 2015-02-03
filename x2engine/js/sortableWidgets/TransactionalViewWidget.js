/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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
 * Constructor 
 * @param dictionary argsDict A dictionary of arguments which can be used to override default values
 *  specified in the defaultArgs dictionary.
 */

x2.TransactionalViewWidget = (function () {

function TransactionalViewWidget (argsDict) {
    var defaultArgs = {
        modelName: null,
        modelId: null,
        actionType: null,
        hideFullHeader: true
    };

    auxlib.applyArgs (this, defaultArgs, argsDict);
	GridViewWidget.call (this, argsDict);	
    TransactionalViewWidget.widgets[this.getWidgetKey ()] = this;
}

TransactionalViewWidget.widgets = {};

TransactionalViewWidget.prototype = auxlib.create (GridViewWidget.prototype);


/*
Public static methods
*/

TransactionalViewWidget.refreshAll = function () {
    for (var i in TransactionalViewWidget.widgets) {
        TransactionalViewWidget.widgets[i]._refreshGrid (); 
    }
};

TransactionalViewWidget.refresh = function (type) {
    for (var widgetKey in TransactionalViewWidget.widgets) {
        var regex = new RegExp ('^' + type + '_.*$');
        console.log ('widgetKey = ');
            console.log (widgetKey);

        if (widgetKey.match (regex)) {
            TransactionalViewWidget.widgets[widgetKey]._refreshGrid (); 
        }
    }
};

/*
Private static methods
*/

/*
Public instance methods
*/

/*
Private instance methods
*/

TransactionalViewWidget.prototype._setUpCreateButtonBehavior = function () {
    var that = this;
    this._createButton$.unbind ('click._setUpCreateButtonBehavior').
        bind ('click._setUpCreateButtonBehavior', function () {

        new x2.QuickCreate ({
            modelType: 'Actions',
            data: {
                actionType: that.actionType,
                secondModelName: that.modelName,
                secondModelId: that.modelId
            },
            dialogAttributes: {
                title: that.translations.dialogTitle
            },
            enableFlash: false,
            success: function () {
                that._refreshGrid ();
                x2.actionHistory.update ();
            }
        });
    });
};

TransactionalViewWidget.prototype._setUpTitleBarBehavior = function () {
    this._createButton$ = this.element.find ('.create-button');
    this._setUpCreateButtonBehavior ();
    GridViewWidget.prototype._setUpTitleBarBehavior.call (this);
};

return TransactionalViewWidget;

}) ();
