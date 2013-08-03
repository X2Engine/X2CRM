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
			$.post(yii.scriptUrl+'/site/showWidget', {
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
			$.post(yii.scriptUrl+'/site/reorderWidgets', $(this).sortable('serialize') + '&block=center');
		},
		handle: $(this).find ('.x2widget-header')
	});
	
	$('.x2-widget-menu-item').draggable({
		revert: 'invalid', 
		helper:'clone', 
		revertDuration:200, 
		appendTo:'#widget-menu',
		iframeFix:true
	});
	
	$('.x2-widget-menu-item').click(function() {
		return handleWidgetMenuItemClick($(this));
	});
	
	$('.x2-widget-menu-item.widget-right').click(function() {
		return handleWidgetRightMenuItemClick($(this));
	});
});


function handleWidgetMenuItemClick(menuItem) {
	$.post(yii.scriptUrl+'/site/showWidget', {
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
	    	$.post(yii.scriptUrl+'/site/reorderWidgets', $('#content-widgets').sortable('serialize') + '&block=center');
	});
	
	return true;
}


$.fn.hideWidget = function() {
	$(this).each(function() {
		var widget = $(this);
		var widgetName = $(this).attr('id').slice(9); // slice of the "x2widget_" from the id to get widget name
		$.post(yii.scriptUrl+'/site/hideWidget', {name: widgetName}, function(response) {
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
	$.post(yii.scriptUrl+'/site/showWidget', {
	    	name: menuItem.attr('id'),
	    	block: 'right'
	    }, 
	    function(response) {
	    	window.location.reload(true);
	  //  	$('#' + menuItem.attr('id')).parent().remove(); // remove widget from menu
	  //  	$('#content-widgets').append(response); // add widget to center widgets list
	  //  	$.post(yii.scriptUrl+'/site/reorderWidgets', $('#content-widgets').sortable('serialize') + '&block=center');
	});
	
	return true;
}


$.fn.hideWidgetRight = function() {
	$(this).each(function() {
		var widget = $(this);
		var widgetName = $(this).attr('id').slice(7); // slice of the "x2widget_" from the id to get widget name
		$.post(yii.scriptUrl+'/site/hideWidget', {name: widgetName}, function(response) {
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

		$.post(yii.scriptUrl+'/site/minimizeWidget', {name: widgetName, minimize: min}, function(response) {
			if(min == true) {
				widget.find('.x2widget-container').slideUp();
				widget.find('.x2widget-minimize').html('<img src="'+yii.themeBaseUrl+'/images/icons/Expand_Widget.png" />');
			} else {
				widget.find('.x2widget-container').slideDown({
					done: function () {
						if (widgetName === 'ActionHistoryChart') {

							// event detected by x2chart.js
							$(document).trigger ('chartWidgetMaximized'); 
						}
					}
				});		
				widget.find('.x2widget-minimize').html('<img src="'+yii.themeBaseUrl+'/images/icons/Collapse_Widget.png" />');
			}
		});
	});
}
