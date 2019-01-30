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






$(function () {

    var DEBUG = false && x2.DEBUG;

    x2.flow.hideShowCronUI = function () {
        DEBUG && console.log('hideShowCronUI');
        var itemsWhichRequireCron = x2.flow.requiresCron;
        var requiresCron = false;
        for (var i in itemsWhichRequireCron) {
            if ($('.x2flow-main').find($('.' + itemsWhichRequireCron[i])).length > 0) {
                requiresCron = true;
                break;
            }
        }
        if (requiresCron) {
            $('#test-cron-button').show();
            $('#view-log-button').show();
        } else {
            $('#test-cron-button').hide();
            $('#view-log-button').hide();
        }
    };

    $.widget("x2.flowDraggable", $.ui.mouse, {

        options: {
            clone: true,
            renderDzBoxes: false,
        },

        nodeTree: [],
        nextId: 0,

        // parentBranch:$(),
        // startingIndex:0,

        node: $(),
        startingBranch: $(),
        startingIndex: 0,

        nodeParent: null, // element previously containing the node
        futureTarget: null,
        nodeBefore: null, // element (if any) right before the node's last good position
        nodeAfter: null, // element (if any) right after the node's last good position

        helper: null, // a clone of the original to be dragged around
        // placeholder:null,    // the original (or a clone) to preview where the node will be dropped

        mouseOffset: {}, // where the user clicked within the draggable
        helperHalfHeight: 0,
        helperHalfWidth: 0,

        deleteButton: null,
        deleteTarget: $(),

        dropTargets: null,
        dropZones: [], // array of top-left and bottom-right coordinates for droppable zones

        hoverTarget: null, // current drop target being hovered over

        moveTimer: null, // timeout to move things after a short delay

        _init: function () {

            var self = this;

            this.deleteButton = $("#item-delete").clone().show().bind('click', function () {
                if (window.flowEditor.isLoading()) {
                    return false;
                }
                if (flowEditor.currentItem.is(self.deleteTarget)) {
                    flowEditor.currentItem = $();
                    flowEditor.openItemConfig();
                }
                var oldParent = self.deleteTarget.parent();
                $(this).detach();
                self.deleteTarget.remove();
                self._toggleEmptyBox(oldParent);
                x2.flow.hideShowCronUI();
            });


            if (!this.options.clone) {
                $(this.element).on('mouseenter', '.x2flow-action, .X2FlowSwitch > .icon, .X2FlowSplitter > .icon', function () {
                    var offset = $(this).offset();
                    self.deleteButton.appendTo(this).offset({top: offset.top - 4, left: offset.left + 36});
                    self.deleteTarget = $(this).closest('.x2flow-node');
                }).on('mouseleave', '.x2flow-action, .x2flow-trigger, .X2FlowSwitch > .icon, .X2FlowSplitter > .icon',
                        function () {

                            self.deleteTarget = $();
                            self.deleteButton.detach();
                        });
            }
            this._mouseInit();    // setup mouse handling

            // $("#save-button").unbind().click(function() {
            // console.debug(self._getNodeTree($("#x2flow-main > .x2flow-branch")));
            // });
            // $(".x2flow-node").disableSelection()
        },

        _destroy: function () {
            this._mouseDestroy();
        },
        /**
         * decide whether to drag this thing
         */
        _mouseCapture: function (e) {
            return $(e.target).is(
                    '#all *, .X2FlowSwitch .icon, .X2FlowSplitter .icon, ' +
                    '.X2FlowSplitter .icon-inner, ' +
                    '.x2flow-node:not(.x2flow-trigger, .X2FlowSwitch, .X2FlowSplitter, .x2flow-empty)');
            //parents(".x2flow-node").length;
        },
        /**
         * start dragging; find drop targets, create helper element, etc
         */
        _mouseStart: function (e) {
            var self = this;
            $(".x2flow-node").disableSelection();

            this.node = $(e.target).closest(".x2flow-node");

            this.deleteTarget = $();
            this.deleteButton.detach();

            this.futureTarget = this.node.parent();
            this.nodeBefore = this.node.prev();
            if (this.options.clone || this.nodeBefore.length === 0)
                this.nodeBefore = null;

            var offset = this.node.offset();
            var nodeCenter = this.node.width() / 2;

            var startOffset = {
                left: offset.left + nodeCenter,
                top: offset.top
            };
            this.mouseOffset = {
                x: e.pageX - offset.left - nodeCenter,
                y: e.pageY - offset.top
            };

            // create a copy of the node to be dragged around
            this.helper = this.node.clone().disableSelection();

            if (this.options.clone) {
                this.node = this.node.clone().appendTo('#all');    // copy the original
            }

            this.node.addClass("x2flow-placeholder");
            
            

            this.dropTargets = $();

            // we can drag it into other branches, or the trash, but not into a branch inside itself
            $("#x2flow-main .x2flow-branch, .x2flow-trash").not(this.node.find(".x2flow-branch")).
                    each(function (i, elem) {

                        if (!(self.node.hasClass("X2FlowSwitch") ||
                                self.node.hasClass("X2FlowSplitter")) ||
                                ($(elem).children(".X2FlowSwitch").not(".x2flow-placeholder").length === 0 &&
                                        $(elem).children(".X2FlowSplitter").not(".x2flow-placeholder").length === 0)) {
                            // we can't drag a switch into a branch with an existing switch

                            self.dropTargets = self.dropTargets.add(elem);
                        }
                    });
            this.dropTargets.addClass("x2flow-active");

            this._calculateDropZones();

            this.helper
                    .addClass("x2flow-helper")
                    .disableSelection()
                    .css({position: "absolute", "z-index": "1"})
                    .offset(startOffset)
                    .appendTo("body");
        },
        /**
         * Called on mousemove event. Moves helper element, moves placeholder sometimes
         */
        _mouseDrag: function (e) {
            var self = this;

            var offset = {
                left: e.pageX - this.mouseOffset.x,
                top: e.pageY - this.mouseOffset.y
            };

            this.helper.offset(offset);        // make the helper follow the mouse

            // update this.hoverTarget, and this.nodeBefore if possible
            this.hoverTarget = this._getHoverTarget({x: e.pageX, y: e.pageY});
            if (this.hoverTarget !== null) {
                this.nodeBefore = this._getNodeBefore(e.pageX);
                this.nodeAfter = this._getNodeAfter(e.pageX);
            }

            // nodes with return val must be last in branch
            if (this.nodeBefore && this.nodeBefore.hasClass('X2FlowPushWebContent')) {
                return;
            } else if (this.nodeAfter && this.node.hasClass('X2FlowPushWebContent')) {
                return;
            }

            if (this.hoverTarget === null) {
                if (this.options.clone) { // if this is a new item being dragged in,
                    // move the placeholder back to the menu box;
                    this.nodeBefore = this.futureTarget.children().last();

                    if (this.futureTarget.attr("id") !== 'all') {
                        this.futureTarget = $('#all');
                        clearTimeout(this.moveTimer);
                        this.moveTimer = setTimeout(function () {
                            self._moveNode();
                            self._calculateDropZones();
                        }, 500);
                    }
                } else {
                    clearTimeout(this.moveTimer);
                }
            } else if (this.nodeBefore !== null) { // we're good to go, move the node
                if (x2.DEBUG && x2.flow.freezeHoverTarget)
                    return;
                this.hoverTarget.addClass("x2flow-hover");

                if (!this.hoverTarget.is(this.node.parent())) {
                    // trying to drag to a different branch

                    if (!this.hoverTarget.is(this.futureTarget)) {
                        this.futureTarget = this.hoverTarget;
                        clearTimeout(this.moveTimer);
                        this.moveTimer = setTimeout(function () {
                            self._moveNode();
                            self._calculateDropZones();
                        }, 100);
                    }
                } else if (!this.nodeBefore.is(this.node.prev(".x2flow-node"))) {
                    this.node.insertAfter(this.nodeBefore); // dragging within branch
                }
            }

        },

        // stop dragging, either destroy the helper or drop it into whatever thing
        _mouseStop: function (e) {
            if (x2.DEBUG && x2.flow.dragDisable)
                return;
            clearTimeout(this.moveTimer);

            this.helper.remove();
            this.node.removeClass("x2flow-placeholder");
            this.dropTargets.removeClass("x2flow-active x2flow-hover");
            this.futureTarget = $();

            if (this.node.parent().attr("id") === 'all') {
                this.node.remove();
            } else {
                if (this.options.clone) {    // node has been added, so open up the config
                    if (this.node.hasClass("X2FlowSwitch") || this.node.hasClass('X2FlowSplitter')) {
                        // switches can only be clicked on their icons
                        this.node.children(".icon").click();
                    } else {
                        this.node.click();
                    }

                }
                x2.flow.hideShowCronUI();
            }


        },
        _moveNode: function () {
            DEBUG && console.log('this.nodeBefore = ');
            DEBUG && console.log(this.nodeBefore);

            // nodes with return val must be last in branch
            if (this.nodeBefore.hasClass('X2FlowPushWebContent') ||
                    this.node.hasClass('X2FlowPushWebContent') &&
                    this.nodeBefore.next('.x2flow-node').not('.x2flow-empty').length) {
                return;
            }

            var oldParent = this.node.parent();
            this.node.insertAfter(this.nodeBefore);
            this._toggleEmptyBox(oldParent);
            this._toggleEmptyBox(this.futureTarget);
            // this.nodeParent = this.hoverTarget;
            this._calculateDropZones();
        },
        _toggleEmptyBox: function (elem) {
            if (!elem.hasClass("x2flow-trash")) {
                if (elem.children(".x2flow-node:not(.x2flow-empty)").length) {
                    elem.children(".x2flow-empty").remove();
                } else if (elem.children(".x2flow-empty").length === 0) {
                    $(document.createElement("div")).addClass("x2flow-node x2flow-empty").
                            appendTo(elem);
                }
            }
        },
        // get the bounding coordinates for each droppable zone
        _calculateDropZones: function () {
            this.dropZones = [];
            /* loop through all the droppable branches in reverse order, so deepest in the tree comes 
             first */
            for (var i = this.dropTargets.length - 1; i >= 0; i--) {
                var target = $(this.dropTargets[i]),
                        offset = target.offset(),
                        h = target.height(),
                        w = target.width();

                if (target.hasClass("x2flow-branch")) {
                    this.dropZones.push({
                        x0: offset.left,
                        y0: offset.top + (h / 2) - 25,
                        x1: offset.left + w + 50, // give it some extra space at the bottom
                        y1: offset.top + (h / 2) + 25,
                        elem: target
                    });
                } else {
                    this.dropZones.push({
                        x0: offset.left,
                        y0: offset.top,
                        x1: offset.left + w,
                        y1: offset.top + h,
                        elem: target
                    });
                }
            }
            // render dropzone boxes
            if (this.options.renderDzBoxes) {
                $(".dz").remove();
                for (var i in this.dropZones) {
                    var z = this.dropZones[i];
                    $(document.createElement("div"))
                            .addClass("dz")
                            .offset({left: z.x0, top: z.y0})
                            .width(z.x1 - z.x0)
                            .height(z.y1 - z.y0).appendTo("body");
                }
            }
        },
        /**
         * Scans through this.dropZones rectangles to determine the branch
         * farthest down the tree containing the given mouse coordinates.
         */
        _getHoverTarget: function (coords) {
            var hoverTarget = null;

            for (var i = 0; i < this.dropZones.length; i++) {
                var dropZone = this.dropZones[i];
                // we only want to highlight one element, so
                // this.hoverTarget will be set to the first one found
                if (hoverTarget === null
                        && coords.x > dropZone.x0
                        && coords.x < dropZone.x1
                        && coords.y > dropZone.y0
                        && coords.y < dropZone.y1
                        ) {
                    hoverTarget = $(dropZone.elem)
                } else {
                    $(dropZone.elem).removeClass("x2flow-hover"); // remove this from everything else
                }
            }

            return hoverTarget;
        },
        _getNodeAfter: function (pageX) {
            if (this.hoverTarget === null)
                return null;
            if (this.node.hasClass("X2FlowSwitch") ||
                    this.node.hasClass("X2FlowSplitter")) {
                // if we're dragging a switch, it has to go at the end
                return null;
            }

            // ignore the placeholder itself, the bracket element, and any .empty boxes
            var targetSiblings = this.hoverTarget.children(".x2flow-node").
                    not(".x2flow-placeholder, .x2flow-empty");
            var nodeAfter = null;

            for (var j = 0; j < targetSiblings.length; j++) {
                var sibling = $(targetSiblings[j]);

                if (sibling.offset().left + sibling.width() / 2 < pageX) {
                    continue;
                } else {
                    nodeAfter = sibling;
                    break;
                }
            }
            return nodeAfter;
        },
        /*
         * Scans through elements in this.hoverTarget and determines
         * which one is right before the placeholder. Returns the element (nodeBefore)
         * or null if it's at the end or hoverTarget is empty.
         */
        _getNodeBefore: function (pageX) {
            if (this.hoverTarget === null)
                return null;

            if (this.node.hasClass("X2FlowSwitch") ||
                    this.node.hasClass("X2FlowSplitter")) {
                // if we're dragging a switch, it has to go at the end
                return this.hoverTarget.children(".x2flow-node:not(.x2flow-placeholder)").last();
            }

            // ignore the placeholder itself, the bracket element, and any .empty boxes
            var targetSiblings = this.hoverTarget.children(".x2flow-node").
                    not(".x2flow-placeholder, .x2flow-empty, .X2FlowSwitch, .X2FlowSplitter");
            var nodeBefore = null;

            for (var j = 0; j < targetSiblings.length; j++) {
                var sibling = $(targetSiblings[j]);

                if (sibling.offset().left + sibling.width() / 2 < pageX) {
                    nodeBefore = sibling;
                } else {
                    break;
                }
            }
            if (nodeBefore !== null)
                return nodeBefore;
            return this.hoverTarget.children(".bracket:first");
        }
    });

    $("#all").flowDraggable({distance: 0, clone: true});
    $("#x2flow-main").flowDraggable({distance: 20, clone: false});

    window.flowEditor = {

        version: '5.2',
        idCounter: 0,
        loading: 0, // when this value is less than 0, config menu is loading

        currentItem: $(),
        currentConfig: {},

        trigger: $("#trigger"),

        flowItemOptionCache: {},
        conditionParamCache: {},

        showIdItems: [
            'X2FlowWait',
            'X2FlowEmail',
            'X2FlowRecordEmail'
        ],

        init: function () {
            var that = this;
            
            // listen for changes in fields on these parent elements
            // x2.fieldUtils.addChangeListener("#x2flow-conditions, #x2flow-attributes");
            x2.fieldUtils.addChangeListener("#x2flow-config-box");
            // we want the "changed" comparison for any attribute conditions
            x2.fieldUtils.enableChangedOperator = true;

            $('#x2flow-main').resizable({
                handles: 's',
                stop: function () {
                    $(this).css('width', '');
                }
            });

            // Listen for clicks on the "delete condition" buttom
            $("#x2flow-conditions, #x2flow-attributes, #x2flow-headers").on(
                    "click", "a.del", function () {

                        $(this).closest("li").slideUp(200, function () {
                            if ($(this).siblings('li').length === 0) {
                                $(this).parent('ol').prev('.x2flow-api-attributes-section-header').hide();
                            }
                            $(this).remove();
                        });
                    });

            // Listen for clicks on actual flow items, and load the config for them
            $("#x2flow-main").
                    on('click',
                            '.x2flow-action, .x2flow-trigger, .X2FlowSwitch > .icon, .X2FlowSplitter > .icon',
                            function () {

                                if (that.isLoading()) {
                                    return false;
                                }

                                if ($(this).is(flowEditor.currentItem))
                                    return;
                                // if(item.attr("id") !== "trigger" || $(this.currentItem.attr("id") !== "trigger"))
                                // don't save if we're changing the trigger, this screws stuff up
                                // but otherwise, save the data for the previous flow item
                                // this.saveCurrentConfig();    
                                flowEditor.saveCurrentConfig();
                                //$('.x2flow-main').find ('.x2flow-node.selected').removeClass ('selected');
                                flowEditor.currentItem.removeClass("selected");
                                flowEditor.currentItem = $(this).closest(".x2flow-node");    // set new current item
                                flowEditor.currentItem.addClass("selected");

                                flowEditor.openItemConfig();
                            });

            // "Add Condition" button (only visible on triggers and switches)
            $("#x2flow-add-condition").click(function () {
                var type = $("#x2flow-condition-type").val();
                if (type === 'attribute') {
                    var modelClass = flowEditor.getModelClass();
                    if (modelClass !== null) {
                        that.lockConfig();
                        x2.fieldUtils.getModelAttributes(modelClass, 'all', function (attributes) {
                            that.unlockConfig();
                            x2.fieldUtils.createAttrListItem(modelClass, attributes)
                                    // mark as a multiselect so it can be toggled back and forth later
                                    .data({"type": type, "multiple": 1})
                                    .hide()
                                    .appendTo("#x2flow-conditions ol")
                                    .slideDown(200)
                        });
                    }
                } else {
                    if (type !== 'workflow_status') {
                        that.lockConfig();
                        if (type === 'email_open') {
                            flowEditor.queryConditionParams(type, function (params) {
                                that.unlockConfig();
                                flowEditor.createItemCondition(params)
                                        .data("type", type)
                                        .hide()
                                        .appendTo("#x2flow-conditions ol")
                                        .addClass('email-open-condition')
                                        .slideDown(200);
                                that.updateEmailOpenConditions();
                            });

                        } else {
                            flowEditor.queryConditionParams(type, function (params) {
                                that.unlockConfig();
                                flowEditor.createItemCondition(params)
                                        .data("type", type)
                                        .hide()
                                        .appendTo("#x2flow-conditions ol")
                                        .slideDown(200);
                            });
                        }
                    } else {
                        flowEditor.createWorkflowStatusCondition()
                                .data("type", type)
                                .hide()
                                .appendTo("#x2flow-conditions ol")
                                .slideDown(200);
                    }
                }
            });

            // add an attribute row (on flow actions)
            $("#x2flow-add-attribute").click(function () {
                var modelClass = flowEditor.getModelClass();
                that.lockConfig();
                x2.fieldUtils.getModelAttributes(modelClass, 'all', function (attributeList) {
                    that.unlockConfig();
                    if (modelClass === "API_params") {
                        $('#x2flow-attributes .x2flow-api-attributes-section-header').show();
                        if (attributeList[0] && attributeList[0].type === "API_params") {
                            flowEditor.createApiParam(attributeList[0].name, attributeList[0].value)
                                    .hide()
                                    .appendTo("#x2flow-attributes ol")
                                    .data({type: "attribute", modelClass: modelClass})
                                    .slideDown(200);
                        }
                    } else if (attributeList) {
                        x2.fieldUtils.createAttrListItem(modelClass, attributeList, null, false)
                                .hide()
                                .appendTo("#x2flow-attributes ol")
                                .data({type: "attribute", modelClass: modelClass})
                                .slideDown(200);
                    }
                });
            });

            $("#x2flow-add-header").click(function () {
                var modelClass = flowEditor.getModelClass();
                that.lockConfig();
                x2.fieldUtils.getModelAttributes(modelClass, 'all', function (attributeList) {
                    that.unlockConfig();
                    $('#x2flow-headers .x2flow-api-attributes-section-header').show();
                    flowEditor.createApiParam(attributeList[0].name, attributeList[0].value, false)
                            .hide()
                            .appendTo("#x2flow-headers ol")
                            .data({type: "headers", modelClass: modelClass})
                            .slideDown(200);
                });
            });

            // Trigger menu - onchange opens trigger config (which then sets the flow's trigger and 
            // modelClass properties)
            $("#trigger-selector").change(function (e) {
                var prevTrigger = flowEditor.trigger.attr('class').match(/[^ ]+Trigger/);
                if (that.isLoading()) {
                    $('#trigger-selector').val(prevTrigger);
                    return false;
                }

                // change to or from targeted content request trigger 
                if ($(e.target).val().match(/TargetedContentRequestTrigger/)) {
                    that._targetedContentRequestTriggerChange();
                } else if (prevTrigger && prevTrigger[0] === 'TargetedContentRequestTrigger') {
                    if (!that._targetedContentRequestTriggerTearDown()) {
                        return;
                    }
                }

                flowEditor.trigger
                        .attr('class', '')
                        // .removeClass() // Doesn't work for some reason.
                        .removeData("config")
                        .addClass("x2flow-node x2flow-trigger " + $(e.target).val())
                        .attr("title", $(e.target).find("option:selected").text());
                if (!x2.flow.showLabels)
                    $(flowEditor.trigger).addClass("no-label");
                flowEditor.currentItem.removeClass("selected");
                flowEditor.currentItem = flowEditor.trigger;
                flowEditor.currentItem.addClass("selected");
                $(flowEditor.trigger).children('.x2flow-icon-label').html(
                        $("#trigger-selector").find("option:selected").text());

                flowEditor.openItemConfig();
                x2.flow.hideShowCronUI();
            });

            // listen for changes in model type; remove attribute conditions from the previous type
            $("#x2flow-main-config").on("change", "fieldset[name='modelClass'] select", function (e) {
                var newModelClass = $(e.target).val();
                var config = flowEditor.currentItem.data("config");
                if (typeof config === "object")
                    config.modelClass = newModelClass;

                $("#x2flow-attributes ol").empty();        // clear this item's attributes
                $("#x2flow-conditions li").each(function (i, condition) {
                    if ($(condition).data("type") === "attribute")
                        $(condition).remove();    // clear any attribute conditions for this item
                });
                $(document).trigger("modelClassChange", [newModelClass]);
            });

            // we changed the global modelClass; loop through all the items and delete 
            // attributes/conditions
            $(document).bind("modelClassChange", function (evt, modelClass) {
                $("#x2flow-main .x2flow-node").each(function (i, item) {
                    var itemConfig = $(item).data("config");
                    if (typeof itemConfig === 'undefined')
                        return;
                    // if this item doens't define its own model class,
                    if (typeof itemConfig.modelClass === 'undefined') {
                        // the attributes must refer to the trigger so they're now invalid
                        delete itemConfig.attributes;
                        delete itemConfig.conditions;
                    }
                });
            });

            $("#x2flow-main-config").on("change", "fieldset[name='linkField'] select", function (e) {
                var selectedOption = $("option:selected", this);
                flowEditor.currentItem.data("config").linkType = $(selectedOption).attr('data-value');
                flowEditor.clearFutureNodeAttributes(flowEditor.currentItem.next());
            });


            $('.x2flow-start form#submitForm').submit(function () {
                flowEditor.save();
            });
            $("#save-button").unbind().click(function () {
                $('#submitForm').submit();
            });
        },
        _getIds: function () {},
        /**
         * Generate a unique node id 
         * @return Number
         */
        _generateId: function () {
            return ++this.idCounter;
        },
        /*
         Parameters:
         node - instance of x2.flowDraggable
         */
        _deleteNode: function (node) {
            var nodeParent = node.parent();

            // replace empty node after switch
            if (nodeParent.hasClass('x2flow-branch') &&
                    nodeParent.children('.x2flow-node').first().is($(node))) {
                $(nodeParent).append($('<div>', {'class': 'x2flow-node x2flow-empty'}));
            }
            node.detach();
            x2.flow.hideShowCronUI();
        },
        _targetedContentRequestTriggerChange: function () {
            $('#all').find('.X2FlowPushWebContent').show();
            $('#targeted-content-embed-code-container').fadeIn(1000);
        },

        /**
         * Removes targeted content trigger specific UI elements if change is confirmed
         * @returns true if the user confirms trigger change or if there's no content to be lost, 
         * @returns false otherwise
         */
        _targetedContentRequestTriggerTearDown: function () {
            var that = this;

            if (window.configFormEditor.getData() &&
                    !window.confirm(x2.flow.translations['targetedContentTriggerChange'])) {

                return false;
            }
            /*$('.x2flow-main').find ('.x2flow-node.X2FlowPushWebContent').each (function () {
             that._deleteNode ($(this));
             });*/
            $('#all').find('.X2FlowPushWebContent').hide();
            $('#targeted-content-embed-code-container').hide();
            return true;
        },
        
        /**
         * Saves workflow flow
         * 
         * @returns {undefined}
         */
        save: function () {
            /* update current item's settings since normally
             they only get updated when you click a different item */
            this.saveCurrentConfig();
            this.invalidateDropdownCaches(this.trigger.data('config'));
            
            var flow = {
                version: this.version,
                idCounter: this.idCounter,
                trigger: this.trigger.data("config"),
                items: this.getNodeTree($("#x2flow-main > .x2flow-branch"))
            };
            
            $("#flowDataField").val(JSON.stringify(flow));
        },
        
        

        /**
         * Loads workflow flow
         * 
         * @param {type} flowData
         * @returns {undefined}
         */
        loadFlow: function (flowData) {
            if (typeof flowData !== 'object')
                flowData = JSON.parse(flowData);
            this.idCounter = flowData.idCounter || 0;
            // console.debug(flowData);
            if (flowData.trigger !== undefined && flowData.trigger.type !== undefined) {
                $("#trigger-selector").val(flowData.trigger.type);
                flowEditor.trigger
                        .removeClass()
                        .data("config", flowData.trigger)
                        .addClass("x2flow-node x2flow-trigger " + flowData.trigger.type)
                        .attr("title", $("#trigger-selector").find("option:selected").text())
                        .click();
                if (!x2.flow.showLabels)
                    $(flowEditor.trigger).addClass("no-label");
                $(flowEditor.trigger).children('.x2flow-icon-label').html(
                        //x2.flow.translations[$('#trigger-selector').find ("option:selected").val ()]);
                        $("#trigger-selector").find("option:selected").text());
            }
            if (flowData.items.length) {
                $("#x2flow-main > .x2flow-branch").empty().append(this.populateBranch(flowData.items));
            }
            x2.flow.hideShowCronUI();
        },

        /**
         * Populates branch
         * 
         * @param {type} items
         * @returns {jQuery|Window.flowEditor.populateBranch.branch|window.flowEditor.populateBranch.branch|window.$|$}
         */
        populateBranch: function (items) {
            var branch = $('<div class="bracket"></div>');
            for (var i in items) {
                var item = items[i];
                
                if (item.type === "X2FlowSwitch" || item.type === 'X2FlowSplitter') {
                    var flowSwitch = $("#all ." + item.type).clone().data("config", item);
                    var rightChildName = item.type === 'X2FlowSwitch' ? 'trueBranch' : 'upperBranch';
                    var leftChildName = item.type === 'X2FlowSwitch' ? 'falseBranch' : 'lowerBranch';

                    var branches =
                            flowSwitch.children(".x2flow-branch-wrapper").children(".x2flow-branch");
                    if (item[rightChildName].length) {
                        // create the branches all recursive-like n stuff
                        $(branches[0]).empty(".x2flow-empty").append(
                                this.populateBranch(item[rightChildName]));
                    }
                    if (item[leftChildName].length) {
                        $(branches[1]).empty(".x2flow-empty").append(
                                this.populateBranch(item[leftChildName]));
                    }
                    delete item[rightChildName];
                    delete item[leftChildName];    // we don't want this stuff going into $.data()

                    branch = branch.add(flowSwitch);
                } else {
                    var template = $("#all ." + item.type);
                    if (template.length) {
                        var flowItem = template.clone().data("config", item);
                        if (flowItem.attr('style') &&
                                flowItem.attr('style').match(/display[ ]*:[ ]*none;/)) {

                            flowItem.attr('style', '');
                        }
                        
                        branch = branch.add(flowItem);
                    }
                }
            }
            if (branch.length === 0) {
                // if for some reason this branch is empty, generate a placeholder item
                return $(document.createElement('div')).addClass("x2flow-node x2flow-empty");
            }
            return branch;
        },

        /**
         * Dependent dropdown caches should not be saved with the flow data. They should only persist
         * over the course of the lifetime of a single page. Clear these out of the config cache before
         * saving the flow.
         * @param object config
         */
        invalidateDropdownCaches: function (config) {
            if (typeof config === 'undefined')
                return;

            for (var i in config.options) {
                delete config.options[i].dropdownCache;
            }
        },

        /**
         * Gets node tree
         * 
         * @param {type} branch
         * @returns {Array|window.flowEditor.getNodeTree.items|Window.flowEditor.getNodeTree.items}
         */
        getNodeTree: function (branch) {
            var children = $(branch).children(".x2flow-node").not(".x2flow-empty");
            var items = [];

            for (var i = 0; i < children.length; i++) {
                var node = $(children[i]);
                var nodeData = node.data("config");
                this.invalidateDropdownCaches(nodeData);
                if (nodeData === undefined)
                    continue;

                if (nodeData.type === "X2FlowSwitch" || nodeData.type === 'X2FlowSplitter') {
                    var branches = node.children(".x2flow-branch-wrapper").children(".x2flow-branch");
                    if (branches.length !== 2)
                        break;    // something's seriously jacked up here

                    var rightChildName = nodeData.type === 'X2FlowSwitch' ?
                            'trueBranch' : 'upperBranch';
                    var leftChildName = nodeData.type === 'X2FlowSwitch' ?
                            'falseBranch' : 'lowerBranch';
                    nodeData[rightChildName] = this.getNodeTree(branches[0]);
                    nodeData[leftChildName] = this.getNodeTree(branches[1]);
                }
                items.push(nodeData);
            }
            return items;
        },
        
        


        /**
         * Gets the model class to be used for the specified item's model attributes
         * this will be either a hard-coded class, the value of the modelClass dropdown,
         * or the global model class (set by the trigger)
         */
        getModelClass: function (item) {
            // default to the current item
            var config = (item === undefined) ?
                    this.currentItem.data("config") : $(item).data("config");
            // console.debug(config);
            if (config !== undefined && config.modelClass !== undefined)
                return config.modelClass;
            if (config.type !== 'X2FlowRecordChange') {
                var linkType = this.findNearestRecordChange((item === undefined) ? this.currentItem : item);
                if (linkType !== undefined && linkType !== null) {
                    return linkType;
                }
            }
            var triggerConfig = flowEditor.trigger.data("config");
            if (triggerConfig !== undefined && triggerConfig.modelClass !== undefined)
                return triggerConfig.modelClass;
            return null;
        },

        /**
         * Finds the nearest Record Change action to determine the model class of
         * a flow item, or stops at the trigger
         */
        findNearestRecordChange: function (item) {
            if (item.is('#trigger')) {
                return null;
            }
            var prevNode = item.closest('.x2flow-node').prev();
            if (prevNode.hasClass('X2FlowRecordChange')) {
                return prevNode.data("config").linkType;
            } else if (prevNode.hasClass('bracket')) {
                var branch = prevNode.parent('.x2flow-branch');
                if (branch.prev().hasClass('x2flow-node')) {
                    var branchNode = branch.prev();
                } else {
                    var branchNode = prevNode.parents('.x2flow-node');
                }
                return this.findNearestRecordChange(branchNode);
            } else {
                return this.findNearestRecordChange(prevNode);
            }
        },

        /**
         * After a change to X2RecordChange nodes, we have to traverse the tree going
         * forwards to clear any conditions or attributes that are dependent on the 
         * modelClass set by a record change node
         */
        clearFutureNodeAttributes: function (item) {
            var that = this;
            var itemConfig = $(item).data("config");
            if (typeof itemConfig === 'undefined' || $(item).hasClass('X2FlowRecordChange')) {
                //Stop when we reach the end of a branch or another record change
                return;
            }
            // if this item doens't define its own model class,
            if (typeof itemConfig.modelClass === 'undefined') {
                // the attributes must refer to the trigger so they're now invalid
                delete itemConfig.attributes;
                delete itemConfig.conditions;
            }
            if ($(item).hasClass('X2FlowSwitch') || $(item).hasClass('X2FlowSplitter')) {
                $.each($(item).find('.x2flow-branch'), function (i, branch) {
                    that.clearFutureNodeAttributes($('.x2flow-node:first', branch));
                });
            } else {
                this.clearFutureNodeAttributes($(item).next());
            }
        },

        /**
         * Queries item parameters
         * 
         * @param {type} itemType
         * @param {type} callback
         * @returns {undefined}
         */
        queryItemParams: function (itemType, callback) {
            var self = this;
            if (this.flowItemOptionCache[itemType]) {
                callback(this.flowItemOptionCache[itemType]);
            } else {
                $.ajax({
                    url: yii.scriptUrl + "/studio/getParams",
                    data: {type: "action", name: itemType},
                    dataType: "json",
                    success: function (response) {
                        self.flowItemOptionCache[itemType] = response;
                        callback(response);
                    }
                });
            }
        },

        /**
         * Queries condition parameters
         * 
         * @param {type} type
         * @param {type} callback
         * @returns {undefined}
         */
        queryConditionParams: function (type, callback) {
            if (this.conditionParamCache[type]) {
                callback(this.conditionParamCache[type]);
            } else {
                $.ajax({
                    url: yii.scriptUrl + "/studio/getParams",
                    data: {type: "condition", name: type},
                    dataType: "json",
                    success: function (response) {
                        flowEditor.conditionParamCache[type] = response;
                        callback(response);
                    }
                });
            }
        },

        /**
         * Generates an object containing the main options for the currently open item config.
         * Used for maintaining flow item state and for saving flows.
         */
        saveCurrentConfig: function (item) {
            if (this.currentItem.length === 0)
                return;
            var config = this.currentItem.data("config");
            if (config === undefined)
                config = {};
            config.options = {};
            // loop through everything in the main config form
            $("#x2flow-main-config fieldset").each(function (i, fieldset) {
                // but only save the ones with an actual name attribute
                var fieldName = $(fieldset).attr("name");

                var val = x2.fieldUtils.getVal(
                        $(fieldset).find(".x2fields-value :input[name='value']").add(
                        $(fieldset).find(".x2fields-value :input[name='value[]']")).
                        not('.checkbox-hidden').first()
                        );

                var op = $(fieldset).find(".x2fields-operator select").val();

                if (fieldName !== undefined && val !== undefined) {
                    config.options[fieldName] = {value: val};
                    if (op !== undefined)
                        config.options[fieldName].operator = op;
                    // if(op === undefined) {
                    // config.options[fieldName] = val;    // no operator, save as a scalar
                    // } else {
                    // save as associative array
                    // config.options[fieldName] = {value:val, operator:op};    
                    // }
                }

                // look for the cache used by dependent dropdowns, cache it if found
                var cache = x2.fieldUtils.checkForDependentDropdownCache(fieldset);
                if (cache) {
                    if (typeof config.options[fieldName] === 'undefined')
                        config.options[fieldName] = {};
                    config.options[fieldName]['dropdownCache'] = cache;
                }

            });

            delete config.conditions;    // clear old data

            // scan through dynamically added attribute fields (if there are any)
            var conditionRows = $("#x2flow-conditions li");
            if (conditionRows.length) {
                config.conditions = [];
                conditionRows.each(function (i, elem) {
                    // var fieldset = $(elem).children("fieldset").first();
                    elem = $(elem);
                    var type = elem.data("type");
                    if (type === undefined)
                        return;
                    if (type !== 'workflow_status') {
                        config.conditions.push({
                            type: type,
                            name: $(elem).find(".x2fields-attribute select").val(),
                            operator: $(elem).find(".x2fields-operator select").val(),
                            value: x2.fieldUtils.getVal($(elem).
                                    find(".x2fields-value :input[name='value']").add(
                                    $(elem).find(".x2fields-value :input[name='value[]']")
                                    ).not('.checkbox-hidden').first()
                                    )
                        });
                    } else {
                        config.conditions.push({
                            type: type,
                            workflowId: $(elem).find('[name="workflowId"]').val(),
                            stageNumber: $(elem).find('[name="stageNumber"]').val(),
                            stageState: $(elem).find('[name="stageState"]').val(),
                        });
                    }
                });
            }
            delete config.attributes;    // clear old data
            // scan through dynamically added attribute fields (if there are any)
            var attributeRows = $("#x2flow-attributes li");
            if (attributeRows.length) {
                config.attributes = [];
                attributeRows.each(function (i, elem) {
                    config.attributes.push({
                        name: $(elem).find(".x2fields-attribute select, .x2fields-attribute input").
                                first().val(),
                        operator: $(elem).find(".x2fields-operator select").first().val(),
                        value: x2.fieldUtils.getVal(
                                $(elem).find(".x2fields-value :input[name='value']").add(
                                $(elem).find(".x2fields-value :input[name='value[]']")
                                ).not('.checkbox-hidden').first()
                                )
                    });
                });
            }

            if (this.getItemType(this.currentItem) === 'X2FlowApiCall') {
                delete config.headerRows;    // clear old data
                var headerRows = $("#x2flow-headers li");
                if (headerRows.length) {
                    config.headerRows = [];
                    headerRows.each(function (i, elem) {
                        config.headerRows.push({
                            name: $(elem).find(".x2fields-attribute select, .x2fields-attribute input").
                                    first().val(),
                            operator: $(elem).find(".x2fields-operator select").first().val(),
                            value: x2.fieldUtils.getVal($(elem).
                                    find(".x2fields-value :input[name='value']").first())
                        });
                    });
                }
            }

            this.currentItem.data("config", config);
        },

        /**
         * Gets workflow item type
         * 
         * @param {type} item
         * @returns {String|jQuery|window.flowEditor.getItemType.classList}
         */
        getItemType: function (item) {
            if ($(item).hasClass("X2FlowSwitch"))
                return "X2FlowSwitch";
            if ($(item).hasClass("X2FlowSplitter"))
                return "X2FlowSplitter";
            var classList = $(item).attr("class");
            if (classList !== undefined) {
                classList = classList.split(/\s+/);
                for (var i in classList) {
                    if ($.inArray(classList[i],
                            ["x2flow-node", "x2flow-action", "x2flow-trigger", "selected"]) === -1) {
                        return classList[i];
                    }
                }
            }
            return null;
        },

        /**
         * Checks if is loading
         * @returns {Boolean}
         */
        isLoading: function () {
            return this.loading < 0;
        },

        /**
         * Locks if loading
         */
        lockConfig: function () {
            this.loading--;
            $("#x2flow-config-box").addClass("loading");
        },

        /**
         * Unlocks when unfinished loading
         */
        unlockConfig: function () {
            this.loading++;
            if (this.loading >= 0) {
                $("#x2flow-config-box").removeClass("loading");
            }
        },

        /**
         * Loads the config panel for a flow item.
         * Calls {@link queryItemParams()} to load the allowed params from AJAX/cache,
         * then calls {@link createMainConfigForm()} and loops through any saved attributes/conditions
         * with {@link createAttrListItem()} and {@link createAttrListItem()}
         */
        openItemConfig: function () {
            if (this.flowItem)
                this.flowItem.destroy();
            var itemType = this.getItemType(this.currentItem);

            // clear out the old config panel
            $("#x2flow-main-config, #x2flow-conditions ol, #x2flow-attributes ol, #x2flow-headers ol").
                    empty();

            // if we're just cleaning stuff, we're done
            if (this.currentItem.length === 0)
                return;

            $("#x2flow-add-condition, #x2flow-condition-type, #x2flow-add-attribute, #x2flow-conditions-hr").hide();

            var isTrigger =
                    (this.currentItem.hasClass("x2flow-trigger") ||
                            this.currentItem.hasClass("X2FlowSwitch"));

            if (itemType === 'X2FlowApiCall') {
                $('#x2flow-add-header').show();
            } else {
                $('#x2flow-add-header').hide();
                $('.x2flow-api-attributes-section-header').hide();
            }

            // load the options (via ajax if not cached), then run this function
            var that = this;
            this.lockConfig();
            this.queryItemParams(itemType, function (params) {
                if (params !== false) {
                    var config = flowEditor.currentItem.data("config");
                    if (config === undefined) {
                        // this item just got added, time to initialize the config

                        config = {
                            id: that._generateId(),
                            type: itemType,
                            options: {}
                        };

                        if (params.modelClass !== undefined) {
                            // set modelClass of this flow item (if applicable)
                            config.modelClass = params.modelClass;
                        }
                        flowEditor.currentItem.data("config", config); // save it
                    }

                    // create main form (with previous settings)
                    var form = flowEditor.createMainConfigForm(params, isTrigger, config.options);

                    $("#x2flow-main-config").append(
                            $('<h2 style="display: inline; margin-right: 0px;">').text(params.title)).append(form);
                    if (that.showIdItems.indexOf(config.type) > -1) {
                        $("#x2flow-main-config h2").after(
                                $('<div>', {
                                    'class': 'x2flow-action-id'
                                }).append(
                                $('<span>').text(' (ID: ' + config.id + ') '),
                                x2.forms.hint(x2.flow.translations['idHint' + config.type])
                                )
                                );
                    }
                    x2.forms.initializeDefaultFields();
                    x2.fieldUtils.updateDependentDropdowns(form);

                    // create attribute and/or generic condition lists
                    flowEditor.loadAttributes(config.attributes);
                    flowEditor.loadLinkAttributes(config);
                    if (itemType === 'X2FlowApiCall') {
                        flowEditor.loadHeaders(config.headerRows);
                    }
                    flowEditor.loadConditions(config.conditions);

                    // instantiate ckeditor for field with richtext type
                    if ($('#configFormEditor').length !== 0) {
                        var ckeditorParams = {
                            //fullPage: true,
                            fullPage: false,
                            height: 130
                        };

                        if (itemType === 'X2FlowPushWebContent' ||
                                itemType === 'TargetedContentRequestTrigger') {
                            var toolbar = 'MyTargetedContentToolbar';
                        }

                        // use insertable attributes associated with model class corresponding with
                        // trigger, if both the trigger and the model class exist
                        if ($(trigger).data() && $(trigger).data().config &&
                                $(trigger).data().config.modelClass && !isTrigger) {


                            var modelClass = $(trigger).data().config.modelClass;
                            ckeditorParams['insertableAttributes'] = {};
                            ckeditorParams['insertableAttributes'][modelClass + ' Attributes'] =
                                    x2.flow.insertableAttributes[modelClass];
                        }
                        that.lockConfig();
                        window.configFormEditor = createCKEditor(
                                'configFormEditor', ckeditorParams, function () {
                                    that.unlockConfig();
                                }, toolbar);
                    }
                }

                // instantiate item-specific manager class
                if (x2.X2FlowItem && x2[itemType] && x2[itemType].prototype &&
                        (x2[itemType].prototype instanceof x2.X2FlowItem)) {
                    that.flowItem = new x2[itemType]({
                        form$: $(form)
                    });
                }
                that.unlockConfig();
            });
        },

        /**
         * Initializes main configuration form for provided node under main stage
         */
        createMainConfigForm: function (params, isTrigger, prevOptions) {
            DEBUG && console.log(params);

            // If prevOptions is undefined, initialize to empty object
            if (prevOptions === undefined)
                prevOptions = {};

            // Loads node description
            var form = $(document.createElement('div'));
            if (params.info) {
                form.html($('<div>', {text: params.info, "class": 'x2-flow-config-info'}));
            }

            // If options available, append an <hr> for UI purposes
            if (params.options.length > 0) {
                form.append('<hr style="margin-top: 5px; margin-bottom: 5px;">');
            }

            // Iterate through parameter options
            for (var i in params.options) {
                var optionParams = params.options[i];

                // If attribute, add then skip to next option
                if (optionParams.name === 'attributes' || optionParams.name === 'headers') {
                    $("#x2flow-add-attribute").show();
                    continue;
                }

                // Creates container for field
                var row = $(document.createElement('div')).addClass('row').appendTo(form);

                // Creates fieldset container
                var fieldset = $(document.createElement('fieldset')).attr("name", optionParams.name).
                        appendTo(row);
                var val = undefined, op = undefined, dropdownCache = undefined;

                // If htmlOptions provided, add to flowEditor
                if (typeof optionParams.htmlOptions !== 'undefined') {
                    flowEditor.addHtmlOptions(fieldset, optionParams.htmlOptions);
                }

                if (prevOptions[optionParams.name] !== undefined) {
                    var field = prevOptions[optionParams.name];
                    if (typeof field === 'object') {    // is it a multipart field or a simple field?
                        val = field.value;
                        op = field.operator;
                        dropdownCache = field.dropdownCache;
                    } else {
                        val = field;
                    }
                }

                // Displays field label
                if (optionParams.label !== undefined) {
                    $(document.createElement('label')).html(optionParams.label).appendTo(fieldset);
                }

                if (optionParams.operators !== undefined) {
                    var operatorCell =
                            $(document.createElement("div")).addClass("cell x2fields-operator").
                            appendTo(fieldset);
                    var dropdown = x2.fieldUtils.buildOperatorDropdown(optionParams.operators, op);
                    $(operatorCell).append(dropdown);
                }

                var fieldOptions = $.extend({}, optionParams);

                if (val !== undefined) {
                    // if there is saved data, insert field values into the fieldOptions object
                    fieldOptions.value = val; //prevOptions[fieldOptions.name];    
                }
                if (dropdownCache) {
                    fieldOptions.dropdownCache = dropdownCache;
                }

                var valueCell = $(document.createElement("div")).addClass("cell x2fields-value").
                        appendTo(fieldset);

                fieldOptions.name = "value";
                var input = x2.fieldUtils.createInput(fieldOptions);

		

                $(valueCell).append(input);

                // id will be used to instantiate ckeditor after form is appended to DOM node
                if (fieldOptions.type === 'richtext') {
                    $(input).attr('id', 'configFormEditor');
                    $(input).attr('class', 'rich-text');
                    $(valueCell).addClass('editor-container');
                }

                if (optionParams.name === 'modelClass') {
                    var config = this.currentItem.data("config");
                    if (typeof config === "object")
                        config.modelClass = $(input).val();
                }

                // instantiate qtips if they're present in the config menu
                $(fieldset).find('.x2-hint').each(function () {
                    $(this).qtip();
                });
            }

            // If displayed node is trigger, show add-condition options
            if (isTrigger) {
                $("#x2flow-add-condition, #x2flow-condition-type, #x2flow-conditions-hr").show();

                if (flowEditor.getModelClass() === null) {
                    $("#x2flow-condition-type option:first").attr("disabled", "disabled");
                    $("#x2flow-condition-type").val($("#x2flow-condition-type option:nth-child(2)").
                            attr("value"));
                } else {
                    $("#x2flow-condition-type option:first").removeAttr("disabled");
                    $("#x2flow-condition-type").val($("#x2flow-condition-type option:first").
                            attr("value"));
                }
            }

            // Sets up email form if node is X2FlowRecordEmail or X2FlowEmail
            if (params['class'] === 'X2FlowRecordEmail' || params['class'] === 'X2FlowEmail') {
                this.setUpEmailForm(form);
            }

            return form;
        },

        /**
         * Adds html options as to the fieldset in the main config menu
         */
        addHtmlOptions: function (fieldset, htmlOptions) {
            var option;
            for (var i in htmlOptions) {
                option = htmlOptions[i];
                switch (i) {
                    case 'class':
                        $(fieldset).addClass(option);
                        break;
                    default:
                        $(fieldset).attr(i, option);
                        break;
                }
            }
        },

        /**
         * Sets up email template feature
         */
        setUpEmailForm: function (form) {

            function templateSwitchConfirm() {
                var proceed = true;
                var noChange = !window.configFormEditor.checkDirty();
                if (!noChange)
                    proceed = window.confirm(x2.flow.translations['templateChangeConfirm']);
                return proceed;
            }

            DEBUG && console.log('setUpEmailForm');
            DEBUG && console.log($(form));
            DEBUG && console.log($(form).find('[name="template"]'));

            $(form).find('[name="template"]').find('select').change(function () {
                DEBUG && console.log('select');

                var template = $(this).val();
                DEBUG && console.log(template);
                if (template !== "" && templateSwitchConfirm()) {

                    $.ajax({
                        url: yii.baseUrl + "/index.php/docs/fullView/" + template + "?json=1",
                        type: "GET",
                        dataType: "json"
                    }).done(function (data) {
                        window.configFormEditor.setData(data.body);
                        $(form).find('[name="subject"]').find('input').val(data.subject);
                        if (typeof data.to !== 'undefined' && data.to !== '')
                            $(form).find('[name="to"]').find('input').val(data.to);
                        window.configFormEditor.document.on("keyup", function () {
                            $(form).find('[name="template"]').find('select').val("0");
                        });
                    });
                }
            });
        },

        /**
         * Updates lists of email opened options on condition attributes
         */
        updateEmailOpenConditions: function () {
            var that = this;
            var emailIds = [];
            $("#x2flow-main .x2flow-node").each(function (i, item) {
                var itemConfig = $(item).data("config");
                if (typeof itemConfig === 'undefined')
                    return;
                if (itemConfig['type'] === 'X2FlowEmail' ||
                        itemConfig['type'] === 'X2FlowRecordEmail') {
                    emailIds.push(itemConfig['id']);
                }
            });
            $(".email-open-condition select").empty();
            for (var i = 0; i < emailIds.length; i++) {
                $(".email-open-condition select").append($("<option>")
                        .val(emailIds[i])
                        .html('Flow Item ID: ' + emailIds[i])
                        );
            }
            $('.email-open-condition select').val(function () {
                return $(this).closest('li').attr('data-value');
            });
            $('.email-open-condition select').on('change', function () {
                $(this).closest('li').attr('data-value', $(this).val());
            });
        },

        /**
         * Loads link attributes
         */
        loadLinkAttributes: function (config) {
            var that = this;
            var modelClass = this.getModelClass();
            var selectedField = null;
            if (typeof config.options.linkField !== 'undefined'
                    && typeof config.options.linkField.value !== 'undefined') {
                selectedField = config.options.linkField.value;
            }
            this.lockConfig();
            x2.fieldUtils.getModelAttributes(modelClass, 'link', function (attributeList) {
                that.unlockConfig();
                $('fieldset[name="linkField"] select').empty();
                var triggerConfig = flowEditor.trigger.data("config");
                $('fieldset[name="linkField"] select').append($('<option></option>').attr('value', 'original').attr('data-value', triggerConfig.modelClass).text('Original Record'));
                for (var i in attributeList) {
                    if (typeof attributeList[i]['name'] !== 'undefined'
                            && typeof attributeList[i]['label'] !== 'undefined') {
                        if (i === 0 && config.type === 'X2FlowRecordChange') {
                            config.linkType = attributeList[i]['linkType'];
                        }
                        var option = $('<option></option>').attr('value', attributeList[i]['name']).attr('data-value', attributeList[i]['linkType']).text(attributeList[i]['label']);
                        if (selectedField === attributeList[i]['name']) {
                            if (config.type === 'X2FlowRecordChange') {
                                config.linkType = attributeList[i]['linkType'];
                            }
                            option.attr('selected', 'selected');
                        }
                        $('fieldset[name="linkField"] select').append(option);
                    }
                }
            });
        },

        /**
         * Creates an attribute entry in #x2flow-attributes for each attribute in the list provided
         */
        loadAttributes: function (attributes) {
            var that = this;
            // console.debug(attributes);
            if (attributes === undefined)
                return;

            var modelClass = this.getModelClass();

            // loop through any saved attributes
            this.lockConfig();
            x2.fieldUtils.getModelAttributes(modelClass, 'all', function (attributeList) {
                that.unlockConfig();

                for (var i in attributes) {
                    var attr = attributes[i];

                    if (modelClass === "API_params") {
                        $('#x2flow-attributes .x2flow-api-attributes-section-header').show();
                        flowEditor.createApiParam(attr.name, attr.value)
                                .appendTo("#x2flow-attributes ol");
                        /*flowEditor.createApiParam(attr.name, attr.value, false)
                         .appendTo("#x2flow-headers ol");*/
                    } else if (modelClass !== null) {
                        // there is no operator, tell createAttrListItem() not to add a selector
                        if (attr.operator === undefined)
                            // (this must be a flow action, where the attributes are just being set, 
                            // not tested)
                            attr.operator = false;
                        x2.fieldUtils.createAttrListItem(
                                modelClass, attributeList, attr.name, attr.operator, attr.value)
                                .data("type", "attribute")
                                .appendTo("#x2flow-attributes ol");
                    }
                }
            });
        },

        loadHeaders: function (headers) {
            // loop through any saved headers
            for (var i in headers) {
                $('#x2flow-headers .x2flow-api-attributes-section-header').show();
                var header = headers[i];

                flowEditor.createApiParam(header.name, header.value, false)
                        .appendTo("#x2flow-headers ol");
            }
        },

        loadConditions: function (conditions) {
            var that = this;
            // console.debug(conditions);
            if (conditions === undefined)
                return;
            $.each(conditions, function (i, condition) {
                if (condition.type === undefined)
                    return;
                if (condition.type === "attribute") {
                    var modelClass = that.getModelClass();
                    if (modelClass !== null) {
                        that.lockConfig();
                        x2.fieldUtils.getModelAttributes(modelClass, 'all', function (attributeList) {
                            that.unlockConfig();
                            // console.debug(condition);
                            x2.fieldUtils.createAttrListItem(
                                    modelClass, attributeList, condition.name, condition.operator,
                                    condition.value)
                                    .data("type", "attribute")
                                    .appendTo("#x2flow-conditions ol");
                        });
                    }
                } else {
                    if (condition.type !== 'workflow_status') {
                        that.lockConfig();
                        if (condition.type === 'email_open') {
                            that.queryConditionParams(condition.type, function (data) {
                                that.unlockConfig();
                                data.operator = condition.operator;
                                data.value = condition.value;
                                flowEditor.createItemCondition(data)
                                        .data("type", condition.type)
                                        .addClass('email-open-condition')
                                        .attr('data-value', data.value)
                                        .appendTo("#x2flow-conditions ol");
                                that.updateEmailOpenConditions();
                            });
                        } else {
                            that.queryConditionParams(condition.type, function (data) {
                                that.unlockConfig();
                                data.operator = condition.operator;
                                data.value = condition.value;
                                flowEditor.createItemCondition(data)
                                        .data("type", condition.type)
                                        .appendTo("#x2flow-conditions ol");
                            });
                        }
                    } else {
                        flowEditor.createWorkflowStatusCondition(condition)
                                .data("type", condition.type)
                                .appendTo("#x2flow-conditions ol");
                    }
                }
            });
        },
        /**
         * Creates a workflow status condition
         * @return object jQuery <li> element containing the workflow status condition fieldset
         */
        createWorkflowStatusCondition: function createWorkflowStatusCondition(condition) {
            var condition = typeof condition === 'undefined' ? {} : condition;

            var li = x2.fieldUtils.templates.workflowStatusConditionForm.clone();
            var stageDropdown = $(li).find('[name="stageNumber"]');
            var workflowDropdown = $(li).find('[name="workflowId"]');
            var stateDropdown = $(li).find('[name="stageState"]');

            // check if saved workflow id is different that the default workflow id
            var fetchNewStageDropdown = false;
            if ($(workflowDropdown).val() !== condition.workflowId) {
                // stages for the workflow with saved workflow id must be fetched
                fetchNewStageDropdown = true;
            }

            if (typeof createWorkflowStatusCondition.cache === 'undefined') {
                // used to cache the requested stage name options
                createWorkflowStatusCondition.cache = {};

                // add default option to the cache
                var defaultOptions = [];
                $(stageDropdown).find('option').each(function () {
                    defaultOptions.push([$(this).val(), $(this).html()]);
                });
                createWorkflowStatusCondition.cache[$(workflowDropdown).val()] = defaultOptions;
            }

            // set saved values
            $(stageDropdown).val(condition.stageNumber);
            $(workflowDropdown).val(condition.workflowId);
            $(stateDropdown).val(condition.stageState);

            // replaces old stage name dropdown with a new one using options either from the cache
            // or from an AJAX response
            function buildNewDropdown(data) {
                var $newDropdown = x2.fieldUtils.buildDropdown(data, {
                    name: $(stageDropdown).attr('name')
                });
                $(stageDropdown).replaceWith($newDropdown);
                stageDropdown = $newDropdown;
                stageDropdown.val(condition.stageNumber);
            }

            // fetch new stage select options when workflow id select changes
            $(workflowDropdown).unbind('change').
                    bind('change', function fetchStageOptions() {

                        var workflowId = $(this).val();
                        // check the cache first
                        if (typeof createWorkflowStatusCondition.cache[workflowId] !== 'undefined') {
                            var data = createWorkflowStatusCondition.cache[workflowId];
                            buildNewDropdown(data);
                            return;
                        }

                        // cache miss, request the options
                        x2.forms.inputLoading(stageDropdown);
                        $.ajax({
                            url: yii.scriptUrl + '/workflow/workflow/getStageNames',
                            type: 'get',
                            dataType: 'json',
                            data: {
                                workflowId: workflowId,
                                optional: false
                            },
                            success: function (data) {
                                x2.forms.inputLoadingStop(stageDropdown);
                                buildNewDropdown(data);

                                // cache the results
                                createWorkflowStatusCondition.cache[workflowId] = data;
                            }
                        });
                    });

            if (fetchNewStageDropdown) {
                $(workflowDropdown).change();
            }
            return li;
        },
        createItemCondition: function (condition) {
            // clone template condition form
            var li = x2.fieldUtils.templates.conditionForm.clone();
            var fieldset = li.find('fieldset').first();
            $(document.createElement("div")).addClass("cell inline-label").text(condition.label).
                    appendTo(fieldset);

            if (condition.operators) {
                x2.fieldUtils.createOperatorCell(
                        condition.operators, condition.operator).appendTo(fieldset);
                li.on("change", ".x2fields-operator select", function () {
                    x2.fieldUtils.updateValueCell(this);
                });
                if (condition.multiple)
                    li.data("multiple", true);
            }
            x2.fieldUtils.createValueCell(condition).appendTo(fieldset);

            return li;
        },
        createApiParam: function (name, val, params) {
            var params = typeof params === 'undefined' ? true : params;
            // clone template condition form
            var li = x2.fieldUtils.templates.conditionForm.clone();
            if (params) {
                var fieldset = li.find('fieldset').replaceWith($("#condition-templates .API_params").
                        clone());
            } else {
                var fieldset = li.find('fieldset').replaceWith($("#condition-templates .APIHeaders").
                        clone());
            }
            li.find(".x2fields-attribute input").val(name);
            li.find(".x2fields-value input").val(val);

            return li;
        }
    };

    /**
     * Shows trace box
     */
    $("#show-trace-button").on("click", function () {
        if ($("#x2flow-trace-box").is(":visible")) {
            $("#x2flow-trace-box").slideUp();
        } else {
            $("#x2flow-trace-box").slideDown();
            $("html,body").animate({
                scrollTop: ($("#x2flow-trace-box").offset().top - 100)
            }, 300);
        }
    });

    /**
     * Toggles flow labels
     */
    $("#x2flow-show-labels-checkbox").on("change", function () {
        if (this.checked) { // show labels
            $(".x2flow-main").find(".x2flow-icon-label").show();
            $(".x2flow-node.x2flow-action").each(function () {
                if ($(this).children().first().hasClass("x2flow-icon-label")) {
                    $(this).children().first().attr("style", "");
                }
            });
            $(".x2flow-node").each(function () {
                if ($(this).children().first().hasClass("x2flow-icon-label")) {
                    $(this).removeClass("no-label"); // used to position arrows
                }
            });
            $("#trigger").find(".x2flow-icon-label").attr("style", "");
            $("#trigger").removeClass("no-label");
            auxlib.saveMiscLayoutSetting("x2flowShowLabels", 1);
            x2.flow.showLabels = true;
        } else { // hide labels
            $(".x2flow-main").find(".x2flow-icon-label").hide();
            $(".x2flow-node.x2flow-action").each(function () {
                if ($(this).children().first().hasClass("x2flow-icon-label")) {
                    $(this).children().first().attr("style", "display: none;");
                }
            });
            $(".x2flow-node").each(function () {
                if ($(this).children().first().hasClass("x2flow-icon-label")) {
                    $(this).addClass("no-label"); // used to position arrows
                }
            });
            $("#trigger").find(".x2flow-icon-label").attr("style", "display: none;");
            $("#trigger").addClass("no-label");
            auxlib.saveMiscLayoutSetting("x2flowShowLabels", 0);
            x2.flow.showLabels = false;
        }
    });

    /**
     * Hides workflow actions based on chosen trigger
     */
    function hideActions() {
        var option = $("#trigger-selector option:selected");
        var trigger = option.text();

        // If no trigger selected, hide actions bank
        if (trigger === 'Select a trigger') {
            $('#actions-bank').hide();
        } else {
            $('#actions-bank').show();
        }

        $('#all').children().each(function () {
            // Hides record actions if record model not passed
            if ($.inArray(trigger, x2.recordModelTriggers) === -1 &&
                    $.inArray(this.title, x2.recordModelActions) !== -1) {
                $(this).hide();
            } else if ($.inArray(this.title, x2.recordModelActions) !== -1) {
                $(this).show();
            }

            // Hides process actions if process model not passed
            if ($.inArray(trigger, x2.processModelTriggers) === -1 &&
                    $.inArray(this.title, x2.processModelActions) !== -1) {
                $(this).hide();
            } else if ($.inArray(this.title, x2.processModelActions) !== -1) {
                $(this).show();
            }
        });
    }

    /**
     * Hide actions on window load
     */
    $(window).load(function () {
        hideActions();
        var shortcodes = Object.values(x2.flow.insertableAttributes[x2.flow.modelClass]);
        for (var i in shortcodes) {
            var shortcode = shortcodes[i];
            //console.log(shortcode);
            $("#shortcodes").append('<div class="shortcode">' + shortcode + '</div>');
        }
    });

    /**
     * Hide actions on trigger change
     */
    $("#trigger-selector").change(function (e) {
        $('.x2flow-branch .x2flow-action').remove();
        $('.x2flow-branch .x2flow-node.x2flow-empty').remove();
        $('.x2flow-branch').append('<div class="x2flow-node x2flow-empty"></div>');
        hideActions();
    });

    flowEditor.init();

    if (x2.flowData !== null)
        flowEditor.loadFlow(x2.flowData);
});
