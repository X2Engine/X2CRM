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




x2.AggregatesPillBox = (function () {

function AggregatesPillBox (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.PillBox.call (this, argsDict);
}

AggregatesPillBox.prototype = auxlib.create (x2.PillBox.prototype);

/**
 * Override parent method so that label gets looked up properly 
 */
AggregatesPillBox.prototype._addPreexistingValues = function () {
    var groupedValues = {};
    for (var i in this.value) {
        var val = this.value[i];
        groupedValues[val] = groupedValues[val] ? groupedValues[val] : [];
        var matches = val.match (/^([^(]+)/);
        if (matches) {
            groupedValues[val].push (matches[1]); 
        }
        
    }
    for (var val in groupedValues) {
        var fns = groupedValues[val];
        var attr = val.replace (/^[^(]+\((.*)\)$/, '$1');
        this._addPill ([attr, fns], this._getLabelOfVal (attr));
    }
};


return AggregatesPillBox;


}) ();

x2.AggregatesPill = (function () {

function AggregatesPill (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        translations: {
            max: '',
            min: '',
            avg: '',
            sum: '',
            'delete': ''
        },
        value: null
    };
    this._aggregateFns = ['min', 'max', 'avg', 'sum'];
    this._aggregateFnCheckboxes$ = null;
    auxlib.applyArgs (this, defaultArgs, argsDict);
    if (Object.prototype.toString.call (this.value) === '[object Array]') {
        this.fns = this.value[1];
        this.value = this.value[0];
    } else {
        this.fns = [];
    }
    x2.Pill.call (this, argsDict);
}

AggregatesPill.prototype = auxlib.create (x2.Pill.prototype);

///**
// * Add sort direction to pill data 
// */
//AggregatesPill.prototype.getData = function () {
//    var selectedAggregateFns = [];
//    for (var i in this._aggregateFns) {
//        var fn = this._aggregateFns[i];
//        if (this._aggregateFnCheckboxes$.find (
//            'input[name="aggregate-fn-' + fn + '"]').is (':checked')) {
//
//            selectedAggregateFns.push (fn);
//        }
//    }
//    return [x2.Pill.prototype.getData.call (this), selectedAggregateFns];
//};

/**
 * Add sort order select element 
 */
AggregatesPill.prototype._setUpPillElements = function () {
    x2.Pill.prototype._setUpPillElements.call (this);
    var i = this.owner.element$.find ('.x2-pill').length;
    // overwrite input name set by parent
    this.element$.find ('input').attr ('disabled', 'disabled');

    this.element$.addClass ('aggregates-pill');
    this._aggregateFnCheckboxes$ = $('<span>', { 'class': 'aggregate-fn-checkboxes-container' });

    for (var j in this._aggregateFns) {
        var fn = this._aggregateFns[j];
        this._aggregateFnCheckboxes$.append ($('<label>', {
            text: this.translations[fn] + ':'
        }));
        this._aggregateFnCheckboxes$.append ($('<input>', {
            type: 'checkbox',
            name: this.owner.name + '[]',
            value: fn + '(' + this.value + ')'
        }));
    }

    // set preexisting values
    for (i in this.fns) {
        var fn = this.fns[i];
        this._aggregateFnCheckboxes$.find ('[value="' + fn + '(' + this.value + ')"]').
            prop ('checked', true);
    }

    this.element$.append (this._aggregateFnCheckboxes$);
};

/**
 * Sets up behavior 
 */
AggregatesPill.prototype._setUpPillBehavior = function () {
    var that = this;
    x2.Pill.prototype._setUpPillBehavior.call (this);
};


return AggregatesPill;

}) ();
