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




x2.SortByPillBox = (function () {

function SortByPillBox (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.PillBox.call (this, argsDict);
}

SortByPillBox.prototype = auxlib.create (x2.PillBox.prototype);

/**
 * Override parent method so that label gets looked up properly 
 */
SortByPillBox.prototype._addPreexistingValues = function () {
    for (var i in this.value) {
        var val = this.value[i];
        var key = this.value[i][0];
        this._addPill (val, this._getLabelOfVal (key));
    }
};


return SortByPillBox;


}) ();

x2.SortByPill = (function () {

function SortByPill (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;

    var defaultArgs = {
        translations: {
            ascending: '',
            descending: '',
            'delete': ''
        },
        value: null
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
   console.log ('this.value = ');
    console.log (this.value);

    this._sortDirectionSelect$ = null;

    if (Object.prototype.toString.call (this.value) === '[object Array]') {
        this.direction = this.value[1];
        this.value = this.value[0];
    }
    x2.Pill.call (this, argsDict);
}

SortByPill.prototype = auxlib.create (x2.Pill.prototype);

///**
// * Add sort direction to pill data 
// */
//SortByPill.prototype.getData = function () {
//    return [x2.Pill.prototype.getData.call (this), this._sortDirectionSelect$.val ()];
//};

/**
 * Add sort order select element 
 */
SortByPill.prototype._setUpPillElements = function () {
    x2.Pill.prototype._setUpPillElements.call (this);
    var i = this.owner.element$.find ('.x2-pill').length;
    // overwrite input name set by parent
    this.element$.find ('input').attr ('name', this.owner.name + '[' + i + '][]');
    this.element$.addClass ('sort-by-pill');

    this._sortDirectionSelect$ = $('<select>', {
        name: this.owner.name + '[' + i + '][]',
    });
    this._sortDirectionSelect$.append ($('<option>', {
        value: 'asc',
        text: this.translations.ascending
    }));
    this._sortDirectionSelect$.append ($('<option>', {
        value: 'desc',
        text: this.translations.descending
    }));
    if (this.direction)
        this._sortDirectionSelect$.val (this.direction);

    this.element$.append (this._sortDirectionSelect$);
};

return SortByPill;

}) ();
