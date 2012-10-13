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

var x2CreateContactDialog = null; // dialog box for creating a new contact on the fly
var x2CreateAccountDialog = null; // dialog box for creating a new action on the fly
var x2CreateOpportunityDialog = null; // dialog box for creating a new opportunity on the fly

/**
 * Create an account dialog when the '+' next to the Account field in Contacts
 * form is clicked.
 *
 * Use a JQuery selector to add this function to an element. It will open a
 * dialog to create a new account when the element is clicked. When the Account is
 * created, the new Account name will be placed in the Account field for the Contact.
 *
 */
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


/**
 * Initialize a Dialog to create a Contact
 *
 * Use a JQuery selector to add this function to an element. When the element is
 * clicked it will open a dialog to create a new Contact. When the contact is saved
 * a new relationship is created between the new Contact and the model with the id modelId.
 * This function is used in the view for Accounts and Opportunity.
 *
 * @param string createContactUrl url for creating a new Contact
 * @param string modelName model type (Accounts or Opportunity) for the relationship
 * @param int modelId id of the model for the relationship
 * @param string accountName Account name to associate with the new contact (can be blank)
 * @param string assignedTo user to assign new Contact to
 * @param string phone phone for new Contact (can be blank)
 * @param string website website for new Contact (can be blank)
 * @param string tooltip text for when the mouse hovers over the button that creates the dialog
 *
 */
$.fn.initCreateContactDialog = function (createContactUrl, modelName, modelId, accountName, assignedTo, phone, website, tooltip) {

	$('#create-contact').data('createContactUrl', createContactUrl);
	$('#create-contact').data('modelName', modelName);
	$('#create-contact').data('modelId', modelId);
	$('#create-contact').data('account-name', accountName);
	$('#create-contact').data('assigned-to', assignedTo);
	$('#create-contact').data('phone', phone);
	$('#create-contact').data('website', website);
	$('#create-contact').qtip({content: tooltip});

	if(x2CreateContactDialog != null) {
		return; // don't create a 2nd dialog, if one already exists
	}
	
	x2CreateContactDialog = $('<div></div>', {id: 'x2-create-contact-dialog'});
	
	x2CreateContactDialog.dialog({
	    title: 'Create Contact', 
	    autoOpen: false,
	    resizable: true,
	    width: '650px',
	    show: 'fade',
	    hide: 'fade',
	});
	
	x2CreateContactDialog.data('inactive', true); // indicate that we can append a creat action page to this dialog
	
	$(this).click(function() {
		if($(this).data('createContactUrl') != undefined) {
			if(x2CreateContactDialog.data('inactive')) {
				$.post($(this).data('createContactUrl'), {x2ajax: true}, function(response) {
					x2CreateContactDialog.append(response);
					x2CreateContactDialog.dialog('open');
					x2CreateContactDialog.data('inactive', false); // indicate that a create-action page has been appended, don't do it until the old one is submitted or cleared.
					x2CreateContactDialog.find('.formSectionHide').remove();
					x2CreateContactDialog.find('.create-account').remove();
					submit = x2CreateContactDialog.find('input[type="submit"]');
					form = x2CreateContactDialog.find('form');
//					submit.attr('disabled', 'disabled');
					$(submit).click(function() {
						return x2CreateContactDialogHandleSubmit(form);
					});
					if($('#create-contact').data('account-name') != undefined)
						$('#Contacts_company').val($('#create-contact').data('account-name'));
					if($('#create-contact').data('assigned-to') != undefined)
						$('#Contacts_assignedTo_assignedToDropdown').val($('#create-contact').data('assigned-to'));
					if($('#create-contact').data('phone') != undefined)
						$('div.formInputBox #Contacts_phone').val($('#create-contact').data('phone'));
					if($('#create-contact').data('website') != undefined)
						$('#Contacts_website').val($('#create-contact').data('website'));
				});
			} else {
				x2CreateContactDialog.dialog('open');
			}
		}
	});
	
	return $(this);
}



function x2CreateContactDialogHandleSubmit(form) {
	var formdata = form.serializeArray();
	var x2ajax = {}; // this form data object indicates this is an ajax request
	                 // note: yii already uses the name 'ajax' for it's ajax calls, so we use 'x2ajax'
	x2ajax['name'] = 'x2ajax';
	x2ajax['value'] = '1';
	var modelName = {};
	modelName['name'] = 'ModelName';
	modelName['value'] = $('#create-contact').data('modelName');
	var modelId = {};
	modelId['name'] = 'ModelId';
	modelId['value'] = $('#create-contact').data('modelId');
	formdata.push(x2ajax);
	formdata.push(modelName);
	formdata.push(modelId);
	$.post($('#create-contact').data('createContactUrl'), formdata, function(response) {
	    response = $.parseJSON(response);
	    if(response['status'] == 'success') {
	    	$.fn.yiiGridView.update('opportunities-grid');
	    	x2CreateContactDialog.dialog('close');
	    	x2CreateContactDialog.empty(); // clean up dialog
	    	$('body').off('click','#Contact_assignedTo_groupCheckbox'); // clean up javascript so we can open this window again without error
	    	x2CreateContactDialog.data('inactive', true); // indicate that we can append a create action page to this dialog
	    	if(response['primaryAccountLink'] != undefined) {
	    		if(response['primaryAccountLink'] != '') {
	    			$('#Opportunity_accountName_field div.formInputBox').html(response['primaryAccountLink']);
	    			showRelationships = false;
	    		}
	    	}
	    	if(response['newPhone'] != undefined) {
	    		if(response['newPhone'] != '') {
	    			$('#Accounts_phone_field div.formInputBox').html(response['newPhone']);
	    		}
	    	}
	    	if(response['newWebsite'] != undefined) {
	    		if(response['newWebsite'] != '') {
	    			$('#Accounts_website_field div.formInputBox').html(response['newWebsite']);
	    		}
	    	}
	    	if($('#relationships-form').is(':hidden')) // show relationships if they are hidden
	    		toggleRelationshipsForm();
	    
	    } else if (response['status'] == 'userError') {
	    	if(response['page'] != undefined) {
	    		x2CreateContactDialog.empty(); // clean up dialog
	    		$('body').off('click','#Contact_assignedTo_groupCheckbox'); // clean up javascript so we can open this window again without error
	    		x2CreateContactDialog.append(response['page']);
				x2CreateContactDialog.find('.formSectionHide').remove();
				x2CreateContactDialog.find('.create-account').remove();
				submit = x2CreateContactDialog.find('input[type="submit"]');
				form = x2CreateContactDialog.find('form');
//				submit.attr('disabled', 'disabled');
				$(submit).click(function() {
				    return x2CreateContactDialogHandleSubmit(form);
				});
	    	}
	    }
	});
	
	return false; // prevent html submit
}




/**
 * Initialize a Dialog to create an Opportunity
 *
 * Use a JQuery selector to add this function to an element. When the element is
 * clicked it will open a dialog to create a new Opportunity. When the Opportunity is saved
 * a new relationship is created between the new Opportunity and the model with the id modelId.
 * This function is used in the view for Accounts and Contacts.
 *
 * @param string createOpportunityUrl url for creating a new Opportunity
 * @param string modelName model type (Accounts or Contacts) for the relationship
 * @param int modelId id of the model for the relationship
 * @param string accountName Account name to associate with the new Opportunity (can be blank)
 * @param string assignedTo user to assign new Opportunity to
 * @param string tooltip text for when the mouse hovers over the button that creates the dialog
 *
 */
$.fn.initCreateOpportunityDialog = function (createOpportunityUrl, modelName, modelId, accountName, assignedTo, tooltip) {

	$(this).data('createOpportunityUrl', createOpportunityUrl);
	$(this).data('modelName', modelName);
	$(this).data('modelId', modelId);
	$(this).data('account-name', accountName);
	$(this).data('assigned-to', assignedTo);
	$(this).qtip({content: tooltip});

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



/**
 * Initialize a Dialog to create an Account
 *
 * Use a JQuery selector to add this function to an element. When the element is
 * clicked it will open a dialog to create a new Account. When the Account is saved
 * a new relationship is created between the new Account and the model with the id modelId.
 * This function is used in the view for Accounts and Opportunity.
 *
 * @param string createAccountUrl url for creating a new Account
 * @param string modelName model type (Accounts or Opportunity) for the relationship
 * @param int modelId id of the model for the relationship
 * @param string accountName Name for the new Account (can be blank)
 * @param string assignedTo user assigned to new Account
 * @param string phone phone for new Account (can be blank)
 * @param string website website for new Account (can be blank)
 * @param string tooltip text for when the mouse hovers over the button that creates the dialog
 *
 */
$.fn.initCreateAccountDialog2 = function (createAccountUrl, modelName, modelId, accountName, assignedTo, phone, website, tooltip) {

	$(this).data('createAccountUrl', createAccountUrl);
	$(this).data('modelName', modelName);
	$(this).data('modelId', modelId);
	$(this).data('account-name', accountName);
	$(this).data('assigned-to', assignedTo);
	$(this).data('phone', phone);
	$(this).data('website', website);
	$(this).qtip({content: tooltip});

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
					$('#Opportunity_accountName_field div.formInputBox').html(response['primaryAccountLink']);
	    			showRelationships = false;
	    		}
	    	}
	    	if(response['newPhone'] != undefined) {
	    		if(response['newPhone'] != '') {
	    			$('#Contacts_phone_field div.formInputBox').html(response['newPhone']);
	    		}
	    	}
	    	if(response['newWebsite'] != undefined) {
	    		if(response['newWebsite'] != '') {
	    			$('#Contacts_website_field div.formInputBox').html(response['newWebsite']);
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
    	$('#relationships-form').children('.form').addClass('focus-mini-module');
//    	$('html,body').animate({
//    		scrollTop: ($('#publisher-form').offset().top - 200)
//    	}, 300);
    }
    $('#relationships-form').animate({
    	opacity: 'toggle',
    	height: 'toggle'
    }, 300);
}
