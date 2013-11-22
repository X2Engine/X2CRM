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

function TagCreationContainer (argsDict) {
	TagContainer.call (this, argsDict);	
    
    var defaultArgs = {
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    // for getting the size of the text in the textfield
    // we use this to set the width of the textfield as the user types there new tag
    this.textsize = $('<div>');
    $('body').append(this.textsize);
    this.textsize.css({
        position: 'absolute',
        visibility: 'hidden',
        height: 'auto',
        width: 'auto'
    });
}

TagCreationContainer.prototype = auxlib.create (TagContainer.prototype);

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


// **** Create New Tag Behavior **** //

/* start creating a new tag that user can type in
if the user hits ENTER, create the new tag
if the user clicks somewhere else (and the new tag looses focus) remove the new tag */
TagCreationContainer.prototype._createNewTagHandler = function (event) {
    var that = this;  

    if(that._creatingNewTag() === false) { // ensure another new tag is not already being created
        that._lockCreateTag(); // don't create another new tag while we're editing this one
    
        var span = $('<span>', {
            'class': 'tag'
        });
    
        var textfield = $('<input type="text">', {
            'class': 'x2-new-tag',
            'value': '#'
        });
    
        $('#x2-tag-list').append(span);
        span.hide();
        that._appendTagInput (that._appendRemove($(span), true)).fadeIn('slow');
        span.children('input').focus();
    }
}

// textfield so the user can fill in a new tag
TagCreationContainer.prototype._appendTagInput = function(linkContainer) {
    var that = this; 

    var tagtext = $('<input type="text">', {
        'class': 'x2-new-tag',
        'value': '#'
    });

    var parent = $(linkContainer);
    parent.append(tagtext);

    tagtext.bind('keydown', function(event) { 
        return (that._tagKeyHandler(event, parent, tagtext)); 
    });
    
    // grow textfield on user input
    tagtext.bind('keypress paste', function(event) {
//            tagtext.qtip({content:'key=' + event.which});
        if($.browser.msie) {
            /* internet explorer (before version 9) sends ENTER through keyress (unlike 
            every other browser… basterds…) */
            if(parseInt($.browser.version) < 9) { 
                that._tagKeyHandler(event, parent, tagtext);
            }
        }
        that._resizeTag($(this));
    });
    
    // create tag if it loses focus and if input isn't empty
    tagtext.blur(function() {
        that._tagInputEnd (event, parent, tagtext);
    });        
    
    return $(linkContainer);
};

/*
Called when user finishes inputting text in tag input
*/
TagCreationContainer.prototype._tagInputEnd = function (event, span, textfield) {
    var that = this;
    if (textfield.val() == '' || textfield.val() == '#') { // if no input, get rid of new tag
        that._removeTag (span);
        that._unlockCreateTag(); // we're done with this tag, so a let another get created
    } else { // create new tag
        var value = textfield.val();
        value = value.replace(/#/g, ''); // strip illegal chars
        value = '#' + value; // format into a tag

        that._afterCreateNewTag (textfield, value, span);

        that._unlockCreateTag(); // we're done with this tag, so a let another get created
    }
};

/* handle keyboard input while the user is creating a new tag

if the user entering text, keep growing the textfeild
if the user presses ENTER, and there is no input, remove the new tag
if the user presses ENTER, and there is input, create the new tag */
TagCreationContainer.prototype._tagKeyHandler = function (event, span, textfield) {
    var that = this; 

    textfield.qtip({content:'key=' + $.browser.ie});
    if (event.which == 13) { // user hit ENTER; create the new tag (or not if no input)
        that._tagInputEnd (event, span, textfield);
    } else { // input to text field; readjust textfield size
        that._resizeTag(textfield);
    }
};

TagCreationContainer.prototype._afterCreateNewTag = function(textfield, value, span) {
    var that = this;     
    that._convertInputToTag (textfield, value, span);
};

TagCreationContainer.prototype._convertInputToTag = function(textfield, value, span) {
    var that = this; 

    var link = $('<a>', {
        'html': value
    });
    textfield.remove();
    span.find ('.delete-tag').remove ();
    that._appendRemove (span);
    span.append(link);

    $(link).attr (
        'href', that.searchUrl + '?term=' + encodeURIComponent ($(link).text ()));
    link.click(function(e) {
        e.stopPropagation(); // prevent #x2-inline-tags click event
    });
    return link;
};

/* resize a textfield
everytime the user enters a character in a textfield for a new tag, resize the textfield */
TagCreationContainer.prototype._resizeTag = function(textfield) {
    var that = this;    

    x2.DEBUG && console.log ('_resizeTag');
    $(textfield).each(function() {
        that.textsize.html($(this).val());
        x2.DEBUG && console.log ('that.textsize.width = ' + that.textsize.width ());
        $(this).css('width', (that.textsize.width() + 10) + 'px');
    });

    return $(textfield);
};
