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
