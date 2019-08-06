
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
 * Prototype for publisher tab. 
 */

if(typeof x2 == 'undefined')
    x2 = {};
if(typeof x2.publisher == 'undefined')
    x2.publisher = {};

x2.PublisherTab = (function () {

function PublisherTab (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        translations: {},
        tabId: null, // id of element containing tab contents
    };

    auxlib.applyArgs (this, defaultArgs, argsDict);

    x2.Widget.call (this, argsDict);

    this._elemSelector = '#' + this.tabId;
    this.publisher = null;
    this._formDefaults = {
        assignedTo: null,
        associationType: null,
        associationId: null
    };
    this._init ();
}

PublisherTab.prototype = auxlib.create (x2.Widget.prototype);

/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/

PublisherTab.prototype.getFormObj = function () {
    return x2.X2Form.getInstance ($(this._elemSelector).find ('form'));
};

/**
 * Clears tab's form inputs 
 */
PublisherTab.prototype.reset = function () {
    var that = this;
    var coords = $("input[name=geoCoords]").val();
    var formObj = this.getFormObj ();
    formObj.findElemByAttr ('associationType').val (this._formDefaults.associationType);
    formObj.findElemByAttr ('associationId').val (this._formDefaults.associationId);
    formObj.findElemByAttr ('assignedTo').val (this._formDefaults.assignedTo);
    $("input[name=geoCoords]").val(coords);
};

PublisherTab.prototype._saveDefaults = function () {
    var formObj = this.getFormObj ();
    this._formDefaults.associationType = formObj.findElemByAttr ('associationType').val ();
    this._formDefaults.associationId = formObj.findElemByAttr ('associationId').val ();
    this._formDefaults.assignedTo = formObj.findElemByAttr ('assignedTo').val ();
};

PublisherTab.prototype._setUpAjaxSuccessHandler = function () {
    var that = this;
    that._form$ = that._element.find ('form');

    x2.X2Form.getInstance (that._form$).onAjaxSuccess = function (data) {
        var persistData = ['dateformat', 'timeformat', 'ampmformat', 'region', 'monthNamesShort'];
        var dataVals = {};
        // Save default locale settings to replace after resetting form
        $.each(persistData, function(i, v) { dataVals[v] = that._form$.data(v); });
        that._form$.replaceWith (data.page);
        that._setUpAjaxSuccessHandler ();
        if (!$(that._elemSelector).find ('.error').length) {
            that.publisher.reset();
            that.publisher.updates();
            if ($(that._elemSelector).closest ('.ui-dialog').length) {
                // if tab is in a transactional widget dialog
                $(that._elemSelector).closest ('.ui-dialog').remove ();
            }
        }
        $.each(dataVals, function(i, v) { that._form$.data(i, v); });
    };
};

PublisherTab.prototype.run = function () {
    var that = this;
    that._element = $(that._elemSelector);
    this._setUpAjaxSuccessHandler ();
};

/*
Private instance methods
*/

PublisherTab.prototype._init = function () {
    var that = this;
    $(function () {
        that._saveDefaults ();
        that.run ();
    });
};

return PublisherTab;

}) ();
