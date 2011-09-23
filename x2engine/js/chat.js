/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/

var ajaxUrl = '';
var updateInterval = 2000;
var latestId = 0;
var pendingUpdate = null;

function updateChat() {

	if (pendingUpdate !=  null) {
		setTimeout(updateChat,updateInterval);
		return;
	}

	pendingUpdate = $.ajax({
		type: 'POST',
		url: ajaxUrl,
		data: {'latestId': latestId},
		success: function (response){
			var messages = $.parseJSON(response);
			var len = (messages != null) ? messages.length-1 : -1;

			for (var i in messages) {
				latestId = messages[i].id;	// update the latest message ID received
				$('#chat-box').append(messages[i].message);	// add new messages to chat window
			}
			if (messages.length > 0)
				$('#chat-box').attr('scrollTop',$('#chat-box').attr('scrollHeight')); // scroll to bottom of window
		},
		complete: function (xhr,status) {
			pendingUpdate = null;
			setTimeout(updateChat,updateInterval);
		}
    });
}