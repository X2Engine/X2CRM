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
 * QTipManager prototype
 */

x2 = typeof x2 === 'undefined' ? {} : x2;

x2.X2GridViewQtipManager = (function () {

function GridViewQtipManager (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    x2.QtipManager.call (this, argsDict);
    var defaultArgs = {
        loadingText: 'Loading...',
        dataAttrTitle: false,
        modelType: 'contacts',
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
}

GridViewQtipManager.prototype = auxlib.create (x2.QtipManager.prototype);

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
 * Initializes qtip objects for all links returned by query on qtipSelector
 */
GridViewQtipManager.prototype.refresh = function () {
    var that = this; 
    that.DEBUG && console.log ('refresh');
	$(that.qtipSelector).each(function (i) {
		var recordId = $(this).attr("href").match(/\d+$/);

		if(recordId !== null && recordId.length) {
			$(this).qtip(that._getConfig ($(this), recordId[0]));
		}
	});
};

/*
Private instance methods
*/

GridViewQtipManager.prototype._getConfig = function (elem, recordId) {
    var that = this;
    var config = {
        content: {
            text: function (event, api) {
                $.ajax ({
                    url: yii.scriptUrl+'/'+that.modelType+'/qtip',
                    data: { 
                        id: recordId,
                        suppressTitle: that.dataAttrTitle ? 1 : 0
                    },
                    method: "get"
                }).then (function (content) {
                    api.set ('content.text', content);
                });
                return that.loadingText;
            }, 
        },
        style: {
            classes: 'x2-qtip',
            tip: {
                corner: true,
            }
        },
        position: {
            viewport: $(window),
            my: 'top center',
            at: 'bottom center',
            target: $(elem),
            effect: false
        }
    };
    if (that.dataAttrTitle) {
        config.content.title = $(elem).attr ('data-qtip-title');
    }
    return config;
};

return GridViewQtipManager;

}) ();
