<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

$canEdit = $model->id==Yii::app()->user->getId() || Yii::app()->user->checkAccess('AdminIndex');

$this->actionMenu = array(
	array('label'=>Yii::t('profile','View Profile')),
	array('label'=>Yii::t('profile','Update Profile'), 'url'=>array('update','id'=>$model->id),'visible'=>$canEdit),
	array('label'=>Yii::t('profile','Change Settings'),'url'=>array('settings','id'=>$model->id),'visible'=>($model->id==Yii::app()->user->getId())),
	array('label'=>Yii::t('profile','Change Password'),'url'=>array('changePassword','id'=>$model->id),'visible'=>($model->id==Yii::app()->user->getId())),
	array('label'=>Yii::t('profile','Reset Widgets'),'url'=>array('resetWidgets','id'=>$model->id),'visible'=>($model->id==Yii::app()->user->getId()))
);

Yii::app()->clientScript->registerScript('highlightButton','
$("#feed-form textarea").bind("focus blur",function(){ toggleText(this); })
	.change(function(){
		if($(this).val()=="")
			$("#save-button").removeClass("highlight");
		else
			$("#save-button").addClass("highlight");
	});
',CClientScript::POS_READY);
?>
<div class="page-title">
	<h2><span class="no-bold"><?php echo Yii::t('profile','Profile:'); ?> </span><?php echo $model->fullName; ?></h2>
</div>
<?php $this->renderPartial('_detailView',array('model'=>$model)); ?>
<?php //echo CHtml::mailto(Yii::t('profile','Send E-Mail'),$model->emailAddress,array('class'=>'x2-button')); ?>

<div class="form">
	<?php $feed=new Events; 
	
	$feed->text = Yii::t('app','Enter text here...');

	$form = $this->beginWidget('CActiveForm', array(
	'id'=>'feed-form',
	'enableAjaxValidation'=>false,
	'action'=>array('addPost','id'=>$model->id,'redirect'=>'view'),
)); ?>
	<div class="float-row">
		<?php
		if($model->allowPost==1)
			echo $form->textArea($feed,'text',array('style'=>'width:558px;height:50px;color:#aaa;display:block;clear:both;'));
		else
			echo "This user does not allow posting on their feed.";
		if($model->allowPost==1) {
			echo $form->dropDownList($feed,'visibility',array(1=>Yii::t('actions','Public'),0=>Yii::t('actions','Private')));
			echo $form->dropDownList($feed,'subtype',json_decode(Dropdowns::model()->findByPk(113)->options,true));
            echo CHtml::submitButton(Yii::t('app','Post'),array('class'=>'x2-button','id'=>'save-button'));
			echo CHtml::button(Yii::t('app','Attach A File/Photo'),array('class'=>'x2-button','type'=>'button','onclick'=>"$('#attachments').toggle();return false;"));
		}
		?>
	</div>
	<?php $this->endWidget(); ?>
</div>

<div id="attachments" style="display:none;">
<?php $this->widget('Attachments',array('associationType'=>'feed', 'associationId'=>$model->id)); ?>
</div>
<?php
$this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'../social/_viewFull',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/listview',
	// 'template'=> '<h3>'.Yii::t('profile','Feed').'</h3>{summary}{sorter}{items}{pager}',
	'template'=> '{sorter}{items}{pager}',
));
?>