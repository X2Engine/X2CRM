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
	array('label'=>Yii::t('contacts','Contacts Lists'),'url'=>array('index')),
	// array('label'=>Yii::t('contacts','All Contacts'),'url'=>array('viewAll')),
	array('label'=>Yii::t('contacts','Create Contact'),'url'=>array('create')),
	array('label'=>Yii::t('contacts','Create Lead'),'url'=>array('actions/quickCreate')),
	array('label'=>Yii::t('contacts','View Contact')),
        array('label'=>Yii::t('contacts','View Sales'),'url'=>array('viewSales','id'=>$model->id)),
	array('label'=>Yii::t('contacts','Delete Contact'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('app','Are you sure you want to delete this item?'))),
);
if (Yii::app()->user->getName() == $model->assignedTo || Yii::app()->user->getName() == 'admin' || $model->assignedTo == 'Anyone') {
	$this->menu[] = array('label'=>Yii::t('contacts','Update Contact'), 'url'=>array('update', 'id'=>$model->id));
}

?>
<?php

$form = $this->beginWidget('CActiveForm', array(
	'id'=>'contacts-form',
	'enableAjaxValidation'=>false,
	'action'=>array('saveChanges','id'=>$model->id),
));
?>
<!--<h2><?php echo Yii::t('contacts','Contact:'); ?> <b><?php echo $model->firstName.' '.$model->lastName; ?></b></h2>-->
<?php

if(isset($_GET['detail'])) {
	$detailView = ($_GET['detail']=='1')? 1 : 0;
	ProfileChild::setDetailView($detailView);
} else {
	$detailView = ProfileChild::getDetailView();
}

if($detailView) { ?>
	<?php echo CHtml::link('['.Yii::t('contacts','Simple View').']',array('view','id'=>$model->id,'detail'=>0),array('style'=>'float:right;text-decoration:none;')); ?>
	<h2 style="margin-bottom:0;"><?php echo Yii::t('contacts','Contact:'); ?> <b><?php echo $model->firstName.' '.$model->lastName; ?></b></h2>
	<?php
	$this->renderPartial('_detailView',array('model'=>$model,'form'=>$form,'users'=>$users,'currentWorkflow'=>$currentWorkflow));
	$this->endWidget();
} else { ?>
	<?php echo CHtml::link('['.Yii::t('contacts','Detail View').']',array('view','id'=>$model->id,'detail'=>1),array('style'=>'float:right;text-decoration:none;')); ?>
	<h2 style="margin-bottom:0;"><?php echo Yii::t('contacts','Contact:'); ?> <b><?php echo $model->firstName.' '.$model->lastName; ?></b></h2>
	<?php
	$this->renderPartial('_simpleView',array('model'=>$model,'form'=>$form,'users'=>$users,'currentWorkflow'=>$currentWorkflow));
	$this->endWidget();
}

if($detailView) { ?>
	<a class="x2-button" id="save-changes" href="#" onClick="submitForm('contacts-form');return false;"><span><?php echo Yii::t('app','Save Changes'); ?></span></a>
	<?php /*<a class="x2-button" href="#" onClick="toggleForm('#note-form',400);return false;"><span><?php echo Yii::t('app','Add Comment'); ?></span></a>
	<a class="x2-button" href="#" onClick="toggleForm('#action-form',400);return false;"><span><?php echo Yii::t('app','Create Action'); ?></span></a> */ ?>
<?php } ?>
	<a class="x2-button" href="#" onclick="toggleEmailForm(); return false;"><span><?php echo Yii::t('app','Send Email'); ?></span></a>
	<a class="x2-button" href="#" onClick="toggleForm('#attachment-form',200);return false;"><span><?php echo Yii::t('app','Attach A File/Photo'); ?></span></a>
	<a class="x2-button" style="margin-bottom:15px;" href="shareContact/<?php echo $model->id;?>"><span><?php echo Yii::t('contacts','Share Contact'); ?></span></a>
	<br />
<div id="attachment-form" style="display:none;">
	<?php $this->widget('Attachments',array('type'=>'contacts','associationId'=>$model->id)); ?>
</div>
<?php
$this->widget('InlineEmailForm',
	array(
		'address'=>$model->email,
		'name'=>$model->name,
		'redirect'=>'contacts/'.$model->id,
		// 'redirectType'=>'contacts',
		'startHidden'=>true,
	)
);
$this->widget('InlineActionForm',
	array(
		'associationType'=>'contacts',
		'associationId'=>$model->id,
		'assignedTo'=>Yii::app()->user->getName(),
		'users'=>$users,
		'startHidden'=>false,
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
