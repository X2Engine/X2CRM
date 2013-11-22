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
	// array('label'=>Yii::t('contacts','Import from Outlook'),'url'=>array('importContacts')),
	array('label'=>Yii::t('contacts','Import from Template'),'url'=>array('importExcel')),
	array('label'=>Yii::t('contacts','Export to CSV')),
));

?>
<div class="page-title icon contacts"><h2><?php echo Yii::t('contacts','Export Contacts'); ?></h2></div>
<div class="form">
<?php echo Yii::t('contacts','Please click the link below to download contacts.');?><br><br>
<a class="x2-button" id="download-link" href="#"><?php echo Yii::t('app','Download');?>!</a>
<script>
$('#download-link').click(function(e) {
    e.preventDefault();  //stop the browser from following
    window.location.href = '<?php echo $this->createUrl('/admin/downloadData',array('file'=>'contact_export.csv')); ?>';
});</script>
</div>