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

$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('services','All Cases'), 'url'=>array('index')),
	array('label'=>Yii::t('services','Create Case')),
	array('label'=>Yii::t('services','Create Web Form'), 'url'=>array('createWebForm')),
));
?>
<div class="page-title icon services"><h2><?php echo Yii::t('services','Create Case'); ?></h2></div>

<?php echo $this->renderPartial('application.components.views._form', array('model'=>$model, 'users'=>$users,'modelName'=>'services')); ?>

<?php
$createContactUrl = $this->createUrl('/contacts/contacts/create');
$contactTooltip = json_encode(Yii::t('contacts', 'Create a new Contact'));

Yii::app()->clientScript->registerScript('create-model', "
	$(function() {
		// init create contact button
		$('#create-contact').initCreateContactDialog('$createContactUrl', 'Services', '{$model->id}', '', '', '', '', $contactTooltip, '', '', '');
	});
");
?>

<?php $this->widget('CStarRating',array('name'=>'rating-js-fix', 'htmlOptions'=>array('style'=>'display:none;'))); ?>
