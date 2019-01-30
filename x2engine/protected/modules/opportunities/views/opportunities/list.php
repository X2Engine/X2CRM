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




$heading = $listModel->name; //Yii::t('contacts','All Contacts');

$authParams['X2Model'] = $listModel;

$opportunityModule = Modules::model()->findByAttributes(array('name'=>'opportunities'));
$accountModule = Modules::model()->findByAttributes(array('name'=>'accounts'));

//these hidden field is here to stop google auto fill from filling in the grid
$ConFields = X2Model::model("Opportunity")->getFields();
foreach($ConFields as $field){
    echo '<input type="hidden" id="Opportunity[' . $field->fieldName . ']" name="Opportunity[' . $field->fieldName . ']">';      
}

$menuOptions = array(
    'all', 'lists', 'create', 'createList', 'viewList', 'editList', 'deleteList',
);
if ($opportunityModule->visible && $accountModule->visible)
    $menuOptions[] = 'quick';
$this->insertMenu($menuOptions, $listModel, $authParams);


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

?>

<div class="search-form" style="display:none">
<?php /* $this->renderPartial('_search',array(
    'model'=>$model,
        'users'=>User::getNames(),
)); */ ?>
</div><!-- search-form -->
<?php

$massActions = array(
    'MassTag', 'MassTagRemove', 'MassUpdateFields', 'MassAddToList', 
    'NewListFromSelection', 'MassExecuteMacro'
);

if ($listModel->type === 'static') {
    $massActions[] = 'MassRemoveFromList';
}

$this->widget('X2GridView', array(
    'id'=>'contacts-grid',
    'enableQtips' => true,
    'qtipManager' => array (
        'X2GridViewQtipManager',
        'loadingText'=> addslashes(Yii::t('app','loading...')),
        'qtipSelector' => ".contact-name"
    ),
    'title'=>$heading,
    'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize'),
    'template'=> 
        '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">'.
        '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">'.
        '<div id="x2-gridview-page-title" '.
         'class="page-title x2-gridview-fixed-title">{title}{buttons}{massActionButtons}'
            .(Yii::app()->user->checkAccess('AdminExportModels',array('module'=>'contacts')) ? 
                CHtml::link(
                    Yii::t('app','Export'),
                    array('/admin/exportModels','model'=>'Opportunities', 'listId'=>$listModel->id),
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
    'massActions'=>$massActions,
    'enableControls'=>true,
    'enableTags'=>true,
    'fullscreen'=>true,
    'enableSelectAllOnAllPages' => false,
));
