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
	array('label'=>Yii::t('actions','Today\'s Actions'),'url'=>array('index')),
	array('label'=>Yii::t('actions','All My Actions'),'url'=>array('viewAll')),
	array('label'=>Yii::t('actions','Everyone\'s Actions'),'url'=>array('viewGroup')),
	array('label'=>Yii::t('actions','Create Lead'),'url'=>array('quickCreate')),
	array('label'=>Yii::t('actions','Create Action'),'url'=>array('create','param'=>Yii::app()->user->getName().";none:0")), 
	array('label'=>Yii::t('actions','View Action')),
	array('label'=>Yii::t('actions','Update Action'),'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('actions','Delete Action'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
);
//Yii::app()->controller->action->id

//echo Yii::t('actions','View Action #{n}',array('{n}'=>$model->id)); 

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
<a class="x2-button" href="shareAction/<?php echo $model->id;?>"><span><?php echo Yii::t('actions','Share Action'); ?></span></a>
<?php
if(isset($associationModel) && $model->associationType=='contacts') {
	?>
	<a class="x2-button" href="#" onclick="toggleEmailForm(); return false;"><span><?php echo Yii::t('app','Send Email'); ?></span></a>
	<?php
	$this->widget('InlineEmailForm',
		array(
			'to'=>'<'.$associationModel->name.'> '.$associationModel->email,
			'redirectId'=>$model->id,
			'redirectType'=>'actions',
			'startHidden'=>true,
		)
	);
}
?>
<div class="form" id="action-form">
	<form name="complete-action" action="complete/<?php echo $model->id; ?>" method="POST">
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