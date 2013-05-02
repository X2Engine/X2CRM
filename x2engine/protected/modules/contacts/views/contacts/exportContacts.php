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
<?php echo CHtml::button('Export',array('class'=>'x2-button','id'=>'export-button')); ?>
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
       $('#status-text').append("<div id='contacts-status'>Exporting <b>Contact</b> data...<br></div>"); 
    }
    $.ajax({
        url:'exportSet?page='+page,
        success:function(data){
            if(data>0){
                $('#contacts-status').html(((data)*100)+" records from <b>Contacts</b> successfully exported.<br>");
                exportContactData(data);
            }else{
                $('#contacts-status').html("All Contact data successfully exported.<br>");
                $('#download-link-box').show();
                alert("Export Complete!");
            }
        }
    });
}   
$('#download-link').click(function(e) {
    e.preventDefault();  //stop the browser from following
    window.location.href = '<?php echo $this->createUrl('/admin/downloadData',array('file'=>$_SESSION['contactExportFile'])); ?>';
});</script>
</div>