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




if(typeof x2 == 'undefined')
    x2 = {};

x2.InlineEmailEditorManager = (function () {

/**
 * Manages interaction with inline email widget. Eventually, the functions in this file
 * which are outside this class should be moved into this class.
 */
function InlineEmailEditorManager (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        translations: {},
        saveDefaultTemplateUrl: '', // points to action which saves default email template
        tmpUploadUrl: '',
        rmTmpUploadUrl: '',
        reinstantiateEditorWhenShown: true,
        disableTemplates: false,
        enableResizability: true,
        type: ''
        //originalContentHeight: 300,
        //originalContentWidth: 713
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._settingsDialog = null;
    this._callbacks = []; // array of functions which get executed after CKEditor is shown
    this.element = $('#inline-email-form');

    this._init ();
}

InlineEmailEditorManager.prototype.addCallback = function (fn) {
    this._callbacks.push (fn);
};

InlineEmailEditorManager.prototype.executeCallbacks = function () {
    for (var i in this._callbacks) {
        this._callbacks[i].call (this);
    }
};

InlineEmailEditorManager.prototype.handleInlineEmailActionResponse = function (data) {
    $('#email-sending-icon').hide();

    if(data.error) {
        if(data.modelHasErrors) {
            // Error-highlight the fields that have errors:
            for (var attr in data.modelErrors) {
                // Skip the message area; it will turn the background pink, and that would be icky.
                if(attr != 'message') { 
                    $('input[name="InlineEmail['+attr+']"]').addClass('error');
                }
            }
        } 
        $('#inline-email-errors').addClass('errorSummary');
        $('#inline-email-errors').html(data.modelHasErrors ? data.modelErrorHtml : data.message).
            show();
        return false;
    }
    if(data !== undefined) {
        // Submission was for getting new template. Fill in with template content.
        if(data.scenario == 'template') { 
            if (x2.isAndroid)
                $('#email-message').val (data.attributes.message);    
            else 
                window.inlineEmailEditor.setData(data.attributes.message);

            if (typeof data.attributes.to !== 'undefined' && data.attributes.to !== '')
                $('input[name="InlineEmail[to]"]').val(data.attributes.to);
            $('input[name="InlineEmail[subject]"]').val(data.attributes.subject);
        } else { // Email was sent successfully. Reset everything.
            this.afterSend (data);
        }
    }
    return false;
};

InlineEmailEditorManager.prototype.clearForm = function () {
    $('.error').removeClass('error');
    $('#inline-email-errors').html('').hide();
    $('#email-template').val(0);
    $('input[name="InlineEmail[subject]"]').val('');
    if (this.type === 'testCampaignEmail') {
        $('#InlineEmail_modelId').val ('');
        $('#InlineEmail_recordName').val ('');
    }
    this.element.find ('.upload-file-container').remove ();
};

InlineEmailEditorManager.prototype.afterSend = function (data) {
    x2.topFlashes.displayFlash (data.message, 'success');
    this.clearForm ();
    toggleEmailForm();
    x2.actionHistory.update ();
    x2.TransactionalViewWidget.refresh ('EmailsWidget');
};

InlineEmailEditorManager.prototype.prependToBody = function (text) {
    inlineEmailEditor.updateElement ();
    var tempContainer$ = $('<div>');
    var message = $.makeArray ($($('#email-message').text ()));
    message = $.makeArray ($(text)).concat (message);
    tempContainer$.append (message);
    inlineEmailEditor.setData (tempContainer$.html ());
    return this;
};

InlineEmailEditorManager.prototype.focus = function () {
    inlineEmailEditor.focus ();
    return this;
};

InlineEmailEditorManager.prototype.setToField = function (val) {
    $('#email-to').val (val);

    return this;
};

InlineEmailEditorManager.prototype.setSubjectField = function (val) {
    $('#InlineEmail_subject').val (val);
    if (val)
        this.element.find ('.email-title-bar > .widget-title').text (val);
    else
        this.element.find ('.email-title-bar > .widget-title').text (
            this.translations['New Message']);

    return this;
};

InlineEmailEditorManager.prototype.hideShowSubjectField = function (hide) {
    if (hide) {
        $('#InlineEmail_subject').parent ().hide ();
    } else {
        $('#InlineEmail_subject').parent ().show ();
    }

    return this;
};

/**
 * Toggles the inline email form open or closed. Scrolls to the email form and animates 
 * the form sliding open. Alternatively, slides the form closed.
 *
 * @param string mode (optional) used when opening the inline email form with
 * a different model, i.e. a quote.
 */
InlineEmailEditorManager.prototype.toggleEmailForm = function (mode) {
    mode = (typeof mode == 'undefined') ? 'default' : mode;
    if(typeof x2.inlineQuotes != 'undefined') {
        if(typeof x2.inlineQuotes.inlineEmailConfig == 'undefined')
            x2.inlineQuotes.setInlineEmailConfig();
        if(x2.inlineQuotes.inlineEmailMode != mode)
            x2.inlineQuotes.resetInlineEmail();
    }

    if($('#inline-email-form .wide.form').hasClass('hidden') ||
       $('#inline-email-form').is(':hidden')) {
        this.showEmailForm ();
    } else {
        this.hideEmailForm ();
    }

    return this;
};

InlineEmailEditorManager.prototype.hideEmailForm = function (animate) {
    animate = typeof animate === 'undefined' ? false : animate; 

    if (animate)
        $('#inline-email-form').animate({
            opacity: 'hide',
            height: 'hide'
        }, 300); 
    else
        $('#inline-email-form').hide ();

    return this;
};

InlineEmailEditorManager.prototype.showEmailForm = function (animate, scroll, focusOnSubject) {
    animate = typeof animate === 'undefined' ? false : animate; 
    scroll = typeof scroll === 'undefined' ? false : scroll; 
    focusOnSubject = typeof focusOnSubject === 'undefined' ? true : focusOnSubject; 

    var that = this;
    this.element.find ('.cke_contents').attr ('style', '');
    that.element.find ('.cke_contents').height (300);
    this.element.find ('.email-reattach-button').css ('visibility', 'hidden');
    var wasHidden = $('#inline-email-form').is(':hidden');
    this.element.addClass ('fixed-email-form').attr ('style', '');

    if($('#inline-email-form .wide.form').hasClass('hidden')) {
        $('#inline-email-form .wide.form').removeClass('hidden');
        $('#inline-email-form .form.email-status').remove();
        return;
    }

    if(wasHidden) {
        $('#inline-email-status').hide(); // Opening new form; hide previous submission's status
        $(document).trigger('setupInlineEmailEditor');
        $('.focus-mini-module').removeClass('focus-mini-module');
        $('#inline-email-form').find('.wide.form').addClass('focus-mini-module');
        if (scroll) {
            $('html,body').animate({
                scrollTop: ($('#inline-email-top').offset().top - 100)
            }, 300);
        } 
    }

    if (animate)
        $('#inline-email-form').animate({
            opacity: 'show',
            height: 'show'
        }, 300); 
    else
        $('#inline-email-form').show ();

    if (!this.reinstantiateEditorWhenShown)
        CKEDITOR.instances['email-message'].resize ('100%'); // prevent iframe from displaying blank

    if (focusOnSubject) {
        $('#InlineEmail_subject')
            .addClass('focus')
            .focus();
            /*.blur(function() {
                $(this).removeClass('focus');
            });*/
    }

    return this;
};

/**
 * Sets up the behavior of the settings menu 
 */
InlineEmailEditorManager.prototype._setUpEmailSettingsMenuBehavior = function () {
    var that = this;

    // open the save default template dialog
    $('#email-settings-menu').children ().first ().unbind ('click.setUpEmailSettingsMenuBehavior').
        bind ('click.setUpEmailSettingsMenuBehavior', function () {
            that._settingsDialog.dialog ('open');
        });

    $('#email-mini-module').mouseenter (function () {
        $('#email-settings-button').show ();
        that.element.find ('.email-fullscreen-button').show ();
        that.element.find ('.email-reattach-button').show ();
    });

    $('#email-mini-module').mouseleave (function () {
        $('#email-settings-button').hide ();
        that.element.find ('.email-fullscreen-button').hide ();
        that.element.find ('.email-reattach-button').hide ();
    });
};

/**
 * Saves the default template setting 
 */
InlineEmailEditorManager.prototype._saveDefaultTemplate = function (data) {
    var that = this;
    $.ajax ({
        url: this.saveDefaultTemplateUrl,
        type: 'GET',
        dataType: 'json',
        data: data,
        success:function (data) {
            if (data.success) {
                that._settingsDialog.dialog ('close');
            } else {
                $('#email-settings-menu').find ('form').
                    append (x2.forms.errorMessage (data.message));
            }
        }
    });
};

/**
 * Sets up the default template settings dialog
 */
InlineEmailEditorManager.prototype._setUpSettingsDialog = function () {
    var that = this;
    this._settingsDialog = $('#email-settings-dialog').dialog ({
        title: this.translations['defaultTemplateDialogTitle'],
        width: 500,
        autoOpen: false,
        buttons: [
            {
                text: that.translations['Cancel'],
                click: function () {
                    $(this).dialog ('close');
                }
            },
            {
                text: that.translations['Save'],
                click: function () {
                    that._saveDefaultTemplate (
                        auxlib.formToJSON ($('#email-settings-dialog').find ('form')));
                }
            },
        ],
        close: function () {
            x2.forms.clearForm ($('#email-settings-menu').find ('form'));
        }

    });
};

/**
 * Set up iframe-based attachment file upload 
 */
InlineEmailEditorManager.prototype._setUpFileUpload = function () {
    var that = this;
    $(document).on ('change', '.x2-file-input', function (event) {
        x2.attachments.checkName(event); 
        if($('#submitAttach').attr('disabled') !== 'disabled') {
            x2.forms.fileUpload ($(this), that.tmpUploadUrl, that.rmTmpUploadUrl); 
        }
    });
};

InlineEmailEditorManager.prototype._setUpSubjectFieldBehavior = function () {
    var that = this;
    var subjectField$ = $('#InlineEmail_subject');
    var title$ = this.element.find ('.email-title-bar > .widget-title');
    subjectField$.blur (function () {
        var val = $(this).val (); 
        if (val) {
            title$.text ($(this).val ());
        } else {
            title$.text (that.translations['New Message']);
        }
    });
};

InlineEmailEditorManager.prototype._setUpAddresseeRowsBehavior = function () {
    var addresseeRows$ = this.element.find ('.addressee-rows');
    var ccRow$ = $('#cc-row');
    var ccToggle$ = $('#cc-toggle');
    var bccRow$ = $('#bcc-row');
    var bccToggle$ = $('#bcc-toggle');
    auxlib.onClickOutside (addresseeRows$, function () {
        if (!ccRow$.find ('input').val ()) {
            ccRow$.hide ();
            ccToggle$.show ();
        }
        if (!bccRow$.find ('input').val ()) {
            bccRow$.hide ();
            bccToggle$.show ();
        }
    });
};


/**
 * Set up attachments in the email form so that the attachments div is droppable for
 * files dragged over from the media widget. This is called when the page loads (if the
 * page has an inline email form) and whenever the email form is replaced, like after an
 * ajax call from pressing the preview button.
 */
InlineEmailEditorManager.prototype._setUpInlineEmailForm = function () {
    var that = this;
    $(document).trigger('setupInlineEmailEditor');
    
    // x2.emailEditor.setupEmailAttachments();
    
    x2.forms.initX2FileInput();
    
    $(document).mouseup(function() {
        $('input.x2-file-input[type=file]').next().removeClass('active');
    });

    // init cc and bcc buttons
    $('#cc-toggle').click(function() {
        $(this).hide ();
        $('#cc-row').show ();
        $('#cc-row > input').focus ();
    });
    
    $('#bcc-toggle').click(function() {
        $(this).hide ();
        $('#bcc-row').show ();
        $('#bcc-row > input').focus ();
    });
    
    $('#email-template').change(function() {
        var template = $(this).val();
        if(template != "0") {
            if(inlineEmailSwitchConfirm()) {
                if (!x2.isAndroid) window.inlineEmailEditor.updateElement();

                x2.forms.clearDefaultValues (that.element.find ('form'));
                $.ajax({
                    'type': 'POST',
                    'url': yii.scriptUrl+'/contacts/inlineEmail?ajax=1&template=1',
                    'data': $(this).parents("form").serialize(),
                    'beforeSend': function() {
                        $('#email-sending-icon').show();
                    }
                }).done(function(data, textStatus, jqXHR) {
                    x2.inlineEmailEditorManager.handleInlineEmailActionResponse(data);
                });
                x2.forms.restoreDefaultValues (that.element.find ('form'));
            }
        }
    });
};

InlineEmailEditorManager.prototype._setUpCloseFunctionality = function () {
    var that = this;
    this.element.find ('.cancel-send-button').click (function () {
        that.toggleEmailForm ();
        that.clearForm ()
    });
};

InlineEmailEditorManager.prototype._setUpDraggability = function () {
    var that = this;
    this.element.draggable ({
        handle: this.element.find ('.widget-title-bar'),
        start: function () { 
            that.element.removeClass ('fixed-email-form');
            that.element.find ('.email-reattach-button').css ('visibility', 'visible');
        }
    });
};

InlineEmailEditorManager.prototype._setUpResizeBehavior = function () {
    var that = this;
    var prevWidth, prevHeight, prevDx = 0, prevDy = 0, dx, dy;
    this.element.inlineEmailResizable ({
        handles: 'n, s, e, w, se',
        resize: function (evt, ui) {
            dy = ui.size.height - ui.originalSize.height;
            dx = ui.size.width - ui.originalSize.width;
            that.element.css ('height', 'auto');
            that.element.css ('width', 'auto');
            if (prevDy !== dy && prevHeight !== ui.size.height) {
                that.element.find ('.cke_contents').height (
                    that.originalContentHeight + dy);
            }
            if (prevDx !== dx && prevWidth !== ui.size.width) { 
                that.element.find ('.cke_contents').width (
                    that.originalContentWidth + dx);
                that.element.width (that.originalContentWidth + dx);
            }
            prevWidth = ui.size.width;
            prevHeight = ui.size.height;
        },
        stop: function () {
            prevHeight = that.originalContentHeight = that.element.find ('.cke_contents').height ();
            prevWidth = that.originalContentWidth = that.element.width ();
            dx = dy = 0;
        }
    });
    this.addCallback (function () {
        prevHeight = that.originalContentHeight = that.element.find ('.cke_contents').height ();
        prevWidth = that.originalContentWidth = that.element.width ();
    });
};

InlineEmailEditorManager.prototype._setUpButtonBehavior = function () {
    var that = this;
    this.element.find ('.email-reattach-button').click (function (evt) {
        that.element.addClass ('fixed-email-form')
        that.element.css ('left', '');
        that.element.css ('right', '');
        that.element.css ('top', '');
        that.element.css ('bottom', '');
        that.element.css ('height', '');
        that.element.find ('.email-reattach-button').css ('visibility', 'hidden');
    });
};

/**
 * kludge to get ckeditor dropdown menus to be properly positioned
 */
InlineEmailEditorManager.prototype._ckeFixes = function () {
    var that = this;
    this.addCallback (function () {

        // event triggered after dropdown is opened
        window.inlineEmailEditor.on ('panelShow', function () {
            $('.cke_combopanel').css ({
                'position': 'fixed',
                'display': 'block'
            });
            $('.cke_combopanel').position ({
                my: 'left top-1',
                at: 'left bottom',
                of: $('.cke_combo_on > a')
            });
        });
    });
};

InlineEmailEditorManager.prototype._init = function () {
    var that = this;
    $(function () {
        if (!that.disableTemplates) {
            that._setUpSettingsDialog ();
            that._setUpEmailSettingsMenuBehavior ();
        }
        that._setUpSubjectFieldBehavior ();
        that._setUpAddresseeRowsBehavior ();
        that._setUpFileUpload ();
        that._setUpCloseFunctionality ();
        if (that.enableResizability)
            that._setUpResizeBehavior ();
        that._setUpDraggability ();
        that._setUpButtonBehavior ();
        that._ckeFixes ();
        that._initializeEmailEditor ();
    });

};

InlineEmailEditorManager.prototype.reinstantiate = function () {
    var that = this;
    if(window.inlineEmailEditor)
        window.inlineEmailEditor.destroy(true);

    $('#email-message').val(x2.inlineEmailOriginalBody);
    window.inlineEmailEditor = createCKEditor(
        'email-message',
        {
            toolbarStartupExpanded: !$('body').hasClass ('x2-mobile-layout'),
            fullPage:true,
            height:'300px',
            tabIndex:5,
            insertableAttributes:x2.insertableAttributes
        }, function() {
            if(typeof inlineEmailEditorCallback == 'function') {
                /* call a callback function after the inline email editor is created (if 
                   function exists) */
                inlineEmailEditorCallback(); 
            }
            that.executeCallbacks ();
            x2.inlineEmailEditor.isSetUp = true;
        });
    
    x2.emailEditor.setupEmailAttachments('email-attachments');
};

InlineEmailEditorManager.prototype._initializeEmailEditor = function () {
    var that = this;
    function setupInlineEmailEditorAndroid () {
        x2.emailEditor.setupEmailAttachments('email-attachments');
    }

    /**
     * Initializes CKEditor in the email form, and the datetimepicker for the "send later" dropdown.
     */
    $(document).on('setupInlineEmailEditor',function(){
        if (!that.reinstantiateEditorWhenShown && 
            window.inlineEmailEditor) return;
        that.reinstantiate ();
    });

    $(document).delegate('#email-template','change',function() {
        if($(this).val() != '0')
            $('#email-subject').val($(this).find(':selected').text());
        $('#preview-email-button').click();
    });
    
    
    // give send-email module focus when clicked
    $('#inline-email-form').click(function() {
        if(!$('#inline-email-form').find('.wide.form').hasClass('focus-mini-module')) {
            $('.focus-mini-module').removeClass('focus-mini-module');
            $('#inline-email-form').find('.wide.form').addClass('focus-mini-module');
        }
    });
    
    // give send-email module focus when tinyedit clicked
    $('#email-message').click(function() {
        if(!$('#inline-email-form').find('.wide.form').hasClass('focus-mini-module')) {
            $('.focus-mini-module').removeClass('focus-mini-module');
            $('#inline-email-form').find('.wide.form').addClass('focus-mini-module');
        }
    });
    
    if(window.hideInlineEmail)
        $('#inline-email-form').hide();

    $('body').on('click','a',function(e) {
        if(/^mailto:/.exec(this.href)) {
            if(typeof toggleEmailForm != 'undefined') {
                e.preventDefault();
                $('#email-to').val(decodeURIComponent(this.href).replace('mailto:',''));
                toggleEmailForm();
            }
        }
    });

    this._setUpInlineEmailForm ();
};

return InlineEmailEditorManager;

}) ();


x2.inlineEmailEditor = {};
x2.inlineEmailEditor.isSetUp = false;

/**
 * Temporary (until refactor) wrapper around InlineEmailEditorManager method 
 */
function toggleEmailForm(mode) {
    if (!x2.inlineEmailEditor.isSetUp && !x2.isAndroid) return;

    x2.inlineEmailEditorManager.toggleEmailForm ();
}

function inlineEmailSwitchConfirm() {
    if (x2.isAndroid) return true;
    var proceed = true;
    var noChange = !window.inlineEmailEditor.checkDirty();
    if(!noChange)
        proceed = confirm($('#template-change-confirm').text());
    return proceed;
}

/**
 * Function called to denote that the email form is being submitted.
 */
function setInlineEmailFormLoading() {
    $('#email-sending-icon').show();
}

