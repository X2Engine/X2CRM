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
	array('label'=>Yii::t('actions','Today\'s Actions'),'url'=>array('index')),
	array('label'=>Yii::t('actions','All My Actions'),'url'=>array('viewAll')),
	array('label'=>Yii::t('actions','Everyone\'s Actions'),'url'=>array('viewGroup')),
	// array('label'=>Yii::t('actions','Create Lead'),'url'=>array('quickCreate')),
	array('label'=>Yii::t('actions','Create'),'url'=>array('create','param'=>Yii::app()->user->getName().";none:0")), 
	array('label'=>Yii::t('actions','View')),
	array('label'=>Yii::t('actions','Update'),'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('actions','Delete'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
);
//Yii::app()->controller->action->id

//echo Yii::t('actions','View Action #{n}',array('{n}'=>$model->id)); 
$this->actionMenu = array( 
	array('label'=>Yii::t('contacts','Share Action'),'url'=>array('shareAction','id'=>$model->id)),
);
?>
<h2><?php
	if($model->associationType=='none')
		echo Yii::t('actions','Action');
	else
		echo Yii::t('actions','Action').': <b>'.$model->associationName.'</b>'; ?>
</h2>
<?php
$this->renderPartial('_detailView',array('model'=>$model));

if (empty($model->type) || $model->type=='Web Lead') {
	if ($model->complete=='Yes')
		echo CHtml::link(Yii::t('actions','Uncomplete'),array('actions/uncomplete','id'=>$model->id),array('class'=>'x2-button'));
	else {
?>
<?php
if(isset($associationModel) && $model->associationType=='contacts') {
    $this->actionMenu[]=array('label'=>Yii::t('app','Send Email'),'url'=>'#','linkOptions'=>array('onclick'=>'toggleEmailForm(); return false;'));
	$this->widget('InlineEmailForm',
	array(
		'attributes'=>array(
			'to'=>'"'.$associationModel->name.'" <'.$associationModel->email.'>, ',
			// 'subject'=>'hi',
			// 'redirect'=>'contacts/'.$model->id,
			'modelName'=>'Contacts',
			'modelId'=>$associationModel->id,
		),
		'startHidden'=>true,
	)
);
}
?>
<div class="form" id="action-form">
	<form id="complete-action" name="complete-action" action="complete/<?php echo $model->id; ?>" method="POST">
		<b><?php echo Yii::t('actions','Completion Notes'); ?></b>
		<textarea name="note" rows="4" ></textarea>
<?php
		//echo CHtml::link(Yii::t('actions','Complete'),array('actions/complete','id'=>$model->id),array('class'=>'x2-button'));
		//echo CHtml::link(,array('actions/complete','id'=>$model->id,'createNew'=>1,'redirect'=>1),array('class'=>'x2-button'));
?>	<div class="row buttons">
		<button type="submit" name="submit" class="x2-button" value="complete"><?php echo Yii::t('actions','Complete'); ?></button>
		<button type="submit" name="submit" class="x2-button" value="completeNew"><?php echo Yii::t('actions','Complete + New Action'); ?></button>
		
	</div>
		
	</form>
</div>
<?php
	}
}

if($model->associationId!=0) {
	if($model->associationType=='contacts') { 
		echo '<h2>'.Yii::t('actions','Contact Info').'</h2>';
		$this->renderPartial('//contacts/_detailViewMini',array('model'=>$associationModel,'actionModel'=>$model));
	}
	
	$actionHistory=new CActiveDataProvider('Actions', array(
		'criteria'=>array(
			'order'=>'(IF (completeDate IS NULL, dueDate, completeDate)) DESC, createDate DESC',
			'condition'=>'associationId='.$model->associationId.' AND associationType=\''.$model->associationType.'\''
	)));
	
	$this->widget('zii.widgets.CListView', array(
		'dataProvider'=>$actionHistory,
		'itemView'=>'../actions/_view',
		'htmlOptions'=>array('class'=>'action list-view'),
		'template'=> '<h3>'.Yii::t('app','History').'</h3>{summary}{sorter}{items}{pager}',
	));
}


?>
<!--<a class="x2-button" href="#" onClick="toggleForm('#action-form',400);return false;"><span><?php echo Yii::t('app','Create Action'); ?></span></a>-->
<?php /*
	$this->widget('InlineActionForm',
			array(
				'associationType'=>'contact',
				'associationId'=>$model->associationId,
				'assignedTo'=>Yii::app()->user->getName(),
				'users'=>$users,
				'startHidden'=>true
			)
	);
	*/
?>
<script>
$('#complete-button').click(function(){
    $("form#complete-action").submit();
});
</script>