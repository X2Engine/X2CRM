/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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

$(function() {

	$('a.x2-link').draggable({revert:true,helper:'clone',revertDuration:200,appendTo:'body'});

	if(window.fullscreen)
		$('#page-body').addClass('no-widgets');

	// jquery references to eliminate repeated lookups in window.resize()
	var $header = $('#header .width-constraint').first();
	var $moreMenuLi = $('#main-menu ul').parent();
	var $moreMenu = $('#main-menu ul');
	var $userSubMenu = $('#user-menu ul');
	var $pageBodyDiv = $('#page-body');
	var $pageWidthDivs = $('div.width-constraint');
	
	var pageMode = -1;		// 0 compact (no widgets)
	var newPageMode = 0;	// 1 fixed width (960px)
							// 2 fill screen (5% margins)

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

		// figure out what layout mode to use
		if(windowWidth <= 960) {
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
			// console.debug(pageMode);
			
			if(pageMode == 0) {
				$pageWidthDivs.css({'width':'','margin':'0 auto'});
				$pageBodyDiv.addClass('no-widgets');
			} else {
				if(!window.fullscreen)
					$pageBodyDiv.removeClass('no-widgets');
				if(pageMode == 1)
					$pageWidthDivs.css({'width':'940px','margin':'0 auto'});
				else if(pageMode == 2)
					$pageWidthDivs.css({'width':'auto','margin':'0 40px'});
			}
		}

		// calculate number of elements to show in the main menu
		var visibleItems = 0;
		for(i=0; i<menuItemCutoffs.length; i++) {
			if(menuItemCutoffs[i] < $header.outerWidth())
				visibleItems = i + 1;
			else
				break;
		}

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

	// force layout calculations on pageload
	$(window).resize();

	
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

	// close menu if they click anywhere else on the page
	$(document).bind('click', function(e) {
		var $clicked = $(e.target);
		if(!$clicked.parents().is('.dropdown'))
			$('.dropdown ul').removeClass('open');
	});
	
	// Yii CWebLogRoute display
	$('.yiiLog').draggable({handle:'td,th'}).height(400).offset({top:200,left:80}).find('tr:first').dblclick(function() {
		var x = $(this).closest('.yiiLog');
		if(x.height() < 50)
			x.height(400);
		else
			x.height(23);
	});
	
	// show/hide widget button
	$('#fullscreen-button').click(function() {
		// save preference
		$.ajax({
			url: yii.baseUrl+'/index.php/site/fullscreen',
			type: 'GET',
			data: 'fs='+(window.fullscreen?'0':'1')
		});
		window.fullscreen = !window.fullscreen;

		if (window.fullscreen)	// hide widgets
			$pageBodyDiv.addClass('no-widgets');
		else if(pageMode != 0)	// don't bring them back if the page is in compact mode
			$pageBodyDiv.removeClass('no-widgets');
	});
	
	// deal with the left sidebar scrolling
	
	var sidebarMenu = $('#sidebar-left');
	if (sidebarMenu.length && ($.browser != 'msie' || $.browser.version > 6)) {

		var sidebarTop = sidebarMenu.parent().offset().top - 5;
		var pageContainer = $('#flexible-content'); //.find('.container:first');
		var hasScrolled = false;
		
		sidebarMenu.parent().height(sidebarMenu.height());
		
		$(window).scroll(function(event) {
			if ($(this).scrollTop() >= sidebarTop) {

				if($(this).scrollTop() + sidebarMenu.height() > pageContainer.offset().top + pageContainer.height() + 10) {
					if(!hasScrolled)
						sidebarMenu.addClass('fixed').css('top','');
						
					if(sidebarMenu.hasClass('fixed'))
						sidebarMenu.css('top',(Math.max(pageContainer.height() - sidebarMenu.height(),0) + 10)+'px').removeClass('fixed');
						
				} else {
					sidebarMenu.addClass('fixed').css('top','');
				}
			} else {
				sidebarMenu.removeClass('fixed');
			}
			hasScrolled = true;
		});
	}
});