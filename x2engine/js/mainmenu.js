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

	// get the widths of the various menu sections
	var menuBarWidth = $('#main-menu-bar').width() - 50;	// leave a little space so the menu doesn't get too crowded
	var userMenuWidth = $('#user-menu').width();
	var mainMenuWidth = 0;

	$('#main-menu > li').each(function() {
		mainMenuWidth += $(this).width();
	});

	// add items from the "More" submenu to the main menu until they don't fit
	$('#main-menu ul > li').each(function() {
		var itemWidth = 1.2 * $(this).find('a').width();
		
		var subMenuLi = $('#main-menu ul').parent();
		// console.debug(itemWidth);
		if((menuBarWidth - (userMenuWidth + mainMenuWidth)) > 1.3 * $(this).width()) {
			$(this).insertBefore(subMenuLi);
			mainMenuWidth += $(this).width();
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

	$("#transparency-button").click(showTransparencySlider);
});

var sliderTimeout;

function resetSliderTimeout() {
	clearTimeout(sliderTimeout);
	sliderTimeout = setTimeout(hideTransparencySlider,1500);
}

function showTransparencySlider() {
	//get the position of the placeholder element
	var pos = $("#transparency-button").position();
	var height = $("#transparency-button").height();
	//show the menu directly over the placeholder
	$("#transparency-slider-box").css( { "left": (pos.left - 3) + "px", "top":(pos.top + height + 5) + "px" } );
	$("#transparency-slider-box").toggle();
	
	resetSliderTimeout();
}
function hideTransparencySlider(e) {
	$("#transparency-slider-box").fadeOut(800);
}



