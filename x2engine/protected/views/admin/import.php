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
<div class="page-title"><h2><?php echo Yii::t('admin','Import Data from Template'); ?></h2></div>
<div class="form">
<?php echo Yii::t('admin','To import your data a CSV file, please  upload the file here using the form below.'); ?>
<br><br>
<?php echo Yii::t('admin','This import has a very specific style of data formatting required to be used. To get a better example of the formatting, export a set of data and look at how it is formatted.  A brief description is also provided here.'); ?>
<br><br>
<?php echo Yii::t('admin','The first cell of the CSV should be the version from which data was exported.  If it is a fresh set of data or was not exported, use the current version.');?>
<br><br>
<?php echo Yii::t('admin','Each record type should have a set of column names as metadata with the type of record (e.g. "Contacts" or "Accounts" at the end.  Each record should also have the record type as the last column.'); ?>
<br><br>
<h3><?php echo Yii::t('contacts','Upload File'); ?></h3>
<?php echo CHtml::form('import','post',array('enctype'=>'multipart/form-data','id'=>'file-form')); ?>
<?php echo CHtml::fileField('data', '', array('id'=>'data')); ?> <br><br>
<?php echo Yii::t('admin','Overwrite old data?');?><br>
<?php echo CHtml::dropDownList('overwrite', '', array('0'=>Yii::t('app','No'),'1'=>Yii::t('app','Yes')),array('id'=>'overwrite-selector')); ?> <br><br>
<?php echo CHtml::submitButton(Yii::t('app','Submit'),array('class'=>'x2-button','id'=>'import-button')); ?>
<?php echo CHtml::endForm(); ?>
</div>