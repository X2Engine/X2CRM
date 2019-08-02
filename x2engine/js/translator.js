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