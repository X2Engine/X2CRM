<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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

Yii::app()->clientScript->registerCss('recordViewCss',"
#content {
    background: none !important;
    border: none !important;
}
");
Yii::app()->clientScript->registerResponsiveCssFile(
    Yii::app()->theme->baseUrl.'/css/responsiveRecordView.css');


Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/Relationships.js');

$modelType = json_encode("Accounts");
$modelId = json_encode($model->id);
Yii::app()->clientScript->registerScript('widgetShowData', "
$(function() {
	$('body').data('modelType', $modelType);
	$('body').data('modelId', $modelId);
});");
$opportunityModule = Modules::model()->findByAttributes(array('name'=>'opportunities'));
$contactModule = Modules::model()->findByAttributes(array('name'=>'contacts'));


$authParams['X2Model']=$model;
$menuOptions = array(
    'all', 'create', 'view', 'edit', 'share',
    'delete', 'email', 'attach', 'quotes', 'print',
);
if ($opportunityModule->visible && $contactModule->visible)
    $menuOptions[] = 'quick';
$this->insertMenu($menuOptions, $model, $authParams);

$themeUrl = Yii::app()->theme->getBaseUrl();
?>

<div class="page-title-placeholder"></div>
<div class="page-title-fixed-outer">
    <div class="page-title-fixed-inner">
<div class="page-title icon accounts">
	<?php //echo CHtml::link('['.Yii::t('contacts','Show All').']','javascript:void(0)',array('id'=>'showAll','class'=>'right hide','style'=>'text-decoration:none;')); ?>
	<?php //echo CHtml::link('['.Yii::t('contacts','Hide All').']','javascript:void(0)',array('id'=>'hideAll','class'=>'right','style'=>'text-decoration:none;')); ?>

	<h2><span class="no-bold"><?php echo Yii::t('accounts','{module}:', array('{module}'=>Modules::displayName(false))); ?></span> <?php echo CHtml::encode($model->name); ?></h2>
	<?php
    if(Yii::app()->user->checkAccess('AccountsUpdate',$authParams)){
        echo X2Html::editRecordButton($model);
    } 
    echo X2Html::emailFormButton();
    echo X2Html::inlineEditButtons();
    ?>
</div>
</div>
</div>
<div id="main-column" class="half-width">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'accounts-form',
	'enableAjaxValidation'=>false,
	'action'=>array('saveChanges','id'=>$model->id),
));
$this->renderPartial('application.components.views._detailView',array('model'=>$model,'form'=>$form,'modelName'=>'accounts'));

$this->endWidget();

$this->widget('InlineEmailForm',
	array(
		'attributes'=>array(
			'to'=>implode (', ', $model->getRelatedContactsEmails ()),
			'modelName'=>'Accounts',
			'modelId'=>$model->id,
		),
		'templateType' => 'email',
		'insertableAttributes' => 
            array(Yii::t('accounts','{module} Attributes', array('{module}'=>Modules::displayName(false)))=>$model->getEmailInsertableAttrs ()),
		'startHidden'=>true,
	)
);

$this->widget('X2WidgetList', array('block'=>'center', 'model'=>$model, 'modelType'=>'accounts'));

?>
    <div id="quote-form-wrapper">
        <?php
        $this->widget('InlineQuotes', array(
            'startHidden' => true,
            'recordId' => $model->id,
            'account' => $model->name,
            'modelName' => X2Model::getModuleModelName ()
        ));
        ?>
    </div>
<?php $this->widget('Attachments',array('associationType'=>'accounts','associationId'=>$model->id,'startHidden'=>true)); ?>
<?php
//$this->widget('InlineRelationships', array('model'=>$model, 'modelName'=>'Accounts'));

$createContactUrl = $this->createUrl('/contacts/contacts/create');
$createOpportunityUrl = $this->createUrl('/opportunities/opportunities/create');
$createAccountUrl = $this->createUrl('/accounts/accounts/create');
$accountName = json_encode($model->name);
$assignedTo = json_encode($model->assignedTo);
$phone = json_encode($model->phone);
$website = json_encode($model->website);
$opportunityTooltip = json_encode(Yii::t('accounts', 'Create a new {opportunity} associated with this {account}.', array(
    '{opportunity}' => Modules::displayName(false, "Opportunities"),
    '{account}' => Modules::displayName(false),
)));
$contactTooltip = json_encode(Yii::t('accounts', 'Create a new {contact} associated with this {account}.', array(
    '{contact}' => Modules::displayName(false, "Contacts"),
    '{account}' => Modules::displayName(false),
)));
$accountsTooltip = json_encode(Yii::t('accounts', 'Create a new {account} associated with this {account}.', array(
    '{account}' => Modules::displayName(false),
)));
?>
</div>
<div class="history half-width">
<?php
$this->widget('Publisher',
	array(
		'associationType'=>'accounts',
		'associationId'=>$model->id,
		'assignedTo'=>Yii::app()->user->getName(),
		'calendar' => false
	)
);

$this->widget('History',array('associationType'=>'accounts','associationId'=>$model->id));
?>
</div>

<?php //$this->widget('CStarRating',array('name'=>'rating-js-fix', 'htmlOptions'=>array('style'=>'display:none;'))); ?>


