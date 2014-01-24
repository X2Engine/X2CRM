/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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

if (typeof x2 === 'undefined') x2 = {};

$(function () {
    x2.layoutManager = new x2.LayoutManager ();
});

/**
 * LayoutManager prototype - used to manage behavior of the layout 
 */

x2.LayoutManager = function (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    /* 
    Queue of functions to be called during window resize event which can optionally accept the
    arguments (windowWidth, contentWidth)
    */
    this._resizeFnQueue = []; 
	this.$body = $('body');
	
	this.pageMode = -1;		// 0 compact (no widgets)
	this.newPageMode = 0;	// 1 fixed width (960px)
							// 2 fill screen (5% margins)

    // values cached on window resize
    this.windowWidth; 
    this.contentWidth;

    this._init ();
}

/*
Public static methods
*/

x2.LayoutManager.togglePortletVisible = function (portlet, response) {
	portlet.children('.portlet-content').toggle('blind');
	var text;
	if(response == true) {
		var text = "[&ndash;]";
	} else {
		var text = "[+]";
	}
	portlet.find('.portlet-minimize a').html(text);
}

/*
Private static methods
*/

/*
Public instance methods
*/

/**
 * Functions added via this method will get called during the window resize event. 
 * @var function a function which optionally accepts the parameters (windowWidth, contentWidth)
 */
x2.LayoutManager.prototype.addFnToResizeQueue = function (fn) {
    this._resizeFnQueue.push (fn);
};

/*
Private instance methods
*/

/**
 * Sets up reponsive behavior of top bar menu items 
 */
x2.LayoutManager.prototype._setUpMenuResponsiveness = function () {
    var that = this; 
	var $moreMenu = $('#main-menu ul');
	var $moreMenuLi = $('#main-menu ul').parent();

	// move all moreMenu items into the main menu so we can get the correct display widths
	$moreMenu.children().insertBefore($moreMenuLi);

	var $header = $('#main-menu-bar');
	var $menuItems = $('#main-menu > li').not('#more-menu');
	var menuItemCutoffs = new Array($menuItems.length);
	var currentVisibleItems = $menuItems.length;


	for(var i = 0; i < menuItemCutoffs.length; i++) {
		if(i == 0) {
			menuItemCutoffs[i] = $($menuItems[i]).outerWidth() + $('#user-menu').outerWidth() + 
                $('#more-menu').outerWidth()+40;
		} else {
			menuItemCutoffs[i] = $($menuItems[i]).outerWidth() + menuItemCutoffs[i-1];
        }
	}

    this.addFnToResizeQueue (function (windowWidth, contentWidth) {
        that.DEBUG && console.log ('_setUpMenuResponsiveness resize fn');

		// calculate number of elements to show in the main menu
		var visibleItems = 0;
		for(var i = 0; i < menuItemCutoffs.length; i++) {
			if(menuItemCutoffs[i] + 70 < $header.outerWidth())
				visibleItems = i + 1;
			else
				break;
		}

		if(visibleItems < 1)
			visibleItems = 1;

		// there is room for more items, bring some out of the moreMenu
		if(visibleItems > currentVisibleItems) {
			for(var i=0; i<visibleItems - currentVisibleItems; i++) {
				$moreMenu.children().first().insertBefore($moreMenuLi);
			}
			currentVisibleItems = $('#main-menu > li').not('#more-menu').length;
			
		// the number of items is too high. move some into the moreMenu
		} else if(visibleItems < currentVisibleItems) {
			for(var i=$menuItems.length-1; i>=visibleItems; i--) {
			
				$($menuItems[i]).prependTo('#main-menu ul');
			}
			currentVisibleItems = $('#main-menu > li').not('#more-menu').length;
		}
		// show More dropdown only if it's needed
		if($moreMenu.children().length == 0) {
			$moreMenuLi.hide();
		} else {
			$moreMenuLi.show();
        }
    });

};

/**
 *  Initializes behavior of miscellaneous layout UI elements
 */
x2.LayoutManager.prototype._setUpX2UIElements = function () {
    var that = this; 
    that.DEBUG && console.log ('_setUpX2UIElements');

    // x2 buttons
	$(document).on('mousedown','.x2-button',function(e){ e.preventDefault(); });
	
    // x2 links
	$('a.x2-link').draggable({
        revert: 'invalid', helper:'clone', revertDuration:200, appendTo:'body',
        iframeFix:true
    });

	// toggle dropdown menus
	$(".dropdown span").mousedown(function() {	
		var $dropdown = $(this).siblings('ul');	// the menu to be opened
		$dropdown.toggleClass('open');
		$('.dropdown ul').not($dropdown).removeClass('open');	// close all other menus
		return false;
	});

	// toggle widget menu
	$('#widget-button').click(function() {
		if($('#x2-hidden-widgets-menu span.x2-hidden-widgets-menu-item').length !== 0)
			$('#x2-hidden-widgets-menu').toggle();
		return false;
	});

	// close menu if they click anywhere else on the page
	$(document).bind('click', function(e) {
		var $clicked = $(e.target);
		if(!$clicked.parents().is('.dropdown'))
			$('.dropdown ul').removeClass('open');
		if(!$clicked.is('#widget-button'))
			$('#x2-hidden-widgets-menu').hide();
	});

	// Yii CWebLogRoute display
	$('.yiiLog').draggable({handle:'tr:first th'}).height(400).offset({top:200,left:80}).
        find('tr:first').dblclick(function() {

		var x = $(this).closest('.yiiLog');
		if(x.height() < 50)
			x.height(400);
		else
			x.height(23);
	});
	
	// translation list
	$('.yiiTranslationList').draggable({handle:'td,th'}).offset({top:300,left:0});
	
	// show/hide widget button
	$('#fullscreen-button').click(function(evt) {
        evt.preventDefault ();
        that.DEBUG && console.log ('fullscreen-button click');
                                                    
		// save preference
		$.ajax({
			url: yii.scriptUrl+'/site/fullscreen',
			type: 'GET',
			data: 'fs='+(window.fullscreen?'0':'1')
		});
		window.fullscreen = !window.fullscreen;

		if (window.fullscreen) {	// hide widgets
			that.$body.addClass('no-widgets');
		} else if(that.pageMode != 0) {	// don't bring them back if the page is in compact mode
			that.$body.removeClass('no-widgets');
			$(document).trigger ('showWidgets');
		}

		$(window).resize();
	});


	// make the record title the same width as the main column
	var pageTitle = $(".page-title-fixed-outer .page-title").first();
	var mainColumn = $(".page-title-fixed-outer").parent();
	if (pageTitle.length) {
		$(window).resize(function(e) {
			mainColumn.css('margin-top',(pageTitle.height()-36)+'px');
		});
	}
		
};

/**
 * Binds window event function and adds a couple functions to the resize function queue
 */
x2.LayoutManager.prototype._setUpWindowResizeEvent = function () {
    var that = this;  
	var $contentDiv = $('div#content');

    // right widget responsiveness setup
    this.addFnToResizeQueue (function (windowWidth, contentWidth) {

		// figure out what layout mode to use
		if(!x2.isAndroid && !x2.isIPad && windowWidth <= 1040) {
			that.newPageMode = 0;
		} else {
			if(windowWidth >= 1040 && window.enableFullWidth) {
				that.newPageMode = 2;
			} else {
				that.newPageMode = 1;
			}
		}
		
		// only change CSS if the layout mode has changed
		if(that.pageMode != that.newPageMode) {
		
			that.pageMode = that.newPageMode;
			
			if(that.pageMode == 0) {
				that.$body.addClass('no-widgets');
			} else {
				 if(!window.fullscreen) {
					that.$body.removeClass('no-widgets');
					$(document).trigger ('showWidgets');
				}
			}
		}
    });

    // action history responsiveness setup
    this.addFnToResizeQueue ((function () {
        var historyMode = -1;		
        return function (windowWidth, contentWidth) {
            if(contentWidth < 940)
                var newHistoryMode = 0; // underneath record
            else
                var newHistoryMode = 1 // side of record
                
            if(historyMode != newHistoryMode) {
                historyMode = newHistoryMode;
                if(historyMode == 1) {
                    $('#main-column, .history').addClass('half-width');
                } else {
                    $('#main-column, .history').removeClass('half-width');
                }
            }
        };
    }) ());

	// the screen just got resized - decide what to do about it
	$(window).resize(function() {
		that.windowWidth = $(window).width();
		that.contentWidth = $contentDiv.width();

        for (var i in that._resizeFnQueue) {
            that._resizeFnQueue[i] (that.windowWidth, that.contentWidth);
        }
	});
};

x2.LayoutManager.prototype._init = function () {
    var that = this; 
    that.DEBUG && console.log ('LayoutManager init');
    
    this._setUpX2UIElements ();
    this._setUpMenuResponsiveness ();
    this._setUpWindowResizeEvent ();

    $(window).resize();
};
