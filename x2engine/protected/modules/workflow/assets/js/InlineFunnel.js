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

x2.InlineFunnel = (function () {

var Point = x2.geometry.Point;

/**
 * InlineFunnel used on the workflow funnel view page
 */
function InlineFunnel (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;

    var defaultArgs = {
        completeButtonUrl: null,
        revertButtonUrl: null,
        stageNames: null, // array containing names of each stage
        /* array of bools, 1 for each stage, true if current user has permission to complete the
           stage, false otherwise */
        stagePermissions: [], 

        /* array of bools, 1 for each stage, true if the stage requires a comment, false 
           otherwise */
        stagesWhichRequireComments: [],

        /* array of bools, 1 for each stage, true if the stage can be uncompleted, false 
           otherwise */
        uncompletionPermissions: []
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.BaseFunnel.call (this, argsDict);

    this._funnelW1 = 160; // width of top of funnel
    this._funnelW2 = 100; // width of bottom of funnel
    this._stageHeight = 30; // temporary. replace when stage heights are depend on status
    this._stageNameContainers = []; // holds stage name container elements


    this._init ();
}

InlineFunnel.prototype = auxlib.create (x2.BaseFunnel.prototype);


/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/

/*
Private instance methods
*/

/**
 * Adds start, complete, revert, and details buttons for each stage 
 */
InlineFunnel.prototype._addInteractionButtons = function () {
    var that = this;

    /*
    Wraps the workflow manager method in a closure, so that it can be passed the stage number.
    Also passes an ajax callback which refreshes the inline workflow UI.
    */
    function wrapWorkflowMethod (stage, method) {
        return function () {
            method.call (
                x2.workflowManager, 
                that.workflowStatus['id'], stage, function (workflowStatus, flashes) { 
                    that.refresh (workflowStatus, flashes); });
            return false;
        };
    }

    for (var i = 0; i < this.stageCount; i++) {
        var previousCheck = x2.workflowManager.checkStageRequirement (i + 1, this.workflowStatus);
        var editPermission = this.stagePermissions[i];

        // contains all interaction buttons (start, complete, revert, details)
        var statusContainer = $('<div>', { 
            'class': 'interaction-buttons',
            css: {
                position: 'absolute',
                right: 0,
                top: this._stageCentroids[i].y - 10,
            }
        });

        if (this.workflowStatus.stages[i+1]['createDate'] !== null) { // if started

            var revertButtonImage = auxlib.fa('fa-undo fa-lg', {
                title: this.translations['Revert Stage']
            });
            var revertButton = $('<a>', { href: '#' });

            revertButton.bind (
                'click', wrapWorkflowMethod (i + 1, x2.workflowManager.revertWorkflowStage));

            if (this.workflowStatus['stages'][i+1]['complete']) { // completed
                if (editPermission && this.uncompletionPermissions[i]) {
                    revertButton.append (revertButtonImage);
                    statusContainer.append (revertButton);
                }
            } else { // started but not completed

                var completeButtonImage = auxlib.fa('fa-check-circle fa-lg', {
                    title: this.translations['Complete Stage']
                });

                if (previousCheck && editPermission) { // can complete
                    var completeButton = $('<a>', { href: '#' });
                    if (parseInt (this.stagesWhichRequireComments[i], 10)) {
                        completeButton.click ( 
                            wrapWorkflowMethod (i + 1, x2.workflowManager.workflowCommentDialog));
                    } else {
                        completeButton.click (
                            wrapWorkflowMethod (i + 1, x2.workflowManager.completeWorkflowStage));
                    }
                    completeButton.append (completeButtonImage);
                    statusContainer.append (completeButton);
                } else if (!previousCheck && !editPermission) { 
                    var completeButton = $('<span>', {
                        'class': 'workflow-hint',
                        style: 'color: gray;',
                        title: this.translations['noCompletePermissions']
                    });
                    completeButton.append (completeButtonImage);
                    statusContainer.append (completeButton);
                }

                if (editPermission) { // can revert
                    revertButton.append (revertButtonImage);
                    statusContainer.append (revertButton);
                } else { // can't revert (no permission)
                    revertButton = $('<span>', {
                        'class': 'workflow-hint',
                        style: 'color: gray;',
                        'title': this.translations['noRevertPermission']
                    })
                    revertButton.append (revertButtonImage);
                    statusContainer.append (revertButton);
                }
            }
                                                                 
            // add details button
            statusContainer.append ($('<a>', {
                text: '[' + this.translations['Details'] + ']'
            }).click ((function () { 
                var stageNumber = i + 1; 
                return function () {
                    x2.workflowManager.workflowStageDetails (
                        that.workflowStatus['id'], stageNumber);
                };
            }) ()));

        } else { // uncomplete stage, display start button
            if (editPermission && previousCheck) {
                var startButton = $('<a>', { 
                    href: '#',
                    text: '[' + this.translations['Start'] + ']'
                });
                startButton.click (
                    wrapWorkflowMethod (i + 1, x2.workflowManager.startWorkflowStage));
                statusContainer.append (startButton);
            }
        }
        $(this.containerSelector).append (statusContainer);
    }
}

/**
 * Adds status strings for each started/completed stage to the right of the funnel. This is used
 * in the case that the browser does not support canvas. When the excanvas polyfill is used, 
 * the stage names will not display when rendered unless they are added after the funnel has been 
 * rendered.
 */
InlineFunnel.prototype._addStageNamesAgain = function () {
    var that = this;
    for (var i = 0; i < this.stageCount; i++) {
        $(this.containerSelector).append (this._stageNameContainers[i]);
    }
};

/**
 * Adds a stage name to the workflow funnel for the specified stage and returns the height of
 * the container element holding the name.
 * @param number stageNumber 
 * @param number top distance from the top of the trapezoid
 * @param number w1 top width of containing trapezoid
 * @return number the height of the new stage container
 */
InlineFunnel.prototype._addStageNameContainer = function (stageNumber, top, w1) {
    var that = this;
    var stageNameContainer = $('<span>', {
        'class': 'workflow-stage-name',
        html: '<b>' + this.stageNames[stageNumber] + '</b>',
        css: {
            width: w1,
            'text-align': 'center',
            position: 'absolute',
            left: (this._funnelW1 / 2) - (w1 / 2),
            top: top + 4,
        }
    });
    this._stageNameContainers.push (stageNameContainer);
    $(this.containerSelector).append (stageNameContainer);
    return stageNameContainer.height () + 8;
};

/**
 * Overrides parent method so that stage heights are determined by the number of lines needed to
 * accomodate the stage name container. 
 */
InlineFunnel.prototype._getBaseFunnelCoordinates = function () {
    var that = this;

    // the four corners of each stage (<upper left>, <bottom left>, <bottom right>, <upper right)
    this._stageCoordinates = []; 

    // get coordinates of corners of each stage
    var prevW2 = this._funnelW1;
    var prevBottomLeft = this._upperLeftCoord;

    // stage heights are calculated as funnel coordinates for each stage are generated
    this._stageHeights = [];

    // there's a fixed delta, in the case that stage names wrap around, the funnel will not be
    // trapezoidal
    var delta = ((this._funnelW1 / 2) - (this._funnelW2 / 2)) / this.stageCount;
    for (var i = 0; i < this.stageCount; i++) {
        var w1 = prevW2;

        // create stage name container and calculate the height of the stage
        this._stageHeights.push (
            this._addStageNameContainer (i, auxlib.sum (this._stageHeights) || 0, w1));
        var angleA = Math.atan (delta / this._stageHeights[i]);

        var w2 = w1 - (2 * delta); 
        this._stageCoordinates.push (this._buildTrapezoid (
            angleA, delta, this._stageHeights[i], w1, w2, prevBottomLeft.x, prevBottomLeft.y
        ));
        prevW2 = w2;

        prevBottomLeft = this._stageCoordinates[i][1];
    }
    this._funnelHeight = auxlib.sum (this._stageHeights);
};

/**
 * Adds status strings for each started/completed stage to the right of the funnel 
 */
InlineFunnel.prototype._addStatusStrings = function () {
    var that = this;
    for (var i = 0; i < this.stageCount; i++) {
        // add status string for started and completed stages
        if (this.workflowStatus.stages[i+1]['createDate'] !== null) {
            if (this.workflowStatus['stages'][i+1]['complete']) { // completed
                var dateContainer = $('<span>', {
                    'class': 'workflow-status-string',
                    text: this.translations['Completed'] + ' ' +
                        $.datepicker.formatDate (
                            'yy-mm-dd',
                            new Date (
                                this.workflowStatus['stages'][i+1]['completeDate'] * 1000)),
                    css: {
                        position: 'absolute',
                        left: (this._funnelW1) + 15,
                        top: this._stageCentroids[i].y - 10,
                    }
                });
            } else { // started
                var dateContainer = $('<span>', {
                    'class': 'workflow-status-string',
                    html: '<b>' + this.translations['Started'] + ' ' +
                        $.datepicker.formatDate (
                            'yy-mm-dd',
                            new Date (
                                this.workflowStatus['stages'][i+1]['createDate'] * 1000)) + '</b>',
                    css: {
                        position: 'absolute',
                        left: (this._funnelW1) + 15,
                        top: this._stageCentroids[i].y - 10,
                    }
                });
            }
            $(this.containerSelector).append (dateContainer);
        }
    }
};

/**
 * Remove interaction buttons and status strings and recreate them using the given workflow status 
 */
InlineFunnel.prototype.refresh = function (workflowStatus, flashes) {
    this.workflowStatus = workflowStatus;
    $('.workflow-status-string').remove ();
    $('.interaction-buttons').remove ();
    this._addStatusStrings ();
    this._addInteractionButtons ();
};

InlineFunnel.prototype._init = function () {
    var that = this;

    x2.BaseFunnel.prototype._init.call (this);
    this._addStatusStrings ();
    this._addInteractionButtons ();

    if (typeof G_vmlCanvasManager !== 'undefined') {
        this._addStageNamesAgain ();
    }

};

return InlineFunnel;

}) ();

