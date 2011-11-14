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
	array('label'=>Yii::t('contacts','Create Contact'),'url'=>array('create')),
	array('label'=>Yii::t('contacts','Create Lead'),'url'=>array('actions/quickCreate')),
	array('label'=>Yii::t('contacts','View Contact')),
	array('label'=>Yii::t('contacts','Delete Contact'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
);
if (Yii::app()->user->getName() == $model->assignedTo || Yii::app()->user->getName() == 'admin' || $model->assignedTo == 'Anyone') {
	$this->menu[] = array('label'=>'Update Contact', 'url'=>array('update', 'id'=>$model->id));
}



?>
<h2><?php echo Yii::t('contacts','Share Contact');?>: <b><?php echo $model->firstName." ".$model->lastName;?></b></h2>
<div class="form">
<form method="POST" name="share-contact-form">
	<b><?php echo Yii::t('contacts','E-Mail');?></b><br /><input type="text" name="email" size="50" /><br />
	<b><?php echo Yii::t('app','Message Body');?></b><br /><textarea name="body" style="height:200px;width:558px;"><?php echo $body; ?></textarea><br />
	<input type="submit" class="x2-button" value="<?php echo Yii::t('app','Share');?>" />
</form>
</div>
<?php
$form = $this->beginWidget('CActiveForm', array(
	'id'=>'contacts-form',
	'enableAjaxValidation'=>false,
	'action'=>array('saveChanges','id'=>$model->id),
));
?>
<h2><?php echo Yii::t('contacts','Contact:'); ?> <b><?php echo $model->firstName.' '.$model->lastName; ?></b></h2>
<?php
$this->renderPartial('_detailView',array('model'=>$model,'form'=>$form,'users'=>$users)); 
$this->endWidget(); ?>