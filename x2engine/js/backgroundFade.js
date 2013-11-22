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

// var windowFocus = false;

$(function() {
	

	/* $(window).bind("focus", function() {
		windowFocus = true;
		setTimeout(function(){windowFocus = false;}, 500);
	});
	
	$('img').bind('click',function(e) {
		if($(this).attr('id') == 'bg' && !windowFocus) {
			//$('#page').stop();
			$('#page').fadeTo(500,0.2);
		}
		windowFocus = false;
	});
	$(document).bind('click',function(e) {
		if($(e.target).attr('id')=='body-tag' && !windowFocus) {
			//$('#page').stop();
			$('#page').fadeTo(500,0.2);
		}
		windowFocus = false;
	});
	
	var pageLeft = $('#page').offset().left;
	var pageTop = $('#page').offset().top;
	var pageRight = pageLeft + $('#page').width();
	var pageBottom = pageTop + $('#page').height();
	
	$(document).bind('click', function(e) {
		// if($('#transparency-slider').length)
			// var opacity = $('#transparency-slider').slider('value');
		// else
			var opacity = 1;

		if(e.pageX < pageRight && e.pageX > pageLeft && e.pageY > pageTop && e.pageY < pageBottom)
			if($('#page').css('opacity') != opacity)
				$('#page').fadeTo(500,opacity);
	}); */
	
	var isHidden = false;
	var hideTargets = $("#page").children().not("#header").add("#footer");
	
	
	$(document).click(function(e) {
		if(isHidden) {
			hideTargets.stop().animate({opacity:1},300);
			isHidden = false;
		}
	});
	
	$("#page-fader").click(function(e) {
		if(!isHidden) {
			e.stopPropagation();
			hideTargets.stop().animate({opacity:0},300);
			isHidden = true;
		}
	});
});