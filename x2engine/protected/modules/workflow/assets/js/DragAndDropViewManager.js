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

x2.DragAndDropViewManager = (function () {

/**
 * @class
 * @name DragAndDropViewManager
 */
function DragAndDropViewManager (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;

    var defaultArgs = {
        DEBUG: false && x2.DEBUG,
        workflowId: null,
        currency: null,
        stageCount: null,
        connectWithClass: '', // class shared by connected columns
        memberListContainerSelectors: [], // a selector for each member list 
        moveFromStageAToStageBUrl: '',
        ajaxGetModelAutocompleteUrl: '',
        memberContainerSelector: '', // selector for individual list items

        /* array of bools, 1 for each stage, true if current user has permission to complete the
           stage, false otherwise */
        stagePermissions: [], 

        /* array of bools, 1 for each stage, true if the stage requires a comment, false 
           otherwise */
        stagesWhichRequireComments: [],
        stageNames: [],
        stageListItemColors: []
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.WorkflowManagerBase.call (this, argsDict);

    this.stagesWhichRequireComments = auxlib.map (function (a) {
        return parseInt (a, 10);
    }, this.stagesWhichRequireComments);

    // get stage number indexed by stage name
    this._stageNumbers = auxlib.map (
        function (a) { return a + 1; }, auxlib.flip (this.stageNames)); 

    this._containersSelector = this.memberListContainerSelectors.join (',');

    this._lastTouchedListItem = null;
    this._lastTouchedListItemPrev = null;
    this._lastTouchedListItemContainer = null;

    this._filtersButtonSelector = '#workflow-filters';
    this._stageChangesLocked = false;

    this._qtipManagers = {};

    this._init ();
}

DragAndDropViewManager.prototype = auxlib.create (x2.WorkflowManagerBase.prototype);


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
 * Should be called during afterAjaxUpdate event.
 */
DragAndDropViewManager.prototype.refresh = function () {
    var that = this;

    // pagination can introduce duplicate entries since records which were once on the page n
    // can be moved to page n + 1 as the result of dragging and dropping records. When page n + 1
    // is fetched, records once on page n will get fetched a second time. calling this method
    // strips those duplicates out. 
    this._removeDuplicateEntries ();

    /*$(this._containersSelector).each (function () {
        if ($(this).closest ('.ui-sortable').length)
            $(this).sortable ('destroy');
    });*/
    this._init ();
};

/*
Private instance methods
*/

/**
 * Locks all stage changes. This prevents, for example, dragging and dropping records.
 */
DragAndDropViewManager.prototype._lockStageChanges = function () {
    this._stageChangesLocked = true;
    $(this._containersSelector).workflowDragAndDropSortable ('disable');
};

/**
 * Unlocks stage changes.
 */
DragAndDropViewManager.prototype._unlockStageChanges = function () {
    this._stageChangesLocked = false;
    $(this._containersSelector).workflowDragAndDropSortable ('enable');
};

/**
 * Moves last dragged record back to its last position 
 */
DragAndDropViewManager.prototype._revertLastChange = function () {
    var that = this;
    this._unlockStageListItem (this._lastTouchedListItem);
    this._lastTouchedListItem.detach ();
    if (this._lastTouchedListItemPrev.length) {
        this._lastTouchedListItemPrev.after (this._lastTouchedListItem);
    } else {
        this._lastTouchedListItemContainer.prepend (this._lastTouchedListItem);
    }
};

/**
 * Returns selector for locked list items 
 * @return string
 */
DragAndDropViewManager.prototype._getLockedListItemSelector = (function () {
    var that = this;
    var selector = 'record-stage-change-pending';
    return function () {
        return selector;
    };
}) ();

/**
 * @param object list item
 * @return object the staging area is the container which holds clones of records with pending
 *  stage changes. Each list has a staging area. 
 */
DragAndDropViewManager.prototype._getStagingArea = function (elem) {
    var that = this;
    return $(elem).closest ('.stage-members').find ('.stage-member-staging-area');
};

/**
 * Locks a list item, preventing it from being dragged 
 */
DragAndDropViewManager.prototype._lockStageListItem = function (elem) {
    var that = this;
    $(elem).addClass (this._getLockedListItemSelector ());
    this._getStagingArea(elem).append ($(elem).clone ());
};

/**
 * Unlocks a list item, preventing it from being dragged 
 */
DragAndDropViewManager.prototype._unlockStageListItem = function (elem) {
    var that = this;
    $(this._getStagingAreaClone (elem)).removeClass (this._getLockedListItemSelector ());
    $(elem).removeClass (this._getLockedListItemSelector ());
};

/**
 * @return bool true if list item is locked, false otherwise 
 */
DragAndDropViewManager.prototype._listItemIsLocked = function (elem) {
    var that = this;
    return $(elem).hasClass (this._getLockedListItemSelector ());
};

/**
 * Saves info about last touched list item so that the change can later be reverted if, for
 * example, permissions checks fail or required comments aren't given.
 * @param object jQuery object corresponding to last touched list item
 */
DragAndDropViewManager.prototype._saveLastTouched = function (elem) {
    var that = this;
    this._lastTouchedListItem = $(elem);
    this._lastTouchedListItemContainer = $(elem).parents (this._containersSelector);
    this._lastTouchedListItemPrev = $(elem).prev ();
}

/**
 * Disables/enables all record name qtips and, if disable is true, hides all qtips too.
 * @param bool disable If true, disable the qtip, enable it otherwise.
 */
DragAndDropViewManager.prototype._disableQTips = function (disable) {
    var disable = typeof disable === 'undefined' ? true : disable; 

    if (disable)
        $('.stage-member-name a').qtip ('toggle', false);
    $('.stage-member-name a').qtip ('disable', disable);
};

DragAndDropViewManager.prototype._emptyColumnFix = function () {
    var that = this;
    // show empty column placeholder in all columns with no visible elements.
    // accounts for bug in jQuery UI which causes sortable "over" event to trigger
    // inconsistently
    var emptyStages$;
    if ((emptyStages$ = $(that.connectWithClass).filter (function () {
        return !$(this).find ('.empty').is (':visible') &&
            !$(this).find (that.memberContainerSelector).not ('.ui-sortable-helper').length;
    })) && emptyStages$.length) {
        emptyStages$.each (function () {
            var stageNumber = that._getWorkflowStageNumber ($(this));
            that._hideShowNoResultsDummyItem (stageNumber); 
        });
    } else {
    }
};

/**
 * Sets up drag and drop feature. Allows records to be dragged from one stage to the next.
 */
DragAndDropViewManager.prototype._setUpDragAndDrop = function () {
    var that = this;
    var startedSortUpdate = false;
    var startStage; // boolean to prevent multiple updates due to connected lists
    var prevStage = Infinity;
    $(this._containersSelector).workflowDragAndDropSortable ({
        items: this.memberContainerSelector,
        connectWith: this.connectWithClass,
        tolerance: 'pointer',
        dropOnEmpty: true,
        change: function (event, ui) {
            //console.log ('change');
            var stageMember = $(ui.item);
            var memberInfo = that._getStageMemberInfo (stageMember);
            //console.log ('memberInfo = ');
            //console.log (memberInfo);

            if (startStage === memberInfo['stageNumber'])
                return true;
        },
        sort: function (event, ui) {
            // prevent user from dragging locked list items
            if (that._listItemIsLocked ($(ui.item))) {
                return false;
            }

            that._emptyColumnFix ();
            //return false;
        },
        out: function (event, ui) {
            //console.log ('out');
            //console.log (ui.item);
            //console.log (stageNumber);
            //return false;
        },
        over: function (event, ui) {
            // hide/show the no results list item as the user drags from list to list

            var stageNumber = that._getStageMemberInfo ($(ui.placeholder))['stageNumber'];
            if (stageNumber !== prevStage) {
                that._hideShowNoResultsDummyItem (stageNumber);
                that._hideShowNoResultsDummyItem (prevStage);
                prevStage = stageNumber;
            }
        },
        stop: function (event, ui) {
            that.DEBUG && console.log ('stop');
            startedSortUpdate = false;
            $('#stage-member-lists-container').scroll ();

            var stageMember = $(ui.item);

            // re-enable qtips
            that._disableQTips (false);
        },
        start: function (event, ui) {
            // save info about dragged record
            that.DEBUG && console.log ('start');
            var stageMember = $(ui.item);
            var memberInfo = that._getStageMemberInfo (stageMember);
            that._saveLastTouched ($(ui.item));
            startStage = memberInfo['stageNumber'];
            prevStage = startStage;

            // prevent qtips from popping up while dragging records and hide them
            that._disableQTips ();
        },
        update: function (event, ui) {
            // enact stage change, lock record

            if (startedSortUpdate) return;
            startedSortUpdate = true;
            that.DEBUG && console.log ('update');
            
            // fixes quirk in ie8 
            $(ui.item).removeClass ('stage-highlight'); 

            // lock record so that it can't be dragged until server response
            var stageMember = $(ui.item);
            var memberInfo = that._getStageMemberInfo (stageMember);

            if (startStage === memberInfo['stageNumber']) return;

            that._lockStageListItem ($(ui.item));
            $(ui.item).find ('.stage-member-button').hide (); 
            return that._moveFromStageAToStageB (
                startStage,
                memberInfo['stageNumber'], 
                memberInfo['modelId'],
                memberInfo['type'],
                $(ui.item)
            );
        }
    });
};

/**
 * @param int stageA starting stage number
 * @param int stageB ending stage number or null (optional)
 * @return bool true if user has permission for all stages between a and b (inclusive). If stage b
 *  is not passed as a parameter or is null, permissions will be checked only for stage a.
 */
DragAndDropViewManager.prototype._checkPermission = function (stageA, stageB) {
    var that = this;
    var hasPermission = true;

    if (typeof stageB === 'undefined' || stageB === null) {
        return this.stagePermissions[stageA - 1];
    }

    var stageRange = [stageA, stageB].sort ();

    hasPermission = auxlib.reduce (function (a, b) { return a & b; }, 
        this.stagePermissions.slice (stageRange[0] - 1, stageRange[1]));

    that.DEBUG && console.log ('hasPermission = ');
    that.DEBUG && console.log (hasPermission);

    return hasPermission;
};

/**
 * @return string jQuery selector for list items associated with records of given id and type.
 */
DragAndDropViewManager.prototype._getStageMemberListItemSelector = function (modelId, modelType) {
    var that = this;
    return '.stage-member-id-' + modelId + '.stage-member-type-' + modelType;
};

/**
 * @return object jQuery object corresponding to list item which is associated with record of
 *  given id and type at a specified stage. 
 */
DragAndDropViewManager.prototype._getStageMemberListItem = function (
    stageNum, modelId, modelType) {

    var that = this;
    var recordListItemSelector = this._getStageMemberListItemSelector (modelId, modelType);
    return $(this.memberListContainerSelectors[stageNum - 1]).find (recordListItemSelector);
};

/**
 * Updates deal count and projected deal values of the current list and previous list (after a
 * stage change).
 * @param object elem current list item 
 * @param number prevListNum (optional)
 * @param number currListNum (optional) either this or prevListNum must be numeric
 */
DragAndDropViewManager.prototype._updateListHeader = function (elem, prevListNum, currListNum) {
    var that = this;
    var currListNum = typeof currListNum === 'undefined' ? null : currListNum; 
    var prevListNum = typeof prevListNum === 'undefined' ? null : prevListNum; 

    if (!currListNum && !prevListNum) {
        auxlib.assert (!currListNum && !prevListNum, '_updateListHeader precondition failed');
    }

    if (prevListNum)
        var prevList = 
            $(this.memberListContainerSelectors[prevListNum - 1]).parents ('.list-view');
    if (currListNum)
        var currList = $(this.memberListContainerSelectors[currListNum - 1]).
            parents ('.list-view');

    that.DEBUG && console.log ('_updateListHeader');
    that.DEBUG && console.log ('prevList = ');
    that.DEBUG && console.log (prevList);

    // update deal counts
    if (prevListNum) {
        var stageDealsNum = parseInt ($(prevList).find ('.stage-deals-num').html (), 10) - 1;
        if (stageDealsNum === 1) {
            $(prevList).find ('.stage-deals-num').next ().html (this.translations['deal']);
        }
        $(prevList).find ('.stage-deals-num').html (stageDealsNum);
    }
    if (currListNum) {
        var stageDealsNum = parseInt ($(currList).find ('.stage-deals-num').html (), 10) + 1;

        if (stageDealsNum === 2) {
            $(currList).find ('.stage-deals-num').next ().html (this.translations['deals']);
        }
        $(currList).find ('.stage-deals-num').html (stageDealsNum);
    }

    // update total projected values
    var elemDealVal = auxlib.currencyToNumber (
        $(elem).find ('.stage-member-value').html (), this.currency);
    that.DEBUG && console.log ('elemDealVal = ');
    that.DEBUG && console.log (elemDealVal);

    if (prevListNum) {
        var totalDealValue = auxlib.currencyToNumber (
            $(prevList).find ('.total-projected-stage-value').html (), this.currency);
        $(prevList).find ('.total-projected-stage-value').html (
            auxlib.numberToCurrency (totalDealValue - elemDealVal, this.currency));
    }

    if (currListNum) {
        var totalDealValue = auxlib.currencyToNumber (
            $(currList).find ('.total-projected-stage-value').html (), this.currency);
        $(currList).find ('.total-projected-stage-value').html (
            auxlib.numberToCurrency (totalDealValue + elemDealVal, this.currency));
    }
};

/**
 * Removes duplicate list items in lists introduced as the result of pagination. 
 */
DragAndDropViewManager.prototype._removeDuplicateEntries = function () {
    var that = this;

    // Loop through stage lists, removing duplicate records. 
    // Each iteration runs in O(n) time with respect to the number of records in the current stage
    // list.
    for (var i = 1; i <= this.stageCount; ++i) {
        //that.DEBUG && console.log ('stage = ');
        //that.DEBUG && console.log (stage);
        var records = {};

        // get list item counts
        var $stagelist = $(this.memberListContainerSelectors[i - 1]);
        $stagelist.find (this.memberContainerSelector).each (function () {
            //console.log ($(this).attr ('class'));
            if (records[auxlib.classToSelector ($(this).attr ('class'))])
                records[auxlib.classToSelector ($(this).attr ('class'))] += 1;
            else 
                records[auxlib.classToSelector ($(this).attr ('class'))] = 1;
        });

        that.DEBUG && console.log (records);

        // remove duplicates. assumes that max number of duplicates per record is 1
        for (var selector in records) {
            if (records[selector] > 1) {
                that.DEBUG && console.log ('removing ' + selector);
                $stagelist.find (selector).last ().remove ();
                records[selector]--;
            }
        }
    }
};

/**
 * Updates the stage lists in accordance with the status of a workflow for a single record.
 * @param object workflowStatus This is the JSON encoded then decoded return value of the Workflow
 *  model's method getWorkflowStatus (). It contains information about the workflow and each of its
 *  stages.
 * @param int modelId
 * @param string modelType
 * @param string elemInfo (optional) If set, this will be used to generate the record prototype. 
 *  This should be an object containing the model id and model type 
 *  ({modelId: <id>, modelType: <type>, recordName: <name>}).
 */
DragAndDropViewManager.prototype._updateStageLists = function (
    workflowStatus, modelId, modelType, elemInfo) {

    var elemInfo = typeof elemInfo === 'undefined' ? null : elemInfo; 

    var that = this;
    var rebindEvents = false;
    var recordListItemSelector = this._getStageMemberListItemSelector (modelId, modelType);
    //that.DEBUG && console.log ('recordListItemSelector = ');
    //that.DEBUG && console.log (recordListItemSelector);

    // this is used to add records to lists of stages that have been completed
    var $recordPrototype = $('body').find ('.list-view ' + recordListItemSelector).eq (0);
    if ($recordPrototype.length) {
        // found a matching list item, use this as a prototype
        $recordPrototype = $recordPrototype.detach ();
    } else if (elemInfo) {
        // create a list item prototype from the given record information
        $recordPrototype = $('#stage-member-prototype').children ().first ().clone ();
        $recordPrototype.attr ('class', 'stage-member-container stage-member-id-' + 
            elemInfo['modelId'] + ' ' + 'stage-member-type-' + elemInfo['modelType']);
        $recordPrototype.find ('.stage-member-name').html (
            $('<a>', {
                href: yii.scriptUrl + '/' + modelType + '/' + modelId,
                text: elemInfo['recordName'],
                'data-qtip-title': elemInfo['recordName']
            })
        );
        $recordPrototype.find ('.stage-member-value').html (elemInfo['dealValue']);

        // replace icon
        $recordPrototype.find ('img').attr (
            'src', 
            $recordPrototype.find ('img').attr ('src').replace (
                /_[^_]+\.png$/, '_' + modelType + '.png'));

        rebindEvents = true;
    } else {
        $recordPrototype = null;
    }

    var $emptyPrototype = $('<span>', {
        'class': 'empty',
        'text': this.translations['No results found.']
    });

    that.DEBUG && console.log ('elemInfo = ');
    that.DEBUG && console.log (elemInfo);

    that.DEBUG && console.log ('$recordPrototype = ');
    that.DEBUG && console.log ($recordPrototype);

    var stageNum = auxlib.keys (workflowStatus.stages).length;
    //that.DEBUG && console.log ('stageNum = ');
    //that.DEBUG && console.log (stageNum);

    // loop through stages, updating each stage's corresponding list
    for (var i = 1; i <= stageNum; ++i) {
        var stage = workflowStatus.stages[i];
        //that.DEBUG && console.log ('stage = ');
        //that.DEBUG && console.log (stage);

        var $stagelist = $(this.memberListContainerSelectors[i - 1]);
        //that.DEBUG && console.log ('$stagelist  = ');
        //that.DEBUG && console.log ($stagelist);

        var $recordElem = $stagelist.find (recordListItemSelector);
        //that.DEBUG && console.log ('$recordElem  = ');
        //that.DEBUG && console.log ($recordElem);

        if ($recordElem.length && (stage['complete'] || !stage['createDate'])) {
            /* List contains a record but the record has either completed that stage or hasn't 
            started it yet. Remove the record from the list */

            $recordElem.remove ();
        } else if ($recordPrototype !== null && 
            !$recordElem.length && (!stage['complete'] && stage['createDate'])) {
            /* List does not contain a record even though it is in the start state. Add the
            prototype record */

            if ($stagelist.find ('.record-stage-change-pending').length) {
                $stagelist.find ('.record-stage-change-pending').last ().after ($recordPrototype);
            } else {
                $stagelist.find (recordListItemSelector).remove (); // remove duplicates
                $stagelist.prepend ($recordPrototype);
            }
            $stagelist.find ('.empty').remove ();
        }

        // check if empty record needs to be added
        if ($stagelist.children ().length === 0) {
            $stagelist.append ($emptyPrototype.clone ());
        }
    }
    if (rebindEvents) {
        that._qtipManagers[modelType].refresh ();
        this._setUpStageMemberButtons ();
    }
};

/*
 * @param int stage The stage to start
 * @param int modelId id of the model associated with the record 
 * @param string type model class 
 */
DragAndDropViewManager.prototype._startStage = function (
    stage, modelId, type, recordName, element) {

    if (!this._checkPermission (stage)) return false;
    var that = this;
    that.DEBUG && console.log ('_startStage');
    $.ajax ({
        url: this.startStageUrl,
        type: 'get',
        dataType: 'json',
        data: {
            workflowId: this.workflowId,
            stageNumber: stage,
            modelId: modelId,
            recordName: recordName,
            type: type
        },
        success: function (data) {
            that.DEBUG && console.log ('data = ');
            that.DEBUG && console.log (data);
            if (data['flashes']['error'].length !== 0) {
                data['flashes']['error'][0] = 
                    that.translations['addADealError'] + data['flashes']['error'][0];
                x2.flashes.displayFlashes (data['flashes']);
            } else {
                that._updateStageLists (data['workflowStatus'], modelId, type, {
                    modelId: modelId, modelType: type, recordName: recordName,
                    dealValue: data['dealValue']
                });
                that.DEBUG && console.log ('_startStage' + stage);
                that.DEBUG && console.log ('element = ');
                that.DEBUG && console.log (element);

                that._updateListHeader ($(element), null, stage);
                that._successFailureAnimation ($(element));
            } 
        }
    });
};


/*
 * @param int stage The stage to complete
 * @param int modelId id of model associated with record being dragged
 * @param string type model class 
 */
DragAndDropViewManager.prototype._completeStage = function (stage, modelId, type, element) {
    if (!this._checkPermission (stage)) return false;
    var that = this;
    that.DEBUG && console.log ('_completeStage');
    $.ajax ({
        url: this.completeStageUrl,
        type: 'get',
        dataType: 'json',
        data: {
            workflowId: this.workflowId,
            stageNumber: stage,
            modelId: modelId,
            type: type
        },
        success: function (data) {
            that.DEBUG && console.log ('data = ');
            that.DEBUG && console.log (data);
            x2.flashes.displayFlashes (data['flashes']);
            if (data['flashes']['error'].length === 0) {
                that._updateStageLists (data['workflowStatus'], modelId, type);
                that.DEBUG && console.log ('_completeStage' + stage);
                that._updateListHeader ($(element), stage);
                if (stage !== that.stageNames.length)
                    that._successFailureAnimation ($(element));
            } else {
                that._removeStagingClone ($(that._lastTouchedListItem));
                that._successFailureAnimation ($(element), false);
            }
        }
    });
};

/*
 * @param int stage The stage to revert
 * @param int modelId id of model associated with record being dragged
 * @param string type model class 
 */
DragAndDropViewManager.prototype._revertStage = function (stage, modelId, type, element) {
    if (!this._checkPermission (stage)) return false;
    var that = this;
    $.ajax ({
        url: this.revertStageUrl,
        type: 'get',
        dataType: 'json',
        data: {
            workflowId: this.workflowId,
            stageNumber: stage,
            modelId: modelId,
            type: type
        },
        success: function (data) {
            that.DEBUG && console.log ('data = ');
            that.DEBUG && console.log (data);
            x2.flashes.displayFlashes (data['flashes']);
            if (data['flashes']['error'].length === 0) {
                that._updateStageLists (data['workflowStatus'], modelId, type);
                that._updateListHeader ($(element), stage);
                if (stage !== 1) 
                    that._successFailureAnimation ($(element));
            } else {
                that._removeStagingClone ($(that._lastTouchedListItem));
                that._successFailureAnimation ($(element), false);
            }
        }
    });
};

/**
 * Get any required comments before submitting stage change request
 * @param int stageA starting stage number
 * @param int stageB ending stage number
 * @param function callback function to call after comments are retrieved
 */
DragAndDropViewManager.prototype._getRequiredComments = function (stageA, stageB, callback) {
    var that = this;

    if (stageA > stageB || auxlib.sum (this.stagesWhichRequireComments.slice (
            stageA - 1, stageB - 1)) === 0) {
        // no comments required in this range or only reversions will take place, just submit 
        // request to change stages

        that.DEBUG && console.log ('no comments required');
        callback ();
        return;
    }

    // get names of stages in range which require comments
    var stageNames = $.grep (
        this.stageNames.slice (stageA - 1, stageB - 1), function (name) { 

        that.DEBUG && console.log ('name = ');
        that.DEBUG && console.log (name);

        return that.stagesWhichRequireComments[that._stageNumbers[name] - 1] === 1;
    });

    // create comments form
    var $commentsForm = $(
        '<div class="form"><form id="stage-comments-form">' +
            '<p>The following stages require comments: </p>' +
        '</form></div>' 
    );
    // add a text area for each stage which requires comments
    $(stageNames).each (function (i, name) {
        $commentsForm.find ('form').append ($(
            '<label for="comments[' + that._stageNumbers[name] + ']">' + 
                stageNames[i] + '</label>' +
            '<textarea name="comments[' + that._stageNumbers[name] + ']"></textarea>'
        ));
    });

    this._lockStageChanges ();
    $commentsForm.dialog ({
        title: that.translations['Comments Required'],
        width: 500,
        buttons: [
            {
                text: that.translations['Cancel'],
                click: function () {
                    $(this).dialog ('close');
                }
            },
            {
                text: that.translations['Submit'],
                click: function () {
                    var valid = true;

                    // validate comments form, comments are required
                    $('#stage-comments-form').find ('textarea').each (function () {
                        if ($(this).val () === '') {
                            valid = false;
                            $(this).addClass ('error');
                        }
                    });
                    if (valid) {
                        callback (auxlib.formToJSON ($commentsForm.find ('form')));
                        that._unlockStageChanges ();
                        $(this).dialog ('destroy');
                        $commentsForm.remove ();
                    }
                }
            }
        ],
        close: function () {
            that._unlockStageChanges ();
            $(this).dialog ('destroy');
            $commentsForm.remove ();
            that._removeStagingClone ($(that._lastTouchedListItem));
            that._revertLastChange ();
            that._successFailureAnimation ($(that._lastTouchedListItem), false);
        }
    });
};

/**
 * Each list item in the pending state has a clone in the staging area. This method returns that
 * clone.
 * @param object elem 
 * @return object
 */
DragAndDropViewManager.prototype._getStagingAreaClone = function (elem) {
    var that = this;
    return this._getStagingArea (elem).find (auxlib.classToSelector ($(elem).attr ('class')));
};

/**
 * Removes the list items staging area clone. 
 */
DragAndDropViewManager.prototype._removeStagingClone = function (elem) {
    this._getStagingAreaClone (elem).remove ();
};

/**
 * Animates the background of the specified list item
 * @param object elem jQuery object corresponding to a record list item 
 */
DragAndDropViewManager.prototype._successFailureAnimation = function (elem, success) {
    var that = this;
    var success = typeof success === 'undefined' ? true : success; 
    var clone = this._getStagingAreaClone (elem);
    that.DEBUG && console.log ('clone = ');
    that.DEBUG && console.log (clone);

    //var originalColor = 'white';//$(elem).css ('background-color');
    var originalColor = this.stageListItemColors[this._getWorkflowStageNumber ($(elem)) - 1];

    //var originalCloneColor = $(clone).css ('background-color');

    // switch background to green and animate back to original color
    if (success) {
        $([elem, clone]).each (function () { 
            $(this).css ({'background-color': 'rgba(211, 255, 211)'}); });
    } else {
        $([elem, clone]).each (function () { 
            $(elem).css ({'background-color': 'rgb(255, 152, 152)'}); });
    }

    function animate (elem, originalColor, isClone) {
        $(elem).animate ({
            backgroundColor: originalColor
        },
        {
            duration: 1000,
            complete: function () {
                that.DEBUG && console.log (this);

                // reset to original color
                $(elem).css ({'background-color': originalColor}); // fixes a FF quirk
                $(elem).css ({'background-color': ''});
                if (isClone) $(elem).remove ();
            }
        });
    }

    animate (clone, originalColor, true);
    animate (elem, originalColor, false);
};

/**
 * A permissions check failed, revert the change, clean up the staging area and display error 
 * feedback.
 */
DragAndDropViewManager.prototype._permissionsFailureResponse = function (element) {
    var that = this;
    that._removeStagingClone ($(that._lastTouchedListItem));
    that._revertLastChange ();
    that._unlockStageListItem ($(element));
    x2.flashes.displayFlashes ({'error': [this.translations.permissionsError]});
    that._successFailureAnimation ($(element), false);
};
  
/**
 * Moves a record from stage a to stage b, if the current user as permission to complete all stages
 * between a and b (inclusive)
 * @param int stageA starting stage number
 * @param int stageB ending stage number
 * @param int modelId id of model associated with record being dragged
 * @param string type model class 
 * @param object element If set, drag lock will be removed from it after server response
 */
DragAndDropViewManager.prototype._moveFromStageAToStageB = function (
    stageA, stageB, modelId, type, element) {

    var element = typeof element === 'undefined' ? null : element; 

    if (stageA === stageB) return false;

    var that = this;

    if (stageA === 1 && stageB === null) { // moved past last column
        this._revertStage (stageA, modelId, type, element);
        return;
    } else if (stageA === that.stageCount && stageB === null) { // moved past first column
        this._completeStage (stageA, modelId, type, element);
        return;
    }

    that.DEBUG && console.log ('stageA = ');
    that.DEBUG && console.log (stageA);
    that.DEBUG && console.log ('stageB = ');
    that.DEBUG && console.log (stageB);

    that.DEBUG && console.log ('_moveFromStageAToStageB');

    if (!this._checkPermission (stageA, stageB)) {
        this._permissionsFailureResponse (element);
        return false;
    }

    this._getRequiredComments (stageA, stageB, function (comments) {
        var comments = typeof comments === 'undefined' ? {} : comments; 
        $.ajax ({
            url: that.moveFromStageAToStageBUrl,
            type: 'post',
            dataType: 'json',
            data: $.extend ({
                workflowId: that.workflowId,
                stageA: stageA,
                stageB: stageB,
                modelId: modelId,
                type: type
            }, comments),
            success: function (data) {
                that.DEBUG && console.log ('data = ');
                that.DEBUG && console.log (data);
                x2.flashes.displayFlashes (data['flashes']);
                var error = false;
                if (data['flashes']['error'].length === 0) {
                    that._successFailureAnimation ($(element), true);
                    that._updateListHeader ($(element), stageA, stageB);
                } else {
                    that._removeStagingClone ($(that._lastTouchedListItem));
                    that._successFailureAnimation ($(element), false);
                }
                that._updateStageLists (data['workflowStatus'], modelId, type);
                that._unlockStageListItem ($(element));
            }
        });
    });
    return true;
};

/**
 * @param object elem A child of the stage member list item view
 * @return mixed The id of the record associated with the stage member list item or false if
 *  an id could not be retrieved. 
 */
DragAndDropViewManager.prototype._getStageMemberId = function (elem) {
    var that = this;
    var match = $(elem).closest (this.memberContainerSelector).attr ('class').
        match (/stage-member-id-([0-9]+)/);

    if (match) 
        return parseInt (match[1], 10);
    else 
        return false;
};

/**
 * @param object elem A child of the stage member list item view
 * @return mixed The type of the record associated with the stage member list item or false if
 *  a type could not be retrieved. 
 */
DragAndDropViewManager.prototype._getStageMemberType = function (elem) {
    var that = this;
    var match = $(elem).closest (this.memberContainerSelector).attr ('class').
        match (/stage-member-type-([a-zA-Z]+)/);

    if (match) 
        return match[1];
    else 
        return false;
};

/**
 * @param object elem A child of the list view containing records at a certain stage 
 * @return mixed The stage number corresponding to this list view, or false if the stage number
 *  could not be retrieved.
 */
DragAndDropViewManager.prototype._getWorkflowStageNumber = function (elem) {
    var that = this;
    var match = $(elem).closest ('.list-view').attr ('id').match (/workflow-stage-([0-9]+)/);

    if (match) 
        return parseInt (match[1], 10);
    else 
        return false;
};

/**
 * @param object elem Any element inside a stage list item view
 * @return object Information about the stage associated with the current list item
 */
DragAndDropViewManager.prototype._getStageMemberInfo = function (elem) {
    var that = this;
    return {
        'modelId': this._getStageMemberId (elem),
        'type': this._getStageMemberType (elem),
        'stageNumber': this._getWorkflowStageNumber (elem),
    };
};


/**
 * @param int stageNumber 
 * @return mixed null if there is no next stage, the next stage number otherwise
 */
DragAndDropViewManager.prototype._getNextStageNumber = function (stageNumber) {
    var that = this;
    var nextStage = stageNumber + 1;
    if (nextStage > this.stageCount) 
        return null;
    else 
        return nextStage;
};

/**
 * @param int stageNumber 
 * @return mixed null if there is no previous stage, the previous stage number otherwise
 */
DragAndDropViewManager.prototype._getPrevStageNumber = function (stageNumber) {
    var that = this;
    var prevStage = stageNumber - 1;
    if (prevStage < 1)
        return null;
    else 
        return prevStage;
};

/**
 * @param int stageNumber
 * @return mixed jQuery object corresponding to list associated with specified stage or null if
 *  stage number is invalid
 */
DragAndDropViewManager.prototype._getListOfStage = function (stageNumber) {
    var that = this;
    if (stageNumber > this.memberContainerSelector.length) return null
    return this.memberListContainerSelectors[stageNumber - 1];
};

/**
 * (un)highlights all list items matching the specified list item
 * @param object elem 
 */
DragAndDropViewManager.prototype._highlightUnHighlightSimilar = function (elem, highlight) {
    var that = this;
    var info = this._getStageMemberInfo (elem);
    if (highlight) {
        $(this._getStageMemberListItemSelector (info['modelId'], info['type'])).
            addClass ('stage-highlight')
    } else {
        $(this._getStageMemberListItemSelector (info['modelId'], info['type'])).
            removeClass ('stage-highlight')
    }
            
};

/**
 * Hides/shows the dummy 'No results found.' list item depending on whether or not the list 
 * associated with the specified stage is empty.
 * @param number stageNum
 */
DragAndDropViewManager.prototype._hideShowNoResultsDummyItem  = function (stageNum) {
    var that = this;
    var list = that._getListOfStage (stageNum);
    if ($(list).find (this.memberContainerSelector).not ('.ui-sortable-helper').length) {
        $(list).find ('.empty').hide ()
    } else {
        if ($(list).find ('.empty').length) {
            $(list).find ('.empty').show ()
        } else {
            $(list).append (
                $('<span>', {
                    'class': 'empty',
                    'text': this.translations['No results found.']
                })
            );
        }
    }
};

/**
 * Sets up behavior of stage member buttons
 */
DragAndDropViewManager.prototype._setUpStageMemberButtons = function () {
    var that = this;

    // hide/show buttons upon mousing over member container
    $(this.memberContainerSelector).unbind ('mouseover._setUpStageMemberButtons').
        bind ('mouseover._setUpStageMemberButtons', function () {

        if (!that._listItemIsLocked ($(this))) {
            $(this).find ('.stage-member-button').show (); 
            that._highlightUnHighlightSimilar ($(this), true);
        }
    });
    $(this.memberContainerSelector).unbind ('mouseleave._setUpStageMemberButtons').
        bind ('mouseleave._setUpStageMemberButtons', function () {

        $(this).find ('.stage-member-button').hide (); 
        that._highlightUnHighlightSimilar ($(this), false);
    });

    // Open stage details dialog
    $('.edit-details-button').unbind ('click._setUpStageMemberButtons').
        bind ('click._setUpStageMemberButtons', function (evt) {

        evt.preventDefault ();
        if (that._stageChangesLocked) return;
        var memberInfo = that._getStageMemberInfo (this);

        that.workflowStageDetails (
            that.workflowId,
            memberInfo['stageNumber'],
            memberInfo['type'],
            memberInfo['modelId']
        );
    });

    // Moves record forward or backward one stage
    function undoCompleteStage (undo) {
        var memberInfo = that._getStageMemberInfo (this);
        if (undo) {
            var relevantStage = that._getPrevStageNumber (memberInfo['stageNumber']);
        } else {
            var relevantStage = that._getNextStageNumber (memberInfo['stageNumber']);
        }
        var list = that._getListOfStage (relevantStage);
        var listItem = $(this).closest (that.memberContainerSelector);
        that._saveLastTouched (listItem);
        listItem = listItem.detach ();
        if (list) {
            $(list).prepend (listItem);
            listItem.mouseleave ();
            that._lockStageListItem (listItem);
        }
        that._hideShowNoResultsDummyItem (memberInfo['stageNumber']); 
        that._hideShowNoResultsDummyItem (relevantStage); 
        
        that._moveFromStageAToStageB (
            memberInfo['stageNumber'], 
            relevantStage,
            memberInfo['modelId'],
            memberInfo['type'],
            listItem
        );
    }

    /* complete button completes a stage, moving it to the next stage or, if the record is on 
    the last stage, off the pipeline entirely */
    $('.complete-stage-button').unbind ('click._setUpStageMemberButtons').
        bind ('click._setUpStageMemberButtons', function (evt) {

        evt.preventDefault ();      
        if (that._stageChangesLocked) return ;
        undoCompleteStage.call (this, false);
        return false;
    });

    /* undo button reverts a stage, moving it to the previous stage or, if the record is on the
    first stage, off the pipeline entirely */
    $('.undo-stage-button').unbind ('click._setUpStageMemberButtons').
        bind ('click._setUpStageMemberButtons', function (evt) {

        evt.preventDefault ();
        if (that._stageChangesLocked) return ;
        undoCompleteStage.call (this, true);
        return false;
    });
};

/**
 * Overrides method in parent 
 */
DragAndDropViewManager.prototype._beforeSaveStageDetails = function (
    form, modelId, modelName, stageNumber) {

    var that = this;
    var completeDate = +$(form).find ('[name="Actions[completeDate]"]').datepicker ('getDate');
    that.DEBUG && console.log ('completeDate = ');
    that.DEBUG && console.log (completeDate);


    if (completeDate !== 0 && completeDate < +new Date () && 
        !this._checkPermission (stageNumber, this._getNextStageNumber (stageNumber))) {
        
        this._successFailureAnimation (
            this._getStageMemberListItem (stageNumber, modelId, modelName), false);
        return false;
    } else {
        return true;
    }

     
    /*if (form['Actions[completeDate]'] && 
        parseInt (form['Actions[completeDate]'], 10) * 1000 < +new Date ()) {

        that.DEBUG && console.log ('true');
    } else {
       that.DEBUG && console.log ('false');
    }*/

};

/**
 * Overrides method in parent 
 */
DragAndDropViewManager.prototype._afterSaveStageDetails = function (
    response, modelId, modelName, stageNumber) {

    var that = this;

    that.DEBUG && console.log ('_afterSaveStageDetails');
    that.DEBUG && console.log ('response = ');
    that.DEBUG && console.log (response);
    that.DEBUG && console.log ('modelName = ');
    that.DEBUG && console.log (modelName);
    that.DEBUG && console.log ('modelId = ');
    that.DEBUG && console.log (modelId);

    if (response === 'complete') {
        var element = this._getStageMemberListItem (stageNumber, modelId, modelName);
        this._saveLastTouched (element);
        this._moveFromStageAToStageB (
            stageNumber, this._getNextStageNumber (stageNumber), modelId, modelName, element);
    }
};

/**
 * Overrides method in parent. Set the title of the change stage details dialog 
 */
DragAndDropViewManager.prototype._getStageDetailsTitle = function (
    stageNumber, modelName, modelId) {

    var that = this;

    var recordName = $(this._getStageMemberListItemSelector (modelId, modelName)).
        find ('.stage-member-name').find ('span').html ();

    var dialogTitle = recordName + ', ' + that.translations['Stage {n}'].replace(
        "{n}",stageNumber);

    that.DEBUG && console.log ('stageNumber = ');
    that.DEBUG && console.log (stageNumber);


    dialogTitle += ": "+this.stageNames[stageNumber-1];

    return dialogTitle;
};

/**
 * Sets up behavior of the title bar filters button.
 */
DragAndDropViewManager.prototype._setUpFilters = function () {
    var that = this;
    $(this._filtersButtonSelector).unbind ('click._setUpFilters')
        .bind ('click._setUpFilters', function (evt) {

        evt.preventDefault ();
        $('#workflow-filters-container').slideToggle ();
    });
};

/**
 * Returns the distance from the top of the window before which the staging area should be stuck. 
 */
DragAndDropViewManager.prototype._getStagingAreaStickyThreshold = function () {
    var pageTitleElem = $('.stage-list-title').eq (0);
    // calculate point before which staging area is hidden. Hiding the staging area just as
    // it reaches the top of the list view gives the illusion that it's sticky
    return $(pageTitleElem).height () + $(pageTitleElem).position ().top - 30;
};


/**
 * Gives illusion that staging area is stuck at the top of the process stage list 
 */
DragAndDropViewManager.prototype._setUpStagingAreas = function () {
    var that = this;
    $(window).off ('scroll._setUpStagingAreas').
        on ('scroll._setUpStagingAreas', function () {

        var scrollTop = $(window).scrollTop ();
        $('.stage-member-staging-area').each (function () {
            if (scrollTop < that._getStagingAreaStickyThreshold ()) {

                $(this).hide ();
            } else {
                $(this).show ();
            }
        });
    });
    $(window).scroll ();

    // cache these values so that they don't get overwritten when refresh () is called
    if (!this._setUpStagingAreas.containerLeft)
        this._setUpStagingAreas.containerLeft = $('.page-title.workflow').position ().left;
    if (!this._setUpStagingAreas.listWidth)
        this._setUpStagingAreas.listWidth = $('.stage-member-staging-area').width ();

    var containerLeft = this._setUpStagingAreas.containerLeft;
    var listWidth = this._setUpStagingAreas.listWidth;

    //that.DEBUG && console.log ('containerRight = ');
    //that.DEBUG && console.log (containerRight);

    // allows staging area to scroll horizontally with lists container
    $('#stage-member-lists-container').off ('scroll._setUpStagingAreas').
        on ('scroll._setUpStagingAreas', function () {

        var containerRight = containerLeft + 
            $('.page-title.workflow').outerWidth ();
        $('.stage-member-staging-area').each (function () {

            var list = $(this).parent ();
            var listLeft = $(list).position ().left;
            var overlapLeft = containerLeft - listLeft + 1;
            that.DEBUG && console.log (overlapLeft);
            var listRight = listLeft + listWidth;
            var overlapRight = listRight - containerRight + 1;
            that.DEBUG && console.log ('overlapRight = ');
            that.DEBUG && console.log (overlapRight);

            if (overlapRight <= 0 && overlapLeft <= 0) {
                // there's enough space to accommodate the staging area
                $(this).css ({
                    left: $(this).next ().position ().left + 'px',
                    width: listWidth + 'px'
                });
                $(this).children ().css ({
                    position: '',
                    right: '',
                    width: ''
                });
            } else if (overlapLeft > 0) {
                // shrink the staging area and shift over its cloned list items so that they 
                // appear to go underneath the edge of the container
                $(this).css ({
                    width: (listWidth - overlapLeft) + 'px',
                    left: overlapLeft + $(this).next ().position ().left + 'px'
                });
                $(this).children ().css ({
                    position: 'relative',
                    right: overlapLeft + 'px',
                    width: '99999999999999px'
                });
            } else { // overlapRight > 0

                // shrink the staging area so that id appears to go underneath the edge of the 
                // container
                $(this).css ({
                    width: (listWidth - overlapRight) + 'px',
                    left: $(this).next ().position ().left + 'px'
                });
                $(this).children ().css ({
                    width: '99999999999999px'
                });
            }
        });
    });
};

/**
 * Sets up the behavior of the 'Add a Deal' button.  
 */
DragAndDropViewManager.prototype._setUpAddADealButtonBehavior = function () {
    var that = this;
    $form = $('#add-a-deal-form-dialog');

    // set up dialog
    $dialog = $form.dialog ({
        title: that.translations['Add a Deal'],
        width: 500,
        autoOpen: false,
        buttons: [
            {
                text: that.translations['Cancel'],
                click: function () {
                    $(this).dialog ('close');
                }
            },
            {
                text: that.translations['Submit'],
                click: function () {
                    var valid = true;

                    // validate form, record name is required
                    if ($form.find ('.record-name-autocomplete').val () === '' ||
                        $form.find ('.record-name-autocomplete').val () === 
                        $form.find ('.record-name-autocomplete').attr ('data-default-text')) {

                        valid = false;
                        $form.find ('.record-name-autocomplete').addClass ('error');
                    };
                    if (valid) {
                        var newDealId = $('#new-deal-id').val ();
                        var newDealType = x2.modelNameToModuleName[$('#new-deal-type').val ()]
                            .toLowerCase ();
                        var recordName = $('.record-name-autocomplete').val ();
                        var newDealSelector = 
                            that.memberListContainerSelectors[0] + ' ' + 
                            that._getStageMemberListItemSelector (newDealId, newDealType);
                        that._startStage (
                            1, newDealId, newDealType, recordName, newDealSelector);
                        // kludge to prevent clearForm from deselecting record type
                        $('#new-deal-type').attr ('data-default', $('#new-deal-type').val ());
                        x2.forms.clearForm ($form, true);
                        $(this).dialog ('close');
                    }
                }
            }
        ],
        close: function () {
            that._unlockStageChanges ();
            $(this).dialog ('close');
        }
    });

    // bind button event
    $('#add-a-deal-button').unbind ('click._setUpAddADealButtonBehavior') 
        .bind ('click._setUpAddADealButtonBehavior', function () {

        that._lockStageChanges ();
        $dialog.dialog ('open');
        return false;
    });

    // fetch new autocomplete when the record type is changed
    $('#new-deal-type').unbind ('change._setUpAddADealButtonBehavior') 
        .bind ('change._setUpAddADealButtonBehavior', function () {

        x2.forms.inputLoading ($('#record-name-container'));
        $.ajax ({
            type: 'GET',
            url: that.ajaxGetModelAutocompleteUrl,
            data: {
                modelType: $(this).val ()
            },
            success: function (data) {
                x2.forms.inputLoadingStop ($('#record-name-container'));
                $('#record-name-container').find ('input').first ().html (data); 
            }
        });
        
    }).change ();
};

/**
 * Sets up a second scroll bar across the tops of the list views
 */
DragAndDropViewManager.prototype._setUpTopScrollbar = function () {
    var that = this;

    $topScrollBar = $('#stage-member-list-container-top-scrollbar');

    // give the scroll bar the same width as the lists container
    $topScrollBar.children ().first ().
        css ('width', $('#stage-member-lists-container-inner').width () + 'px');

    // scroll the lists container when the top scroll bar scrolls...
    $topScrollBar.unbind ('scroll._setUpTopScrollbar').
        bind ('scroll._setUpTopScrollbar', function () {
            $('#stage-member-lists-container').scrollLeft ($(this).scrollLeft ()); 
        });
    // ...and vice versa
    $('#stage-member-lists-container').unbind ('scroll._setUpTopScrollbar').
        bind ('scroll._setUpTopScrollbar', function () {
            $topScrollBar.scrollLeft ($(this).scrollLeft ()); 
        });

    // hide/show the scroll bar as the window resizes
    $(window).unbind ('resize._setUpTopScrollbar').
        bind ('resize._setUpTopScrollbar', function () {
            
            if ($('#stage-member-lists-container').width () <
                $('#stage-member-lists-container').get (0).scrollWidth) {

                $('#stage-member-list-container-top-scrollbar-outer').show ();
            } else {
                $('#stage-member-list-container-top-scrollbar-outer').hide ();
            }
        });

    $(window).trigger ('resize._setUpTopScrollbar');

};

/**
 * Instantiates record name qtips
 */
DragAndDropViewManager.prototype._setUpQtips = function () {

    var that = this;
    $.each (['opportunities', 'contacts', 'accounts'], function (i, type) {
        that._qtipManagers[type] = new x2.X2GridViewQtipManager ({
            loadingText: that.translations['Loading...'],
            qtipSelector: '.stage-member-type-' + type + ' .stage-member-name a',
            modelType: type,
            dataAttrTitle: false
        });
        that._qtipManagers[type].refresh ();
    });

};

DragAndDropViewManager.prototype._init = function () {
    var that = this;

    // shorten time before fade since these flashes can occur in quick succession
    x2.flashes.successFadeTimeout = 500; 

    // This shrinks this container horizontally to fit its contents. Originally, this container's
    // width is set to some extremely high value to prevent the list views from wrapping. After
    // the layout is rendered, this value can be reduced. Without this line of code, scrolling
    // issues occur.
    $('#stage-member-lists-container-inner').css (
        'width', ($('.stage-members').width () * this.stageCount) + 'px')

    this._setUpTopScrollbar ();
    this._setUpStageDetailsDialog ();
    this._setUpDragAndDrop ();
    this._setUpStageMemberButtons ();
    this._setUpFilters ();
    this._setUpStagingAreas ();
    this._setUpAddADealButtonBehavior ();
    this._setUpQtips ();
};

return DragAndDropViewManager;

}) ();

