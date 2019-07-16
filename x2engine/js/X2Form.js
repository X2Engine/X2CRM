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
 * Manages behavior of settings forms
 */
x2.X2Form = (function () {

function X2Form (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        formSelector: '',
        submitUrl: '',
        formModelName: '',
        translations: {},
        namespace: '',
        /**
         * @var bool if true, form submission will be done via ajax 
         */
        ajaxForm: false,
        onAjaxSuccess: function () {}
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this.element$ = this._form$ = $(this.formSelector);
    this.element$.data (X2Form.dataKey, this);
    this._init ();
}

X2Form.dataKey = 'x2-form';

/**
 * Returns X2Form instance associated with jQuery object
 * @param string|object
 * @return Widget 
 */
X2Form.getInstance = function (elem) {
    return $(elem).data (X2Form.dataKey);
};

X2Form.prototype.findElemByAttr = function (attr) {
    return this.element$.find ('[name=\"' + this.formModelName + '[' + attr + ']\"]');
};

X2Form.prototype.ajaxSubmit = function () {
    var that = this;
    $.ajax ({
        url: this.submitUrl,
        type: 'POST',
        data: [
            {
                name: 'x2ajax', 
                value: '1'
            },
            {
                name: 'saveOnly', 
                value: '1'
            },
            {
                name: x2.Widget.NAMESPACE_KEY, 
                value: this.namespace
            }
        ].concat (this.element$.serializeArray ()),
        dataType: 'json',
        success: function (data) {
            that.onAjaxSuccess (data);
        }
    });
};

X2Form.prototype._setUpAjaxSubmission = function () {
    var that = this;
    this.element$.on ('submit', function () {
        that.ajaxSubmit (); 
        return false;
    });
};

X2Form.prototype._init = function() {
    if (this.ajaxForm) this._setUpAjaxSubmission ();
};

return X2Form;

}) ();
