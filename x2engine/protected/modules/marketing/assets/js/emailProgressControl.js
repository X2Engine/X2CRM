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




x2.EmailProgressControl = (function() {

    function EmailProgressControl(argsDict) {
        var defaultArgs = {

            sentCount: 0,
            totalEmails: null,
            listItems: [],
            sendUrl: '',
            campaignId: null,
            translations: {
                resume: '',
                pause: '',
                error: '',
                confirm: ''
            },

            paused:  false,
            currentlySending:  false, // Tells whether there's currently a send operation in progress
            nErrors:  0, // Number of errors
            containerSelector:  "#emailProgressControl",
            elements: {}
        };

        auxlib.applyArgs (this, defaultArgs, argsDict);
        this.init();
    }


    /**
     * Initial set-up of the email widget
     */
    EmailProgressControl.prototype.init = function() {
        var that = this;
        this.setUpSelectors();

        this.bar.progressbar({
            value: that.sentCount,
            max: that.totalEmails,
            change: function() {
                that.updateTextCount();
            }
        });

        this.updateTextCount();
        this.setUpButtons();

        // And now finally:
        if(that.listItems.length > 0 && !this.paused)
            that.start();
        else
            that.pause();

    }

    /**
     * Sets up the queries that the widget uses
     */
    EmailProgressControl.prototype.setUpSelectors = function() {
        this.container = $(this.containerSelector);
        
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
        
        this.throbber = this.getElement('#emailProgressControl-throbber');
    }

    /**
     * Sets up the button click behaviors
     */
    EmailProgressControl.prototype.setUpButtons = function() {
        var that = this;

        // Pause Button
        this.toggleButton = this.controls.find('.startPause');
        this.toggleButton.click(function() {
            if(that.paused) {
                that.start();
            } else {
                that.pause();
            }
        });
        this.controls.find('.refresh').click(this.refresh);

        // Stop Button
        $("#campaign-toggle-button").bind("click",function(e){
            e.preventDefault();
            var element = this;
            if(that.paused) {
                $(element).parents("form").submit();
            } else {
                that.afterSend = function() {
                    $(element).parents("form").submit();
                }
            }
        });

        // Ask the user if they would really like to cancel the current campaign
        $("#campaign-complete-button").bind("click.confirm",function(e){
            e.preventDefault();
            var element = this;
            var proceed = that.listItems.length == 0;

            if(!proceed)
                proceed = confirm(that.translations['confirm']);

            if(proceed) {
                if(that.paused) {
                    $(element).parents("form").submit();
                } else {
                    that.afterSend = function() {
                        $(element).parents("form").submit();
                    }
                }
            } else {
                that.afterSend = function(){};
            }
        });
    }


    EmailProgressControl.prototype.getElement = function (selector) {
        if(typeof this.elements[selector] == 'undefined') {
            this.elements[selector] = this.container.find(selector);
        }
        return this.elements[selector];
    }

    /**
     * This function will always be called after an email has finished sending.
     */
    EmailProgressControl.prototype.afterSend = function() {};

    /**
     * Start or resume sending email by making AJAX requests to the server.
     */
    EmailProgressControl.prototype.start = function () {
        this.paused = false;
        this.showThrobber();
        this.toggleButton.find('.button-text').text(this.translations['pause']);
        this.toggleButton.find('.fa-pause').show();
        this.toggleButton.find('.fa-play').hide();
        this.send();
    }

    EmailProgressControl.prototype.pause = function () {
        this.paused = true;
        this.hideThrobber();
        this.toggleButton.find('.button-text').text(this.translations['resume']);
        this.toggleButton.find('.fa-pause').hide();
        this.toggleButton.find('.fa-play').show();
    }

    EmailProgressControl.prototype.errorMessage = function(message) {
        this.getElement('#emailProgressControl-errorContainer').show();
        this.errorBox.append(message+'<br />');
    }

    EmailProgressControl.prototype.refresh = function () {
        if(typeof x2.campaignChart != "undefined")
            x2.campaignChart.chart.getEventsBetweenDates();
        $.fn.yiiGridView.update("campaign-grid", {
            data: {
                "id_page": 1
            }
        })

    }

    /**
     * Recursive AJAX function that works its way through the email queue.
     *
     * This is where both making the AJAX request and updating the progress bar/text
     * should happen.
     */
    EmailProgressControl.prototype.send = function () {
        var that = this;
        if(this.listItems.length == 0) {
            // Halt; all done.
            this.textStatus.text(this.translations['complete']);
            this.pause();
            return;
        }
        this.currentlySending = true;
        var listItem = this.listItems.shift();
        $.ajax({
            url: that.sendUrl+'?campaignId='+that.campaignId+'&itemId='+listItem,
            dataType:'json',
            beforeSend: function () {that.showThrobber();}
        }).done(function(response){
            that.currentlySending = false;
            // Update text status
            that.textStatus.text(response.message);
            if(!(response.error && response.fullStop)) {
                // Update progress bar:
                that.sentCount++;
                that.bar.progressbar({'value':that.sentCount});
                if(response.undeliverable) { // List it as undeliverable and keep going
                    that.errorMessage(response.message);
                }
                if (response.warning) {
                    that.errorMessage('<span class="warning">'+response.message+'</span>');
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

    EmailProgressControl.prototype.updateTextCount = function() {
        this.progressText.text(this.sentCount + '/' + this.totalEmails);
    }

    EmailProgressControl.prototype.showThrobber = function () {
        this.throbber.show();
    }

    EmailProgressControl.prototype.hideThrobber = function () {
        this.throbber.hide();
    }


    return EmailProgressControl;
})();

