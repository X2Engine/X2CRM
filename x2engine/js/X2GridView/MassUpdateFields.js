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






x2.MassUpdateFields = (function () {

function MassUpdateFields (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        massActionName: 'updateFields'
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.MassAction.call (this, argsDict);
    this.progressBarLabel = this.translations['updated'];
    this.progressBarDialogTitle = this.translations['updateFieldprogressBarDialogTitle'];
    this.dialogTitle = this.massActionsManager.translations['updateField'];
    this.goButtonLabel = this.massActionsManager.translations['update'];
    this._init ();
}

MassUpdateFields.prototype = auxlib.create (x2.MassAction.prototype);

/**
 * @return object fields Returns selected field and field value in format expected by mass actions
 *  action
 */
MassUpdateFields.prototype.getFields = function () {
    var that = this;
    var updateFieldDialogSelector = '#' + this.dialogElem$.attr ('id');
    var inputContainer$ = that.dialogElem$.find ('.update-fields-inputs-container').
        find ('.update-fields-field-input-container');
    var fieldFieldSelector = $(updateFieldDialogSelector + ' .update-field-field-selector');
    var fieldName = $(fieldFieldSelector).val ();
    var fields = {};
    var fieldType = that.dialogElem$.find ('.update-fields-inputs-container').
        find ('.update-fields-field-input-container').attr ('data-type');

    switch (fieldType) {
        case 'rating':
            // count stars
            fields[fieldName] = $(fieldFieldSelector).next ().find ('.star-rating-control').
                find ('.star-rating-on').length;
            break;
        case 'boolean':
            fields[fieldName] = inputContainer$.find (':checkbox').is (':checked') ? 1 : 0;
            break;
        default:
            var tempName,
                tempVal,
                inputs = $(fieldFieldSelector).next().find (':input');

            if (fieldName === 'associationName') {
                // Gather the association data
                if (inputs[0].name)
                    tempName = inputs[0].name.replace(/^.*\[(.*)\]/, "$1");
                if (tempVal = $(inputs[0]).find(':selected').val())
                    fields[tempName] = tempVal;
                fields[fieldName] = $(inputs[1]).val();
            } else {
                var inputField = inputs.first ();
                if ($(inputField).attr ('type') === 'hidden') {
                    inputField = $(inputField).next ();
                }

                if ($(inputField).length) tempVal = $(inputField).val ();
                fields[fieldName] = tempVal;
            }
    }
    return fields;
};

MassUpdateFields.prototype.getExecuteParams = function () {
    var params = x2.MassAction.prototype.getExecuteParams.call (this);
    params['fields'] = this.getFields ();
    return params;
};

MassUpdateFields.prototype.openDialog = function () {
    var that = this;
    this.dialogElem$.find ('.update-field-field-selector').unbind ('change').change (function () {

        that.DEBUG && console.log ('update-field-field-selector: change');
        var inputName = $(this).val ();
        that._getUpdateFieldInput (inputName);
    });

    // kludge to get autocomplete to render with correct z-index
    if (this._getInputType () === 'link')  {
        this.dialogElem$.find ('.update-field-field-selector').change ();
    }

    x2.MassAction.prototype.openDialog.call (this);
};

/**
 * Used by update field mass action to dynamically construct field form
 * @param string inputName the name of the X2Fields field
 */
MassUpdateFields.prototype._getUpdateFieldInput = function (inputName) {
    var that = this; 
    that.DEBUG && console.log ('removing old input');
    
    var updateFieldDialogSelector = '#' + that.gridId + '-update-field-dialog';

    this.dialogElem$.find ('.update-fields-inputs-container').
        find ('.update-fields-field-input-container').children ().remove ();
    this.dialogElem$.find ('.update-fields-inputs-container').
        find ('.update-fields-field-input-container').append ($('<div>', {
            'class': 'x2-loading-icon updating-field-input-anim'
        }));
    $.ajax({
        url: that.massActionsManager.updateFieldInputUrl,
        dataType: 'json',
        type:'get',
        data:{
            modelName: that.massActionsManager.modelName,
            fieldName: inputName,
        },
        success: function (response) { 
            that.DEBUG && console.log ('getUpdateFieldInput: ajax ret: ' + response);
            var input = response.input;
            var type = response.field.type;
            var inputContainer$ = that.dialogElem$.find ('.update-fields-inputs-container').
                find ('.update-fields-field-input-container');
            inputContainer$.children ().remove (); // clear old input
            that.DEBUG && console.log ('replacing old input');
            inputContainer$.html (input);
            inputContainer$.attr ('data-type', type);
            if (type === 'link') {
                inputContainer$.find (':input').css ('z-index', '1000');
            }
        }
    });
};

MassUpdateFields.prototype._getInputType = function () {
    var that = this;
    var inputContainer$ = that.dialogElem$.find ('.update-fields-inputs-container').
        find ('.update-fields-field-input-container');
    var type = inputContainer$.attr ('data-type');
    return type;
};

MassUpdateFields.prototype._init = function () {
    var that = this;
};

return MassUpdateFields;

}) ();

