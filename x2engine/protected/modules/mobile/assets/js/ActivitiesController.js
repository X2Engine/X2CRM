/* global x2, x2touch */

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




x2.ActivitiesController = (function () {

function ActivitiesController (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this.feed$ = $('.record-index-list-view.activity-feed');
    x2.RecordIndexControllerBase.call (this, argsDict);
}

ActivitiesController.prototype = auxlib.create (x2.RecordIndexControllerBase.prototype);

ActivitiesController.prototype.setUpEventClick = function () {
    var that = this;
    var clickedLink$ = null;
    this.feed$.find ('.record-list-item a').click (function () {
        if (x2.main.checkForExternalLink ($(this).attr ('href')) !== false) {
            clickedLink$ = $(this);
        } else {
            return false;
        }
    });
    this.feed$.find ('.record-list-item').click (function () {
        if (!clickedLink$) { 
            var comments$ = $(this).find ('.comments');
            if (comments$.length) {
                $(':mobile-pagecontainer').pagecontainer (
                    'change', comments$.attr ('data-x2-url'), { transition: 'none' }); 
            }
        } else {
            var url = clickedLink$.attr ('href');
            $(':mobile-pagecontainer').pagecontainer (
                'change', url, { transition: 'none' }); 
        }
        clickedLink$ = null;
        return false;
    });
};

ActivitiesController.prototype.setUpPublisher = function () {
    var that = this;
    this.publisher$ = $('.profile-mobileActivity .event-publisher-dummy');
    var clickedLink$ = null;
    this.publisher$.find ('a').click (function (evt) {
        if (x2.main.checkForExternalLink ($(this).attr ('href')) !== false) {
            clickedLink$ = $(this);
        } else {
            return false;
        }
    });
    this.publisher$.click (function () {
        if (!clickedLink$) { 
            var url = that.publisher$.attr ('data-x2-href');
            $(':mobile-pagecontainer').pagecontainer (
                'change', url, { transition: 'none' }); 
        } else {
            var url = clickedLink$.attr ('href');
            $(':mobile-pagecontainer').pagecontainer (
                'change', url, { transition: 'none' }); 
        }
        clickedLink$ = null;
        return false;
    });
};

ActivitiesController.prototype.init = function () {
    x2.RecordIndexControllerBase.prototype.init.call (this);
    this.setUpEventClick ();
    this.setUpPublisher ();
};

return ActivitiesController;

}) ();
