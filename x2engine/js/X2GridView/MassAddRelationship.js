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






x2.MassAddRelationship = (function () {

function MassAddRelationship (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        massActionName: 'MassAddRelationship',
        enableAjaxValidation: true
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.MassAction.call (this, argsDict);
    this.dialogTitle = this.massActionsManager.translations[this.massActionName + 'DialogTitle'];
    this.goButtonLabel = this.massActionsManager.translations[this.massActionName + 'GoButton'];
    this._init ();
}

MassAddRelationship.prototype = auxlib.create (x2.MassAction.prototype);

MassAddRelationship.prototype._setUpFormBehavior = function () {
    var that = this;

    this.form$.data ('afterAutocompleteCreated', function () {
        // kludge to get autocomplete menu to display correctly
        that.menu$ = that.form$.find ('.record-name-autocomplete').autocomplete ('widget');
        $('body').append (that.menu$.detach ());
        that.menu$.addClass ('x2-dialog-autocomplete-menu');
    });
};

MassAddRelationship.prototype.openDialog = function () {
    x2.MassAction.prototype.openDialog.call (this);
    this.dialogElem$.find ('select').change ();
    this.dialogElem$.dialog ('widget').addClass ('fixed-dialog-z-index');
};

MassAddRelationship.prototype._init = function () {
    this._setUpFormBehavior ();
    x2.MassAction.prototype._init.call (this);
};


return MassAddRelationship;

}) ();

