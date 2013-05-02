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

$authParams['assignedTo'] = $model->assignedTo;
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('contacts','All Contacts'),'url'=>array('index')),
	array('label'=>Yii::t('contacts','Lists'),'url'=>array('lists')),
	array('label'=>Yii::t('contacts','Create Contact'),'url'=>array('create')),
	array('label'=>Yii::t('contacts','View'),'url'=>array('view', 'id'=>$model->id)),
    array('label'=>Yii::t('contacts','Edit Contact'),'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('contacts','Share Contact')),
	array('label'=>Yii::t('contacts','View Relationships'),'url'=>array('viewRelationships','id'=>$model->id)),
	array('label'=>Yii::t('contacts','Delete Contact'),'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
),$authParams);

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/ckeditor/ckeditor.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/ckeditor/adapters/jquery.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/emailEditor.js');

Yii::app()->clientScript->registerScript('editorSetup','createCKEditor("input");',CClientScript::POS_READY);
?>
<div class="page-title icon contacts"><h2><span class="no-bold"><?php echo Yii::t('contacts','Share Contact');?>:</span> <?php echo $model->firstName." ".$model->lastName;?></h2></div>
<?php
if(!empty($status)) {
	$index = array_search('200',$status);
	if($index !== false) {
		unset($status[$index]);
		$email = '';
		$subject = '';
	}
	echo '<div class="form">';
	foreach($status as &$status_msg) echo $status_msg." \n";
	echo '</div>';
}
// echo var_dump($errors);
?>
<div class="form">
<form method="POST" name="share-contact-form">
	<b><span<?php if(in_array('email',$errors)) echo ' class="error"'; ?>><?php echo Yii::t('contacts','E-Mail');?></span></b><br /><input type="text" name="email" size="50"<?php if(in_array('email',$errors)) echo ' class="error"'; ?> value="<?php if(!empty($email)) echo $email; ?>"><br />
	<b><span<?php if(in_array('body',$errors)) echo ' class="error"'; ?>><?php echo Yii::t('app','Message Body');?></span></b><br /><textarea name="body" id="input" style="height:200px;width:558px;"<?php if(in_array('body',$errors)) echo ' class="error"'; ?>><?php echo $body; ?></textarea><br />
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
$this->renderPartial('application.components.views._detailView',array('model'=>$model,'modelName'=>'contacts'));
$this->endWidget(); ?>