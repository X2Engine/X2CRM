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
<div style="width:600px;">
    <?php echo Yii::t('contacts','Please click the button below to begin the export. Do not close this page until the export is finished, which may take some time if you have a large number of records. A counter will keep you updated on how many records have been successfully updated.') ?><br><br>
    <?php echo Yii::t('contacts','You are currently exporting: ');?><b><?php echo $listName; ?></b>


</div>
<br>
<?php echo CHtml::button(Yii::t('app','Export'),array('class'=>'x2-button','id'=>'export-button')); ?>
<div id="status-text" style="color:green">

</div>

<div style="display:none" id="download-link-box">
    <?php echo Yii::t('contacts','Please click the link below to download contacts.');?><br><br>
    <a class="x2-button" id="download-link" href="#"><?php echo Yii::t('app','Download');?>!</a>
</div>
<script>
$('#export-button').on('click',function(){
    exportContactData(0);
});
function exportContactData(page){
    if($('#contacts-status').length==0){
       $('#status-text').append("<div id='contacts-status'><?php echo Yii::t('contacts','Exporting <b>Contact</b> data...'); ?><br></div>");
    }
    $.ajax({
        url:'exportSet?page='+page,
        success:function(data){
            if(data>0){
                $('#contacts-status').html(((data)*100)+" <?php echo Yii::t('contacts','records from <b>Contacts</b> successfully exported.');?><br>");
                exportContactData(data);
            }else{
                $('#contacts-status').html("<?php echo Yii::t('contacts','All Contact data successfully exported.');?><br>");
                $('#download-link-box').show();
                alert("<?php echo Yii::t('contacts','Export Complete!');?>");
            }
        }
    });
}
$('#download-link').click(function(e) {
    e.preventDefault();  //stop the browser from following
    window.location.href = '<?php echo $this->createUrl('/admin/downloadData',array('file'=>$_SESSION['contactExportFile'])); ?>';
});</script>
</div>