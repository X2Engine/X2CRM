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






function EmailInboxWidget (argsDict) {
    var defaultArgs = {
        emailInboxId: null,
        folder: null
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
	SortableWidget.call (this, argsDict);	
}

EmailInboxWidget.prototype = auxlib.create (SortableWidget.prototype);

EmailInboxWidget.prototype.refresh = function () {
    // kludge to prevent email editor from being removed by widget contents replacement
    if (window.inlineEmailEditor) {
        window.inlineEmailEditor.destroy (true);
    }
    var inlineEmailForm$ = $('#inline-email-form').detach ();
    var that = this;
    x2.profileWidgetManager.refreshWidget (this.getWidgetKey (), function () {
        $(that.elementSelector).append (inlineEmailForm$);
        x2.inlineEmailEditorManager.reinstantiate ();
    });
};

EmailInboxWidget.prototype._setUpInboxSelection = function () {
    var inboxSelector$ = this.element$.find ('.email-inbox-selector');
    var that = this;
    inboxSelector$.change (function () {
        that.setProperties ({
            emailInboxId: $(this).val (), 
            folder: 'INBOX'
        }, function () {
            // if the widget contents haven't already loaded on the page, we need to do a whole
            // page refresh. Otherwise, CKEditor will be buggy.
            if (!that.emailInboxId) {
                window.location.reload ();
                return;
            }
            auxlib.containerLoading (that.element$)
            that.refresh ();
        });
    });
};

EmailInboxWidget.prototype._setUpFolderSelection = function () {
    var folderSelector$ = this.element$.find ('.email-inbox-folder-selector');
    var that = this;
    folderSelector$.change (function () {
        that.setProperty ('folder', $(this).val (), function () {
            auxlib.containerLoading (that.element$)
            that.refresh ();
        });
    });
};

EmailInboxWidget.prototype._init = function () {
    if (!this.hasError) {
        this._setUpInboxSelection ();
        this._setUpFolderSelection ();
    }
    SortableWidget.prototype._init.call (this);
};
