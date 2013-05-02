<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
    
