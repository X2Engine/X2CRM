<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/



Yii::app()->clientScript->registerCss('recordViewCss',"

#content {
    background: none !important;
    border: none !important;
}
");
Yii::app()->clientScript->registerResponsiveCssFile(
    Yii::app()->theme->baseUrl.'/css/responsiveRecordView.css');

$layoutManager = $this->widget ('RecordViewLayoutManager', array ('staticLayout' => false));

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
    'editLayout', 'print'
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
    <?php
    echo X2Html::editRecordButton($model);
    echo X2Html::emailFormButton();
    echo X2Html::inlineEditButtons();
    ?>
</div>
</div>
</div>
<div id="main-column" <?php echo $layoutManager->columnWidthStyleAttr (1); ?>>

<?php
$this->beginWidget('CActiveForm', array(
    'id'=>'contacts-form',
    'enableAjaxValidation'=>false,
    'action'=>array('saveChanges','id'=>$model->id),
));
$this->widget('DetailView', array(
    'model'   => $model,
));
// $this->renderPartial('application.components.views.@DETAILVIEW',array('model'=>$model,'modelName'=>'Opportunity'));
$this->endWidget();

$inlineEmailTo = '';
if((bool) $model->contactName){
    if ($contact = $model->getLinkedModel('contactName'))
        $inlineEmailTo = '"'.$contact->name.'" <'.$contact->email.'>, ';
}
$this->widget('InlineEmailForm', array(
    'attributes' => array(
        'to' => $inlineEmailTo,
        'modelName' => 'Opportunities',
        'modelId' => $model->id,
        'targetModel' => $model,
    ),
    'startHidden' => true,
));


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

// $this->widget(
//     'Attachments',
//     array(
//         'associationType'=>'opportunities','associationId'=>$model->id,
//         'startHidden'=>true
//     )
// ); 
$this->widget(
    'ModelFileUploader',
    array(
        'associationType'=>'opportunities',
        'associationId'=>$model->id,
    )
); 

?>
</div>
<?php
$this->widget(
    'X2WidgetList', 
    array(
        'layoutManager' => $layoutManager,
        'block'=>'center',
        'model'=>$model,
        'modelType'=>'opportunities'
    ));
