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
 * Front end of X2ModelConversionWidget.php
 */

x2.X2ModelConversionWidget = (function () {

function X2ModelConversionWidget (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    x2.Widget.call (this, argsDict);
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        // button to convert record
        buttonSelector: null,
        translations: {},
        // id of model to convert
        modelId: null,
        // class to convert model to
        targetClass: null,
        // conversion error summary
        errorSummary: null,
        conversionIncompatibilityWarnings: null,
        conversionFailed: null
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this._button$ = $(this.buttonSelector);
    this._conversionWarningDialog$ = $('#' + this.namespace + 'conversion-warning-dialog');
    this._init ();
}

X2ModelConversionWidget.prototype = auxlib.create (x2.Widget.prototype);

/**
 * Call convert action or, if incompatibility warnings are present, display a confirmation dialog 
 */
X2ModelConversionWidget.prototype._convert = function () {
    var that = this;
    var pathname = window.location.href; 

    pathname = pathname.replace('/id/'+this.modelId, '/');
    pathname = pathname.replace('/'+this.modelId, '/');

    // no incompatibilities present. convert the lead
    if (!this.conversionIncompatibilityWarnings.length) {
        window.location = pathname + 'convert?id=' + this.modelId + '&targetClass=' + 
            this.targetClass;
        return false;
    }

    if (this._conversionWarningDialog$.closest ('.ui-dialog').length) {
        this._conversionWarningDialog$.dialog ('open');
    } else {
        // show the warning dialog to the user
        this._conversionWarningDialog$.dialog ({
            title: this.translations.conversionWarning,
            autoOpen: true,
            width: 500,
            buttons: [
                {
                    text: this.translations.convertAnyway,
                    click: function () {
                        window.location = pathname + 'convert?force=1&id=' + that.modelId + '&' +
                            'targetClass=' + that.targetClass;
                    }
                },
                {
                    text: this.translations.Cancel,
                    click: function () {
                        that._conversionWarningDialog$.dialog ('close');
                    }
                }
            ]
        });
    }
    return false;
};

X2ModelConversionWidget.prototype._setUpButtonBehavior = function () {
    var that = this;
    this._button$.click (function () {
        that._convert ();
    });
};

X2ModelConversionWidget.prototype._init = function () {
    this._setUpButtonBehavior ();
    if (this.conversionFailed) {
        $('#main-column').append (this.errorSummary);
    }
};

return X2ModelConversionWidget;

}) ();
