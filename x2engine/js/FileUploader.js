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




/**
 * File Uploader Class to go with the accompanying PHP class. 
 *
 * Example API Usage: 
 *
 * In PHP:
 * $this->widget('FileUploader', array(
 *     'id' => 'myFileUploader'
 * ));
 * 
 * in JS:
 *
 * // Accessing an instance
 * var fileUploader = x2.FileUploader.list['myFileUploader'];
 * fileUploader.toggle();
 *
 * // Attaching a handler
 * // Dropzone Handlers
 * x2.FileUploader.on ('myFileUploader', 'success', function(){
 *     x2.FileUploader.list['myFileUploader'].toggle();
 * });
 *
 * // Calling instance functions statically
 * x2.FileUploader.toggle ('myFileUploader');
 * x2.FileUploader.upload ('myFileUploader');
 * 
 */
x2.FileUploader = (function() {

    function FileUploader(argsDict) {
        var defaultArgs = {

            // Unique id of this file uploader. 
            id: '', 

            // url to upload to (defaults to site/upload by php class)
            url: '',

            // Parameters to set on the media item
            mediaParams: {
                associationType: 'none',
            },

            // Accepted File MIME type (defaults to image/*)
            acceptedFiles: '',
            // max file size in mb
            maxFileSize: 256
        };
        auxlib.applyArgs (this, defaultArgs, argsDict);
        this.element = $('.file-uploader#'+this.id);

        this.init();
        
        // Add this instance to the master list
        FileUploader.append (this);

        x2.Widget.call (this, argsDict);
    }

    FileUploader.prototype = auxlib.create (x2.Widget.prototype);

    FileUploader.prototype.init = function() {
        this.showButton = $('.show-button#'+this.id+'-button');

        this.setUpDropzone ();
        this.setUpEventHandlers ();
    };

    FileUploader.prototype.setUpDropzone = function() {
        var that = this;
        Dropzone.autoDiscover = false;

        // selector for the actual dropzone element.
        var selector = this.element.selector + ' .dropzone';
        var element = document.querySelector (selector);

        // Initialize the dropzone
        var uploadUrl = yii.scriptUrl + that.url;
        if (!Dropzone.isBrowserSupported ())
            uploadUrl = $.param.querystring (uploadUrl, { 'redirect': 1 });
        this.dropzone = new Dropzone (selector, {
            url: uploadUrl,
            paramName: 'upload',
            maxFilesize: this.maxFileSize,
            acceptedFiles: this.acceptedFiles,
            dictFallbackText: ""
        });
        if (!Dropzone.isBrowserSupported ()) {
            // hack to allow dropzone methods to be called normally
            this.dropzone = element.dropzone;

            // hide unsightly browser compatibility messages and remove extra space
            $(selector).find ('.dz-message').hide ();
            $(selector).attr ('style', 'min-height: 0;');
        }

        // Format form data. Adds any mediaParams that were initially set
        // and adds any extra form items, as well as CSRF Token
        this.dropzone.on ("sending", function(file, xhr, formData) {
            formData.append('YII_CSRF_TOKEN', x2.csrfToken);

            // Extra options avaliable
            var options = that.element.find('#options :input').serializeArray();
            for (var i in options) {
                formData.append(options[i].name, options[i].value);
            }

            for (var key in that.mediaParams) {
                formData.append (key, that.mediaParams[key]);
            }

            var recordLinks = $("#Events_recordLinks").val();
            if (recordLinks) {
                formData.append ('recordLinks', recordLinks);
                $("#Events_recordLinks").val('');
                $("#feed_record_links").html('');
            }

        });

    };

    // Sets up all JQuery events
    FileUploader.prototype.setUpEventHandlers = function() {
        var that = this;

        // Set up the upload toggle buttons
        this.showButton.click(function(){
            that.toggle();
        });

        // Set up the upload toggle buttons
        this.element.find('.dz-close').click(function(){
           that.toggle();
        });

        // Have the dropzone appear when hovered over
        this.element.parent().on ('dragover', function(e){
            e.preventDefault();
            if (that.element.is(':hidden')) {
                that.element.slideToggle();
            }
        });
    }

    /************************************************
    * API Methods
    *************************************************/

    // Toggle the frame open or closed
    FileUploader.prototype.toggle = function (state) {
        if (state == true && this.element.is(':visible')) {
            return;
        }

        if (state == false && this.element.is(':hidden')) {
            return;
        }

        this.element.slideToggle();
    }

    FileUploader.prototype.filesQueued = function() {
        return this.dropzone.getQueuedFiles().length;
    }

    FileUploader.prototype.upload = function() {
        return this.dropzone.processQueue();
    }

    /************************************************
    * Static Methods / API 
    *************************************************/

    // List of public methods to attach to the static instance
    FileUploader.publicMethods = ['toggle', 'upload'];

    // Create a static version of all public instance methods
    // allows for an API as so: 
    // 
    // FileUploader.toggle(<ID>) 
    // FileUploader.upload(<ID>) 
    // 
    // This is useful for quick onclick events
    for(var i in FileUploader.publicMethods) {
        var fnName = FileUploader.publicMethods[i];
        FileUploader[fnName] = (function(fnName) {
            return function(id, params) {
                FileUploader.exec(id, fnName, params);
            }
        })(fnName);
    }

    // List of all file uploaders
    FileUploader.list = {};

    // List of event handlers to be attached once 
    // a specific fileUploader instance is instantiated
    FileUploader.handlers = {};


    /** 
     * Appends an instance to the list of open instances.
     * Attaches any event handlers scheduled for it. 
     * @param instance FileUploader instance.
     */
    FileUploader.append = function(instance) {

        //Don't append if this id already exists
        if (typeof FileUploader.list[instance.id] !== 'undefined') {
            return;
        }

        // Append the instance to the master list. 
        FileUploader.list[instance.id] = instance;

        // Check if there are any handlers.
        var handlers = FileUploader.handlers[instance.id];

        // return if no handlers where scheduled. 
        if (typeof handlers === 'undefined') {
            return;
        }

        // Attach all event handlers scheduled
        for (var i in handlers) {
            var h = handlers[i];
            instance.dropzone.on (h.event, h.closure);
        }

    }

    FileUploader.destroy = function (instance) {
        delete FileUploader.list[instance.id];
    }

    /**
     * Attaches an event handler to a File Uploader Instance.
     * 
     * If the file uploader has not been instantiated yet, this method
     * will add it to a queue, and once the instance is created, it will
     * attach the handler
     * 
     * @param  string   id      Name of the fi
     * @param  string   event    dropzone event name e.g. 'success'
     * @param  Function function to call upon the event firing
     */
    FileUploader.on = function(id, event, closure) {

        // If the instance is in the list, attach immediately
        if (typeof FileUploader.list[id] !== 'undefined') {
            FileUploader.list[id].dropzone.on(event, closure);
            return;
        }

        // If the id hasnt been created yet, create a list. 
        if (typeof FileUploader.handlers[id] == 'undefined') {
            FileUploader.handlers[id] = [];
        }

        // Schedule the handler
        FileUploader.handlers[id].push({
            'event': event,
            closure: closure
        });
    }

    /**
     * Executes the function of an instance
     * @param  {string} id       Id of instance
     * @param  {string} function Name of function to call
     * @param  {object} params   Object of params
     * @return {mixed}           Return Val of function called
     */
    FileUploader.exec = function(id, fn, params) {
        // If it is in the list, attach immediately
        if (typeof FileUploader.list[id] === 'undefined') {
            return
        }

        var instance = FileUploader.list[id];

        // If it is in the list, attach immediately
        if (typeof instance[fn] === 'undefined') {
            throw "FileUploader Error: Function "+fn+" does not exists.";
        }

        return instance[fn].call (instance, params);
    }

    return FileUploader;
})();
