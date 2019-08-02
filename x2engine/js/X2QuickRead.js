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




x2.QuickRead = (function () {

function X2QuickRead (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        modelName: null,
        modelId: null,
        mode: 'dialog',
        afterRequest: function () {}
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.QuickCRUD.call (this, argsDict);
    this.dialogResizable = false;

    this.dialogTitle = this.modelName;
    this.viewRecordUrl = x2.QuickRead.viewRecordUrls[this.modelType];

    this._init ();
}

X2QuickRead.viewRecordUrls = {};
X2QuickRead.dialogTitles = {};
X2QuickRead.translations = {};

X2QuickRead.instantiateQuickReadLinks = function (container) {
    container = typeof container === 'undefined' ? document : container; 
    $(container).find ('.quick-read-link').each (function () {
        $(this).after ($('<a class="fa fa-eye pseudo-link" style="display: none" href="#"></a>').
            attr ({
                title: X2QuickRead.translations['View inline record details']
            }));
    });
    $('.quick-read-link ~ .fa-eye').unbind ('click.instantiateQuickReadLinks').bind (
        'click.instantiateQuickReadLinks', function () {
        new x2.QuickRead ({ 
            modelName: $(this).prev ().attr ('data-name'),
            modelId: $(this).prev ().attr ('data-id'),
            modelType: $(this).prev ().attr ('data-class'),
        });
        return false;
    });
};

$(function () { X2QuickRead.instantiateQuickReadLinks (); });

X2QuickRead.prototype = auxlib.create (x2.QuickCRUD.prototype);

X2QuickRead.prototype.requestDetails = function (callback) {
    var that = this;

    var data = $.extend (this.data, {
        x2ajax: true,
    });

    $.ajax ({
        type: 'post',
        url: this.viewRecordUrl + '?id=' + this.modelId, 
        data: data,
        success: function(response) {
            callback (response);
        }
    });
};

X2QuickRead.prototype.openQuickCRUDDialog = function () {

    var that = this;
    x2.QuickCRUD.prototype.openQuickCRUDDialog.call (this);

    this.requestDetails (function (response) {
        that._dialog.append(response);
        that._dialog.dialog('open');
        
        auxlib.onClickOutside (
            '.ui-dialog, .ui-datepicker',
            function () { 
                if ($(that._dialog).closest ('.ui-dialog').length) 
                    that._dialog.dialog ('close'); 
            }, true);
        that._dialog.find('.formSectionHide').remove();
    });

};

X2QuickRead.prototype._init = function () {
    if (this.mode === 'dialog') {
        this.openQuickCRUDDialog ();
    } else {
        this.requestDetails (this.afterRequest);
    }
};

return X2QuickRead;

}) ();
