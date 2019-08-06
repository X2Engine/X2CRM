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




x2.RecordAliasesWidget = (function () {

function RecordAliasesWidget (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    x2.Widget.call (this, argsDict);
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        baseUrl: '',
        aliasOptions: {},
        aliasTypeIcons: {},
        recordId: null,
         
        googlePlusIntegrationEnabled: false,
         
        translations: {
        }
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this._hideShowButton$ = this.element$.find ('.view-aliases-button');
    this._dropdown$ = this.element$.find ('.alias-dropdown');
    this._addAliasButton$ = this.element$.find ('.new-alias-button');
    this._secondaryAddAliasButton$ = $('#record-aliases-action-menu-link');
    this._dialog$ = this.element$.find ('.add-alias-dialog');
    this._init ();
}

RecordAliasesWidget.prototype = auxlib.create (x2.Widget.prototype);

RecordAliasesWidget.prototype._showDropdown = function () {
    var that = this;
    this._dropdown$.show ();
    auxlib.onClickOutside (this.element$.selector + ', .ui-dialog, .ui-button-text', function () {
        that._hideDropdown ();
    }, true);
};

RecordAliasesWidget.prototype._hideDropdown = function () {
    this._dropdown$.attr ('style', '');
    this._dropdown$.hide ();
};

RecordAliasesWidget.prototype._setUpHideShowBehavior = function () {
    var that = this;
    this.element$.prev ('.view-aliases-button').click (function () {
        if (!that._dropdown$.is (':visible')) {
            that._showDropdown ();
        } else {
            that._hideDropdown ();
        }
        return false;
    });
};

/**
 * Add new alias to dropdown 
 */
RecordAliasesWidget.prototype._addAlias = function (aliasType, alias, id, label) {
    var label = label ? label : alias;
    var newAliasTitle = this.aliasOptions[aliasType];
    var li$ = this._dropdown$.find ('.alias-template').clone ();
    li$.show ().
        removeClass ('alias-template').
        attr ('data-alias-type', newAliasTitle).
        attr ('data-id', id);
    li$.find ('.record-alias').html (label);
    li$.find ('.record-alias').before (this.aliasTypeIcons[aliasType]);
    this._dropdown$.children ('span').append (li$);
    listItems$ = this._dropdown$.find ('li').
        not ('.new-alias-button, .find-google-plus-profile, .alias-template');
    sortedListItems$ = listItems$.sort (function (a, b) {
        var a$ = $(a);
        var b$ = $(b);
        var aliasTypeA = a$.attr ('data-alias-type').toLowerCase ();
        var aliasTypeB = b$.attr ('data-alias-type').toLowerCase ();
        if (aliasTypeA < aliasTypeB) {
            return -1;
        } else if (aliasTypeA === aliasTypeB) {
            var aliasA = $.trim (a$.find ('.record-alias').text ());
            var aliasB = $.trim (b$.find ('.record-alias').text ());
            if (aliasA < aliasB) {
                return -1;
            } else if (aliasA === aliasB) {
                return 0;
            } else {
                return 1;
            }
        } else {
            return 1;
        }
    });
    this._dropdown$.find ('li').
        not ('.new-alias-button, .find-google-plus-profile, .alias-template').remove ();
    this._dropdown$.children ('span').append (sortedListItems$);
    this._setUpAliasDeletion ();
};

/**
 * Submit alias creation form
 */
RecordAliasesWidget.prototype._createAlias = function (afterCreate, dialog$) {
    afterCreate = typeof afterCreate === 'undefined' ? function () {} : afterCreate; 
    dialog$ = typeof dialog$ === 'undefined' ? this._dialog$ : dialog$; 
    var that = this;
    var data = dialog$.serialize ();
    var dataObj = $.deparam (data);
    var aliasType = dataObj['RecordAliases']['aliasType'];
    var alias = dataObj['RecordAliases']['alias'];

    $.ajax ({
        url: this.baseUrl + '/createRecordAlias',
        data: data,
        dataType: 'json',
        success: function (data) {
            if (data.success) {
                that._addAlias (aliasType, data.success.alias, data.success.id, data.success.label);
                dialog$.dialog ('close');
                if (dialog$.attr ('id') === 'record-alias-form') {
                    x2.forms.clearForm (dialog$, true);
                    dialog$.find ('.alias-type-cell').first ().click ();
                }
                afterCreate.call (
                    that, data.success.alias, data.success.id, data.success.label,
                    data.success.rawAlias);
            } else {
                x2.forms.clearErrorMessages (dialog$);
                dialog$.append (data.failure);
            }
        }
    })
};

RecordAliasesWidget.prototype._deleteAlias = function (aliasId) {
    var that = this;
    $.ajax ({
        url: this.baseUrl + '/deleteRecordAlias?id=' + aliasId,
        success: function (data) {
            if (data === 'success') {
                that._dropdown$.find ('li').filter (function () { 
                    return $(this).attr ('data-id') == aliasId;
                }).remove ();
            } else {
            }
        }
    })
};

RecordAliasesWidget.prototype._openDialog = function (afterCreate) {
    afterCreate = typeof afterCreate === 'undefined' ? function () {} : afterCreate; 
    var that = this;
    this._dialog$.dialog ({
        title: this.translations.dialogTitle,
        autoOpen: true,
        width: 500,
        buttons: [
            {
                text: this.translations.cancel,
                click: function () {
                    $(this).dialog ('close');
                }
            },
            {
                text: this.translations.create,
                click: function () {
                    that._createAlias (afterCreate);
                },
                'class': 'highlight'
            }
        ],
        close: function () {
            $(this).dialog ('destroy');
        }
    });

};

RecordAliasesWidget.prototype._setUpDialog = function () {
    var that = this;
    this._addAliasButton$.click (function () {
        that._openDialog ();
        that._dropdown$.hide ();
    });
    this._secondaryAddAliasButton$.click (function () {
        that._openDialog (function () {
            that._dropdown$.show ();
        });
        that._dropdown$.hide ();
    });
};

/**
 * Bind event handlers to alias creation form elements 
 */
RecordAliasesWidget.prototype._bindFormEvents = function () {
    var that = this;
    this.element$.find ('form').submit (function () {
        return false; 
    });
    this._dialog$.find ('input[type="radio"]').change (function () {
        that._dialog$.find ('.selected').removeClass ('selected');
        $(this).closest ('.alias-type-cell').children ().addClass ('selected');
    });
    this._dialog$.find ('.alias-type-cell').click (function (evt) {
        $(this).find ('input').prop ('checked', function (i, val) {
            return !val;
        });
        $(this).find ('input').change ();
    });
    this._dialog$.find ('.alias-type-cell input').click (function (evt) {
        evt.stopPropagation ();
    });

};

RecordAliasesWidget.prototype._setUpAliasDeletion = function () {
    var that = this; 
    this._dropdown$.find ('.delete-alias-button').click (function () {
        var aliasId = $(this).closest ('li').attr ('data-id');
        auxlib.confirm (function () {
            that._deleteAlias (aliasId);
        }, {
            title: that.translations.confirmDeletionTitle, 
            message: that.translations.confirmDeletion, 
            cancel: that.translations.cancel,
            confirm: that.translations.OK
        });
    });
};

RecordAliasesWidget.prototype._showSkypeTooltip = function (li$) {
    if (li$.attr ('data-hasqtip')) return;

    var that = this;
    li$.qtip ({
        content: {
            text: function (event, api) {
                $.ajax ({
                    url: yii.scriptUrl+'/site/getSkypeLink',
                    data: { 
                        'usernames[]': $.trim (li$.find ('.record-alias').html ())
                    },
                    method: "get"
                }).then (function (content) {
                    api.set ('content.text', content);
                });
                return that.translations.skypeQtipLoadingText;
            }, 
        },
        style: {
            classes: 'skype-qtip',
            def: false,
            tip: {
                corner: true,
            }
        },
        show: {
            ready: true,
            event: 'click'
        },
        hide: {
            event: 'mouseleave',
            fixed: true,
            delay: 200
        },
        position: {
            viewport: $(window),
            my: 'top center',
            at: 'bottom center',
            target: li$,
            effect: false
        }
    });
};

RecordAliasesWidget.prototype._setUpSkypeLinks = function () {
    var that = this;
    this._dropdown$.on ('click', 'li', function () {
        if ($(this).attr ('data-alias-type') === 'Skype') {
            that._showSkypeTooltip ($(this));
        }
    });
};



RecordAliasesWidget.prototype._refreshGooglePlusWidget = function (alias, label) {
    var googlePlusWidget = x2.GooglePlusProfileWidget;
    if (!googlePlusWidget) return;
    googlePlusWidget.addProfile (alias, label);
};

/**
 * Sets up Google+ profile search dialog
 */
RecordAliasesWidget.prototype._setUpGooglePlusIntegration = function () {
    var that = this;
    var searchProfileButton$ = this.element$.find ('.find-google-plus-profile');
    var profileSearchForm$ = $('#google-plus-profile-search-form');
    this.profileSearchForm$ = profileSearchForm$;
    var searchResultsContainer$ = profileSearchForm$.find ('.search-results-container');
    var searchInput$ = profileSearchForm$.find ('.search-string');
    var oldSearchVal = null;

    profileSearchForm$.dialog ({
        title: this.translations.googleSearchdialogTitle,
        autoOpen: false,
        width: 500,
        buttons: [
            {
                text: this.translations.cancel,
                click: function () {
                    $(this).dialog ('close');
                }
            },
            {
                text: this.translations.createProfile,
                click: function () {
                    var selected$ = profileSearchForm$.find ('.selected'); 
                    if (!selected$.length) {
                        x2.forms.errorSummaryAppend (profileSearchForm$, [
                            'Please select a profile.'
                        ]); 
                    } else {
                        that._createAlias (function (alias, id, label, rawAlias) {
                            that._refreshGooglePlusWidget (rawAlias, label);
                        }, profileSearchForm$);
                    }
                },
                'class': 'create-profile-button'
            }
        ], 
        open: function () {
            searchInput$.blur ();
        },
        close: function () {
            that._clearGoogleProfileSelection (profileSearchForm$, searchResultsContainer$);
            profileSearchForm$.closest ('.ui-dialog').find ('.create-profile-button').
                removeClass ('highlight');
        }
    });

    searchProfileButton$.click (function () {
        profileSearchForm$.dialog ('open'); 
    });

    // set up profile fetch
    var nextPageToken = null;
    searchInput$.blur (function () {
        if (searchInput$.val () === oldSearchVal) {
            return;
        }
        oldSearchVal = searchInput$.val ();
        // request profiles matching search string and render them inside options container
        that._searchGooglePlusProfiles ($(this).val (), function (data) { 
            searchResultsContainer$.empty ();
            nextPageToken = data.nextPageToken;
            that._updateGoogleProfileOptions (
                searchResultsContainer$, profileSearchForm$, data.profiles);
        });
    });

    // set up infinity scroll. New pages of profile options should be fetched as user scrolls 
    // downwards
    var searching = false;
    var infScroll = new x2.InfinityScroll ({
        element: searchResultsContainer$,
        callback: function (next) {
            if (!next || searching) return;

            if (nextPageToken) {
                searching = true;
                that._searchGooglePlusProfiles (searchInput$.val (), function (data) {
                    nextPageToken = data.nextPageToken;
                    that._updateGoogleProfileOptions (
                        searchResultsContainer$, profileSearchForm$, data.profiles);
                    searching = false;
                }, nextPageToken);
            }
        }
    });
        
    this._setUpGoogleProfileSelection (profileSearchForm$, searchResultsContainer$);
};

/**
 * Update options with profiles fetched from the Google+ API
 */
RecordAliasesWidget.prototype._updateGoogleProfileOptions = function (
    searchResultsContainer$, profileSearchForm$, profiles) {

    var template$ = profileSearchForm$.find ('.search-result-template');
    for (var i in profiles) { 
        var result = profiles[i];
        var option$ = template$.clone ()
            .removeClass ('search-result-template')
            .show ();
        option$.find ('.label-input').val (result.displayName);
        option$.find ('.alias-input').val (result.id);
        option$.find ('img').attr ('src', result.image);
        option$.find ('.search-result-display-name').text (result.displayName).
            attr ('href', result.url);
        searchResultsContainer$.append (option$);
    }
};

/**
 * Set up behavior of Google+ profile option selection
 */
RecordAliasesWidget.prototype._setUpGoogleProfileSelection = function (
    profileSearchForm$, searchResultsContainer$) {

    var that = this;
    this.prevGoogleProfileOption$ = null;
    searchResultsContainer$.on ('click', '.search-result', function () {
        var selected$; 
        x2.forms.clearErrorMessages (profileSearchForm$);

        profileSearchForm$.closest ('.ui-dialog').find ('.create-profile-button').
            addClass ('highlight');

        that._clearGoogleProfileSelection (profileSearchForm$, searchResultsContainer$);

        profileSearchForm$.find ('.search-result').removeClass ('selected'); 
        that.prevGoogleProfileOption$ = $(this).prev ();
        selected$ = $(this).addClass ('selected').detach ();
        searchResultsContainer$.before (selected$);
        selected$.find ('input').removeAttr ('disabled');
    });
};

RecordAliasesWidget.prototype._clearGoogleProfileSelection = function (
    profileSearchForm$, searchResultsContainer$) {

    var that = this;
    if (profileSearchForm$.find ('.selected').length) {
        selected$ = profileSearchForm$.find ('.selected').detach ();
        selected$.find ('input').attr ('disabled', 'disabled');
        if (that.prevGoogleProfileOption$.length) {
            that.prevGoogleProfileOption$.after (selected$);
        } else {
            searchResultsContainer$.prepend (selected$);
        }
    }
};

/**
 * Request Google+ profiles matching search string
 */
RecordAliasesWidget.prototype._searchGooglePlusProfiles = function (
    searchString, callback, nextPageToken) {

    nextPageToken = typeof nextPageToken === 'undefined' ? null : nextPageToken; 
    var that = this;
    $.ajax ({
        url: yii.scriptUrl + '/googlePlus/search',
        data: { 
            searchString: searchString,
            nextPageToken: nextPageToken
        },
        type: 'GET',
        dataType: 'JSON',
        success: function (data) {
            callback (data);
        },
        error: function (data) {
            x2.forms.errorSummaryAppend (that.profileSearchForm$, [
                data.responseText 
            ]); 
        }
    });
};
 

RecordAliasesWidget.prototype._init = function () {
    this._setUpHideShowBehavior ();
    this._setUpDialog ();
    this._setUpAliasDeletion ();
    this._bindFormEvents ();
    this._setUpSkypeLinks ();
     
    this._setUpGooglePlusIntegration ();
     
};

return RecordAliasesWidget;

}) ();
