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
 * Manages behavior of email inbox contact and non-contact tooltips
 */

x2.EmailInboxesQtipManager = (function () {

function EmailInboxesQtipManager (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    x2.X2GridViewQtipManager.call (this, argsDict);
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        translations: {
            Email: 'Email',
            'Create contact': 'Create contact',
            'action': 'action'
        }
    };
    this.nonContactClass = '.non-contact-entity-tag';
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this._init ();
}

EmailInboxesQtipManager.prototype = auxlib.create (x2.X2GridViewQtipManager.prototype);

/**
 * refresh both contact and non-contact tooltips 
 */
EmailInboxesQtipManager.prototype.refresh = function () {
    var that = this;
    x2.X2GridViewQtipManager.prototype.refresh.call (this);

	$(this.nonContactClass).each(function (i) {
		var email = $(this).attr("data-email");
		var name = $(this).text ();

        $(this).qtip(that._getNonContactConfig ($(this), email, name));
	});
};

/**
 * add shared config to contact tooltip 
 */
EmailInboxesQtipManager.prototype._getConfig = function (elem, recordId) {
    var that = this;
    var template$ = $(
        '<div>' +
            '<div class="contact-details">' +
            '</div>' + 
            '<div class="contact-action-button x2-button qtip-button">' + 
                '<span class="fa fa-plus highlight-text"></span>' +
                '<span>' + this.translations['action'] + '</span>' +
            '</button' +
        '</div>');

    var config = $.extend (this._getSharedConfig (elem), {
        content: {
            text: function (event, api) {
                $.ajax ({
                    url: yii.scriptUrl+'/'+that.modelType+'/qtip',
                    data: { 
                        id: recordId,
                        suppressTitle: that.dataAttrTitle ? 1 : 0
                    },
                    method: "get"
                }).then (function (content) {
                    var content$ = template$.clone ();
                    content$.find ('.contact-details').html (content);
                    api.set ('content.text', content$.get (0).outerHTML);
                });
                return $('<div>', {
                    'class': 'qtip-loading-text',
                    text: that.loadingText
                });
            }, 
        },
        style: {
            classes: 'x2-qtip email-inboxes-qtip',
            tip: {
                corner: true,
            }
        }
    });
    return config;
};

/**
 * @return object config settings shared by contact and non-contact tooltips 
 */
EmailInboxesQtipManager.prototype._getSharedConfig = function (elem) {
    return {
        position: {
            viewport: $(window),
            my: 'top center',
            at: 'bottom center',
            target: $(elem),
            effect: false
        },
        hide: {
            fixed: true,
            delay: 100
        },
        show: {
            delay: 800
        }
    }
};

EmailInboxesQtipManager.prototype._getNonContactConfig = function (elem, email, name) {
    var that = this;

    var template$ = $(
        '<div>' +
            '<div>' +
                '<h2 class="non-contact-name"></h2>' +
                '<div>' + 
                    this.translations['Email'] + 
                        ':&nbsp;<strong class="non-contact-email"></strong>' + 
                '</div>' + 
            '</div>' + 
            '<button class="new-contact-from-entity-button x2-button">' + 
                this.translations['Create contact'] +
            '</button' +
        '</div>');

    var config = $.extend (this._getSharedConfig (elem), {
        content: {
            text: function (event, api) {
                var content$ = template$.clone ();
                content$.find ('.non-contact-name').text (name);
                content$.find ('.non-contact-email').text (email);
                return content$.html ();
            }, 
        },
        style: {
            classes: 'x2-qtip non-contact-qtip',
            tip: {
                corner: true,
            }
        }
    });
    if (that.dataAttrTitle) {
        config.content.title = $(elem).attr ('data-qtip-title');
    }
    return config;
};

EmailInboxesQtipManager.prototype._setUpQuickCreateButtonBehavior = function () {
    var that = this;

    // close tooltip and open quick create dialog when quick create button is clicked
    $(document).off ('click._setUpQuickCreateButtonBehavior', '.new-contact-from-entity-button').
        on ('click._setUpQuickCreateButtonBehavior', '.new-contact-from-entity-button', 
            function () {

        var qtip = $(this).closest ('.qtip').data ('qtip');
        var link = qtip.options.position.target;
        var email = $(link).attr ('data-email');
        var fullName = $.trim ($(link).text ());
        var attributes = {};
        attributes.email = email;
        var pieces = fullName.split (/[ ]+/);
        if (pieces.length === 2) {
            attributes.firstName = pieces[0];
            attributes.lastName = pieces[1];
        } else {
            attributes.firstName = fullName;
        }
        new x2.QuickCreate ({
            modelType: 'Contacts',
            attributes: attributes,
            success: function (modelId) {
                that._convertNonContactLinks (modelId, link);
            }
        });
        qtip.hide ();
        return false;
    });

    // set up quick action creation
    $(document).off ('click._setUpQuickCreateButtonBehavior2', '.contact-action-button').
        on ('click._setUpQuickCreateButtonBehavior2', '.contact-action-button', 
            function () {

        var messageContainer$ = $('#message-container');
        // decode and remove email message
        var messageBody = auxlib.htmlDecode (
            messageContainer$.find ('.message-body-temp').html ());
        var subject = auxlib.htmlDecode (
            $.trim (messageContainer$.find ('.message-subject').html ()));

        var qtip = $(this).closest ('.qtip').data ('qtip');
        var link = qtip.options.position.target;
		var recordId = $(link).attr("href").match(/\d+$/)[0];
        var data = {
            actionType: 'ActionFormModel',
            keepForm: true,
            showAssociationControls: true,
            'ActionFormModel[associationId]': recordId,
            'ActionFormModel[associationType]': 'contacts',
            'ActionFormModel[associationName]': $(link).text ()
        };
        if (subject !== 'undefined') {
            data['ActionFormModel[subject]'] = subject;
        }
        if (messageBody !== 'undefined') {
            data['ActionFormModel[actionDescription]'] = messageBody;
        }
        new x2.QuickCreate ({
            modelType: 'Actions',
            data: data,
            success: function (modelId) {
            }
        });
        qtip.hide ();
        return false;
    });
};

/**
 * Converts non-contact links into a contact links
 * @param object attributes attributes of newly created record
 */
EmailInboxesQtipManager.prototype._convertNonContactLinks = function (attributes, link) {
    var that = this;
    var grid$ = $(link).closest ('.grid-view');
    var newLink$ = $('<a>', {
        href: yii.scriptUrl + '/contacts/id/' + attributes.id, 
        text: attributes.firstName + ' ' + attributes.lastName,
        'class': 'contact-name'
    });
    $(newLink$).qtip (that._getConfig (newLink$, attributes.id));
    $('.non-contact-entity-tag').each (function () {
        var link$ = $(this);
        if (link$.attr ('data-email') === attributes.email) {
            link$.qtip ('destroy', true);
            link$.replaceWith (newLink$.clone ());
        }
    });
    grid$.data ('x2-emailInboxesGridSettings').rebindContactLinkEventHandler ();
    this.refresh ();
};

EmailInboxesQtipManager.prototype._init = function () {
    this._setUpQuickCreateButtonBehavior ();
};


return EmailInboxesQtipManager;

}) ();
