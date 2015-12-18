/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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
                    if (requestedUrl && requestedUrl.match (/mobile\/login$/) &&
                        !options.url.match (/mobile\/login$/)) {

                        if (x2.main.isPhoneGap) {
                            x2touch.API.refresh ();
                        } else {
                            window.location = requestedUrl;
                        }
                        return;
                    }
                }
                oldSuccess.apply (this, Array.prototype.slice.call (arguments));
            };
        }

        return nativeAjax.call ($, options);
    };
}) ();

