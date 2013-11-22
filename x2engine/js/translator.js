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

	var $inputs = $("input, button, textarea").filter(function() { return $(this).attr('value').match(/<dt class="yii-t">.+/); });
	var $options = $("option").filter(function() { return $(this).text().match(/<dt class="yii-t">.+/); });
	
	
	$.each($inputs,function(i,elem) {
		var translation = $('.yiiTranslationList').append($(elem).attr('value')+'<br>').find('dt').last().text();
		$(elem).attr('value',translation);
	});
	
	$.each($options,function(i,elem) {
		var translation = $('.yiiTranslationList').append($(elem).text()+'<br>').find('dt').last().text();
		$(elem).text(translation);
	});


	var altKeyDown = false;

	$('body').mousemove(function(e) {	// check for altkey on mousemove,
		if(e.altKey != altKeyDown)		// only call toggleClass if it has changed
			$(this).toggleClass('yii-t',e.altKey);
		altKeyDown = e.altKey;
	});

	$(document).delegate('dt.yii-t','click',function(e) {
		
		if(e.altKey) {
			e.preventDefault();
			e.stopPropagation();
			
			
			
			var category = $(this).find('input#cat').val();
			var message = $(this).find('input#msg').val();
			alert(category+', '+message);	 
			
			return false;
		}
		return true;
	});

});