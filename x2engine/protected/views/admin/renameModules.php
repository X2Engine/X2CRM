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
<div class="page-title"><h2><?php echo Yii::t('admin','Rename A Module'); ?></h2></div>
<div class="form">
<?php echo Yii::t('admin','You can rename a module by selecting a module and typing the new name below.'); ?>
<br><br>

<h2><?php echo Yii::t('admin','Rename A Module'); ?></h2>
<?php echo CHtml::form('renameModules','post',array('enctype'=>'multipart/form-data')); ?>
<?php echo CHtml::dropDownList('module', '', $modules); ?> <br><br>
<?php echo CHtml::textField('name'); ?>
<?php echo CHtml::submitButton(Yii::t('app','Submit'),array('class'=>'x2-button')); ?>
<?php echo CHtml::endForm(); ?> </div>