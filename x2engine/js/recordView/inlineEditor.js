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




x2.InlineEditor = (function() {

function InlineEditor(argsDict) {
    var defaultArgs = {
        modelId: null,
        translations: {
            unsavedChanges: null
        },
        'form$': null
    };

    auxlib.applyArgs(this, defaultArgs, argsDict);

    this.init();
}

InlineEditor.prototype.init  = function () {
    var that = this;

    // Set up Selectors 
    
    this.inlineEdit  = '.inline-edit';
    this.editIcon    = '.edit-icon';
    this.confirmIcon = '.confirm-icon';
    this.cancelIcon  = '.cancel-icon';

    this.$inlineEdit  = this.form$.find (this.inlineEdit);
    this.$editIcon    = this.form$.find (this.editIcon);
    this.$confirmIcon = this.form$.find (this.confirmIcon);
    this.$cancelIcon  = this.form$.find (this.cancelIcon);

    this.setUpUnsavedBehavior ();
    this.setUpEditButton ();
    this.setUpCancelButton ();
    this.setUpConfirmButton ();

}

InlineEditor.prototype.setUpEditButton  = function () {
    var that = this;

    this.$inlineEdit.find (this.editIcon).click( function(e) {
        e.preventDefault();

        var inlineEdit = $(this).closest (that.inlineEdit);
        var id = inlineEdit.attr('id');
        var inputContainer = $('#' + id + '-input');
        var input = inputContainer.find (':input');
        var field = $('#' + id + '-field');

        inlineEdit.
            find (that.confirmIcon + ', ' + that.cancelIcon).
            addClass('active'); 

        $(this).removeClass('active');

        inputContainer.height(field.height());
        inputContainer.show ();
        field.hide ();
        
        if (input.is ('textarea')) 
            input.height (field.height());

    });

}

InlineEditor.prototype.setUpCancelButton = function () {
    var that = this;
    
    this.$inlineEdit.find (this.cancelIcon).click (function (e) {
        e.preventDefault();

        var inlineEdit = $(this).closest(that.inlineEdit);
        that.resetField(inlineEdit);
    });

}

InlineEditor.prototype.setUpConfirmButton = function () { 
    var that = this;

    this.$inlineEdit.find (this.confirmIcon).click (function (e) {
        e.preventDefault();

        var inlineEdit = $(this).closest (that.inlineEdit);

        var attributes = {};

        inlineEdit.find ('.model-input input, .model-input select, .model-input textarea').
            each (function() {
                attributes[$(this).attr('name')] = $(this).val();
            }
        );

        $.each(x2.InlineEditor.ratingFields, function(index, value) {
            if (typeof value === 'undefined') {
                attributes[index] = '';
            } else {
                attributes[index] = value;
            }
        });

        inlineEdit.find ('.model-input :checkbox').each(function(){
            if($(this).is(':checked')){
                attributes[$(this).attr('name')] = 1;
            }else{
                attributes[$(this).attr('name')] = 0;
            }
        });

        $.ajax({
            url: yii.scriptUrl + '/site/ajaxSave',
            type: 'POST',
            dataType: 'json',
            data: {
                attributes: attributes,
                modelId: that.modelId
            },
            success: function(data) {
                if (data.errors) {
                    x2.topFlashes.displayFlash (data.errors, 'error', 'clickOutside');
                } else {
                    $.each (data.updatedFields, function(index, value) {
                        if (index.match ('_email$')) {
                            // If an email address is modified, update the InlineEmail widget
                            that.updateInlineEmail (index);
                        }
                        $('#' + index + '_field-field').html(value);
                        $('#' + index + '_field-field input[type=radio]').rating();
                        $('#' + index + '_field-field input[type=radio]').rating('readOnly', true);
                    });
                    that.resetField (inlineEdit);
                }
            }
        });

    });

    // Set up key functions
    $(document).keypress(function(e) {
        // 13 is the Enter Key
        if(e.which != 13)  return;

        // Dont trigger on textareas
        var active = $(this.activeElement);
        if (active.is('textarea')) return;

        // find the closest editable field if there is one
        var inlineEdit = active.closest (that.inlineEdit);
        if (inlineEdit.length == 0) return;

        e.preventDefault();
        // Trigger clicking the confirm icon;
        inlineEdit.find (that.confirmIcon +'.active').click ();
    });

}

InlineEditor.prototype.resetField = function ($parent) {

    $parent.find (this.confirmIcon).removeClass('active');
    $parent.find (this.cancelIcon).removeClass('active');
    $parent.find (this.editIcon).addClass('active');

    $parent.find ('.model-input').hide();
    $parent.find ('.model-attribute').show();
}


/**
 * Update the "to" address in the InlineEmail widget if present
 * @param string HTML Element ID namespace, e.g., 'Contacts_email'
 */
InlineEditor.prototype.updateInlineEmail = function (namespace) {
    var email = $('#' + namespace + '_field-input').
        find('input').val();
    if (typeof x2 != 'undefined' && typeof x2.inlineEmailEditorManager != 'undefined') {
        x2.inlineEmailEditorManager.setToField (email);
    }
}

InlineEditor.prototype.setUpUnsavedBehavior  = function () {
    var that = this;
}

return InlineEditor;

})();

x2.InlineEditor.ratingFields = {};

