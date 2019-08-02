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




Yii::app()->clientScript->registerCssFile (Yii::app()->theme->baseUrl.'/css/importexport.css');
?>
<div class="page-title icon contacts"><h2>
    <?php echo Yii::t('contacts','Export {model}', array(
        '{model}'=>$modelDisplayName,
    )); ?>
</h2></div>

<div class="form">

<?php if (!empty($model)) {
    echo '<div style="width:600px;">';
    echo Yii::t('admin', 'Please click the button below to begin the export. Do not close this '.
        'page until the export is finished, which may take some time if you have a large number '.
        'of records. A counter will keep you updated on how many records have been successfully '.
        'updated.'); ?><br><br>
    <?php if (isset($listName)) {
        echo Yii::t('admin','You are currently exporting: ')."<b>$listName</b>";
    } ?>
    </div>

    <div class="exportOption">
        <?php 
        if (Yii::app()->params->isAdmin) {
            echo CHtml::label(Yii::t('admin', 'Include Hidden Records?'), 'includeHidden');
            echo CHtml::checkbox('includeHidden', false); 
            echo X2Html::hint2 (Yii::t('admin', 'Include records that were hidden by the duplicate checker.'));
        }
        ?>
    </div>

    <div class="exportOption">
        <?php 
        echo CHtml::label(Yii::t('admin', 'Include Tags?'), 'includeTags');
        echo CHtml::checkbox('includeTags', false); 
        ?>
    </div>

    <h3><?php echo Yii::t('admin', 'Customize CSV') .
        CHtml::link(X2Html::minimizeButton(array(), '#exportSeparator', true, false), '#'); ?></h3>
    <div id='exportSeparator' style='display:none'>
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
    </div>
    <br>

    <?php echo CHtml::button(Yii::t('app','Export'),array('class'=>'x2-button','id'=>'export-button')); ?>

    <div id="status-text"></div>

    <div style="display:none" id="download-link-box">
        <?php echo Yii::t('admin','Please click the link below to download {model}.', array('{model}'=>$model));?><br><br>
        <a class="x2-button" id="download-link" href="#"><?php echo Yii::t('app','Download');?>!</a>
    </div>

    <?php Yii::app()->clientScript->registerScript('recordExportVariables', "
        if (typeof x2 == 'undefined')
            x2 = {};
        if (typeof x2.recordExport == 'undefined')
            x2.recordExport = {
                'modelName': '".$model."',
                'dlUrl': '".$this->createUrl('/admin/downloadData')."',
                'dlFile': '".$_SESSION['modelExportFile']."',
                'exportMessages': {
                    'init': ".CJSON::encode(Yii::t('admin',
                        'Exporting <b>{model}</b> data...', array('{model}'=>$model))).",
                    'progress': ".CJSON::encode(Yii::t('admin',
                        'records from <b>{model}</b> successfully exported.', array('{model}'=>$model))).",
                    'finished': ".CJSON::encode(Yii::t('admin',
                        'All {model} data successfully exported.', array('{model}'=>$model))).",
                    'complete': ".CJSON::encode(Yii::t('admin','Export Complete!')).",
                    'exportFormatError': ".CJSON::encode(Yii::t('admin','There was an error saving the export.')).",
                    'invalidParams': ".CJSON::encode(Yii::t('admin', 'Invalid CSV parameters! Delimeter '.
                        'and enclosure can only be a single character'))."
                }
            };
    ", CClientScript::POS_HEAD);

    Yii::app()->clientScript->registerScript('recordExportJs', "
    /**
     *
     */
    x2.recordExport.prepareModelExport = function() {
        var includeTags = $('#includeTags').is (':checked');
        var exportTargetParams = x2.exportFormats.readExportFormatOptions();

        $.ajax({
            url:'prepareModelExport?' + exportTargetParams,
            data: {
                model: x2.recordExport.modelName,
                includeTags: includeTags,
                delimeter: x2.recordExport.delimeter,
                enclosure: x2.recordExport.enclosure
            },
            success:function(data) {
                x2.recordExport.exportModelData(0);
            },
            error: function(data) {
                var resp = JSON.parse(data['responseText']);
                $('#status-text').html (resp['message'])
                    .addClass ('flash-error')
                    .css ('color', 'red')
                    .show();
            }
        });
    }

    /**
     * Recursively make ajax calls to export the requested records
     * @param int page Page number
     */
    x2.recordExport.exportModelData = function (page) {
        if($('#export-status').length==0){
            $('#status-text').append('<div id=\'export-status\'>' +
                x2.recordExport.exportMessages['init'] + '<br></div>');
        }
        $('#export-button').hide();

        var includeHidden = $('#includeHidden').is(':checked');
        $.ajax({
            url:'exportModelRecords',
            data: {
                page: page,
                model: x2.recordExport.modelName,
                includeHidden: includeHidden
            },
            success: function (data) {
                var msg = JSON.parse(data['message']);
                if (msg['page'] && msg['page'] > 0){
                    $('#export-status').html (((msg['page'])*100)+ ' ' +
                        x2.recordExport.exportMessages['progress'] + '<br>');
                    x2.recordExport.exportModelData(msg['page']);
                } else {
                    var success = msg['success'];
                    var dlUrl = msg['dlUrl'];

                    if (success) {
                        $('#export-status').html (x2.recordExport.exportMessages['finished'] + '<br>');
                        if (dlUrl !== '') {
                            x2.recordExport.dlFile = dlUrl;
                            $('#download-link-box').show();
                        }
                        alert (x2.recordExport.exportMessages['complete']);
                    } else {
                        alert (x2.recordExport.exportMessages['exportFormatError']);
                    }
                }
            }
        });
    }

    /**
     * Set up event listeners for export button and download link
     */
    $('#export-button').on('click',function(){
        x2.recordExport.delimeter = $('#delimeter').val();
        x2.recordExport.enclosure = $('#enclosure').val();
        if (x2.recordExport.delimeter.length != 1 || x2.recordExport.enclosure.length != 1) {
            alert (x2.recordExport.exportMessages['invalidParams']);
            return false;
        }
        x2.recordExport.prepareModelExport();
    });
    $('#download-link').click(function(e) {
        e.preventDefault();  //stop the browser from following
        var queryParams = {
            file: x2.recordExport.dlFile
        };
        window.location.href = x2.recordExport.dlUrl + '?' + $.param (queryParams);
    });
    ", CClientScript::POS_READY);
} else {
    // Render list of models to choose from
    echo "<h3>".Yii::t('admin','Please select a module to export from.')."</h3>";
    foreach ($modelList as $class => $modelName) {
        echo CHtml::link($modelName, array('/admin/exportModels', 'model'=>$class))."<br />";
    }
} ?>

</div>
