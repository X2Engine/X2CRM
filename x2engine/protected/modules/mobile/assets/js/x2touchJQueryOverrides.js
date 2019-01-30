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




(function () {
    var nativeAjax = $.ajax; 
     
    /**
     * Configures logout due to session expiration. Also see Panel.prototype.setUpItemBehavior. 
     * A full page refresh must be performed on logout in order to remove sensitive data from the 
     * DOM and JS.
     * 
     * Copyright 2005, 2014 jQuery Foundation, Inc. and other contributors
     * Released under the MIT license
     * http://jquery.org/license
     */
    $.ajax = function (url, options) {

        // If url is an object, simulate pre-1.5 signature
        if ( typeof url === "object" ) {
            options = url;
            url = undefined;
        }

        // Force options to be an object
        options = options || {};

        if ($.type (options.success) === 'function') {
            var oldSuccess = options.success;
            options.success = function (data, textStatus, jqXHR) {
                if (jqXHR && options.url) {
                    // a custom HTTP header is used to access the request URL, allowing us
                    // to determine whether we're being redirected to the login screen
                    var requestedUrl = jqXHR.getResponseHeader ('X2-Requested-Url');

                    if (requestedUrl && requestedUrl.match (/mobile\/login(\?.*)?$/) &&
                        !options.url.match (/mobile\/login(\?.*)?$/)) {

                        if (x2.main.isPhoneGap) {
                            x2touch.API.refresh ();
                        } else {
                            window.location = requestedUrl.replace (/\?.*$/, '');
                        }
                        return;
                    }
                }
                oldSuccess.apply (this, Array.prototype.slice.call (arguments));
            };
        }

        return nativeAjax.call ($, options);
    };


    /**
     * Base jQuery mobile is unable to recover from errors occurring in JS loaded during a 
     * page transition. Here we override the jQM method responsible for inserting ajax-fetched
     * assets into the DOM, wrapping the base method in a try/catch. In the event of an error, 
     * a full page refresh is performed (the DOM is already polluted with extra assets, so it 
     * would take a lot of cleanup work to recover). Without performing the page refresh, the 
     * client would possibly get stuck in a broken state, rendering the app unusable until
     * forceably restarted.
     */
    var nativeFn = $.mobile.pagecontainer.prototype._loadSuccess;
    $.mobile.pagecontainer.prototype._loadSuccess = function () {
        var args = Array.prototype.slice.call (arguments);
        var fn = nativeFn.apply (this, args);
        var absUrl = args[0];
        var settings = args[2];
        var deferred = args[3];
        var that = this;
        return function () {
            var args = Array.prototype.slice.call (arguments);
            try {
                fn.apply (that, Array.prototype.slice.call (arguments));
            } catch (e) {
                if (x2.main.isPhoneGap) {
                    x2touch.API.alert (
                        'An unrecoverable error has occurred.', 'We\'re sorry', 
                        function () {
                            x2touch.API.refresh ();
                        });
                } else {
                    alert ('An unrecoverable error has occured.');
                    window.location = window.location;
                }
            }
        };
    };
      
    function isLocal() {
        return true;
    }

    /**
     * jQM's isLocal() is broken when navigating via ajax to a page with tabs. Here we replace
     * the _processTabs method with a copy that calls our locally defined isLocal().
     * Note: this breaks ajax-fetched tabs
     * 
     * Copyright 2005, 2014 jQuery Foundation, Inc. and other contributors
     * Released under the MIT license
     * http://jquery.org/license
     */
    $.ui.tabs.prototype._processTabs = function() {
		var that = this;

		this.tablist = this._getList()
			.addClass( "ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all" )
			.attr( "role", "tablist" );

		this.tabs = this.tablist.find( "> li:has(a[href])" )
			.addClass( "ui-state-default ui-corner-top" )
			.attr({
				role: "tab",
				tabIndex: -1
			});

		this.anchors = this.tabs.map(function() {
				return $( "a", this )[ 0 ];
			})
			.addClass( "ui-tabs-anchor" )
			.attr({
				role: "presentation",
				tabIndex: -1
			});

		this.panels = $();

		this.anchors.each(function( i, anchor ) {
			var selector, panel, panelId,
				anchorId = $( anchor ).uniqueId().attr( "id" ),
				tab = $( anchor ).closest( "li" ),
				originalAriaControls = tab.attr( "aria-controls" );

			// inline tab
			if ( isLocal( anchor ) ) {
				selector = anchor.hash;
				panel = that.element.find( that._sanitizeSelector( selector ) );
			// remote tab
			} else {
				panelId = that._tabId( tab );
				selector = "#" + panelId;
				panel = that.element.find( selector );
				if ( !panel.length ) {
					panel = that._createPanel( panelId );
					panel.insertAfter( that.panels[ i - 1 ] || that.tablist );
				}
				panel.attr( "aria-live", "polite" );
			}

			if ( panel.length) {
				that.panels = that.panels.add( panel );
			}
			if ( originalAriaControls ) {
				tab.data( "ui-tabs-aria-controls", originalAriaControls );
			}
			tab.attr({
				"aria-controls": selector.substring( 1 ),
				"aria-labelledby": anchorId
			});
			panel.attr( "aria-labelledby", anchorId );
		});

		this.panels
			.addClass( "ui-tabs-panel ui-widget-content ui-corner-bottom" )
			.attr( "role", "tabpanel" );
	};


}) ();

