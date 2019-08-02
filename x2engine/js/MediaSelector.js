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
 * Media Selector for CKEditor
 * See js/ckeditor/plugins/imageSelector/plugin.js
 * @author Alex Rowe <alex@x2engine.com>
 */
x2.MediaSelector = (function() {
    function MediaSelector(argsDict) {
        var defaultArgs = {
            translations: {},
        };
        
        auxlib.applyArgs (this, defaultArgs, argsDict);
        this.init ();
    }

    /************************************************
    * Public Methods / API
    *************************************************/

    /**
     * Closes the Image Selector Dialog
     */ 
    MediaSelector.prototype.closeWindow = function () {
        this.element.dialog('close');
    };

    /**
     * Opens the Dialog, requires a ckeditor instance. This is called
     * from the CKEditor plugin. 
     */ 
    MediaSelector.prototype.openWindow = function (editor) {
        if (typeof editor !== 'undefined') {
            this.ckeditor = editor;
        }

        this.element.dialog('open');
        this.refresh();
    };

    /**
     * Inserts the Selected image into the ckeditor instance
     */
    MediaSelector.prototype.insertImage = function () {
        var that = this;
        
        // Clone the image tag on the selected element
        var clone = this.selected.clone ()

        // Apply extra options (Size)
        var options = auxlib.formToJSON (this.element.find('#media-options'));
        clone.find('img').attr (options);
        // Width / height

        // Insert the html into the instance
        this.ckeditor.insertHtml(clone.html());
    };

    /**
     * Refreshes the Yii List view to show newly added images
     */
    MediaSelector.prototype.refresh = function () {
        $.fn.yiiListView.update('media-list');
    }

    /**
    * Selects a .media-square Elements.
    * This will highlight it and bring up the details.
    * @param  {Html Element} element The element to be selected
    */
    MediaSelector.prototype.selectSquare = function(element) {
        var that = this;

        // Add The Active Class and remove all other active
        this.element.find('.media-square').removeClass('active');
        $(element).addClass('active');

        // Set the selected square
        var data = $(element).data();

        // Retrieve the detail quick view
        $.ajax({
            url: yii.scriptUrl + '/media/quickView',
            data: {
                id: data.id,
            },
            success: function (data) {
                that.detailView.html($(data));
            }

        });

        this.selected = $(element);
    }

    /************************************************
    * Private Methods
    *************************************************/
    MediaSelector.prototype.init = function () {
        var that = this;

        // Set up Variables and Elements
        this.element    = $('#image-selector');
        this.detailView = this.element.find ('#media-details');

        // The currently selected Thumbnail
        this.selected = null;

        // The instance of the CKeditor
        this.ckeditor = null;

        // Set up the dialog functionality
        this.setUpDialog ();

        // Set up all Event Handlers
        this.setUpEventHandling ();

        // Select first image
        var first = this.element.find('.media-square').first();
        if (typeof first !== 'undefined' && typeof first.data('id') !== 'undefined') {
            this.selectSquare (first);
        }
    }

    /**
     * Sets up the dialog that will pop up when button is clicked
     */
    MediaSelector.prototype.setUpDialog = function () {
        var that = this;

        // Create a dialog in middle of screen
        this.element.dialog({
            title: that.translations.title,
            autoOpen: false,
            dialogClass:'media-dialog',
            width: '50%',
            position: { my: "left top", at:"left+25% top+10%"},
            buttons: [
                {
                    text: that.translations['Insert Image'], 
                    class:"blue",
                    click: function (e){
                        e.preventDefault();
                        that.insertImage ();
                        that.closeWindow ();
                    }
                },
                {
                    text: that.translations['Close'], 
                    click: function (e){
                        e.preventDefault();
                        $(this).dialog('close');
                    }
                }
            ]
        });
    }

    /**
     * Sets up all event handling 
     */
    MediaSelector.prototype.setUpEventHandling = function () {
        var that = this;

        // On successful upload, refresh the media
        x2.FileUploader.on('mediaSelector', 'success', function(){
            that.refresh();
        });

        // Set up square selection
        this.element.on('click','.media-square',function(){
            that.selectSquare(this);
        });

        this.element.find('#delete').click(function(){
            if(!confirm(that.translations.deleteText)) return;

            var id = that.selected.data('id');
            $.ajax({
                type: 'post',
                url: yii.scriptUrl + '/media/delete/' + id,
                success: function(){
                    that.refresh()
                }
            });
        });

    }

    return MediaSelector;
})(); 