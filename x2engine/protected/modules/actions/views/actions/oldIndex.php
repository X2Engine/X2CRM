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

$profile = ProfileChild::model()->findByPk(Yii::app()->user->id);
$this->showActions = $profile->showActions;
if(!$this->showActions) // if user hasn't saved a type of action to show, show uncomple actions by default
    $this->showActions = 'uncomplete';

if($this->showActions == 'uncomplete')
	$model->complete = 'No';
else if ($this->showActions == 'complete')
	$model->complete = 'Yes';
else
	$model->complete = '';



$menuItems = array(
	array('label'=>Yii::t('actions','Today\'s Actions'),'url'=>array('index')),
	array('label'=>Yii::t('actions','All My Actions'),'url'=>array('viewAll')),
	array('label'=>Yii::t('actions','Everyone\'s Actions'),'url'=>array('viewGroup')),
	array('label'=>Yii::t('actions','Create'),'url'=>array('create')),
);

if($this->route=='/actions/actions/index') {
	$heading = Yii::t('actions','Today\'s Actions');
	$dataProvider=$model->searchIndex();
	$dataProvider2=$model->searchComplete();

	unset($menuItems[0]['url']);

} elseif($this->route=='/actions/actions/viewAll') {
	$heading = Yii::t('actions','All My Actions');
	$dataProvider=$model->searchAll();
	$dataProvider2=$model->searchComplete();

	unset($menuItems[1]['url']);
} else {
	$heading = Yii::t('actions','Everyone\'s Actions');
	$dataProvider=$model->searchAllGroup();
	$dataProvider2=$model->searchAllComplete();

	unset($menuItems[2]['url']);
}

$this->actionMenu = $this->formatMenu($menuItems);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('contacts-grid', {
		data: $(this).serialize()
	});
	return false;
});
");

// functions for completeing/uncompleting multiple selected actions
Yii::app()->clientScript->registerScript('completeUncompleteSelected', "
function completeSelected() {
	var checked = $.fn.yiiGridView.getChecked('actions-grid', 'C_gvCheckbox');
	$.post('completeSelected', {'actionIds': checked}, function() {jQuery.fn.yiiGridView.update('actions-grid')});
}
function uncompleteSelected() {
	var checked = $.fn.yiiGridView.getChecked('actions-grid', 'C_gvCheckbox');
	$.post('uncompleteSelected', {'actionIds': checked}, function() {jQuery.fn.yiiGridView.update('actions-grid')});
}

function toggleShowActions() {
	var show = $('#dropdown-show-actions').val(); // value of dropdown (which actions to show)
	$.post(".json_encode(Yii::app()->controller->createUrl('/actions/actions/saveShowActions')).", {ShowActions: show}, function() {
		$.fn.yiiGridView.update('actions-grid', {data: $.param($('#actions-grid input[name=\"Actions[complete]\"]'))});
	});
}
",CClientScript::POS_HEAD);

// init qtip for contact names
Yii::app()->clientScript->registerScript('contact-qtip', '
function refreshQtip() {
	$(".contact-name").each(function (i) {
		var contactId = $(this).attr("href").match(/\\d+$/);

		if(contactId !== null && contactId.length) {
			$(this).qtip({
				content: {
					text: "'.addslashes(Yii::t('app','Loading...')).'",
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


function trimText($text) {
	if(mb_strlen($text,'UTF-8')>150)
		return mb_substr($text,0,147,'UTF-8').'...';
	else
		return $text;
}

?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->
<?php
$this->widget('application.components.X2GridView', array(
	'id'=>'actions-grid',
    'title'=>$heading,
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
    'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize'),
	'template'=> '<div class="page-title icon actions">{title}{buttons}'
		.CHtml::link(Yii::t('actions','Switch to List'),array('index','toggleView'=>1),array('class'=>'x2-button')).
        '{filterHint}{summary}</div>{items}{pager}',
	'dataProvider'=>$dataProvider,
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$model,
	// 'columns'=>$columns,
	'modelName'=>'Actions',
	'viewName'=>'actions',
	// 'columnSelectorId'=>'contacts-column-selector',
	'defaultGvSettings'=>array(
		'gvCheckbox' => 30,
		'actionDescription' => 140,
		'associationName' => 165,
		'assignedTo' => 105,
		'completedBy' => 86,
		'createDate' => 79,
		'dueDate' => 77,
		'lastUpdated' => 79,
	),
	'specialColumns'=>array(
		'actionDescription'=>array(
            'header'=>Yii::t('actions','Action Description'),
			'name'=>'actionDescription',
			'value'=>'CHtml::link(($data->type=="attachment")? Media::attachmentActionText($data->actionDescription) : CHtml::encode(trimText($data->actionDescription)),array("view","id"=>$data->id))',
			'type'=>'raw',
		),
		'associationName'=>array(
			'name'=>'associationName',
			'header'=>Yii::t('actions','Association Name'),
			'value'=>'strcasecmp($data->associationName,"None")==0 ? Yii::t("app","None") : CHtml::link($data->associationName,array("/".$data->associationType."/".$data->associationId),array("class"=>($data->associationType=="contacts"? "contact-name" : null)))',
			'type'=>'raw',
		),
	),
	'enableControls'=>true,
	'fullscreen'=>true,
));
echo CHtml::button(Yii::t('actions','Complete Selected'),array('class'=>'x2-button','style'=>'display:inline-block;','onclick'=>'completeSelected()'));
echo CHtml::button(Yii::t('actions','Uncomplete Selected'),array('class'=>'x2-button','style'=>'display:inline-block;','onclick'=>'uncompleteSelected()'));
