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

// called when a tab is clicked
function tabSelected(event, ui) {
	// set field SelectedTab for use in POST request
	$('#SelectedTab').val(ui.panel.id);
	
	if(ui.panel.id == 'new-event') {
		// switch labels Due Date vs Start Date
		$('#due-date-label').css('display', 'none');
		$('#start-date-label').css('display', 'block');
		
		// show end date
		$('#end-date-label').css('display', 'block');
		$('#end-date-input').css('display', 'inline-block');
		
		// show action-event-panel
		$('#action-event-panel').css('display', 'block');
		
	} else if(ui.panel.id == 'new-action') {
		$('#due-date-label').css('display', 'block');
		$('#start-date-label').css('display', 'none');
		
		// hide end date
		$('#end-date-label').css('display', 'none');
		$('#end-date-input').css('display', 'none');
		
		// show action-event-panel
		$('#action-event-panel').css('display', 'block');
	} else if(ui.panel.id == 'log-a-call') {
		// hide action-event-panel
		$('#action-event-panel').css('display', 'none');
	} else if(ui.panel.id == 'new-comment') {
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
