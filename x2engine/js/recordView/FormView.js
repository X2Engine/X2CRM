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




x2.FormView = (function() {

function FormView (argsDict) {
    var defaultArgs = {
        quickCreate: {
            urls: [],
            tooltips: [],
            dialogTitles: [],
            defaults: []
        },
        translations: {}
    };

    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.RecordView.call(this, argsDict);
    this.form$ = $('#' + this.namespace + 'form-view');
    this._hasChanges = false;
    this.init ();
}

FormView.prototype = auxlib.create(x2.RecordView.prototype);

FormView.prototype.setUpEvents = function() {
    $('.x2-layout.form-view :input').change(function() {
        $('#save-button, #save-button1, #save-button2, h2 a.x2-button').addClass('highlight');
    });
};

FormView.prototype.setUpQuickCreate = function() {
    var that = this;
    $('.quick-create-button').each (function () {
        if ($(this).data ('x2QuickCreateInitialized') !== 1) {
            var relatedModelType = $(this).attr ('class').match (/(?:[ ]|^)create-([^ ]+)/)[1];
            $(this).data ('x2QuickCreateInitialized', 1);
            new x2.RelationshipsManager ({
                element: $(this),
                modelType: that.modelName,
                modelId: that.modelId,
                relatedModelType: relatedModelType,
                createRecordUrl:   that.quickCreate.urls        [relatedModelType],
                dialogTitle:       that.quickCreate.dialogTitles[relatedModelType],
                tooltip:           that.quickCreate.tooltips    [relatedModelType],
                attributeDefaults: that.quickCreate.defaults    [relatedModelType],
                lookupFieldElement: $(this).siblings ('.formInputBox').find ('input').last (),
                isViewPage: false
            });
        }
    });
};

FormView.prototype.setUpUnsavedChangeDetection = function () {
    var that = this;
    this.form$.on ('change', function () {
        that._hasChanges = true;
    });
    // display confirmation dialog if there are unsaved changes and user attempts to navigate away
    // via a link
    $(document).on ('click.' + this.namespace + 'setUpUnsavedChangeDetection', 'a', function (evt) {
        var link$ = $(this); 
        if ($.type (link$.attr ('href')) === 'string' && link$.attr ('href') !== '#' && 
            !link$.attr ('href').match (/^javascript:/) && 
            that._hasChanges) {

            auxlib.confirm (function () {
                that._hasChanges = false;
                window.location = link$.attr ('href');
            }, that.translations);
            return false;
        } 
    });
};

FormView.prototype.init = function() {
    x2.RecordView.prototype.init.call(this);

    this.setUpUnsavedChangeDetection ();

    $.datepicker.setDefaults ($.datepicker.regional['']);
    $(".currency-field").maskMoney("mask");

    this.setUpEvents();
    this.setUpQuickCreate();
};

return FormView;


})();


