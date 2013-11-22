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

var x2_chatPoll = 5000;
var x2_latest = '';
var x2_pending = null;
var x2_skip = false;

$(document).bind("mobileinit", function(){
    //apply overrides here
    $('#site-chat').live('pagebeforehide',function(event, ui){
        //console.log('PBH<='+x2_pending);
        x2_skip = true;
        if (x2_pending != null){
            x2_pending.abort();
            x2_pending = null;
            //console.log('PBH>='+x2_pending);
        }
    });
    $('#site-chat').live('pageshow',function(event, ui){
        //console.log('PS*='+x2_latest);
        x2_latest ='';
        x2_skip = false;
        setTimeout(updateChat,1000);
    });

	// set up page loader
  	$.mobile.loader.prototype.options.text = "loading";
  	$.mobile.loader.prototype.options.textVisible = true;
  	$.mobile.loader.prototype.options.theme = "a";
  	$.mobile.loader.prototype.options.html = "";
});
