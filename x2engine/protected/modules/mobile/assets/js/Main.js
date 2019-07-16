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





if (typeof x2 === 'undefined') x2 = {};

x2.Main = (function () {

var namespace = 'x2.Main';

function Main (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        controllers: {},
        translations: {},
        platform: 'Android',
        pageDepth: 0
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this.prevPage$ = null;
    this.activePage$ = null;
    this.showLeftMenuButton$ = $('#header .show-left-menu-button');
    this.isPhoneGap = typeof cordova !== 'undefined';
    this.init ();
}

Main.onPageCreate = function (fn) {
    if (!Main.onPageCreate.counter)
        Main.onPageCreate.counter = 0;
    else
        Main.onPageCreate.counter++;
    var that = this;
    if (x2.isAjaxRequest) {
        $(function () {
            that.DEBUG && console.log ('ready'); 
            fn (); 
        });
    } else {
        var evtName = 'pagecontainercreate.' + namespace + Main.onPageCreate.counter;
        $(document).on (evtName, function (evt, ui) {
            that.DEBUG && console.log ('create'); 
            fn (); 
        });
    }
};

Main.prototype.getController = function () {
    return $.mobile.activePage && this.controllers[$.mobile.activePage.attr ('data-page-id')] || 
        new x2.Controller;
};

/*
 * http://stackoverflow.com/questions/10730362/get-cookie-by-name
 * @func: function to get Cookie
 */
Main.prototype.getCookie = function (name) {
    var value = "; " + document.cookie;
    var parts = value.split("; " + name + "=");
    if (parts.length == 2){
        return parseInt(parts.pop().split(";").shift(),10);
    } 
    return null;
};

Main.prototype.updateHeader = function () {
    var that = this;

    if (this.platform === 'iOS') {
        that.showLeftMenuButton$ = $('#header .show-left-menu-button');
        that.showLeftMenuButton$.toggle (!x2.isGuest && that.pageDepth === 0);
        that.backButton$ = $('#header .header-back-button');
        that.backButton$.toggle (that.pageDepth > 0);
    } else {
        that.showLeftMenuButton$ = $('#header .show-left-menu-button');
        that.showLeftMenuButton$.toggle (!x2.isGuest);
    }


    that.DEBUG && console.log ('that.getController () = ');
    that.DEBUG && console.log (that.getController ());

    that.settingsMenuButton$ = $('#header .show-settings-menu');
    that.DEBUG && console.log ("$('#settings-menu'); = ");
    that.DEBUG && console.log ($('#settings-menu').length);
    that.DEBUG && console.log ('that.settingsMenuButton$ = ');
    that.DEBUG && console.log (that.settingsMenuButton$);

    that.DEBUG && console.log (that.getController ().hasSettingsMenu && $('#settings-menu').length);

    var visible = !!(that.getController ().hasSettingsMenu && $('#settings-menu').length);

    that.settingsMenuButton$.toggle (visible);
    $.mobile.activePage.toggleClass ('has-settings-menu', visible);
};

Main.prototype.updatePanel = function () {
    if (x2.panel) this.activePage$ && x2.panel.selectItem (this.activePage$.attr ('data-url'));
};

Main.prototype.configurePageShow = function () {
    var that = this;
    that.DEBUG && console.log ('adding page show');

    this.onPageShow (function(){       
        that.prevPage$ = that.activePage$;
        that.activePage$ = $.mobile.activePage ? $('#' + $.mobile.activePage.attr ('id')) : null;
        that.updateHeader ();
        that.updatePanel ();
    });
};

Main.prototype.configureDebug = function () {
    //if (!x2.DEBUG) return;
    $(document).on('pagebeforeshow', function(){       
        //$('#header .show-left-menu-button').click ();
        //$('#panel').panel ('open');
    });
};

Main.prototype.onPageShow = function (fn, customNamespace) {
    customNamespace = typeof customNamespace === 'undefined' ? namespace : customNamespace; 
    var eventName = 'pagecontainershow.' + customNamespace;
    $(document).on (eventName, function (evt, ui) {
        fn (); 
    });
    return eventName;
};

/**
 * True page load event. jQuery mobile's page load event only fires on ajax-loaded pages 
 */
Main.prototype.onPageCreate = function (fn) {
    return Main.onPageCreate (fn);
};

/**
 * Allows panel to be shared between pages. Upon page load, panel is moved from previously 
 * active page to currently active page.
 * Note: disabled to simplify panel updates. This can be reinstated if it becomes important to 
 * optimize panel rendering
 */
Main.prototype.transferPanel = function () {
    var that = this;
    var activePage$;

    if (!$.mobile.activePage || !(activePage$ = $('#' + $.mobile.activePage.attr ('id'))) ||
        !activePage$.length) {

        that.DEBUG && console.log ('no active page');
        return false;
    }
    var uiPanel$ = activePage$.find ('.ui-panel');
    var contents$ = $('#panel-contents').detach ();

    that.DEBUG && console.log ('transferPanel');
    that.DEBUG && console.log (uiPanel$.html ());

    if (!$.trim (uiPanel$.html ()) || uiPanel$.find ('.ui-panel-inner') && 
        !uiPanel$.find ('.ui-panel-inner').children ().length) {

        that.DEBUG && console.log ('transferPanel true');
        var oldAncestorPanelContainer$ = $('#panel-contents').closest ('.x2touch-panel');
        oldAncestorPanelContainer$.empty ();
        uiPanel$.append (contents$);
        return true;
    }
    return false;
};

Main.prototype.refreshPage = function (data) {
    var url = $.mobile.activePage.attr ('data-url');
    var mergeMode = 2;
    url = $.param.querystring (url, data);
    $(':mobile-pagecontainer').pagecontainer (
        'change', url);
};

Main.prototype.setUpLocation = function () {
    var that = this;
    var form$ = $('#geoCoordsForm');   
    $('<input />').attr('type', 'hidden')
          .attr('name', "YII_CSRF_TOKEN")
          .attr('value', x2.csrfToken)
          .appendTo('#geoCoordsForm');

    var locationTrackingFrequency = this.getCookie("locationTrackingFrequency");
    if (locationTrackingFrequency === null){
        locationTrackingFrequency = 60; //every hour
    } 
    var locationTrackingSwitch = this.getCookie("locationTrackingSwitch");
    if (locationTrackingSwitch === null){
        locationTrackingSwitch = 0; //every hour
    } 
   var phpSessionId = this.getCookie("PHPSESSID");
    
   if(locationTrackingSwitch === 1 && phpSessionId !== null) {
        setInterval(function() {
            //your jQuery ajax code
            if (x2.main.isPhoneGap) {
              x2touch.API.getCurrentPosition(function(position) {
                  var pos = {
                     lat: position.coords.latitude,
                     lon: position.coords.longitude
                   };

                   $.mobile.activePage.find ('#geoCoords').val(JSON.stringify (pos));
              }, function (error) {
                  alert('code: '    + error.code    + '\n' +
                        'message: ' + error.message + '\n');
              }, {});         
            }
            x2.mobileForm.submitWithFiles (
                form$, 
                function (data) {
                    
                }, function (jqXHR, textStatus, errorThrown) {
                    $.mobile.loading ('hide');
                    x2.main.alert (textStatus, 'Error');
                }
            );
        }, 1000 * 60 * locationTrackingFrequency); 
        // where locationTrackingFrequency is your every x minutes
    }
    
};

/**
 * Meant to be called after a page is fetched via ajax. Updates parts of the page with updated
 * version contained in server response.
 */
Main.prototype.refreshContent = function () {
    var that = this;
    var activePage$ = $.mobile.activePage ? $('#' + $.mobile.activePage.attr ('id')) : null;
    if (!activePage$) return null;
    //console.log ('refreshContent');
    var newContent$ = $('.refresh-content');
    newContent$.each (function () {
        // data attribute contains selector of elements to update
        var updateSelector = $(this).attr ('data-refresh-selector'); 
        //console.log ('that.activePage$ = ');
        //console.log (activePage$);

        if ($(this).attr ('data-x2-replace-on-refresh')) {
            var newContentInner$ = $(this).detach ();
            newContentInner$.removeClass ('refresh-content');
        } else {
            var newContentInner$ = $(this).detach ().html ();
        }
        //console.log ('newContentInner$ = ');
        //console.log (newContentInner$);

        elemsToUpdate$ = activePage$.find (updateSelector);
        if (!elemsToUpdate$.length) {
            elemsToUpdate$ = $(updateSelector);
        }
        //console.log ('elemsToUpdate$ = ');
        //console.log (elemsToUpdate$);

        // replace first item with updated content and remove the rest. Allows a sequential set
        // of elements to be replaced without having to put them in a container
        elemsToUpdate$.eq (0).replaceWith (newContentInner$);
        elemsToUpdate$.slice (1).remove ();
    });
};

/**
 * Handles persistent page components (e.g. the panel) and partial updates 
 * (e.g. updating portions of the panel)
 */
Main.prototype.configurePartialRefresh = function () {
    var that = this;
    var transferred = false;
    var refreshed = false;

    // jQuery mobile's page removal doesn't always occur on page transfer, so we need to bind
    // panel transfer to two events.

    // using deprecated pageremove event. The new pagecontainerpageremove event doesn't fire  
    // as expected
    $(document).on ('pageremove.configurePartialRefresh' + namespace, 
        function (evt, ui) {

        that.DEBUG && console.log ('remove');
        // content refresh must occur before panel transfer since the new page's panel might contain
        // an updated panel section.
        refreshed = that.refreshContent (); 

        //transferred = that.transferPanel ();
    });
    $(document).on ('pagecontainershow.configurePartialRefresh' + namespace, function (evt, ui) {
        that.DEBUG && console.log ('show transfer');
        if (!refreshed) that.refreshContent ();
        if (true || !transferred) { // prevent this method from being called twice per page load
            //that.transferPanel ();

           //console.log ('that.prevPage$ = ');
            //console.log (that.prevPage$);

            // initial page doesn't get automatically removed by jQuery mobile, which is a problem
            // since navigating back to that page will cause the page to be duplicated in the DOM.
            // This corrects that problem by removing the page if it wasn't removed by jQM.
            // see https://github.com/jquery/jquery-mobile/issues/3249
            if (that.prevPage$ && $('#' + that.prevPage$.attr ('id')).length &&
                !that.prevPage$.attr ('data-is-local-page')) {
                that.prevPage$.removeWithDependents ();
            }
        }
        transferred = false;
    });

};

Main.prototype.displayErrorDialogs = function () {
    var that = this;
    $('.error-dialog').each (function () { 
        if (that.isPhoneGap) {
            x2touch.API.alert (
                $(this).find ('.message').text (),
                $(this).find ('.title').text ());
        } else {
            alert (
                $(this).find ('.title').text () + "\n" +
                $(this).find ('.message').text ());
        }
    });
    $('.errorSummary').each (function () { 
        var title = $(this).children ('p').first ().text ();
        var list$ = $(this).children ('ul');
        if (list$.length) {
            var message = $.makeArray (list$.find ('li').map (function () { 
                return $(this).text (); })).join ("\n");
        } else {
            return; // format could not be parsed
        }
        if (that.isPhoneGap) {
            x2touch.API.alert (
                message, 
                title);
        } else {
            alert (
                title + "\n" + message);
        }
    });
};

Main.prototype.setUpErrorHandling = function () {
    var that = this;
    $(document).on ('pagecontainerloadfailed', function (evt, ui) {
        evt.preventDefault ();
        if (!that.isPhoneGap) {
            ui.deferred.reject (ui.absUrl, ui.options);
            alert (
                ui.errorThrown
            );
        }
        $.mobile.loading ('hide');
    });
    this.onPageShow (function () {
        that.displayErrorDialogs ();
    });
};

Main.prototype.createUrl = function (uri) {
    return yii.absoluteBaseUrl + '/index.php' + uri;
};

Main.prototype.footerFix = function () {
    // add margin to bottom of content in order to prevent fixed footer from obscuring page
    this.onPageShow (function () {
        $('.ui-content').on ('scroll.footerFix', function () {
            if ($('#footer').length && $('#footer').is (':visible')) {
                $(this).attr ('style', 'margin-bottom: ' + $('#footer').outerHeight () + 'px;');
                $.mobile.activePage.find ('.nano-pane').attr ('style', 
                    'margin-bottom: ' + $('#footer').outerHeight () + 'px;');
            } else {
                $(this).attr ('style', 'margin-bottom: 0px;');
                $.mobile.activePage.find ('.nano-pane').attr ('style', 
                    'margin-bottom: 0px;');
            }
//            var slider$ = $.mobile.activePage.find ('.nano-slider');
//            if (!slider$.attr ('x2-slider-height')) {
//                slider$.attr ('x2-slider-height', slider$.height ());
//            }
//            $.mobile.activePage.find ('.nano-slider').height (
//                $.mobile.activePage.find ('.nano-slider').attr ('x2-slider-height') - 
//                    $('#footer').outerHeight ());
        });
    });
};


Main.prototype.instantiateNano = function (elem$) {
    //if (elem$.parent ().hasClass ('nano')) return;
    elem$.parent ().addClass ('nano');
    elem$.addClass ('nano-content');

    elem$.parent ().nanoScroller({
        iOSNativeScrolling: this.platform === 'iOS',
        preventPageScrolling: this.platform === 'iOS'
    });
    elem$.scrollstart (function () {
        $(this).closest ('.nano').addClass ('hover');
    });
    elem$.scrollstop (function () {
        $(this).closest ('.nano').removeClass ('hover');
    });
};


Main.prototype.setUpScrollBars = function () {
    var that = this;
     
    if (this.isPhoneGap) {
        this.onPageShow (function () {
            var activePage$ = $.mobile.activePage;
            that.instantiateNano (activePage$.find ('.innermost-content-container'));
        });
    }
     
};

Main.prototype.setUpOrientationChange = function () {
    $(window).on ('orientationchange', function () {
        x2.panel && x2.panel.close (); 
    });
};

Main.prototype.alert = function (message, title) {
     
    if (this.isPhoneGap) {
        x2touch.API.alert (
            message, title
        );
    } else {
     
        window.alert (message);
     
    }
     

};

Main.prototype.confirm = function (message, title, buttons, callback) {
    if (x2.UNIT_TESTING) {
        callback ();
        return;
    }
     
    if (this.isPhoneGap) {
        x2touch.API.confirm (
            message,
            title,
            buttons,
            function (index) {
                if (index === 1) callback ();
            }
        );
    } else {
     
        if (window.confirm (title + "\n" + message)) {
            callback ();
        }
     
    }
     
};

Main.prototype.checkForExternalLink = function (a$, url) {
    var that = this;
    var urlRegex = new RegExp ('^' + that.getBaseUrl ().replace (
        // escape special characters
        /([-\/\\^$*+?.()|[\]{}])/g, '\\$1'));
    if (url &&
        url.match (/^https?\/\//) &&
        (a$.attr ('rel') === 'external' ||
         !url.match (/^#/) && 
         !url.match (urlRegex))) {

         
        if (that.isPhoneGap) { 
            x2touch.API.openExternalLink (url);
        } else {
         
            window.open (url, '_blank');
         
        }
         
        return false;
    } 
};

/**
 * Prevents user generated links from directing jqm to an invalid page. 
 */
Main.prototype.setUpLinkClick = function () {
    var that = this;

    $(document).on ('click', 'a', function () {
        var href = $(this).attr ('href');

        if ($(this).hasClass ('requires-confirmation')) {
            that.confirm (
                $.trim ($(this).siblings ('.confirmation-text').text ()), 
                ' ', 
                [
                    that.translations.confirmOkay, 
                    that.translations.confirmCancel
                 ],
                function () {
                    $(':mobile-pagecontainer').pagecontainer (
                        'change', href);
                });
            return false;
        }

        if ($(this).hasClass ('file-download-link')) {
             
            if (that.isPhoneGap) {
                x2touch.API.downloadFile (
                    href, $.trim ($(this).attr ('data-x2-filename')), function () {
                }, function () {
                }, {
                    includeSessionId: true
                });
            } else {
             
                window.location = href;
             
            }
             
            return false;
        }
        return that.checkForExternalLink ($(this), href);
    });
};

Main.prototype.getBaseUrl = function () {
     
    if (this.isPhoneGap) {
        return x2touch.API.getBaseUrl ();
    } else {
     
        return yii.absoluteBaseUrl;
     
    }
     
};

/**
 * Fixes fixed corner button rendering issues in iOS (related to scrolling) and more obscure 
 * rendering issue on action history screen (button animation causes tab labels to disappear)
 */
Main.prototype.fixedCornerButtonFixes = function () {
    this.onPageShow (function(){       
        $.mobile.activePage.children ('.fixed-corner-button').remove ();
        $.mobile.activePage.append ($.mobile.activePage.find ('.fixed-corner-button').detach ());
    }); 
};

Main.prototype.setUpBackButton = function () {
    $(document).on ('click', '#header .header-back-button', function () {
        history.back (); 
    });
};

Main.prototype.init = function () {
    var that = this;
    $(document).on ('pagecontainerbeforeload', function () {
        var controller = that.getController ();
        if (controller) controller.destroy ();
    });
    this.configurePageShow (); 
    this.configurePartialRefresh (); 
    this.setUpErrorHandling ();
    this.footerFix ();
    this.configureDebug (); 
    this.setUpScrollBars (); 
    this.setUpOrientationChange (); 
    this.setUpLinkClick (); 
    if (this.platform === 'iOS') {
        this.setUpBackButton (); 
    }
    this.fixedCornerButtonFixes (); 
    this.setUpLocation ();
};

return Main;

}) ();

