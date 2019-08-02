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




x2.NewListFromSelection = (function () {

function NewListFromSelection (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        massActionName: 'createList'
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.MassAction.call (this, argsDict);
    this.goButtonLabel = this.massActionsManager.translations['create'];
    this.dialogTitle = this.massActionsManager.translations['newList'];
}

NewListFromSelection.prototype = auxlib.create (x2.MassAction.prototype);

NewListFromSelection.prototype.validateMassActionDialogForm = function () {
    var that = this;
    var newListName = this.dialogElem$.find ('.new-list-name');
    auxlib.destroyErrorFeedbackBox ($(newListName));
    var listName = $(newListName).val ();
    if(listName === '' || listName === null) {
        auxlib.createErrorFeedbackBox ({
            prevElem: $(newListName),
            message: that.translations['blankListNameError']
        });
        $('#mass-action-dialog-loading-anim').remove ();
        this.dialogElem$.dialog ('widget').find ('.x2-dialog-go-button').show ();
        return false;
    }
    return true;
};

NewListFromSelection.prototype.afterExecute = function () {
    var that = this;
    var newListName = this.dialogElem$.find ('.new-list-name');
    $(newListName).val ('');
    this.dialogElem$.dialog ('close');
    this.massActionsManager.massActionInProgress = false;
};

NewListFromSelection.prototype.getExecuteParams = function () {
    var that = this;
    var params = x2.MassAction.prototype.getExecuteParams.call (this);
    var newListName = this.dialogElem$.find ('.new-list-name');
    var listName = $(newListName).val ();
    params['listName'] = listName;
    return params;
};

/**
 * This complicated method is used to switch mass actions (from new list to add to list) after
 * the first batch is completed.
 * @return MassAddToList
 */
NewListFromSelection.prototype.convertToAddToList = function (listId, dialogState) {
    var that = this;
    var addToList = this.massActionsManager.massActionObjects['MassAddToList'];
    var newListName = this.dialogElem$.find ('.new-list-name');
    var listName = $(newListName).val ();
    addToList.addListOption (listId, listName);
    addToList.setListId (listId);
    addToList.progressBar = this.progressBar;
    addToList.recordCount = this.recordCount;
    dialogState.superExecuteParams.listId = listId;
    dialogState.superExecuteParams.massAction = addToList.massActionName;

    return addToList;
};

/**
 * Overrides parent method so that after the first batch is completed, requests are made to add to
 * that list. This is accomplished by swapping out the mass action objects after the first 
 * response. This method also handles the case where the list could not be created successfully.
 */
NewListFromSelection.prototype._nextBatch = function (dialog, dialogState) {
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

            if (response['successes'] === -1) { // list could not be created
                dialog.dialog ('close');
                return;
            }

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
                listId = response['listId'];

                if (!dialogState.stop && !dialogState.pause) { 
                    that.convertToAddToList (listId, dialogState)._nextBatch (dialog, dialogState);
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
                            that.convertToAddToList (listId, dialogState)._nextBatch (
                                dialog, dialogState);
                        }
                    }, 500)
                }
            }
        }
    });
};


return NewListFromSelection;

}) ();

