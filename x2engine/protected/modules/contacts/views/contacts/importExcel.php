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
	array('label'=>Yii::t('contacts','Import Contacts')),
	array('label'=>Yii::t('contacts','Export to CSV'),'url'=>array('exportContacts')),
));

?>
<div class="page-title icon contacts"><h2><?php echo Yii::t('contacts','Import Contacts from Template'); ?></h2></div>
<div class="form">
<div id="info-box" style="width:600px;">
<?php echo Yii::t('contacts','To import your contacts, please fill out a CSV file where the first row contains the column headers for your records (e.g. first_name, last_name, title etc.).  A properly formatted example can be found below.'); ?>
<br><br>
<?php echo Yii::t('contacts','The application will attempt to automatically map your column headers to our fields in the database.  If a match is not found, you will be given the option to choose one of our fields to map to, ignore the field, or create a new field within X2.'); ?>
<br><br>
<?php echo Yii::t('contacts','If you decide to map the "Create Date", "Last Updated", or any other explicit date field, be sure that you have a valid date format entered so that the software can convert to a UNIX Timestamp (if it is already a UNIX Timestamp even better).  Visibility should be either "1" for Public or "0" for Private (it will default to 1 if not provided).'); ?>

<br><br><?php echo Yii::t('contacts','Example');?> <a href="#" id="example-link">[+]</a>
<div id="example-box" style="display:none;"><img src="<?php echo Yii::app()->getBaseUrl()."/images/examplecsv.png" ?>"/></div>
<br><br>
</div>
<div class="form" style="width:600px;">
<?php if(!empty($errors)){
    echo "<span class='error' style='font-weight:bold'>".Yii::t('admin',$errors)."</span>";
    unset($_SESSION['errors']);
} ?>
<h3><?php echo Yii::t('contacts','Upload File'); ?></h3>
<?php echo CHtml::form('importExcel','post',array('enctype'=>'multipart/form-data','id'=>'importExcel')); ?>
<?php echo CHtml::fileField('contacts', '', array('id'=>'contacts')); ?> <br>
<i><?php echo Yii::t('app','Allowed filetypes: .csv'); ?> </i>
<br><br>
<?php echo CHtml::submitButton(Yii::t('app','Submit'),array('class'=>'x2-button')); ?> 
<?php echo CHtml::endForm(); ?> 
</div>
</div>
<script>
    $('#example-link').click(function(){
       $('#example-box').toggle(); 
    });
    $(document).on('submit','#importExcel',function(){
        var fileName=$("#contacts").val();
        var pieces=fileName.split('.');
        var ext=pieces[pieces.length-1];
        if(ext!='csv'){
            $("#contacts").val("");
            alert("File must be a .csv file.");
            return false;
        }
    });
</script>
    
