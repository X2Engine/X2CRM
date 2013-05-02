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
$authParams['assignedTo']=$model->assignedTo;
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('services','All Cases'), 'url'=>array('index')),
	array('label'=>Yii::t('services','Create Case'), 'url'=>array('create')),
	array('label'=>Yii::t('services','View'), 'url'=>array('view','id'=>$model->id)),
	array('label'=>Yii::t('services','Edit Case')),
	array('label'=>Yii::t('services','Delete Case'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>Yii::t('services','Create Web Form'), 'url'=>array('createWebForm')),
),$authParams);
?>
<?php //echo CHtml::link('['.Yii::t('contacts','Show All').']','javascript:void(0)',array('id'=>'showAll','class'=>'right hide','style'=>'text-decoration:none;')); ?>
<?php //echo CHtml::link('['.Yii::t('contacts','Hide All').']','javascript:void(0)',array('id'=>'hideAll','class'=>'right','style'=>'text-decoration:none;')); ?>
<div class="page-title icon services">
	<h2><span class="no-bold"><?php echo Yii::t('module','Update'); ?>: </span>	<?php echo Yii::t('services','Case {n}',array('{n}'=>$model->id)); ?></h2>
	<a class="x2-button right highlight" href="javascript:void(0);" onclick="$('#save-button').click();"><?php echo Yii::t('app','Save'); ?></a>
</div>
<?php echo $this->renderPartial('application.components.views._form', array('model'=>$model, 'users'=>$users, 'modelName'=>'services')); ?>

<?php
$createContactUrl = $this->createUrl('/contacts/create');
$contactTooltip = json_encode(Yii::t('contacts', 'Create a new Contact'));

Yii::app()->clientScript->registerScript('create-model', "
	$(function() {		
		// init create contact button
		$('#create-contact').initCreateContactDialog('$createContactUrl', 'Services', '{$model->id}', '', '', '', '', $contactTooltip);
	});
");
?>

<?php $this->widget('CStarRating',array('name'=>'rating-js-fix', 'htmlOptions'=>array('style'=>'display:none;'))); ?>
