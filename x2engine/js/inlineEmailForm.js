/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
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

	/**
	 *	Initializes CKEditor in the email form, and the datetimepicker for the "send later" dropdown.
	 */
	$(document).on('setupInlineEmailEditor',function(){
		if(window.inlineEmailEditor)
			window.inlineEmailEditor.destroy(true);
		window.inlineEmailEditor = createCKEditor('email-message',{tabIndex:5,insertableAttributes:x2.insertableAttributes}, function() {
			if(typeof inlineEmailEditorCallback == 'function') {
				inlineEmailEditorCallback(); // call a callback function after the inline email editor is created (if function exists)
			}
		});
		
		setupEmailAttachments('email-attachments');
	});

	$(document).delegate('#email-template','change',function() {
		if($(this).val() != '0')
			$('#email-subject').val($(this).find(':selected').text());
		$('#preview-email-button').click();
	});
	
	
	// give send-email module focus when clicked
	$('#inline-email-form').click(function() {
		if(!$('#inline-email-form').find('.wide.form').hasClass('focus-mini-module')) {
			$('.focus-mini-module').removeClass('focus-mini-module');
			$('#inline-email-form').find('.wide.form').addClass('focus-mini-module');
		}
	});
	
	// give send-email module focus when tinyedit clicked
	$('#email-message').click(function() {
		if(!$('#inline-email-form').find('.wide.form').hasClass('focus-mini-module')) {
			$('.focus-mini-module').removeClass('focus-mini-module');
			$('#inline-email-form').find('.wide.form').addClass('focus-mini-module');
		}
	});
	
	if(window.hideInlineEmail)
		$('#inline-email-form').hide();
	else
		$(document).trigger('setupInlineEmailEditor');


	
	setupInlineEmailForm();
});


/**
 * Toggles the inline email form open or closed. Scrolls to the email form and animates 
 * the form sliding open. Alternatively, slides the form closed.
 */
function toggleEmailForm() {
	
	
	if($('#inline-email-form .wide.form').hasClass('hidden')) {
		$('#inline-email-form .wide.form').removeClass('hidden');
		$('#inline-email-form .form.email-status').remove();
		return;
	}
	
	if($('#inline-email-form').is(':hidden')) {
		$(document).trigger('setupInlineEmailEditor');
		$('.focus-mini-module').removeClass('focus-mini-module');
		$('#inline-email-form').find('.wide.form').addClass('focus-mini-module');
		$('html,body').animate({
			scrollTop: ($('#inline-email-top').offset().top - 100)
		}, 300);
	}
	
	$('#inline-email-form').animate({
		opacity: 'toggle',
		height: 'toggle'
	}, 300); // ,function() {  $('#inline-email-form #InlineEmail_subject').focus(); }
	
	$('#InlineEmail_subject')
		.addClass('focus')
		.focus()
		.blur(function() {$(this).removeClass('focus');});
}


/**
 * Set up attachments in the email form so that the attachments div is droppable for
 * files dragged over from the media widget. This is called when the page loads (if the
 * page has an inline email form) and whenever the email form is replaced, like after an
 * ajax call from pressing the preview button.
 */
function setupInlineEmailForm() {
	
	$(document).trigger('setupInlineEmailEditor');
	
	// setupEmailAttachments();
	
	initX2FileInput();
	
	$(document).mouseup(function() {
		$('input.x2-file-input[type=file]').next().removeClass('active');
	});

	// init cc and bcc buttons
	$('#cc-toggle').click(function() {
		$(this).animate({
				opacity: 'toggle',
				width: 0
			}, 400);
		
		$('#cc-row').slideDown(300);
	});
	
	$('#bcc-toggle').click(function() {
		$(this).animate({
				opacity: 'toggle',
				width: 0
			}, 400);
		
		$('#bcc-row').slideDown(300);
	});
	
	$('#email-template').change(function() {
		var template = $(this).val();
		if(template != "0") {
			window.inlineEmailEditor.updateElement();
			jQuery.ajax({
				'beforeSend':function() {
					$('#email-sending-icon').show();
				},
				'complete':function(response) {
					$('#email-sending-icon').hide();
					setupInlineEmailForm();
					return false;
				},
				'type':'POST',
				'url':yii.baseUrl+'/index.php/contacts/inlineEmail?ajax=1&preview=1',
				'data':jQuery(this).parents("form").serialize(),
				'success':function(html){
					jQuery("#inline-email-form").replaceWith(html)
				}
			});
		}
	});
}