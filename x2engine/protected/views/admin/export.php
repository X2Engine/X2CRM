<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
?>
<div class="page-title"><h2><?php echo Yii::t('admin', 'Export All Data'); ?></h2></div>
<div class="form">
    <?php echo Yii::t('admin', 'This page will export all data from all modules into a CSV file. This CSV can be re-imported to another X2CRM installation as is without any formatting changes.') ?>
    <br><br>
    <?php
    echo Yii::t('admin', 'Which modules would you like to export data from?');
    echo "<br><br><h2>";
    echo Yii::t('admin', 'Available Models')."</h2>";

    foreach($modelList as $model => $array){
        echo "<div><label style='display:inline-block;'>".$array['name']." ".Yii::t('admin','({n} records)',array('{n}'=>$array['count']))."</label>";
        echo CHtml::checkBox("$model", true, array('class' => 'model-checkbox','style'=>'margin-left:5px;'));
        echo "</div>";
    }
    echo CHtml::button(Yii::t('app','Export'), array('class' => 'x2-button', 'id' => 'export-button'));
    ?>
    <div id="status-text" style="color:green">

    </div>
    <br>
    <div style="display:none" id="download-link-box">
<?php echo Yii::t('admin', 'Please click the link below to download data.'); ?><br><br>
        <a class="x2-button" id="download-link" href="#"><?php echo Yii::t('app', 'Download'); ?>!</a>
    </div>
</div>

<script>
    $('#export-button').on('click',function(){
        prepareFile();
    });
    function exportData(models,i, page){
        if($('#'+models[i]+'-status').length==0){
            $('#status-text').append("<div id='"+models[i]+"-status'>Exporting data from: <b>"+models[i]+"</b><br></div>");
        }
        $.ajax({
            url:'globalExport?model='+models[i]+'&page='+page,
            success:function(data){
                if(data>0){
                    $('#'+models[i]+'-status').html(((data)*100)+" records from: <b>"+models[i]+"</b> successfully exported.<br>");
                    exportData(models,i,data);
                }else{
                    if(i==models.length-1){
                        $('#'+models[i]+'-status').html("All data from: <b>"+models[i]+"</b> successfully exported.<br>");
                        $('#download-link-box').show();
                        alert("Export Complete!");
                    }else{
                        $('#'+models[i]+'-status').html("All data from: <b>"+models[i]+"</b> successfully exported.<br>");
                        exportData(models,i+1,0);
                    }
                }
            }
        });
    }
    function prepareFile(){
        $('#status-text').html('');
        $('#download-link-box').hide();
        $.ajax({
            'url':'prepareExport',
            success:function(){
                $('#status-text').append("Data file prepared.<br>");
                var models=getModelList();
                var i=0;
                var page=0;
                exportData(models,i, page);
            }
        });
    }
    function getModelList(){
        var models=new Array();
        $('.model-checkbox').each(function(){
            if($(this).attr('checked')=='checked'){
                models.push($(this).attr('name'));
                if($(this).attr('name')=="User"){
                    models.push("Profile");
                }
                if($(this).attr('name')=="Workflow"){
                    models.push("WorkflowStage");
                }
            }
        });
        return models;

    }
    $('#download-link').click(function(e) {
        e.preventDefault();  //stop the browser from following
        window.location.href = 'downloadData?file=data.csv';
    });
</script>