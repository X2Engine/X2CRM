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

x2.WorkflowManager = (function () {

function WorkflowManager (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;

    var defaultArgs = {
        modelId: null,
        modelName: '',
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.WorkflowManagerBase.call (this, argsDict);

    this._init ();
}

WorkflowManager.REQUIRE_ALL = 1;

WorkflowManager.prototype = auxlib.create (x2.WorkflowManagerBase.prototype);

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
 * JS equivalent of Workflow::checkStageRequirement ()
 * @param number stageNumber
 * @param array workflowStatus
 * @return bool 
 */
WorkflowManager.prototype.checkStageRequirement = function (stageNumber, workflowStatus) {
    var requirementMet = true;

    // check if all stages before this one are complete
    if(parseInt (workflowStatus['stages'][stageNumber]['requirePrevious'], 10) ===
       WorkflowManager.REQUIRE_ALL) {    

        for(var i=1; i<stageNumber; i++) {
            if(!workflowStatus['stages'][i]['complete']) {
                requirementMet = false;
                break;
            }
        }
    } else if(parseInt (workflowStatus['stages'][stageNumber]['requirePrevious'], 10) < 0) { 
        // or just check if the specified stage is complete

        if(!workflowStatus['stages']
            [ -1 * parseInt (workflowStatus['stages'][stageNumber]['requirePrevious'], 10) ]
            ['complete']) {

            requirementMet = false;
        }
    }
    return requirementMet;
};
        
WorkflowManager.prototype.startWorkflowStage = function (workflowId,stageNumber,callback) {
    var that = this;
    $.ajax({
        url: that.startStageUrl,
        dataType: 'json',
        type: "GET",
        data: "workflowId="+workflowId+"&stageNumber="+stageNumber+"&modelId="+
            that.modelId + '&type=' + that.modelName + '&renderFlag=0',
        success: function(response) {
            callback (response['workflowStatus'], response['flashes']);
            x2.Notifs.updateHistory();
        }
    });
};

WorkflowManager.prototype.completeWorkflowStage = function (workflowId,stageNumber,callback) {
    var that = this;
    $.ajax({
        url: that.completeStageUrl,
        type: 'GET',
        dataType: 'json',
        data: "workflowId="+workflowId+"&stageNumber="+stageNumber+"&modelId="+
            that.modelId + '&type=' + that.modelName + '&renderFlag=0',
        success: function(response) {
            callback (response['workflowStatus'], response['flashes']);
            x2.Notifs.updateHistory();
        }
    });
};

WorkflowManager.prototype.workflowCommentDialog = function (workflowId,stageNumber,callback) {
    var that = this;

    $('#workflowCommentDialog').dialog(
        'option','title',that.translations['Comment Required']);

    $('#workflowCommentWorkflowId').val(workflowId);
    $('#workflowCommentStageNumber').val(stageNumber);
    
    $('#workflowComment').css('border','1px solid black');
    $('#workflowComment').val('')
    $('#workflowCommentDialog').dialog('open');
    $('#workflowCommentDialog').data ('callback', callback);
};

WorkflowManager.prototype.completeWorkflowStageComment = function (callback) {
    var that = this;
    var comment = $.trim($('#workflowComment').val());
    if(comment.length < 1) {
        $('#workflowComment').css('border','1px solid red');
    } else {
        $.ajax({
            url: that.completeStageUrl,
            type: 'GET',
            dataType: 'json',
            data: 'workflowId='+$('#workflowCommentWorkflowId').val()+'&stageNumber='+
                $('#workflowCommentStageNumber').val()+
                '&modelId='+that.modelId+"&type="+that.modelName+'&comment='+
                encodeURI(comment) + '&renderFlag=0',
            success: function(response) {
                callback (response['workflowStatus'], response['flashes']);
                x2.Notifs.updateHistory();
            }
        });
        $('#workflowCommentDialog').dialog('close');
    }
};

WorkflowManager.prototype.revertWorkflowStage = function (workflowId,stageNumber,callback) {
    var that = this;
    $.ajax({
        url: that.revertStageUrl,
        type: 'GET',
        dataType: 'json',
        data: 'workflowId='+workflowId+'&stageNumber='+stageNumber+
            '&modelId='+that.modelId+"&type="+that.modelName + '&renderFlag=0',
        success: function(response) {
            callback (response['workflowStatus'], response['flashes']);
            x2.Notifs.updateHistory();
        }
    });
};
        

/*
Private instance methods
*/

WorkflowManager.prototype._setUpCommentDialog = function () {
    var that = this;
    $("#workflowCommentDialog").dialog({
        autoOpen:false,
        resizable: false,
        modal: true,
        show: "fade",
        hide: "fade",
        width:400,
        buttons:[
            {
                click: function() {
                    that.completeWorkflowStageComment(
                        $('#workflowCommentDialog').data ('callback')); 
                    return false;
                },
                text: that.translations['Submit'],
                "class": "highlight"
            },
            {
                text: that.translations['Cancel'],
                click: function() {
                    $(this).dialog("close");
                }
            }
        ]
    });
};

/**
 * Forces a UI refresh 
 */
WorkflowManager.prototype._afterSaveStageDetails = function () {
    $("#workflowSelector").change();
};
        
WorkflowManager.prototype._init = function () {
    var that = this;

    this._setUpStageDetailsDialog ();
    this._setUpCommentDialog ();
};

return WorkflowManager;

}) ();

