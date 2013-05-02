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
$menuItems = array(
	array('label'=>Yii::t('opportunities','Opportunities List'), 'url'=>array('index')),
	array('label'=>Yii::t('opportunities','Create'), 'url'=>array('create')),
	array('label'=>Yii::t('opportunities','View')),
	array('label'=>Yii::t('opportunities','Edit Opportunity'), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('accounts','Share Opportunity'),'url'=>array('shareOpportunity','id'=>$model->id)),
	array('label'=>Yii::t('contacts','View Relationships'),'url'=>'#', 'linkOptions'=>array('onclick'=>'toggleRelationshipsForm(); return false;')),
	array('label'=>Yii::t('opportunities','Add A User'), 'url'=>array('addUser', 'id'=>$model->id)),
	array('label'=>Yii::t('opportunities','Remove A User'), 'url'=>array('removeUser', 'id'=>$model->id)),
	array('label'=>Yii::t('opportunities','Delete'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>Yii::t('app','Attach A File/Photo'),'url'=>'#','linkOptions'=>array('onclick'=>'toggleAttachmentForm(); return false;')),
);

$contactModule = Modules::model()->findByAttributes(array('name'=>'contacts'));
$accountModule = Modules::model()->findByAttributes(array('name'=>'accounts'));

if($accountModule->visible) {
	$createAccountButton = 	array(array('label'=>Yii::t('accounts','Create Account'), 'url'=>'#', 'linkOptions'=>array('onclick'=>'return false;', 'id'=>'create-account')));
	array_splice($menuItems, 6, 0, $createAccountButton);
}

if($contactModule->visible) {
	$createContactButton = array(array('label'=>Yii::t('contacts','Create Contact'), 'url'=>'#', 'linkOptions'=>array('onclick'=>'return false;', 'id'=>'create-contact')));
	array_splice($menuItems, 6, 0, $createContactButton);
}

if($contactModule->visible && $accountModule->visible)
	$menuItems[] = 	array('label'=>Yii::t('app', 'Quick Create'), 'url'=>array('/site/createRecords', 'ret'=>'opportunities'), 'linkOptions'=>array('id'=>'x2-create-multiple-records-button', 'class'=>'x2-hint', 'title'=>Yii::t('app', 'Create a Contact, Account, and Opportunity.')));

$this->actionMenu = $this->formatMenu($menuItems, $authParams);
$themeUrl = Yii::app()->theme->getBaseUrl();
?>
<div id="main-column" class="half-width">
<div class="page-title icon opportunities">
	<?php //echo CHtml::link('['.Yii::t('contacts','Show All').']','javascript:void(0)',array('id'=>'showAll','class'=>'right hide','style'=>'text-decoration:none;')); ?>
	<?php //echo CHtml::link('['.Yii::t('contacts','Hide All').']','javascript:void(0)',array('id'=>'hideAll','class'=>'right','style'=>'text-decoration:none;')); ?>
	<h2><span class="no-bold"><?php echo Yii::t('opportunities','Opportunity:'); ?> </span><?php echo $model->name; ?></h2>
	<?php echo CHtml::link('<span></span>',array('update', 'id'=>$model->id),array('class'=>'x2-button icon edit right')); ?>
</div>
<?php
$form = $this->beginWidget('CActiveForm', array(
	'id'=>'contacts-form',
	'enableAjaxValidation'=>false,
	'action'=>array('saveChanges','id'=>$model->id),
));

$this->renderPartial('application.components.views._detailView',array('model'=>$model,'modelName'=>'Opportunity'));
$this->endWidget();

$this->widget('X2WidgetList', array('block'=>'center', 'model'=>$model, 'modelType'=>'opportunity'));

// $this->widget('InlineTags', array('model'=>$model));

// render workflow box
// $this->renderPartial('application.components.views._workflow',array('model'=>$model,'modelName'=>'opportunities','currentWorkflow'=>$currentWorkflow));
// $this->widget('WorkflowStageDetails',array('model'=>$model,'modelName'=>'opportunities','currentWorkflow'=>$currentWorkflow));
?>
<?php $this->widget('Attachments',array('associationType'=>'opportunities','associationId'=>$model->id,'startHidden'=>true)); ?>

<?php
//$this->widget('InlineRelationships', array('model'=>$model, 'modelName'=>'Opportunity'));

$linkModel = X2Model::model('Accounts')->findByPk($model->accountName);
if (isset($linkModel))
	$accountName = json_encode($linkModel->name);
else
	$accountName = json_encode('');
$createContactUrl = $this->createUrl('/contacts/create');
$createAccountUrl = $this->createUrl('/accounts/create');
$assignedTo = json_encode($model->assignedTo);
$contactTooltip = json_encode(Yii::t('opportunities', 'Create a new Contact associated with this Opportunity.'));
$accountsTooltip = json_encode(Yii::t('opportunities', 'Create a new Account associated with this Opportunity.'));

Yii::app()->clientScript->registerScript('create-model', "
	$(function() {
		// init create account button
		$('#create-account').initCreateAccountDialog2('$createAccountUrl', 'Opportunity', '{$model->id}', $accountName, $assignedTo, '', '', $accountsTooltip);

		// init create contact button
		$('#create-contact').initCreateContactDialog('$createContactUrl', 'Opportunity', '{$model->id}', $accountName, $assignedTo, '', '', $contactTooltip);
	});
");

?>
</div>
<div class="history half-width">
<?php
$this->widget('Publisher',
	array(
		'associationType'=>'opportunity',
		'associationId'=>$model->id,
		'assignedTo'=>Yii::app()->user->getName(),
		'halfWidth'=>true
	)
);

$this->widget('History',array('associationType'=>'opportunity','associationId'=>$model->id));
?>
</div>

<?php $this->widget('CStarRating',array('name'=>'rating-js-fix', 'htmlOptions'=>array('style'=>'display:none;'))); ?>