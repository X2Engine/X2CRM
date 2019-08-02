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




if (typeof x2 === 'undefined')
    x2 = {};

$(function () {
    x2.layoutManager = new x2.LayoutManager();
});

/**
 * LayoutManager prototype - used to manage behavior of the layout 
 */

x2.LayoutManager = function (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs(this, defaultArgs, argsDict);

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

    this._halfWidthSelector = '#main-column, .history';
    this._hideSideBarLeftThreshold = 657;
    this._hideWidgetsThreshold = 1040;
    //this._hideWidgetsThreshold = 940;
    this._fullSearchBarThreshold = x2.logoWidth ? x2.logoWidth + 915 : 915;
    //this._publisherHalfWidthThreshold = 940;
    this._recordViewSingleColumnThreshold = 1406;
    this._recordViewSingleColumnThresholdNoWidgets = 1129;
    this._titleBarThresholds;
    this._logoWidth = x2.logoWidth ? x2.logoWidth : 30; // default logo width
    this._mobileLayout; // true if mobile layout is active

    this._init();
}

/*
 Public static properties
 */

/**
 * Used to determine which mode search bar is in  
 */
x2.LayoutManager.searchBarModes = {
    MOBILE: 0,
    COMPACT: 1,
    FULL: 2
};

/*
 Public static methods
 */

x2.LayoutManager.togglePortletVisible = function (portlet, response) {
    portlet.children('.portlet-content').toggle('blind');
    var text;
    if (response == true) {
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
 * @deprecated
 */
x2.LayoutManager.prototype.setHalfWidthSelector = function (halfWidthSelector) {
    this._halfWidthSelector = halfWidthSelector;
};

/**
 * @deprecated
 */
x2.LayoutManager.prototype.setHalfWidthThreshold = function (halfWidthThreshold) {
    this._publisherHalfWidthThreshold = halfWidthThreshold;
};

/**
 * Functions added via this method will get called during the window resize event. 
 * @var function a function which optionally accepts the parameters (windowWidth, contentWidth)
 */
x2.LayoutManager.prototype.addFnToResizeQueue = function (fn) {
    /*var $contentDiv = $('div#content');
     var that = this;
     $(window).resize (function () {
     that.windowWidth = window.innerWidth || $(window).width ();
     that.contentWidth = $contentDiv.width();
     fn (that.windowWidth, that.contentWidth); 
     });*/
    this._resizeFnQueue.push(fn);
};

x2.LayoutManager.prototype.isMobileLayout = function () {
    return this._mobileLayout;
};

/*
 Private instance methods
 */

x2.LayoutManager.prototype._calculateTitleBarThresholds = function () {
    var that = this;
    var thresholds = [];
    that.DEBUG && console.log('_calculateTitleBarThresholds');

    $('#main-menu > .top-bar-module-link').show();
    var $menuItems = $('#main-menu > .top-bar-module-link');
    that.DEBUG && console.log('$menuItems = ');
    that.DEBUG && console.log($menuItems);

    for (var i = 0; i < $menuItems.length; i++) {
        if (i == 0) {
            thresholds[i] =
                    $($menuItems[i]).outerWidth() +
                    $('#user-menu').outerWidth() +
                    $('#user-menu-2').outerWidth() +
                    that._logoWidth +
                    $('#more-menu').outerWidth();
        } else {
            thresholds[i] = $($menuItems[i]).outerWidth() + thresholds[i - 1] + 10;
        }
    }
    $('#main-menu .top-bar-module-link').hide();
    $('#more-menu .top-bar-module-link').show();
    $('#module-menu .top-bar-module-link').show();

    this._titleBarThresholds = thresholds;
};

/**
 * Helper method for _setUpMenuResponsiveness. Used to determine how the search bar is being 
 * displayed. The search bar switches between three modes (mobile, compact, full) and with each
 * mode it takes up a different amount of space. As a result, each time there is a mode change,
 * the title bar thresholds must be recalculated.
 */
x2.LayoutManager.prototype._getSearchbarMode = function (windowWidth) {
    var that = this;
    if (Modernizr.mq('(max-width: ' + that._hideSideBarLeftThreshold + 'px)')) {
        return x2.LayoutManager.searchBarModes['MOBILE'];
    } else if (Modernizr.mq('(min-width: ' + that._hideSideBarLeftThreshold + 'px) and ' +
            '(max-width: ' + that._fullSearchBarThreshold + 'px)')) {

        return x2.LayoutManager.searchBarModes['COMPACT'];
    } else {
        return x2.LayoutManager.searchBarModes['FULL'];
    }
};

/**
 * Sets up reponsive behavior of top bar menu items 
 */
x2.LayoutManager.prototype._setUpMenuResponsiveness = function () {
    //if (x2.isAndroid && $('body').hasClass ('disable-mobile-layout')) return;

    var that = this;
    var $moreMenu = $('#more-menu ul');
    var $moreMenuLi = $('#more-menu');
    var $moduleMenu = $('#module-menu ul');
    var $moduleMenuLi = $('#module-menu');
    var $mainMenu = $('#main-menu');
    var menuItem = $('#main-menu > .top-bar-module-link');
    var menuItemCount = menuItem.length;
    var searchBarMode = this._getSearchbarMode($(window).width());
    var currentVisibleItems;

    this.addFnToResizeQueue(function (windowWidth, contentWidth) {
        if ($('body').hasClass('x2-mobile-layout'))
            return;

        that.DEBUG && console.log('searchBarMode = ');
        that.DEBUG && console.log(searchBarMode);
        var newSearchBarMode = that._getSearchbarMode(windowWidth);
        that.DEBUG && console.log('newSearchBarMode = ');
        that.DEBUG && console.log(newSearchBarMode);

        if (newSearchBarMode !== searchBarMode ||
                typeof that._titleBarThresholds === 'undefined') {

            that._calculateTitleBarThresholds();
            currentVisibleItems = 0;
        }
        searchBarMode = newSearchBarMode;
        that.DEBUG && console.log('_setUpMenuResponsiveness resize fn');

        // calculate number of elements to show in the main menu
        var visibleItems = 0;
        for (var i = 0; i < that._titleBarThresholds.length; i++) {
            if (that._titleBarThresholds[i] + 70 < windowWidth)
                visibleItems = i + 1;
            else
                break;
        }
        that.DEBUG && console.log('visibleItems = ');
        that.DEBUG && console.log(visibleItems);
        that.DEBUG && console.log('currentVisibleItems = ');
        that.DEBUG && console.log(currentVisibleItems);

        if (visibleItems < 0)
            visibleItems = 0;

        // there is room for more items, bring some out of the moreMenu
        if (visibleItems > currentVisibleItems) {
            for (var i = 0; i < visibleItems - currentVisibleItems; ++i) {
                $mainMenu.children(':hidden').not('#more-menu').first().show();
                $moreMenu.children(':visible').first().hide();
            }
            currentVisibleItems = $('#main-menu > .top-bar-module-link:visible').length;

            // the number of items is too high. move some into the moreMenu
        } else if (visibleItems < currentVisibleItems) {
            $moreMenuLi.show();
            for (var i = currentVisibleItems; i > visibleItems; --i) {
                $mainMenu.children(':visible').not('#more-menu').last().hide();
                $moreMenu.children(':hidden').last().show();
            }
            currentVisibleItems = $('#main-menu > .top-bar-module-link:visible').length;
        }

        // show More dropdown only if it's needed
        if ($moreMenu.children(':visible').length == 0) {
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
    that.DEBUG && console.log('_setUpX2UIElements');

    // x2 buttons
    $(document).on('mousedown', '.x2-button', function (e) {
        e.preventDefault();
    });

    // x2 links
    $('a.x2-link').draggable({
        revert: 'invalid', helper: 'clone', revertDuration: 200, appendTo: 'body',
        iframeFix: true
    });

    // toggle dropdown menus
    $(".dropdown span").mouseover(function () {
        var $dropdown = $(this).siblings('ul');	// the menu to be opened
        $dropdown.toggleClass('open');
        $('.dropdown ul').not($dropdown).removeClass('open');	// close all other menus
        return false;
    });

    

    // toggle dropdown menus
    $('.dropdown a').mouseover(function (e) {
        /*
         * TODO: Account for actions links dropdown for each module under 'more' dropdown
         */
        var $dropdown = $(this).siblings('ul');	// the menu to be opened
        if (!$(this).parents().is('#more-menu') && !$(this).parents().is('#profile-dropdown')) {
            $dropdown.toggleClass('open');
            if (!$(e.target).parent().hasClass('top-bar-module-action-link'))
                $('.dropdown ul').not($dropdown).removeClass('open');	// close all other menus

        }
        
        return false;
    });

    

    // toggle widget menu
    $('#widget-button').click(function () {
        if ($('#x2-hidden-widgets-menu span.x2-hidden-widgets-menu-item').length !== 0)
            $('#x2-hidden-widgets-menu').toggle();
        return false;
    });

    // close menu if they click anywhere else on the page
    $(document).bind('click', function (e) {
        var $clicked = $(e.target);
        if (!$clicked.parents().is('.dropdown'))
            $('.dropdown ul').removeClass('open');
        if (!$clicked.is('#widget-button'))
            $('#x2-hidden-widgets-menu').hide();
    });

    // Yii CWebLogRoute display
    $('.yiiLog').draggable({handle: 'tr:first th'}).height(400).offset({top: 200, left: 80}).
            find('tr:first').dblclick(function () {

        var x = $(this).closest('.yiiLog');
        if (x.height() < 50)
            x.height(400);
        else
            x.height(23);
    });

    // translation list
    $('.yiiTranslationList').draggable({handle: 'td,th'}).offset({top: 300, left: 0});

    // show/hide widget button
    $('#fullscreen-button').click(function (evt) {
        evt.preventDefault();
        that.DEBUG && console.log('fullscreen-button click');

        // save preference
        $.ajax({
            url: yii.scriptUrl + '/site/fullscreen',
            type: 'GET',
            data: 'fs=' + (window.fullscreen ? '0' : '1')
        });
        window.fullscreen = !window.fullscreen;

        if (window.fullscreen) {	// hide widgets
            that.$body.addClass('no-widgets');
            that.$body.removeClass('show-widgets');
        } else if (that.pageMode != 0) {	// don't bring them back if the page is in compact mode
            that.$body.removeClass('no-widgets');
            that.$body.addClass('show-widgets');
            $(document).trigger('showWidgets');
        }

        var windowWidth = $(window).width();
        if (windowWidth > that._hideSideBarLeftThreshold)
            $(window).resize();
    });


    // make the record title the same width as the main column
    var pageTitle = $(".page-title-fixed-outer .page-title").first();
    var mainColumn = $(".page-title-fixed-outer").parent();
    if (pageTitle.length) {
        $(window).resize(function (e) {
            mainColumn.css('margin-top', (pageTitle.height() - 36) + 'px');
        });
    }

};

/**
 * Called when layout transitions from mobile to desktop. Loops through all children of x2 global
 * and calls mobileRefreshBehavior on all objects which support it.
 */
x2.LayoutManager.prototype._mobileRefreshX2Objects = function () {
    for (var i in x2) {
        if (typeof x2[i] === 'object' && x2[i] && 'mobileRefreshBehavior' in x2[i]) {
            x2[i].mobileRefreshBehavior();
        }
    }
}

x2.LayoutManager.prototype._setUpMobileLayout = function () {
    var that = this;

    if (!Modernizr.mq('only all')) { // check for media query support
        $('body').addClass('disable-mobile-layout');
        return;
    }

    this.addFnToResizeQueue(function (windowWidth) {
        if ((typeof that._mobileLayout === 'undefined' || !that._mobileLayout) &&
                windowWidth <= that._hideSideBarLeftThreshold) {

            $('body').addClass('x2-mobile-layout');
            that._mobileRefreshX2Objects();
            that._mobileLayout = true;
            //if (x2.gridViewStickyHeader) x2.gridViewStickyHeader.makeStickyForMobile ();
        } else if ((typeof that._mobileLayout === 'undefined' || that._mobileLayout) &&
                windowWidth > that._hideSideBarLeftThreshold) {

            $('body').removeClass('x2-mobile-layout');
            that._mobileLayout = false;
            that._mobileRefreshX2Objects();
            //if (x2.gridViewStickyHeader) x2.gridViewStickyHeader.makeUnstickyForMobile ();
        }
    });
};

/**
 * Binds window event function and adds a couple functions to the resize function queue
 */
x2.LayoutManager.prototype._setUpWindowResizeEvent = function () {
    var that = this;
    var $contentDiv = $('div#content');

    // right widget responsiveness setup
    this.addFnToResizeQueue(function (windowWidth, contentWidth) {

        // figure out what layout mode to use
        if (/*!x2.isAndroid &&*/ !x2.isIPad && windowWidth <= that._hideWidgetsThreshold) {
            that.newPageMode = 0;
        } else {
            if (windowWidth >= that._hideWidgetsThreshold && window.enableFullWidth) {
                that.newPageMode = 2;
            } else {
                that.newPageMode = 1;
            }
        }

        // only change CSS if the layout mode has changed
        if (that.pageMode != that.newPageMode) {

            that.pageMode = that.newPageMode;

            if (that.pageMode == 0) {
                that.$body.addClass('no-widgets');
                that.$body.removeClass('show-widgets');
            } else {
                if (!window.fullscreen) {
                    that.$body.removeClass('no-widgets');
                    that.$body.addClass('show-widgets');
                    $(document).trigger('showWidgets');
                }
            }
        }
    });

    // action history responsiveness setup
    this.addFnToResizeQueue((function () {
        return function (windowWidth, contentWidth) {
            if (that.$body.hasClass('no-widgets') &&
                    windowWidth < that._recordViewSingleColumnThresholdNoWidgets) {
                $('#content').addClass('record-view-single-column');
            } else if (that.$body.hasClass('show-widgets') &&
                    windowWidth < that._recordViewSingleColumnThreshold) {

                $('#content').addClass('record-view-single-column');
            } else {
                $('#content').removeClass('record-view-single-column');
            }
        };
    })());

    // the screen just got resized - decide what to do about it
    $(window).resize(function () {
        that.windowWidth = window.innerWidth || $(window).width();
        that.contentWidth = $contentDiv.width();

        for (var i in that._resizeFnQueue) {
            that._resizeFnQueue[i](that.windowWidth, that.contentWidth);
        }
    });
};

/**
 * Sets up behavior of left menu (the mobile version of the title bar)
 */
x2.LayoutManager.prototype._setUpLeftMenuResponsiveness = function () {
    if ($('body').hasClass('disable-mobile-layout'))
        return;
    var that = this;

    // hide/show left menu
    $('#show-left-menu-button').on('click', function () {
        var windowWidth = window.innerWidth;
        if (windowWidth < that._hideSideBarLeftThreshold) {
            var contentWidth = $('#content-container').width();
            if ($('body').hasClass('show-left-bar')) {
                that._hideLeftBar();
            } else {
                that._showLeftBar(contentWidth);
            }
        }
        return false;
    });

    // disable/enable logo link on window resize
    (function () {
        $searchBarTitle = $('#search-bar-title');
        var logoHref = $searchBarTitle.attr('href');
        var disabled = false; // whether or not logo link is disabled

        $(window).on('resize.leftMenuResponsiveness', function () {
            if (that.windowWidth < that._hideSideBarLeftThreshold) {
                if (!disabled) {
                    $searchBarTitle.attr('href', '#');
                    $searchBarTitle.css({cursor: 'default'});
                    $searchBarTitle.on('click.leftMenuResponsiveness', function (e) {
                        $('#show-left-menu-button').click();
                        e.preventDefault;
                        return false;
                    });
                    disabled = true;
                }
            } else {
                if (disabled) {
                    $searchBarTitle.attr('href', logoHref);
                    $searchBarTitle.css({cursor: ''});
                    $searchBarTitle.unbind('click.leftMenuResponsiveness');
                    disabled = false;
                }
            }
        });

        $(window).trigger('resize.leftMenuResponsiveness');
    })();
};

/**
 * Hide/show search bar when search button is clicked. This behavior is only active when 
 * window width is between width used for full title bar and width used for mobile title bar.
 */
x2.LayoutManager.prototype._setUpSearchBarResponsiveness = function () {
    if ($('body').hasClass('disable-mobile-layout'))
        return;
    var that = this;
    $searchButton = $('#search-bar .x2-button.black, #search-bar-box');
    $searchButton.on('click._setUpSearchBarResponsiveness', function () {
        if ($(this).next().is(':hidden')) {
            $(this).next().animate({width: 'toggle'}, 300);
            $('#user-menu li').not('#search-bar').hide();
            auxlib.onClickOutside($searchButton, function () {
                if ($(this).next().is(':visible')) {
                    $(this).next().hide();
                    $('#user-menu li').not('#search-bar').show();
                }
            }, true);
            return false;
        }
    });
};

/**
 * Show slideout left title bar
 * @param int contentWidth width of page content
 */
x2.LayoutManager.prototype._showLeftBar = function (contentWidth) {
    var that = this;
    if ($('body').hasClass('show-right-bar'))
        that._hideRightBar();

    x2.forms.showShortDefaultText($('#search-bar-box')[0]);
    $('body').addClass('show-left-bar');
    $('#content-container').width(contentWidth);
    $('#sidebar-left-widget-box').width(contentWidth);
    $('#footer').width(contentWidth);
    $('#user-menu-2').css({
        right: parseInt(auxlib.rStripPx($('#user-menu-2').css('right')), 10) - contentWidth
    });
    $('.page-title-fixed-inner .page-title').width(contentWidth);

    // prevent left bar from closing when phone keyboard slides out
    var searchBarHasFocus = false;
    $('#search-bar-box').focus(function () {
        searchBarHasFocus = true;
    });
    $('#search-bar-box').blur(function () {
        searchBarHasFocus = false;
    });

    $(window).one('resize._showLeftBar', function () {
        if (!searchBarHasFocus)
            that._hideLeftBar();
    });

    auxlib.onClickOutside($('#top-menus-container'), function () {
        if ($(this).is(':visible') && $('body').hasClass('x2-mobile-layout')) {
            that._hideLeftBar();
        }
    }, true);

};

/**
 * Hide slideout left title bar
 */
x2.LayoutManager.prototype._hideLeftBar = function () {
    $('body').removeClass('show-left-bar');

    x2.forms.showLongDefaultText($('#search-bar-box')[0]);
    $('#content-container').width('');
    $('#footer').width('');
    $('#user-menu-2').css({
        right: ''
    });
    $('#sidebar-left-widget-box').width('');
    $('.page-title-fixed-inner .page-title').width('');
};

x2.LayoutManager.prototype._minimizeResponsiveTitleBar = function (titleBar) {
    $(titleBar).removeClass('responsive-title-bar-shown');
    $(titleBar).css({height: ''});
    $(titleBar).find('.responsive-menu-items').css({display: ''});
};

x2.LayoutManager.prototype._expandResponsiveTitleBar = function (titleBar) {
    $(titleBar).addClass('responsive-title-bar-shown');
    $(titleBar).animate({height: ($(titleBar).height() * 2) + 'px'}, 300);
    $(titleBar).find('.responsive-menu-items').show();
    $(titleBar).find('.responsive-menu-items').css({display: 'block'});
};

/**
 * Sets up behavior of responsive page titles. Hides/shows page title buttons when grip button
 * is pressed.
 */
x2.LayoutManager.prototype._setUpTitleBarResponsiveness = function () {
    if ($('body').hasClass('disable-mobile-layout'))
        return;
    var that = this;

    var delay = false;
    $('.responsive-page-title > .mobile-dropdown-button').bind('click', function () {
        if (delay)
            return;
        var titleBar = $(this).parents('.responsive-page-title');
        if ($(titleBar).hasClass('responsive-title-bar-shown')) {
            that._minimizeResponsiveTitleBar(titleBar);
        } else {
            auxlib.onClickOutside($('.responsive-page-title'), function () {
                that._minimizeResponsiveTitleBar(titleBar);
            }, true);

            $(window).one('resize._setUpTitleBarResponsiveness', function () {
                if ($(titleBar).children('.responsive-menu-items').is(':visible')) {
                    that._minimizeResponsiveTitleBar(titleBar);
                }
            });
            that._expandResponsiveTitleBar(titleBar);
        }
        delay = true;
        setTimeout(function () {
            delay = false;
        }, 350); // prevent double click bugs
    });

    /*
     Prevents issue which causes title bar to display incorrectly when switching from mobile to 
     desktop layout. Issue only arises when h2 has display block on page load. This function 
     replaces the functionality of a css media query adding/removing display block.  
     */
    var pageTitleDisplay;
    this.addFnToResizeQueue(function (windowWidth, contentWidth) {
        if ((!pageTitleDisplay || pageTitleDisplay === 'inline-block') &&
                windowWidth < that._hideSideBarLeftThreshold) {

            $('.responsive-page-title > h2').css({'display': 'block'});
            pageTitleDisplay = 'block';
        } else if ((!pageTitleDisplay || pageTitleDisplay === 'block') &&
                windowWidth >= that._hideSideBarLeftThreshold) {

            $('.responsive-page-title > h2').css({'display': ''});
            pageTitleDisplay = 'inline-block';
        }
    });
};

/**
 * Prevents wider logos from breaking responsive title bar. For wide custom logos, a minimum width
 * must be applied to the layout to prevent the layout from being to wide to display the mobile
 * version of it and too narrow to accomodate all of title bar elements.
 */
x2.LayoutManager.prototype._accountForCustomTitleBarLogo = function () {
    if (!x2.logoWidth)
        return; // no custom logo

    if ($('body').hasClass('disable-mobile-layout')) {
        var baseWidth = 888; // responsive layout not available
    } else {
        var baseWidth = 548; // base width can be narrower because search bar resizes
    }

    /* apply a min-width which will prevent content resizing until the switch to the mobile 
     layout occurs at _hideSideBarLeftThreshold */
    $('#page').css({
        'min-width': (baseWidth + this._logoWidth) + 'px'
    });
    $('#header').css({
        'min-width': (baseWidth + this._logoWidth) + 'px'
    });
};

x2.LayoutManager.prototype._init = function () {
    var that = this;
    that.DEBUG && console.log('LayoutManager init');

    this._setUpMobileLayout();
    this._setUpX2UIElements();
    this._setUpMenuResponsiveness();
    this._setUpWindowResizeEvent();
    this._setUpLeftMenuResponsiveness();
    this._setUpSearchBarResponsiveness();
    this._setUpTitleBarResponsiveness();
    this._accountForCustomTitleBarLogo();

    $(window).resize();
};
