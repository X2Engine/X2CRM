<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

Yii::app()->clientScript->registerCss ('servicesView', "
	#contact-info-container {
		margin: -6px 5px 5px 5px !important;
	}
");

$authParams['assignedTo']=$model->assignedTo;
$menuItems = array(
	array('label'=>Yii::t('services','All Cases'), 'url'=>array('index')),
	array('label'=>Yii::t('services','Create Case'), 'url'=>array('create')),
	array('label'=>Yii::t('services','View')),
	array('label'=>Yii::t('services','Edit Case'), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('services','Delete Case'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>Yii::t('app','Send Email'),'url'=>'#','linkOptions'=>array('onclick'=>'toggleEmailForm(); return false;')),
	array('label'=>Yii::t('app','Attach a File/Photo'),'url'=>'#','linkOptions'=>array('onclick'=>'toggleAttachmentForm(); return false;')),
	array('label'=>Yii::t('services','Create Web Form'), 'url'=>array('createWebForm')),
);
$menuItems[] = array(
	'label' => Yii::t('app', 'Print Record'), 
	'url' => '#',
	'linkOptions' => array (
		'onClick'=>"window.open('".
			Yii::app()->createUrl('/site/printRecord', array (
				'modelClass' => 'Services', 
				'id' => $model->id, 
				'pageTitle' => Yii::t('app', 'Service Case').': '.$model->name
			))."');"
	)
);
$modelType = json_encode("Services");
$modelId = json_encode($model->id);
Yii::app()->clientScript->registerScript('widgetShowData', "
$(function() {
	$('body').data('modelType', $modelType);
	$('body').data('modelId', $modelId);
});");
$this->actionMenu = $this->formatMenu($menuItems, $authParams);
$themeUrl = Yii::app()->theme->getBaseUrl();
?>
<div class="page-title icon services">
<?php //echo CHtml::link('['.Yii::t('contacts','Show All').']','javascript:void(0)',array('id'=>'showAll','class'=>'right hide','style'=>'text-decoration:none;')); ?>
<?php //echo CHtml::link('['.Yii::t('contacts','Hide All').']','javascript:void(0)',array('id'=>'hideAll','class'=>'right','style'=>'text-decoration:none;')); ?>
	<h2><?php echo Yii::t('services','Case {n}',array('{n}'=>$model->id)); ?></h2>
	<?php //if(Yii::app()->user->checkAccess('ServicesUpdate',$authParams)){ ?>
	<a class="x2-button icon edit right" href="<?php echo $this->createUrl('update',array('id'=>$model->id));?>"><span></span></a>
    <?php
    echo CHtml::link(
        '<img src="'.Yii::app()->request->baseUrl.'/themes/x2engine/images/icons/email_button.png'.
            '"></img>', '#',
        array(
            'class' => 'x2-button icon right email',
            'title' => Yii::t('app', 'Open email form'),
            'onclick' => 'toggleEmailForm(); return false;',
            'style' => (empty($model->contactId) ? "display:none" : '')
        )
    );
    ?>
	<?php //} ?>
</div>
<div id="main-column" class="half-width">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'services-form',
	'enableAjaxValidation'=>false,
	'action'=>array('saveChanges','id'=>$model->id),
));
$this->renderPartial('application.components.views._detailView',array('model'=>$model,'form'=>$form,'modelName'=>'services'));

?>

<?php $childCases = Services::model()->findAllByAttributes(array('parentCase'=>$model->id)); ?>
<?php if($childCases) { ?>
	<div id="service-child-case-wrapper" class="x2-layout form-view">
	<div class="formSection showSection">
		<div class="formSectionHeader">
			<span class="sectionTitle"><?php echo Yii::t('services', 'Child Cases'); ?></span>
		</div>
		<div id="parent-case" class="tableWrapper" style="min-height: 75px; padding: 5px;">
			<?php
				$comma = false;
				foreach($childCases as $c) {
					if($comma) { // skip the first comma
						echo ", ";
					} else {
						$comma = true;
					}
					echo $c->createLink();
				}
			?>
		</div>
	</div>
	</div>
<?php } ?>

<?php
$this->endWidget();

if($model->contactId) { // every service case should have a contact associated with it
	$contact = Contacts::model()->findByPk($model->contactId);
	if($contact) { // if associated contact exists, display mini contact view
		?>
		<div id='contact-info-container'>
		<h2> <?php echo Yii::t('actions','Contact Info'); ?> </h2>
		<?php
		$this->renderPartial('application.modules.contacts.views.contacts._detailViewMini',array('model'=>$contact, 'serviceModel'=>$model));
		?>
		</div>
		<?php
	}
}
?>

<?php 
	$this->widget('X2WidgetList', array(
		'block'=>'center', 
		'model'=>$model, 
		'modelType'=>'services'
	)); 
?>


<?php $this->widget('Attachments',array('associationType'=>'services','associationId'=>$model->id,'startHidden'=>true)); ?>

<?php
$to = null;
if(isset($contact))
	$to = '"'.$contact->name.'" <'.$contact->email.'>, ';
$this->widget('InlineEmailForm', array(
	'attributes' => array(
		'to' => $to,
		'modelName' => 'Services',
		'modelId' => $model->id,
	),
	'startHidden' => true,
		)
);
?>

</div>
<div class="history half-width">
<?php
$this->widget('Publisher',
	array(
		'associationType'=>'services',
		'associationId'=>$model->id,
		'assignedTo'=>Yii::app()->user->getName(),
		'halfWidth'=>true
	)
);

$this->widget('History',array('associationType'=>'services','associationId'=>$model->id));
?>
</div>

<?php $this->widget('CStarRating',array('name'=>'rating-js-fix', 'htmlOptions'=>array('style'=>'display:none;'))); ?>

