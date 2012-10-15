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

// Check for iPhone screen size
//    if($.mobile.media("screen and (min-width: 320px)")) {
//        // Check for iPhone4 Retina Display
//        if($.mobile.media("screen and (-webkit-min-device-pixel-ratio: 2)")) {
//            $('meta[name=viewport]').attr('content','width=device-width, user-scalable=no,initial-scale=.5, maximum-scale=.5, minimum-scale=.5');
//        }
//    }

//$(document).ready(function () {
//    var deviceIsAndroid = (window.navigator.userAgent.toLowerCase().search('android') > -1);
//    if(deviceIsAndroid2){
//        var html = 'meta name="viewport" content="target-densityDpi=device-dpi,';
//        html+='width=device-width,initial-scale=1,minimum-scale=1,'
//        html+='maximum-scale=1,user-scalable=no"';
//        document.write(html);
//    } else {
//        var html = 'meta name="viewport" content="width=device-width,initial-scale=1,';
//        html+='minimum-scale=1,maximum-scale=1,user-scalable=no"';
//        document.write(html);
//    
//    }
//});

var x2_ScaleFix = {
viewportmeta : document.querySelector && document.querySelector('meta[name="viewport"]'),
ua : navigator.userAgent,
gestureStart : function()
{
x2_ScaleFix.viewportmeta.content = "width=device-width, minimum-scale=0.25, maximum-scale=1.6";
},
init : function()
{
if (x2_ScaleFix.viewportmeta && /iPhone|iPad/.test(x2_ScaleFix.ua) && !/Opera Mini/.test(x2_ScaleFix.ua))
{
x2_ScaleFix.viewportmeta.content = "width=device-width, minimum-scale=1.0, maximum-scale=1.0";
document.addEventListener("gesturestart", x2_ScaleFix.gestureStart, false);
}
window.onorientationchange = function()
{
document.body.scrollLeft = 0;
};
}
};
x2_ScaleFix.init();

function setMobileBrowserFalse() {
	document.cookie = 'x2mobilebrowser=false; ' + new Date().toGMTString() + '; path=/';
}
