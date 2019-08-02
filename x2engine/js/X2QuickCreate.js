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
 * Handles creation of record quick create dialogs
 */

x2.QuickCreate = (function () {

function QuickCreate (argsDict) {
    var that = this;
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        /**
         * @var object data to pass along with request for quick create form
         */
        data: {},
        /**
         * @var object attributes default attributes of new record
         */
        attributes: {},
        /**
         * @var function success callback called after successful record creation
         */
        success: function () {},
        validate: function () { return true; },
        enableFlash: true,
        /**
         * @var object dialogAttributes dialog settings 
         */
        dialogAttributes: {},
        translations: {
            create: 'Create',
            cancel: 'Cancel',
        }
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.QuickCRUD.call (this, argsDict);
    if (!QuickCreate.createRecordUrls[this.modelType]) throw new Error ('invalid model type');

    $.extend (this.dialogAttributes, {
        buttons: [
            {
                text: that.translations.create,
                click: function () {
                    that.submit (); 
                }
            },
            {
                text: that.translations.cancel,
                click: function () {
                    that._dialog.dialog ('close');
                }
            }
        ]
    });

    this.createRecordUrl = QuickCreate.createRecordUrls[this.modelType];
    this.dialogTitle = QuickCreate.dialogTitles[this.modelType];
    this.openQuickCreateDialog ();

}

QuickCreate.createRecordUrls = {};
QuickCreate.dialogTitles = {};

QuickCreate.prototype = auxlib.create (x2.QuickCRUD.prototype);

QuickCreate.prototype.submit = function () {
    this._handleFormSubmission (this._dialog.find ('form'));
};

/**
 * Open record creation dialog 
 */
QuickCreate.prototype.openQuickCreateDialog = function () { 

    var that = this;

    x2.QuickCRUD.prototype.openQuickCRUDDialog.call (this);

    var data = $.extend (this.data, {
        x2ajax: true,
        validateOnly: true,
    });
    for (var attrName in this.attributes) {
        data[this.modelType + '[' + attrName + ']'] = this.attributes[attrName];
    }

    $.ajax ({
        type: 'post',
        url: this.createRecordUrl, 
        data: data,
        dataType: 'json',
        success: function(response) {
            that._dialog.append(response.page);
            that._dialog.dialog('open');
            
            auxlib.onClickOutside (
                '.ui-dialog, .ui-datepicker, .ui-datepicker-header',
                function () { 
                    if ($(that._dialog).closest ('.ui-dialog').length) 
                        that._dialog.dialog ('close'); 
                }, true);
            that._dialog.find('.formSectionHide').remove();
            var form = that._dialog.find('form');
            var submit = that._dialog.find('[type="submit"]');
            submit.hide ();
        }
    });
};

QuickCreate.prototype.closeDialog = function () {
    that._dialog.empty ().remove ()
};

QuickCreate.prototype._handleFormSubmission = function (form) {
    if (!this.validate ()) return;
    //if (form.find ('.error').length) return;
    var that = this;
    var formdata = form.serializeArray();

    formdata = formdata.concat ([{
    /* this form data object indicates this is an ajax request 
       note: yii already uses the name 'ajax' for it's ajax calls, so we use 'x2ajax' */
        name: 'x2ajax',
        value: '1'
    }]);

    $.ajax ({
        type: 'post',
        url: this.createRecordUrl, 
        data: formdata, 
        dataType: 'json',
        success: function(response) {
            that._dialog.empty ();
            if (response['status'] === 'success' || response[0] === 'success') {
                that._dialog.remove ();
                if (that.enableFlash)
                    x2.topFlashes.displayFlash (
                        response.message, 'success', 'clickOutside', false);
                that.success (response.attributes);
            } else {
                var page = response.page;
                that._dialog.append(page);
                var submit = that._dialog.find('[type="submit"]');
                submit.hide ();
                that._dialog.find('.formSectionHide').remove();
                that._dialog.find('.create-account').remove();
                var submit = that._dialog.find('input[type="submit"]');
                var form = that._dialog.find('form');
                $(form).submit (function () {
                    that._handleFormSubmission (form);
                    return false;
                });
            }
        }
    });
};

return QuickCreate;

}) ();
