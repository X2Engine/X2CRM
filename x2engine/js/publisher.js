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

// called when a tab is clicked
function tabSelected(event, ui) {
	// set field SelectedTab for use in POST request
	$('#SelectedTab').val(ui.newTab.attr('aria-controls'));

	if(ui.newTab.attr('aria-controls') == 'new-event') {
		// switch labels Due Date vs Start Date
		$('#due-date-label').css('display', 'none');
		$('#start-date-label').css('display', 'block');

		// show end date
		$('#end-date-label').css('display', 'block');
		$('#end-date-input').css('display', 'inline-block');

		// show action-event-panel
		$('#action-event-panel').css('display', 'block');

	} else if(ui.newTab.attr('aria-controls') == 'new-action') {
		$('#due-date-label').css('display', 'block');
		$('#start-date-label').css('display', 'none');

		// hide end date
		$('#end-date-label').css('display', 'none');
		$('#end-date-input').css('display', 'none');

		// show action-event-panel
		$('#action-event-panel').css('display', 'block');
	} else if(ui.newTab.attr('aria-controls') == 'log-a-call') {
		// hide action-event-panel
		$('#action-event-panel').css('display', 'none');
	} else if(ui.newTab.attr('aria-controls') == 'new-comment') {
		// hide action-event-panel
		$('#action-event-panel').css('display', 'none');
	}
}

// Sanity check the form before we submit
// if sanity check fails, tell the user what's insane, and cancel submit (by returning false)
function sanityCheck() {

}

function publisherUpdates() {
	if($('#calendar').length !== 0) // if we are in calendar module
		$('#calendar').fullCalendar('refetchEvents'); // refresh calendar

	if($('.list-view').length !== 0)
		$.fn.yiiListView.update($('.list-view').attr('id'));
}


function resetPublisher() {

	// reset group checkbox (if checked)
	if($('#groupCheckbox').is(':checked')) {
		$('#groupCheckbox').click(); // unchecks group checkbox, and calls ajax function to restor Assigned To to list of users
	}

	// reset textarea and dropdowns
	$('#publisher-form select, #publisher-form input[type=text], #publisher-form textarea').each(function(i) {
		$(this).val($(this).data('defaultValue'));
	});

	// reset checkboxes
	$('#publisher-form input[type=checkbox]').each(function(i) {
		$(this).attr('checked', $(this).data('defaultValue'));
	});

	// reset save button
	$('#save-publisher').removeClass('highlight');
}
