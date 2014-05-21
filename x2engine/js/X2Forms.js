/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
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

if (typeof x2 === 'undefined') x2 = {};

x2.Forms = (function () {

function X2Forms (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        translations: {}
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._init ();
}

/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/

/**
 * Initializes all elements with the class 'x2-multiselect-dropdown' as multiselect dropdown 
 * elements. The value of the element's 'data-selected-text' attribute will be used as the
 * text in the multiselect element indicating the number of options selected.
 */
X2Forms.prototype.initializeMultiselectDropdowns = function () {
    var that = this;
    $('.x2-multiselect-dropdown').each (function () {

        // don't reinitialize
        if ($(this).next ().hasClass ('.ui-multiselect')) return;

        var selectedText = $(this).attr ('data-selected-text'); 
        $(this).multiselect2 ({ 
            selectedText: '# ' + selectedText + ' ' + that.translations['selected'],
            checkAllText: that.translations['Check All'], 
            uncheckAllText: that.translations['Uncheck All'], 
            'classes': 'x2-multiselect-dropdown-menu'
        });
    });
};

/**
 * Creates an error message element which can be appended to a form and cleared with clearForm ().
 * @return object jQuery element containing specified error message
 */
X2Forms.prototype.errorMessage = function (message) {
    return $('<span>', {
        'class': 'x2-forms-error-msg',
        text: message
    });
};

/**
 * Clears all inputs in form. Removes 'error' class from all inputs. Also clears all error message
 * elements created by errorMessage ().
 * @param Object element containing the form
 * @param Bool preserveDefaults if true, values of form inputs will be set to their default values.
 *  Otherwise, they are set to the empty string.
 */
X2Forms.prototype.clearForm = function (container, preserveDefaults) {
    var preserveDefaults = typeof preserveDefaults === 'undefined' ? false : preserveDefaults; 
    if (preserveDefaults) {
        $(container).find ('textarea, input, select').each (function () {
            var defaultVal = $(this).attr ('data-default') || $(this)[0].defaultValue;
            if (typeof defaultVal === 'undefined') {
                $(this).val (''); 
            } else {
                $(this).val (defaultVal); 
            }
        });
    } else {
        $(container).find ('textarea, input, select').val ('');
    }
    $(container).find ('.error').removeClass ('error');
    $(container).find ('.x2-forms-error-msg').remove ();
};

/**
 * Sets data-default attributes of all form inputs in specified container to their current value.
 * These values get restored in the clearForm () method.
 * @param Object element containing the form
 */
X2Forms.prototype.setDefaults = function (container) {
    $(container).find ('textarea, input, select').each (function () {
        var defaultVal = $(this).val ();
        $(this).attr ('data-default', defaultVal);
    });
};

/**
 * Disables/enables a subsection of a form
 * @param Object container the element containing the form subsection
 * @param Bool disable if true, disables the subsection. enables the subsection otherwise
 */
X2Forms.prototype.disableEnableFormSubsection = function (container, disable) {
    var disable = typeof disable === 'undefined' ? true : disable; 

    if (disable) { 
        $(container).find ('textarea, input, select').attr ('disabled', 'disabled');
    } else {
        $(container).find ('textarea, input, select').removeAttr ('disabled');
    }
}


X2Forms.prototype.toggleFormSection = function(section) {
    var that = this;
    if($(section).hasClass('showSection'))
        $(section).find('.tableWrapper').slideToggle(400,function(){
            $(this).parent('.formSection').toggleClass('showSection');
            that.saveFormSections();
        });
    else {
        $(section).toggleClass('showSection').find('.tableWrapper').slideToggle(400);
        that.saveFormSections();
    }
};

X2Forms.prototype.saveFormSections = function() {
    var formSectionStatus = [];
    $('div.x2-layout .formSection').each(function(i,section) {
        formSectionStatus[i] = $(section).hasClass('showSection')? '1' : '0';
    });
    var formSettings = '['+formSectionStatus.join(',')+']';
    $.ajax({
        url: yii.scriptUrl+'/site/saveFormSettings',
        type: 'GET',
        data: 'formName='+window.formName+'&formSettings='+encodeURI(formSettings)
    });
};


X2Forms.prototype.hideDefaultText = function(field) {
    if(field.defaultValue==field.value) {
        field.value = ''
        field.style.color = 'black'
    }
};

/**
 * Displays short default text if field text matches long default text
 */
X2Forms.prototype.showShortDefaultText = function(field) {
    if (!$(field).attr ('data-short-default-text') ||
        !$(field).attr ('data-long-default-text')) {

        return;
    }
    
    var shortDefaultText = $(field).attr ('data-short-default-text');
    var longDefaultText = $(field).attr ('data-long-default-text');
    if(field.value === longDefaultText) {
        field.value = shortDefaultText;
        field.style.color = '#aaa'
    }
};

/**
 * Displays long default text if field text matches short default text
 */
X2Forms.prototype.showLongDefaultText = function(field) {
    if (!$(field).attr ('data-short-default-text') ||
        !$(field).attr ('data-long-default-text')) {

        return;
    }
    
    var longDefaultText = $(field).attr ('data-long-default-text');
    var shortDefaultText = $(field).attr ('data-short-default-text');
    if(field.value === shortDefaultText) {
        field.value = longDefaultText;
        field.style.color = '#aaa'
    }
};

/**
 * Toggles text, using short/long versions of default text
 */
X2Forms.prototype.toggleTextResponsive = function(field) {
    if (!$(field).attr ('data-short-default-text') ||
        !$(field).attr ('data-long-default-text')) {

        return;
    }
    var shortDefault = $(field).attr ('data-short-default-text');
    var longDefault = $(field).attr ('data-long-default-text');
    if ($('body').hasClass ('x2-mobile-layout')) {
        if (field.value === shortDefault) {
            field.value = '';
            field.style.color = 'black';
        } else {
            field.value = shortDefault;
            field.style.color = '#aaa';
        }
    } else {
        if (field.value === longDefault) {
            field.value = '';
            field.style.color = 'black';
        } else {
            field.value = longDefault;
            field.style.color = '#aaa';
        }
    }
};

/**
 * Used to hide/show default text of input
 */
X2Forms.prototype.toggleText = function(field, focus) {
    if(field.defaultValue==field.value) {
        field.value = ''
        field.style.color = 'black'
    } else if(field.value=='') {
        field.value = field.defaultValue
        field.style.color = '#aaa'
    }
};


/**
 * Like toggleText except that it uses the attribute data-default-text to store the
 * placeholder value. This can be used for fields that are already populated on page load.
 * Instead, all fields with the class 'x2-default-field' are automatically initialized on page load.
 * @param object jQuery object
 */
X2Forms.prototype.enableDefaultText = function (element) {
    if (!$(element).attr ('data-default-text')) {
        return;
    }
    var defaultText = $(element).attr ('data-default-text');
    if ($(element).val () === '') {
        $(element).val (defaultText); 
        $(element).css ({color: '#aaa'});
    }
    $(element).off ('blur.defaultText').
        on ('blur.defaultText', function () {

        if ($(element).val () === '') {
            $(element).val (defaultText); 
            $(element).css ({color: '#aaa'});
        }
    });
    $(element).off ('focus.defaultText').
        on ('focus.defaultText', function () {

        if ($(element).val () === $(element).attr ('data-default-text')) {
            $(element).val (''); 
            $(element).css ({color: 'black'});
        }
    });
};

X2Forms.prototype.formFieldFocus = function(elem) {
    var field = $(elem);
    if(field.val() == field.attr('title')) {
        field.val('');
        field.css('color','#000');
    }
};

X2Forms.prototype.formFieldBlur = function(elem) {
    var field = $(elem);
    if(field.val() == '') {
        field.val(field.attr('title'));
        field.css('color','#aaa');
    }
};

X2Forms.prototype.submitForm = function(formName) {
    document.forms[formName].submit();
};

/**
 * Show form and scroll down to it
 */
X2Forms.prototype.toggleForm = function(formName,duration) {
    if($(formName).is(':hidden')) {
        $('html,body').animate({
            scrollTop: ($('#action-form').offset().top-200)
        }, 300);
    }
    $(formName).toggle('blind',{},duration);

};

X2Forms.prototype.hide = function(field) {
    $(field).hide(); 
    $('#save-changes').addClass('highlight'); 
};

X2Forms.prototype.show = function(field) {
    $(field).show();
};

X2Forms.prototype.renderContactLookup = function(item) {
    var label = "<a style=\"line-height: 1;\">" + item.label + "<span style=\"font-size: 0.6em;\">";

    if(item.email) {        // add email if defined
        label += "<br>";
        label += item.email;
    }

    if(item.city || item.state || item.country || item.email) {
        label += "<br>";

        if(item.city)
            label += item.city;
        if(item.state) {
            if(item.city)
                label += ", ";
            label += item.state;
        }
        if(item.country) {
            if(item.city || item.state)
                label += ", ";
            label += item.country;
        }
    }
    if(item.assignedTo){
        label += "<br>" + item.assignedTo;
    }
    label += "</span>";
    label += "</a>";

    return label;
};

X2Forms.prototype.fileUpload = function(form, fileField, action_url, remove_url) {
    // Create the iframe...
    var iframe = document.createElement("iframe");
    iframe.setAttribute("id", "upload_iframe");
    iframe.setAttribute("name", "upload_iframe");
    iframe.setAttribute("width", "0");
    iframe.setAttribute("height", "0");
    iframe.setAttribute("border", "0");
    iframe.setAttribute("style", "width: 0; height: 0; border: none;");

    // Add to document...
    form.parentNode.appendChild(iframe);
    window.frames['upload_iframe'].name = "upload_iframe";

    iframeId = document.getElementById("upload_iframe");

    // Add event...
    var eventHandler = function () {

            if(iframeId.detachEvent) iframeId.detachEvent("onload", eventHandler);
            else iframeId.removeEventListener("load", eventHandler, false);

            // Message from server...
            if(iframeId.contentDocument) {
                var content = iframeId.contentDocument.body.innerHTML;
            } else if(iframeId.contentWindow) {
                var content = iframeId.contentWindow.document.body.innerHTML;
            } else if(iframeId.document) {
                var content = iframeId.document.body.innerHTML;
            }

            var response = $.parseJSON(content)

            if(response['status'] == 'success') {
                // success uploading temp file
                // save it's name in the form so it gets attached when the user clicks send
                var file = $('<input>', {
                    'type': 'hidden',
                    'name': 'AttachmentFiles[id][]',
                    'class': 'AttachmentFiles',
                    'value': response['id'] // name of temp file
                });

                var temp = $('<input>', {
                    'type': 'hidden',
                    'name': 'AttachmentFiles[temp][]',
                    'value': true
                });

                var parent = fileField.parent().parent().parent();

                parent.parent().find('.error').html(''); // clear error messages
                
                // save copy of file upload span before we start making changes
                var newFileChooser = parent.clone(); 

                parent.removeClass('next-attachment');
                parent.append(file);
                parent.append(temp);

                var remove = $("<a>", {
                    'href': "#",
                    'html': "[x]"
                });

                parent.find('.filename').html(response['name']);
                parent.find('.remove').append(remove);

                remove.click(function() {
                    removeAttachmentFile(remove.parent().parent(), remove_url); return false;
                });

                fileField.parent().parent().remove();

                parent.after(newFileChooser);
                initX2FileInput();

            } else {
                fileField.parent().parent().parent().find('.error').html(response['message']);
                fileField.val("");
            }

            // Del the iframe...
            setTimeout('iframeId.parentNode.removeChild(iframeId)', 250);
        }

    if(iframeId.addEventListener)
        iframeId.addEventListener("load", eventHandler, true);
    if(iframeId.attachEvent)
        iframeId.attachEvent("onload", eventHandler);

    // Set properties of form...
    form.setAttribute("target", "upload_iframe");
    form.setAttribute("action", action_url);
    form.setAttribute("method", "post");
    form.setAttribute("enctype", "multipart/form-data");
    form.setAttribute("encoding", "multipart/form-data");

    // Submit the form...
    form.submit();
};

// remove an attachment that is stored on the server as a temp file
X2Forms.prototype.removeAttachmentFile = function(attachment, remove_url) {
    var id = attachment.find(".AttachmentFiles");
    $.post(remove_url, {'id': id.val()});

    attachment.remove();
};

// set up x2 file input
// call this function everytime an x2 file input is created
X2Forms.prototype.initX2FileInput = function() {
    // bind hover and click effects
    $('input.x2-file-input[type=file]').hover(function() {
        var button = $('input.x2-file-input[type=file]').next();
        if(button.hasClass('active') == false) {
            $('input.x2-file-input[type=file]').next().addClass('hover');
        }
    }, function() {
        $('input.x2-file-input[type=file]').next().removeClass('hover');
    });

    $('input.x2-file-input[type=file]').mousedown(function() {
        $('input.x2-file-input[type=file]').next().removeClass('hover');
        $('input.x2-file-input[type=file]').next().addClass('active');
    });

    $('body').mouseup(function() {
        $('input.x2-file-input[type=file]').next().removeClass('active');
    });

    // position the saving icon for uploading files
    // width
    var chooseFileButtonCenter = parseInt($('input.x2-file-input[type=file]').css('width'), 10)/2;
    var halfIconWidth = parseInt($('#choose-file-saving-icon').css('width'), 10)/2;
    var iconLeft = chooseFileButtonCenter - halfIconWidth;
    $('#choose-file-saving-icon').css('left', iconLeft + 'px');

    // height
    var chooseFileButtonCenter = parseInt($('input.x2-file-input[type=file]').css('height'), 10)/2;
    var halfIconHeight = parseInt($('#choose-file-saving-icon').height(), 10)/2;
    var iconTop = chooseFileButtonCenter - halfIconHeight;
    $('#choose-file-saving-icon').css('top', iconTop + 'px');

};

/**
 * Hide input and place a loading gif in its place 
 * @param object the input element
 */
X2Forms.prototype.inputLoading = function (elem) {
    $(elem).hide ();
    $(elem).before ($('<div>', {
        'class': 'x2-loading-icon',
        'style': 'height: 27px; background-size: 27px;'
    }));
};

/**
 * Remove loading gif created by inputLoading ()
 * @param object the input element
 */
X2Forms.prototype.inputLoadingStop = function (elem) {
    $(elem).prev ().remove ();
    $(elem).show ();
};

/*
Private instance methods
*/

X2Forms.prototype._setUpFormElementBehavior = function () {
    var that = this;
    $('div.x2-layout .formSectionShow, .formSectionHide').click(function() {
        that.toggleFormSection($(this).closest('.formSection'));
        that.saveFormSections();
    });

    $('a#showAll, a#hideAll').click(function() {
        $('a#showAll, a#hideAll').toggleClass('hide');
        if($('#showAll').hasClass('hide')) {
            $('div.x2-layout .formSection:not(.showSection)').each(function() {
                if($(this).find('a.formSectionHide').length > 0)
                    that.toggleFormSection(this);
            });
        } else {
            $('div.x2-layout .formSection.showSection').each(function() {
                if($(this).find('a.formSectionHide').length > 0)
                    that.toggleFormSection(this);
            });
        }
    });

    $('.inlineLabel').find('input:text, textarea').focus(function() { 
            that.formFieldFocus(this); 
        }).blur(function() { 
            that.formFieldBlur(this); 
        });

    // set up x2 helper tooltips
    if (typeof $().qtip !== 'undefined') {
        $('.x2-hint').qtip({
            events: {
                show: function (event, api) {
                    var tooltip = api.elements.tooltip;
                    var windowWidth = $(window).width ();
                    var elemWidth = $(api.elements.target).width ();
                    var elemLeft = $(api.elements.target).offset ().left;
                    var tooltipWidth = $(api.elements.tooltip).width ();

                    if (elemLeft + elemWidth + tooltipWidth > windowWidth) {

                        // flip tooltip if it would go off screen
                        api.set ({
                            'position.my': 'top right',
                            'position.at': 'bottom right'
                        });
                    } else {
                        api.set ({
                            'position.my': 'top left',
                            'position.at': 'bottom right'
                        });
                    }
                }
            }

        });
        $('.x2-info').qtip(); // no format qtip (.x2-hint turns text blue)
    }

};

X2Forms.prototype.getElementWidth = function (elem) {
    // determine width, using a clone if necessary
    if (!$(elem).is (':visible')) {
        var dummyElem = $(elem).clone ();
        $('body').append (dummyElem);
        var elemWidth = $(dummyElem).width () + 15;
        //var elemHeight = $(dummyElem).height ();
        dummyElem.remove ();
    } else {
        var elemWidth = $(elem).width ();
        //var elemHeight = $(elem).height ();
    }
    return elemWidth;
};

/**
 * Initialize all the fields that have default values.
 */
X2Forms.prototype.initializeDefaultFields = function () {
    var that = this;
    $('.x2-default-field').each (function () { that.enableDefaultText ($(this)); });
};

X2Forms.prototype._init = function () {
    var that = this;
    $(function () { 
        that._setUpFormElementBehavior (); 
        that.initializeDefaultFields ();
        that.initializeMultiselectDropdowns ();
    });
};

return X2Forms;

}) ();
