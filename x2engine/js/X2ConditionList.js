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




x2.ConditionList = (function () {

function ConditionList (argsDict) {
    var that = this;
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        containerSelector: '',
        visibilityOptions: {},
        operatorList: {},
        allTags: {},
        options: [],
        modelClass: '',
        value: []
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._container$ = $(this.containerSelector);
    this._sortList$ = this._container$.children ('.x2-cond-list');
    this._addCondButton$ = this._container$.find ('button');
    var fieldsOptions = {};
    var newOptionsCombined = []
    for (var elemArray in this.options) {
        newOptionsCombined = newOptionsCombined.concat(this.options[elemArray]);
    }
    fieldsOptions[this.modelClass+"_all"] = newOptionsCombined;
    this._fields = new x2.FieldsGeneric ({
        templateSelector: this.containerSelector + ' .x2fields-template',
        options: fieldsOptions,
        visibilityOptions: this.visibilityOptions,
        operatorList: this.operatorList,
        allTags: this.allTags
    });
    this._fields.addChangeListener (this.containerSelector, function () {
        that._reindexInputs (); 
    })
    this._fields.enableChangedOperator = true;
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

/*
Private instance methods
*/

/**
 * Sets up behavior of add/delete condition buttons 
 */
ConditionList.prototype._setUpAddRemoveConditionBehavior = function () {
    var that = this;
    this._addCondButton$.click (function () {
        var field$ = that._fields.createAttrListItem(that.modelClass, that.options)
            .hide()
            .appendTo(that._sortList$.children ('ol'))
            .slideDown(200);
        var i = that._sortList$.children ('ol').children ().length - 1;
        var attr;
        field$.find (':input').each (function (index, elem) {
            attr = $(elem).attr ('name');
            if (typeof attr !== 'undefined' && attr !== false && $(elem).attr ('name')) {
                $(elem).attr ('name', $(elem).attr ('name').replace (/\[i\]/, '[' + i + ']'));
            }
        });
        return false;
    });

    // Listen for clicks on the "delete condition" buttom
    this._container$.on("click", "a.del", function() {
        $(this).closest("li").slideUp(200, function(){ 
            $(this).remove(); 
            that._reindexInputs (); 
        });
    });
};

/**
 * Used to retrieve condition input values for form submission
 * @deprecated condition list inputs now have names that allow form to be correctly serialized,
 *  simplifying AJAX form submission
 * @return object contains information about each condition (name, operator, and value)
 */
ConditionList.prototype.getAttributesConfig = function () {
    var that = this;
    var attributeRows = this._sortList$.children ('ol').children ('li');
    var attrConfig = [];
    if(attributeRows.length) {
        attributeRows.each(function(i, elem) {
            attrConfig.push({
                name:$(elem).find(".x2fields-attribute select, .x2fields-attribute input").
                    first().val(),
                operator:$(elem).find(".x2fields-operator select").first().val(),
                value:that._fields.getVal($(elem).
                    find(".x2fields-value :input[name='value']").first())
            });
        });
    }
    return attrConfig;
};

/**
 * Reindexes input names, ensuring that numeric indices are sequential and start at 0
 */
ConditionList.prototype._reindexInputs = function () {
    var that = this;
    this._sortList$.children ('ol').children ().each (function (i, elem) {
        $(elem).find (':input').each (function (j, elem) {
            if ($(elem).attr ('name')) 
                $(elem).attr ('name', $(elem).attr ('name').replace (/\[[i0-9]+\]/, '[' + i + ']'));
        });
    });
};

ConditionList.prototype._addPreexistingValues = function () {
    var that = this;

    for (var i in this.value) {
        var val = this.value[i];
        var field$ = that._fields.createAttrListItem(that.modelClass, that.options)
        that._sortList$.children ('ol').append (field$);
        field$.find ('.x2fields-attribute :input').val (val.name).change ();
        field$.find ('.x2fields-operator :input').val (val.operator).change;
        field$.find ('.x2fields-value :input').val (val.value);
    }
    this._reindexInputs ();
};

ConditionList.prototype._init = function () {
    this._setUpAddRemoveConditionBehavior ();
    this._addPreexistingValues ();
};

return ConditionList;

}) ();
