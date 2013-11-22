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


