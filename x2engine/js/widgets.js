/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

//                     **** tags.js **** //
//
// This file includes functions to drag tags from the tag cloud to the inline tag widget,
// create new tags inside the inline tag widget, and remove tags.
//
// note: whatever file includes tags.js (probably inlineTags.php) needs to define the following
// 		$('#x2-inline-tags').data('appendTagUrl');
// 		$('#x2-inline-tags').data('removeTagUrl');
// 		$('#x2-inline-tags').data('searchUrl');
// 		$('#x2-inline-tags').data('type'); // model type
// 		$('#x2-inline-tags').data('id'); // model id
//


// init inline tags widget javascript
$(function() {
	$('#content-widgets').droppable({ // allow widgets to be dropped into content widgets list
		accept: '.x2-widget-menu-item',
		activeClass: 'x2-state-active',
		hoverClass: 'x2-state-hover',
		drop: function(event, ui) { // add a tag to this model
			// from the server get the widget and add it to the center widgets list
			$.post($('body').data('showWidgetUrl'), {
					name: ui.draggable.attr('id'),
					block: 'center',
					modelType: $('body').data('modelType'),
					modelId: $('body').data('modelId')
				}, 
				function(response) {
					$('#' + ui.draggable.attr('id')).parent().remove(); // remove widget from menu
					$('#content-widgets').append(response); // add widget to center widgets list
			});
		}
	});
	
	// handle when user rearranges widgets
	$('#content-widgets').sortable({
		update: function(event, ui) {
			$.post($('body').data('reorderWidgetsUrl'), $(this).sortable('serialize') + '&block=center');
		}
	});
	
	$('.x2-widget-menu-item').draggable({revert: 'invalid', helper:'clone', revertDuration:200, appendTo:'#widget-menu',iframeFix:true});
	
	$('.x2-widget-menu-item').click(function() {
		return handleWidgetMenuItemClick($(this));
	});
	
	$('.x2-widget-menu-item.widget-right').click(function() {
		return handleWidgetRightMenuItemClick($(this));
	});
});


function handleWidgetMenuItemClick(menuItem) {
	$.post($('body').data('showWidgetUrl'), {
	    	name: menuItem.attr('id'),
	    	block: 'center',
	    	modelType: $('body').data('modelType'),
	    	modelId: $('body').data('modelId')
	    }, 
	    function(response) {
	    	$('#' + menuItem.attr('id')).parent().remove(); // remove widget from menu
	    	
	    	// remove divider if it's not needed anymore (e.g. it's at the top or bottom of the menu
	    	if($('#widget-menu > :first-child').hasClass('x2widget-menu-divider')) {
	    		$('#widget-menu > :first-child').remove();
	    	} else if($('#widget-menu > :last-child').hasClass('x2widget-menu-divider')) {
	    		$('#widget-menu > :last-child').remove();
	    	}
	    	
	    	$('#content-widgets').prepend(response); // add widget to center widgets list
	    	$.post($('body').data('reorderWidgetsUrl'), $('#content-widgets').sortable('serialize') + '&block=center');
	});
	
	return true;
}


$.fn.hideWidget = function() {
	$(this).each(function() {
		var widget = $(this);
		var widgetName = $(this).attr('id').slice(9); // slice of the "x2widget_" from the id to get widget name
		$.post($('body').data('hideWidgetUrl'), {name: widgetName}, function(response) {
			widget.slideUp(function() {
				widget.remove();
				$('#widget-menu').replaceWith(response);
		//		$('.x2-widget-menu-item').draggable({revert: 'invalid', helper:'clone', revertDuration:200, appendTo:'#widget-menu',iframeFix:true});
				$('.x2-widget-menu-item').click(function() {
					return handleWidgetMenuItemClick($(this));
				});
				$('.x2-widget-menu-item.widget-right').click(function() {
					return handleWidgetRightMenuItemClick($(this));
				});
			});
		});
	});
}

// adds a widget to the right side widget bar
function handleWidgetRightMenuItemClick(menuItem) {
	$.post($('body').data('showWidgetUrl'), {
	    	name: menuItem.attr('id'),
	    	block: 'right',
	    }, 
	    function(response) {
	    	window.location.reload(true);
	  //  	$('#' + menuItem.attr('id')).parent().remove(); // remove widget from menu
	  //  	$('#content-widgets').append(response); // add widget to center widgets list
	  //  	$.post($('body').data('reorderWidgetsUrl'), $('#content-widgets').sortable('serialize') + '&block=center');
	});
	
	return true;
}


$.fn.hideWidgetRight = function() {
	$(this).each(function() {
		var widget = $(this);
		var widgetName = $(this).attr('id').slice(7); // slice of the "x2widget_" from the id to get widget name
		$.post($('body').data('hideWidgetUrl'), {name: widgetName}, function(response) {
			widget.slideUp(function() {
				widget.remove();
				$('#widget-menu').replaceWith(response);
			//	$('.x2-widget-menu-item').draggable({revert: 'invalid', helper:'clone', revertDuration:200, appendTo:'#widget-menu',iframeFix:true});
				$('.x2-widget-menu-item').click(function() {
					return handleWidgetMenuItemClick($(this));
				});
				$('.x2-widget-menu-item.widget-right').click(function() {
					return handleWidgetRightMenuItemClick($(this));
				});
				if($('#sidebar-right .portlet').length == 0 && window.fullscreen == false) {
					$('#fullscreen-button').trigger('click');
				}
			});
		});
	});
}


$.fn.minimizeWidget = function() {
	$(this).each(function() {
		var widget = $(this);
		var widgetName = $(this).attr('id').slice(9); // slice of the "x2widget_" from the id to get widget name
		var min = widget.find('.x2widget-container').is(':hidden') == false; // are we minimizing?

		$.post($('body').data('minimizeWidgetUrl'), {name: widgetName, minimize: min}, function(response) {
			if(min == true) {
				widget.find('.x2widget-container').slideUp();
				widget.find('.x2widget-minimize').html('[+]');
			} else {
				widget.find('.x2widget-container').slideDown();		
				widget.find('.x2widget-minimize').html('[&ndash;]');
			}
		});
	});
}
