<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
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
	$menuItems[] = 	array('label'=>Yii::t('app', 'Quick Create'), 'url'=>array('/site/createRecords', 'ret'=>'contacts'), 'linkOptions'=>array('id'=>'x2-create-multiple-records-button', 'class'=>'x2-hint', 'title'=>Yii::t('app', 'Create a Contact, Account, and Opportunity.')));

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
				url:'".$this->createUrl('/contacts/createListFromSelection')."',
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
			url:'".$this->createUrl('/contacts/addToList')."',
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
				url:'".$this->createUrl('/contacts/removeFromList')."',
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

		if(typeof contactId != null && contactId.length) {
			$(this).qtip({
				content: {
					text: "'.addslashes(Yii::t('app','loading...')).'",
					ajax: {
						url: yii.baseUrl+"/index.php/contacts/qtip",
						data: { id: contactId[0] },
						method: "get",
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
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<div class="page-title"><h2>'.$heading.'</h2><div class="title-bar">'
		// .CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
		.CHtml::link(Yii::t('app','Clear Filters'),array('list','id'=>$listModel->id,'clearFilters'=>1)) . ' | '
		.CHtml::link(Yii::t('app','Export'),array('/contacts/exportList/'.$listModel->id)) . ' | '
		.CHtml::link(Yii::t('app','Columns'),'javascript:void(0);',array('class'=>'column-selector-link')) . ' | '
		.CHtml::link(Yii::t('marketing','Email List'), Yii::app()->createUrl('/marketing/create?Campaign[listId]='.$listModel->id)) . ' | '
		.X2GridView::getFilterHint()
		.'{summary}</div></div>{items}{pager}',
	'dataProvider'=>$dataProvider,
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$model,
	// 'columns'=>$columns,
	'modelName'=>'Contacts',
	'viewName'=>'contacts_list'.$listModel->id,
	// 'columnSelectorId'=>'contacts-column-selector',
	'defaultGvSettings'=>array(
		'gvCheckbox'=>35,
		'name'=>180,
		'phone'=>101,
		'lastUpdated'=>94,
		'leadSource'=>101,
		'gvControls'=>74
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
	'enableControls'=>true,
	'enableTags'=>true,
));
?>
<span class="list-actions">
<?php
echo CHtml::link(Yii::t('app','New List From Selection'),'#',array('id'=>'createList','class'=>'list-action'));

$listNames = array();
foreach(X2List::model()->findAllByAttributes(array('type'=>'static')) as $list) {	// get all static lists
	if($this->checkPermissions($list,'edit'))	// check permissions
		$listNames[$list->id] = $list->name;
}
unset($listNames[$listModel->id]);	// remove current list from the list...yo dawg, I heard you like lists
$editPermissions=Yii::app()->user->checkAccess('ContactsUpdateList',$authParams);
if($editPermissions && $listModel->type == 'static')
	echo ' | '.CHtml::link(Yii::t('contacts','Remove From List'),'#',array('id'=>'removeFromList','class'=>'list-action'));

if(!empty($listNames)) {
	echo ' | '.CHtml::link(Yii::t('app','Add to list:'),'#',array('id'=>'addToList','class'=>'list-action'));
	echo CHtml::dropDownList('addToListTarget',null,$listNames, array());
}
?>
</span>
