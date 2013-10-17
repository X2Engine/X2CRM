/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

//                     **** tags.js **** //
//
// This file includes functions to drag tags from the tag cloud to the inline tag widget,
// create new tags inside the inline tag widget, and remove tags.
//
// note: whatever file includes tags.js (probably inlineTags.php) needs to define the following
//         $('#x2-inline-tags').data('appendTagUrl');
//         $('#x2-inline-tags').data('removeTagUrl');
//         $('#x2-inline-tags').data('searchUrl');
//         $('#x2-inline-tags').data('type'); // model type
//         $('#x2-inline-tags').data('id'); // model id
//


// for getting the width of text
// we use this to set the width of the textfield as the user types a new tag
var textsize;


// init inline tags widget javascript
function initTags() {
    $('#x2-inline-tags').droppable({ // allow tags to be dropped into inline tags widget
        accept: '.x2-tag',
        activeClass: 'x2-state-active',
        hoverClass: 'x2-state-hover',
        drop: function(event, ui) { // add a tag to this model            
            $('#x2-tag-list').appendTag(ui.draggable.context);
        }
    });
    
    $('#x2-inline-tags-filter').droppable({ // allow tags to be dropped into inline tags widget
        accept: '.x2-tag',
        activeClass: 'x2-state-active',
        hoverClass: 'x2-state-hover',
        drop: function(event, ui) { // add a tag to this model        
            if($('#x2-tag-list-filter').text()==$('#x2-tag-list-filter').attr('title')){
                $('#x2-tag-list-filter').html('');
            }
            $('#x2-tag-list-filter').appendTagFilter(ui.draggable.context);
            $('.link-disable a').click(function(e){
                e.preventDefault();
            });
        }
    });
    
    // allow tags to be dropped into the publisher
    // don't highlight the publisher,
    // just allow user to drop tags on the off chance they want that sort of thing
    // when tags are dropped, the tag text is appended to the description in publisher
    $('#publisher-form #tabs').droppable({
        accept: '.x2-tag',
        drop: function(event, ui) {
            $('#Actions_actionDescription').val(
                $('#Actions_actionDescription').val() + ' ' + ui.draggable.context.innerHTML);
        }
    });
    
    // activate remove buttons ([x]) for already created tags
    $('.delete-tag').click(function(event) { return removeHandler(event); });
    $('.filter').click(function(event) { return removeHandlerFilter(event); });
    $('.link-disable a').click(function(e){
        e.preventDefault();
    });
    
    // indicate new tags can be created
    // (after user starts creating a new tag, call lockCreateTag until they are done creating the 
    // new tag)
    unlockCreateTag();
    
    // create a new tag for this model
    $('#x2-inline-tags').click(function(event) { return createNewTagHandler(event); });
    
    // ui enhancment (stop new tag from poping up while link is being followed)
    $('.tag a').click(function(event) { event.stopPropagation(); });
    
    // for getting the size of the text in the textfield
    // we use this to set the width of the textfield as the user types there new tag
    textsize = $('<div>');
    $('body').append(textsize);
    textsize.css({
        position: 'absolute',
        visibility: 'hidden',
        height: 'auto',
        width: 'auto'
    });
}


// append a tag to an element (presumably a span representing a list of tags)
// and add it to the current model
// args: tag - a tag link with innerHTML that is the tag text and href that does a search for this 
//  tag
$.fn.appendTag = function(tag) {
    
    $(this).each(function() {
        // span holds remove button and tag link
        var span = $('<span>', {
            'class': 'tag'
        });
        
        var parent = $(this);
        
        // try adding the tag to the current model
        $.post(
            $('#x2-inline-tags').data('appendTagUrl'), 
            {
                Type: $('#x2-inline-tags').data('type'), 
                Id: $('#x2-inline-tags').data('id'), 
                Tag: tag.innerHTML
            }, 
            function(response) {
                // response - JSON true if tag was created
                //          - JSON false if failed to create tag
                if(eval(response) == true) { // tag was created (and wasn't a duplicate)
                    parent.append(span);
                    span.hide().appendRemove().appendTagLink(tag).fadeIn('slow');
                }
            }
        );
    });
    
    return $(this);
}

$.fn.appendTagFilter = function(tag) {
    
    $(this).each(function() {
        // span holds remove button and tag link
        var span = $('<span>', {
            'class': 'tag link-disable'
        });
        
        var parent = $(this);
        parent.append(span);
        span.hide().appendRemoveFilter().appendTagLink(tag).fadeIn('slow');
    });
    
    
    return $(this);
}

// tag link
//
// this is where the actual tag text is placed
// a handler is attached so that the click doesn't propagate up
// and create a new tag, instead the link is just followed
$.fn.appendTagLink = function(tag) {

    $(this).each(function() {
        // tag link
        var link = $('<a>', {
            'href': tag.href,
            'html': tag.innerHTML
        });
        
        $(this).append(link);
        
        link.click(function(event) { return (tagLinkClickHandler(event)) });
    });
    
    return $(this);
}

// handler for when a tag is clicked
//
// we stop event propogation when a tag is clicked so that the click
// event of the #x2-inline-tags will not get called (that should only happen
// when the user clicks somewhere in #x2-inline-tags besides a tag)
// note: the tags link will still be followed
// note2: if we didn't do this, a new tag would pop up before the link was followed
function tagLinkClickHandler(event) {
    event.stopPropagation();
}


// remove button ([x])
// let's the user remove a tag and all elements within it (like this button and the tag link)
$.fn.appendRemove = function() {
    $(this).each(function() {
        var remove = $('<span>', {
            'class': 'delete-tag',
            'html': '[x] '
        });
        
        $(this).append(remove);
        
        remove.click( function(event) { return (removeHandler(event)); } );
    });
    
    return $(this);
}

$.fn.appendRemoveFilter = function() {
    $(this).each(function() {
        var remove = $('<span>', {
            'class': 'delete-tag filter',
            'html': '[x] '
        });
        
        $(this).append(remove);
        
        remove.click( function(event) { return (removeHandlerFilter(event)); } );
    });
    
    return $(this);
}

// handler to remove a tag
// this handler is attached to the remove ([x]) button and removes
// the tag and all the elements inside it (the remove button and the tag link)
function removeHandler(event) {
    var remove = $(event.target);
    var tag = remove.next('a'); // tag link
    tag = tag[0];

    // parent is the span that holds the remove button and the link tag
    var parent = remove.parent(); 

    var width = parent.width();

    if(parent.children('input').length > 0) { 
        // fix for if we remove a new tag before it's done being created

        parent.fadeOut(function() { parent.empty().remove(); });
        unlockCreateTag(); // we're done with this tag, so a let another get created
        
        return false;
    }
    $.post(
        $('#x2-inline-tags').data('removeTagUrl'), 
        {
            Type: $('#x2-inline-tags').data('type'), 
            Id: $('#x2-inline-tags').data('id'), 
            Tag: tag.innerHTML
        }, 
        function(response) {
            if(eval(response) == true) { // tag was removed
                parent.animate({opacity: 0}, function() {
                    parent.css('width', width + 'px');
                    parent.empty();
                    parent.animate({width: 'toggle'}, function() {
                        $(this).remove();
                    });
                });
            }
        }
    );
    
    return false; // prevent click from propagating (to x2-inline-tags)
}

function removeHandlerFilter(event) {
    var remove = $(event.target);
    var tag = remove.next('a'); // tag link
    tag = tag[0];

    // parent is the span that holds the remove button and the link tag
    var parent = remove.parent(); 
    var width = parent.width();

    // fix for if we remove a new tag before it's done being created
    if (parent.children('input').length > 0) { 
        parent.fadeOut(function() { parent.empty().remove(); });
        unlockCreateTag(); // we're done with this tag, so a let another get created
        
        return false;
    }
    parent.animate({opacity: 0}, function() {
                parent.css('width', width + 'px');
                parent.empty();
                parent.animate({width: 'toggle'}, function() {
                    $(this).remove();
                });
        });
    
    return false; // prevent click from propagating (to x2-inline-tags)
}




// **** Create New Tag **** //

// start creating a new tag that user can type in
// if the user hits ENTER, create the new tag
// if the user clicks somewhere else (and the new tag looses focus) remove the new tag
function createNewTagHandler(event) {
    if(creatingNewTag() == false) { // ensure another new tag is not already being created
        lockCreateTag(); // don't create another new tag while we're editing this one
    
        var span = $('<span>', {
            'class': 'tag'
        });
    
        var textfield = $('<input type="text">', {
            'class': 'x2-new-tag',
            'value': '#'
        });
    
        $('#x2-tag-list').append(span);
        span.hide().appendRemove().appendTagInput().fadeIn('slow');
        
        span.children('input').focus();
    }
}

// textfield so the user can fill in a new tag
$.fn.appendTagInput = function() {
    $(this).each(function() {
        var tagtext = $('<input type="text">', {
            'class': 'x2-new-tag',
            'value': '#'
        });

        var parent = $(this);
        parent.append(tagtext);

        tagtext.bind('keydown', function(event) { 
            return (tagKeyHandler(event, parent, tagtext)); 
        });
        
        // grow textfield on user input
        tagtext.bind('keypress paste', function(event) {
//            tagtext.qtip({content:'key=' + event.which});
            if($.browser.msie) {
                /* internet explorer (before version 9) sends ENTER through keyress (unlike 
                every other browser… basterds…) */
                if(parseInt($.browser.version) < 9) { 
                    tagKeyHandler(event, parent, tagtext);
                }
            }
            $(this).resizeTag();
        });
        
        // delete the new tag if it looses focus
        tagtext.blur(function() {
            parent.fadeOut(function() { parent.empty().remove(); });
            unlockCreateTag(); // we're done with this tag, so a let another get created
        });        
    });
    
    return $(this);
}

// handle keyboard input while the user is creating a new tag
//
// if the user entering text, keep growing the textfeild
// if the user presses ENTER, and there is no input, remove the new tag
// if the user presses ENTER, and there is input, create the new tag
function tagKeyHandler(event, span, textfield) {
    textfield.qtip({content:'key=' + $.browser.ie});
    if (event.which == 13) { // user hit ENTER; create the new tag (or not if no input)
        if (textfield.val() == '' || textfield.val() == '#') { // if no input, get rid of new tag
            span.fadeOut(function() { span.empty().remove(); });
            unlockCreateTag(); // we're done with this tag, so a let another get created
        } else { // create new tag
            var value = textfield.val();
            value = value.replace(/#/g, ''); // strip illegal chars
            value = '#' + value; // format into a tag
            $.post(
                $('#x2-inline-tags').data('appendTagUrl'), 
                {
                    Type: $('#x2-inline-tags').data('type'), 
                    Id: $('#x2-inline-tags').data('id'), 
                    Tag: value
                }, 
                function(response) {
                    var link = $('<a>', {
                        'href': $('#x2-inline-tags').data('searchUrl') + '?term=' + 
                            value.replace(/#/g, '%23'),
                        'html': value
                    });
                    textfield.remove();
                    span.append(link);
                    link.click(function(e) {
                        e.stopPropagation(); // prevent #x2-inline-tags click event
                    });
                }
            );
            unlockCreateTag(); // we're done with this tag, so a let another get created
        }
    } else { // input to text field; readjust textfield size
        textfield.resizeTag();
    }
}


// resize a textfield
//
// everytime the user enters a character in a textfield for a new tag, resize the textfield
$.fn.resizeTag = function() {
    $(this).each(function() {
        textsize.html($(this).val());
        $(this).css('width', (textsize.width() + 10) + 'px');
    });
    
    return $(this);
}




// **** Lock/Unlock Create New Tag **** //

// prevent new tags from being created
// this is so if the user clicks on the tags widget after already starting to
// create a new tag, and second new tag won't pop up
function lockCreateTag() {
    $('#x2-inline-tags').data('new-tag-active', true);
}

// let new tags be created when the user clicks on the tags widget
function unlockCreateTag() {
    $('#x2-inline-tags').data('new-tag-active', false);
}

// check if a new tag is being created
function creatingNewTag() {
    return $('#x2-inline-tags').data('new-tag-active');
}
