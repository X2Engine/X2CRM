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
 * Pill box ui element
 */

x2.PillBox = (function () {

function PillBox (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;

    x2.Widget.call (this, argsDict);
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        /**
         * Used to populate options dropdown
         * @var object options option labels indexed by value or options indexed by opt group names
         */
        options: {},
        /**
         * Used to prepopulate pill box with pills
         * @var array value
         */
        value: [],
        /**
         * @var string name of class used for pill box pills
         */
        pillClass: 'Pill',
        /**
         * @var string name of the form element 
         */
        name: '',
        translations: {
            helpText: '',
            optionsHeader: '',
            'delete': ''
        },
        /**
         * @var bool if true, pill input name indices will be recalculated when pills are deleted 
         *  or sorted
         */
        enablePillReindexing: false,
        /**
         * @var function called when pill dragging starts
         */
        pillDragStart: function() {},
        /**
         * @var function called after pill dragging stops
         */
        pillDragStop: function() {}
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._optionsDropdown$ = null;
    this._pills = []; // pill objects
    this._init ();
}

PillBox.prototype = auxlib.create (x2.Widget.prototype);

///**
// * @return array pill input values
// */
//PillBox.prototype.getData = function () {
//    return auxlib.map (function (pill) {
//        return pill.getData ();
//    }, this._pills);
//};

/**
 * Removes pill from pill array 
 * @param Pill 
 */
PillBox.prototype.removePill = function (pill) {
    var newPillSet = [];
    for (var i in this._pills) {
        var currPill = this._pills[i];
        if (currPill !== pill) {
            newPillSet.push (currPill);
        }
    }
    this._pills = newPillSet;
    if (this.enablePillReindexing) x2.Pill.reindexPills (this.element$);
};

/**
 * Refreshes pills array so it matches pills currently in pill box 
 */
PillBox.prototype._refreshPillsArray = function () {
    this._pills = $.map ($.makeArray (this.element$.find ('.x2-pill')), function (elem) { 
        return $(elem).data (x2.Widget.dataKey); 
    });
};

/**
 * Creates pill box-related DOM elements (pill box, options dropdown) 
 */
PillBox.prototype._setUpPillBoxElements = function () {
    var that = this;

    this.element$.attr ({
        'class': 'x2-pill-box ' + this.element$.attr ('class'),
        'title': this.translations.helpText
    });

    // build options dropdown
    this._optionsDropdown$ = $('<div>', {
        'class': 'x2-pill-box-options',
        style: 'display: none;'
    }).append ($('<ul>', {
        'class': 'x2-dropdown-list',
    }));
    this._optionsDropdown$.prepend ($('<div>', {
        'class': 'options-header',
        text: this.translations.optionsHeader
    }));

    // add options, increasing left padding of nested option groups
    (function addOptions (options, depth, header) {
        var header = typeof header === 'undefined' ? '' : header; 
        for (var val in options) {
            var label = options[val];
            if (Object.prototype.toString.call (label) === '[object Object]') {
                // found option group, add a header and increase indentation of nested options
                // through recursive call
                if (val !== '') {
                    var li$ = $('<li>', {
                        'class': 'opt-group-header',
                        text: val
                    });
                    that._optionsDropdown$.find ('.x2-dropdown-list').append (li$);
                }
                addOptions (label, depth++, val);
            } else {
                var classes = 'x2-dropdown-option x2-dropdown-option-' + depth;
                if (depth > 0) {
                    classes += ' x2-dropdown-option-indent';
                }
                var li$ = $('<li>', {
                    text: label,
                    'class': classes,
                    'data-value': val,
                    'data-header': header
                });
                that._optionsDropdown$.find ('.x2-dropdown-list').append (li$);
            }
        }
    }) (this.options, 0, header);
    this.element$.after (this._optionsDropdown$);
};

/**
 * Sets up behavior of pill box elements 
 */
PillBox.prototype._setUpPillBoxBehavior = function () {
    var that = this;

    // set up dropdown display
    this.element$.click (function (event) { 
        that._optionsDropdown$.position ({    
            my: 'left top',
            at: 'center',
            of: event
        }).show ();

        auxlib.onClickOutside (that._optionsDropdown$, function () {
            this.attr ('style', ''); 
            this.hide (); 
        });
        event.stopPropagation ();
    });

    // set up pill creation
    this._optionsDropdown$.find ('.x2-dropdown-option').click (function () {
        var header = $(this).attr ('data-header');
        var value = $(this).attr ('data-value');
        var label = $(this).text ();
        label = header !== '' ? header + ': ' + label : label;
        that._addPill (value, label);
    });

    // set up pill sortability
    this.element$.sortable ({
        items: '.x2-pill',
        tolerance: 'pointer',
        helper: function (evt, elem) {
            // prevents issue which caused pill to resize when dragged
            return $(elem).clone ().css ({width: $(elem).width () + 1, height: $(elem).height ()});
        },
        start: function () {
            that.pillDragStart ();
        },
        stop: function () {
            that.pillDragStop ();
        },
        cursor: 'move',
        update: function () {
            that._refreshPillsArray ();
            if (that.enablePillReindexing) x2.Pill.reindexPills (that.element$);
        }
    });
};

/**
 * Adds a pill to the pill box 
 * @param string value pill value
 * @param string label pill label
 */
PillBox.prototype._addPill = function (value, label) {
    var pill = new x2[this.pillClass] ({
        value: value,
        label: label,
        translations: this.translations,
        owner: this
    });
    this._pills.push (pill);
    this.element$.append (pill.element$);
};

PillBox.prototype._getLabelOfVal = function (val, options) {
    var options = typeof options === 'undefined' ? this.options : options; 
    if (typeof options[val] !== 'undefined' &&
        Object.prototype.toString.call (options[val]) === '[object String]') {
        
        return options[val];
    }
    var label = val;
    for (var i in options) {
        var opt = options[i];
        if (Object.prototype.toString.call (opt) === '[object Object]') {
            label = this._getLabelOfVal (val, opt);
            if (label !== null) {
                break; 
            }
        } 
    }
    return label;
};

PillBox.prototype._addPreexistingValues = function () {
    for (var i in this.value) {
        var val = this.value[i];
        this._addPill (val, this._getLabelOfVal (val));
    }
};

PillBox.prototype._init = function () {
    this._setUpPillBoxElements ();
    this._setUpPillBoxBehavior ();
    this._addPreexistingValues ();
};




return PillBox;

}) ();


/**
 * Pill class 
 */

x2.Pill = (function () {

function Pill (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        value: null,
        label: null,
        translations: {
            'delete': ''
        },
        owner: null
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this.element$ = null;
    this._deleteButton$ = null;
    this._init ();
}

///**
// * @return string pill value 
// */
//Pill.prototype.getData = function () {
//    return this.element$.attr ('data-value');
//};

/**
 * Reindexes pill input names. Numeric name indices will be set to the index of the pill
 */
Pill.reindexPills = function (parentElem$) {
    parentElem$.find ('.x2-pill').each (function (i, elem) {
        $(elem).find (':input').not ('button').each (function (j, elem) {
            $(elem).attr ('name', $(elem).attr ('name').replace (/\[[0-9]+\]/, '[' + i + ']'));
        })
    });
};

/**
 * Sets up behavior 
 */
Pill.prototype._setUpPillBehavior = function () {
    var that = this;

    // set up pill deletion
    this._deleteButton$.click (function (event) {
        that.element$.trigger ('mouseout'); // add the help text back
        that._delete (); 
        that.owner.removePill (that);
        event.stopPropagation ();
    });

    // prevent options dropdown from appearing when pill is clicked
    this.element$.click (function (event) {
        event.stopPropagation ();
    });

    // hide help text when user hover's over pill
    this.element$.mouseover (function () {
        $(this).parent ().attr ('title', ''); 
    });
    this.element$.mouseout (function () {
        $(this).parent ().attr ('title', that.translations.helpText); 
    });
};

/**
 * Delete pill 
 */
Pill.prototype._delete = function () {
    this.element$.remove ();
};

/**
 * Create pill-related HTML elements 
 */
Pill.prototype._setUpPillElements = function () {
    this.element$ = $('<div>', {    
        'class': 'x2-pill',
        text: this.label
    });
    this.element$.append ($('<input>', {
        type: 'hidden',
        name: this.owner.name + '[]',
        value: this.value
    }));
    this._deleteButton$ = $('<button>', {
        'class': 'x2-pill-delete-button x2-button',
        text: 'x',
        title: this.translations['delete']
    });
    this.element$.prepend (this._deleteButton$);
    this.element$.data (x2.Widget.dataKey, this);
};

Pill.prototype._init = function () {
    this._setUpPillElements ();
    this._setUpPillBehavior ();
};

return Pill;

}) ();
