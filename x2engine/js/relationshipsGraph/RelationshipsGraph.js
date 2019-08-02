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




x2.RelationshipsGraph = (function () {

function RelationshipsGraph (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    x2.Widget.call (this, argsDict);
    var defaultArgs = {
        DEBUG: x2.DEBUG && true,
        nodes: [],
        edges: [],
        colors: {},
        nodeUidToIndex: {},
        nodeUidsToEdgeIndex: {},
        adjacencyDictionary: {},
        initialFocus: [], // type, id of node to focus on initially
        initialNeighborData: [],
        inline: null,
        translations: {}
    };
    if (typeof argsDict.DEBUG === 'undefined') argsDict.DEBUG = defaultArgs.DEBUG;
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this._initSimIters = this.DEBUG ? 1000 : 8000;
    this._svg = null;
    this._forceLayout = null;
    this._zoom = null;
    this._toolbar$ = $('#relationships-graph-toolbar');
    this._graphContainer$ = $('#relationships-graph-container');
    this._addNodeBox$ = this._toolbar$.find ('.add-node-box');
    this._getRecordDataCache = {};
    this._draggingNode = false;
    this._qtipManager = null;
    this._hoverTarget = null;
    this._labellingMode = 'active'; // | 'all'
    this._getRecordDataCache[this.initialFocus[0] + this.initialFocus[1]] = { 
        neighborData: this.initialNeighborData,
        detailView: this._toolbar$.find ('.record-detail-box').html ()
    };
    this._shiftPressed = false;
    this._SHIFTWHICH = 16; 
    this._init ();
}

RelationshipsGraph.prototype = auxlib.create (x2.Widget.prototype);

/**
 * Create node with type and id and connect it to the node with initial focus 
 */
RelationshipsGraph.prototype.connectNodeToInitialFocus = function (
    recordType, recordId, recordName) {

    var that = this;
    var newNode = { type: recordType, id: recordId, name: recordName };
    var initialFocus = { type: this.initialFocus[0], id: this.initialFocus[1] }; 
    this._addNodes ([newNode], initialFocus);
    this._clickNodeWithUid (recordType + recordId);
};

/**
 * Get label data for primary active node and neighbors and detail view for primary active node
 */
RelationshipsGraph.prototype._getRecordData = function (type, id, callback, error) {
    // first check the cache
    if (this._getRecordDataCache[type + id]) {
        callback (this._getRecordDataCache[type + id]);
        return;
    }
    var that = this;

    $.ajax ({
        url: yii.scriptUrl + '/relationships/getRecordData',
        data: { 
            recordType: type,
            recordId: id,
        }, 
        dataType: 'json',
        success: function (data) {
            that._getRecordDataCache[type + id] = data;
            callback (data);
        },
        error: function () {
            error ();
        }
    });
};

/**
 * Reposition node's label 
 */
RelationshipsGraph.prototype._positionLabel = function (node) {
    node = d3.select (node);
    var translationVec = this._zoom.translate ();
    var nodeX = translationVec[0] + node.attr ('cx') * this._zoom.scale ();
    var nodeY = translationVec[1] + node.attr ('cy') * this._zoom.scale ();
    return 'translate(' + Math.floor (nodeX) + ',' + Math.floor (nodeY) + ')';

};

RelationshipsGraph.prototype._removeNodeLabel = function (node) {
    var uid = node.type + node.id;
    $('#' + uid).remove ();
};

/**
 * Adds label to node
 * @param object node
 * @param bool me
 * @param bool force
 */
RelationshipsGraph.prototype._addNodeLabel = function (node, me, force) {
    me = typeof me === 'undefined' ? false : me; 
    force = typeof force === 'undefined' ? false : force; 
    if (!force && this._labellingMode === 'all') return;

    var that = this;
    var uid = node.type + node.id;
    var name = node.name;
    var nodeIndex = this.nodeUidToIndex[uid];
    var g = this._svg.append ('g')
        .attr ('class', 'label')
        .attr ('id', uid)
        .on ('mouseover', function () {
             d3.select (this).select ('rect').attr ('class', 'hover');
        })
        .on ('mouseout', function () {
             d3.select (this).select ('rect').attr ('class', '');
        })
        .on ('click', function () {
             var key = d3.select (this).attr ('id');
             var nodeIndex = that.nodeUidToIndex[key];
             d3.select (d3.selectAll ('.node')[0][nodeIndex])
                .each (function () {
                    that._clickNode (that.nodes[nodeIndex], this);
                })
                ;
        })
        .attr ('transform', function () {
            return that._positionLabel (d3.selectAll ('.node')[0][nodeIndex]);
        })
        ;
    if (me) {
        g.attr ('class', g.attr ('class') + ' active active-primary');
    }
    var rect = g.append ('rect')
        .attr ('width', 150)
        .attr ('height', 20)
        .attr ('stroke', this.colors[node.type])
        ;
    var text = g.append ('text')
        .text (name === '' ? '#' + node.id : name)
        .attr ('font-size', '12px')
        .attr ('transform', 'translate(3,15)')
        ;
    //if ($(text[0][0]).width () > $(rect[0][0]).width ()) {
        //TODO label truncation
    //}
    if (!this.inline || this.initialFocus[0] + this.initialFocus[1] !== uid) {
        text
            .attr ('class', 'graph-node-qtip')
            .attr ('data-type', node.type)
            .attr ('data-id', node.id)
            ;
    }
    return rect;
//    g.append ('a')
//        .attr ('xlink:href', yii.scriptUrl + '/' +
//            neighborData[uid].type.toLowerCase () + '/' + neighborData[uid].id)
//            .append ('text')
//        .text ('View record')
//        ;
};

/**
 * Add labels for all nodes with data in neighborData 
 * @param object node
 * @param object neighborData
 * @param bool clickShift whether or not click+shift is currently being pressed
 */
RelationshipsGraph.prototype._addNodeLabels = function (me, neighbors, clickShift) {
    var that = this;
    if (clickShift) { // clear all but active label and add label for me
        if (this._labellingMode !== 'all')
            this._svg.selectAll ('.label').filter (function () {
                return !d3.select (this).attr ('class').match (/active-primary/);
            }).remove ();
        that._addNodeLabel (me, true);
    } else { // clear all labels and add label for me and all my neighbors
        this._removeAllLabels ();
        for (var i in neighbors) {
            that._addNodeLabel (neighbors[i]);
        }
        that._addNodeLabel (me, true);
    }
    this._qtipManager.refresh ();
};

/**
 * Add a class to the specified svg element 
 */
RelationshipsGraph.prototype._addClass = function (elem, classToAdd) {
    d3.select (elem).attr ('class', d3.select (elem).attr ('class') + ' ' + classToAdd);
};

/**
 * Remove class from all elements corresponding to d3 selector 
 */
RelationshipsGraph.prototype._removeClass = function (selector, classToRemove) {
    selector.attr ('class', function () {
        var regex = new RegExp (classToRemove);
        return d3.select (this).attr ('class').replace (regex, '');
    });
};

/**
 * Clear active classes from graph elements 
 * @param bool clearPrimary If true, active class will also be removed from selected node
 */
RelationshipsGraph.prototype._clearActive = function (clearPrimary) {
    var that = this;
    clearPrimary = typeof clearPrimary === 'undefined' ? true : clearPrimary; 
    this._toolbar$.find ('.delete-edges-button').addClass ('disabled');
    that._removeClass (that._svg.selectAll ('.active'), 'active');
    if (clearPrimary) {
        that._svg.selectAll ('.active-primary-ring')
            .remove ();
            ;
        that._removeClass (that._svg.selectAll ('.active-primary'), 'active-primary');
        that._toolbar$.find ('.add-node-button').addClass ('disabled');
    }
};

/**
 * Retrieve an svg node by uid 
 */
RelationshipsGraph.prototype._getSvgNode = function (uid) {
    var nodeIndex = this.nodeUidToIndex[uid];
    return d3.selectAll ('.node')[0][nodeIndex];
};

/**
 * Retrieve an svg edge by source and target uids
 */
RelationshipsGraph.prototype._getSvgEdge = function (uidA, uidB) {
    var edgeIndex = this.nodeUidsToEdgeIndex[uidA + uidB];
    return d3.selectAll ('.edge')[0][edgeIndex];
};

/**
 * edge click event handler 
 */
RelationshipsGraph.prototype._clickEdge = function (data, elem) {
    var that = this;
    // if shift isn't pressed or previous selection wasn't an edge
    if (this._svg.selectAll ('.active-primary')[0].length || !this._shiftPressed) {
        this._clearActive ();
        this._removeAllLabels ();
    }
    this._toolbar$.find ('.delete-edges-button').removeClass ('disabled');
    this._addClass (elem, 'active');
    var nodeA = data.source; 
    var nodeB = data.target; 
    this._addNodeLabel (nodeA);
    this._addNodeLabel (nodeB);

    if (d3.event) d3.event.stopPropagation ();
};

/**
 * node click event handler 
 */
RelationshipsGraph.prototype._clickNode = function (data, elem) {
    var clickShift = this._shiftPressed;
    var that = this;
    var uid = data.type + data.id;

    // remove active class from previously active objects
    that._clearActive (!clickShift);
    this._toolbar$.find ('.add-node-button').removeClass ('disabled');
    this._addClass (elem, 'active');

    // add outer circle around clicked noded
    this._addClass (elem, 'active-primary');
    d3.select (elem.parentNode).insert ('circle', '.active-primary')
        .attr ('r', parseFloat (d3.select (elem).attr ('r')) + 8)
        .attr ('cx', d3.select (elem).attr ('cx'))
        .attr ('cy', d3.select (elem).attr ('cy'))
        .attr ('class', 'active-primary-ring')
        .attr ('fill', 'transparent')
        .attr ('stroke', 'black')
        .attr ('stroke-width', '2px')
        ;

    if (!clickShift) {
        // add active class to all of clicked node's edges and all adjacent nodes
        for (var neighborUid in this.adjacencyDictionary[uid]) {
            this._addClass (this._getSvgEdge (uid, neighborUid), 'active');
            this._addClass (this._getSvgNode (neighborUid), 'active');
        }
    }

    if (this._svg.selectAll ('circle.active-primary')[0].length > 1 &&
        this._svg.selectAll ('circle.active-primary')[0].length < 5) {

        this._toolbar$.find ('.connect-nodes-button').removeClass ('disabled');
    } else {
        this._toolbar$.find ('.connect-nodes-button').addClass ('disabled');
    }

    // fetch record data
    if (!this.inline) {
        var throbber$ = auxlib.containerLoading (this._toolbar$.find ('.record-detail-box'));
    }
    this._addNodeLabels (data, this._getNodeNeighbors (data), clickShift);

    this._getRecordData (data.type, data.id, function (response) {

        // display detail view and add labels
        var detailView = response.detailView;
        if (detailView)
            that._toolbar$.find ('.record-detail-box').html (detailView);
        if (that.inline) {
            that._addNodes (response.neighborData, data); 
            that._addNodeLabels (data, that._getNodeNeighbors (data), clickShift);
        }
        if (!that.inline) throbber$.remove ();
    }, function () {
        if (!that.inline) throbber$.remove ();
    });
    if (d3.event) d3.event.stopPropagation ();
};

RelationshipsGraph.prototype._getNodeNeighbors = function (node) {
    var uid = node.type + node.id;
    var neighbors = [];
    var index;
    for (var neighborUid in this.adjacencyDictionary[uid]) {
        index = this.nodeUidToIndex[neighborUid];
        neighbors.push (this.nodes[index]);
    }
    return neighbors;
};

/**
 * Gather and save data about the graph 
 */
RelationshipsGraph.prototype._gatherMetaData = function () {

    // calculate degree of each node and max degree
    var max = -Infinity;
    this._degrees = {};
    for (var i in this.adjacencyDictionary) {
        var keys = auxlib.keys (this.adjacencyDictionary[i]);
        this._degrees[i] = keys.length;
        if (this._degrees[i] > max) {
            max = this._degrees[i];
        }
    }
    max = Math.max (max, 0);
    this._maxDegree = max;

};

/**
 * associate node and edge data with svg elements 
 */
RelationshipsGraph.prototype._refreshGraphEntities = function () {
    var that = this;
    this._edgeContainer.selectAll ('.edge')
            .data (this.edges)
        .enter ().append ('g').append ('line')
            .attr ('class', 'edge')
            .attr ('stroke', 'rgb(173, 173, 173)')
            .attr ('stroke-width', '2px')
        ;
    // add invisible sibling edges, used to increase edge click target size
    this._gGraphContainer.selectAll ('.edge')
        .each (function () {
            d3.select (this.parentNode)
                    .append ('line')
                .attr ('stroke', 'transparent')
                .attr ('stroke-width', '5px')
                .on ('click', function () {
                     var data = d3.select (this.previousSibling).data ()[0];
                     that._clickEdge (data, this.previousSibling);
                })
                ;
        })
        ;

    this._nodeContainer.selectAll ('.node')
            .data (this.nodes)
        .enter ().append ('g').append ('circle')
            .attr ('class', 'node')
            // mouse event handlers which show node label on hover and remove the node label only
            // when mouse moves out of node, label rectangle, label text, and qtip.
            .on ('mouseover', function (d) {
                if (that._labellingMode === 'all') return;
                if ($('#' + d.type + d.id).length) return; // node already labelled
                var label = that._addNodeLabel (d);
                that._qtipManager.refresh ();
            })
            .on ('mouseout', function (d) {
                if (that._labellingMode === 'all') return;
                if (d3.select (this).attr ('class').match (/active/)) return;

                var node = this;
                (function () {
                    var interval = window.setInterval (function () {
                        if ((!$(that._hoverTarget).closest ('g').length || 
                            $(that._hoverTarget).closest ('g').attr ('id') !== d.type + d.id) &&
                            !$(that._hoverTarget).is (node) &&
                            !$(that._hoverTarget).closest ('.x2-qtip').length) {

                            if (!d3.select (node).attr ('class').match (/active/)) 
                                that._removeNodeLabel (d);
                            window.clearInterval (interval);
                        }
                    }, 200);
                }) ();
                return;
            })
            .attr ('r', function (d) {
                if (that._maxDegree)
                    return 7 + 5 * (that._degrees[d.type + d.id] / that._maxDegree);
                else 
                    return 7;
            })
            .attr ('fill', function (d) {
                return that.colors[d.type];
            })
            .attr ('stroke', 'whitesmoke')
            .attr ('stroke-width', '2px')
            .on ('mousedown.dragstart', function() { 
                 that._draggingNode = true;  
            })
            .on ('click', function (d) { that._clickNode (d, this); })
            ;
    this.svgNodes = this._svg.selectAll ('.node');
    this.svgEdges = this._svg.selectAll ('.edge');
};

/**
 * Initialize positions of nodes 
 */
RelationshipsGraph.prototype._initDataPositions = function () {
    var width = this._graphContainer$.width ();
    var nodeCount = this.nodes.length;
    for (var i in this.nodes) {
        var node = this.nodes[i];
        node.x = node.y = (width / nodeCount) * i;
    }
};

/**
 * Initialize force layout, set up zoom behavior, add graph svg elements, start the simulation,
 * and more
 */
RelationshipsGraph.prototype._buildGraph = function () {
    var that = this;
    this._svg = d3.select ('#content .relationships-graph');

    // append inner container used for panning and zooming
    this._gGraphContainer = this._svg.append ('g').attr ('class', 'g-graph-container');
    this._edgeContainer = this._gGraphContainer.append ('g');
    this._nodeContainer = this._gGraphContainer.append ('g');

    // set up initial layout parameters
    this._forceLayout = d3.layout.force ()
        .nodes (this.nodes)
        .links (this.edges)
        .linkStrength (function (link) {
            //if (link.target.type === '__label') return 1;
            return 0.7;
        })
        //.friction(0.9)
        .distance (function (link) {
            // link distance relative to average degree of nodes
            return 60 + 220 * (
                ((that._degrees[link.source.type + link.source.id] +
                  that._degrees[link.target.type + link.target.id]) / 2) / that._maxDegree);
        })
        .charge (function (d) {
            // charge relative to node degree
            return -330 - 590 * (that._degrees[d.type + d.id] / that._maxDegree); 
        })
//        .gravity(0.1)
        //.theta(0.1)
        //.alpha(0.1)
        .size ([this._graphContainer$.width (), this._graphContainer$.height ()])
        .start ();

    //this._initDataPositions ();
    that._refreshGraphEntities ();

    this._gGraphContainer
        .on ('mouseup.dragend', function() { 
             that._draggingNode = false; 
        })
        ;

    // skip over the initial animation
    var ticks = this._initSimIters;
    for (var i = 0; i < ticks; i++) {
        that._forceLayout.tick (); 
    }
    that._refreshGraphEntityPositions ();
    that._forceLayout.stop ();

    // set up force layout animation
    this._forceLayout.on ('tick', function () {
        that._refreshGraphEntityPositions ();
    });

    // set up zooming and panning
    var ignoreClick = false; // used to prevent panning from triggering a click event
    this._prevZoomTranslate = null;
    this._zoom = d3.behavior.zoom ()
        .on ('zoomstart', function () {
             //console.log ('zoomstart');
        })
        .on ('zoomend', function () {
             //console.log ('zoomend');
        })
        .on ('zoom', function () {
            ignoreClick = true;
            if (!that._draggingNode) {
                that._positionGraphContainer ();
            } else if (that._prevZoomTranslate) { // cancel zoom, restore previous transform
                that._zoom.translate (that._prevZoomTranslate);
            }
            that._prevZoomTranslate = that._zoom.translate ();
        })
        ;
    this._zoom (that._svg);

    // deselect nodes when negative space is clicked
    this._svg.on ('click', function () {
        if (ignoreClick) {
            ignoreClick = false;
            return;
        }
        that._clearActive ();
        that._removeAllLabels ();
    });

    // center graph container on node given initial focus
    this._centerNode (this.initialFocus[0] + this.initialFocus[1]);
};

/**
 * Update position of svg objects using positions of d3 node and edge data
 */
RelationshipsGraph.prototype._refreshGraphEntityPositions = function () {
    var that = this;
    that.svgEdges
        .attr ('x1', function (d) { return d.source.x; })
        .attr ('y1', function (d) { return d.source.y; })
        .attr ('x2', function (d) { return d.target.x; })
        .attr ('y2', function (d) { return d.target.y; })
        .each (function () {
            d3.select (this.nextSibling)
                .attr ('x1', d3.select (this).attr ('x1'))
                .attr ('y1', d3.select (this).attr ('y1'))
                .attr ('x2', d3.select (this).attr ('x2'))
                .attr ('y2', d3.select (this).attr ('y2'))
            ;
        })
        ;
    that.svgNodes
        .attr ('cx', function (d) { return d.x; })
        .attr ('cy', function (d) { return d.y; })
        .each (function (d) {
            // reposition label
            d3.select ('#' + d.type + d.id)
                .attr ('transform', (function (node) {
                    return function () {
                        return that._positionLabel (node);
                    };
                }) (this))
            ;
        });
    ;
    that._svg.selectAll ('circle.active-primary')
        .each (function () {
            d3.select (this.previousSibling)
                .attr ('cx', d3.select (this).attr ('cx'))
                .attr ('cy', d3.select (this).attr ('cy'))
            ;
        })
        ;
};

/**
 * Reposition graph using zoom behavior object
 */
RelationshipsGraph.prototype._positionGraphContainer = function () {
    var that = this;
    // scale and pan graph container
    that._gGraphContainer
        .attr (
            'transform', 
            'translate(' + that._zoom.translate () + ') ' +
            'scale(' + that._zoom.scale () + ')'
        )
        ;
    // reposition labels
    that._svg.selectAll ('.label')
        .attr ('transform', function () {
            var key = d3.select (this).attr ('id');
            var nodeIndex = that.nodeUidToIndex[key];
            var node = d3.selectAll ('.node')[0][nodeIndex];
            return that._positionLabel (node);
        })
        ;
};

/**
 * Centers graph around node and zooms in
 */
RelationshipsGraph.prototype._centerNode = function (uid) {
    var node = d3.select (this._getSvgNode (uid));
    if (node.empty ()) return;
    this._zoom.scale (this.inline ? 1 : 1.5);
    this._zoom.translate ([
        -(this._zoom.scale () * parseFloat (node.attr ('cx'))) + 
        this._graphContainer$.width () / 2, 
        -(this._zoom.scale () * parseFloat (node.attr ('cy'))) + 
            this._graphContainer$.height () / 2
    ]);
    this._positionGraphContainer ();
};

/**
 * Adds a new node to the graph 
 */
RelationshipsGraph.prototype._createNode = function (node) {
    var uid = node.type + node.id;
    this.nodes.push ({
        type: node.type,  
        id: node.id, 
        name: node.name
    });
    this.nodeUidToIndex[uid] = this.nodes.length - 1;
    this.adjacencyDictionary[uid] = [];
    //return this._getSvgNode (uid);
};

/**
 * Adds new nodes to the graph, each connected to the source node 
 */
RelationshipsGraph.prototype._addNodes = function (nodes, sourceNode) {
    var node, 
        uid,
        sourceUid = sourceNode.type + sourceNode.id,
        edges = [],
        targetIndex
        ;

    for (var i in nodes) {
        node = nodes[i];
        uid = node.type + node.id;
        if (typeof this.nodeUidToIndex[uid] !== 'undefined') continue;
        targetIndex = this.nodes.length;
        edges.push ({
            source: this.nodeUidToIndex[sourceUid],
            target: targetIndex
        });
        this._createNode (node);
    }

    if (edges.length) this._addEdges (edges);
};

RelationshipsGraph.prototype._addEdges = function (edges) {
    for (var i in edges) {
        var edge = edges[i];

        // update lookup tables
        var sourceUid = this.nodes[edge.source].type + this.nodes[edge.source].id;
        var targetUid = this.nodes[edge.target].type + this.nodes[edge.target].id;
        this.adjacencyDictionary[sourceUid][targetUid] = true;
        this.adjacencyDictionary[targetUid][sourceUid] = true;
        this.edges.push (edge);
        this.nodeUidsToEdgeIndex[sourceUid + targetUid] = this.edges.length - 1;
        this.nodeUidsToEdgeIndex[targetUid + sourceUid] = this.edges.length - 1;

        // invalidate neighbor data cache
        delete this._getRecordDataCache[targetUid];
        delete this._getRecordDataCache[sourceUid];
    }
    this._gatherMetaData (); // refresh meta data
    this._refreshGraphEntities ();
    this._start ();
};

RelationshipsGraph.prototype._deleteEdges = function (edgeData, edges) {
    var data = [];
    var that = this;
    for (var i in edgeData) {
        data.push ([
            [edgeData[i].source.type, edgeData[i].source.id],
            [edgeData[i].target.type, edgeData[i].target.id]
        ]);
    }
    $.ajax ({  
        url: yii.scriptUrl + '/relationships/deleteEdges',
        data: { edgeData: data },
        success: function (data) {
            if (data === 'success') {
                edges.remove ();
                that._clearActive ();
                that._removeAllLabels ();
                // update lookup tables
                for (var i in edgeData) {
                    var uidA = edgeData[i].source.type + edgeData[i].source.id;
                    var uidB = edgeData[i].target.type + edgeData[i].target.id;
                    var edgeIndex = that.nodeUidsToEdgeIndex[uidA + uidB];

                    // replace edge with dummy edge (instead of deleting it) so that we don't need 
                    // to update the edge index
                    that.edges[edgeIndex] = { source: {}, target: {} };

                    delete that.nodeUidsToEdgeIndex[uidA + uidB];
                    delete that.nodeUidsToEdgeIndex[uidB + uidA];
                    delete that.adjacencyDictionary[uidB][uidA];
                    delete that.adjacencyDictionary[uidA][uidB];

                    // invalidate neighbor data cache
                    delete that._getRecordDataCache[uidA];
                    delete that._getRecordDataCache[uidB];
                }
                that._refreshGraphEntities ();
            }
        }
    })
};

RelationshipsGraph.prototype._translateNodeUidsToIndices = function (edges) {
    var that = this;
    // translate node uids to indices
    for (var i in edges) { 
        var edge = edges[i];
        edge.source = that.nodeUidToIndex[edge.source];
        edge.target = that.nodeUidToIndex[edge.target];
    }
};

/**
 * Completes subgraph consisting of specified nodes
 */
RelationshipsGraph.prototype._connectNodes = function (nodes) {
    var data = [];
    var that = this;
    for (var i in nodes) {
        data.push ([nodes[i].type, nodes[i].id]);
    }
    $.ajax ({  
        url: yii.scriptUrl + '/relationships/connectNodes',
        data: { recordInfo: data },
        dataType: 'json',
        success: function (edges) {
            that._translateNodeUidsToIndices (edges);
            that._addEdges (edges);
        }
    })
};

RelationshipsGraph.prototype._toggleSimulationStartStopButton = function (showStart) {
    showStart = typeof showStart === 'undefined' ? null : showStart; 
    if ((showStart !== null && !showStart) || 
        (showStart === null &&
         this._toolbar$.find ('.start-animation-button').is (':visible'))) {

        this._toolbar$.find ('.start-animation-button').hide ();
        this._toolbar$.find ('.stop-animation-button').show ();
    } else if ((showStart !== null && showStart) || 
        (showStart === null &&
        !this._toolbar$.find ('.start-animation-button').is (':visible'))) {

        this._toolbar$.find ('.start-animation-button').show ();
        this._toolbar$.find ('.stop-animation-button').hide ();
    }
};

RelationshipsGraph.prototype._start = function () {
    var that = this;
    that._forceLayout.start ();
    that._svg.selectAll ('.node')
        .call (that._forceLayout.drag)
        ;
    that._toggleSimulationStartStopButton (false);
};

RelationshipsGraph.prototype._clickNodeWithUid = function (uid) {
    var that = this;
    var nodeIndex = that.nodeUidToIndex[uid];
    d3.select (d3.selectAll ('.node')[0][nodeIndex]).each (function (d) {
        that._clickNode (d, this);
    });
};

/**
 * Adds a node to the graph, creating a new edge between it and all specified nodes 
 */
RelationshipsGraph.prototype._addNode = function (recordType, recordId, recordName, nodes) {
    var that = this;
    var data = {};
    var otherRecordInfo = [];
    var that = this;
    for (var i in nodes) {
        otherRecordInfo.push ([nodes[i].type, nodes[i].id]);
    }
    data.recordType = recordType;
    data.recordId = recordId;
    data.otherRecordInfo = otherRecordInfo;
    $.ajax ({  
        url: yii.scriptUrl + '/relationships/addNode',
        data: data,
        dataType: 'json',
        success: function (edges) {
            that._createNode ({ type: recordType, id: recordId, name: recordName });
            that._translateNodeUidsToIndices (edges);
            that._addEdges (edges);
            that._clickNodeWithUid (recordType + recordId);
        }
    })
};

RelationshipsGraph.prototype._addLabelsFromNodeData = function (nodes) {
    var node,
        uid,
        neighborData
        ;
    for (var i in nodes) {
        node = nodes[i];
        uid = node.type + node.id;
        this._addNodeLabel (node, false, true);
    }
};

RelationshipsGraph.prototype._labelAll = function () {
    this._removeAllLabels (true);
    this._addLabelsFromNodeData (this.nodes);
    this._qtipManager.refresh ();
};

RelationshipsGraph.prototype._removeAllLabels = function (force) {
    force = typeof force === 'undefined' ? false : force; 
    if (!force && this._labellingMode === 'all') return;
    this._svg.selectAll ('.label').remove ();
};

RelationshipsGraph.prototype._labelActive = function () {
    this._removeAllLabels ();
    var activeNodes = this._svg.selectAll ('circle.active').data ()
    this._addLabelsFromNodeData (activeNodes);
};

RelationshipsGraph.prototype._setUpToolbar = function () {
    var that = this;
    this._toolbar$.find ('.stop-animation-button').click (function () {
        that._forceLayout.stop ();
        // remove dragging event listeners
        that._svg.selectAll ('.node')
            .call (function () {
                this.on ('mousedown.drag', null).on ('touchstart.drag', null) 
            })
            ;
        that._toggleSimulationStartStopButton (true);
    });

    this._toolbar$.find ('.start-animation-button').click (function () {
        that._start ();
    });

    this._toolbar$.find ('.label-all-button').click (function () {
        that._removeClass (that._svg, 'hide-labels');
        $(this).hide ();
        $(this).next ().show ();
        that._labellingMode = 'all';
        that._labelAll ();
    });
    this._toolbar$.find ('.label-active-button').click (function () {
        that._removeClass (that._svg, 'hide-labels');
        $(this).hide ();
        $(this).prev ().show ();
        that._labellingMode = 'active';
        that._labelActive ();
    });

    this._toolbar$.find ('.show-labels-button').click (function () {
        that._removeClass (that._svg, 'hide-labels');
        $(this).hide ();
        $(this).next ().show ();
    });
    this._toolbar$.find ('.hide-labels-button').click (function () {
        that._addClass (that._svg[0][0], 'hide-labels');
        $(this).hide ();
        $(this).prev ().show ();
    });

    this._toolbar$.find ('.add-node-button').click (function () {
        if ($(this).hasClass ('disabled')) return;

        that._addNodeBox$.find ('.record-name-autocomplete').removeClass ('error');
        var error = false;
        // validate add node form
        if (that._addNodeBox$.find ('.hidden-id').val () === '') {
            that._addNodeBox$.find ('.record-name-autocomplete').addClass ('error');
            error = true;
        } else {
            var activeNodes = that._svg.selectAll ('circle.active-primary').data ()
            var recordType = that._addNodeBox$.find ('.type-select').val ();
            var recordId = that._addNodeBox$.find ('.hidden-id').val ();
            var recordName = that._addNodeBox$.find ('.record-name-autocomplete').val ();
            if (typeof that.nodeUidToIndex[recordType + recordId] !== 'undefined') {
                that._addNodeBox$.find ('.record-name-autocomplete').addClass ('error');
                error = true;
                x2.topFlashes.displayFlash (that.translations.duplicateRecordError, 'error');
            } else {
                that._addNode (recordType, recordId, recordName, activeNodes);
                that._addNodeBox$.find ('.record-name-autocomplete').val ('');
            }
        }

        if (error)
            auxlib.onClickOutside (that._addNodeBox$, function () {
                that._addNodeBox$.find ('.record-name-autocomplete').removeClass ('error');
            }, true);
    });

    this._toolbar$.find ('.connect-nodes-button').click (function () {
        if (!$(this).hasClass ('disabled')) {
            var activeNodes = that._svg.selectAll ('circle.active-primary').data ()
            that._connectNodes (activeNodes);
        }
    });

    this._toolbar$.find ('.delete-edges-button').click (function () {
        if (!$(this).hasClass ('disabled')) {
            var activeEdges = that._svg.selectAll ('.edge.active');
            var edgeData = activeEdges.data ();
            that._deleteEdges (edgeData, activeEdges);
        }
    });

    this._toolbar$.find ('.hints-close-button').click (function () {
        $(this).closest ('.graph-hints-box').hide ();
        $('#hints-show-button').show ();
    });

    $('#hints-show-button').click (function () {
        that._toolbar$.find ('.graph-hints-box').show ();
        $('#hints-show-button').hide ();
    });

};

RelationshipsGraph.prototype._adjustZoom = function (x, y, scale) {
    var that = this;
    var translate = that._zoom.translate ();
    translate[0] += x;
    translate[1] += y;
    that._zoom.scale (that._zoom.scale () + scale);
    that._zoom.translate (translate);
    that._positionGraphContainer ();
};

RelationshipsGraph.prototype._setUpNavControls = function () {
    var that = this;
    var movementDelta = 30;
    var mouseDown = false;

    function untilMouseup (fn) {
        mouseDown = true;
        var interval = setInterval (function () {
            if (mouseDown)
                fn ();
            else
                clearInterval (interval); 
        }, 100);
    }

    this._toolbar$.find ('.pan-up-button').mousedown (function () {
        untilMouseup (function () { that._adjustZoom (0, movementDelta, 0); });
    });
    this._toolbar$.find ('.pan-right-button').mousedown (function () {
        untilMouseup (function () { that._adjustZoom (-movementDelta, 0, 0); });
    });
    this._toolbar$.find ('.pan-down-button').mousedown (function () {
        untilMouseup (function () { that._adjustZoom (0, -movementDelta, 0); });
    });
    this._toolbar$.find ('.pan-left-button').mousedown (function () {
        untilMouseup (function () { that._adjustZoom (movementDelta, 0, 0); });
    });
    $(document).on ('mouseup._setUpNavControls', function () {
        mouseDown = false;
    });
    this._toolbar$.find ('.zoom-in-button').click (function () {
        that._adjustZoom (0, 0, 0.1);
        return false;
    });
    this._toolbar$.find ('.zoom-out-button').click (function () {
        that._adjustZoom (0, 0, -0.1);
        return false;
    });
};

RelationshipsGraph.prototype._provideInitialFocus = function () {
    var that = this;
    // look up node index by uid, locate the node, and then trigger the click handler
    var nodeUid = this.initialFocus[0] + this.initialFocus[1];
    var nodeIndex = this.nodeUidToIndex[nodeUid];
    d3.select (d3.selectAll ('.node')[0][nodeIndex]).each (function (d) {
        that._clickNode (d, this);
    });
};

RelationshipsGraph.prototype._setUpQtips = function () {
    this._qtipManager = new x2.RelationshipsGraphQtipManager ({
        qtipSelector: '.graph-node-qtip'
    });
};

RelationshipsGraph.prototype._setUpWorkspaceResizing = function () {
    var that = this;
    $(window).resize (function () {
        var height = $(window).height () - that._graphContainer$.offset ().top - 100;
        that._graphContainer$.height (height);
        that._toolbar$.height (height - 15);
    }).resize ();
};

RelationshipsGraph.prototype._init = function () {
    this._setUpQtips ();
    this._gatherMetaData ();
    this._buildGraph (); 
    if (!this.inline)
        this._setUpToolbar ();
    this._setUpNavControls ();
    this._provideInitialFocus ();
    if (!this.inline) this._setUpWorkspaceResizing ();
    var that = this;

    // set and unset shift property
    $(document).unbind ('keydown._setUpToolbar');
    $(document).on ('keydown._setUpToolbar', function (evt) {
        if (evt.which === that._SHIFTWHICH) that._shiftPressed = true;
    });
    $(document).unbind ('keyup._setUpToolbar');
    $(document).on ('keyup._setUpToolbar', function (evt) {
        if (evt.which === that._SHIFTWHICH) that._shiftPressed = false;
    });

    $(window).mouseover (function (evt) {
        that._hoverTarget = evt.target;
    });

    //window.setTimeout (function () { 
        //that._toolbar$.find ('.stop-animation-button').click (); 
    //}, this._initSimulationTime);
};

return RelationshipsGraph;

}) ();
