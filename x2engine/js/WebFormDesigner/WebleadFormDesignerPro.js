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




x2.WebleadFormDesignerPro = (function(){

    

    function WebleadFormDesignerPro (argsDict) {
    	x2.WebFormDesigner.call (this, argsDict);	
    }

    WebleadFormDesignerPro.prototype = auxlib.create (x2.WebleadFormDesigner.prototype);

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

    /*
    Append additional query parameters
    */
    WebleadFormDesignerPro.prototype._appendToQuery = function (query) {

        var fieldList = this._getFieldList ();
        
        if(query.match (/[?]/)) {
            query += '&';
        } else {
            query += '?';
        }

        query += 'css=' + encodeURIComponent($('#custom-css').val());
        query += 'redirectUrl=' + encodeURIComponent($('#redirect-url').val());
        return query;
    };

    /*
    Clear form input
    */
    WebleadFormDesignerPro.prototype._clearFields = function () {
        var that = this;
        $('#web-form-name').val('');
        $('#custom-html').val('');
        $('#custom-css').val('');
        $('#tags').val('');
        $.each(that.fields, function(i, field) {
            $('#'+field).val('');
        });
    };

    /*
    Insert form input
    */
    WebleadFormDesignerPro.prototype._updateExtraFields = function (form) {

        if(typeof form.redirectUrl !== 'undefined') {
            $('#redirect-url').val(form.redirectUrl);
        }
        if(typeof form.css !== 'undefined') {
            $('#custom-css').val(form.css);
        }
        if(typeof form.header !== 'undefined') {
            $('#custom-html').val(form.header);
        }
        if(typeof form.userEmailTemplate !== 'undefined') {
            $('#user-email-template').val(form.userEmailTemplate);
        }
        if(typeof form.webleadEmailTemplate !== 'undefined') {
            $('#weblead-email-template').val(form.webleadEmailTemplate);
        }

        x2.WebleadFormDesigner.prototype._updateExtraFields.call (this, form);
    };

    WebleadFormDesignerPro.prototype._beforeSaved = function () {
        $('#add-custom-html-button').removeClass ('highlight');
        auxlib.destroyErrorBox ($('#custom-html-input-container'));
    };

    WebleadFormDesignerPro.prototype._afterSavedFormsChange = function () {
        auxlib.destroyErrorBox ($('#custom-html-input-container'));
        $('#add-custom-html-button').removeClass ('highlight');
    };

    WebleadFormDesignerPro.prototype._afterInit = function () {
        var that = this;

        $('#custom-css').on('change', function() {
            that.updateParams();
        });

        /*
        Indicate to user that they have changes to save
        */
        $('#custom-html').on('keydown change', function(evt) {
            x2.DEBUG && console.log ('change'); 
            if ($('#custom-html').val () !== '') {
                $('#web-form-save-button').addClass ('highlight');
            } else {
                $('#web-form-save-button').removeClass ('highlight');
            }
        });

        x2.WebFormDesigner._enableTabsForCustomCss ();
        that._setUpSortableCustomFieldsBehavior ();

    };

    /*
    Returns a dictionary containing custom fields form input values + tags.
    */
    WebleadFormDesignerPro.prototype._getFieldList = function (form) {
        var fieldList = [];
        $('#sortable2').find('li').each(function() {
            var f = {};
            f['fieldName'] = $(this).attr('name');
            f['required'] = $(this).find('input[type="checkbox"]').is(':checked');
            f['label'] = $(this).find('input[type="text"]').val();
            f['position'] = $(this).find('select.field-position').val();
            f['type'] = $(this).find('select.field-type').val();
            fieldList.push(f);
        });
        if ($('#tags').val () !== '') {
            fieldList.push ({
                fieldName: 'tags',  
                type: 'tags',  
                required: false,  
                position: 'top',  
                label: $('#tags').val ()
            });
        }
        return fieldList;
    };

    return WebleadFormDesignerPro;
})();
