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




$menuOptions = array(
    'index', 'create', 'createEmail', 'createQuote', 'import', 'export',
);
$this->insertMenu($menuOptions);

Yii::app()->clientScript->registerCssFile(
    Yii::app()->controller->module->assetsUrl.'/css/index.css');
Yii::app()->clientScript->registerScriptFile(Yii::app()->controller->module->assetsUrl.'/js/FolderManager.js');


Yii::app()->clientScript->registerScript('docsIndexJS',"
x2.folderManager = new x2.FolderManager (".CJSON::encode (array (
    'translations' => array (    
        'createFolder' => Yii::t('docs', 'Create Folder'),
        'deleteFolderConf' => 
            Yii::t('docs', 'Are you sure you want to delete this folder and all of its contents?'),
        'deleteDocConf' => Yii::t('docs','Are you sure you want to delete this Doc?'),
        'folderDeleted' => Yii::t('docs', 'Folder deleted.'),
        'docDeleted' => Yii::t('docs', 'Doc deleted.'),
        'permissionsMissing' => 
            Yii::t('docs', 'You do not have permission to delete that Doc or folder.'),
    ),
    'urls' => array (
        'moveFolder' => Yii::app()->controller->createUrl('/docs/moveFolder'),
        'index' => Yii::app()->controller->createUrl('/docs/index'),
        'deleteFileFolder' => Yii::app()->controller->createUrl('/docs/deleteFileFolder'),
    ),
)).");
", CClientScript::POS_END);

?>
<div>
<?php
$folderViewHeader = FileSystemObject::getListViewHeader();

$columns = array (
    array (
        'name' => 'gvCheckbox',
        'width' => '30px',
        'header' => '',
        'disabled' => '!$data->objId || $data->objId === -1',
        'value' => '$data->objId ? $data->objId : -2',
    ),
    array (
        'name' => 'name',
        'header' => Yii::t('docs', 'Name'),
        'type' => 'raw',
        'value' => '$data->renderName ()',
        'width' => '30%',
        'htmlOptions' => array (
            'id' => 'php:$data->id . "-file-system-object"',
            'data-type' => 'php:$data->type',
            'data-id' => 'php:$data->objId',
            'class' => 'php:"view file-system-object".'.
                "(\$data->type=='folder'?' file-system-object-folder':' file-system-object-doc')",
        )

    ),
    array (
        'name' => 'owner',
        'header' => Yii::t('docs', 'Owner'),
        'type' => 'raw',
        'value' => '$data->getOwner ();',
        'width' => '30%',
        'htmlOptions' => array (
            'class' => 'file-system-object-owner',
        ),
    ),
    array (
        'name' => 'lastUpdated',
        'header' => Yii::t('docs', 'Last Updated'),
        'type' => 'raw',
        'value' => '$data->getLastUpdateInfo ();',
        'width' => '25%',
        'htmlOptions' => array (
            'class' => 'file-system-object-last-updated',
        ),
    ),
    array (
        'name' => 'visibility',
        'header' => Yii::t('docs', 'Visibility'),
        'type' => 'raw',
        'value' => '$data->getVisibility ();',
        'width' => '10%',
        'htmlOptions' => array (
            'class' => 'file-system-object-visibility',
        ),
    ),
);

$listView = $this->widget('X2GridViewGeneric', array(
    'dataProvider' => $folderDataProvider,
    //'itemView' => '_viewFileSystemObject',
    'id' => 'folder-contents',
    //'htmlOptions' => array('class'=>'x2-list-view list-view'),
    'baseScriptUrl' => Yii::app()->request->baseUrl . '/themes/' . Yii::app()->theme->name . 
        '/css/listview',
    'columns' => $columns,
    'template' => '<div class="page-title rounded-top icon docs"><h2>'.Yii::t('docs','Docs').
        ' </h2>{massActionButtons}{summary}'
        .  X2Html::tag(
            'span', 
            array(
                'id'=>'create-folder-button',
                'class' => 'x2-button fa-stack',
                'style'=>'float:right;margin-top:5px;'),
            X2Html::fa(
                'folder fa-stack-2x',
                array('style' => 'margin-top:1px;')) . 
            X2Html::fa(
                'plus-circle fa-stack-1x fa-inverse', 
                array('style' => 'margin-top:3px;margin-left:5px;')))
        . '</div>{items}{pager}',
    'afterGridViewUpdateJSString' => 'x2.folderManager.setUpDragAndDrop ();',
    'massActions' => array ('MassMoveFileSysObjToFolder', 'MassRenameFileSysObj'),
    'dataColumnClass' => 'X2DataColumnGeneric',
    'rowHtmlOptionsExpression' => 'array (
        "class" => ($data->validDraggable() ? " draggable-file-system-object" : "").
                   ($data->validDroppable() ? " droppable-file-system-object" : ""),
    )',
    'enableColDragging' => false,
    'enableGridResizing' => false,
    'rememberColumnSort' => false,
));

?>
</div>
<div id="file-delete" style="text-align:center;display:none;"> 
    <?php 
    echo X2Html::fa(
        'trash fa-3x fa-border', 
        array('id' => 'delete-drop', 'style' => 'color:red;margin:auto;margin-top:20px;')); ?>
</div>
<br />
<div class='flush-grid-view'>
<?php
$this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'attachments-grid',
    'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.
        '/css/gridview',
    'template'=> '<div class="page-title rounded-top icon docs"><h2>'.
        Yii::t('docs','Uploaded {module}', array('{module}'=>Modules::displayName())).
        '</h2>{summary}</div>{items}{pager}',
    'dataProvider'=>$attachments,
    'columns'=>array(
        array(
            'name'=>'fileName',
            'value'=>'$data->getMediaLink()',
            'type'=>'raw',
            'htmlOptions'=>array('width'=>'30%'),
        ),
        array(
            'name'=>'uploadedBy',
            'value'=>'User::getUserLinks($data->uploadedBy)',
            'type'=>'raw',
        ),
        array(
            'name'=>'createDate',
            'type'=>'raw',
            'value'=>'Yii::app()->dateFormatter->format(
                Yii::app()->locale->getDateFormat("medium"), $data->createDate)',
        ),
    ),
));
?>
</div>
<br>
<?php
$this->widget ('FileUploader',array(
    'id' => 'attachment',
    'mediaParams' => array (
        'associationType'=>'docs',
        'associationId'=>null
    ),
    'events' => array(
        'success' => '$.fn.yiiGridView.update("attachments-grid")',
    ))
); 

echo '<div class="form" id="folder-form" style="display:none;" 
    title="'.Yii::t('docs','Create Folder').'">';
$this->renderPartial ('_folderCreate', array (
    'model' => $model
));
echo '</div>';

?>
<br>
