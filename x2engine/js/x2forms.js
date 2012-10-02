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
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
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

var x2CreateAccountDialog = null; // dialog box for creating a new action no the fly
var x2CreateOpportunityDialog = null; // dialog box for creating a new opportunity on the fly

$(function() {
	// $('div.x2-layout .formSection:not(.showSection) .tableWrapper').hide();

	// $('div.x2-layout .formItem').disableSelection();

	$('div.x2-layout .formSectionShow, .formSectionHide').click(function() {
		toggleFormSection($(this).closest('.formSection'));
		saveFormSections();
	});
	
	$('a#showAll, a#hideAll').click(function() {
		$('a#showAll, a#hideAll').toggleClass('hide');
		if($('#showAll').hasClass('hide')) {
			$('div.x2-layout .formSection:not(.showSection)').each(function() {
				if($(this).find('a.formSectionHide').length > 0)
					toggleFormSection(this);
			});
		} else {
			$('div.x2-layout .formSection.showSection').each(function() {
				if($(this).find('a.formSectionHide').length > 0)
					toggleFormSection(this);
			});
		}
	});

	$('.inlineLabel').find('input:text, textarea').focus(function() { formFieldFocus(this); }).blur(function() { formFieldBlur(this); });
	

	// set up x2 helper tooltips
	$('.x2-hint').qtip();
});


function toggleFormSection(section) {
	if($(section).hasClass('showSection'))
		$(section).find('.tableWrapper').slideToggle(400,function(){
			$(this).parent('.formSection').toggleClass('showSection');
			saveFormSections();
		});
	else {
		$(section).toggleClass('showSection').find('.tableWrapper').slideToggle(400);
		saveFormSections();
	}
}
function saveFormSections() {
	var formSectionStatus = [];
	$('div.x2-layout .formSection').each(function(i,section) {
		formSectionStatus[i] = $(section).hasClass('showSection')? '1' : '0';
	});
	var formSettings = '['+formSectionStatus.join(',')+']';
	$.ajax({
		url: yii.baseUrl+'/index.php/site/saveFormSettings',
		type: 'GET',
		data: 'formName='+window.formName+'&formSettings='+encodeURI(formSettings)
	});
}

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

// $(function() {
	// placeholderTest = document.createElement('input');
	// if(!('placeholder' in placeholderTest)) {
	
		// var active = document.activeElement;
		
		// $.delegate(':text','focus',function() {
			// if ($(this).attr('placeholder') != '' && $(this).val() == $(this).attr('placeholder'))
				// $(this).val('').removeClass('placeholder');
		// });
		
		// $.delegate(':text','blur',function() {
			// if ($(this).attr('placeholder') != '' && ($(this).val() == '' || $(this).val() == $(this).attr('placeholder')))
				// $(this).val($(this).attr('placeholder')).addClass('placeholder');
		// });
		// $(':text').blur();
		// $(active).focus();
		// $('form').submit(function () {
			// $(this).find('.placeholder').each(function() { $(this).val(''); });
		// });
	// }
// });

function submitForm(formName) {
	document.forms[formName].submit();
}
function toggleForm(formName,duration) {
	if($(formName).is(':hidden')) {
		$('html,body').animate({
			scrollTop: ($('#action-form').offset().top-200)
		}, 300);
	}
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

function fileUpload(form, fileField, action_url, remove_url) {
    // Create the iframe...
    var iframe = document.createElement("iframe");
    iframe.setAttribute("id", "upload_iframe");
    iframe.setAttribute("name", "upload_iframe");
    iframe.setAttribute("width", "0");
    iframe.setAttribute("height", "0");
    iframe.setAttribute("border", "0");
    iframe.setAttribute("style", "width: 0; height: 0; border: none;");
 
    // Add to document...
    form.parentNode.appendChild(iframe);
    window.frames['upload_iframe'].name = "upload_iframe";
 
    iframeId = document.getElementById("upload_iframe");
 
    // Add event...
    var eventHandler = function () {
 
            if (iframeId.detachEvent) iframeId.detachEvent("onload", eventHandler);
            else iframeId.removeEventListener("load", eventHandler, false);
 
            // Message from server...
            if (iframeId.contentDocument) {
                var content = iframeId.contentDocument.body.innerHTML;
            } else if (iframeId.contentWindow) {
                var content = iframeId.contentWindow.document.body.innerHTML;
            } else if (iframeId.document) {
                var content = iframeId.document.body.innerHTML;
            }
            
            var response = $.parseJSON(content)
             
            if(response['status'] == 'success') {
            	// success uploading temp file
            	// save it's name in the form so it gets attached when the user clicks send
            	var file = $('<input>', {
            		'type': 'hidden',
            		'name': 'AttachmentFiles[id][]',
            		'class': 'AttachmentFiles',
            		'value': response['id'], // name of temp file
            	});
            	
            	var temp = $('<input>', {
            		'type': 'hidden',
            		'name': 'AttachmentFiles[temp][]',
            		'value': true,
            	});
            	
            	var parent = fileField.parent().parent().parent();
            	
            	parent.parent().find('.error').html(''); // clear error messages
            	var newFileChooser = parent.clone(); // save copy of file upload span before we start making changes
            	
            	parent.removeClass('next-attachment');
            	parent.append(file);
            	parent.append(temp);
            	
            	var remove = $("<a>", {
            		'href': "#",
            		'html': "[x]",
            	});
            	
            	parent.find('.filename').html(response['name']);
            	parent.find('.remove').append(remove);
            	
            	remove.click(function() {removeAttachmentFile(remove.parent().parent(), remove_url); return false;});
            	
            	fileField.parent().parent().remove();
            	
            	parent.after(newFileChooser);
            	initX2FileInput();
            	
            } else {
            	fileField.parent().parent().parent().find('.error').html(response['message']);
            	fileField.val("");
            }
 			
            // Del the iframe...
            setTimeout('iframeId.parentNode.removeChild(iframeId)', 250);
        }
 
    if (iframeId.addEventListener) iframeId.addEventListener("load", eventHandler, true);
    if (iframeId.attachEvent) iframeId.attachEvent("onload", eventHandler);
 
    // Set properties of form...
    form.setAttribute("target", "upload_iframe");
    form.setAttribute("action", action_url);
    form.setAttribute("method", "post");
    form.setAttribute("enctype", "multipart/form-data");
    form.setAttribute("encoding", "multipart/form-data");
 
    // Submit the form...
    form.submit(); 
}

// remove an attachment that is stored on the server as a temp file
function removeAttachmentFile(attachment, remove_url) {
	var id = attachment.find(".AttachmentFiles");
	$.post(remove_url, {'id': id.val()});

	attachment.remove();
}

// set up x2 file input
// call this function everytime an x2 file input is created
function initX2FileInput() {
	// bind hover and click effects
	$('input.x2-file-input[type=file]').hover(function() {
		var button = $('input.x2-file-input[type=file]').next();
		if(button.hasClass('active') == false) {
			$('input.x2-file-input[type=file]').next().addClass('hover');
		}
	}, function() {
		$('input.x2-file-input[type=file]').next().removeClass('hover');
	});
	
	$('input.x2-file-input[type=file]').mousedown(function() {
		$('input.x2-file-input[type=file]').next().removeClass('hover');
		$('input.x2-file-input[type=file]').next().addClass('active');
	});
	
	$('body').mouseup(function() {
		$('input.x2-file-input[type=file]').next().removeClass('active');
	});
	
	// position the saving icon for uploading files
	// width
	var chooseFileButtonCenter = parseInt($('input.x2-file-input[type=file]').css('width'), 10)/2;
	var halfIconWidth = parseInt($('#choose-file-saving-icon').css('width'), 10)/2;
	var iconLeft = chooseFileButtonCenter - halfIconWidth;
	$('#choose-file-saving-icon').css('left', iconLeft + 'px');
	
	// height
	var chooseFileButtonCenter = parseInt($('input.x2-file-input[type=file]').css('height'), 10)/2;
	var halfIconHeight = parseInt($('#choose-file-saving-icon').height(), 10)/2;
	var iconTop = chooseFileButtonCenter - halfIconHeight;
	$('#choose-file-saving-icon').css('top', iconTop + 'px');

}

$.fn.initCreateAccountDialog = function () {

	if(x2CreateAccountDialog != null) {
		return; // don't create a 2nd dialog, if one already exists
	}
	
	x2CreateAccountDialog = $('<div></div>', {id: 'x2-create-action-dialog'});
	
	x2CreateAccountDialog.dialog({
	    title: 'Create Account', 
	    autoOpen: false,
	    resizable: true,
	    width: '650px',
	    show: 'fade',
	    hide: 'fade',
//	    buttons: boxButtons,
/*	    open: function() {
	        $('.ui-dialog-buttonpane').find('button:contains(\"' + focusButton + '\")')
	        	.css('background', '#579100')
	        	.css('color', 'white')
	        	.focus();
	        $('.ui-dialog-buttonpane').find('button').css('font-size', '0.85em');
	        $('.ui-dialog-title').css('font-size', '0.8em');
	        $('.ui-dialog-titlebar').css('padding', '0.2em 0.4em');
	        $(viewAccount).css('font-size', '0.75em');
	    }, */
	});
	
	x2CreateAccountDialog.data('inactive', true); // indicate that we can append a creat action page to this dialog
	
	$(this).click(function() {
		if($(this).data('createAccountUrl') != undefined) {
			if(x2CreateAccountDialog.data('inactive')) {
				$.post($(this).data('createAccountUrl'), {x2ajax: true}, function(response) {
					x2CreateAccountDialog.append(response);
					x2CreateAccountDialog.dialog('open');
					x2CreateAccountDialog.data('inactive', false); // indicate that a create-action page has been appended, don't do it until the old one is submitted or cleared.
					x2CreateAccountDialog.find('.formSectionHide').remove();
					submit = x2CreateAccountDialog.find('input[type="submit"]');
					form = x2CreateAccountDialog.find('form');
//					submit.attr('disabled', 'disabled');
					$(submit).click(function() {
						var formdata = form.serializeArray();
						var x2ajax = {}; // this form data object indicates this is an ajax request
						                 // note: yii already uses the name 'ajax' for it's ajax calls, so we use 'x2ajax'
						x2ajax['name'] = 'x2ajax';
						x2ajax['value'] = '1';
						formdata.push(x2ajax);
						$.post($('.create-account').data('createAccountUrl'), formdata, function(response) {
							response = $.parseJSON(response);
							if(response['status'] == 'success') {
								$('#Contacts_company').val(response['name']);
								$('#Contacts_company_id').val(response['id']);
								x2CreateAccountDialog.dialog('close');
							}
						});
						
						return false; // prevent html submit
					});
					$('#Accounts_phone').val($('div.formInputBox #Contacts_phone').val());
					$('#Accounts_website').val($('div.formInputBox #Contacts_website').val());
					$('#Accounts_assignedTo_assignedToDropdown').val($('#Contacts_assignedTo_assignedToDropdown').val());
				});
			} else {
				x2CreateAccountDialog.dialog('open');
			}
		}
	});
	
	return $(this);
}


$.fn.initCreateOpportunityDialog = function () {

	if(x2CreateOpportunityDialog != null) {
		return; // don't create a 2nd dialog, if one already exists
	}
	
	x2CreateOpportunityDialog = $('<div></div>', {id: 'x2-create-opportunity-dialog'});
	
	x2CreateOpportunityDialog.dialog({
	    title: 'Create Opportunity', 
	    autoOpen: false,
	    resizable: true,
	    width: '650px',
	    show: 'fade',
	    hide: 'fade',
//	    buttons: boxButtons,
/*	    open: function() {
	        $('.ui-dialog-buttonpane').find('button:contains(\"' + focusButton + '\")')
	        	.css('background', '#579100')
	        	.css('color', 'white')
	        	.focus();
	        $('.ui-dialog-buttonpane').find('button').css('font-size', '0.85em');
	        $('.ui-dialog-title').css('font-size', '0.8em');
	        $('.ui-dialog-titlebar').css('padding', '0.2em 0.4em');
	        $(viewAccount).css('font-size', '0.75em');
	    }, */
	});
	
	x2CreateOpportunityDialog.data('inactive', true); // indicate that we can append a creat action page to this dialog
	
	$(this).click(function() {
		if($(this).data('createOpportunityUrl') != undefined) {
			if(x2CreateOpportunityDialog.data('inactive')) {
				$.post($(this).data('createOpportunityUrl'), {x2ajax: true}, function(response) {
					x2CreateOpportunityDialog.append(response);
					x2CreateOpportunityDialog.dialog('open');
					x2CreateOpportunityDialog.data('inactive', false); // indicate that a create-action page has been appended, don't do it until the old one is submitted or cleared.
					x2CreateOpportunityDialog.find('.formSectionHide').remove();
					submit = x2CreateOpportunityDialog.find('input[type="submit"]');
					form = x2CreateOpportunityDialog.find('form');
//					submit.attr('disabled', 'disabled');
					$(submit).click(function() {
						var formdata = form.serializeArray();
						var x2ajax = {}; // this form data object indicates this is an ajax request
						                 // note: yii already uses the name 'ajax' for it's ajax calls, so we use 'x2ajax'
						x2ajax['name'] = 'x2ajax';
						x2ajax['value'] = '1';
						var modelName = {};
						modelName['name'] = 'ModelName';
						modelName['value'] = $('#create-opportunity').data('modelName');
						var modelId = {};
						modelId['name'] = 'ModelId';
						modelId['value'] = $('#create-opportunity').data('modelId');
						formdata.push(x2ajax);
						formdata.push(modelName);
						formdata.push(modelId);
						$.post($('#create-opportunity').data('createOpportunityUrl'), formdata, function(response) {
							response = $.parseJSON(response);
							if(response['status'] == 'success') {
								$.fn.yiiGridView.update('opportunities-grid');
								x2CreateOpportunityDialog.dialog('close');
								x2CreateOpportunityDialog.empty(); // clean up dialog
								$('body').off('click','#Opportunity_assignedTo_groupCheckbox'); // clean up javascript so we can open this window again without error
								x2CreateOpportunityDialog.data('inactive', true); // indicate that we can append a create action page to this dialog
								if($('#relationships-form').is(':hidden')) // show relationships if they are hidden
									toggleRelationshipsForm();

							}
						});
						
						return false; // prevent html submit
					});
					if($('#create-opportunity').data('account-name') != undefined)
						$('#Opportunity_accountName').val($('#create-opportunity').data('account-name'));
					if($('#create-opportunity').data('assigned-to') != undefined)
						$('#Opportunity_assignedTo_assignedToDropdown').val($('#create-opportunity').data('assigned-to'));
				});
			} else {
				x2CreateOpportunityDialog.dialog('open');
			}
		}
	});
	
	return $(this);
}



$.fn.initCreateAccountDialog2 = function () {

	if(x2CreateAccountDialog != null) {
		return; // don't create a 2nd dialog, if one already exists
	}
	
	x2CreateAccountDialog = $('<div></div>', {id: 'x2-create-action-dialog'});
	
	x2CreateAccountDialog.dialog({
	    title: 'Create Account', 
	    autoOpen: false,
	    resizable: true,
	    width: '650px',
	    show: 'fade',
	    hide: 'fade',
//	    buttons: boxButtons,
/*	    open: function() {
	        $('.ui-dialog-buttonpane').find('button:contains(\"' + focusButton + '\")')
	        	.css('background', '#579100')
	        	.css('color', 'white')
	        	.focus();
	        $('.ui-dialog-buttonpane').find('button').css('font-size', '0.85em');
	        $('.ui-dialog-title').css('font-size', '0.8em');
	        $('.ui-dialog-titlebar').css('padding', '0.2em 0.4em');
	        $(viewAccount).css('font-size', '0.75em');
	    }, */
	});
	
	x2CreateAccountDialog.data('inactive', true); // indicate that we can append a create action page to this dialog
	
	$(this).click(function() {
		if($(this).data('createAccountUrl') != undefined) {
			if(x2CreateAccountDialog.data('inactive')) {
				$.post($(this).data('createAccountUrl'), {x2ajax: true}, function(response) {
					x2CreateAccountDialog.append(response);
					x2CreateAccountDialog.dialog('open');
					x2CreateAccountDialog.data('inactive', false); // indicate that a create-action page has been appended, don't do it until the old one is submitted or cleared.
					x2CreateAccountDialog.find('.formSectionHide').remove();
					submit = x2CreateAccountDialog.find('input[type="submit"]');
					form = x2CreateAccountDialog.find('form');
//					submit.attr('disabled', 'disabled');
					$(submit).click(function() {
						return x2CreateAccountDialogHandleSubmit(form);
					});
					/*
						var formdata = form.serializeArray();
						var x2ajax = {}; // this form data object indicates this is an ajax request
						                 // note: yii already uses the name 'ajax' for it's ajax calls, so we use 'x2ajax'
						x2ajax['name'] = 'x2ajax';
						x2ajax['value'] = '1';
						var modelName = {};
						modelName['name'] = 'ModelName';
						modelName['value'] = $('#create-account').data('modelName');
						var modelId = {};
						modelId['name'] = 'ModelId';
						modelId['value'] = $('#create-account').data('modelId');
						formdata.push(x2ajax);
						formdata.push(modelName);
						formdata.push(modelId);
						$.post($('#create-account').data('createAccountUrl'), formdata, function(response) {
							response = $.parseJSON(response);
							if(response['status'] == 'success') {
								$.fn.yiiGridView.update('opportunities-grid');
								x2CreateAccountDialog.dialog('close');
								x2CreateAccountDialog.empty(); // clean up dialog
								$('body').off('click','#Accounts_assignedTo_groupCheckbox'); // clean up javascript so we can open this window again without error
								x2CreateAccountDialog.data('inactive', true); // indicate that we can append a create action page to this dialog
								showRelationships = true;
								if(response['primaryAccountLink'] != undefined) {
									if(response['primaryAccountLink'] != '') {
										$('#Contacts_company_field div.formInputBox').html(response['primaryAccountLink']);
										showRelationships = false;
									}
								}
								if($('#relationships-form').is(':hidden') && showRelationships) // show relationships if they are hidden
									toggleRelationshipsForm();
							} else if (response['status'] == 'userError') {
								if(response['page'] != undefined) {
									x2CreateAccountDialog.empty(); // clean up dialog
									$('body').off('click','#Accounts_assignedTo_groupCheckbox'); // clean up javascript so we can open this window again without error
									x2CreateAccountDialog.append(response['page']);
								}
							}
						});
						
						return false; // prevent html submit
					});
					*/
					if($('#create-account').data('phone') != undefined)
						$('#Accounts_phone').val($('#create-account').data('phone'));
					if($('#create-account').data('website') != undefined)
						$('#Accounts_website').val($('#create-account').data('website'));
					if($('#create-account').data('assigned-to') != undefined)
						$('#Accounts_assignedTo_assignedToDropdown').val($('#create-account').data('assigned-to'));
				});
			} else {
				x2CreateAccountDialog.dialog('open');
			}
		}
	});
	
	return $(this);
}

function x2CreateAccountDialogHandleSubmit(form) {
	var formdata = form.serializeArray();
	var x2ajax = {}; // this form data object indicates this is an ajax request
	                 // note: yii already uses the name 'ajax' for it's ajax calls, so we use 'x2ajax'
	x2ajax['name'] = 'x2ajax';
	x2ajax['value'] = '1';
	var modelName = {};
	modelName['name'] = 'ModelName';
	modelName['value'] = $('#create-account').data('modelName');
	var modelId = {};
	modelId['name'] = 'ModelId';
	modelId['value'] = $('#create-account').data('modelId');
	formdata.push(x2ajax);
	formdata.push(modelName);
	formdata.push(modelId);
	$.post($('#create-account').data('createAccountUrl'), formdata, function(response) {
	    response = $.parseJSON(response);
	    if(response['status'] == 'success') {
	    	$.fn.yiiGridView.update('opportunities-grid');
	    	x2CreateAccountDialog.dialog('close');
	    	x2CreateAccountDialog.empty(); // clean up dialog
	    	$('body').off('click','#Accounts_assignedTo_groupCheckbox'); // clean up javascript so we can open this window again without error
	    	x2CreateAccountDialog.data('inactive', true); // indicate that we can append a create action page to this dialog
	    	showRelationships = true;
	    	if(response['primaryAccountLink'] != undefined) {
	    		if(response['primaryAccountLink'] != '') {
	    			$('#Contacts_company_field div.formInputBox').html(response['primaryAccountLink']);
	    			showRelationships = false;
	    		}
	    	}
	    	if($('#relationships-form').is(':hidden') && showRelationships) // show relationships if they are hidden
	    		toggleRelationshipsForm();
	    } else if (response['status'] == 'userError') {
	    	if(response['page'] != undefined) {
	    		x2CreateAccountDialog.empty(); // clean up dialog
	    		$('body').off('click','#Accounts_assignedTo_groupCheckbox'); // clean up javascript so we can open this window again without error
	    		x2CreateAccountDialog.append(response['page']);
				x2CreateAccountDialog.find('.formSectionHide').remove();
				submit = x2CreateAccountDialog.find('input[type="submit"]');
				form = x2CreateAccountDialog.find('form');
//				submit.attr('disabled', 'disabled');
				$(submit).click(function() {
				    return x2CreateAccountDialogHandleSubmit(form);
				});
	    	}
	    }
	});
	
	return false; // prevent html submit
}



function toggleRelationshipsForm() {			
    if($('#relationships-form').is(':hidden')) {
    	$('.focus-mini-module').removeClass('focus-mini-module');
    	$('#relationships-form').find('.form').addClass('focus-mini-module');
//    	$('html,body').animate({
//    		scrollTop: ($('#publisher-form').offset().top - 200)
//    	}, 300);
    }
    $('#relationships-form').animate({
    	opacity: 'toggle',
    	height: 'toggle'
    }, 300);
}

