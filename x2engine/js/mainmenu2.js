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

	var $header = $('#header');
	var subMenuLi = $('#main-menu ul').parent();
	var subMenu = $('#main-menu ul');

		$('#main-menu > li:last-child').hide();
		
	// move all submenu items into the main menu so we can get the correct display widths
	subMenu.children().insertBefore(subMenuLi);

	var menuItems = $('#main-menu > li').not('#more-menu');
	var menuItemCutoffs = new Array(menuItems.length);
	
	for(i=0; i<menuItemCutoffs.length; i++) {
		if(i == 0)
			menuItemCutoffs[i] = $(menuItems[i]).outerWidth() + $('#user-menu').outerWidth() + $('#more-menu').outerWidth()+40;
		else
			menuItemCutoffs[i] = $(menuItems[i]).outerWidth() + menuItemCutoffs[i-1];
	}

	// add items from the "More" submenu to the main menu until they don't fit
	$(window).resize(function() {
		var windowWidth = $(window).width();

		if(windowWidth <= 960) {
		
			$('#page').css({'width':'960px','margin':'0 auto'});
			
		} else {
			if(windowWidth < 1080) {
				$('#page').css({'width':'960px','margin':'0 auto'});
			} else {
				$('#page').css({'width':'auto','margin':'0 60px'});
			}
			
			
			var w = $header.outerWidth();
			// number of elements to show in the main menu
			var visibleItems = 0;
			for(i=0; i<menuItemCutoffs.length; i++) {
				if(menuItemCutoffs[i] < w)
					visibleItems = i + 1;
				else
					break;
			}
			
			var currentVisibleItems = $('#main-menu > li').not('#more-menu').length;
			
			// there is room for more items, bring some out of the submenu
			if(visibleItems > currentVisibleItems) {
				for(i=0; i<visibleItems - currentVisibleItems; i++) {
					subMenu.children().first().insertBefore(subMenuLi);
				}
			// the number of items is too damn high! move some into the submenu
			} else if(visibleItems < currentVisibleItems) {
				for(i=menuItems.length-1; i>=visibleItems; i--) {
				
					$(menuItems[i]).prependTo('#main-menu ul');
				}
			}

			if(subMenu.children().length == 0)
				subMenuLi.hide();
			else
				subMenuLi.show();
		}
	});

	// toggle menu when user clicks on "More" or whatever
	$("#main-menu li span").click(function() {
		$("#main-menu ul").toggleClass('visible');
		return false;
	});
	// same for user menu
	$("#user-menu li span").click(function() {
		$("#user-menu ul").toggleClass('visible');
		return false;
	});
	// close menu if they click anywhere else on the page
	$(document).bind('click', function(e) {
		var $clicked = $(e.target);
		if (!$clicked.parents().hasClass("#main-menu ul"))
			$("#main-menu ul").removeClass('visible');

		if (!$clicked.parents().hasClass("#user-menu ul"))
			$("#user-menu ul").removeClass('visible');
	});
	
	
	$(window).resize();
});