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




if(typeof x2.fieldEditor == 'undefined')
    x2.fieldEditor = {};

x2.fieldEditor.load = function(type,reload,save,override) {
    reload = typeof reload == 'undefined' ? 0 : reload;
    save = typeof save == 'undefined' ? 0 : save;
    override = typeof override == 'undefined' ? 0 : override;
    var that = this;
    var ajaxConfig = {
        url:that.loadUrl+'?search='+(type == 'create' ? '0' : '1')+'&save='+save+'&override='+override,
        type:'GET',
        dataType:'html'
    };
    if(reload) {
        ajaxConfig.type = 'POST';
        ajaxConfig.data = that.formArea.find('form').serialize();
    }
    that.loading.css({
        "height":Math.floor(that.formArea.outerHeight())+'px',
        "width":Math.floor(that.formArea.outerWidth())+'px',
        "top":that.formArea.offset().top,
        "left":that.formArea.offset().left,
        "display":"block"
    });
    jQuery.ajax(ajaxConfig).done(function(data){
        that.formArea.html(data);
    }).always(function() {
        that.loading.hide();
    });
}

jQuery(document).ready(function() {
    x2.fieldEditor.formArea = $('#createUpdateField');
    x2.fieldEditor.form = x2.fieldEditor.formArea.find('form');
    x2.fieldEditor.loading = $("#createUpdateField-loading");

    x2.fieldEditor.formArea.on('change','#modelName-existing,#fieldName-existing',function(){
        // Refetch the page, in "customize" mode
        x2.fieldEditor.load('update',1);
    });
    x2.fieldEditor.formArea.on('change','#fieldType,#dropdown-type,#assignment-multiplicity', function() {
        // Refetch the page, either to customize or to create new, based on class
        var mode = $(this).hasClass('new') ? 'create' : 'update';
        x2.fieldEditor.load(mode,1,0,1);
    });
    x2.fieldEditor.formArea.on('click','#createUpdateField-savebutton',function(e) {
        e.preventDefault();
        var mode = $(this).hasClass('new') ? 'create' : 'update';
        x2.fieldEditor.load(mode,1,1);
        $.fn.yiiGridView.update("fields-grid");
    });

    // Event handler for using the insertable attributes dropdown:
    $('#createUpdateField').on('change',"#insertAttrToken",function(e) {
        // insert this.data.value at current cursor position
        var insertToken = $(e.target).val();
        $("#custom-field-template").each(function(e){
            var obj;
            if( typeof this[0] != 'undefined' && typeof this[0].name !='undefined' ) {
                obj = this[0];
            } else {
                obj = this;
            }

            if ($.browser.msie) {
                obj.focus();
                sel = document.selection.createRange();
                sel.text = insertToken;
                obj.focus();
            } else if ($.browser.mozilla || $.browser.webkit) {
                var startPos = obj.selectionStart;
                var endPos = obj.selectionEnd;
                var scrollTop = obj.scrollTop;
                obj.value = obj.value.substring(0, startPos)+insertToken+obj.value.substring(endPos,obj.value.length);
                obj.focus();
                obj.selectionStart = startPos + insertToken.length;
                obj.selectionEnd = startPos + insertToken.length;
                obj.scrollTop = scrollTop;
            } else {
                obj.value += insertToken;
                obj.focus();
            }
        });
               
    });

});
