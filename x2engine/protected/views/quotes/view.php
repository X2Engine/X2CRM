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

$this->menu=array(
	array('label'=>Yii::t('quotes','Quotes List'), 'url'=>array('index')),
	array('label'=>Yii::t('quotes','Create'), 'url'=>array('create')),
	array('label'=>Yii::t('quotes','View')),
	array('label'=>Yii::t('quotes','Update'), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('quotes','Delete'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
);

?>
<?php echo CHtml::link('['.Yii::t('contacts','Show All').']','javascript:void(0)',array('id'=>'showAll','class'=>'right hide','style'=>'text-decoration:none;')); ?>
<?php echo CHtml::link('['.Yii::t('contacts','Hide All').']','javascript:void(0)',array('id'=>'hideAll','class'=>'right','style'=>'text-decoration:none;')); ?>
<h2><?php echo Yii::t('quotes','Quote:'); ?> <b><?php echo $model->name; ?></b> <a class="x2-button" href="update/<?php echo $model->id;?>">Edit</a></h2>

<?php
$form = $this->beginWidget('CActiveForm', array(
	'id'=>'quotes-form',
	'enableAjaxValidation'=>false,
	'action'=>array('saveChanges','id'=>$model->id),
));


$this->renderPartial('application.components.views._detailView',array('model'=>$model,'modelName'=>'quotes'));

$productField = Fields::model()->findByAttributes(array('modelName'=>'Quotes', 'fieldName'=>'products'));

?>
<div class="x2-layout form-view" style="margin-bottom: 0;">
	<div class="formSection">
		<div class="formSectionHeader">
			<span class="sectionTitle"><?php echo $productField->attributeLabel; ?></span>
		</div>
	</div>
</div>
<div class="form" style="border:1px solid #ccc; border-top: 0; padding: 0; margin-top:-1px; border-radius:0;-webkit-border-radius:0; background:#fafafa;">
<?php
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>"quote-products-grid",
	'baseScriptUrl'=>Yii::app()->theme->getBaseUrl().'/css/gridview',
	'summaryText'=>'',
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		array(
			'name'=>'name',
			'header'=>Yii::t('product','Line Item'),
			'value'=>'$data["name"]',
			'type'=>'raw',
		),
		array(
			'name'=>'unit',
			'header'=>Yii::t('product','Unit Price'),
			'value'=>'Yii::app()->locale->numberFormatter->formatCurrency($data["unit"],"'.$model->currency.'")',
			'type'=>'raw',
		),
		array(
			'name'=>'quantity',
			'header'=>Yii::t('product','Quantity'),
			'value'=>'$data["quantity"]',
			'type'=>'raw',
		),
		array(
			'name'=>'adjustment',
			'header'=> Yii::t('product', 'Adjustment'),
			'value'=>'$data["adjustment"]',
			'type'=>'raw',
			'footer'=>'<b>Total</b>',
		),
		array(
			'name'=>'price',
			'header'=>Yii::t('product', "Price"),
			'value'=>'Yii::app()->locale->numberFormatter->formatCurrency($data["price"],"'.$model->currency.'")',
			'type'=>'raw',
			'footer'=>'<b>'. Yii::app()->locale->numberFormatter->formatCurrency($total,$model->currency) .'</b>',
		),
	),
));
?>
</div>
<?php
/*
$this->renderPartial('_detailView',
	array(
		'model'=>$model,
		'form'=>$form,
		'currentWorkflow'=>$currentWorkflow,
		'dataProvider'=>$dataProvider,
		'total'=>$total
	)
);
*/
$this->endWidget();

?>
<a class="x2-button" id="save-changes" href="#" onClick="submitForm('contacts-form');return false;"><span><?php echo Yii::t('app','Save Changes'); ?></span></a>
<a class="x2-button" href="#" onClick="toggleForm('#attachment-form',200);return false;"><span><?php echo Yii::t('app','Attach A File/Photo'); ?></span></a>
<a class="x2-button" href="shareQuote/<?php echo $model->id;?>"><span><?php echo Yii::t('quotes','Share Quote'); ?></span></a>
<a class="x2-button" href="#" onClick="window.open('<?php echo Yii::app()->createUrl('quotes/print', array('id'=>$model->id)); ?>')"><span><?php echo Yii::t('quotes','Print Quote'); ?></span></a>
<br /><br />

<div id="attachment-form" style="display:none;">
	<?php $this->widget('Attachments',array('type'=>'quotes','associationId'=>$model->id)); ?>
</div>
<?php

$this->widget('InlineActionForm',
	array(
		'associationType'=>'quotes',
		'associationId'=>$model->id,
		'assignedTo'=>Yii::app()->user->getName(),
		'users'=>$users,
		'startHidden'=>false
	)
);

if(isset($_GET['history']))
    $history=$_GET['history'];
else
    $history="all";

$this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$actionHistory,
	'itemView'=>'../actions/_view',
	'htmlOptions'=>array('class'=>'action list-view'),
	'template'=> 
            ($history=='all'?'<h3>'.Yii::t('app','History')."</h3>":CHtml::link(Yii::t('app','History'),"?history=all")).
            " | ".($history=='actions'?'<h3>'.Yii::t('app','Actions')."</h3>":CHtml::link(Yii::t('app','Actions'),"?history=actions")).
            " | ".($history=='comments'?'<h3>'.Yii::t('app','Comments')."</h3>":CHtml::link(Yii::t('app','Comments'),"?history=comments")).
            " | ".($history=='attachments'?'<h3>'.Yii::t('app','Attachments')."</h3>":CHtml::link(Yii::t('app','Attachments'),"?history=attachments")).
            '</h3>{summary}{sorter}{items}{pager}',
));
?>