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

$heading = Yii::t('contacts','Contacts Lists'); 
// $dataProvider = $model->searchAll();

$this->menu=array(
	array('label'=>Yii::t('contacts','All Contacts'),'url'=>array('index')),
	array('label'=>Yii::t('contacts','Lists')),
	array('label'=>Yii::t('contacts','Create List'),'url'=>array('createList')),
	array('label'=>Yii::t('contacts','Create Contact'),'url'=>array('create')),
);

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
        'users'=>User::getNames(),
)); */ ?> 
</div><!-- search-form -->
<h2><?php echo $heading; ?></h2>
<?php
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'lists-grid',
	'enableSorting'=>false,
	'baseScriptUrl'=>Yii::app()->theme->getBaseUrl().'/css/gridview',
	'htmlOptions'=>array('class'=>'grid-view contact-lists'),
	'template'=> '{items}',
	'dataProvider'=>$contactLists,
	// 'filter'=>$model,
	'columns'=>array(
		//'id',
		array(
			'name'=>'name',
			'type'=>'raw',
			'value'=>'CHtml::link($data->name,X2List::getRoute($data->id))',
			'headerHtmlOptions'=>array('style'=>'width:40%;'),
		),
		array(
			'name'=>'type',
			'type'=>'raw',
			'value'=>'$data->type=="static"? Yii::t("app","Static") : Yii::t("app","Dynamic")',
			'headerHtmlOptions'=>array('style'=>'width:15%;'),
		),
		array(
			'name'=>'assignedTo',
			'type'=>'raw',
			'value'=>'User::getUserLinks($data->assignedTo)',
		),
		array(
			'name'=>'count',
			'headerHtmlOptions'=>array('class'=>'contact-count'),
			'htmlOptions'=>array('class'=>'contact-count'),
			'value'=>'Yii::app()->locale->numberFormatter->formatDecimal($data->count)',
			'headerHtmlOptions'=>array('style'=>'width:20%;'),
		),
	),
)); ?>
<br>
<?php
echo CHtml::link('<span class="add-button">'.Yii::t('app','New List').'</span>',array('/contacts/createList'),array('class'=>'x2-button'));