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
					modelId: $('body').data('modelId'),
                    moduleName: x2.currModule
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

function removeChartWidget () {
    if (x2.actionHistoryChart) {
        x2.actionHistoryChart.chart.tearDown ();
    }
    /*if (x2.campaignChart) {
        delete x2.campaignChart;
    }*/
}


$.fn.hideWidget = function() {
	$(this).each(function() {
		var widget = $(this);
		var widgetName = $(this).attr('id').slice(9); // slice of the "x2widget_" from the id to get widget name

        // console.log ('widgetName = ' + widgetName);
		$.post(yii.scriptUrl+'/site/hideWidget', {name: widgetName}, function(response) {
			widget.slideUp(function() {
				widget.remove();
                if (widgetName === 'RecordViewChart') removeChartWidget ();
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
						if (widgetName === 'RecordViewChart') {

							// event detected by centerWidget.php
							$(document).trigger ('chartWidgetMaximized'); 
						}
					}
				});		
				widget.find('.x2widget-minimize').html('<img src="'+yii.themeBaseUrl+'/images/icons/Collapse_Widget.png" />');
			}
		});
	});
}
