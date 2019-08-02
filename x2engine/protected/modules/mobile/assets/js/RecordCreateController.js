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

x2.RecordCreateController = (function () {

function RecordCreateController (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        translations: {},
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.Controller.call (this, argsDict);
}

RecordCreateController.prototype = auxlib.create (x2.Controller.prototype);

RecordCreateController.prototype.importContact = function () {
    var that = this;
    this.importButton$ = $('#header .contact-import-button');
    this.importButton$.click (function () {
        x2touch.API.getContact (function(contactInfo){
            x2.main.activePage$.find ('#Contacts_firstName').val(contactInfo.name.givenName);
            x2.main.activePage$.find ('#Contacts_lastName').val(contactInfo.name.familyName);
            x2.main.activePage$.find ('#Contacts_company_id').val(contactInfo.id);
            if (contactInfo.orgranizaions[0] !== null){
                x2.main.activePage$.find ('#Contacts_company').val(contactInfo.orgranizaions[0].name);
                x2.main.activePage$.find ('#Contacts_title').val(contactInfo.organizations[0].title);
            }
            if (contactInfo.emails[0] !== null) {
                x2.main.activePage$.find ('#Contacts_email').val(contactInfo.emails[0].value);
            }
            if (contactInfo.phoneNumbers[0] !== null) {
                x2.main.activePage$.find ('#Contacts_phone').val(contactInfo.phoneNumbers[0].value);
            }
            x2.main.activePage$.find ('#Contacts_backgroundInfo').val(contactInfo.note);
            if (contactInfo.addresses[0] !== null){
                x2.main.activePage$.find ('#Contacts_address').val(contactInfo.addresses[0].streetAddress);
                x2.main.activePage$.find ('#Contacts_city').val(contactInfo.addresses[0].locality);
                x2.main.activePage$.find ('#Contacts_state').val(contactInfo.addresses[0].region);
                x2.main.activePage$.find ('#Contacts_zipcode').val(contactInfo.addresses[0].postalCode);
                x2.main.activePage$.find ('#Contacts_country').val(contactInfo.addresses[0].country);
            }
            x2.main.activePage$.find ('#Contacts_assignedTo_assignedToDropdown').val("");
            x2.main.activePage$.find ('#Contacts_visibility').val("");
        },function(err){
            alert(that.translations['Error']+': ' + err);
        });
        form$.submit ();
    });
};

RecordCreateController.prototype.importProduct = function () {
    var that = this;
    this.importButton$ = $('#header .product-import-button');
    this.importButton$.click (function () {
        x2touch.API.barcodeScanner (function(result){
            /*alert("We got a barcode\n" +
                  "Result: " + result.text + "\n" +
                  "Format: " + result.format + "\n" +
                  "Cancelled: " + result.cancelled);*/
            x2.main.activePage$.find ('#Product_description').val(
                x2.main.activePage$.find ('#Product_description').val() 
                + "\n"
                + result.text
            );
        },function(err){
            alert("Scanning failed: " + error);
        },{
            /*
            preferFrontCamera : true, // iOS and Android
            showFlipCameraButton : true, // iOS and Android
            showTorchButton : true, // iOS and Android
            torchOn: true, // Android, launch with the torch switched on (if available)
            prompt : "Place a barcode inside the scan area", // Android
            resultDisplayDuration: 500, // Android, display scanned text for X ms. 0 suppresses it entirely, default 1500
            formats : "QR_CODE,PDF_417", // default: all but PDF_417 and RSS_EXPANDED
            orientation : "landscape", // Android only (portrait|landscape), default unset so it rotates with the device
            disableAnimations : true, // iOS
            disableSuccessBeep: false // iOS
             */
        });
        form$.submit ();
    });
};

RecordCreateController.prototype.exportContact = function () {
    var that = this;
    var contactInfo = {};
    var firstName = x2.main.activePage$.find ('#Contacts_firstName').val();
    var lastName = x2.main.activePage$.find ('#Contacts_lastName').val();
    var companyID = x2.main.activePage$.find ('#Contacts_company_id').val();
    var companyName = x2.main.activePage$.find ('#Contacts_company').val();
    var title = x2.main.activePage$.find ('#Contacts_title').val();
    var email = x2.main.activePage$.find ('#Contacts_email').val();
    var phoneNum = x2.main.activePage$.find ('#Contacts_phone').val();
    var backgroundInfo = x2.main.activePage$.find ('#Contacts_backgroundInfo').val();
    var address = x2.main.activePage$.find ('#Contacts_address').val();
    var city = x2.main.activePage$.find ('#Contacts_city').val();
    var state = x2.main.activePage$.find ('#Contacts_state').val();
    var zipcode = x2.main.activePage$.find ('#Contacts_zipcode').val();
    var country = x2.main.activePage$.find ('#Contacts_country').val();
    var assignedTo = x2.main.activePage$.find ('#Contacts_assignedTo_assignedToDropdown').val();
    var visibility = x2.main.activePage$.find ('#Contacts_visibility').val();
    contactInfo.firstName = firstName;
    contactInfo.lastName = lastName;
    contactInfo.companyID = companyID;
    contactInfo.companyName = companyName;
    contactInfo.title = title;
    contactInfo.email = email;
    contactInfo.phoneNum = phoneNum;
    contactInfo.backgroundInfo = backgroundInfo;
    contactInfo.address = address;
    contactInfo.city = city;
    contactInfo.state = state;
    contactInfo.zipcode = zipcode;
    contactInfo.country = country;
    contactInfo.assignedTo = assignedTo;
    contactInfo.visibility = visibility;
    
    this.importButton$ = $('#header .export-button');
    //pass in contact info to be saved in device's contacts
    this.importButton$.click (function () {
        if (confirm(that.translations['Would you like to export this contact']+'?')) {
            // Save it!
            x2touch.API.setContact (function (contact) {
                alert(that.translations['Export Success']);
            }, function(contactError){
                alert(that.translations['Error']+' = ' + contactError.code);
            },contactInfo);
        } else {
            // Do nothing!
        }
    });
};

RecordCreateController.prototype.setUpForm = function () {
    var that = this;
    this.submitButton$ = $('#header .submit-button');
    this.form$ = $.mobile.activePage.find ('form');
//    this.submitButton$.click (function () {
//        that.form$.submit ();
//    });
    var homeButton$ = $.mobile.activePage.find('#home-btn');
    homeButton$.hide();
};

RecordCreateController.prototype.init = function () {
    var that = this;
    this.documentEvents.push (x2.main.onPageShow (function () {
        that.form$ = $.mobile.activePage.find ('form');
        that.exportContact ();
        that.importContact ();
        that.importProduct ();
        that.setUpForm ();
    }, this.constructor.name));
};


return RecordCreateController;

}) ();
