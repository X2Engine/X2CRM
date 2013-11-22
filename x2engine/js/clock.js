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
	function updateTzClock() {
				
		var tzClock = new Date();
		var tzUtcOffset = "";
		if(x2.tzOffset)
			tzClock.setTime(tzClock.getTime() + x2.tzOffset + (tzClock.getTimezoneOffset()*60000));
		if(x2.tzUtcOffset)
			tzUtcOffset = x2.tzUtcOffset;
		
		var h = tzClock.getHours();
		var m = tzClock.getMinutes();
		var s = tzClock.getSeconds() + tzClock.getMilliseconds()/1000;
		
		var ampm = "am";
		
		if(h>11)			// 0-11 -> am, 12-23 -> pm
			ampm = "pm";
		if(h>12)			// 13-23 -> 1->11
			h -= 12;
		if(h==0)
			h = 12;
		
		if(Modernizr.csstransforms) {
			var sAngle = Math.round(s * 6);
			var sCssAngle = "rotate(" + sAngle + "deg)";
			
			var hAngle = Math.round(h * 30 + (m / 2));
			var hCssAngle = "rotate(" + hAngle + "deg)";
			
			var mAngle = m * 6;
			var mCssAngle = "rotate(" + mAngle + "deg)";
			
			var browsers = ["-moz-transform","-webkit-transform","-o-transform","-ms-transform"];
			
			for(i in browsers) {
				$("#tzClock .sec").css(browsers[i],sCssAngle);
				$("#tzClock .min").css(browsers[i],mCssAngle);
				$("#tzClock .hour").css(browsers[i],hCssAngle);
			}
			$("#tzClock").attr("title",h+":"+fixWidth(m)+ampm+tzUtcOffset);
		} else {
			$("#tzClock2").html(
				h+":"+fixWidth(m)+":"+fixWidth(Math.floor(s))+ampm+tzUtcOffset
				// h+":"+fixWidth(m)+":"+fixWidth(Math.floor(s))+ampm+tzUtcOffset	// 12 hour time version
			);
		}
	}
	
	function fixWidth(x) {
		return (x<10)? "0"+x : x;
	}
	
	if(Modernizr.csstransforms) {
		$("<ul id=\"tzClock\">\
			<li class=\"sec\"><div></div><div></div></li>\
			<li class=\"hour\"><div></div></li>\
			<li class=\"min\"><div></div></li>\
		</ul>").appendTo("#widget_TimeZone .portlet-content");
		setInterval(updateTzClock, 200);
	} else {
		$("<div id=\"tzClock2\"></div>").appendTo("#widget_TimeZone .portlet-content");
		setInterval(updateTzClock, 1000);
	}
	updateTzClock();
});