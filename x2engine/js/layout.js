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

$(function() {
	
	$(document).on('mousedown','.x2-button',function(e){ e.preventDefault(); });
	
	
	$('a.x2-link').draggable({revert: 'invalid', helper:'clone', revertDuration:200, appendTo:'body',iframeFix:true});

	// if(window.fullscreen)
		// $('body').addClass('no-widgets');
		
	//widget collapsing
	// var widgetHoverTimeout = null;
	
	// $("#sidebar-right").css({display:"block",position:"absolute",top:"0",right:"0","z-index":"1"}).unbind().mouseenter(function(){
		// clearTimeout(widgetHoverTimeout);
		// $(this).stop().animate({"right":"0"},300);
	// }).mouseleave(function() {
		// var self = this;
		// widgetHoverTimeout = setTimeout(function() {
			// $(self).stop().animate({"right":"-"+($(self).width()-20)+"px"},300);
		// },750);
	// });

	// jquery references to eliminate repeated lookups in window.resize()
	var $header = $('#main-menu-bar');
	var $moreMenuLi = $('#main-menu ul').parent();
	var $moreMenu = $('#main-menu ul');
	var $userSubMenu = $('#user-menu ul');
	// var $pageBodyDiv = $('#page-body');
	var $body = $('body');
	var $pageWidthDivs = $('div.width-constraint');
	var $contentDiv = $('div#content');
	
	var pageMode = -1;		// 0 compact (no widgets)
	var newPageMode = 0;	// 1 fixed width (960px)
							// 2 fill screen (5% margins)

	var historyMode = -1;		// 0 underneath record
	var newHistoryMode = 0;		// 1 side of record

	// move all moreMenu items into the main menu so we can get the correct display widths
	$moreMenu.children().insertBefore($moreMenuLi);

	var $menuItems = $('#main-menu > li').not('#more-menu');
	var currentVisibleItems = $menuItems.length;
	var menuItemCutoffs = new Array($menuItems.length);
	
	for(i=0; i<menuItemCutoffs.length; i++) {
		if(i == 0)
			menuItemCutoffs[i] = $($menuItems[i]).outerWidth() + $('#user-menu').outerWidth() + $('#more-menu').outerWidth()+40;
		else
			menuItemCutoffs[i] = $($menuItems[i]).outerWidth() + menuItemCutoffs[i-1];
	}

	// the screen just got resized - decide what to do about it
	$(window).resize(function() {
		var windowWidth = $(window).width();
		var contentWidth = $contentDiv.width();

		// figure out what layout mode to use
		if(!x2.isAndroid && !x2.isIPad && windowWidth <= 1040) {
			newPageMode = 0;
		} else {
			if(windowWidth >= 1040 && window.enableFullWidth) {
				newPageMode = 2;
			} else {
				newPageMode = 1;
			}
		}
		
		// only change CSS if the layout mode has changed
		if(pageMode != newPageMode) {
		
			pageMode = newPageMode;
			
			if(pageMode == 0) {
				$body.addClass('no-widgets');
			} else {
				 if(!window.fullscreen) {
					$body.removeClass('no-widgets');
					$(document).trigger ('showWidgets');
				}
			}
		}
		
		if(contentWidth < 940)
			newHistoryMode = 0;
		else
			newHistoryMode = 1
			
		if(historyMode != newHistoryMode) {
			historyMode = newHistoryMode;
			if(historyMode==1)
				$('#main-column, .history').addClass('half-width');
			else
				$('#main-column, .history').removeClass('half-width');
		}

		// calculate number of elements to show in the main menu
		var visibleItems = 0;
		for(i=0; i<menuItemCutoffs.length; i++) {
			if(menuItemCutoffs[i] + 70 < $header.outerWidth())
				visibleItems = i + 1;
			else
				break;
		}
		if(visibleItems < 1)
			visibleItems = 1;

		// there is room for more items, bring some out of the moreMenu
		if(visibleItems > currentVisibleItems) {
			for(i=0; i<visibleItems - currentVisibleItems; i++) {
				$moreMenu.children().first().insertBefore($moreMenuLi);
			}
			currentVisibleItems = $('#main-menu > li').not('#more-menu').length;
			
		// the number of items is too damn high! move some into the moreMenu
		} else if(visibleItems < currentVisibleItems) {
			for(i=$menuItems.length-1; i>=visibleItems; i--) {
			
				$($menuItems[i]).prependTo('#main-menu ul');
			}
			currentVisibleItems = $('#main-menu > li').not('#more-menu').length;
		}
		// show More dropdown only if it's needed
		if($moreMenu.children().length == 0)
			$moreMenuLi.hide();
		else
			$moreMenuLi.show();
	});

	

	
	// $('img').mousedown(function(e) {
		// e.preventDefault ? e.preventDefault() : e.returnValue = false;
	// });
	// $('div, span, a').attr('unselectable','on');	
	
	
	
	// toggle dropdown menus
	$(".dropdown span").mousedown(function() {	
		var $dropdown = $(this).siblings('ul');	// the menu to be opened
		$dropdown.toggleClass('open');
		$('.dropdown ul').not($dropdown).removeClass('open');	// close all other menus
		return false;
	});
	
	// toggle widget menu
	$('#widget-button').click(function() {
		if($('#widget-menu li').length != 0)
			$('#widget-menu').toggle();
		return false;
	});

	// close menu if they click anywhere else on the page
	$(document).bind('click', function(e) {
		var $clicked = $(e.target);
		if(!$clicked.parents().is('.dropdown'))
			$('.dropdown ul').removeClass('open');
		if(!$clicked.is('#widget-button'))
			$('#widget-menu').hide();
	});
	
	// Yii CWebLogRoute display
	$('.yiiLog').draggable({handle:'tr:first th'}).height(400).offset({top:200,left:80}).find('tr:first').dblclick(function() {
		var x = $(this).closest('.yiiLog');
		if(x.height() < 50)
			x.height(400);
		else
			x.height(23);
	});
	
	// translation list
	$('.yiiTranslationList').draggable({handle:'td,th'}).offset({top:300,left:0});
	
	
	// show/hide widget button
	$('#fullscreen-button').click(function(evt) {
        evt.preventDefault ();
                                                    
		// save preference
		$.ajax({
			url: yii.scriptUrl+'/site/fullscreen',
			type: 'GET',
			data: 'fs='+(window.fullscreen?'0':'1')
		});
		window.fullscreen = !window.fullscreen;

		if (window.fullscreen) {	// hide widgets
			$body.addClass('no-widgets');
		} else if(pageMode != 0) {	// don't bring them back if the page is in compact mode
			$body.removeClass('no-widgets');
			$(document).trigger ('showWidgets');
		}
		
		$(window).resize();
	});

	// make the record title the same width as the main column
	var pageTitle = $(".page-title-fixed-outer .page-title").first();
	// var pageTitleWidth = pageTitle.next();
	// var mainColumn = pageTitle.parent();
	var mainColumn = $(".page-title-fixed-outer").parent();
	
	if(pageTitle.length) {
		$(window).resize(function(e) {
			// pageTitle.width(pageTitleWidth.width()-48);
			mainColumn.css('margin-top',(pageTitle.height()-36)+'px');
		});
	}
	
	// force layout calculations on pageload
	$(window).resize();
});


function togglePortletVisible(portlet, response) {
	portlet.children('.portlet-content').toggle('blind');
	var text;
	if(response == true) {
		var text = "[&ndash;]";
	} else {
		var text = "[+]";
	}
	portlet.find('.portlet-minimize a').html(text);
}
