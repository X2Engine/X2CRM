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

// Quick quote create javascript.
// To use: just stick a hidden div with id="quote-form-wrapper" somewhere and
// register a script that declares a "quickQuote" object within x2 with the
// following properties:
// - contact (optional) name of associated contact
// - account (optional) name of associated account
// - failMessage (required) translated message when the AJAX request to create the quote fails

jQuery(document).ready(function ($) {

	quickQuote.declare = function() {
		quickQuote.wrapper = $('#quote-create-form-wrapper').first();
		quickQuote.loadingImg = $('<img>',{
			src:yii.themeBaseUrl+'/images/loading.gif',
			height:32,
			width:32
		}).css({
			'display':'inline-block'
		});
		quickQuote.loading = $('<div>').css({
			'text-align':'center',
			'display':'block'
		}).append(quickQuote.loadingImg);
	}

	quickQuote.declare();

	quickQuote.closeForm = function(e) {
		if(e!==undefined) { // Function is being used as an event handler
			e.stopPropagation();
			e.preventDefault();
		}
		quickQuote.wrapper.slideUp('slow'); // Close
	}

	// This reloads everything...the entire quote form.
	quickQuote.reloadAll = function () {
		$.ajax({
			url:quickQuote.reloadAction
		}).done(function(html) {
			$('#quote-form-wrapper').html(html).find('#quotes-form .wide.form').addClass('focus-mini-module');
			quickQuote.declare();
			$.fn.yiiGridView.update("relationships-grid");
			$('html,body').animate({
				scrollTop: quickQuote.wrapper.parents('#quote-form-wrapper').first().offset().top
			},300);
		});
	}

	quickQuote.openForm = function(id) {
		id = typeof id === 'undefined' ? 0 : id;
		if(quickQuote.wrapper.is(':hidden')) {
			quickQuote.wrapper.append(quickQuote.loading).show();

			$.ajax({
				type:'GET',
				url: id == 0 ? quickQuote.createAction : quickQuote.updateAction+'&id='+id,
				dataType:'html'
			}).done(function (data) {
				quickQuote.loadForm(data);
				if(quickQuote.contact !== undefined)
					quickQuote.form.find('input[name="Quote[associatedContacts]"]').val(quickQuote.contact);
				if(quickQuote.account !== undefined)
					quickQuote.form.find('input[name="Quote[accountName]"]').val(quickQuote.account);
			}).fail(function(jqXHR, textStatus, errorThrown) {
				if(jqXHR.status != 0 && jqXHR.status != 400) {
					alert(textStatus+' '+jqXHR.status+' '+errorThrown);
					quickQuote.closeForm();
				} else {
					quickQuote.loadForm(jqXHR.responseText);
				}
			});
		} else {
			quickQuote.closeForm();
			quickQuote.openForm(id);
		}
	}

	/**
	 * Any extra javascript that needs to be run on the form for it to work properly.
	*/
	quickQuote.loadForm = function(markup) {
		quickQuote.wrapper.html(markup); // .on('keypress.hitenter','input',function(e) {if(e.keyCode==13) e.stopPropagation();});
		quickQuote.form = quickQuote.wrapper.find('form#quotes-form').first();
		quickQuote.form.find('#quote-save-button').click(function(e) {
			quickQuote.submitForm(e);
		});
		quickQuote.form.find('#quote-cancel-button').click(quickQuote.closeForm);
		quickQuote.form.find('#Quote_name').addClass('focus').focus();
		quickQuote.form.find('.x2-hint').qtip();
		// These things are last-minute stylistic adjustments and widget initializationst that don't happen because the scripts are never rende'
		quickQuote.form.find('div.x2-layout.form-view > div:last-child div.formInputBox').css({
			'margin-bottom':'-495px'
		});

	}

	quickQuote.submitForm = function(e) {
		if(e!==undefined) { // Function is being used as an event handler
			e.preventDefault();
		}
		// Add the loading gif:
		quickQuote.wrapper.find('h2').append(quickQuote.loadingImg.css({
			'margin-left':'20px'
		}));
		$.ajax({
			type: 'POST',
			url:quickQuote.form.attr('action'),
			dataType:'html',
			data: quickQuote.form.fadeTo(200,0.5).serialize()
		}).done(function(jqXHR, textStatus, errorThrown) {
			quickQuote.closeForm();
			quickQuote.reloadAll();
		}).fail(function(jqXHR, textStatus, errorThrown) {
			if(jqXHR.status != 0 && jqXHR.status != 400) {
				alert(quickQuote.failMessage+' '+textStatus+' '+jqXHR.status+' '+errorThrown);
				quickQuote.closeForm();
			} else {
				quickQuote.loadForm(jqXHR.responseText);
			}
		}).always(function(d) {
			quickQuote.loading.hide();
		});
	}

});