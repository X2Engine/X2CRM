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

if(typeof x2 == 'undefined')
    x2 = {};
if(typeof x2.emailProgressControl == 'undefined')
    x2.emailProgressControl = {};

x2.emailProgressControl.elements = {};

x2.emailProgressControl.getElement = function (selector) {
    if(typeof this.elements[selector] == 'undefined') {
        this.elements[selector] = this.container.find(selector);
    }
    return this.elements[selector];
}

x2.emailProgressControl.newSent = function (message)  {

}

x2.emailProgressControl.newSent = function (message)  {
    
}

/**
 * This function will always be called after an email has finished sending.
 */
x2.emailProgressControl.afterSend = function() {};

/**
 * Initial set-up of the email widget
 */
x2.emailProgressControl.init = function() {
    // Tells whether the process is paused
    this.paused = false;
    // Tells whether there's currently a send operation in progress
    this.currentlySending = false;
    // Number of errors
    this.nErrors = 0;
    // Container div
    this.container = $("#emailProgressControl");
    // Progress bar
    this.bar = this.getElement("#emailProgressControl-bar");
    // Control div container
    this.controls = this.getElement("#emailProgressControl-toolbar");
    // Div displaying number of sends total
    this.progressText = this.getElement("#emailProgressControl-text");
    // Last message
    this.textStatus = this.getElement('#emailProgressControl-textStatus');
    // Displays error messages:
    this.errorBox = this.getElement('#emailProgressControl-errors');
    //
    this.throbber = this.getElement('#emailProgressControl-throbber');
    var that = this;
    this.bar.progressbar({
        value: that.sentCount,
        max: that.totalEmails,
        change: function() {
            that.updateTextCount();
        }
    });

    this.updateTextCount();

    // Bind click handler to the pause button
    this.toggleButton = this.controls.find('.startPause');
    this.toggleButton.click(function() {
        if(that.paused) {
            that.start();
        } else {
            that.pause();
        }
    });
}

/**
 * Start or resume sending email by making AJAX requests to the server.
 */
x2.emailProgressControl.start = function () {
    this.paused = false;
    this.toggleButton.text(this.text['Pause']);
    this.send();
}

x2.emailProgressControl.pause = function () {
    this.paused = true;
    this.toggleButton.text(this.text['Resume']);
}

x2.emailProgressControl.errorMessage = function(message) {
    this.getElement('#emailProgressControl-errorContainer').show();
    this.errorBox.append(message+'<br />');
}

/**
 * Recursive AJAX function that works its way through the email queue.
 *
 * This is where both making the AJAX request and updating the progress bar/text
 * should happen.
 */
x2.emailProgressControl.send = function () {
    var that = this;
    if(this.listItems.length == 0) {
        // Halt; all done.
        this.textStatus.text(this.text['Email delivery complete.']);
        this.pause();
        return;
    }
    this.currentlySending = true;
    var listItem = this.listItems.shift();
    this.showThrobber();
    $.ajax({
        url: that.sendUrl+'?campaignId='+that.campaignId+'&itemId='+listItem,
        dataType:'json'
    }).done(function(response){
        that.currentlySending = false;
        // Update text status
        that.textStatus.text(response.message);
        if(!response.error) {
            // Update progress bar:
            that.sentCount++;
            that.bar.progressbar({'value':that.sentCount});
            if(response.undeliverable) { // List it as undeliverable and keep going
                that.errorMessage(response.message);
            }
            if(!that.paused) { // Send the next one!
                that.send();
            }
        } else { // full stop
            that.pause();
            that.listItems.push(listItem); // Add the item back in at the end
            that.errorMessage('<span class="emailFail">'+response.message+'</span>');
        }
    }).fail(function(jqXHR,textStatus,message) {
        that.pause();
        that.currentlySending = false;
        that.listItems.push(listItem); // Add the item back in at the end
        that.errorMessage('<span class="emailFail">'+that.text['Could not send email due to an error in the request to the server.']+' ('+textStatus+' '+jqXHR.errorCode+' '+message+')</span>');
    }).always(function() {
        that.hideThrobber();
        that.afterSend();
    });
}

x2.emailProgressControl.updateTextCount = function() {
    this.progressText.text(this.sentCount + '/' + this.totalEmails);
}

x2.emailProgressControl.showThrobber = function () {
    this.throbber.show();
}

x2.emailProgressControl.hideThrobber = function () {
    this.throbber.hide();
}