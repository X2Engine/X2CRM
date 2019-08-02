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




x2.RelationshipsGraphQtipManager = (function () {

function RelationshipsGraphQtipManager (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        qtipSelector: '',
        translations: {
            loadingText: 'Loading...',
            'View record': 'View record'
        }
    };
    x2.QtipManager.call (this, argsDict);
    auxlib.applyArgs (this, defaultArgs, argsDict);
}

RelationshipsGraphQtipManager.prototype = auxlib.create (x2.QtipManager.prototype);

RelationshipsGraphQtipManager.prototype._getConfig = function (elem$) {
    var that = this;
    var elem = elem$.get (0);
    var type = d3.select (elem).attr ('data-type').toLowerCase ();
    var id = d3.select (elem).attr ('data-id');
    var template$ = $(
        '<div class="graph-qtip-inner">' +
            '<div class="qtip-record-details">' +
            '</div>' + 
            '<a href="' + yii.scriptUrl + '/' + type + '/' + id + 
                '" class="view-record-button x2-button">' + 
                this.translations['View record'] +
            '</a>' +
        '</div>');

    var config = {
        hide: {
            fixed: true,
            delay: 100
        },
        show: {
            delay: 800
        },
        content: {
            text: function (event, api) {
                $.ajax ({
                    url: yii.scriptUrl+'/'+type+'/qtip',
                    data: { 
                        id: id,
                    },
                    method: "get"
                }).then (function (content) {
                    var content$ = template$.clone ();
                    content$.find ('.qtip-record-details').append (content);
                    api.set ('content.text', content$.get (0).outerHTML);
                });
                return $('<div>', {
                    text: that.translations.loadingText,
                    style: 'padding: 3px 5px'
                });
            }, 
        },
        style: {
            classes: 'x2-qtip',
            tip: {
                corner: true,
            }
        },
        position: {
            viewport: $(window),
            my: 'top center',
            at: 'bottom center',
            target: $(elem),
            effect: false
        },
    };

    return config;
};

return RelationshipsGraphQtipManager;

}) ();
