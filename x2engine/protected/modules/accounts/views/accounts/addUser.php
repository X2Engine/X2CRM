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



$menuItems = array(
	array('label'=>Yii::t('accounts','All Accounts'), 'url'=>array('index')),
	array('label'=>Yii::t('accounts','Create Account'), 'url'=>array('create')),
	array('label'=>Yii::t('accounts','View'), 'url'=>array('view','id'=>$model->id)),
	array('label'=>Yii::t('accounts','Edit Account'), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('accounts','Share Account'),'url'=>array('shareAccount','id'=>$model->id)),
	array('label'=>Yii::t('accounts','Add a User'), 'url'=>array('addUser', 'id'=>$model->id)),
	array('label'=>Yii::t('accounts','Remove a User'), 'url'=>array('removeUser', 'id'=>$model->id)),
	array('label'=>Yii::t('accounts','Delete Account'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
);
if($action=='Remove')
	unset($menuItems[6]['url']);
else
	unset($menuItems[5]['url']);

$authParams['assignedTo']=$model->assignedTo;
$this->actionMenu = $this->formatMenu($menuItems,$authParams);



?>
<div class="page-title icon accounts">
	<h2><span class="no-bold"><?php echo Yii::t('accounts','Update:'); ?></span> <?php echo $model->name; ?></h2>
</div>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'acocunts-form',
	'enableAjaxValidation'=>false,
)); 
echo ($action=='Remove')?Yii::t('accounts','Please click any new users you wish to remove.'):Yii::t('accounts','Please click any new users you wish to add.');
?>
<br /><br />
<div class="row">
	<?php echo $form->dropDownList($model,'assignedTo',$users,array("multiple"=>"multiple", 'size'=>8)); ?>
	<?php echo $form->error($model,'assignedTo'); ?>
</div>

<div class="row buttons">
	<?php echo CHtml::submitButton(Yii::t('app',$action),array('class'=>'x2-button highlight')); ?>
</div>

<?php $this->endWidget(); ?>

</div>