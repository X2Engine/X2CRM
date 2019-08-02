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




x2.X2FlowApiCall = (function () {

function X2FlowApiCall (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        form$: null,
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this.enableJsonCheckbox$ = this.form$.find ('[name="jsonPayload"]').
        find ('input[type="checkbox"]');
    this.jsonBlobContainer$ = this.form$.find ('[name="jsonBlob"]');
    this.attributes$ = $('#x2flow-attributes');
    this.attributesButton$ = $('#x2flow-add-attribute');
    this.method$ = this.form$.find ('[name="method"]').find ('select');

    x2.X2FlowItem.call (this, argsDict);
}

X2FlowApiCall.prototype = auxlib.create (x2.X2FlowItem.prototype);

/**
 * Restore config menu to its original state 
 */
X2FlowApiCall.prototype.destroy = function () {
    this.attributes$.show ();
    this.attributesButton$.show ();
};

/**
 * Hide the JSON blob textarea and show the key-value pairs interface or vice versa
 */
X2FlowApiCall.prototype._hideShowJSONInterface = function (show) {
    this.jsonBlobContainer$.toggle (!show);
    this.attributes$.toggle (show);
    this.attributesButton$.toggle (show);
};

X2FlowApiCall.prototype._setUpFormInteraction = function () {
    var that = this;
    this.enableJsonCheckbox$.click (function () {
        that._hideShowJSONInterface (!$(this).is (':checked'));
    });
    this._hideShowJSONInterface (!this.enableJsonCheckbox$.is (':checked'));

    this.method$.change (function () {
        if (that.method$.val () === 'GET') {
            if (that.enableJsonCheckbox$.is (':checked'))
                that.enableJsonCheckbox$.click ();
            that.enableJsonCheckbox$.attr ('disabled', 'disabled');
        } else {
            that.enableJsonCheckbox$.removeAttr ('disabled');
        }
    }).change ();
};

X2FlowApiCall.prototype._init = function () {
    this._setUpFormInteraction ();
};

return X2FlowApiCall;

}) ();
