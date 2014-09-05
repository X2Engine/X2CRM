
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

/**
 * Prototype for publisher tab. 
 */

if(typeof x2 == 'undefined')
    x2 = {};
if(typeof x2.publisher == 'undefined')
    x2.publisher = {};

x2.PublisherTab = (function () {

function PublisherTab (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        translations: {},
        id: null, // id of element containing tab contents
    };

    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._elemSelector = '#' + this.id;

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

PublisherTab.prototype.submit = function (publisher, form) {
    var that = this;

    x2.forms.clearErrorMessages ($(form));

    // submit tab contents
    $.ajax ({
        url: publisher.publisherCreateUrl,
        type: 'POST',
        data: form.serialize (),
        dataType: 'json',
        success: function (data) {
            if (typeof data['redirect'] !== 'undefined') {

                window.location = data['redirect']
                return;
            }
            if (typeof data['error'] !== 'undefined') {
                $(form).find ('.form').append (x2.forms.errorSummary ('', data));
                $(that._elemSelector).find ('[name="Actions\\[associationName\\]"]').
                    addClass ('error');
                $(form).find ('input.hightlight').removeClass ('highlight');
            } else {
                publisher.updates();
                publisher.reset();
            }
        }
    });

};

/**
 * Clears tab's form inputs 
 */
PublisherTab.prototype.reset = function () {
    var that = this;
    x2.forms.clearForm (this._element, true);
};

/**
 * Disables tab's form inputs 
 */
PublisherTab.prototype.disable = function () {
    var that = this;
    that.DEBUG && console.log ('disable');
    x2.forms.disableEnableFormSubsection (this._element, true);
};

/**
 * Enables tab's form inputs 
 */
PublisherTab.prototype.enable = function () {
    var that = this;
    that.DEBUG && console.log ('enable');
    x2.forms.disableEnableFormSubsection (this._element, false);
};

/**
 * Blurs tab
 */
PublisherTab.prototype.blur = function () {
    $(this._elemSelector).find ('.action-description').animate({"height":22},300);
};

/**
 * Focus tab 
 */
PublisherTab.prototype.focus = function () {
};


/**
 * @param Bool True if form input is valid, false otherwise
 */
PublisherTab.prototype.validate = function () {
    x2.forms.clearErrorMessages (this._element);
    var actionDescription$ = this._element.find ('.action-description');

    if (actionDescription$.hasClass ('x2-required') && actionDescription$.val () === '') {

        actionDescription$.parent ().addClass ('error');
        x2.forms.errorSummaryAppend (this._element, this.translations['beforeSubmit']);
        return false;
    } else {
        return true;
    }
};

PublisherTab.prototype.run = function () {
    var that = this;
    that._element = $(that._elemSelector);
    x2.forms.setDefaults (that._element);
    that._setUpActionDescriptionBehavior ();
};

/*
Private instance methods
*/

/**
 * Expand action description textarea on click
 */
PublisherTab.prototype._setUpActionDescriptionBehavior = function () {
    var that = this;
    that.DEBUG && console.log ('_setUpActionDescriptionBehavior');
    this._element.find ('.action-description').click (function () {
        that.DEBUG && console.log ('_setUpActionDescriptionBehavior.click'); 
        $(this).height (80);
    });
};

PublisherTab.prototype._init = function () {};

return PublisherTab;

}) ();
