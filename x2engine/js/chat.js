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

			
			var scrollToBottom = $('#chat-box').prop('scrollTop') >= $('#chat-box').prop('scrollHeight') - $('#chat-box').height();
			
			for (var i in messages) {
				latestId = messages[i].id;	// update the latest message ID received
				$('#chat-box').append(messages[i].message);	// add new messages to chat window
			}
			
			if (messages.length > 0 && scrollToBottom)
				$('#chat-box').prop('scrollTop',$('#chat-box').prop('scrollHeight')); // scroll to bottom of window
		},
		complete: function (xhr,status) {
			pendingUpdate = null;
			setTimeout(updateChat,updateInterval);
		}
    });
}