<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

$heading = $listModel->name; //Yii::t('contacts','All Contacts');

$authParams['assignedTo'] = $listModel->assignedTo;
$menuItems = array(
    array('label'=>Yii::t('contacts','All Contacts'),'url'=>array('index')),
    array('label'=>Yii::t('contacts','Lists'),'url'=>array('lists')),
    array('label'=>Yii::t('contacts','Create Contact'),'url'=>array('create')),
    array('label'=>Yii::t('contacts','Create List'),'url'=>array('createList')),
    array('label'=>Yii::t('contacts','View List')),
    array('label'=>Yii::t('contacts','Edit List'),'url'=>array('updateList','id'=>$listModel->id)),
    array('label'=>Yii::t('contacts','Delete List'),'url'=>'#', 'linkOptions'=>array('submit'=>array('deleteList','id'=>$listModel->id),'confirm'=>'Are you sure you want to delete this item?')),
);

$opportunityModule = Modules::model()->findByAttributes(array('name'=>'opportunities'));
$accountModule = Modules::model()->findByAttributes(array('name'=>'accounts'));

if($opportunityModule->visible && $accountModule->visible)
    $menuItems[] =     array('label'=>Yii::t('app', 'Quick Create'), 'url'=>array('/site/createRecords', 'ret'=>'contacts'), 'linkOptions'=>array('id'=>'x2-create-multiple-records-button', 'class'=>'x2-hint', 'title'=>Yii::t('app', 'Create a Contact, Account, and Opportunity.')));

$this->actionMenu = $this->formatMenu($menuItems, $authParams);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').unbind('click').click(function(){
    $('.search-form').toggle();
    return false;
});
$('.search-form form').submit(function(){
    $.fn.yiiGridView.update('contacts-grid', {
        data: $(this).serialize()
    });
    return false;
});

$('#content').on('mouseup','#contacts-grid a',function(e) {
    document.cookie = 'vcr-list=".$listModel->id."; expires=0; path=/';
});

$('#createList').unbind('click').click(function() {
    var selectedItems = $.fn.yiiGridView.getChecked('contacts-grid','C_gvCheckbox');
    if(selectedItems.length > 0) {
        var listName = prompt('".addslashes(Yii::t('app','What should the list be named?'))."','');

        if(listName != '' && listName != null) {
            $.ajax({
                url:'".$this->createUrl('/contacts/contacts/createListFromSelection')."',
                type:'post',
                data:{listName:listName,modelName:'Contacts',gvSelection:selectedItems},
                success:function(response) { if(response != '') window.location.href=response; }
            });
        }
    }
    return false;
});
$('#addToList').unbind('click').click(function() {
    var selectedItems = $.fn.yiiGridView.getChecked('contacts-grid','C_gvCheckbox');

    var targetList = $('#addToListTarget').val();

    if(selectedItems.length > 0) {
        $.ajax({
            url:'".$this->createUrl('/contacts/contacts/addToList')."',
            type:'post',
            data:{listId:targetList,gvSelection:selectedItems},
            success:function(response) { if(response=='success') alert('".addslashes(Yii::t('app','Added items to list.'))."'); else alert(response); }
        });
    }
    return false;
});
$('#removeFromList').unbind('click').click(function() {
    var selectedItems = $.fn.yiiGridView.getChecked('contacts-grid','C_gvCheckbox');
    if(selectedItems.length > 0) {
        var confirmRemove = confirm('".addslashes(Yii::t('app','Are you sure you want to remove these items from the list?'))."');

        if(confirmRemove) {
            $.ajax({
                url:'".$this->createUrl('/contacts/contacts/removeFromList')."',
                type:'post',
                data:{listId:".$listModel->id.",gvSelection:selectedItems},
                success:function(response) { if(response=='success') $.fn.yiiGridView.update('contacts-grid'); else alert(response); }
            });
        }
    }
    return false;
});
");

// init qtip for contact names
Yii::app()->clientScript->registerScript('contact-qtip', '
function refreshQtip() {
    $(".contact-name").each(function (i) {
        var contactId = $(this).attr("href").match(/\\d+$/);

        if(contactId !== null && contactId.length) {
            $(this).qtip({
                content: {
                    text: "'.addslashes(Yii::t('app','loading...')).'",
                    ajax: {
                        url: yii.baseUrl+"/index.php/contacts/qtip",
                        data: { id: contactId[0] },
                        method: "get"
                    }
                },
                style: {
                }
            });
        }
    });
}

$(function() {
    refreshQtip();
});
');
?>

<div class="search-form" style="display:none">
<?php /* $this->renderPartial('_search',array(
    'model'=>$model,
        'users'=>UserChild::getNames(),
)); */ ?>
</div><!-- search-form -->
<?php

$this->widget('application.components.X2GridView', array(
    'id'=>'contacts-grid',
    'title'=>$heading,
    'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize'),
    'template'=> 
        '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">'.
        '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">'.
        '<div id="x2-gridview-page-title" '.
         'class="page-title x2-gridview-fixed-title">{title}{buttons}{massActionButtons}'
            .(Yii::app()->user->checkAccess('ContactsExportContacts') ? 
                CHtml::link(
                    Yii::t('app','Export'),
                    array('/contacts/contacts/exportContacts','listId'=>$listModel->id),
                    array('class'=>'x2-button')
                ) : null)
            .CHtml::link(
                Yii::t('marketing','Email List'), 
                Yii::app()->createUrl('/marketing/marketing/create',array('Campaign[listId]'=>$listModel->id)),
                array('class'=>'x2-button')
            )
        .'{filterHint}{summary}{items}{pager}',
    'fixedHeader'=>true,
    'dataProvider'=>$dataProvider,
    // 'enableSorting'=>false,
    // 'model'=>$model,
    'filter'=>$model,
    'pager'=>array('class'=>'CLinkPager','maxButtonCount'=>10),
    // 'columns'=>$columns,
    'modelName'=>'Contacts',
    'viewName'=>'contacts_list'.$listModel->id,
    // 'columnSelectorId'=>'contacts-column-selector',
    'defaultGvSettings'=>array(
        'gvCheckbox' => 30,
        'name' => 125,
        'email' => 165,
        'leadSource' => 83,
        'leadstatus' => 91,
        'phone' => 107,
        'lastActivity' => 78,
        'gvControls' => 73,
    ),
    'selectableRows'=>2,
    'specialColumns'=>array(
        'name'=>array(
            'name'=>'name',
            'header'=>Yii::t('contacts','Name'),
            'value'=>'CHtml::link($data->name,array("view","id"=>$data->id), array("class" => "contact-name"))',
            'type'=>'raw',
        ),
    ),
    'massActions'=>array(
        /* x2prostart */'delete', 'tag', 'updateField', /* x2proend */'addToList', 'newList',
        'removeFromList'
    ),
    'enableControls'=>true,
    'enableTags'=>true,
    'fullscreen'=>true,
));
