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




x2.FolderManager = (function () {

function FolderManager (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        urls: {
            moveFolder: null,
            index: null,
            deleteFileFolder: null
        },
        translations: {
            createFolder: '',
            deleteFolderConf: '',
            deleteDocConf: '',
            folderDeleted: '',
            docDeleted: '',
            permissionsMissing: '',
        }
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this._init ();
}


FolderManager.prototype._moveStart = function (fileSysObj$) {
    fileSysObj$.addClass ('moving-file');
    fileSysObj$.find('.file-system-object-attributes').hide();
    fileSysObj$.find('.file-system-object-link').css('width','100%');
    $('#file-delete').show();
//    fileSysObj$.next ().width (fileSysObj$.width ()).animate ({
//        width: 150,
//    }, 500);
};

FolderManager.prototype._moveStop = function (fileSysObj$) {
    fileSysObj$.removeClass ('moving-file');
    fileSysObj$.find('.file-system-object-link').css('width','30%');
    fileSysObj$.find('.file-system-object-attributes').show();
    $('#file-delete').hide();
};
        
FolderManager.prototype.setUpDragAndDrop = function(){
    var that = this;
    $('.draggable-file-system-object').draggable({
        helper: 'clone',
        delay: 200,
        cursor:'move',
        cursorAt:{top:0,left:0},
        revert:'invalid',
        stack:'.draggable-file-system-object',
        start: function(){
            that._moveStart ($(this));
        },
        stop: function(){
            that._moveStop ($(this));
        }   
    });
    $('.droppable-file-system-object').droppable({
        accept:'.draggable-file-system-object',
        activeClass:'x2-active-folder',
        hoverClass:'x2-state-active highlight',
        drop: function(event, ui){
            ui.draggable.hide();
            var type = ui.draggable.find ('.file-system-object').attr('data-type');
            var objId = ui.draggable.find ('.file-system-object').attr('data-id');
            var destId = $(this).find ('.file-system-object').attr('data-id');
            $.ajax({
                url:that.urls.moveFolder,
                data:{type:type, objId:objId, destId:destId},
                error:function(){
                    ui.draggable.show();
                }
            });
        }
    });
}

FolderManager.prototype._init = function () {
    var that = this;
    $(document).on('click','#create-folder-button',function(){
        $('#folder-form').dialog({
            width: '500px',
            buttons: [
                {
                    text: that.translations.createFolder,
                    click: function () {
                        $('#folder-form input[type=\"submit\"]').click ();
                    }
                }
            ]
        });
    });
    $(document).on('click','.file-system-object-folder .folder-link',function(){
        $.fn.yiiGridView.update('folder-contents',{
            url:that.urls.index,
            data:{id:$(this).attr('data-id')},
            complete:function(){
                that.setUpDragAndDrop();
            }
        }); 
        $('#DocFolders_parentFolder').val($(this).attr('data-id')); 
        return false;
    });
    $(document).on('ready',function(){
        that.setUpDragAndDrop();
        $('#delete-drop').droppable({
            accept:'.draggable-file-system-object',
            hoverClass:'highlight',
            tolerance: 'touch',
            drop:function(event, ui){
                ui.draggable.hide();
                var type = ui.draggable.find ('.file-system-object').attr('data-type');
                var id = ui.draggable.find ('.file-system-object').attr('data-id');
                var message = type === 'folder' ?
                    that.translations.deleteFolderConf :
                    that.translations.deleteDocConf;
                if(window.confirm(message)){
                    $.ajax({
                        url:that.urls.deleteFileFolder,
                        method:'POST',
                        data:{YII_CSRF_TOKEN:x2.csrfToken,type:type, id:id},
                        success:function(){
                            x2.flashes.displayFlashes({
                                success:[type === 'folder' ? 
                                   that.translations.folderDeleted :
                                   that.translations.docDeleted]
                            });
                        },
                        error:function(){
                            x2.flashes.displayFlashes({
                                'error':[that.translations.permissionsMissing],
                                });
                            $.fn.yiiGridView.update(
                                'folder-contents', {
                                    complete:function(){ 
                                        that.setUpDragAndDrop(); 
                                    }});
                        }
                    });
                }else{
                    ui.draggable.show();
                    return false;
                }
            }
        });
    });
};


return FolderManager;

}) ();
