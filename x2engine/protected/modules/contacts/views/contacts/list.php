<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

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
$listActions = '<div class="list-actions">'.CHtml::link(Yii::t('app','New List From Selection'),'#',array('id'=>'createList','class'=>'list-action'));

$listNames = array();
foreach(X2List::model()->findAllByAttributes(array('type'=>'static')) as $list) {	// get all static lists
	if($this->checkPermissions($list,'edit'))	// check permissions
		$listNames[$list->id] = $list->name;
}
unset($listNames[$listModel->id]);	// remove current list from the list...yo dawg, I heard you like lists
$editPermissions=Yii::app()->user->checkAccess('ContactsUpdateList',$authParams);
if($editPermissions && $listModel->type == 'static')
	$listActions .= ' | '.CHtml::link(Yii::t('contacts','Remove From List'),'#',array('id'=>'removeFromList','class'=>'list-action'));

if(!empty($listNames)) {
	$listActions .= ' | '.CHtml::link(Yii::t('app','Add to list:'),'#',array('id'=>'addToList','class'=>'list-action'));
	$listActions .= CHtml::dropDownList('addToListTarget',null,$listNames, array());
}
$listActions .= '</div>';

$this->widget('application.components.X2GridView', array(
	'id'=>'contacts-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<div class="page-title icon contacts"><h2>'.$heading.'</h2><div class="title-bar">'
		// .CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
		.CHtml::link(Yii::t('app','Clear Filters'),array('list','id'=>$listModel->id,'clearFilters'=>1)) . ' | '
		.CHtml::link(Yii::t('app','Export'),array('/contacts/exportContacts?listId='.$listModel->id)) . ' | '
		.CHtml::link(Yii::t('app','Columns'),'javascript:void(0);',array('class'=>'column-selector-link')) . ' | '
		.CHtml::link(Yii::t('marketing','Email List'), Yii::app()->createUrl('/marketing/create?Campaign[listId]='.$listModel->id)) . ' | '
		.X2GridView::getFilterHint()
		.'{summary}</div></div>{items}{pager}',
	'dataProvider'=>$dataProvider,
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$model,
	'pager'=>array('class'=>'CLinkPager','header'=>$listActions),
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
