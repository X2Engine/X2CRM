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

$modelType = json_encode("Opportunities");
$modelId = json_encode($model->id);
Yii::app()->clientScript->registerScript('widgetShowData', "
$(function() {
	$('body').data('modelType', $modelType);
	$('body').data('modelId', $modelId);
});");


$authParams['X2Model'] = $model;
$contactModule = Modules::model()->findByAttributes(array('name'=>'contacts'));
$accountModule = Modules::model()->findByAttributes(array('name'=>'accounts'));

$menuOptions = array(
    'index', 'create', 'view', 'edit', 'share', 'delete', 'attach', 'quotes', 'import', 'export',
);
if ($contactModule->visible && $accountModule->visible)
    $menuOptions[] = 'quick';
$this->insertMenu($menuOptions, $model, $authParams);


$themeUrl = Yii::app()->theme->getBaseUrl();
?>

<div class="page-title-placeholder"></div>
<div class="page-title-fixed-outer">
    <div class="page-title-fixed-inner">
<div class="page-title icon opportunities">
	<?php //echo CHtml::link('['.Yii::t('contacts','Show All').']','javascript:void(0)',array('id'=>'showAll','class'=>'right hide','style'=>'text-decoration:none;')); ?>
	<?php //echo CHtml::link('['.Yii::t('contacts','Hide All').']','javascript:void(0)',array('id'=>'hideAll','class'=>'right','style'=>'text-decoration:none;')); ?>
	<h2><span class="no-bold"><?php echo Yii::t('opportunities','{opportunity}:', array('{opportunity}'=>Modules::displayName(false))); ?> </span><?php echo CHtml::encode($model->name); ?></h2>
	<?php echo X2Html::editRecordButton($model); ?>
    <?php if ((bool) $model->contactName) {
        echo X2Html::emailFormButton();        
    }
    echo X2Html::inlineEditButtons();
    ?>
</div>
</div>
</div>
<div id="main-column" class="half-width">
<?php
$this->beginWidget('CActiveForm', array(
    'id'=>'contacts-form',
    'enableAjaxValidation'=>false,
    'action'=>array('saveChanges','id'=>$model->id),
));

$this->renderPartial('application.components.views._detailView',array('model'=>$model,'modelName'=>'Opportunity'));
$this->endWidget();

$this->widget('InlineEmailForm', array(
    'attributes' => array(
        'modelName' => 'Opportunities',
        'modelId' => $model->id,
        'targetModel' => $model,
    ),
    'startHidden' => true,
));

$this->widget('X2WidgetList', array('block'=>'center', 'model'=>$model, 'modelType'=>'opportunities'));

// $this->widget('InlineTags', array('model'=>$model));

// render workflow box
// $this->renderPartial('application.components.views._workflow',array('model'=>$model,'modelName'=>'opportunities','currentWorkflow'=>$currentWorkflow));
// $this->widget('WorkflowStageDetails',array('model'=>$model,'modelName'=>'opportunities','currentWorkflow'=>$currentWorkflow));
?>
    <div id="quote-form-wrapper">
        <?php
        $this->widget('InlineQuotes', array(
            'startHidden' => true,
            'recordId' => $model->id,
            'account' => $model->getLinkedAttribute('accountName', 'name'),
            'modelName' => X2Model::getModuleModelName ()
        ));
        ?>
    </div>

<?php 
if((bool) $model->contactName){ // if associated contact exists, setup inline email form
    $contact = $model->getLinkedModel('contactName');
    if ($contact) {
        $this->widget('InlineEmailForm', array(
            'attributes' => array(
                'to' => '"'.$contact->name.'" <'.$contact->email.'>, ',
                'modelName' => 'Opportunity',
                'modelId' => $model->id,
            ),
            'startHidden' => true,
        ));
    }
}

$this->widget(
    'Attachments',
    array(
        'associationType'=>'opportunities','associationId'=>$model->id,
        'startHidden'=>true
    )
); 

//$this->widget('InlineRelationships', array('model'=>$model, 'modelName'=>'Opportunity'));

$linkModel = X2Model::model('Accounts')->findByPk($model->accountName);

if (isset($linkModel))
	$accountName = json_encode($linkModel->name);
else
	$accountName = json_encode('');

$createContactUrl = $this->createUrl('/contacts/contacts/create');
$createAccountUrl = $this->createUrl('/accounts/accounts/create');
$createOpportunityUrl=$this->createUrl('/opportunities/opportunities/create');
$assignedTo = json_encode($model->assignedTo);
$tooltip = json_encode(
    Yii::t('opportunities', 'Create a new {opportunity} associated with this {opportunity}.', array(
        '{opportunity}' => Modules::displayName(false),
    )));
$contactTooltip = json_encode(
    Yii::t('opportunities', 'Create a new {contact} associated with this {opportunity}.', array(
        '{opportunity}' => Modules::displayName(false),
        '{contact}' => Modules::displayName(false, "Contacts"),
    )));
$accountsTooltip = json_encode(
    Yii::t('opportunities', 'Create a new {account} associated with this {opportunity}.', array(
        '{opportunity}' => Modules::displayName(false),
        '{account}' => Modules::displayName(false, "Accounts"),
    )));

?>
</div>
<div class="history half-width">
<?php
$this->widget('Publisher',
	array(
		'associationType'=>'opportunities',
		'associationId'=>$model->id,
		'assignedTo'=>Yii::app()->user->getName(),
		'calendar' => false
	)
);

$this->widget('History',array('associationType'=>'opportunities','associationId'=>$model->id));
?>
</div>

<?php $this->widget('CStarRating',array('name'=>'rating-js-fix', 'htmlOptions'=>array('style'=>'display:none;'))); ?>
