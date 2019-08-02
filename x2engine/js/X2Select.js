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
 * Custom select element. To use this, apply the class 'x2-select' to your select elements.
 * On page ready, all such elements will be converted to custom select elements. 
 */

x2.Select = (function ($) {

function Select (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        elem: null,
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._customSelect = null; // the custom select element
    this._customSelectMenu = null; // the custom select menu element
    this._customSelectOuter = null; // the element containing the menu and custom select
    this._instanceNum = Select.instances++; // used to create unique html ids

    /* false until width of custom select is set properly. This prevents width from being
    reinitialized more than once */
    //this._widthSet = false; 

    this._init ();
}

Select.instances = 0;

/*
Public static methods
*/

/**
 * Calls reinitWidth on all custom select elements inside specified container
 * @param object jQuery element 
 */
/*Select.reinitWidths = function (containerElem) {
    return;
    $(containerElem).find ('.x2-select').each (function () {
        $(this).data ('Select').reinitWidth (); 
    });
};*/

/*
Private static methods
*/

/*
Public instance methods
*/

/**
 * Tears down custom select and reinitializes it. Use this if you have dynamically updated the
 * original select element and need to refresh the custom select element to match it (e.g. if you
 * have added options).
 */
Select.prototype.reinit = function () {
    $(this._customSelectOuter).remove ();
    $(this.elem).show ();
    new Select ({'elem': this.elem});
};

/**
 * Select an option. Changes value of hidden select element and changes text of custom select
 * element. 
 * @param string val the value of the option to select
 */
Select.prototype.select = function (val) {
    var that = this;
    that.DEBUG && console.log ('select');
    auxlib.selectOptionFromSelector ($(this.elem), val);
    $(this._customSelect).html ($.trim ($(this.elem).find (':selected').html ()));
};

/**
 * Since the width of custom select elements is determined by the width of the associated select
 * element, the width cannot be know at page load if the select element is hidden. To get around
 * this issue, reinitWidth should be called when a select element, which was hidden on page load, 
 * is shown. 
 */
/*Select.prototype.reinitWidth = function () {
    return;
    var that = this;
    if (this._widthSet) return;
    this.elem.show ();
    if ($(this.elem).is (':visible')) this._widthSet = true;
    that.DEBUG && console.log ($(this.elem).width ());
    this._customSelectMenu.width ($(this.elem).width ());
    this._customSelect.width ($(this.elem).width ());
    this._customSelect.height ($(this.elem).height ());
    this.elem.hide ();
};*/


/*
Private instance methods
*/

/**
 * Sets up behavior of custom options menu 
 */
Select.prototype._setUpDropdownBehavior = function () {
    var that = this;

    // show and position custom select menu
    $(this._customSelectOuter).children ().click (function () {
        if ($(that._customSelectMenu).is (':visible')) {
            $(that._customSelectMenu).hide ();
            return;
        }
        $(that._customSelectMenu).show ();
        $(that._customSelectMenu).position ({
            my: 'left top',
            at: 'left bottom',
            of: $(that._customSelect)
            //collision: 'none'
        });
    });

    // close custom select menu on click outside
    auxlib.onClickOutside ($('#x2-custom-select-menu-' + that._instanceNum + 
        ', #x2-custom-select-' + that._instanceNum + 
        ', #x2-custom-select-triangle-' + that._instanceNum), function () {

        $(that._customSelectMenu).hide ();
        return false;
    });

    // select an option, both in the custom select and the actual, hidden, select element
    $(this._customSelectMenu).find ('li').click (function () {
        $(that._customSelectMenu).hide ();
        that.select ($(this).attr ('data-x2-select-val'));
        return false;
    });
};

/**
 * Generate a custom select element to mask the default one 
 */
Select.prototype._createCustomSelectElem = function () {
    var that = this; 

    /* if element is not visible, its width cannot be determined. clone it and use the 
    clones width instead */
    if (!$(this.elem).is (':visible')) {
        var dummyElem = $(this.elem).clone ();
        $('body').append (dummyElem);
        var elemWidth = $(dummyElem).width ();
        var elemHeight = $(dummyElem).height ();
        dummyElem.remove ();
    } else {
        var elemWidth = $(this.elem).width ();
        var elemHeight = $(this.elem).height ();
    }

    var customSelectCss = $.extend ({
            //background: 'red'
        }, {
            display: $(this.elem).css ('display'),
            'float': $(this.elem).css ('float')
        });

    // build custom select element
    var customSelectOuter = $('<span>', {
        'class': 'x2-custom-select-outer',
        css: customSelectCss
    });
    var customSelect = $('<div>', {
        'class': 'x2-custom-select',
        'id': 'x2-custom-select-' + this._instanceNum,
        'width': elemWidth,
        'height': elemHeight,
        'html': $.trim ($(this.elem).find (':selected').html ())
    });
    $(customSelectOuter).append (customSelect);
    $(this.elem).after (customSelectOuter);

    // add dropdown triangle
    $(customSelectOuter).append ($('<div>', {
        'class': 'x2-custom-select-triangle',
        'id': 'x2-custom-select-triangle-' + this._instanceNum,
    }));

    // build custom select element menu
    var customSelectMenu = $('<ul>', {
        'class': 'x2-custom-select-menu',
        'id': 'x2-custom-select-menu-' + this._instanceNum,
        width: elemWidth,
        css: {
            display: 'none'
        }
    });
    $(this.elem).find ('option').each (function () {
        $(customSelectMenu).append ($('<li>', {
            html: $.trim ($(this).html ()),
            'data-x2-select-val': $(this).val ()
        }));
    });
    $(customSelectOuter).append (customSelectMenu);

    if (!$(this.elem).find ('option').length) {
        $(customSelect).addClass ('x2-custom-select-empty');
    }

    this._customSelect = customSelect;
    this._customSelectMenu = customSelectMenu;
    this._customSelectOuter = customSelectOuter;
    //if ($(this.elem).is (':visible')) this._widthSet = true;
    $(this.elem).hide ();
};

Select.prototype._init = function () {
    var that = this;

    $(this.elem).data ('Select', this);
    this._createCustomSelectElem ();
    this._setUpDropdownBehavior ();
};

return Select;

}) (jQuery);

/*
auto-instantiate custom select elements
*/
$(function () {
    $('.x2-select').each (function () {
        new x2.Select ({elem: $(this)});
    });
});


