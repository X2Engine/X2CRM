<?php

/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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

Yii::app()->clientScript->registerCssFile (Yii::app()->theme->baseUrl.'/css/importexport.css');
?>
<div class="page-title icon contacts"><h2><?php echo Yii::t('contacts','Export {model}', array('{model}'=>Modules::displayName(true, $model))); ?></h2></div>
<div class="form">
    
<?php if (!empty($model)) { ?>
    <?php echo '<div style="width:600px;">'; ?>
    <?php echo Yii::t('admin','Please click the button below to begin the export. Do not close this page until the export is finished, which may take some time if you have a large number of records. A counter will keep you updated on how many records have been successfully updated.'); ?><br><br>
    <?php echo isset($listName)?Yii::t('admin','You are currently exporting: ')."<b>$listName</b>":''; ?>
    </div>
    <br>
    <?php
    if (is_null($listId)) {
        echo CHtml::label(Yii::t('admin', 'Include Hidden Records?'), 'includeHidden');
        echo CHtml::checkbox('includeHidden', false);
    } ?>
    <?php echo CHtml::button(Yii::t('app','Export'),array('class'=>'x2-button','id'=>'export-button')); ?>
    <div id="status-text">

    </div>

    <div style="display:none" id="download-link-box">
        <?php echo Yii::t('admin','Please click the link below to download {model}.', array('{model}'=>$model));?><br><br>
        <a class="x2-button" id="download-link" href="#"><?php echo Yii::t('app','Download');?>!</a>
    </div>
    <script>
$('#export-button').on('click',function(){
    exportModelData(0);
});
function exportModelData(page){
    var includeHidden = $("#includeHidden").is(':checked');
    if($('#export-status').length==0){
       $('#status-text').append("<div id='export-status'><?php echo Yii::t('admin','Exporting <b>{model}</b> data...', array('{model}'=>$model)); ?><br></div>");
    }
    $('#export-button').hide();
    $.ajax({
        url:'exportModelRecords?page='+page+'&model=<?php echo $model; ?>&includeHidden=' + includeHidden,
        success:function(data){
            if(data>0){
                $('#export-status').html(((data)*100)+" <?php echo Yii::t('admin','records from <b>{model}</b> successfully exported.', array('{model}'=>$model));?><br>");
                exportModelData(data);
            }else{
                $('#export-status').html("<?php echo Yii::t('admin','All {model} data successfully exported.', array('{model}'=>$model));?><br>");
                $('#download-link-box').show();
                alert("<?php echo Yii::t('admin','Export Complete!');?>");
            }
        }
    });
}
$('#download-link').click(function(e) {
    e.preventDefault();  //stop the browser from following
    window.location.href = '<?php echo $this->createUrl('/admin/downloadData',array('file'=>$_SESSION['modelExportFile'])); ?>';
});</script>
<?php } else {
    echo "<h3>".Yii::t('admin','Please select a module to export from.')."</h3>";
    foreach ($modelList as $class => $modelName) {
        echo CHtml::link($modelName, array('/admin/exportModels', 'model'=>$class))."<br />";
    }
} ?>

</div>
