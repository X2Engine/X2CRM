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

var x2CreateContactDialog = null; // dialog box for creating a new contact on the fly
var x2CreateAccountDialog = null; // dialog box for creating a new action on the fly
var x2CreateOpportunityDialog = null; // dialog box for creating a new opportunity on the fly
var x2CreateCaseDialog = null; // dialog box for creating a new service case on the fly

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
	    hide: 'fade'
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
	    hide: 'fade'
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
	    	if($('#relationships-grid').length == 1) {
	    		$.fn.yiiGridView.update('relationships-grid');
	    	}
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
	    	
	    	if(response['name'] != undefined && response['id'] != undefined && $('#Services_contactId').length == 1 && $('#Services_contactId_id').length == 1) {
	    		// Services Module uses this to set field 'Contact'
	    		$('#Services_contactId').val(response['name']);
	    		$('#Services_contactId_id').val(response['id']);
	    	}
	    
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
	    hide: 'fade'
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
								$.fn.yiiGridView.update('relationships-grid');
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
				},'html');
			} else {
				x2CreateOpportunityDialog.dialog('open');
			}
		}
	});
	
	return $(this);
}







/**
 * Initialize a Dialog to create a Service Case
 *
 * Use a JQuery selector to add this function to an element. When the element is
 * clicked it will open a dialog to create a new Service Case. When the Case is saved
 * a new relationship is created between the new Case and the model with the id modelId.
 * This function is used in the view for Contacts.
 *
 * @param string createCaseUrl url for creating a new Service Case
 * @param string modelName model type (Contacts) for the relationship
 * @param int modelId id of the model for the relationship
 * @param string contactName Contact name to associate with the new Case (can be blank)
 * @param string assignedTo user to assign new Case to
 * @param string tooltip text for when the mouse hovers over the button that creates the dialog
 *
 */
$.fn.initCreateCaseDialog = function (createCaseUrl, modelName, modelId, contactName, assignedTo, tooltip) {

	$(this).data('createCaseUrl', createCaseUrl);
	$(this).data('modelName', modelName);
	$(this).data('modelId', modelId);
	$(this).data('contact-name', contactName);
	$(this).data('assigned-to', assignedTo);
	$(this).qtip({content: tooltip});

	if(x2CreateCaseDialog != null) {
		return; // don't create a 2nd dialog, if one already exists
	}
	
	x2CreateCaseDialog = $('<div></div>', {id: 'x2-create-opportunity-dialog'});
	
	x2CreateCaseDialog.dialog({
	    title: 'Create Case', 
	    autoOpen: false,
	    resizable: true,
	    width: '650px',
	    show: 'fade',
	    hide: 'fade'
	});
	
	x2CreateCaseDialog.data('inactive', true); // indicate that we can append a creat action page to this dialog
	
	$(this).click(function() {
		if($(this).data('createCaseUrl') != undefined) {
			if(x2CreateCaseDialog.data('inactive')) {
				$.post($(this).data('createCaseUrl'), {x2ajax: true}, function(response) {
					x2CreateCaseDialog.append(response);
					x2CreateCaseDialog.dialog('open');
					x2CreateCaseDialog.data('inactive', false); // indicate that a create-case page has been appended, don't do it until the old one is submitted or cleared.
					x2CreateCaseDialog.find('.formSectionHide').remove();
					submit = x2CreateCaseDialog.find('input[type="submit"]');
					form = x2CreateCaseDialog.find('form');
//					submit.attr('disabled', 'disabled');
					$(submit).click(function() {
						 return x2CreateCaseDialogHandleSubmit(form);
					});
					if($('#create-case').data('contact-name') != undefined)
						$('#Services_contactId').val($('#create-case').data('contact-name'));
					if($('#create-case').data('assigned-to') != undefined)
						$('#Services_assignedTo_assignedToDropdown').val($('#create-case').data('assigned-to'));
				});
			} else {
				x2CreateCaseDialog.dialog('open');
			}
		}
	});
	
	return $(this);
}

function x2CreateCaseDialogHandleSubmit(form) {
	var formdata = form.serializeArray();
	var x2ajax = {}; // this form data object indicates this is an ajax request
	                 // note: yii already uses the name 'ajax' for it's ajax calls, so we use 'x2ajax'
	x2ajax['name'] = 'x2ajax';
	x2ajax['value'] = '1';
	var modelName = {};
	modelName['name'] = 'ModelName';
	modelName['value'] = $('#create-case').data('modelName');
	var modelId = {};
	modelId['name'] = 'ModelId';
	modelId['value'] = $('#create-case').data('modelId');
	formdata.push(x2ajax);
	formdata.push(modelName);
	formdata.push(modelId);
	$.post($('#create-case').data('createCaseUrl'), formdata, function(response) {
	    response = $.parseJSON(response);
	    if(response['status'] == 'success') {
	    	$.fn.yiiGridView.update('relationships-grid');
	    	x2CreateCaseDialog.dialog('close');
	    	x2CreateCaseDialog.empty(); // clean up dialog
	    	$('body').off('click','#Services_assignedTo_groupCheckbox'); // clean up javascript so we can open this window again without error
	    	x2CreateCaseDialog.data('inactive', true); // indicate that we can append a create action page to this dialog
	    	if($('#relationships-form').is(':hidden')) // show relationships if they are hidden
	    		toggleRelationshipsForm();
	    
	    } else if (response['status'] == 'userError') {
	    	if(response['page'] != undefined) {
	    		x2CreateCaseDialog.empty(); // clean up dialog
	    		$('body').off('click','#Services_assignedTo_groupCheckbox'); // clean up javascript so we can open this window again without error
	    		x2CreateCaseDialog.append(response['page']);
				x2CreateCaseDialog.find('.formSectionHide').remove();
				submit = x2CreateCaseDialog.find('input[type="submit"]');
				form = x2CreateCaseDialog.find('form');
//				submit.attr('disabled', 'disabled');
				$(submit).click(function() {
				    return x2CreateCaseDialogHandleSubmit(form);
				});
	    	}
	    }
	});
	
	return false; // prevent html submit
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
	    hide: 'fade'
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
	    	$.fn.yiiGridView.update('relationships-grid');
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
