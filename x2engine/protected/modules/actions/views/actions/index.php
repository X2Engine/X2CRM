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

if($this->route=='actions/actions/index') {
	$heading = Yii::t('actions','Today\'s Actions');
	$dataProvider=$model->search();
	$dataProvider2=$model->searchComplete();
	
	unset($menuItems[0]['url']);

} elseif($this->route=='actions/actions/viewAll') {
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
	$.post('saveShowActions', {ShowActions: show}, function() {
		$.fn.yiiGridView.update('actions-grid', {data: $.param($('#actions-grid input[name=\"Actions[complete]\"]'))});
	});
}
",CClientScript::POS_HEAD);

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


function trimText($text) {
	if(strlen($text)>150)
		return substr($text,0,147).'...';
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
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<div class="page-title"><h2>'.$heading.'</h2><div class="title-bar">'
		.CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
		.CHtml::link(Yii::t('app','Clear Filters'),array(Yii::app()->controller->action->id,'clearFilters'=>1)) . ' | '
		.CHtml::link(Yii::t('app','Columns'),'javascript:void(0);',array('class'=>'column-selector-link')) . ' | '
		.X2GridView::getFilterHint()
		.'{summary}</div></div>'
		.CHtml::button(Yii::t('actions','Complete Selected'),array('class'=>'x2-button','style'=>'display:inline-block;','onclick'=>'completeSelected()'))
		.CHtml::button(Yii::t('actions','Uncomplete Selected'),array('class'=>'x2-button','style'=>'display:inline-block;','onclick'=>'uncompleteSelected()'))
		.'{items}{pager}',
	'dataProvider'=>$dataProvider,
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$model,
	// 'columns'=>$columns,
	'modelName'=>'Actions',
	'viewName'=>'actions',
	// 'columnSelectorId'=>'contacts-column-selector',
	'defaultGvSettings'=>array(
		'gvCheckbox'=>28,
		'actionDescription'=>257,
		'associationName'=>132,
		'dueDate'=>91,
		'assignedTo'=>105,
	),
	'specialColumns'=>array(
		'actionDescription'=>array(
			'name'=>'actionDescription',
			'value'=>'CHtml::link(($data->type=="attachment")? MediaChild::attachmentActionText($data->actionDescription) : CHtml::encode(trimText($data->actionDescription)),array("view","id"=>$data->id))',
			'type'=>'raw',
		),
		'associationName'=>array(
			'name'=>'associationName',
			'header'=>Yii::t('actions','Association Name'),
			'value'=>'$data->associationName=="None" ? Yii::t("app","None") : CHtml::link($data->associationName,array("/".$data->associationType."/".$data->associationId),array("class"=>($data->associationType=="contacts"? "contact-name" : null)))',
			'type'=>'raw',
		),
	),
	'enableControls'=>true,
));
