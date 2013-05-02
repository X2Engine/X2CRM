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

$pieces = $model->editPermissions;
$user = Yii::app()->user->getName();

$authParams['assignedTo']=$model->createdBy;
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('docs','List Docs'), 'url'=>array('index')),
	array('label'=>Yii::t('docs','Create Doc'), 'url'=>array('create')),
	array('label'=>Yii::t('docs','Create Email'), 'url'=>array('createEmail')),
	array('label'=>Yii::t('docs','Create Quote'), 'url'=>array('createQuote')),
	array('label'=>Yii::t('docs','View'), 'url'=>array('view','id'=>$model->id)),
    array('label'=>Yii::t('docs','Edit Doc'), 'url'=>array('update', 'id'=>$model->id)),
    array('label'=>Yii::t('docs','Delete Doc'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('docs','Are you sure you want to delete this item?')))
),$authParams);


if(array_search($user,$pieces)!=false || $user==$model->editPermissions || $user=='admin' || $user==$model->createdBy)
	$this->actionMenu[]=array('label'=>Yii::t('docs','Edit Doc Permissions'));
	
$this->actionMenu[] = array('label'=>Yii::t('docs','Export Doc'),'url'=>array('exportToHtml','id'=>$model->id));
	
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'docs-form',
	'enableAjaxValidation'=>false,
));?>
<div class="page-title icon docs"><h2><?php echo Yii::t('docs','Edit Doc Permissions');?></h2></div>
<div class="form">
	<div class="row" style="width:500px;">
		<?php echo Yii::t('docs','Please select which users are allowed to edit the document.  Use Control + Click to select or deselect individual users.'); ?>
	</div><br>
	<div class="row"><?php
		echo $form->label($model,'editPermissions');
		echo $form->dropDownList($model,'editPermissions',$users,array('multiple'=>'multiple','size'=>'5'));
		echo $form->error($model,'editPermissions'); ?>
	</div>
	<div class="row">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create') : Yii::t('app','Save'),array('class'=>'x2-button')); ?>
	</div>
</div>
<?php $this->endWidget();