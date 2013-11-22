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



function MapTagsContainer (argsDict) {
	TagContainer.call (this, argsDict);	

    var defaultArgs = {
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._init ();
}

MapTagsContainer.prototype = auxlib.create (TagContainer.prototype);

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

MapTagsContainer.prototype._init = function () {
    var that = this;    

    $(that.containerSelector).droppable({ // allow tags to be dropped into inline tags widget
        accept: '.x2-tag',
        activeClass: 'x2-state-active',
        hoverClass: 'x2-state-hover',
        drop: function(event, ui) { // add a tag to this model        
            // clear placeholder text
            $(that.containerSelector).find ('.tag-container-placeholder').hide ();
            that._appendTag (ui.draggable.context);
            $('.link-disable a').click(function(e){
                e.preventDefault();
            });
        }
    });
};

