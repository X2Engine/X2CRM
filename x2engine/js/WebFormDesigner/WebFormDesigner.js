
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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




/*
Base prototype. Should not be instantiated. 
*/

function WebFormDesigner (argsDict) {
    var that = this;

	// properties that can be set with constructor arguments
	var defaultArgs = {
		translations: [], // used for various web form text
		iframeSrc: '',
		savedForms: {},
        fields: [],
        colorfields: [],
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
    return encodeURIComponent(value.trim().replace(/[^a-zA-Z0-9#,]/g, ''));
}

/*
Public instance methods
*/

/*
Private instance methods
*/

/*
Sets up the web form designer
*/
WebFormDesigner.prototype._init = function () {
    var that = this;
    x2.DEBUG && console.log (this);

    // set up embedded code container behavior
    $('#embedcode').focus(function() {
        $(this).select();
    });
    $('#embedcode').mouseup(function(e) {
        e.preventDefault();
    });
    $('#embedcode').focus();

    // instantiate color pickers
    $.each(that.colorfields, function(i, field) {
        var selector = '#' + field;
        setupSpectrum ($(selector));
        x2.DEBUG && console.log ('color change: that = ');
        x2.DEBUG && console.log (that);
        $(selector).on ('change', function () { that.updateParams (); });
    });
    
    // set up form field behavior
    $.each(that.fields, function(i, field) {
        $('#'+field).on('change', function () { that.updateParams (); });
    });

    // set up save web form button behavior
    $('#save').click(function(e) {

        // check form empty input
        if ($.trim($('#web-form-name').val()).length === 0) { // invalid, show errors
            $('#web-form-name').addClass('error');
            $('[for="web-form-name"]').addClass('error');
            $('#save').after('<div class="errorMessage">'+
                that.translations.nameRequiredMsg+'</div>');
            e.preventDefault(); //has no effect
            return false;
        } else { // name validated, remove error messages
            $('#web-form-name').removeClass('error');
            $('[for="web-form-name"]').removeClass('error');
            $('#save').next('.errorMessage').remove ();
        }
    });

    // set up saved form selection behavior
    $('#saved-forms').on('change', function() {
        var id = $(this).val();

        // clear old form, populate form with saved input
        that._clearFields();
        if (id != 0) {
            var match = $.grep(that.savedForms, function(el, i) {
                return id == el.id;
            });
            that._updateFields(match[0]);
        } 

        // update iframe and embedded code
        that.updateParams();
        $('#embedcode').focus();  
        $.each(that.colorfields, function(i, field) {
            if ($('#'+field).val () === '') {
                addCheckerImage ($('#'+field));
            } else {
                removeCheckerImage ($('#'+field));
            }
        });

        // extra behaviors set in child prototype
        that._afterSavedFormsChange ();
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

    // set up reset form button behavior
    $('#reset-form').on('click', function(evt) {
        evt.stopPropagation ();
        $("#saved-forms").val("0").change();
        return false;
    });

    that._afterInit ();

    if (that.listId !== null) { that.updateParams(); }
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
    x2.DEBUG && console.log (that);

    if ($(iframeContainer).data ('ignoreChange')) {
        return;
    }
    var params = [];
    if (that.listId !== null) {
        params.push('lid='+that.listId);
    }

    x2.DEBUG && console.log (that.fields);
    $.each(that.fields, function(i, field) {
        x2.DEBUG && console.log ('getting field: ' + field);
        var value = WebFormDesigner.sanitizeInput($('#'+field).val());
        if (value.length > 0) { params.push(field+'='+value); }
    });

    /* send iframe height to iframe contents view so that iframe contents can be set to correct
    height on iframe load */
    var iframeHeight = $('#iframe_example').height ();
    params.push ('iframeHeight=' + (Math.floor (iframeHeight)));

    var query = this._generateQuery(params);

    var newembed = '<iframe name="web-form-iframe" src="' + $('#iframe_example').data('src') + 
        query + '" frameborder="0" scrolling="0" width="' + 
        parseInt($('#iframe_example').width()) + '" height="' + iframeHeight + '"></iframe>';

    $('#embedcode').val(newembed);
    $('#iframe_example iframe').replaceWith(newembed);
};

/*
Generates a GET parameter string from the given paramaters array
*/
WebFormDesigner.prototype._generateQuery = function (params) {
    var query = '';
    var first = true;

    for (var i=0; i<params.length; i++) {
        if (params[i].search(/^[^=]+=[^=]+$/) != -1) {
            if (first) {
                query += '?'; first = false;
            } else {
                query += '&';
            }

            query += params[i];
        }
    }

    query = this._appendToQuery (query);

    return query;
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

    x2.DEBUG && console.log ('_updateFields');
    x2.DEBUG && console.log (form.params);
    $('#web-form-name').val(form.name);
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

    this._updateExtraFields (form);
    this._updateCustomFields (form);
};

// override in child prototype
WebFormDesigner.prototype._updateExtraFields = function (form) {
    return;
};

// override in child prototype
WebFormDesigner.prototype._updateCustomFields = function (form) {
    return;
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
    alert(that.translations.formSavedMsg);
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

