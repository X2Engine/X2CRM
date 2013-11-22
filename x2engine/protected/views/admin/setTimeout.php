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
?>
<h2><?php echo Yii::t('admin','Set Timeout'); ?></h2>
<?php echo Yii::t('admin','Set user session time out (in seconds) here. Default is 1 hour.'); ?>
<br /><br />
<div class="form">
<?php
$form=$this->beginWidget('CActiveForm', array(
		'id'=>'timeout-form',
		'enableAjaxValidation'=>false,
	));
?>
	<div class="row">
<?php echo $form->labelEx($admin,'timeout'); ?>
<?php echo $form->textField($admin,'timeout'); ?>
<?php echo $form->errorSummary($admin,'timeout'); ?>
	</div>
<?php echo CHtml::submitButton($admin->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button'))."\n";?>
<?php $this->endWidget();?></div>
