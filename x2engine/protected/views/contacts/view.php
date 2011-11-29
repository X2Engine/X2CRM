<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
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
	$this->renderPartial('_simpleView',array('model'=>$model,'form'=>$form,'users'=>$users));
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
		'to'=>'<'.$model->name.'> '.$model->email,
		'redirectId'=>$model->id,
		'redirectType'=>'contacts',
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
