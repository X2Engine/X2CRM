<?php
/* * *******************************************************************************
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
 * ****************************************************************************** */
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