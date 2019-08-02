/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




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
			
			for(var i in browsers) {
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
