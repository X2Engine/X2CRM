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


var pageTitle = '';


function updateHistory() {
	$('.action.list-view').each(function(i) {
		$.fn.yiiListView.update($(this).attr('id'));
	});
}

// title marquee jquery plugin
$(function() {
	var opts = {
		titleTimeout:null,
		message:'',
		titleOffset:0,
		speed:200,
		softStop:false
	};
	
	// var pageTitle = 

	var methods = {
		set: function(msg) {
			if(typeof msg != 'undefined')
				opts.message = msg;
		},
		start: function(msg) {
			if(typeof msg != 'undefined') {
				opts.softStop = false;
				opts.message = msg;
				clearInterval(opts.titleTimeout);
				opts.titleTimeout = setInterval(function() { methods['tick'](); }, opts.speed);
			}
		},
		tick: function() {
			++opts.titleOffset;
			if(opts.titleOffset >= opts.message.length ) {
				opts.titleOffset = 0;
				if(opts.softStop) {
					methods['stop']();
				}
			}
			var newTitle = opts.message.substring(opts.titleOffset)+' '+opts.message.substring(0,opts.titleOffset);

			document.title = newTitle;
		},
		pause: function() {
			clearInterval(opts.titleTimeout);
		},
		stop: function() {
			clearInterval(opts.titleTimeout);
			opts.titleOffset = 0;
			opts.softStop = false;
			document.title = pageTitle;
		},
		softStop: function() {
			opts.softStop = true;
		}
	};

	$.fn.titleMarquee = function(method) {
		if (methods[method])
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		else
			$.error( 'Method ' +  method + ' does not exist on titleMarquee' );
	};
});


$(function() {
	/**
	 * X2Engine Inter-Window Communication system
	 * Based on jStorage library
	 * 
	 * Each window chooses a random unique ID and checks for key "masterId"
	 * If it's not set then the window sets it to it's own ID and begins making 
	 * AJAX requests for notifications and chat. This check is done once per 
	 * second, and AJAX requests are made at a user-specified rate.
	 * 
	 * When the master window is closed, it unsets "masterId" so another window 
	 * can take over. If for some reason this event doesn't fire, "masterId" 
	 * still expires after 2 seconds
	 * 
	 */
	 
	// initialize variables
	var hasFocus = true;
	var notifUrl,
		newNotif = null,
		notifTimeout,
		// notifUpdateInterval = 1500,
		notifViewTimeout,
		lastNotifId = 0,
		lastChatId = 0;

		iwcMode = $.jStorage.storageAvailable(),
		windowId = +new Date()+Math.floor(Math.random()*1000),	// generate ID from timestamp and random number
		masterId = null;

	if(iwcMode) {	// find out of this is going to work

		/**
		 * Looks for a non-expired masterId entry in local storage.
		 * If there is no current masterId, sets it to current window's ID and 
		 * starts making AJAX requests. Otherwise, does nothing.
		 * 
		 * If this is the master window, masterId will never expire as long as 
		 * checkMasterId() continues running, since it resets the key's TTL.
		 */
		function checkMasterId(forceUpdate) {	// check if there's currently a master window
			if(masterId == windowId) {
				$.jStorage.setTTL('iwcMasterId',notifUpdateInterval+1000);	// still here, update masterId expiration
			} else {
				masterId = $.jStorage.get('iwcMasterId',null);
				if(masterId == null) {	// no incumbent master window, time to step up!
					masterId = windowId;
					$.jStorage.set('iwcMasterId',masterId,{TTL:notifUpdateInterval+1000});
				}
			}
			
			if(masterId == windowId || forceUpdate)
				getUpdates();	// do AJAX check
		}
		
		checkMasterId(true);	// always get updates on startup, because the master 
								// window will only send ones it considers new
		
		var checkMasterIdTimeout = setInterval(checkMasterId,notifUpdateInterval);

		/**
		 * Subscribe to various IWC channels using jStorage plugin.
		 * Each channel represents a particular event type.
		 * 
		 * Note that events from the same window are ignored to maintain cross 
		 * browser consistency in Opera and IE.
		 */
		$.jStorage.subscribe("x2iwc_notif", function(ch,payload) {
			if(payload.origin != windowId)
				addNotifications(payload.data,payload.notifCount);
		});
		$.jStorage.subscribe("x2iwc_notif_delete", function(ch,payload) {
			if(payload.origin != windowId)
				removeNotification(payload.id)
		});
		$.jStorage.subscribe("x2iwc_chat", function(ch,payload) {
			if(payload.origin != windowId)
				addChatMessages(payload.data);

		});

		// remove masterId when we close the window or navigate away
		$(window).unload(function() {
			masterId = $.jStorage.get('iwcMasterId',null);	// get the current masterId in case that has changed somehow
			if(windowId == masterId)
				$.jStorage.deleteKey('iwcMasterId');
		});
	} else {
		getUpdates();		// no IWC, we're on our own here so we gotta just do the AJAX
	}
	
	pageTitle = document.title;
	
	$('#notif-box .close').click(function() {
		$('#notif-box').fadeOut(300);
		$.fn.titleMarquee('softStop');
	});
	
	// listen for window focus/blur for stupid browsers that can't handle document.hasFocus()
	$(window).bind('blur focusout', function(){ hasFocus = false; });
	$(window).bind('focus focusin', function(){ hasFocus = true; });
	
	$(document).bind('x2.newNotifications',function(e) {
	
		// if($('#notif-box').not(':visible') && document.hasFocus() || hasFocus)
		if($('#notif-box').not(':visible'))
			openNotifications();
	});
	$('#main-menu-notif').click(function() {
		if($('#notif-box').is(':visible'))
			closeNotifications();
		else
			openNotifications();
		return false;
	});
	
	$(document).click(function(e) {
		if(!$(e.target).is('#notif-box, #notif-box *'))
			closeNotifications();
	});
	
	
	/**
	 * Deletes a notification.
	 * Makes AJAX delete request, calls removeNotification() to remove it from 
	 * the DOM and publishes the deletion to other windows.
	 */
	$('#notif-box').delegate('.notif .close','click',function(e) {
		e.stopPropagation();
		var notifId = $(this).parent().data('id');

		$.ajax({
			type: 'GET',
			url: yii.baseUrl+'/index.php/notifications/delete',
			data: 'id='+notifId
		});
		removeNotification(notifId);	// remove notif from the list
		
		if(iwcMode) {	// tell other windows to do the same
			$.jStorage.publish("x2iwc_notif_delete",{
				origin:windowId,
				id:notifId
			});
		}
	});
	
	
	/**
	 * Submits a chat message.
	 * Makes AJAX request, calls addChatMessages() and publishes the message 
	 * to other windows.
	 */
	$('#chat-submit').click(function() {
		$.ajax({
			type:'POST',
			url:yii.baseUrl+'/index.php/site/newMessage',
			data:$(this).closest('form').serialize()
		}).done(function(response) {
			$('#chat-message').val('');
			
			var chatData = $.parseJSON(response);
			
			if(chatData != null) {
				addChatMessages(chatData);
				if(iwcMode) {	// tell other windows about it
					$.jStorage.publish("x2iwc_chat",{
						origin:windowId,
						data:chatData
					});
				}
			}
		});
		return false;
	});

	
	/**
	 * Checks for notifications or chat updates via AJAX and calls 
	 * addNotification or whatever, then publishes to the other windows via 
	 * the IWC system
	 */
	function getUpdates() {
		$.ajax({
			type: 'GET',
			url: yii.baseUrl+'/index.php/notifications/get',
			data: {
				lastNotifId:lastNotifId,
				lastChatId:lastChatId
			}
		}).done(function (response) {
		
			if(!iwcMode)
				notifTimeout = setTimeout(getUpdates,notifUpdateInterval);		// set timeout only if there's no IWC

			if(response == '')	// if there's no new data, we're done
				return;
				
			try {
				var data = $.parseJSON(response);

				if(data.notifData) {
					addNotifications(data.notifData,data.notifCount);		// add new notifications to the notif box
					
					if(iwcMode) {	// tell other windows about it
						$.jStorage.publish("x2iwc_notif",{
							origin:windowId,
							data:data.notifData,
							notifCount:data.notifCount
						});
					}
				}
				if(data.chatData) {
					if(iwcMode) {	// tell other windows about it
						$.jStorage.publish("x2iwc_chat",{
							origin:windowId,
							data:data.chatData
						});
					}
					addChatMessages(data.chatData);
				}
			} catch(e) { }	// ignore if JSON is being an idiot
		}).fail(function() {
			clearTimeout(notifTimeout);
		});
	}
	
	/**
	 * Opens the notification box and starts a timer to mark the notifications 
	 * as viewed after 2 seconds (unless the user closes the box before then)
	 */
	function openNotifications() {

		notifViewTimeout = setTimeout(function() {
		
			var notifIds = [];
			
			$('#notifications .notif').each(function() { notifIds.push('id[]='+$(this).data('id')); });
			
			$.ajax({
				type: 'GET',
				url: yii.baseUrl+'/index.php/notifications/markViewed',
				data: encodeURI(notifIds.join('&'))
			});
		},2000);
		
		$('#notif-box').fadeIn(300);
	}
	
	/**
	 * Closes the box and cancels the "mark as viewed" timer
	 */
	function closeNotifications() {
		clearTimeout(notifViewTimeout);
		$('#notif-box').fadeOut(300);
		$.fn.titleMarquee('softStop');
	}
	
	/**
	 * Generates notifications HTML from notifData and adds them to the DOM, 
	 * and updates the notification count.
	 * Also triggers x2.newNotifications event (which opens the box)
	 */
	function addNotifications(notifData,notifCount) {
		if(notifCount)
			$('#main-menu-notif span').html(notifCount);

		var newNotif = false;

		var $notifBox = $('#notifications');


		for (var i=notifData.length-1;i>=0;--i) {
			var notif = $(document.createElement('div'))
				.addClass('notif')
				.html('<div class="msg">'+notifData[i].text+'</div><div class="close">x</div>')
				.data('id',notifData[i].id)
				.prependTo($notifBox);
				
			if(notifData[i].viewed == 0) {
				notif.addClass('unviewed');
				newNotif = true;
			}
		}
		
		while($notifBox.find('.notif').length > 10)		// remove older messages if it gets past 10
			$notifBox.find('.notif:last').remove();
			
		if(notifData.length) {
			// console.debug(lastNotifId);
			lastNotifId = notifData[0].id;
		}

		if(notifCount > 0) {
			$('#no-notifications').hide();
			if(notifCount > 10)
				$("#notif-view-all").show();
		} else {
			$('#no-notifications').show();
			$("#notif-view-all").hide();
		}
			
		if(newNotif)
			$(document).trigger('x2.newNotifications');
	}

	/**
	 * Finds a notification by its id and removes it from the DOM
	 */
	function removeNotification(id) {

		$('#notifications .notif').each(function() {
			if($(this).data('id') == id) {
				$(this).remove();
				$('#main-menu-notif span').html(parseInt($('#main-menu-notif span').html())-1);
				if($('#notif-box .notif').length == 0)
					$('#no-notifications').show();
				return false;
			}
		});
	}
	
	
	/**
	 * Processes chat JSON data, generates chat entries and adds them to the 
	 * chat window. Scrolls to the bottom of the chat window (unless the user 
	 * has manually scrolled up)
	 */
	function addChatMessages(messages) {
		// var messages = $.parseJSON(response);
		if(messages == null)
			messages = [];

		var scrollToBottom = $('#chat-box').prop('scrollTop') >= $('#chat-box').prop('scrollHeight') - $('#chat-box').height();
		
		for (var i in messages) {
			// console.debug(messages[i][0]);
			if(messages[i].length != 4 || messages[i][0] <= lastChatId)	// skip messages we already have
				continue;
		
			lastChatId = messages[i][0];	// update the latest message ID received
			
			var msgHtml = '<div class="message">';
			msgHtml += messages[i][2];
			msgHtml += '<span class="chat-timestamp"> ('+messages[i][1]+')</span>';
			msgHtml += ': '+messages[i][3]+'</div>';
			
			
			$('#chat-box').append(msgHtml);	// add new messages to chat window
		}
		
		if (messages.length > 0 && scrollToBottom)
			$('#chat-box').prop('scrollTop',$('#chat-box').prop('scrollHeight')); // scroll to bottom of window
	}

});
