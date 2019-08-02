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




if (typeof x2 === 'undefined') x2 = {};

x2.MassAction = (function () {

/**
 * Abstract base for mass action classes 
 */
function MassAction (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        progressBarLabel: '',
        massActionsManager: null,
        updateAfterExecute: true,
        recordCount: null,
        massActionName: '',
        allowMultiple: true,
        disableDialog: false,
        /**
         * Set to true to enable validation via ajax of mass action dialog form before executing
         * mass action. In case of validation error, there should be an entry in the response 
         * JSON with the key set to "form" and the value set to the rerendered form.
         */
        enableAjaxValidation: false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this.dialogElem$ = $('#' + this.massActionsManager.gridId + '-' + this.massActionName + 
        '-dialog');
    if (this.enableAjaxValidation) {
        this.form$ = this.dialogElem$.find ('form');
    }
    this.translations = this.massActionsManager.translations;
}

MassAction.prototype.validateMassActionDialogForm = function () {
    return true;
};

/**
 * Gets called by owner when mass action ui is shown
 */
MassAction.prototype.showUI = function () {};

MassAction.prototype.afterExecute = function () {
    var that = this;

    if (!this.disableDialog) {
        this.dialogElem$.dialog ('close');
    }
    this.massActionsManager.massActionInProgress = false;
    this.massActionsManager._updateGrid ();
};

MassAction.prototype.afterSuperExecute = function () {
    this.massActionsManager.massActionInProgress = false;
};

MassAction.prototype.afterValidationFailure = function (data) {};

MassAction.prototype.validateForm = function () {
    var that = this;
    if (!(this._superExecute ^ this._execute)) throw new Error ('invalid internal state');
    if (this.enableAjaxValidation) { 
        if (this._superExecute) {
            var data = $.extend ({
                gvSelection: [null],
                massAction: this.massActionName
            }, x2.forms.flattenSerializedArray (this.form$));
        } else {
            var data = this.getExecuteParams ();
        }
        $.ajax({
            url: that.massActionsManager.massActionUrl,
            type:'POST',
            data: data,
            success: function (data) { 
                var response = JSON.parse (data);
                if (that._execute) {
                    that._displayFlashes (response);
                }
                if (response['form']) {
                    that.form$.replaceWith (response.form);
                    that.form$ = that.dialogElem$.find ('form');
                    that.afterValidationFailure (response);
                    x2.forms.inputLoadingStop (
                        that.dialogElem$.dialog ('widget').find ('.x2-dialog-go-button'));
                    if (that._superExecute)
                        that._superExecute = false;
                    else
                        that._execute = false;
                } else {
                    if (that._execute && response['success']) {
                        that.afterExecute ();
                    } else if (that._superExecute) {
                        that.superExecute ();
                    }
                }
            }
        });
    } else {
        if (this._superExecute) this.superExecute ();
        else this.execute ();
    }
};

/**
 * Open dialog for mass action form
 */
MassAction.prototype.openDialog = function () {
    var that = this; 

    if (this.disableDialog) {
        this.massActionsManager.loading ();
        if (this.massActionsManager._allRecordsOnAllPagesSelected) {
            that.superExecute ();
        } else {
            that.execute ();
        }
        return;
    }

    // cache the total item count to ensure that number of records displayed doesn't change while
    // the dialog is open
    this.recordCount = this.massActionsManager.totalItemCount; 

    var dialog = this.dialogElem$;
    $('#' + that.massActionsManager.gridId + '-mass-action-buttons .mass-action-button').
        attr ('disabled', 'disabled');

    $(dialog).show ();

    this._superExecute = false;
    this._execute = false;

    $(dialog).dialog ({
        title: this.dialogTitle,
        autoOpen: true,
        width: 500,
        buttons: [
            {
                text: this.goButtonLabel,
                'class': 'x2-dialog-go-button',
                click: function () { 
                    if (that.validateMassActionDialogForm ()) {
                        x2.forms.inputLoading (
                            $(dialog).dialog ('widget').find ('.x2-dialog-go-button'), false);
                        if (that.massActionsManager._allRecordsOnAllPagesSelected) {
                            that._superExecute = true;
                            that.validateForm ();
                        } else {
                            that._execute = true;
                            that.validateForm ();
                        }
                    }
                }
            },
            {
                text: that.translations['cancel'],
                click: function () { 
                    $(dialog).dialog ('close'); 
                }
            }
        ],
        close: function () {
            $(dialog).hide ();
            x2.forms.inputLoadingStop (
                $(dialog).dialog ('widget').find ('.x2-dialog-go-button'));
            $(dialog).dialog ('widget').find ('.x2-dialog-go-button').show ();
            $('#' + that.massActionsManager.gridId + '-mass-action-buttons .mass-action-button').
                removeAttr ('disabled', 'disabled');
            if (!that._superExecute && !that._execute) 
                that.massActionsManager.massActionInProgress = false;
            $(dialog).dialog ('destroy').hide (); 
        }
    });

};

/**
 * Execute mass action on checked records
 */
MassAction.prototype.execute = function () {
    var that = this;
    var selectedRecords = that.massActionsManager._getSelectedRecords () 
    $.ajax({
        url: that.massActionsManager.massActionUrl,
        type:'POST',
        data:this.getExecuteParams (),
        success: function (data) { 
            var response = JSON.parse (data);
            if (response['success']) {
                that.afterExecute ();
            } 
            that._displayFlashes (response);
        }
    });
};

/**
 * Ensures that grid view is still displaying the records that the user is trying to operate on
 */
MassAction.prototype._beforeNextBatch = function () {
    // ensures that # of records in grid hasn't changed since first mass action dialog in sequence
    // was opened.
    // also ensures that the max count visible in the progress bar is the same as the count
    // reported to the server
    if (this.massActionsManager.totalItemCount !== this.recordCount ||
        this.massActionsManager.totalItemCount !== this.progressBar.getMax ()) {

        throw new Error ('invalid selection');
    }
};

MassAction.prototype._nextBatch = function (dialog, dialogState) {
    var that = this;
    this._beforeNextBatch ();
    dialogState.batchOperInProgress = true;
    $.ajax({
        url: that.massActionsManager.massActionUrl,
        type:'POST',
        data: $.extend (dialogState.superExecuteParams, {
            uid: dialogState.uid
        }),
        dataType: 'json',
        success: function (data) { 
            dialogState.batchOperInProgress = false;
            var response = data;
            that.massActionsManager._displayFlashesList (
                response, $(dialog).find ('.super-mass-action-feedback-box'));
            if (response['failure']) {
                dialogState.loadingAnim$.hide ();
                $(dialog).append ($('<span>', {
                    text: response['errorMessage'],
                    'class': 'error-message'
                }));
                return;
            } else if (response['complete']) {
                $(dialog).dialog ('close');
            } else if (response['batchComplete']) {
                that.progressBar.incrementCount (response['successes']);
                dialogState.uid = response['uid'];
                if (!dialogState.stop && !dialogState.pause) { 
                    that._nextBatch (dialog, dialogState);
                } else {
                    dialogState.loadingAnim$.hide ();
                    if (dialogState.stop) {
                        that.massActionsManager._updateGrid (function () {
                            that.afterSuperExecute ();
                        });
                        return;
                    }

                    var interval = setInterval (function () { 
                        if (dialogState.stop || !dialogState.pause) {
                            clearInterval (interval);
                        } 
                        if (!dialogState.stop && !dialogState.pause) {
                            dialogState.loadingAnim$.show ();
                            that._nextBatch (dialog, dialogState);
                        }
                    }, 500)
                }
            }
        }
    });
};

/**
 * Execute mass action on all records on all pages
 */
MassAction.prototype.superExecute = function (uid) {
    var that = this;
    var uid = typeof uid === 'undefined' ? null : uid; 
    if (that.dialogElem$ !== null && that.dialogElem$.closest ('.ui-dialog').length)  
        that.dialogElem$.dialog ('close');
    this.progressBarDialog$ = $(this.massActionsManager.progressBarDialogSelector);
    this.progressBar = this.progressBarDialog$.find ('.x2-progress-bar-container').
        data ('progressBar');
    this.progressBar.updateLabel (this.progressBarLabel);
    var dialogState = {
        pause: false,
        stop: false,
        uid: uid,
        loadingAnim$: null,
        batchOperInProgress: false,
        superExecuteParams: this.getSuperExecuteParams ()
    };
    //console.log ('superExecute');
    this.progressBarDialog$.dialog ({
        title: this.progressBarDialogTitle, 
        autoOpen: true,
        modal: true,
        width: 500,
        buttons: [
            { 
                text: this.translations['pause'],
                'class': 'pause-button',
                click: function () {
                    $(this).dialog ('widget').find ('.pause-button').hide ();
                    $(this).dialog ('widget').find ('.resume-button').show ();
                    dialogState.pause = true;
                }
            },
            { 
                text: this.translations['resume'],
                'class': 'resume-button',
                'style': 'display: none;',
                click: function () {
                    $(this).dialog ('widget').find ('.resume-button').hide ();
                    $(this).dialog ('widget').find ('.pause-button').show ();
                    dialogState.pause = false;
                }
            },
            { 
                text: this.translations['stop'],
                'class': 'stop-button',
                click: function () {
                    $(this).dialog ('close');
                }
            }
        ],
        /*
        Opens the dialog and starts making requests to perform mass updates on batches. Updates
        progress bar as records are updated.
        */
        open: function () {
            var dialog = this;
            that._nextBatch (dialog, dialogState);
        },
        close: function () {
            $(this).dialog ('destroy');
            dialogState.stop = true;
            if (dialogState.uid !== null) {
                $.ajax({
                    url: that.massActionsManager.massActionUrl,
                    type:'POST',
                    data: $.extend (dialogState.superExecuteParams, {
                        uid: dialogState.uid,
                        clearSavedIds: true
                    }),
                });
            }
            if (!dialogState.batchOperInProgress) that.massActionsManager._updateGrid (
                function () {
                    that.afterSuperExecute ();
                });
        }
    });
    dialogState.loadingAnim$ = $('<div>', {
        'class': 'x2-loading-icon updating-field-input-anim',
        style: 'float: left; margin-right: 14px',
    });
    this.progressBarDialog$.dialog ('widget').find ('.pause-button').before (
        dialogState.loadingAnim$);

};

MassAction.prototype.getExecuteParams = function () {
    var params = {};
    params['massAction'] = this.massActionName;
    params['gvSelection'] = this.massActionsManager._getSelectedRecords ();
    if (this.enableAjaxValidation) {
        $.extend (params, x2.forms.flattenSerializedArray (this.form$));
    }
    return params;
};

MassAction.prototype.getSuperExecuteParams = function () {
    var params = this.getExecuteParams ();
    params['superCheckAll'] = true;
    params['totalItemCount'] = this.massActionsManager.totalItemCount;
    params['idChecksum'] = this.massActionsManager.idChecksum;
    updateParams = $('#' + this.massActionsManager.gridId).gvSettings (
        'getUpdateParams', this.massActionsManager.sortStateKey);

    params = $.extend (params, updateParams);
    return params;
};

MassAction.prototype._displayFlashes = function (flashes) {
    $('#x2-gridview-flashes-container').remove ();
    var flashesManager = new x2.Flashes ({
        containerId: 'x2-gridview-flashes-container', 
        translations: $.extend ({}, x2.flashes.translations, this.translations), 
        expandWidgetSrc: x2.flashes.expandWidgetSrc,
        closeWidgetSrc: x2.flashes.closeWidgetSrc,
        collapseWidgetSrc: x2.flashes.collapseWidgetSrc
    })
    flashesManager.displayFlashes (flashes, true);
    flashesManager.container$.width ($('#content-container').width () - 5);
    $(window).unbind ('resize.contentContainer').bind ('resize.contentContainer', function () {
        $(flashesManager.container$).width ($('#content-container').width () - 5);
    });
};

MassAction.prototype._init = function () {};

return MassAction;

}) ();

