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


$(function() {
	$('div.x2-layout .formSection.hideSection .tableWrapper').hide();
	// $('div.x2-layout .formItem').disableSelection();

	$('div.x2-layout .formSectionShow, .formSectionHide').click(function() {
		$(this).closest('.formSection').toggleClass('hideSection').find('.tableWrapper').slideToggle();
		
		var formSectionStatus = [];
		$('div.x2-layout .formSection').each(function(i,section) {
			formSectionStatus[i] = $(section).hasClass('hideSection')? '0' : '1';
		});
		
		var formSettings = '['+formSectionStatus.join(',')+']';
		$.ajax({
			url: yiiBaseUrl+'/site/saveFormSettings',
			type: 'GET',
			data: 'formName='+window.formName+'&formSettings='+encodeURI(formSettings)
		});
	});
	
	$('.inlineLabel').find('input:text, textarea').focus(function() { formFieldFocus(this); }).blur(function() { formFieldBlur(this); });
});

function toggleText(field) {
	if (field.defaultValue==field.value) {
		field.value = ''
		field.style.color = 'black'
	} else if (field.value=='') {
		field.value = field.defaultValue
		field.style.color = '#aaa'
	}
}
function formFieldFocus(elem) {
	var field = $(elem);
	if (field.val() == field.attr('title')) {
		field.val('');
		field.css('color','#000');
	}
}
function formFieldBlur(elem) {
	var field = $(elem);
	if (field.val() == '') {
		field.val(field.attr('title'));
		field.css('color','#aaa');
	}
}
function submitForm(formName) {
	document.forms[formName].submit();
}
function toggleForm(formName,duration) {
	$(formName).toggle('blind',{},duration);
}
function hide(field) {
	$(field).hide(); //field.style.display="none";
	// button=document.getElementById('save-changes');
	$('#save-changes').addClass('highlight'); //button.style.background='yellow';
}
function show(field) {
	$(field).show();
	// field.style.display="block";
}

function toggleFormSection(button) {
	var $button = $(button);

	$button.closest('.formSection').find('.formSectionRow').css('min-height','').toggle(); 
	// animate({
		// height:'toggle'
	// },300);
	if($button.html() == '[ Show ]')
		$button.html('[ Hide ]');
	else
		$button.html('[ Show ]');
}