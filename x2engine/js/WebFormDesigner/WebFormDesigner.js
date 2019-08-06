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




x2.WebFormDesigner = (function() {

    /*
    Base prototype. Should not be instantiated. 
    */

    function WebFormDesigner (argsDict) {
        var that = this;

        // properties that can be set with constructor arguments
        var defaultArgs = {
            translations: [], // used for various web form text
            baseUrl: '',
            iframeSrc: '', 
            externalAbsoluteBaseUrl: '', // used for specifying web form generation script source
            saveUrl: '', // used to save the web form settings
            savedForms: {}, // used to cache previously viewed forms
            fields: [],
            colorfields: [],
            deleteFormUrl: '',
            listId: null
        };
        auxlib.applyArgs (this, defaultArgs, argsDict);

        $(document).on ('ready', function () {
            that._init ();
        });
    }


    /*
    Public static methods
    */

    /*
    Private static methods
    */

    WebFormDesigner.sanitizeInput = function (value) {
        return encodeURIComponent(value.replace(/(^[ ]*)|([ ]*$)|([^a-zA-Z0-9#,])/g, ''));
    }

    /*
    Public instance methods
    */

    /*
    Private instance methods
    */
    WebFormDesigner.prototype._saveForm = function (json) {
        var that = this;
        $.ajax({
            url: that.saveUrl,
            type: 'POST',
            data: json,
            success: function (data, status, xhr) {
                that.saved (data, status, xhr);
            }
        });
    }

    /*
    Set up form submission behavior.
    */
    WebFormDesigner.prototype._setUpFormSubmission = function () {
        var that = this;
        $('#web-form-submit-button').on('click', function(evt) {
            evt.preventDefault ();
            that._refreshForm ();

            var formJSON = auxlib.formToJSON ($('#web-form-designer-form'));
            formJSON.name = $('#web-form-name').val();
            that._saveForm(formJSON);

            return false;
        });

        $('#generate').click(function(){
            $('#web-form-submit-button').click();
        });

        /*********************************
        * Resets form and saves as a new Form
        ********************************/
        $('#web-form-new-button').click(function(){
            that._updateFields ({
                params: that.defaultJSON,
                name: $('#web-form-new-name').val()
            });
            

            // Reset Field Container
            $('#sortable2 li')
            .not('[name=firstName], [name=lastName]')
            .not('[name=backgroundInfo], [name=email]')
            .prependTo($('#sortable1'));

            // Clear Code mirror stuff
            for (var i in that.codemirror) {
                that.codemirror[i].setValue('');
            }
            

            // Wipe inputs
            $('#web-form-designer-form :input')
             .not(':button, :submit, :reset, [type="hidden"]')
             .val('')
             .removeAttr('checked')
             .removeAttr('selected');

            $('#web-form-submit-button').click();
            $('#new-field').slideToggle();
        });
    };

    /*
    Sets up the web form designer
    */
    WebFormDesigner.prototype._init = function () {
        var that = this;

        // set up embedded code container behavior
        $('#embedcode').focus(function() {
            $(this).select();
        });
        $('#embedcode').mouseup(function(e) {
            e.preventDefault();
        });
        $('#embedcode').focus();
        
        $('#unsubembedcode').focus(function() {
            $(this).select();
        });
        $('#unsubembedcode').mouseup(function(e) {
            e.preventDefault();
        });
        $('#unsubembedcode').focus();

        // instantiate color pickers
        $.each(that.colorfields, function(i, field) {
            var selector = '#' + field;
            x2.colorPicker.setUp ($(selector));
            $(selector).on ('change', function () { that.updateParams (); });
        });
        
        // set up form field behavior
        $.each(that.fields, function(i, field) {
            $('#'+field).on('change', function () { that.updateParams (); });
        });

        // set up save web form button behavior
        $('#web-form-save-button').click(function(e) {

            // check form empty input
            if ($.trim($('#web-form-name').val()).length === 0) { // invalid, show errors
                $('#web-form-name').addClass('error');
                $('[for="web-form-name"]').addClass('error');
                $('#web-form-save-button').after('<div class="errorMessage">'+
                    that.translations.nameRequiredMsg+'</div>');
                e.preventDefault(); //has no effect
                return false;
            } else { // name validated, remove error messages
                $('#web-form-name').removeClass('error');
                $('[for="web-form-name"]').removeClass('error');
                $('#web-form-save-button').next('.errorMessage').remove ();
            }
        });

        that._setUpFormSubmission ();

        // set up saved form selection behavior
        $('#saved-forms').on('change', function() {
            var id = $(this).val();
            that._showHideDeleteButton ();

            // clear old form, populate form with saved input
            that._clearFields();
            if (id != 0) {
                var match = $.grep(that.savedForms, function(el, i) {
                    return id == el.id;
                });
                that._updateFields(match[0]);
                $('#web-form-inner').show();
            } else {
                that._updateFields({
                    params: that.defaultJSON,
                });
                
                $('#sortable2 li').appendTo($('#sortable1'));
                
                
                $('#web-form-inner').hide();
            }

            // update iframe and embedded code
            that.updateParams();
            // $('#embedcode').focus();  
            $.each(that.colorfields, function(i, field) {
                if ($('#'+field).val () === '') {
                    x2.colorPicker.addCheckerImage ($('#'+field));
                } else {
                    x2.colorPicker.removeCheckerImage ($('#'+field));
                }
            });

            // extra behaviors set in child prototype
            that._afterSavedFormsChange ();

            
            for (var i in that.codemirror) {
                that.codemirror[i].setValue(that.codemirror[i].getTextArea().value);
            }
            
        });

        // set up iframe resizing behavior
        $('#iframe_example').data('src',that.iframeSrc);
        $('#iframe_example').resizable({
            start: function(event, ui) {
                
            },
            stop: function(event, ui) {
                that.updateParams();
                //$(this).removeAttr('style');
            },
            helper: 'ui-resizable-helper',
            resize: function(event, ui) {
            //    $('#iframe_example').width(ui.size.width);
            //    $('#iframe_example').height(ui.size.height);
            //    $('#iframe_example iframe').attr('width', ui.size.width);
            //    $('#iframe_example iframe').attr('height', ui.size.height);
            },
        });
        
        $('#iframe_unsub').resizable({
            start: function(event, ui) {
                
            },
            stop: function(event, ui) {
                that.updateParams();
                //$(this).removeAttr('style');
            },
            helper: 'ui-resizable-helper',
            resize: function(event, ui) {
            //    $('#iframe_example').width(ui.size.width);
            //    $('#iframe_example').height(ui.size.height);
            //    $('#iframe_example iframe').attr('width', ui.size.width);
            //    $('#iframe_example iframe').attr('height', ui.size.height);
            },
        });

        // set up reset form button behavior
        $('#reset-form').on('click', function(evt) {
            evt.stopPropagation ();
            $("#saved-forms").val("0").change();
            return false;
        });

        that._afterInit ();

        that.updateParams();


        that._showHideDeleteButton ();

        that._setUpFormButtons ();
        that._setUpFormDeletion ();

        // Get the default form 
        this.defaultJSON = auxlib.formToJSON ($('#web-form-designer-form'));

        this.formName = '';

        
        this.codemirror = []
        // Syntax Highlighting
        $('.code').each(function(){
            var cm = CodeMirror.fromTextArea($(this)[0], {
                mode: $(this).data('mode'),
                showCursorWhenSelecting: true
            });

            that.codemirror.push(cm);
        });
        

        that._setUpTabs();

    };

    // Finds all the tab containers in the view files, and creates 
    // tabs out of them. 
    WebFormDesigner.prototype._setUpTabs = function () {
        var that = this;
        var tabs = $('#webform-tabs');
        var ul = tabs.find('ul').first();

        $('.webform-tab').each(function(){
            var title = $(this).data('title');
            var id = '#' + $(this).attr('id');
            var li = $('<li></li>').appendTo(ul);

            $('<a></a>').
            attr('href', id).
            html(title).
            appendTo(li);

        });

        // Tabs can be appended to accross view files
        $('.webform-tab-content').each(function(){
            var id = $(this).data('tab');
            $(this).appendTo('#'+id+' .tab-content');
        });

        
        // Set up a refresh for advanced tab
        // The timeout seems necessary, so its fully loaded
        // before refreshing
        $('[href=#advanced-tab]').click(function(){
            setTimeout(function(){
                for (var i in that.codemirror) {
                    that.codemirror[i].refresh();
                }
            }, 50);
        });
        

        tabs.tabs();
    }

    WebFormDesigner.prototype._showHideDeleteButton = function () {
        if ($('#saved-forms').val () === '0') {
            $('#delete-form').addClass ('disabled');
        } else {
            $('#delete-form').removeClass ('disabled');
        }
    };

    WebFormDesigner.prototype._setUpFormButtons = function () {
        var buttons = $('#webform-buttons');
        var that = this;

        buttons.find('#save-as').click(function(){
            $('#web-form-name').val(that.formName);
            $('#web-form #save-field').slideToggle();

            if($('#web-form #new-field').is(':visible')) {
                $('#web-form #new-field').slideToggle();
            }
        });

        buttons.find('#new-form').click(function(){
            $('#web-form-new-name').val('');
            $('#web-form #new-field').slideToggle();

            if($('#web-form #save-field').is(':visible')) {
                $('#web-form #save-field').slideToggle();
            }
        });

        $('#clipboard').click(function(){
            $('#embedcode').focus();
            $('#copy-help').show();
            setTimeout(function(){
                $('#copy-help').hide();
            }, 2000);
        });
        
        $('#unsubclipboard').click(function(){
            $('#unsubembedcode').focus();
            $('#unsub-copy-help').show();
            setTimeout(function(){
                $('#unsub-copy-help').hide();
            }, 2000);
        });
    }

    /**
     * Sets up behavior of 'Delete Form' button
     */
    WebFormDesigner.prototype._setUpFormDeletion = function () {
        var that = this; 
        $('#delete-form').on ('click', function (evt) {
            auxlib.confirm (function () {
                var formId = $('#saved-forms').val ();
                auxlib.destroyErrorFeedbackBox ($('#saved-forms'));
                $.ajax ({
                    url: that.deleteFormUrl,
                    type: 'GET',
                    data: {
                        id: formId, 
                    },
                    dataType: 'json',
                    success: function (data) {
                        if (data[0]) {
                            $('#saved-forms').find ('[value="' + formId + '"]').remove();
                            $('#saved-forms').change();
                            x2.topFlashes.displayFlash(data[1], 'success');
                        } else {
                            x2.topFlashes.displayFlash(data[1], 'error');
                        }

                    }
                });
            });
        });
    };

    // override in child prototype
    WebFormDesigner.prototype._afterSavedFormsChange = function () {};

    // override in child prototype
    WebFormDesigner.prototype._afterInit = function () {};


    /*
    Generates a new iframe with the user-set dimensions and with GET parameters corresponding
    to the current form input.
    */
    WebFormDesigner.prototype.updateParams = function (iframeContainer) {
        var that = this;

        if ($(iframeContainer).data ('ignoreChange')) {
            return;
        }
        var params = [];
        if (that.listId !== null) {
            params.push('lid='+that.listId);
        }
        
        $.each(that.fields, function(i, field) {
            var value = WebFormDesigner.sanitizeInput($('#'+field).val());
            if (value.length > 0) { params.push(field+'='+value); }
        });

        /* send iframe height to iframe contents view so that iframe contents can be set to correct
        height on iframe load */
        var iframeHeight = $('#iframe_example').height ();
        params.push ('iframeHeight=' + (Math.floor (iframeHeight)));

        var query = this._generateQuery(params);

        var iframeWidth;
        if ($('#iframe_example').find ('iframe').length) {
            iframeWidth = $('#iframe_example').width ();
        } else {
            iframeWidth = 200;
        }
        
        /* 
        */
        var embedCode = '<iframe name="web-form-iframe" src="' + that.iframeSrc + query +
            '" frameborder="0" allowtransparency="true" scrolling="0" width="' + iframeWidth +  '" height="' + 
            iframeHeight + '"></iframe>';
    
        if ($('#saved-forms').val() != 0) {
            $('#embedcode').val(embedCode);
        } else {
            $('#embedcode').val('');
        }

        $('#iframe_example').children ('iframe').remove ();
        $('#iframe_example').append (embedCode);
        
        
        /*
         * Unsub Embed 
         */
        
        var unsubHeight = $('#iframe_unsub').height ();
        var unsubWidth;
        if ($('#iframe_unsub').find ('iframe').length) {
            unsubWidth = $('#iframe_unsub').width ();
        } else {
            unsubWidth = 200;
        }
        
        var unsubEmbed = '<iframe name="web-form-iframe" src="' + that.baseUrl +
                '/index.php/marketing/marketing/unsubWebleadForm' +
            '" frameborder="0" allowtransparency="true" scrolling="0" width="' +
            unsubWidth +  '" height="' + unsubHeight + '"></iframe>';
        
        $('#unsubembedcode').val(unsubEmbed);

        $('#iframe_unsub').children ('iframe').remove ();
        $('#iframe_unsub').append (unsubEmbed);
        
    };

    /*
    Generates a GET parameter string from the given paramaters array
    */
    WebFormDesigner.prototype._generateQuery = function (params) {
        var query = '';
        var first = true;

        for (var i = 0; i < params.length; i++) {
            if (params[i].search(/^[^=]+=[^=]+$/) != -1) {
                if (first) {
                    query += '?'; first = false;
                } else {
                    query += '&';
                }

                query += params[i];
            }
        }

         
        // add web form id to GET params so that fields can be retrieved
        query += '&webFormId=' + encodeURIComponent($('#saved-forms').val());
        

        query = this._appendToQuery (query);

        return query;
    };

    
    /*
    Returns a dictionary containing custom fields form input values
    */
    WebFormDesigner.prototype._getFieldList = function (form) {

        var fieldList = [];
        $('#sortable2').find('li').each(function() {
            var f = new Object;
            f['fieldName'] = $(this).attr('name');
            f['required'] = $(this).find('input[type="checkbox"]').is(':checked');
            f['label'] = $(this).find('input[type="text"]').val();
            f['position'] = $(this).find('select.field-position').val();
            f['type'] = $(this).find('select.field-type').val();
            fieldList.push(f);
        });
        return fieldList;
    };
    

    /**
     * Use to refresh form data before submission
     */
    WebFormDesigner.prototype._refreshForm = function () {
         
        var that = this;

        var fieldList = this._getFieldList ();

        for(var i in that.codemirror) {
            that.codemirror[i].save();
        }

        // set POST data for saving weblead form
        $('#fieldList').val(encodeURIComponent(JSON.stringify(fieldList))); 
         
    };

    // override in child prototype
    WebFormDesigner.prototype._appendToQuery = function (query) {
        return query;
    };

    /*
    Clear form inputs.
    */
    WebFormDesigner.prototype._clearFields = function () {
        var that = this;
        $('#web-form-name').val('');
        $.each(that.fields, function(i, field) {
            $('#'+field).val('');
        });
    };

    /*
    Populate form with form settings
    */
    WebFormDesigner.prototype._updateFields = function (form) {
        var that = this;

        that.DEBUG && console.log ('_updateFields');
        that.DEBUG && console.log (form.params);
        $('#web-form-name').val(form.name);
        that.formName = form.name;

        if (form.params) {
            $.each(form.params, function(key, value) {
                if ($.inArray(key, that.fields) != -1) {
                    $('#'+key).val(value);
                }
                if ($.inArray(key, that.colorfields) != -1) {
                    $('#'+key).spectrum ("set", $('#'+key).val ());
                }
            });
        }

        if (typeof form.requireCaptcha !== 'undefined') {
            if (parseInt (form.requireCaptcha, 10) === 1) {
                $('#require-captcha-checkbox').prop ('checked', true);
            } else {
                $('#require-captcha-checkbox').prop ('checked', false);
            }
            $('#generate-lead-checkbox').change ();
        }
        if (typeof form.thankYouText !== 'undefined' && form.thankYouText != '') {
            $('#thankYouText').val (form.thankYouText);
        } else {
            $('#thankYouText').val ('');
        }
        if (typeof form.fingerprintDetection !== 'undefined') {
            if (parseInt (form.fingerprintDetection, 10) === 1) {
                $('#fingerprint-detection-checkbox').prop ('checked', true);
            } else {
                $('#fingerprint-detection-checkbox').prop ('checked', false);
            }
        }

        this._updateExtraFields (form);
        this._updateCustomFields (form);
    };

    // override in child prototype
    WebFormDesigner.prototype._updateExtraFields = function (form) {
        return;
    };

    
    WebFormDesigner._enableTabsForCustomCss = function () {

        // enable tabs for CSS textarea
        $(document).delegate('#custom-css, #custom-html', 'keydown', function(e) {
          var keyCode = e.keyCode || e.which;

          if (keyCode == 9) {
            e.preventDefault();
            var start = $(this).get(0).selectionStart;
            var end = $(this).get(0).selectionEnd;

            // set textarea value to: text before caret + tab + text after caret
            $(this).val($(this).val().substring(0, start)
                        + "\t"
                        + $(this).val().substring(end));

            // put caret at right position again
            //$(this).get(0).selectionStart =
            $(this).get(0).selectionEnd = start + 1;
          }
        });
    };
    

    
    WebFormDesigner.prototype._onFieldUpdate = function () {
        var that = this;
        var fieldList = that._getFieldList ();
        $('#fieldList').val(encodeURIComponent(JSON.stringify(fieldList))); 
        $('#web-form-save-button').addClass ('highlight');
    };
    

    
    /*
    Make custom fields containes sortable, set up their behavior
    */
    WebFormDesigner.prototype._setUpSortableCustomFieldsBehavior = function () {
        var that = this;
        $( "#sortable1" ).sortable({
            placeholder: "ui-state-highlight",
            connectWith: ".connectedSortable",
            receive: function(event, ui) {
                // ui.item.find('.field-settings').toggleClass('closed', false);
                that._onFieldUpdate ();
            },
            update: function(event, ui) {
                that._onFieldUpdate ();
            }
        });
        $( "#sortable2" ).sortable({
            placeholder: "ui-state-highlight",
            connectWith: ".connectedSortable",
            receive: function(event, ui) {
                // ui.item.find('.field-settings').toggleClass('closed', true);
                that._onFieldUpdate ();
            },
            update: function(event, ui) {
                that._onFieldUpdate ();
            }
        });

        $('#sortable2, #sortable1').find('li > label').add('.field-expander').click(function(){
            $(this).closest('li').find('.field-settings').slideToggle();
            $(this).closest('li').find('.field-expander').toggleClass('closed');
        });
    };
    

    // override in child prototype
    WebFormDesigner.prototype._updateCustomFields = function (form) {
         
        if(typeof form.fields != 'undefined' && form.fields != null) {
            try {
                var savedFieldList = JSON.parse(decodeURIComponent(form.fields));
            } catch (e) {
                return;
            }
            var fieldList = $('.connectedSortable li');

            // clear form fields
            $('#sortable2 li').each(function() {
                $(this).prependTo('#sortable1');
                $(this).find('div').css('display', 'none');
            });

            // load form fields from saved form
            var savedField;
            for(var i=0; i<savedFieldList.length; i++) {
                savedField = savedFieldList[i];
                if (savedField.type === 'tags') { // tag field uses a separate input
                    $('#tags').val (savedField.label);
                    continue;
                }
                var f = $('#sortable1 li[name="' + savedField.fieldName + '"]');
                f.appendTo('#sortable2');
                f.find('.field-settings').css('display', 'none');
                f.find('.field-expander').toggleClass('closed',true);
                f.find('input[type="checkbox"]').prop('checked', savedField.required);
                f.find('input[type="text"]').val(savedField.label);
                f.find('.field-position').val(savedField.position);
                f.find('.field-type').val(savedField.type);

                // Update the label to either 'Value:' or 'Label'
                if (savedField.type == 'hidden') {
                    f.find('.field-value-label').html(this.translations['Value:']);
                } else {
                    f.find('.field-value-label').html(this.translations['Label:']);
                }
            }
        }
         
    };

    // override in child prototype
    WebFormDesigner.prototype._beforeSaved = function () {};

    /*
    Called on ajax success. Form saved successfully. Alert user and cache the form.
    */
    WebFormDesigner.prototype.saved = function (data, status, xhr) {
        var that = this;

        this._beforeSaved ();
        var newForm = $.parseJSON(data);
        if (typeof newForm.errors !== "undefined") { return; }
        this._cacheSavedForm (newForm);
        that.updateParams();
        $('#web-form-save-button').removeClass ('highlight');
        x2.topFlashes.displayFlash(that.translations.formSavedMsg, 'success');
        that._showHideDeleteButton ();

        if ($('#save-field').is(':visible')) {
            $('#save-field').slideToggle();
        } 
        if (!$('#web-form-inner').is(':visible')) {
            $('#web-form-inner').show();
        }
    }

    /*
    Cache saved forms on client for fast access on form switch
    */
    WebFormDesigner.prototype._cacheSavedForm = function (newForm) {
        var that = this;

        newForm.params = $.parseJSON(newForm.params);
        var index = -1;
        $.each(that.savedForms, function(i, el) {
            if (newForm.id == el.id) {
                index = i;
            }
        });
        if (index != -1) {
            that.savedForms.splice(index, 1, newForm);
        } else {
            that.savedForms.push(newForm);
            $('#saved-forms').append('<option value="'+newForm.id+'">'+newForm.name+'</option>');
        }
        $('#saved-forms').val(newForm.id);
    }

    return WebFormDesigner;
})();
