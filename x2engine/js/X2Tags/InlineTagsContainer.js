/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/



function InlineTagsContainer (argsDict) {
	TagCreationContainer.call (this, argsDict);	

    var defaultArgs = {
        appendTagUrl: undefined,
        removeTagUrl: undefined,
        searchUrl: undefined,
        modelType: undefined,
        modelId: undefined,
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._init ();
}

InlineTagsContainer.prototype = auxlib.create (TagCreationContainer.prototype);

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


InlineTagsContainer.prototype._afterRemoveHandler = function (tag, parent) {
    var that = this;    
    $.post(
        that.removeTagUrl,
        {
            Type: that.modelType,
            Id: that.modelId,
            Tag: $(tag).text ()
        }, 
        function(response) {
            if(response === 'true') { // tag was removed
                that._removeTag (parent);
            }
        }
    );
}

/*
append a tag to an element (presumably a span representing a list of tags)
and add it to the current model
Parameters: tag - a tag link with innerHTML that is the tag text and href that does a search for 
    this tag 
*/
InlineTagsContainer.prototype._appendTag = function (tag) {
    var that = this;    
    x2.DEBUG && console.log ('InlineTagsContainer: _appendTag'); 
    // span holds remove button and tag link
    var span = $('<span>', {
        'class': 'tag'
    });
    
    var parent = $(that.containerSelector);
    x2.DEBUG && console.log (parent); 
    // try adding the tag to the current model
    $.post(
        that.appendTagUrl,
        {
            Type: that.modelType,
            Id: that.modelId,
            Tag: $(tag).text ()
        }, 
        function(response) {
            // response - JSON true if tag was created
            //          - JSON false if failed to create tag
            if(eval(response) == true) { // tag was created (and wasn't a duplicate)
                parent.append(span);
                span.hide();
                that._appendTagLink (that._appendRemove($(span)), tag).fadeIn('slow');
            }
        }
    );
    
    return $(this);
};

// override ancestor method
InlineTagsContainer.prototype._afterCreateNewTag = function(textfield, value, span) {
    var that = this; 
    
    var link = that._convertInputToTag (textfield, value, span);

    $.post(
        that.appendTagUrl, 
        {
            Type: that.modelType,
            Id: that.modelId,
            Tag: $(link).text ()
        }, 
        function(response) {
        }
    );
};

// override ancestor method
InlineTagsContainer.prototype._init = function () {
    var that = this;    

    $(that.containerSelector).droppable({ // allow tags to be dropped into inline tags widget
        accept: '.x2-tag',
        activeClass: 'x2-state-active',
        hoverClass: 'x2-state-hover',
        drop: function(event, ui) { // add a tag to this model            
            // clear placeholder text
            $(that.containerSelector).find ('.tag-container-placeholder').hide ();
            that._appendTag(ui.draggable.context);
        }
    });

    // create a new tag for this model
    $(that.containerSelector).click(function(event) { 
        x2.DEBUG && console.log ('InlineTagsContainer: containerSelector: click');
        // clear placeholder text
        $(that.containerSelector).find ('.tag-container-placeholder').hide ();
        that._createNewTagHandler(event); 
    });
};
