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
 * Handles creation of record quick create dialogs
 */

x2.QuickCreate = (function () {

function QuickCreate (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        /**
         * @var string modelType name of X2Model child that has X2QuickCreateBehavior  
         */
        modelType: null,
        /**
         * @var object dialogAttributes dialog settings 
         */
        dialogAttributes: {},
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
        enableFlash: true
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    if (!QuickCreate.createRecordUrls[this.modelType]) throw new Error ('invalid model type');
    this.createRecordUrl = QuickCreate.createRecordUrls[this.modelType];
    this.dialogTitle = QuickCreate.dialogTitles[this.modelType];
    this.openQuickCreateDialog ();
}

QuickCreate.createRecordUrls = {};
QuickCreate.dialogTitles = {};

/**
 * Open record creation dialog 
 */
QuickCreate.prototype.openQuickCreateDialog = function () { 

    var that = this;

    this._dialog = $('<div>');
    this._dialog.dialog ($.extend ({
        title: this.dialogTitle,
        autoOpen: false,
        resizable: true,
        width: '650px',
        show: 'fade',
        hide: 'fade',
        close: function () {
            that._dialog.dialog ('destroy');
            that._dialog.remove ();
        }
    }, this.dialogAttributes));

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
        success: function(response) {
            that._dialog.append(response);
            that._dialog.dialog('open');
            
            auxlib.onClickOutside (
                '.ui-dialog, .ui-datepicker',
                function () { 
                    if ($(that._dialog).closest ('.ui-dialog').length) 
                        that._dialog.dialog ('close'); 
                }, true);
            that._dialog.find('.formSectionHide').remove();
            var submit = that._dialog.find('[type="submit"]');
            var form = that._dialog.find('form');
            $(form).submit (function () {
                that._handleFormSubmission (form);
                return false;
            });
        }
    });
};

QuickCreate.prototype.closeDialog = function () {
    that._dialog.empty ().remove ()
};


QuickCreate.prototype._handleFormSubmission = function (form) {
    if (form.find ('.error').length) return;
    var that = this;
    var formdata = form.serializeArray();

    formdata = formdata.concat ([{
    /* this form data object indicates this is an ajax request 
       note: yii already uses the name 'ajax' for it's ajax calls, so we use 'x2ajax' */
        name: 'x2ajax',
        value: '1'
    }, {
        name: 'quickCreateOnly',
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
                    x2.topFlashes.displayFlash (response.message, 'success', 'clickOutside', false);
                that.success (response.attributes);
            } else if (response['status'] === 'userError') {
                if(typeof response['page'] !== 'undefined') {
                    that._dialog.append(response['page']);
                    that._dialog.find('.formSectionHide').remove();
                    that._dialog.find('.create-account').remove();
                    var submit = that._dialog.find('input[type="submit"]');
                    var form = that._dialog.find('form');
                    $(submit).unbind ('click').bind ('click', function() {
                        return that._handleFormSubmission (form);
                    }, true);
                }
            }
        }
    });
};

return QuickCreate;

}) ();
