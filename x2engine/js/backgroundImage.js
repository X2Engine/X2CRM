/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/

function resizeBg() {
	var bgImg = $("#bg");
	//aspectRatio = bgImg.width() / bgImg.height();

	var imgwidth = bgImg.width();
	var imgheight = bgImg.height();

	var winwidth = $(window).width();
	var winheight = $(window).height();

	var widthratio = winwidth / imgwidth;
	var heightratio = winheight / imgheight;

	var widthdiff = heightratio * imgwidth;
	var heightdiff = widthratio * imgheight;


	if(heightdiff>winheight) {
		bgImg.css({
			width: winwidth+'px',
			height: heightdiff+'px'
		});
	} else {
		bgImg.css({
			width: widthdiff+'px',
			height: winheight+'px'
		});		
	}
	// if ( (theWindow.width() / theWindow.height()) < aspectRatio ) {
		// $bg
			// .removeClass()
			// .addClass('bgheight');
	// } else {
		// $bg
			// .removeClass()
			// .addClass('bgwidth');
	// }
}

var windowFocus = false;

$(function() {
	
	$(window).resize(function() {
		resizeBg();
	});

	$(window).bind("focus", function() {
		windowFocus = true;
		setTimeout(function(){windowFocus = false;}, 500);
	});
	
	
	$(window).bind("load", function () {resizeBg();});
	
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
		var opacity = $('#transparency-slider').slider('value');
		//var clicked = $(e.target);
		//if (clicked.parents().attr('id')=='page')
			//$('#page').stop();
		if(e.pageX < pageRight && e.pageX > pageLeft && e.pageY > pageTop && e.pageY < pageBottom)
			if($('#page').css('opacity') != opacity)
				$('#page').fadeTo(500,opacity);
	});

	// $('#page').bind('click',function(e){
		// var opacity = $('#transparency-slider').slider('value');
		// $('#page').stop();
		// $('#page').fadeTo(500,opacity);
	// });
});