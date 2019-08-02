<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/importexport.css');
?>
<div class="page-title"><h2><?php echo Yii::t('admin', 'Export All Data'); ?></h2></div>
<div class="form">
    <?php echo Yii::t('admin', 'This page will export all data from all modules into a CSV file. This CSV can be re-imported to another X2Engine installation as is without any formatting changes.') ?>
    <br><br>
    <?php
    echo Yii::t('admin', 'Which modules would you like to export data from?');
    echo "<br><br><h2>";
    echo Yii::t('admin', 'Available Models')."</h2>";

    foreach($modelList as $model => $array){
        echo "<div>";
        echo CHtml::checkBox("$model", true, array('class' => 'model-checkbox','style'=>'margin-right:5px;'));
        echo "<label style='display:inline-block;'>".$array['name']." ".Yii::t('admin','({n} records)',array('{n}'=>$array['count']))."</label>";
        echo "</div>";
    }

    echo "<br />";
    echo CHtml::checkBox('select-all', true, array('id' => 'toggle-checkbox', 'style'=>'margin-right:5px;'));
    echo CHtml::label(Yii::t("admin", "Select All"), 'select-all', array('style'=>'display:inline-block;'));
?>

    <h3><?php echo Yii::t('admin', 'Customize CSV') .
        X2Html::minimizeButton (array('class' => 'pseudo-link'), '#importSeparator'); ?></h3>

    <div id='importSeparator' style='display:none'>
        <?php
            echo CHtml::label(Yii::t('admin', 'Delimeter'), 'delimeter');
            echo CHtml::textField('delimeter', ',').'<br />';
            echo CHtml::label(Yii::t('admin', 'Enclosure'), 'enclosure');
            echo CHtml::textField('enclosure', '"');
        ?>
    </div>

    <h3><?php echo Yii::t ('admin', 'Format Options').
        CHtml::link(X2Html::minimizeButton (array(), '#exportFormat', true, false), '#'); ?></h3>

    <div id="exportFormat">
        <?php $this->renderPartial ('application.components.views._exportFormat'); ?>
    </div><br /><br />

    <?php
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

<?php Yii::app()->clientScript->registerScript ('globalExportJs', "
    if (typeof x2 === 'undefined')
        x2 = {};
    if (typeof x2.export === 'undefined')
        x2.export = {};
    x2.export.globalExportFile = 'data.csv';

    x2.export.finishExport = function() {
        $.ajax({
            url:'finishGlobalExport',
            success:function(data){
                if (data !== '') {
                    x2.export.globalExportFile = data;
                    $('#download-link-box').show();
                }
                alert('Export Complete!');
            }
        });
    };

    x2.export.exportData = function (models,i, page) {
        if($('#'+models[i]+'-status').length==0){
            $('#status-text').append('<div id=\''+models[i]+'-status\'>Exporting data from: <b>'+models[i]+'</b><br></div>');
        }
        $.ajax({
            url:'globalExport?model='+models[i]+'&page='+page,
            success:function(data){
                if(data>0){
                    $('#'+models[i]+'-status').html(((data)*100)+' records from: <b>'+models[i]+'</b> successfully exported.<br>');
                    x2.export.exportData(models,i,data);
                }else{
                    $('#'+models[i]+'-status').html('All data from: <b>'+models[i]+'</b> successfully exported.<br>');
                    if(i==models.length-1)
                        x2.export.finishExport();
                    else
                        x2.export.exportData(models,i+1,0);
                }
            }
        });
    }

    x2.export.prepareFile = function (){
        $('#status-text').html('');
        $('#download-link-box').hide();
        var exportTargetParams = x2.exportFormats.readExportFormatOptions();

        $.ajax({
            'url':'prepareExport?' + exportTargetParams,
            data: {
                'delimeter': $('#delimeter').val(),
                'enclosure': $('#enclosure').val()
            },
            success:function(){
                $('#status-text').append('Data file prepared.<br>');
                var models = x2.export.getModelList();
                var i=0;
                var page=0;
                x2.export.exportData(models,i, page);
            }
        });
    }

    x2.export.getModelList = function (){
        var models=new Array();
        $('.model-checkbox').each (function() {
            if ($(this).is (':checked')) {
                if($(this).attr('name')=='Workflow'){
                    // Add process and process stages to beginning of model list to
                    // satisfy constraints when the data is imported
                    models.unshift('WorkflowStage');
                    models.unshift('Workflow');
                } else if ($(this).attr('name')=='X2Calendar'){
                    models.unshift('X2Calendar');
                } else {
                    models.push($(this).attr('name'));
                    if($(this).attr('name')=='User'){
                        models.push('Profile');
                    }
                }
            }
        });
        return models;
    }

    x2.export.toggleAll = function (elem) {
        if ($(elem).is(':checked')) {
            $('.model-checkbox').attr('checked', 'checked');
        } else {
            $('.model-checkbox').removeAttr('checked');
        }
    }

    $('#toggle-checkbox').change (function(){
        x2.export.toggleAll($(this));
    });

    $('#export-button').click (function(){
        $(this).hide();
        x2.export.prepareFile();
    });

    $('#download-link').click(function(e) {
        e.preventDefault();  //stop the browser from following
        window.location.href = 'downloadData?file=' + x2.export.globalExportFile;
    });
", CClientScript::POS_READY);
