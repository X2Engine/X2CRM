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




// Quick quote create javascript.
// To use: just stick a hidden div with id="quote-form-wrapper" somewhere and
// register a script that declares a "x2.inlineQuotes" object within x2 with the
// following properties:
// - contact (optional) name of associated contact
// - account (optional) name of associated account
// - failMessage (required) translated message when the AJAX request to create the quote fails

if(typeof x2 == 'undefined')
    x2 = {};

if(typeof x2.inlineQuotes == 'undefined')
    x2.inlineQuotes = {};

jQuery(document).ready(function ($) {

// eventually all inline quotes code should all be moved into this class
var InlineQuotes = (function () {

function InlineQuotes (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.Widget.call (this, argsDict);
    this._init ();
}

InlineQuotes.prototype = auxlib.create (x2.Widget.prototype);

InlineQuotes.prototype._init = function () {
    this.element$.find ('.widget-close-button').click (function () {
        x2.inlineQuotes.toggle ();
        return false;
    });
};

return InlineQuotes;

}) ();

	// Declare all properties required for proper function
	x2.inlineQuotes.declare = function() {

        /* x2.quotes is used by _lineItems.php partial to determine view-dependent behavior of 
           quote table */
        x2.quotes = {};
        x2.quotes.view = "x2.inlineQuotes";
        x2.inlineQuotes.obj = new InlineQuotes ({
            element: $('#wide-quote-form')
        });

		// Basic properties:
		x2.inlineQuotes.wrapper = $('#quote-create-form-wrapper').first();
		x2.inlineQuotes.wrapperOriginalHtml = undefined;
		x2.inlineQuotes.loadingImg = $('<img>',{
			src:yii.themeBaseUrl+'/images/loading.gif',
			height:32,
			width:32
		}).css({
			'display':'inline-block'
		});
		x2.inlineQuotes.loading = $('<div>').css({
			'text-align':'center',
			'display':'block'
		}).append(x2.inlineQuotes.loadingImg);
		x2.inlineQuotes.updatingId = 0;

		// Inline email properties:
		x2.inlineQuotes.inlineEmailModule = $('#inline-email-form');
		x2.inlineQuotes.inlineEmailModelName = x2.inlineQuotes.inlineEmailModule.find('input[name="InlineEmail[modelName]"]');
		x2.inlineQuotes.inlineEmailModelId = x2.inlineQuotes.inlineEmailModule.find('input[name="InlineEmail[modelId]"]');

		x2.inlineQuotes.inlineEmailMode = (typeof x2.inlineQuotes.inlineEmailMode != 'undefined') ? x2.inlineQuotes.inlineEmailMode : 'default';
		// Copy current attributes:
		if(typeof window.inlineEmailEditor !== 'undefined' && x2.inlineQuotes.inlineEmailMode == 'default') {
			x2.inlineQuotes.setInlineEmailConfig();
		}

        // Miscellaneous actions to take at document load and widget reset time:
        if(x2.inlineQuotes.sendingQuote) {
            x2.inlineQuotes.sendingQuote = false;
        } else {
            if(!$('#quotes-form').find('.wide.form').hasClass('focus-mini-module')) {
                $('.focus-mini-module').removeClass('focus-mini-module');
                $('#quotes-form').find('.wide.form').addClass('focus-mini-module');
            }
        }
    }

	x2.inlineQuotes.moveWrapper = function(sel) {
		x2.inlineQuotes.wrapper = $(sel);
		x2.inlineQuotes.wrapperOriginalHtml = x2.inlineQuotes.wrapper.html();
	}

	// Close and reset the form.
	x2.inlineQuotes.closeForm = function(e) {
		if(e!==undefined) { // Function is being used as an event handler
			e.stopPropagation();
			e.preventDefault();
		}
		if(typeof x2.inlineQuotes.wrapperOriginalHtml != 'undefined') {
			x2.inlineQuotes.wrapper.html(x2.inlineQuotes.wrapperOriginalHtml); // Reset wrapper contents
		} else {
			x2.inlineQuotes.wrapper.hide().html(''); // Simply close
		}
		x2.inlineQuotes.declare(); // Reset all properties
		$('#show-new-quote-button').show();
	}

	// This reloads everything...the entire quote form.
	x2.inlineQuotes.reloadAll = function () {
		$.ajax({
			url:x2.inlineQuotes.reloadAction
		}).done(function(html) {
			$('#quote-form-wrapper').html(html).find('#quotes-form .wide.form').addClass('focus-mini-module');
			x2.inlineQuotes.declare();
            if (x2.inlineRelationshipsWidget)
                x2.inlineRelationshipsWidget.refresh ();
            x2.actionHistory.update ();
            x2.TransactionalViewWidget.refresh ('QuotesWidget'); 
			$('html,body').animate({
				scrollTop: x2.inlineQuotes.updatingId ? 
                    $('#quote-detail-' + id).offset().top : 
                    x2.inlineQuotes.wrapper.parents('#quote-form-wrapper').first().offset().top
			},300);
		});
	}

	x2.inlineQuotes.openForm = function(id,duplicate) {
		$('#show-new-quote-button').hide();
		if(!x2.inlineQuotes.wrapper.is(':hidden'))
			x2.inlineQuotes.closeForm();
		id = typeof id === 'undefined' ? 0 : id;
		if(typeof duplicate == 'undefined') { // If not duplicating, we must be updating.
			x2.inlineQuotes.updatingId = id;
			if(id != 0)
				x2.inlineQuotes.moveWrapper('#quote-detail-' + id);
		}

		x2.inlineQuotes.wrapper.append(x2.inlineQuotes.loading).show();
		
		$.ajax({
			type:'GET',
			url: (id == 0 ? x2.inlineQuotes.createAction + (typeof duplicate == 'undefined' ? '' : '&duplicate='+duplicate) : x2.inlineQuotes.updateAction+'&id='+id),
			dataType:'html'
		}).done(function (data) {
			x2.inlineQuotes.loadForm(data);
			if(x2.inlineQuotes.contact !== undefined)
				x2.inlineQuotes.form.find('input[name="Quote[associatedContacts]"]').val(x2.inlineQuotes.contact);
			if(x2.inlineQuotes.account !== undefined)
				x2.inlineQuotes.form.find('input[name="Quote[accountName]"]').val(x2.inlineQuotes.account);
		}).fail(function(jqXHR, textStatus, errorThrown) {
			if(jqXHR.status != 0 && jqXHR.status != 400) {
				alert(textStatus+' '+jqXHR.status+' '+errorThrown);
				x2.inlineQuotes.closeForm();
			} else {
				x2.inlineQuotes.loadForm(jqXHR.responseText);
			}
		});
	}

	/**
	 * Any extra javascript that needs to be run on the form for it to work properly.
	*/
	x2.inlineQuotes.loadForm = function(markup) {
		x2.inlineQuotes.wrapper.html(markup); // .on('keypress.hitenter','input',function(e) {if(e.keyCode==13) e.stopPropagation();});
		x2.inlineQuotes.form = x2.inlineQuotes.wrapper.find('form#quotes-form').first();
		x2.inlineQuotes.form.find('#quote-save-button').click(function(e) {
			x2.inlineQuotes.submitForm(e);
		});
		x2.inlineQuotes.form.find('#quote-cancel-button').click(x2.inlineQuotes.closeForm);
		x2.inlineQuotes.form.find('#Quote_name').addClass('focus').focus();
		x2.inlineQuotes.form.find('.x2-hint').qtip();
		// These things are last-minute stylistic adjustments and widget initializationst that don't happen because the scripts are never rende'
		x2.inlineQuotes.form.find('div.x2-layout.form-view > div:last-child div.formInputBox').css({
			//'margin-bottom':'-495px'
		});
//		$('html,body').animate({
//			scrollTop: x2.inlineQuotes.wrapper.offset().top
//		},300);

	}

	x2.inlineQuotes.submitForm = function(e) {
		if(typeof e!=='undefined') { // Function is being used as an event handler
			e.preventDefault();
		}

        if (!x2.quoteslineItems.validateAllInputs () /* defined in lineItems.js */) {
           return false; 
        }

		// Add the loading gif:
		x2.inlineQuotes.wrapper.find('h2').append(x2.inlineQuotes.loadingImg.css({
			'margin-left':'20px'
		}));
		$.ajax({
			type: 'POST',
			url:x2.inlineQuotes.form.attr('action'),
			dataType:'html',
			data: x2.inlineQuotes.form.fadeTo(200,0.5).serialize()
		}).done(function(jqXHR, textStatus, errorThrown) {
			x2.inlineQuotes.closeForm();
			x2.inlineQuotes.reloadAll();
		}).fail(function(jqXHR, textStatus, errorThrown) {
			if(jqXHR.status != 0 && jqXHR.status != 400) {
				alert(x2.inlineQuotes.failMessage+' '+textStatus+' '+jqXHR.status+' '+errorThrown);
				x2.inlineQuotes.closeForm();
			} else {
				x2.inlineQuotes.loadForm(jqXHR.responseText);
			}
		}).always(function(d) {
			x2.inlineQuotes.loading.hide();
		});
	}

	// Restores the local inline email form to its state before using it to issue quotes.
	x2.inlineQuotes.resetInlineEmail = function() {
		x2.inlineQuotes.inlineEmailModelName.val(x2.inlineQuotes.inlineEmailConfig.modelName);
		x2.inlineQuotes.inlineEmailModelId.val(x2.inlineQuotes.inlineEmailConfig.modelId);
		x2.inlineQuotes.inlineEmailModule.find('select#email-template').html(x2.inlineQuotes.inlineEmailConfig.templateList);
		if(typeof x2 != 'undefined')
			x2.insertableAttributes = x2.inlineQuotes.inlineEmailConfig.insertableAttributes;
	}

	// Switches the inline email form into quote mode for issuing via email:
	x2.inlineQuotes.setInlineEmail = function(id,template) {
		template = typeof template == 'undefined' ? 0 : (template == null ? 0 : template);
		// Warn the user we're switching into quote issue mode:
		if (!inlineEmailSwitchConfirm())
			return false;
		x2.inlineQuotes.inlineEmailMode = 'quote';
		x2.inlineQuotes.inlineEmailModelName.val('Quote');
		x2.inlineQuotes.inlineEmailModelId.val(id);
		x2.inlineQuotes.inlineEmailModule.find('select#email-template').val(0);
		// Set up initial quote email by requesting a template change from the server:
		$.ajax({
			'type':'POST',
			'url':yii.scriptUrl+'/contacts/inlineEmail?ajax=1&template=1&loadTemplate='+template,
			'data':x2.inlineQuotes.inlineEmailModule.find("form").serialize(),
			'beforeSend':function() {
				$('#email-sending-icon').show();
			}

		}).done(function(data, textStatus, jqXHR) {
			// Update the list of templates:
			var tmplSelect = $('select[name="InlineEmail[template]"]'); // Template selector
			var selTemplate = tmplSelect.val(); // Currently selected template
			// Load new template list:
			if(typeof data.templateList != 'undefined') {
				tmplSelect.html(''); // Empty the current list
				var tmpl;
				for(var i=0;i<data.templateList.length; i++) {
					tmpl = data.templateList[i];
					var elt = $('<option>');
					elt.attr({
						value:tmpl.id,
						selected:(tmpl.id==selTemplate?'selected':'')
					}).text(tmpl.name);
					tmplSelect.append(elt);
				}
			}
			// Set the insertable attributes:
			if(typeof x2 != 'undefined' && typeof data.insertableAttributes != 'undefined')
				x2.insertableAttributes = data.insertableAttributes;
			// Close the form if it's open already:
			if(!$('#inline-email-form').is(':hidden'))
				toggleEmailForm('quote');
			// Now open (or re-open) with new quote-related settings:
			toggleEmailForm('quote');
			// Load data:
			$('input[name="InlineEmail[subject]"]').val(data.attributes.subject);
			window.inlineEmailEditor.setData(data.attributes.message);
		}).always(function(){
			$('#email-sending-icon').hide();
		});
	}

    x2.inlineQuotes.setInlineEmailConfig = function() {
        // This stores the original value of insertable attributes, for switching between quote forms:
        x2.inlineQuotes.inlineEmailConfig = {
            insertableAttributes:{},
            modelName:'',
            modelId:''
        };
        $.extend(x2.inlineQuotes.inlineEmailConfig.insertableAttributes,x2.insertableAttributes);
        x2.inlineQuotes.inlineEmailConfig.modelName = x2.inlineQuotes.inlineEmailModelName.val();
        x2.inlineQuotes.inlineEmailConfig.modelId = x2.inlineQuotes.inlineEmailModelId.val();
        x2.inlineQuotes.inlineEmailConfig.templateList = x2.inlineQuotes.inlineEmailModule.find('select#email-template').html();
        x2.inlineQuotes.inlineEmailConfig.insertableAttributes = (typeof x2.insertableAttributes != 'undefined')?x2.insertableAttributes:{};
    }

    x2.inlineQuotes.toggle = function () {
        var wasHidden = $('#quotes-form').is(':hidden');
        if(wasHidden) {
            $('.focus-mini-module').removeClass('focus-mini-module');
            $('#quotes-form').find('.wide.form').addClass('focus-mini-module');
            $('html,body').animate({
                scrollTop: ($('#quote-form-wrapper').offset().top - 100)
            }, 300);
        }
        $('#quotes-form').toggle('blind',300,function() {
            $('#quotes-form').focus();
        });
        if(!wasHidden) {
            $('html,body').animate({
                scrollTop: ($('body').offset().top)
            }, 300);
        }
    }

    x2.inlineQuotes.sendEmail = function (quoteId,quoteTemplate) {  // fill the inline email form with some info about a quote: name, table of products, description
        x2.inlineQuotes.setInlineEmail(quoteId,quoteTemplate);
        x2.inlineQuotes.sendingQuote = true; // stop quote mini-module from stealing focus away from email
    };

    x2.inlineQuotes.toggleUpdateQuote = function (id, locked, strict) {
        var confirmBox = $('<div></div>')
        .html(x2.inlineQuotes.lockedMessage)
        .dialog({
            title: x2.inlineQuotes.lockedDialogTitle,
            autoOpen: false,
            resizable: false,
            buttons: {
                'Yes': function() {
                    $(this).dialog('close');
                    x2.inlineQuotes.openForm(id);
                },
                'No': function() {
                    $(this).dialog('close');
                }
            }
        });

        var denyBox = $('<div></div>')
        .html(x2.inlineQuotes.deniedMessage)
        .dialog({
            title: x2.inlineQuotes.lockedDialogTitle,
            autoOpen: false,
            resizable: false,
            buttons: {
                'OK': function() {
                    $(this).dialog('close');
                }
            }
        });

        if(locked)
            if(strict)
                denyBox.dialog('open');
            else
                confirmBox.dialog('open');
        else {
            x2.inlineQuotes.openForm(id);
        }
    }

	x2.inlineQuotes.declare();

});
