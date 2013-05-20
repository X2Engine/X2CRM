/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/


$(function() {

	/**
	 *	Initializes CKEditor in the email form, and the datetimepicker for the "send later" dropdown.
	 */
	$(document).on('setupInlineEmailEditor',function(){
		if(window.inlineEmailEditor)
			window.inlineEmailEditor.destroy(true);
		$('#email-message').val(x2.inlineEmailOriginalBody);
		window.inlineEmailEditor = createCKEditor('email-message',{fullPage:true,tabIndex:5,insertableAttributes:x2.insertableAttributes}, function() {
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
 *
 * The optional "mode" parameter is used when opening the inline email form with
 * a different model, i.e. a quote.
 */
function toggleEmailForm(mode) {
	mode = (typeof mode == 'undefined') ? 'default' : mode;
	if(typeof quickQuote != 'undefined') {
		if(quickQuote.inlineEmailMode != mode)
			quickQuote.resetInlineEmail();
	}
	
	if($('#inline-email-form .wide.form').hasClass('hidden')) {
		$('#inline-email-form .wide.form').removeClass('hidden');
		$('#inline-email-form .form.email-status').remove();
		return;
	}
	
	if($('#inline-email-form').is(':hidden')) {
		$('#inline-email-status').hide(); // Opening new form; hide previous submission's status
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
			if(inlineEmailSwitchConfirm()) {
				window.inlineEmailEditor.updateElement();
				jQuery.ajax({
					'type':'POST',
					'url':yii.scriptUrl+'/contacts/inlineEmail?ajax=1&template=1',
					'data':jQuery(this).parents("form").serialize(),
					'beforeSend':function() {
						$('#email-sending-icon').show();
					}
				}).done(function(data, textStatus, jqXHR) {
					handleInlineEmailActionResponse(data, textStatus, jqXHR);
				});
			}
		}
	});
}

function inlineEmailSwitchConfirm() {
	var proceed = true;
	var noChange = ! window.inlineEmailEditor.checkDirty();
	if(!noChange)
		proceed = confirm($('#template-change-confirm').text());
	return proceed;
}

/**
 * Function called to denote that the email form is being submitted.
 */
function setInlineEmailFormLoading() {
	$('#email-sending-icon').show();
}

function handleInlineEmailActionResponse(data, textStatus, jqXHR) {	
	$('#email-sending-icon').hide();
	if(data.error) {
		if(data.modelHasErrors) {
			// Error-highlight the fields that have errors:
			for (var attr in data.modelErrors) {
				if(attr != 'message') { // Skip the message area; it will turn the background pink, and that would be icky.
					$('input[name="InlineEmail['+attr+']"]').addClass('error');
				}
			}
		} else {
			$('#inline-email-errors').addClass('errorSummary');
		}
		$('#inline-email-errors').html(data.modelHasErrors ? data.modelErrorHtml : data.message).show();
		return false;
	}
	if(data !== undefined) {
		if(data.scenario == 'template') { // Submission was for getting new template. Fill in with template content.
			window.inlineEmailEditor.setData(data.attributes.message);
			$('input[name="InlineEmail[subject]"]').val(data.attributes.subject);
		} else { // Email was sent successfully. Reset everything.
			
			$('.error').removeClass('error');
			$('#inline-email-status').show().html(data.message);
			$('#inline-email-errors').html('').hide();
			$('#email-template').val(0);			
			$('input[name="InlineEmail[subject]"]').val('');
			toggleEmailForm();
			updateHistory();
			
		}
	}
	return false;
}