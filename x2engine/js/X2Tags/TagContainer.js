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


/* base prototype */

function TagContainer (argsDict) {
    var defaultArgs = {
        containerSelector: undefined
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    var that = this;
    that.newTagActive = false; // tag lock is off (i.e. tags can be created)

    // activate remove buttons ([x]) for already created tags
    $('.delete-tag').click(function(event) { return that._removeHandler(event); });
    $('.link-disable a').click(function(e){
        e.preventDefault();
    });

    // ui enhancment (stop new tag from poping up while link is being followed)
    $('.tag a').click(function(event) { event.stopPropagation(); });
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

/*
Call this before reinstantiating tag container if same container selector is used
*/
TagContainer.prototype.destructor = function () {
    var that = this; 
    x2.DEBUG && console.log ('TagContainer.destructor: ');
    x2.DEBUG && console.log ($(that.containerSelector));
    x2.DEBUG && console.log ($(that.containerSelector).hasClass ('ui-droppable'));
    if ($(that.containerSelector).hasClass ('ui-droppable')) {
        $(that.containerSelector).droppable('destroy');
        x2.DEBUG && console.log ('destroying droppable');
    }
    $(that.containerSelector).remove ();
    delete that;
};

/*
Return array containing contents of tags in tag container
*/
TagContainer.prototype.getTags = function () {
    var that = this; 
    var tags = [];
    $(that.containerSelector).find ('.tag').each (function () {
        tags.push ($(this).find ('a').text ());
    });
    x2.DEBUG && console.log ('_getTags: tags = ');
    x2.DEBUG && console.log (tags);
    return tags;
};

/*
Private instance methods
*/

// override in child prototype
TagContainer.prototype._init = function () {};

/* 
tag link
this is where the actual tag text is placed
a handler is attached so that the click doesn't propagate up
and create a new tag, instead the link is just followed
*/
TagContainer.prototype._appendTagLink = function (linkContainer, tag) {
    var that = this;    

    // tag link
    var link = $('<a>', {
        'href': tag.href,
        'html': tag.innerHTML
    });
    
    $(linkContainer).append(link);
    
    link.click(function(event) { event.stopPropagation (); });

    return $(linkContainer);
}


/*
remove button ([x])
let's the user remove a tag and all elements within it (like this button and the tag link)
*/
TagContainer.prototype._appendRemove = function (linkContainer, suppressClickBehavior) {
    suppressClickBehavior = typeof suppressClickBehavior === 'undefined' ? false : true;
    var that = this;    
    x2.DEBUG && console.log ('_appendRemove: suppressClickBehavior = ' + suppressClickBehavior);
    var remove = $('<span>', {
        'class': 'delete-tag',
        'html': '[x] '
    });
    
    $(linkContainer).append(remove);

    if (!suppressClickBehavior) {
        remove.click( function(event) { return (that._removeHandler(event)); } );
    } else {
        remove.click( function(event) { return false; });
    }
    
    return $(linkContainer);
};


TagContainer.prototype._appendTag = function (tag) {    
    var that = this;  

    // span holds remove button and tag link
    var span = $('<span>', {
        'class': 'tag link-disable'
    });
    
    var parent = $(that.containerSelector);
    parent.append(span);
    that._appendTagLink (that._appendRemove ($(span)), tag).fadeIn('slow');
    
    return $(that.containerSelector);
};

TagContainer.prototype._removeTag = function (parent) {
    x2.DEBUG && console.log ('TagContainer: _removeTag');

    var that = this;    
    parent.animate({opacity: 0}, function() {
                parent.css('width', parent.width () + 'px');
                parent.empty();
                parent.animate({width: 'toggle'}, function() {
                    $(this).remove();
                    x2.DEBUG && console.log ('animate remove');
                    x2.DEBUG && console.log ($(that.containerSelector).find ('.tag'));
                    if ($(that.containerSelector).find ('.tag').length === 0)
                        $(that.containerSelector).find ('.tag-container-placeholder').show ();
                });
        });
}

TagContainer.prototype._afterRemoveHandler = function (tag, parent) {
    this._removeTag (parent);
};

/* 
handler to remove a tag
this handler is attached to the remove ([x]) button and removes
the tag and all the elements inside it (the remove button and the tag link)
*/
TagContainer.prototype._removeHandler = function (event) {
    var that = this;    

    x2.DEBUG && console.log ('_removeHandler');
    
    var remove = $(event.target);
    var tag = remove.next('a'); // tag link
    tag = tag[0];

    // parent is the span that holds the remove button and the link tag
    var parent = remove.parent(); 

    if(parent.children('input').length > 0) { 
        // fix for if we remove a new tag before it's done being created

        parent.fadeOut(function() { parent.empty().remove(); });
        that._unlockCreateTag(); // we're done with this tag, so a let another get created
        
        return false;
    }
    that._afterRemoveHandler (tag, parent);

    return false; // prevent click from propagating (to x2-inline-tags)
};


// **** Lock/Unlock Create New Tag **** //

/* prevent new tags from being created
this is so if the user clicks on the tags widget after already starting to
create a new tag, and second new tag won't pop up */
TagContainer.prototype._lockCreateTag = function () {
    this.newTagActive = true;
};

// let new tags be created when the user clicks on the tags widget
TagContainer.prototype._unlockCreateTag = function () {
    this.newTagActive = false;
};

// check if a new tag is being created
TagContainer.prototype._creatingNewTag = function () {
    return (this.newTagActive);
};
