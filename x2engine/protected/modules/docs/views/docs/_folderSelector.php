<?php
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




Yii::app()->clientScript->registerScript('folderSelectorJS',"
;(function init () {
    x2.folderSelector = {};
    x2.folderSelector.currentFolderId = ".($folder === 'root' ? 'null' : $folder->id).";
    x2.folderSelector.currentFolderName = ".CJSON::encode ($folder === 'root' ? 
        Yii::t('docs', 'Docs') : $folder->name).";

    var folderSelectorListView$ = $('#folder-selector-list-view');
    var targetFolderInput$ = folderSelectorListView$.closest ('.folder-selector').
        siblings ('[name=\"targetFolderId\"]');
    var targetFolderName$ = folderSelectorListView$.closest ('.folder-selector').
        siblings ('.target-folder');

    function selectFolder (option$) {
        option$.addClass ('selected'); 
        targetFolderInput$.val (option$.attr ('data-id'));
        var folderName = option$.find ('.folder-link').text ();
        if (option$.attr ('data-is-parent') === '1')
            folderName = option$.find ('.file-system-object-name').attr ('title');
        targetFolderName$.text (folderName);
    }

    function selectCurrentFolder () {
        targetFolderInput$.val (x2.folderSelector.currentFolderId);
        targetFolderName$.text (x2.folderSelector.currentFolderName);
    }

    folderSelectorListView$.find ('.folder-selector-option-container').
        click (function () {
            if ($(this).hasClass ('disabled-folder-option')) return false;
            if ($(this).hasClass ('selected')) {
                $(this).removeClass ('selected'); 
                selectCurrentFolder ();
            } else {
                folderSelectorListView$.find ('.selected').removeClass ('selected'); 
                selectFolder ($(this));
            }
        });
    folderSelectorListView$.find ('.folder-link, .fa').click (function () {
        var option$ = $(this).closest ('.folder-selector-option-container'); 
        if (option$.hasClass ('disabled-folder-option')) return false;
        $.fn.yiiListView.update (folderSelectorListView$.attr ('id'),{
            url:yii.scriptUrl + '/docs/getFolderSelector',
            data:{
                selectedFolders: 
                    x2.foldercontentsMassActionsManager.massActionObjects.
                        MassMoveFileSysObjToFolder.getSelectedFolders (),
                id: $(this).closest ('.folder-selector-option-container').attr('data-id')
            },
            complete:function(){
                selectFolder (option$);
                //init ();
            }
        }); 
        return false;
    });
}) ();
", CClientScript::POS_END);

$widget = $this->widget ('X2ListView', array (
    'dataProvider' => $dataProvider,
    'itemView' => '_folderSelectorItemView',
    'id' => 'folder-selector-list-view',
    'viewData' => array (
        'selectedFolders' => array_flip ($selectedFolders),
    ),
));

?>
