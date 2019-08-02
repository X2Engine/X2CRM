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
    gestureStart : function() {
        x2_ScaleFix.viewportmeta.content = "width=device-width, minimum-scale=0.25, maximum-scale=1.6";
    },
    init : function() {
        if (x2_ScaleFix.viewportmeta && /iPhone|iPad/.test(x2_ScaleFix.ua) && !/Opera Mini/.test(x2_ScaleFix.ua)) {
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


