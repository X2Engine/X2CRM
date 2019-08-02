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

x2.InlineRelationshipsWidget = (function () {

function InlineRelationshipsWidget (argsDict) {
    var defaultArgs = {
        hideFullHeader: true,
        DEBUG: x2.DEBUG && false,
        recordId: null,
        recordType: null,
        displayMode: null,
        height: null,
        ajaxGetModelAutocompleteUrl: '',
        defaultsByRelatedModelType: {}, // {<model type>: <dictionary of default attr values>}
        createUrls: {}, // {<model type>: <string>}
        dialogTitles: {}, // {<model type>: <string>}
        tooltips: {}, // {<model type>: <string>}
        hasUpdatePermissions: null,
        createRelationshipUrl: null,

        // used to determine which models the quick create button is displayed for
        modelsWhichSupportQuickCreate: []
    };
    this._relationshipsGridContainer$ = $('#relationships-form');
     
    this._relationshipsGraph = null;
    this._inlineGraphContainer$ = $('#inline-relationships-graph-container');
    this._inlineGraphViewButton$ = $('#inline-graph-view-button');
     
    this._gridViewButton$ = $('#rel-grid-view-button');
    this._form$ = $('#new-relationship-form');
    this._relationshipManager = null;

    auxlib.applyArgs (this, defaultArgs, argsDict);

    GridViewWidget.call (this, argsDict);
}

InlineRelationshipsWidget.prototype = auxlib.create (GridViewWidget.prototype);

/**
 * Set up quick create button for given model class
 * @param string modelType 
 */
InlineRelationshipsWidget.prototype.initQuickCreateButton = function (modelType) {
    var that = this;
    if (this._relationshipManager && 
        this._relationshipManager instanceof x2.RelationshipsManager) {

        this._relationshipManager.destructor ();
    }

    if ($.inArray (modelType, this.modelsWhichSupportQuickCreate) > -1) {
        $('#quick-create-record').css ('visibility', 'visible');
    } else {
        $('#quick-create-record').css ('visibility', 'hidden');
        return;
    }

    this._relationshipManager = new x2.RelationshipsManager ({
        element: $('#quick-create-record'),
        modelType: this.recordType,
        modelId: this.recordId,
        relatedModelType: modelType,
        createRecordUrl: this.createUrls[modelType],
        attributeDefaults: this.defaultsByRelatedModelType[modelType] || {},
        dialogTitle: this.dialogTitles[modelType],
        tooltip: this.tooltips[modelType],
        afterCreate: function (attributes) {
            $.fn.yiiGridView.update('relationships-grid');
            if (that._graphLoaded ()) {
                that._relationshipsGraph.connectNodeToInitialFocus (
                    modelType, attributes.id, 
                    typeof attributes.name === 'undefined' ? attributes.id : attributes.name);
            }
        }
    });

};

/**
 * Requests a new autocomplete widget for the specified model class, replacing the current one
 * @param string modelType
 */
InlineRelationshipsWidget.prototype._changeAutoComplete = function (modelType) {
    x2.forms.inputLoading ($('#inline-relationships-autocomplete-container'));
    $.ajax ({
        type: 'GET',
        url: this.ajaxGetModelAutocompleteUrl,
        data: {
            modelType: modelType
        },
        success: function (data) {
            // remove span element used by jQuery widget
            $('#inline-relationships-autocomplete-container input').
                first ().next ('span').remove ();
            // replace old autocomplete with the new one
            $('#inline-relationships-autocomplete-container input').first ().replaceWith (data); 
            $('#inline-relationships-autocomplete-container').find ('script').remove ();
 
            // remove the loading gif
            x2.forms.inputLoadingStop ($('#inline-relationships-autocomplete-container'));
        }
    });
};

/**
 * submits relationship create form via AJAX, performs validation 
 */
InlineRelationshipsWidget.prototype._submitCreateRelationshipForm = function () {
    var that = this; 
    $('.record-name-autocomplete').removeClass ('error');
    var error = false;

    if ($('#RelationshipModelId').val() === '') {
        that.DEBUG && console.log ('model id is not set');
        error = true;
    } else if (isNaN (parseInt($('#RelationshipModelId').val(), 10))) {
        that.DEBUG && console.log ('model id is NaN');
        error = true;
    } else if($('.record-name-autocomplete').val() === '') {
        that.DEBUG && console.log ('second name autocomplete is not set');
        error = true;
    }
    if (error) {
        $('.record-name-autocomplete').addClass ('error');
        return false;
    }
    that._form$.slideUp (200);

    var recordId = $('#RelationshipModelId').val ();
    var recordType = $('#relationship-type').val ();
    var recordName = that._form$.find ('.record-name-autocomplete').val ();

    $.ajax ({
        url: this.createRelationshipUrl,
        type: 'POST', 
        data: $('#new-relationship-form').serialize (),
        success: function (data) {
            if(data === 'duplicate') {
                alert('Relationship already exists.');
            } else if(data === 'success') {
                $.fn.yiiGridView.update('relationships-grid');
                var count = parseInt ($('#relationship-count').html (), 10);
                $('#relationship-count').html (count + 1);
                that._form$.find ('.record-name-autocomplete').val ('');
                $('#RelationshipModelId').val('');
                $('#firstLabel').val('');
                $('#secondLabel').val('');
                 
                if (that._graphLoaded ()) {
                    that._relationshipsGraph.connectNodeToInitialFocus (
                        recordType, recordId, recordName);
                }
                 
            }
        }
    });
};

/**
 * Sets up create form submission button behavior 
 */
InlineRelationshipsWidget.prototype._setUpCreateFormSubmission = function () {
    var that = this;

    $('#add-relationship-button').on('click', function () {
        that._submitCreateRelationshipForm ();
        return false;
    });
};


InlineRelationshipsWidget.prototype._changeMode = function (mode) {
    var form$ = $('#relationships-form');
    if (mode === 'simple') {
        form$.addClass ('simple-mode');
        form$.removeClass ('full-mode');
    } else {
        form$.removeClass ('simple-mode');
        form$.addClass ('full-mode');
    }
};

InlineRelationshipsWidget.prototype._setUpModeSelection = function () {
    var that = this;
    this.element.find ('a.simple-mode, a.full-mode').click (function () {
        if ($(this).hasClass ('disabled-link')) return false;
        var newMode = $(this).hasClass ('simple-mode') ? 'simple' : 'full';
        that.setProperty ('mode', newMode);
        $(this).siblings ().removeClass ('disabled-link');
        $(this).addClass ('disabled-link');
        that._changeMode (newMode);
        return false;
    });
};


InlineRelationshipsWidget.prototype._displayInlineGraph = function () {
    this._inlineGraphContainer$.show ();
    this._relationshipsGridContainer$.hide ();
    this._inlineGraphViewButton$.hide ();
    this._gridViewButton$.show ();
    this.element.find ('.ui-resizable-handle').show ();
    this.setProperty ('displayMode', 'graph');
    this.displayMode = 'graph';
    this._setUpResizeBehavior ();
};


InlineRelationshipsWidget.prototype._displayGrid = function () {
     
    this._inlineGraphContainer$.hide ();
     
    this._relationshipsGridContainer$.show ();
     
    this._inlineGraphViewButton$.show ();
     
    this._gridViewButton$.hide ();
    this.element.find ('.ui-resizable-handle').hide ();
    $(this.contentContainer).attr ('style', '');
    this.setProperty ('displayMode', 'grid');
    this.displayMode = 'grid';
};


InlineRelationshipsWidget.prototype._graphLoaded = function () {
    if ($.trim (this._inlineGraphContainer$.html ()) !== '') {
        this._relationshipsGraph = x2.relationshipsGraph;
        return true;
    }
    return false;
};



InlineRelationshipsWidget.prototype._getInlineGraph = function () {
    if (this._graphLoaded ()) {
        this._displayInlineGraph ();
        return;
    }
    var that = this;
    $.ajax ({
        url: yii.scriptUrl + '/relationships/viewInlineGraph',
        data: {
            recordId: this.recordId,
            recordType: this.recordType,
            height: that.height
        },
        success: function (data) {
            that._inlineGraphContainer$.html (data);
            that._relationshipsGraph = x2.relationshipsGraph;
            that._displayInlineGraph ();
        }
    });
};



InlineRelationshipsWidget.prototype._setUpGraphViewButton = function () {
    var that = this;
    this._inlineGraphViewButton$.click (function () {
        that._getInlineGraph ();
    });
    this._gridViewButton$.click (function () {
        that._displayGrid ();
    });
};


InlineRelationshipsWidget.prototype._afterStop = function () {
    var that = this; 
    var savedHeight = that.element.height ();
    if (this._form$.is (':visible'))
        savedHeight -= this._form$.height () + 12;
    that.setProperty ('height', savedHeight);
};


InlineRelationshipsWidget.prototype._resizeEvent = function () {
    var that = this;
    if (that.displayMode === 'graph') {
        var newHeight = $(this.contentContainer).height ();
        if (this._form$.is (':visible'))
            newHeight -= this._form$.height () + 12;
        $('#relationships-graph-container').height (newHeight);
    }
};


InlineRelationshipsWidget.prototype._setUpNewRelationshipsForm = function () {
    var that = this;
    $('#relationship-type').change (function () {
        that.initQuickCreateButton ($(this).val ()); 
        that._changeAutoComplete ($(this).val ());
    }).change ();
    
    $('#secondLabel').hide();
    $('#myName').hide();
    $('#RelationshipLabelButton').bind('click', function(){
        $('#RelationshipLabelButton').toggleClass('fa fa-long-arrow-right');
        $('#RelationshipLabelButton').toggleClass('fa fa-long-arrow-left');
        $('#myName').toggle(200);
        $('#secondLabel').toggle( 200);
        var val = $('#mutual').val();
        val = (val == 'true') ? 'false' : 'true';

        $('#mutual').val(val);
    });

    $('#new-relationship-button').click (function () {
        if (that._form$.is (':visible')) {
            that._form$.slideUp (200);
        } else {
            that.contentContainer.attr ('style', '');
            that._form$.slideDown (200);
        }
    });

    this._setUpCreateFormSubmission ();
};


/**
 * Sets up widget resize behavior 
 */
InlineRelationshipsWidget.prototype._setUpResizeBehavior = function () {
    if (this._setUpResizeBehavior.setUp) return;
    this.resizeHandle = $('#relationships-graph-resize-handle');
    if (!this.resizeHandle.length) return;

    this._setUpResizeBehavior.setUp = true;
    var that = this; 
    this.resizeHandle.addClass ('ui-resizable-handle');
    this.resizeHandle.addClass ('ui-resizable-s');
    $(this.contentContainer).resizable ({
        handles: {
            s: $('#relationships-graph-resize-handle')
        },
        minHeight: 50,
        start: function () {
            $('body').attr ('style', 'cursor: se-resize');
        },
        stop: function () {
            that._afterStop ();
            $('body').attr ('style', '');
        },
        resize: function () { that._resizeEvent (); }
    });
};


InlineRelationshipsWidget.prototype._setUpInlineViewButtons = function () {
    var that = this;
    this.element.find ('tr').mouseover (function () {
        $(this).find ('.fa-eye').show (); 
    });
    this.element.find ('tr').mouseout (function () {
        $(this).find ('.fa-eye').hide (); 
    });
};

InlineRelationshipsWidget.prototype.afterRefresh = function () {
    x2.QuickRead.instantiateQuickReadLinks (this.element);
};

InlineRelationshipsWidget.prototype._setUpDetailViewToggle = function () {
    var that = this;
    var toggle$ = this.element.find ('.expand-detail-views');
    toggle$.unbind ('click._setUpDetailViewToggle').
        bind ('click._setUpDetailViewToggle', function () {
            that.element.find ('.detail-view-toggle .fa:visible').click ();  
        });
};


InlineRelationshipsWidget.prototype._init = function () {
    GridViewWidget.prototype._init.call (this);
    if (this.displayMode === 'grid') this.element.find ('.ui-resizable-handle').hide ();
    this._setUpPageSizeSelection ();
    this._setUpModeSelection ();
    //this._setUpInlineViewButtons ();
    this._setUpDetailViewToggle ();

     
    this._setUpGraphViewButton ();
     

    if (this.hasUpdatePermissions) this._setUpNewRelationshipsForm ();
};


return InlineRelationshipsWidget;

}) ();



