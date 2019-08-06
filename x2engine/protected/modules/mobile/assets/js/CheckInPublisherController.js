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




x2.CheckInPublisherController = (function () {
    
function CheckInPublisherController (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        photoAttrName: '',
        translations: {}

    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.Controller.call (this, argsDict);
}

CheckInPublisherController.prototype = auxlib.create (x2.Controller.prototype);

CheckInPublisherController.prototype.setUpForm = function () {
    if (!x2.main.isPhoneGap) return;
    var that = this;
    this.form$ = $.mobile.activePage.find ('form.publisher-form');
    var eventBox$ = this.form$.find ('.event-text-box');

    $.mobile.activePage.find ('.event-publisher').click (function () {
        eventBox$.focus (); 
    });
    
    x2touch.API.getCurrentPosition(function(position) {
         var pos = {
           lat: position.coords.latitude,
           lon: position.coords.longitude
         };
        that.form$.find ('#geoCoords').val(JSON.stringify (pos));
        that.form$.find ('#geoLocationCoords').val("set");
        x2.mobileForm.submitWithFiles (
           that.form$, 
           function (response) {
               try {
                   var data = JSON.parse(response);
                   if (!data['results']) {
                       if (data['error']) {
                           alert(data['error']);
                       }
                       if (data['redirectUrl']) {
                           var url = data['redirectUrl'];
                           $(':mobile-pagecontainer').pagecontainer (
                              'change', url, { transition: 'none' });
                       }
                       return;
                   }
                   var theAddress = data['results'][0]['formatted_address'];
                   $.mobile.activePage.find ('.event-text-box').val(that.translations['Checking in at']+' '+theAddress);
                    alert(that.translations['Thanks for checking in!']);
                    $.mobile.activePage.find('.post-event-button').trigger( "click" );
               } catch (e) {
                   /*
                    * Possibilities: API token not set or API not turned on
                    */
                   alert(e);
                   window.history.back();
               }
               x2.main.refreshContent ();
               $.mobile.loading ('hide');
           }, function (jqXHR, textStatus, errorThrown) {
               $.mobile.loading ('hide');
               x2.main.alert (textStatus, 'Error');
           }
       ); 
        that.form$.find ('#geoLocationCoords').val("unset");
    }, function (error) {
        alert(that.translations['error code']+': ' + error.code    + '\n' +
              that.translations['error message']+': ' + error.message + '\n');
    }, {});
        
};

CheckInPublisherController.prototype.init = function () { 
    x2.Controller.prototype.init.call (this);
    var that = this;
    that.documentEvents.push (x2.main.onPageShow (function () {
        that.setUpForm ();
    }, 'CheckInPublisherController'));
};

return CheckInPublisherController;

}) ();
