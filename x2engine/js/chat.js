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
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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

var chatAjaxUrl = '';
var chatUpdateInterval = 2000;
var chatLastId = 0;
var chatPendingUpdate = null;
var chatTimeout = null;

function updateChat() {

	
	if (chatPendingUpdate !=  null) {
		clearTimeout(chatTimeout);
		chatTimeout = setTimeout(updateChat,chatUpdateInterval);
		return;
	}

	chatPendingUpdate = $.ajax({
		type: 'POST',
		url: chatAjaxUrl,
		data: {'latestId': chatLastId},
		success: function (response){
			var messages = $.parseJSON(response);
			if(messages == null)
				messages = [];

			
			var scrollToBottom = $('#chat-box').prop('scrollTop') >= $('#chat-box').prop('scrollHeight') - $('#chat-box').height();
			
			for (var i in messages) {
				if(messages[i].length != 4)
					continue;
			
				chatLastId = messages[i][0];	// update the latest message ID received
				var msgHtml = '<div class="message">';
				msgHtml += messages[i][2];
				msgHtml += '<span class="chat-timestamp"> ('+messages[i][1]+')</span>';
				msgHtml += ': '+messages[i][3]+'</div>';
				
				
				$('#chat-box').append(msgHtml);	// add new messages to chat window
			}
			
			if (messages.length > 0 && scrollToBottom)
				$('#chat-box').prop('scrollTop',$('#chat-box').prop('scrollHeight')); // scroll to bottom of window
		},
		complete: function (xhr,status) {
			chatPendingUpdate = null;
			clearTimeout(chatTimeout);
			chatTimeout = setTimeout(updateChat,chatUpdateInterval);
		}
    });
}