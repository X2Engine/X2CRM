/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




/*
Notifs prototype 

Used to set up notifications behavior. 

Feed widget updates are handled with notifications in order to reduce network traffic. As a 
result, this prototype contains methods and properties used only for feed widget messages.
*/

x2.Notifs = function (argsDict) {
    var defaultArgs = {
        translations: [],
        DEBUG: x2.DEBUG && false,
        isMobile: false,
        disablePopup: false // profile setting, if true don't automatically display notif menu
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    
    this._cachedFeedMessages = []; // used by x2touch

    // initialize variables
    this._hasFocus = true;
        //notifCount = 0,
    this._notifTimeout;
        // notifUpdateInterval = 1500,
    this._notifViewTimeout;
    this._lastNotifId = 0;
    this._lastEventId = 0;
    this._lastTimestamp = 0;
    this._iwcMode = $.jStorage.storageAvailable();
    // generate ID from timestamp and random number
    this._windowId = +new Date() + Math.floor(Math.random() * 1000); 
    this._masterId = null;

    this._run ();    
}

/*
Public static properties
*/

x2.Notifs.fetchNotificationUpdates = !x2.DEV_MODE;
//x2.Notifs.fetchNotificationUpdates = true;


/*
Public static methods
*/

x2.Notifs.updateHistory = function () {
    $('.action.list-view').each(function() {
        $.fn.yiiListView.update($(this).attr('id'));
    });
};


/*
Deletes all notifications in the notification menu.
Used by viewNotifications.php
*/
x2.Notifs.triggerNotifRemoval = function (id) {
    $('#notifications .notif').each(function() {
        if ($(this).data('id') === id) {
            $(this).find ('.close').click ();
        }
    });
};

x2.Notifs.playNotificationSound = function (){
    $('#notificationSound').attr("src", yii.notificationSoundPath);
    var sound = $("#notificationSound")[0];
    if (Modernizr.audio) sound.play();
}


/*
Private static methods
*/

/*
Public instance methods
*/

/*
Public instance methods for activity feed widget updates
*/

/*
Used by x2touch to retrieve feed messages when user navigates to mobile activity feed page
*/
x2.Notifs.prototype.getCachedFeedMessages = function () {
    var that = this;
    return that._cachedFeedMessages;
};

/*
Private instance methods
*/

x2.Notifs.prototype._run = function () {
    var that = this;
    that._setUpNotifRetrieval ();
    that._setUpUIBehavior ();
};

/*
Sets up retrieval of notifications. Sets up IWC if available.
*/
x2.Notifs.prototype._setUpNotifRetrieval = function () {
    var that = this;  

    if (that._iwcMode) { // find out of this is going to work
        //x2.DEBUG && console.log ('notifications: is iwcMode');

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

        that._checkMasterId(true);    // always get updates on startup, because the master
        // window will only broadcast ones it considers new

        // var checkMasterIdTimeout = setInterval(checkMasterId,notifUpdateInterval);

        /**
         * Subscribe to various IWC channels using jStorage plugin.
         * Each channel represents a particular event type.
         *
         * Note that events from the same window are ignored to maintain cross
         * browser consistency in Opera and IE.
         */
        // new notif received, add it to the list, update count
        $.jStorage.subscribe("x2iwc_notif", function(ch, payload) {
            if (payload.origin == that._windowId)
                return;
            /*if (payload.notifCount)
                notifCount = payload.notifCount;*/
            if (payload.data) {
                that._addNotifications(payload.data, false);
                if (!that.disablePopup) that._openNotifications();
            }
        });
        // notif box toggled open/closed
        $.jStorage.subscribe("x2iwc_toggle_notif", function(ch, payload) {
            if (payload.origin == that._windowId)
                return;
            if (payload.show)
                if (!that.disablePopup) that._openNotifications();
            else
                that._closeNotifications();
        });
        // notif deleted, remove it and add the next notif, update count
        $.jStorage.subscribe("x2iwc_notif_delete", function(ch, payload) {
            //x2.DEBUG && console.log ('x2iwc_notif_delete: payload = ');
            //x2.DEBUG && console.log (payload);
            //x2.DEBUG && console.log (windowId);
            if (payload.origin == that._windowId)
                return;

            //notifCount = payload.notifCount;
            that._removeNotification(payload.id)
            //notifCount = $('#notifications').children ('.notif').length; 

            if (payload.nextNotif)
                that._addNotifications(payload.nextNotif, true);
        });

        // all notifs deleted, remove them and update count
        //x2.DEBUG && console.log ('x2iwc_notif_delete_all: setup');
        $.jStorage.subscribe("x2iwc_notif_delete_all", function(ch, payload) {
            //x2.DEBUG && console.log ('x2iwc_notif_delete_all: payload, windowId = ');
            //x2.DEBUG && console.log (payload);
            //x2.DEBUG && console.log (windowId);
            if (payload.origin == that._windowId)
                return;

            that._removeAllNotifications ()
        });

        // new chat message, add it
        $.jStorage.subscribe("x2iwc_chat", function(ch, payload) {
            if (payload.origin == that._windowId)
                return;
            that.addFeedMessages(payload.data);

        });

        // remove masterId when we close the window or navigate away
        $(window).unload(function() {

            // get the current masterId in case that has changed somehow
            that._masterId = $.jStorage.get('iwcMasterId', null);    
            if (that._windowId == that._masterId)
                $.jStorage.deleteKey('iwcMasterId');
        });
    } else {
        that._getUpdates();        // no IWC, we're on our own here so we gotta just do the AJAX
    }

};


/*
Binds event handlers responsible for notifications UI behavior 
*/
x2.Notifs.prototype._setUpUIBehavior = function () {
    var that = this;

    /* listen for window focus/blur for browsers that can't handle 
       document.that._hasFocus() */
    $(window).bind('blur focusout', function() {
        that._hasFocus = false;
    });
    $(window).bind('focus focusin', function() {
        that._hasFocus = true;
    });

    // new notification event handler
    $(document).bind('x2.newNotifications', function(e) {
        if ($('#notif-box').not(':visible')) {
            if (!that.disablePopup) that._openNotifications();
        }
    });

    // toggle notif menu
    $('#main-menu-notif').click(function() {
        var show = !$('#notif-box').is(':visible')    // if it's hidden, show it
        if (show) {
            that._openNotifications();
        } else {
            that._closeNotifications();
        }

        $.jStorage.publish("x2iwc_toggle_notif", {show: show, origin: that._windowId});

        return false;
    });

    // close notifications menu on click outside
    $(document).click(function(e) {
        if (!$(e.target).is('#notif-box, #notif-box *')) {
            that._closeNotifications();
            $.jStorage.publish("x2iwc_toggle_notif", {show: false});
        }
    });

    // close notifications menu on close button click
    $('#notif-box .close').click(function() {
        //console.log ('close');                                            
        $('#notif-box').fadeOut(300);
        // $.fn.titleMarquee('softStop');
    });

    // notifications clear all button behavior
    $('#notif-clear-all').on ('click', function (evt) {
        evt.preventDefault ();
        if (!window.confirm (that.translations['clearAll'])) return;
        $.ajax({
            type: 'GET',
            url: auxlib.createUrl ('/notifications/deleteAll'),
            success: function(response) {
                that._removeAllNotifications ();
            },
            complete: function() {
                if (that._iwcMode) {    // tell other windows to do the same
                    $.jStorage.publish("x2iwc_notif_delete_all", {
                        origin: that._windowId
                    });
                }
            }
        });
    })

    // delete notfication when notif delete button is pressed
    $('#notif-box').delegate('.notif .close', 'click', function(e) {
        e.stopPropagation();
        // if($(this).is(":animated"))
        // return;
        var notifId = $(this).parent().data('id');
        that._deleteNotification (notifId);
    });

};


/**
 * Looks for a non-expired masterId entry in local storage.
 * If there is no current masterId, sets it to current window's ID and
 * starts making AJAX requests. Otherwise, does nothing.
 *
 * If this is the master window, masterId will never expire as long as
 * checkMasterId() continues running, since it resets the key's TTL.
 */
x2.Notifs.prototype._checkMasterId = function (forceUpdate) {    
    var that = this;
    // check if there's currently a master window
    if (that._masterId == that._windowId) {
        // still here, update masterId expiration
        $.jStorage.setTTL('iwcMasterId', x2.notifUpdateInterval + 2000);    
    } else {
        that._masterId = $.jStorage.get('iwcMasterId', null);
        if (that._masterId == null) {    // no incumbent master window, time to step up!
            that._masterId = that._windowId;
            $.jStorage.set(
                'iwcMasterId', that._masterId, {TTL: x2.notifUpdateInterval + 2000});
        }
    }

    if (forceUpdate) {
        that._getUpdates(true);    // check for notifs but don't update other windows
    } else if (that._masterId == that._windowId) {
        that._getUpdates();    // check for notifs
    } else {
        // leave the AJAX to the master window, but keep an eye on him
        that._notifTimeout = setTimeout(
            function () { that._checkMasterId () }, x2.notifUpdateInterval);    
    }
}

/**
 * Deletes a notification.
 * Makes AJAX delete request, calls removeNotification() to remove it from
 * the DOM and publishes the deletion to other windows.
 */
x2.Notifs.prototype._deleteNotification = function (notifId) {
    var that = this;
    var nextNotif = false;

    that._removeNotification(notifId);    // remove notif from the list
    var notifCount = $('#notifications').children ('.notif').length;

    // load the next notification if there are any more
    getNextNotif = notifCount > 9 ? '1' : null;    

    $.ajax({
        type: 'GET',
        url: auxlib.createUrl ('/notifications/delete'),
        data: {
            id: notifId,
            getNext: getNextNotif,
            lastNotifId: that._lastNotifId
        },
        success: function(response) {
            try {
                data = $.parseJSON(response);
                if (data.notifData) {
                    nextNotif = data.notifData;
                    // append next notification to the notif box
                    that._addNotifications(nextNotif, true);        
                }

            } catch (e) {
            }    // ignore if JSON is being an idiot
        },
        complete: function() {
            if (that._iwcMode) {    // tell other windows to do the same
                $.jStorage.publish("x2iwc_notif_delete", {
                    origin: that._windowId,
                    id: notifId,
                    nextNotif: nextNotif,
                    notifCount: notifCount
                });
            }
        }
    });
};

/**
 * Checks for notifications or chat updates via AJAX and calls
 * addNotification or whatever, then publishes to the other windows via
 * the IWC system
 */
x2.Notifs.prototype._getUpdates = function (firstCall) {
    if(!x2.Notifs.fetchNotificationUpdates) return false;
    var that = this;

    $.ajax({
        type: 'GET',
        url: auxlib.createUrl ('/notifications/get'),
        data: {
            lastNotifId: that._lastNotifId,
            lastEventId: that._lastEventId,
            lastTimestamp: that._lastTimestamp
        },
        dataType: 'json'
    }).done(function(response) {

        if (that._iwcMode) {
            // call checkMasterId, which will then call getUpdates
            that._notifTimeout = setTimeout(
                function () { that._checkMasterId (); }, x2.notifUpdateInterval);    
        } else {
            // there's no IWC, so call getUpdates directly
            that._notifTimeout = setTimeout(that._getUpdates, x2.notifUpdateInterval);        
        }

        // if there's no new data, we're done
        if (response == null || typeof response != 'object')    
            return;

        if(typeof response.sessionError != 'undefined' && that._hasFocus) {
            x2.Notifs.fetchNotificationUpdates = confirm(response.sessionError);
            if(x2.Notifs.fetchNotificationUpdates) {
                window.location = window.location;
            }
        }

        //x2.DEBUG && console.log ('ajax response');
        
        try {
            var data = response; //$.parseJSON(response);

            if (data.notifData.length) {
                //notifCount = data.notifCount;
                
                // add new notifications to the notif box (prepend)
                that._addNotifications(data.notifData, false, firstCall);        
                var notifCount = $('#notifications').children ('.notif').length; 

                if (!firstCall) {
                    x2.Notifs.playNotificationSound();
                    if (!that.disablePopup) that._openNotifications();

                    if (that._iwcMode) {    // tell other windows about it
                        $.jStorage.publish("x2iwc_notif", {
                            origin: that._windowId,
                            data: data.notifData,
                            //notifCount: data.notifCount
                            notifCount: notifCount
                        });
                    }
                }
            }
            if (data.chatData) {
                if (that._iwcMode && !firstCall) {    // tell other windows about it
                    $.jStorage.publish("x2iwc_chat", {
                        origin: that._windowId,
                        data: data.chatData
                    });
                }
                that.addFeedMessages(data.chatData);
            }
        } catch (e) {
        }    // ignore if JSON is being an idiot
    }).fail(function() {
        clearTimeout(that._notifTimeout);
    });

    return false;
};

/**
 * Opens the notification box and starts a timer to mark the notifications
 * as viewed after 2 seconds (unless the user closes the box before then)
 */
x2.Notifs.prototype._openNotifications = function () {
    var that = this;

    that._notifViewTimeout = setTimeout(function() {

        var notifIds = [];

        // loop through notifs, collect IDs (ignore if already viewed)
        $('#notifications .notif.unviewed').each(function() {    
            notifIds.push('id[]=' + $(this).removeClass('unviewed').data('id'));
        });
        if (notifIds.length) {
            $.ajax({
                type: 'GET',
                url: auxlib.createUrl ('/notifications/markViewed'),
                data: encodeURI(notifIds.join('&'))
            });
        }
    }, 2000);

    $('#notif-box').fadeIn({
        duration: 300,
        complete: function () {
            $('#notif-box-shadow-correct').show ();
            $('#notif-box-shadow-correct').position ({ // IE bug fix, forces repaint
                my: "left-20 top",
                at: "left bottom",
                of: $(this)
            });
        }
    });
};

/**
 * Closes the box and cancels the "mark as viewed" timer
 */
x2.Notifs.prototype._closeNotifications = function () {
    var that = this;
    clearTimeout(that._notifViewTimeout);
    $('#notif-box').fadeOut(300);
    $('#notif-box-shadow-correct').hide ();
};

x2.Notifs.prototype._checkIfAlreadyReceived = function (notifId) {
    var that = this;
    var alreadyReceived = false;
    $('#notifications .notif').each(function() {
        if ($(this).data('id') == notifId) {
            alreadyReceived = true;
            return false;
        }
    });
    that.DEBUG && console.log ('_checkIfAlreadyReceived: ');
    that.DEBUG && console.log ('alreadyReceived = ');
    that.DEBUG && console.log (alreadyReceived);

    return alreadyReceived;
};

/**
 * Generates notifications HTML from notifData and adds them to the DOM,
 * and updates the notification count.
 * Also triggers x2.newNotifications event (which opens the box)
 */
x2.Notifs.prototype._addNotifications = function (notifData, append, firstCall) {
    var that = this;

    that.DEBUG && console.log ('addNotifications');

    firstCall = typeof firstCall === 'undefined' ? false : true;

    var newNotif = false;

    var $notifBox = $('#notifications');
    var newNotifNum = 0;

    /* loop through the notifications backwards (they're ordered by ID descending, so start 
       with the oldest) */
    var timeNow = new Date();
    var uTimeNow = timeNow.getTime()/1000.0;
    for (var i = notifData.length - 1; i >= 0; --i) {
        

        var notifId = notifData[i].id;
        //console.log ('addNotifications: notifId = ' + notifId);

        if (that._checkIfAlreadyReceived (notifId)) continue;
        newNotifNum++;
        
        if (notifData[i].type === 'voip_call'
                && uTimeNow - notifData[i].timestamp < 2*x2.notifUpdateInterval/1000
                && that._windowId === that._masterId
                && notifData[i].viewed == '0'
                && !that.disablePopup) {
            // Screen pop only if less than 2*interval ago, and master window,
            // and unread.
            var newWindow = window.open (yii.baseUrl+'/index.php/contacts/'+notifData[i].modelId,'_blank');
            newWindow.focus();
        }

        var notif = $(document.createElement('div'))
                .addClass('notif')
                .html('<div class="msg">' + notifData[i].text + 
                    '</div><div class="close">x</div>')
                .data('id', notifId);

        if (append)
            notif.appendTo($notifBox);
        else
            notif.prependTo($notifBox);

        if (notifData[i].viewed == 0) {
            notif.addClass('unviewed');
            newNotif = true;
        }

    }

    while ($notifBox.find('.notif').length > 10) { // remove older messages if it gets past 10
        $notifBox.find('.notif:last').remove();
    }

    if (notifData.length && !append)
        that._lastNotifId = notifData[0].id;

    // increment notification number if it's new
    if (!append && !firstCall && newNotifNum > 0) that._incrNotif (newNotifNum);

    if (newNotif && !append)
        $(document).trigger('x2.newNotifications');
};


x2.Notifs.prototype._removeAllNotifications = function (id) {
    var that = this;
    $('#notifications .notif').remove ();
    $('#notif-box-shadow-correct').position ({ // IE bug fix, forces repaint
        my: "left-20 top",
        at: "left bottom",
        of: $('#notif-box')
    });

    var notifCount = 0;
    $('#main-menu-notif span').html(notifCount);

    that._toggleClearAllViewAllNoNotif (notifCount);
};


/**
 * Finds a notification by its id and removes it from the DOM
 */
x2.Notifs.prototype._removeNotification = function (id) {
    var that = this;
    $('#notifications .notif').each(function() {
        if ($(this).data('id') == id) {
            $(this).remove();
            $('#notif-box-shadow-correct').position ({ // IE bug fix, forces repaint
                my: "left-20 top",
                at: "left bottom",
                of: $('#notif-box')
            });
            return false;
        }
    });

    //that._countNotifications();
    that._decrNotif ();
};

/*
Decrement number in ui indicating number of notifications
*/
x2.Notifs.prototype._decrNotif = function () {
    var that = this;
    //x2.DEBUG && console.log ('decrNotif');
    var notifCount = parseInt ($('#main-menu-notif span').html()) - 1;
    $('#main-menu-notif span').html(notifCount);

    that._toggleClearAllViewAllNoNotif (notifCount);
};


x2.Notifs.prototype._toggleClearAllViewAllNoNotif = function (notifCount) {
    var that = this;
    var showViewAll = false,
        showNoNotif = false;

    if (notifCount < 1)
        showNoNotif = true;
    else if (notifCount > 10)
        showViewAll = true;

    $("#notif-view-all").toggle(showViewAll);
    $("#notif-clear-all").toggle(!showNoNotif);
    $('#no-notifications').toggle(showNoNotif);

};


/*
Increment number in ui indicating number of notifications by newNotifNum
*/
x2.Notifs.prototype._incrNotif = function (newNotifNum) {
    var that = this;
    //x2.DEBUG && console.log ('incrNotif');
    var notifCount = parseInt ($('#main-menu-notif span').html()) + newNotifNum;
    $('#main-menu-notif span').html(notifCount);

    that._toggleClearAllViewAllNoNotif (notifCount);
};


/**
 * See how many notifications are in the list, update the counter,
 * and decide whether to show the "no notifications" thingy
 */
x2.Notifs.prototype._countNotifications = function () {
    var that = this;
    //notifCount = Math.max(0, notifCount);
    var notifCount = $('#notifications').children ('.notif').length; 

    $('#main-menu-notif span').html(notifCount);

    var showViewAll = false,
        showNoNotif = false;

    if (notifCount < 1)
        showNoNotif = true;
    else if (notifCount > 10)
        showViewAll = true;

    $("#notif-view-all").toggle(showViewAll);
    $('#no-notifications').toggle(showNoNotif);
};


/*
Private Methods for activity feed widget updates
*/

/**
 * Processes chat JSON data, generates chat entries and adds them to the
 * chat window. Scrolls to the bottom of the chat window (unless the user
 * has manually scrolled up)
 */
x2.Notifs.prototype.addFeedMessages = function (messages, suppressCaching) {
    var suppressCaching = typeof suppressCaching === 'undefined' ? false : suppressCaching; 
    if (this.isMobile) {
        var feedOrder = true;
    } else {
        var feedOrder = parseInt (yii.profile.activityFeedOrder) === 0 ? false : true;
    }
    var scrollToBottom = !feedOrder;
        
    var that = this;
    that.DEBUG && console.log ('addFeedMessages');

    // var messages = $.parseJSON(response);
    if (messages == null) {
        messages = [];
    } 

    if (!suppressCaching) that._cachedFeedMessages = that._cachedFeedMessages.concat (messages);

    /* var scrollToBottom = 
        $('#feed-box').prop('scrollTop') >= 
        $('#feed-box').prop('scrollHeight') - $('#feed-box').height(); */

    that.DEBUG && console.log ('messages = ');
    that.DEBUG && console.log (messages);

    for (var i in messages) {
        //console.debug(messages[i][0]);
        if (messages[i].length !== 5)    // skip messages we already have
            continue;

        if (messages[i][0] > that._lastEventId)
            that._lastEventId = messages[i][0];
        if (messages[i][1] > that._lastTimestamp)
            that._lastTimestamp = messages[i][1];

        var msgHtml = '<div class="message">';
        msgHtml += messages[i][3] + ' <span class="comment-age">(' + messages[i][4] + 
                ')</span>' + '</div>';

        //if top is true prepend to the list printing out in reverse order
        if(feedOrder==1) {
            $('#feed-box').prepend(msgHtml);
        } else {
            $('#feed-box').append(msgHtml);    // add new messages to chat window
        }
    }

    if (messages.length > 0 && scrollToBottom) {
        // scroll to bottom of window
        $('#feed-box').prop('scrollTop', $('#feed-box').prop('scrollHeight')); 
    }
};

/**
 * Submits a chat message.
 * Makes AJAX request, calls addFeedMessages() and publishes the message
 * to other windows.
 */
/*$('#chat-submit').click(function() {
    $.ajax({
        type: 'POST',
        url: yii.scriptUrl + '/site/newMessage',
        data: $(this).closest('form').serialize()
    }).done(function(response) {
        $('#chat-message').val('');

        var chatData = $.parseJSON(response);

        if (chatData != null) {
            that._addFeedMessages(chatData);
            if (that._iwcMode) {    // tell other windows about it
                $.jStorage.publish("x2iwc_chat", {
                    origin: that._windowId,
                    data: chatData
                });
            }
        }
    });
    return false;
});*/
