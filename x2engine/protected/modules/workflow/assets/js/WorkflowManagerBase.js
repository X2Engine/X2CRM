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

x2.WorkflowManagerBase = (function () {

function WorkflowManagerBase (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        translations: [],
        getStageDetailsUrl: '',
        startStageUrl: '',
        completeStageUrl: '',
        revertStageUrl: ''
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    $(function () {
        $.fn.extend({loading:function(){
            $(this).html("<img src=\""+yii.themeBaseUrl+"/images/loading.gif\" class=\"loading\">");
        }});
    });
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

WorkflowManagerBase.prototype._afterSaveStageDetails = function (response, modelId, modelName) {};
WorkflowManagerBase.prototype._beforeSaveStageDetails = function (
    form, modelId, modelName, stageNumber) {

    return true;
};
        
WorkflowManagerBase.prototype.saveWorkflowStageDetails = function (
    modelId, modelName, stageNumber) {

    if (!this._beforeSaveStageDetails (
        $('#workflowDetailsForm'), modelId, modelName, stageNumber)) {

        return false;
    }

    var that = this;
    $.ajax({
        url: $("#workflowDetailsForm").attr("action"),
        type: "POST",
        data: $("#workflowDetailsForm").serialize(),
        beforeSend: function() { $("#workflowStageDetails").loading(); },
        success: function(response) {
            $("#workflowStageDetails").dialog("close");
            x2.Notifs.updateHistory();
            that._afterSaveStageDetails (response, modelId, modelName, stageNumber);
        }
    });

    return true;
};

WorkflowManagerBase.prototype._getStageDetailsTitle = function (stageNumber) {
    var that = this;
    var dialogTitle = that.translations['Stage {n}'].replace(
        "{n}",stageNumber);

    var stageLabels = $("#workflow-diagram .workflow-funnel-stage b");
    if(stageLabels.length >= stageNumber)
        dialogTitle += ": "+$(stageLabels[stageNumber-1]).html();

    return dialogTitle;
};

WorkflowManagerBase.prototype.workflowStageDetails = function (
    workflowId,stageNumber,modelName,modelId) {

    var modelName = typeof modelName === 'undefined' ? this.modelName : modelName; 
    var modelId = typeof modelId === 'undefined' ? this.modelId : modelId; 


    var that = this;

    var dialogTitle = this._getStageDetailsTitle (stageNumber, modelName, modelId);
    var dialogBox = $("#workflowStageDetails");

    dialogBox.dialog("option","title",dialogTitle);
    
    dialogBox.removeClass("editMode");

    $("#workflowDetails_createDate, #workflowDetails_startDate").datepicker("destroy");
    dialogBox.parent().find(".ui-dialog-buttonpane button:nth-child(1)").hide(); // save
    dialogBox.parent().find(".ui-dialog-buttonpane button:nth-child(2)").hide(); // edit
    dialogBox.parent().find(".ui-dialog-buttonpane button:nth-child(3)").hide(); // cancel
    dialogBox.data ('modelId', modelId);
    dialogBox.data ('modelName', modelName);
    dialogBox.data ('stageNumber', stageNumber);
    
    dialogBox.dialog("open");
    
    dialogBox.loading();
    
    $.ajax({
        url: that.getStageDetailsUrl,
        type: "GET",
        data: "workflowId="+workflowId+"&stage="+stageNumber+"&modelId=" +
            modelId + '&type=' + modelName,
        success: function(response) {
            if(response=="") return;
            $("#workflowStageDetails").html(response);
            
            // remove the edit button if theres no form
            if($("#workflowStageDetails #workflowDetailsForm").length)    
                dialogBox.parent().find(".ui-dialog-buttonpane button:nth-child(2)").show();
            else
                dialogBox.parent().find(".ui-dialog-buttonpane button:nth-child(2)").hide();
            
        }
    });
};

/*
Private instance methods
*/


WorkflowManagerBase.prototype._setUpStageDetailsDialog = function () {
    var that = this;
    $("#workflowStageDetails").dialog({
        autoOpen:false,
        closeOnEscape:true,
        resizable: false,
        modal: false,
        show: "fade",
        hide: "fade",
        width:400,
        buttons:[
            {
                text:that.translations['Save'], 
                click: function() { 
                    if (!that.saveWorkflowStageDetails(
                        $(this).data ('modelId'), $(this).data ('modelName'),
                        $(this).data ('stageNumber'))) {

                        $(this).dialog ('close');
                    }
                },
            },
            {
                text: that.translations['Edit'], 
                click: function() {
                    $(this).addClass("editMode");
                    
                    // save
                    $(this).parent().find(".ui-dialog-buttonpane button:nth-child(1)").show();    
                    // edit
                    $(this).parent().find(".ui-dialog-buttonpane button:nth-child(2)").hide();    
                    // cancel
                    $(this).parent().find(".ui-dialog-buttonpane button:nth-child(3)").show();    
                }, 
            },
            {
                text: that.translations['Cancel'],
                click: function() {
                    $(this).removeClass("editMode");

                    // save
                    $(this).parent().find(".ui-dialog-buttonpane button:nth-child(1)").hide();     
                    // edit
                    $(this).parent().find(".ui-dialog-buttonpane button:nth-child(2)").show();    
                    // cancel
                    $(this).parent().find(".ui-dialog-buttonpane button:nth-child(3)").hide();     
                },
            },
            {
                text: that.translations['Close'], 
                click: function() { 
                    $(this).dialog("close"); 
                }
            }
        ]
    });
};

return WorkflowManagerBase;

}) ();

