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
 * RelationshipsManager prototype 
 * Instantiates a quick create widget
 */

if (typeof x2 === 'undefined') x2 = {};

x2.RelationshipsManager = (function () {

function RelationshipsManager (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        element: null, // the create relationship button

        /* <attr name>: <attr value>, default form values. If values are prefixed by 'js:', they 
          will be eval'd and their return value will be treated as the default value. */
        attributeDefaults: {}, 

        createRecordUrl: '', // url of create action of this type
        modelId: null, // id of record with which to create the new relationship
        modelType: null, // class of record with which to create the new relationship
        relatedModelType: null, // class of the second record in the relationship
        tooltip: '', // tooltip to be added to the create button
        dialogTitle: '', // title of quick create dialog
        
        // used to determine which fields to update after quick create form submits
        isViewPage: true, 
        
        // if set, updated after new record is created with new record's name
        lookupFieldElement: null,
        afterCreate: function () {}
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._dialog; // the dialog element
    this._dialogInactive; // bool, dialog does/doesn't have create form 

    this._init ();
}

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
 * Remove dialog and delete object
 */
RelationshipsManager.prototype.destructor = function () {
    if ($(this._dialog).hasClass ('ui-dialog')) {
        $(this._dialog).dialog ('destroy');
    }
    $(this._dialog).remove ();
    delete this;
};

/*
Private instance methods
*/

/**
 * Set values of create form inputs to defaults
 */
RelationshipsManager.prototype._setDefaults = function () {
    for (var attrName in this.attributeDefaults) {
        var attrDefault = this.attributeDefaults[attrName];
        var attrVal;
        if (attrDefault.match (/^js:/)) {
            attrDefault = attrDefault.replace (/^js:/, '');
            attrVal = eval (attrDefault);
        } else {
            attrVal = attrDefault;
        }

        $(this._dialog).find ('[name="' + this.relatedModelType + '[' + attrName + ']"]')
            .val (attrVal);
    }
};

/**
 * Update record detail view with data
 * @param string data The updated detail view
 */
RelationshipsManager.prototype._updateDetailView = function (data) {
    if (data) {
        $('#' + this.modelType.toLowerCase () + '-detail-view').replaceWith (data);
    }
};

/**
 * Update record formview with data
 * @param dictionary data <attr name>: <attr value>
 */
RelationshipsManager.prototype._setFormViewValues = function (data) {
    for (var attrName in data) {
        $('#' + this.modelType.toLowerCase () + '-form').
            find ('[name="' + this.modelType + '[' + attrName + ']"]')
            .val (data[attrName]);
    }
};

/**
 * Updates lookup field with name of new record. If present, the hidden id field will
 * also be updated.
 */
RelationshipsManager.prototype._updateLookupField = function (name, id) {
    if (!this.lookupFieldElement) return;

    var that = this;
    if ($(that.lookupFieldElement).is ('input')) {
        $(that.lookupFieldElement).val (name);
    } else {
        $(that.lookupFieldElement).html (name);
    }

    if ($(that.lookupFieldElement).siblings ('input[type="hidden"]')) {
        $(that.lookupFieldElement).siblings ('input[type="hidden"]').val (id);
    }
};

/**
 * Handles submission of create record form
 * @param string form ajax request create record form
 */
RelationshipsManager.prototype._handleFormSubmission = function (form) {
    var that = this;
    var formdata = form.serializeArray();

    /* this form data object indicates this is an ajax request 
       note: yii already uses the name 'ajax' for it's ajax calls, so we use 'x2ajax' */
    var x2ajax = {
        name: 'x2ajax',
        value: '1'
    }; 

    var modelName = {
        name: 'ModelName',
        value: this.modelType
    };

    var modelId = {
        name: 'ModelId',
        value: this.modelId
    };

    formdata.push(x2ajax);
    formdata.push(modelName);
    formdata.push(modelId);

    $.post(
        this.createRecordUrl, 
        formdata, 
        function(response) {

            response = $.parseJSON(response);
    
            // clean up javascript so we can open this window again without error
            $('body').off('click','#' + that.modelType + '_assignedTo_groupCheckbox'); 
            that._dialog.empty(); // clean up dialog
                
            if(response['status'] == 'success') {
                that.afterCreate (response.attributes);
                that._dialog.dialog ('close');
    
                // indicate that we can append a create action page to this dialog
                that._dialogInactive = true;
    
                if (that.isViewPage)
                    that._updateDetailView (response['data']);
                else 
                    that._setFormViewValues (response['data']);

                that._updateLookupField (response['name'], response['id']);
    
            } else if (response['status'] === 'userError') {
                if(typeof response['page'] !== 'undefined') {
                    
                    that._dialog.append(response['page']);
                    that._dialog.find('.formSectionHide').remove();
                    that._dialog.find('.create-account').remove();
                    var submit = that._dialog.find('input[type="submit"]');
                    var form = that._dialog.find('form');
    
                    $(submit).unbind ('click').bind ('click', function() {
                        return that._handleFormSubmission (form);
                    });
                }
            }
        });

    return false; 
};

/**
 * Creates record create dialog and sets up open on click behavior
 */
RelationshipsManager.prototype._setUpOpenDialogBehavior = function () {
    var that = this;

    this._dialog = $('<div>');

    this._dialog.dialog ({
        title: this.dialogTitle,
        autoOpen: false,
        resizable: true,
        width: '650px',
        show: 'fade',
        hide: 'fade'
    });

    // indicate that we can append a create action page to this dialog
    that._dialogInactive = true;

    $(this.element).unbind ('click')
        .bind ('click', function() {

        var data = {
            x2ajax: true
        };
        data[x2.Widget.NAMESPACE_KEY] = 'RelationshipsManager';

        if (that._dialogInactive) {
            $.ajax({
                type: 'post',
                url: that.createRecordUrl, 
                data: data,
                dataType: 'json',
                success: function(response) {
                    var page = response.page;

                    that._dialog.append(page);
                    that._dialog.dialog('open');
                    /* indicate that a create-action page has been appended, don't do it until 
                       the old one is submitted or cleared. */
                    that._dialogInactive = false;

                    that._dialog.find('.formSectionHide').remove();
                    //that._dialog.find('.create-account').remove();

                    var submit = that._dialog.find('input[type="submit"]');
                    var form = that._dialog.find('form');
                    $(submit).unbind ('click').bind ('click', function() {
                        return that._handleFormSubmission (form);
                    });
                    that._setDefaults ();
                }
            });
        } else {
            that._dialog.dialog('open');
        }
    });
};

RelationshipsManager.prototype._init = function () {
    $(this.element).qtip({content: this.tooltip});
    this._setUpOpenDialogBehavior ();
};

return RelationshipsManager;

}) ();


