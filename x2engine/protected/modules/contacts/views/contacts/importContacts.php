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
	array('label'=>Yii::t('contacts','All Contacts'),'url'=>array('index')),
	array('label'=>Yii::t('contacts','Lists'),'url'=>array('lists')),
	array('label'=>Yii::t('contacts','Create Contact'),'url'=>array('create')),
	// array('label'=>Yii::t('contacts','Import from Outlook')),
	array('label'=>Yii::t('contacts','Import Contacts')),
	array('label'=>Yii::t('contacts','Export to CSV'),'url'=>array('exportContacts')),
));

?>
<h2><?php echo Yii::t('contacts','Import Contacts from Outlook'); ?></h2>
<?php echo Yii::t('contacts','To import your contacts from Outlook, please first create a CSV file (DOS format) by opening outlook and exporting the contacts. Then, upload the file here using the form below.'); ?>
<br><br>

<h3><?php echo Yii::t('contacts','Upload File'); ?></h3>
<?php echo CHtml::form('importContacts','post',array('enctype'=>'multipart/form-data')); ?>
<?php echo CHtml::fileField('contacts', '', array('id'=>'contacts')); ?> <br><br>
<?php echo CHtml::submitButton(Yii::t('app','Submit')); ?> 
<?php echo CHtml::endForm(); ?> 