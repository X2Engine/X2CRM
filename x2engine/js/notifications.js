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


var hasFocus = true;

var pageTitle = '';
var notifUrl;
var newNotif = null;
var notifTimeout;
// notifUpdateInterval = 1500;
var notifViewTimeout;
var lastNotifId = 0;
var lastChatId = 0;

/* function updateNotifications() {
    
	newNotif = $.ajax({
		type: 'POST',
		url: notifUrl,
		success: function (response){
			if(response>0){
			   $('#main-menu-notif').show();
			   $('#main-menu-icon').hide();
			   $('#main-menu-notif').html(response); 
			}else{
			   $('#main-menu-notif').hide();
			   $('#main-menu-icon').show();
			}
		},
		complete: function (xhr,status) {
			notifTimeout = setTimeout(updateNotifications,30000);
		}
    });
} */

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


function updateNotifications() {

	$.ajax({
		type: 'POST',
		url: yii.baseUrl+'/index.php/notifications/get',
		data: 'lastId='+lastNotifId+'&lastChat='+lastChatId,
		success: function (response) {
			if(response != '') {	// if there's no new data, we're done
				try {
					var data = $.parseJSON(response);
					
					if(data.notifCount)
						$('#main-menu-notif span').html(data.notifCount);
					if(data.notifData) {
					
						var newNotif = false;

						for (var i=data.notifData.length-1;i>=0;--i) {
							// console.debug(data.notifData[i]);
							// if(typeof item['text'] != 'undefined')
								var notif = $(document.createElement('div'))
									.addClass('notif')
									.html('<div class="msg">'+data.notifData[i].text+'</div><div class="close">x</div>')
									.data('id',data.notifData[i].id)
									.prependTo('#notifications');
								
							if(data.notifData[i].viewed == 0) {
								notif.addClass('unviewed');
								newNotif = true;
							}
							
								// .append('<div class="notif">'+</div>\n'); 
							
						}
						
						if(data.notifData.length) {
							$('#no-notifications').hide();
							lastNotifId = data.notifData[data.notifData.length - 1].id;
							
						} else
							$('#no-notifications').show();
							
						if(newNotif)
							$(document).trigger('x2.newNotifications');
					}
					// console.debug(data);
					
					
				} catch(e) {
					
				}
			}
			notifTimeout = setTimeout(updateNotifications,notifUpdateInterval);
		},
		error: function() {
			clearTimeout(notifTimeout);
		}
    });

}


$(function() {

	updateNotifications();
	
	pageTitle = document.title;
	
	// return;
	$('#notif-box .close').click(function() {
		$('#notif-box').fadeOut(300);
		// $('#main-menu-notif').hide();
		// $('#main-menu-icon').show();
		$.fn.titleMarquee('softStop');
	
	
	});
	
	// listen for window focus/blur for stupid browsers that can't handle document.hasFocus()
	$(window).bind('blur focusout', function(){ hasFocus = false; });
	$(window).bind('focus focusin', function(){ hasFocus = true; });
	
	$(document).bind('x2.newNotifications',function(e) {
	
		if($('#notif-box').not(':visible') && document.hasFocus() || hasFocus) {
			 // $('#main-menu-notif').show(); //.find('span').html('31415');
			// $('#notif-box').fadeIn(300);
			openNotifications();
		
		}
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
	
	// delete a notification
	$('#notif-box').delegate('.notif .close','click',function(e) {
	
		e.stopPropagation();
		var notifId = $(this).parent().data('id');

		$.ajax({
			type: 'GET',
			url: yii.baseUrl+'/index.php/notifications/delete',
			data: 'id='+notifId
			// complete: function (response) {
				
			// }
		});
	
		removeNotification(notifId);
		
	
	});
	
	
});

function openNotifications() {

	notifViewTimeout = setTimeout(function() {
	
		var notifIds = [];
		
		$('#notifications .notif').each(function() { notifIds.push('id[]='+$(this).data('id')); });
		
		$.ajax({
			type: 'GET',
			url: yii.baseUrl+'/index.php/notifications/markViewed',
			data: encodeURI(notifIds.join('&'))
			// complete: function (response) {
				
			// }
		});
		
		
	},2000);
	
	$('#notif-box').fadeIn(300);
}

function closeNotifications() {
	clearTimeout(notifViewTimeout);
	$('#notif-box').fadeOut(300);
	$.fn.titleMarquee('softStop');
}

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





