/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/
if(typeof x2 == 'undefined')
    x2 = {};

x2.inlineEmailEditor = {};
x2.inlineEmailEditor.isSetUp = false;

$(function() {


    function setupInlineEmailEditorAndroid () {
        setupEmailAttachments('email-attachments');
    }

    /**
     *    Initializes CKEditor in the email form, and the datetimepicker for the "send later" dropdown.
     */
    $(document).on('setupInlineEmailEditor',function(){

        if(window.inlineEmailEditor)
            window.inlineEmailEditor.destroy(true);
        $('#email-message').val(x2.inlineEmailOriginalBody);
        window.inlineEmailEditor = createCKEditor('email-message',{fullPage:true,height:'300px',tabIndex:5,insertableAttributes:x2.insertableAttributes}, function() {
            if(typeof inlineEmailEditorCallback == 'function') {
                inlineEmailEditorCallback(); // call a callback function after the inline email editor is created (if function exists)
            }
            x2.inlineEmailEditor.isSetUp = true;
        });
        
        setupEmailAttachments('email-attachments');
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
    else
        $(document).trigger('setupInlineEmailEditor');

    $('body').on('click','a',function(e) {
        if(/^mailto:/.exec(this.href)) {
            if(typeof toggleEmailForm != 'undefined') {
                e.preventDefault();
                $('#email-to').val(decodeURIComponent(this.href).replace('mailto:',''));
                toggleEmailForm();
            }
        }
    });

    
    setupInlineEmailForm();
});


/**
 * Toggles the inline email form open or closed. Scrolls to the email form and animates 
 * the form sliding open. Alternatively, slides the form closed.
 *
 * The optional "mode" parameter is used when opening the inline email form with
 * a different model, i.e. a quote.
 */
function toggleEmailForm(mode) {
    if (!x2.inlineEmailEditor.isSetUp) return;
    mode = (typeof mode == 'undefined') ? 'default' : mode;
    if(typeof x2.inlineQuotes != 'undefined') {
        if(typeof x2.inlineQuotes.inlineEmailConfig == 'undefined')
            x2.inlineQuotes.setInlineEmailConfig();
        if(x2.inlineQuotes.inlineEmailMode != mode)
            x2.inlineQuotes.resetInlineEmail();
    }
    
    if($('#inline-email-form .wide.form').hasClass('hidden')) {
        $('#inline-email-form .wide.form').removeClass('hidden');
        $('#inline-email-form .form.email-status').remove();
        return;
    }
    
    if($('#inline-email-form').is(':hidden')) {
        $('#inline-email-status').hide(); // Opening new form; hide previous submission's status
        $(document).trigger('setupInlineEmailEditor');
        $('.focus-mini-module').removeClass('focus-mini-module');
        $('#inline-email-form').find('.wide.form').addClass('focus-mini-module');
        $('html,body').animate({
            scrollTop: ($('#inline-email-top').offset().top - 100)
        }, 300);
    }
    
    $('#inline-email-form').animate({
        opacity: 'toggle',
        height: 'toggle'
    }, 300); // ,function() {  $('#inline-email-form #InlineEmail_subject').focus(); }
    
    $('#InlineEmail_subject')
        .addClass('focus')
        .focus()
        .blur(function() {$(this).removeClass('focus');});
}


/**
 * Set up attachments in the email form so that the attachments div is droppable for
 * files dragged over from the media widget. This is called when the page loads (if the
 * page has an inline email form) and whenever the email form is replaced, like after an
 * ajax call from pressing the preview button.
 */
function setupInlineEmailForm() {
    
    $(document).trigger('setupInlineEmailEditor');
    
    // setupEmailAttachments();
    
    initX2FileInput();
    
    $(document).mouseup(function() {
        $('input.x2-file-input[type=file]').next().removeClass('active');
    });

    // init cc and bcc buttons
    $('#cc-toggle').click(function() {
        $(this).animate({
                opacity: 'toggle',
                width: 0
            }, 400);
        
        $('#cc-row').slideDown(300);
    });
    
    $('#bcc-toggle').click(function() {
        $(this).animate({
                opacity: 'toggle',
                width: 0
            }, 400);
        
        $('#bcc-row').slideDown(300);
    });
    
    $('#email-template').change(function() {
        var template = $(this).val();
        if(template != "0") {
            if(inlineEmailSwitchConfirm()) {
                if (!x2.isAndroid) window.inlineEmailEditor.updateElement();
                jQuery.ajax({
                    'type':'POST',
                    'url':yii.scriptUrl+'/contacts/inlineEmail?ajax=1&template=1',
                    'data':jQuery(this).parents("form").serialize(),
                    'beforeSend':function() {
                        $('#email-sending-icon').show();
                    }
                }).done(function(data, textStatus, jqXHR) {
                    handleInlineEmailActionResponse(data, textStatus, jqXHR);
                });
            }
        }
    });
}

function inlineEmailSwitchConfirm() {
    if (x2.isAndroid) return true;
    var proceed = true;
    var noChange = ! window.inlineEmailEditor.checkDirty();
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

function handleInlineEmailActionResponse(data, textStatus, jqXHR) {    
    $('#email-sending-icon').hide();
    if(data.error) {
        if(data.modelHasErrors) {
            // Error-highlight the fields that have errors:
            for (var attr in data.modelErrors) {
                if(attr != 'message') { // Skip the message area; it will turn the background pink, and that would be icky.
                    $('input[name="InlineEmail['+attr+']"]').addClass('error');
                }
            }
        } else {
            $('#inline-email-errors').addClass('errorSummary');
        }
        $('#inline-email-errors').html(data.modelHasErrors ? data.modelErrorHtml : data.message).show();
        return false;
    }
    if(data !== undefined) {
        if(data.scenario == 'template') { // Submission was for getting new template. Fill in with template content.
            if (x2.isAndroid)
                $('#email-message').val (data.attributes.message);    
            else 
                window.inlineEmailEditor.setData(data.attributes.message);
            $('input[name="InlineEmail[subject]"]').val(data.attributes.subject);
        } else { // Email was sent successfully. Reset everything.
            
            $('.error').removeClass('error');
            $('#inline-email-status').show().html(data.message);
            $('#inline-email-errors').html('').hide();
            $('#email-template').val(0);            
            $('input[name="InlineEmail[subject]"]').val('');
            toggleEmailForm();
            x2.Notifs.updateHistory();
            
        }
    }
    return false;
}
