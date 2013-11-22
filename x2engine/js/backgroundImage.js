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
	$(window).resize(function() {
		resizeBg();
	});
	
	$(window).bind("load", function () {resizeBg();});
});

function resizeBg() {
	var bgImg = $("#bg");

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
}