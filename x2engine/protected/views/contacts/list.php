<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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
// if($this->route=='contacts/viewAll') {
	$heading = $listName; //Yii::t('contacts','All Contacts');
	
	// $dataProvider = $model->searchAll();
	
	$this->menu=array(
	array('label'=>Yii::t('contacts','All Contacts'),'url'=>array('index')),
	array('label'=>Yii::t('contacts','Lists'),'url'=>array('lists')),
	array('label'=>Yii::t('contacts','Create'),'url'=>array('create')),
	array('label'=>Yii::t('contacts','Import from Outlook'),'url'=>array('importContacts')),
	array('label'=>Yii::t('contacts','Import from Template'),'url'=>array('importExcel')),
	array('label'=>Yii::t('contacts','Export to CSV'),'url'=>array('export')),
	);
// } else {
	// $heading = Yii::t('contacts','My Contacts'); 
	// $dataProvider = $model->search();
	
	// $this->menu=array(
		// array('label'=>Yii::t('contacts','My Contacts')),
		// array('label'=>Yii::t('contacts','All Contacts'),'url'=>array('viewAll')),
		// array('label'=>Yii::t('contacts','Create Contact'),'url'=>array('create')),
		// array('label'=>Yii::t('contacts','Create Lead'),'url'=>array('actions/quickCreate')),
		// array('label'=>Yii::t('contacts','Import Contacts from Outlook'),'url'=>array('importContacts')),
		// array('label'=>Yii::t('contacts','Import Contacts from Template'),'url'=>array('importExcel')),
		// array('label'=>Yii::t('contacts','Export Contacts'),'url'=>array('export')),
	// );
// }
// $this->menu=array(
	// array('label'=>Yii::t('contacts','Contact List')),
	// array('label'=>Yii::t('contacts','Create Contact'),'url'=>array('create')),
	// array('label'=>Yii::t('contacts','Create Lead'),'url'=>array('actions/quickCreate')),
	// array('label'=>Yii::t('contacts','Import Contacts from Outlook'),'url'=>array('importContacts')),
	// array('label'=>Yii::t('contacts','Import Contacts from Template'),'url'=>array('importExcel')),
	// array('label'=>Yii::t('contacts','Export Contacts'),'url'=>array('export')),
// );

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
	'template'=> '<h2>'.$heading.'</h2><div class="title-bar">'
		.CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
		.CHtml::link(Yii::t('app','Clear Filters'),array(Yii::app()->controller->action->id,'clearFilters'=>1)) . ' | '
		.CHtml::link(Yii::t('app','Columns'),'javascript:void(0);',array('class'=>'column-selector-link'))
		.'{summary}</div>{items}{pager}',
	'dataProvider'=>$dataProvider,
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$model,
	// 'columns'=>$columns,
	'modelName'=>'Contacts',
	'viewName'=>'contacts',
	// 'columnSelectorId'=>'contacts-column-selector',
	'defaultGvSettings'=>array(
		'name'=>185,
		'phone'=>95,
		'lastUpdated'=>106,
		'leadSource'=>133,
		'gvControls'=>66,
	),
	'specialColumns'=>array(
		'name'=>array(
			'name'=>'lastName',
			'header'=>Yii::t('contacts','Name'),
			'value'=>'CHtml::link($data->firstName." ".$data->lastName,array("view","id"=>$data->id))',
			'type'=>'raw',
		),
	),
	'enableControls'=>true,
	'enableTags'=>true,
));

/* 
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'contacts-grid',
	'baseScriptUrl'=>Yii::app()->theme->getBaseUrl().'/css/gridview',
	'template'=> '<h2>'.$heading.'</h2><div class="title-bar">'
		// .CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
		// .CHtml::link(Yii::t('app','Clear Filters'),array('list','id'=>$listId,'clearFilters'=>1))
		.'{summary}</div>{items}{pager}',
	'dataProvider'=>$dataProvider, //CActiveRecord::model('Contacts')->searchList($listId),
	'filter'=>$model,
	'columns'=>array(
		//'id',
		array(
			'name'=>'lastName',
			'header'=>Yii::t('contacts','Name'),
			'value'=>'CHtml::link($data["firstName"]." ".$data["lastName"],array("view","id"=>$data["id"]))',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'30%')
		),
		array(
			'name'=>'phone',
			'header'=>Yii::t('contacts','Work Phone'),
		),
		array(
			'name'=>'createDate',
			'header'=>Yii::t('contacts','Create Date'),
			'value'=>'date("Y-m-d",$data["createDate"])',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'15%')
		),
                array(
			'name'=>'lastUpdated',
			'header'=>Yii::t('contacts','Last Updated'),
			'value'=>'date("Y-m-d",$data["lastUpdated"])',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'15%')
		),
		array(
			'name'=>'leadSource',
			'header'=>Yii::t('contacts','Lead Source'),
		),
		
	),
)); */
?>
