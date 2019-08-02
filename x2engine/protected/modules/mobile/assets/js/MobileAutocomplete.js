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




x2.MobileAutocomplete = (function () {

function MobileAutocomplete (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.Widget.call (this, argsDict);

    this.url = this.element$.attr ('data-x2-link-source');
    this.hiddenElem$ = this.element$.closest ('.ui-input-text').prev ();
    this.container$ = this.element$.closest ('.field-container');
    this.blurring = false;
    this.init ();
}

MobileAutocomplete.prototype = auxlib.create (x2.Widget.prototype);

MobileAutocomplete.prototype.search = function () {
    var that = this;
    $.ajax ({
        url: this.url,
        data: {
            term: this.element$.val ()
        },
        dataType: 'json',
        success: function (data) {
            if (data.length) {
                that.populate (data);
            } else {
                that.clear ();
            }
        }
    });
};

MobileAutocomplete.prototype.populate = function (data) {
    var that = this;
    this.results$.empty ();
    var options$ = $();
    for (var i in data) {
        var datum = data[i];
        options$ = options$.add ($(
            '<div>',
            {
                'class': 'autocomplete-option',
                text: datum.value,
                'data-x2-val': datum.id
            }
        ));
    }

    this.results$.append (options$);
    this.resultsContainer$.show ();
    this.results$.closest ('.ui-content').addClass ('autocomplete-shown');
    this.resultsContainer$.height ($(window).height () - this.results$.position ().top);

    this.results$.find ('.autocomplete-option').click (function () { 
        that.element$.val ($(this).text ());
        that.hiddenElem$.val ($(this).attr ('data-x2-val'));
        that.clear ();
    });
    if (x2.main.isPhoneGap)
        x2.main.instantiateNano (this.results$);

    this.container$.addClass ('has-focus');
};

MobileAutocomplete.prototype.resize = function () {
    if (this.resultsContainer$.is (':visible')) {
        this.resultsContainer$.height ($(window).height () - this.results$.position ().top);
        //x2.main.instantiateNano (this.results$);
    }
};

MobileAutocomplete.prototype.hide = function () {
    this.resultsContainer$.hide ();
    this.results$.closest ('.ui-content').removeClass ('autocomplete-shown');
};

MobileAutocomplete.prototype.clear = function () {
    this.results$.empty ();
    this.hide ();
    this.container$.removeClass ('has-focus');
};

MobileAutocomplete.prototype.setUpInteraction = function () {
    var that = this;
    this.element$.keyup (function () {
        that.hiddenElem$.val ('');
        if ($(this).val ()) {
            that.search ();
        } else {
            that.clear ();
        }
    });

    // kludge to allow option click to be handled before blur
    $.mobile.activePage.click (function () {
        if (that.blurring) {
            that.clear ();
            that.blurring = false;
        }
    });
    this.element$.blur (function (evt) {
        that.blurring = true;
    });
};

MobileAutocomplete.prototype.buildAutocomplete = function () {
    this.resultsContainer$ = $('<div>', {
        'class': 'x2-autocomplete-results-container'
    });
    this.results$ = $('<div>', {
        'class': 'x2-autocomplete-results'
    });
    this.resultsContainer$.append (this.results$);
    this.resultsContainer$.hide ();
    this.element$.closest ('.ui-input-text').append (this.resultsContainer$);
};

MobileAutocomplete.prototype.init = function () {
    this.buildAutocomplete ();
    this.setUpInteraction ();
};

return MobileAutocomplete;

}) ();

$(document).on ('pagecontainershow', function (evt, ui) {
    $.mobile.activePage.find ('.x2-mobile-autocomplete').each (function () {
        if (!$(this).data (x2.Widget.dataKey)) { 
            new x2.MobileAutocomplete ({
                element: $(this)
            });
        }
    });
});

$(window).resize (function () {
    if ($.mobile && $.mobile.activePage) {
        $.mobile.activePage.find ('.x2-mobile-autocomplete').each (function () {
            x2.Widget.getInstance ($(this)).resize ();
        });
    }
});
