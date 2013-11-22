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

$menuItems = array(
	array('label'=>Yii::t('contacts','All Contacts'),'url'=>array('index')),
	array('label'=>Yii::t('contacts','Lists'),'url'=>array('lists')),
	array('label'=>Yii::t('contacts','Create Contact'),'url'=>array('create')),
	array('label'=>Yii::t('contacts','Create List'),'url'=>array('createList')),
	array('label'=>Yii::t('contacts','View List')),
    array('label'=>Yii::t('contacts','Import Contacts'),'url'=>array('importExcel')),
	array('label'=>Yii::t('contacts','Export to CSV'),'url'=>array('exportContacts')),
    array('label'=>Yii::t('contacts','Contact Map'),'url'=>array('googleMaps')),
    array('label'=>Yii::t('contacts','Saved Maps'),'url'=>array('savedMaps')),
    //array('label'=>Yii::t('contacts','Saved Searches'),'url'=>array('savedSearches'))
);

$heading = '';

if($this->route=='contacts/contacts/index') {
	$heading = Yii::t('contacts','All Contacts');
	$dataProvider = $model->searchAll();
	unset($menuItems[0]['url']);
	unset($menuItems[3]);
	unset($menuItems[4]);
} elseif($this->route=='contacts/contacts/myContacts') {
	$heading = Yii::t('contacts','My Contacts');
	$dataProvider = $model->searchMyContacts();
} elseif($this->route=='contacts/contacts/newContacts') {
	$heading = Yii::t('contacts','Today\'s Contacts');
	$dataProvider = $model->searchNewContacts();
}

$opportunityModule = Modules::model()->findByAttributes(array('name'=>'opportunities'));
$accountModule = Modules::model()->findByAttributes(array('name'=>'accounts'));

if($opportunityModule->visible && $accountModule->visible)
	$menuItems[] = 	array('label'=>Yii::t('app', 'Quick Create'), 'url'=>array('/site/createRecords', 'ret'=>'contacts'), 'linkOptions'=>array('id'=>'x2-create-multiple-records-button', 'class'=>'x2-hint', 'title'=>Yii::t('app', 'Create a Contact, Account, and Opportunity.')));

$this->actionMenu = $this->formatMenu($menuItems);

Yii::app()->clientScript->registerScript('search', "
/*$('.search-button').unbind('click').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('contacts-grid', {
		data: $(this).serialize()
	});
	return false;
});*/

$('#content').on('mouseup','#contacts-grid a',function(e) {
	document.cookie = 'vcr-list=".$this->getAction()->getId()."; expires=0; path=/';
});
",CClientScript::POS_READY);

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
						url: yii.scriptUrl+"/contacts/qtip",
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
<?php $this->renderPartial('_search',array(
	'model'=>$model,
	'users'=>User::getNames(),
)); ?>
</div><!-- search-form -->
<form>
<?php

$this->widget('application.components.X2GridView', array(
	'id'=>'contacts-grid',
	'title'=>$heading,
	'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize'),
	'template'=> 
        '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">'.
        '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">'.
        '<div id="x2-gridview-page-title" '.
         'class="page-title icon contacts x2-gridview-fixed-title">'.
        '{title}{buttons}{filterHint}{massActionButtons}{summary}{topPager}{items}{pager}',
    'fixedHeader'=>true,
	'dataProvider'=>$dataProvider,
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$model,
	'pager'=>array('class'=>'CLinkPager','maxButtonCount'=>10),
	// 'columns'=>$columns,
	'modelName'=>'Contacts',
	'viewName'=>'contacts',
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
	'specialColumns'=>array(
		'name'=>array(
			'name'=>'name',
			'header'=>Yii::t('contacts','Name'),
			'value'=>'$data->link',
			'type'=>'raw',
		),
	),
    'massActions'=>array(
        /* x2prostart */'delete', 'tag', 'updateField', /* x2proend */'addToList', 'newList'
    ),
	'enableControls'=>true,
	'enableTags'=>true,
	'fullscreen'=>true,
));
?>

</form>
