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
Tours::loadTips('admin.processModels');
?>
<script>
var record=0;
</script>
<?php
    if (isset($model))
        unset($_SESSION['model']);
?>
<div class="page-title"><h2><?php echo Yii::t('admin','{model} Import', array('{model}'=>X2Model::getModelTitle ($model))); ?></h2></div>
<div class="form" >
<div style="width:600px">
<?php
if ($preselectedMap) {
    echo Yii::t('admin', 'You have selected to use the following import mapping: ').$_SESSION['mapName']."<br><br>";
    ?>
    <table id="importMapSummary">
    <tr>
        <td><strong><?php echo Yii::t('admin','Your Field'); ?></strong></td>
        <td><strong><?php echo Yii::t('admin','Our Field'); ?></strong></td>
    </tr>
    <?php foreach ($importMap as $key => $val) { ?>
        <tr>
            <td style='width: 50%'><?php echo $key; ?></td>
            <td style='width: 50%'><?php echo (empty($val) ? Yii::t('admin', 'DO NOT MAP'): $val); ?></td>
        </tr>
    <?php
    }
    echo "</table>";
    echo CHtml::link(Yii::t('admin', 'Edit'), '#', array('id' => 'editPresetMap', 'class' => 'x2-button')).'<br /><br />';
} else {
    echo Yii::t('admin',"First, we'll need to make sure your fields have mapped properly for import. ");
    echo Yii::t('admin','Below is a list of our fields, the fields you provided, and a few sample records that you are importing.')."<br /><br />";
    echo Yii::t('admin','If the ID field is selected to be imported, the import tool will attempt to overwrite pre-existing records with that ID. Do not map the ID field if you don\'t want this to happen.');
    echo Yii::t('admin', 'Select the fields you wish to map. Fields that have been detected as matching an existing field have been selected.').'<br /><br />';
    echo Yii::t('admin', 'Fields that are not selected will not be mapped. To override a mapping, select the appropriate field from the corresponding drop down.').'<br /><br />';
    echo Yii::t('admin','Selecting "DO NOT MAP" will ignore the field from your CSV, and selecting "CREATE NEW FIELD" will generate a new text field within X2 and map your field to it.').'<br /><br />';
}
$maxExecTime = ini_get('max_execution_time');
if ($maxExecTime <= 30) {
    echo '<div class="flash-notice">'.Yii::t('admin', 'Warning: This server is configured with a short maximum execution time. This can result in the import being terminated before completion. You may wish to increase'
        .' this value. The current maximum execution time is {exec_time} seconds.', array('{exec_time}' => $maxExecTime)).'</div>';
}
?>

</div><br /></div>
<div id="import-container" class='form'>
<div id="super-import-map-box">
<h2><span class="import-hide">
    <?php echo Yii::t('admin', 'Import Map').
        X2Html::minimizeButton (array(), '#import-map-box', false, true);
?></span></h2>
<div id="import-map-box" class="import-hide form" style="width:600px">
</br />

<div id='mapping-overrides'>
<?php echo Yii::t('admin','Below is a list of our fields, the fields you provided, and a few sample records that you are importing. ');?>
<?php echo Yii::t('admin','Selecting "DO NOT MAP" will ignore the field. Selecting "CREATE NEW FIELD" will generate a new text field within X2 and map your field to it. Selecting "APPLY TAGS" will treat the attribute as a list of tags and apply each tag to the imported record.') ?>
<br /><br />
<table id="import-map" >
    <tr>
        <td><strong><?php echo Yii::t('admin','Your Field');?></strong></td>
        <td><strong><?php echo Yii::t('admin','Our Field');?></strong></td>
        <td><strong><?php echo Yii::t('admin','Sample Record');?></strong> <a href="#" class="clean-link" onclick="x2.importer.prevRecord();"><?php echo Yii::t('admin','[Prev]');?></a> <a href="#" class="clean-link" onclick="x2.importer.nextRecord();"><?php echo Yii::t('admin','[Next]');?></a></td>
    </tr>
<?php
    foreach($meta as $attribute){
        echo "<tr>";
        echo "<td style='width:33%'>$attribute</td>";
        echo "<td style='width:33%'>".CHtml::dropDownList(
                $attribute,
                isset ($importMap[$attribute]) ? $importMap[$attribute] : $defaultMapping,
                array_merge(
                    array(
                        '' => Yii::t('admin','DO NOT MAP'),
                        'createNew' => Yii::t('admin','CREATE NEW FIELD'),
                        'applyTags'=>Yii::t('admin','APPLY TAGS')
                    ),
                    X2Model::model($model)->attributeLabels()
                ),
                array('class'=>'import-attribute')
            )."</td>";
        echo "<td style='width:33%'>";
        for ($i=0; $i < count($sampleRecords); $i++) {
            if (isset($sampleRecords[$i])) {
                if ($i>0) {
                    echo "<span class='record-$i' id='record-$i-$attribute' style='display:none;'>".$sampleRecords[$i][$attribute]."</span>";
                } else {
                    echo "<span class='record-$i' id='record-$i-$attribute'>".$sampleRecords[$i][$attribute]."</span>";
                }
            }
        }
        echo "</td>";
        echo "</tr>";
    }
?>
</table>
</div>
<br />
<?php
    echo X2Html::hint(Yii::t('admin', "A meaningful description of the data source will be helpful to identify the import mapping. The mapping name will "
                ." be generated in the form '{source} to X2Engine {version}' to identify the data sources for which the import map was intended."), false);
    echo CHtml::textField("mapping-name", "", array('id'=>'mapping-name', 'placeholder'=>Yii::t('admin', 'Import Source')))."&nbsp;";
    echo CHtml::link(Yii::t('admin', 'Export Mapping'), '#', array('id'=>'export-map', 'class'=>'x2-button'));
    echo CHtml::link(Yii::t('admin', 'Download Mapping'), '#', array('id'=>'download-map', 'class'=>'x2-button', 'style'=>'display:none'));

?>
</div>
</div>
<br /><br />
<h2><?php echo Yii::t('admin','Process Import Data'); ?></h2>
<div class="form" style="width:600px">
    <div class="row">
        <div class="cell"><strong><?php echo Yii::t('admin','Create records for link fields?'); ?></strong></div>
        <div class="cell"><?php echo X2Html::hint(Yii::t('admin',"This will attempt to create a record for any field that links to another record type (e.g. Account)"),false); ?></div>
        <div class="cell"><?php echo CHtml::checkBox('create-records-box','checked');?></div>
    </div>
    <?php
     
    if (Yii::app()->contEd ('pro')) {
    ?>
    <div class="row">
        <div class="cell"><strong><?php echo Yii::t('admin','Update existing records?'); ?></strong></div>
        <div class="cell"><?php echo X2Html::hint(Yii::t('admin',"An existing record with the same ID will have its fields updated instead of being overwritten."),false); ?></div>
        <div class="cell"><?php echo CHtml::checkBox('update-records-box');?></div>

        <div id="update-field-selector" class="row" style="display:none;">
            <?php
                echo CHtml::label (Yii::t('admin', 'Match Attribute'), 'update-field', array('style' => 'float:left; padding-top: 0.5em;'));
                echo CHtml::dropdownList ('update-field', 'id', X2Model::model($model)->attributeLabels());
            ?>
        </div>
    </div>
    <?php
    }
     
    ?>
    <div class="row">
        <div class="cell"><strong><?php echo Yii::t('marketing','Tags'); ?></strong></div>
        <div class="cell"><?php echo X2Html::hint(Yii::t('admin',"These tags will be applied to any record created by the import. Example: web,newlead,urgent."),false); ?></div>
        <div class="cell"><?php echo CHtml::textField('tags'); ?></div>
    </div>
    <div class="row">
        <div class="cell"><strong><?php echo Yii::t('admin','Automatically fill certain fields?'); ?></strong></div>
        <div class="cell"><?php echo X2Html::hint(Yii::t('admin',"These fields will be applied to all imported records and override their respective mapped fields from the import."),false); ?></div>
        <div class="cell"><?php echo CHtml::checkBox('fill-fields-box');?></div>

        <div id="fields" class="row" style="display:none;">
            <div>
                <div id="field-box">

                </div>
            </div>
            &nbsp;&nbsp;&nbsp;&nbsp;<a href="#" id="add-link" class="clean-link">[+]</a>
        </div>
    </div>
    <div class="row">
        <div class="cell"><strong><?php echo Yii::t('admin','Automatically log a comment on these records?'); ?></strong></div>
        <div class="cell"><?php echo X2Html::hint(Yii::t('admin',"Anything entered here will be created as a comment and logged as an Action in the imported record's history."),false); ?></div>
        <div class="cell"><?php echo CHtml::checkBox('log-comment-box');?></div>
        <div class="row">
            <div id="comment-form" style="display:none;">
                <div class="text-area-wrapper" >
                    <textarea name="comment" id="comment" style="height:70px;"></textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="cell"><strong><?php echo Yii::t('admin','Assign records via lead-routing?'); ?></strong></div>
        <div class="cell"><?php echo X2Html::hint(Yii::t('admin',"If this box is checked, all records will be assigned to users based on your lead routing settings."),false); ?></div>
        <div class="cell"><?php echo CHtml::checkBox('lead-routing-box');?></div>
    </div>
    <div class="row">
        <div class="cell"><strong><?php echo Yii::t('admin','Skip posting new records to activity feed?'); ?></strong></div>
        <div class="cell"><?php echo X2Html::hint(Yii::t('admin',"If this box is checked, the activity feed will not be populated with the new records."),false); ?></div>
        <div class="cell"><?php echo CHtml::checkBox('activity-feed-box');?></div>
    </div>
    <div class="row">
        <div class="cell"><strong><?php echo Yii::t('admin','Batch Size'); ?></strong></div>
        <div class="cell"><?php echo X2Html::hint(Yii::t('admin',"Modify the number of records to be process per batch request."),false); ?></div>
        <div class="cell"><?php echo CHtml::textField('batch-size', 25, array('style' => 'width: 32px')); ?></div>
        <div class="cell"><?php
            $this->widget('zii.widgets.jui.CJuiSlider', array(
                'value' => 25,
                'options' => array(
                    'min' => 5,
                    'max' => 1000,
                    'step' => 5,
                    'change' => "js:function(event,ui) {
                                    $('#batch-size').val(ui.value);
                                }",
                    'slide' => "js:function(event,ui) {
                                    $('#batch-size').val(ui.value);
                                }",
                ),
                'htmlOptions' => array(
                    'style' => 'width:200px;margin:6px 9px;',
                    'id' => 'batch-size-slider',
                ),
            ));
        ?></div>
    </div>

</div>
<br /><br />
<?php echo CHtml::link(Yii::t('admin',"Process Import"),"#",array(
        'id'=>'process-link',
        'class'=>'x2-button highlight'
    ));?>
<br /><br />
</div>
<h3 id="import-status" style="display:none;"><?php echo Yii::t('admin','Import Status'); ?></h3>
<div id="import-progress-bar" style="display:none;">
<?php
    if ($csvLength !== null) {
        $this->widget('X2ProgressBar', array(
            'uid' => 'x2import',
            'max' => $csvLength,
            'label' => '',
        ));
    }
?>
</div>
<div id="prep-status-box">

</div>
<br />
<div id="status-box">

</div>
<div id="failures-box">

<div id="continue-box" style="display:none;">
<br />
<?php
    echo CHtml::link(Yii::t('admin', 'Import more {records}', array(
            '{records}' => X2Model::getModelTitle($model),
        )), 'importModels?model='.$model, array('class' => 'x2-button'));
    echo CHtml::link(Yii::t('admin', 'Import to another module'), 'importModels',
        array('class' => 'x2-button'));
    echo CHtml::link(Yii::t('admin', 'Rollback Import'),
        array('rollbackImport', 'importId' => $_SESSION['importId']),
        array('class' => 'x2-button', 'id' => 'revert-btn', 'style' => 'display:none;'));

    $importerMessages = array(
        'success' => Yii::t('admin', 'Import setup completed successfully.'),
        'begin' => Yii::t('admin', 'Beginning import.'),
        'complete' => Yii::t('admin', 'Import Complete!'),
        'failCreate' => Yii::t('admin', "Import preparation failed.  Failed to create the following fields: "),
        'failConflicting' => Yii::t('admin', "Import preparation failed.  The following fields already exist: "),
        'failRequired' => Yii::t('admin', "Import Preparation failed. The following required fields were not mapped: "),
        'confirm' => Yii::t('admin', "You have mapped multiple columns to the same field, are you sure you would like to proceed? The following fields were mapped more than once: "),
        'aborting' => Yii::t('admin', "Import preparation failed.  Aborting import."),
        
        'nonUniqueMatch' => Yii::t ('admin', 'You have selected to match on a '.
                        'non-unique attribute to update existing records. This can result in '.
                        'unintended changes to data. Are you sure you would like to proceed? '.
                        'Match attribute was: '),
        
        'nonUniqueAssocMatch' => Yii::t ('admin', 'You have selected to match link type fields on '.
                        'non-unique attributes. This can result in an association being '.
                        'formed with the incorrect record. Are you sure you would like to proceed? '.
                        'The following mappings would match on non-unique attributes: '),
        'modelsImported' => Yii::t('admin', " <b>{model}</b> have been successfully imported.", array('{model}' => $model)),
        'modelsLinked' => Yii::t('admin', "were created and linked to {model}.", array('{model}' => $model)),
        'modelsFailed' => Yii::t('admin', " <b>{model}</b> have failed validation and were not imported.", array('{model}' => $model)),
        'clickToRecover' => Yii::t('admin', 'Click here to recover them: ', array('{model}' => $model)).
                "<a href=\"#\" id=\"download-link\" class=\"x2-button\">".Yii::t('admin', "Download")."</a>",
    );

    Yii::app()->clientScript->registerScriptFile (Yii::app()->getBaseUrl().'/js/importexport.js');
?>
</div>
<?php
    Yii::app()->clientScript->registerScript ('importexport', "
    if (typeof x2 == 'undefined')
        x2 = {};
    if (typeof x2.importer == 'undefined') {
        x2.importer = {
            batchSize: 25
        };
    }

    x2.importer.model = ". CJSON::encode ($model) .";
    x2.importer.preselectedMap = ". ($preselectedMap ? 'true':'false') .";
    x2.importer.numSampleRecords = ". count($sampleRecords) .";
    x2.importer.attributeLabels = ". CJSON::encode (X2Model::model($model)->attributeLabels(), false) .";
    x2.importer.failedRecordsUrl = ". CJSON::encode ($this->createUrl ('/admin/downloadData',array('file'=>'failedRecords.csv'))) .";
    x2.importer.modifiedPresetMap = false;
    x2.importer.messageTranslations = ". CJSON::encode ($importerMessages) .";
    x2.importer.linkFieldModelMap = ". CJSON::encode ($linkFieldModelMap) .";
    x2.importer.linkedRecordDropdowns = ". CJSON::encode ($linkedRecordDropdowns) .";
    x2.importer.linkFieldHint = ". CJSON::encode(X2Html::hint (Yii::t('app', 'Please select the attribute you would like to match on to link the associated record.'))) .";

    $(function() {
        // Hide the import map box if a mapping was uploaded
        if (x2.importer.preselectedMap)
            $('#super-import-map-box').hide();

        // Present a dropdown to select the match attribute for reconstructing
        // associations for link type fields
        $.each ($('#import-map select'), function(i, dropdown) {
            if ($.inArray($(dropdown).val(), Object.keys(x2.importer.linkFieldModelMap)) != -1)
                x2.importer.showLinkFieldMatchSelector ($(dropdown));
        });
    });

    $('#process-link').click(function(){
       $('#editPresetMap').hide();
       x2.importer.prepareImport();
    });

    $('#fill-fields-box').change(function(){
        $('#fields').toggle();
    });

     
    $('#update-records-box').change(function(){
        $('#update-field-selector').toggle();
    });
     

    $('#log-comment-box').change(function(){
       $('#comment-form').toggle();
    });

    $('#batch-size').change(function() {
        $('#batch-size-slider').slider('value', $('#batch-size').val ());
    });

    // Present an additional dropdown to select the linked record's match attribute
    $('.import-attribute').change(function() {
        var selected = $(this).val();
        var linkedModel = x2.importer.linkFieldModelMap[selected];
        var attributeId = $(this).attr('id');
        if ($.inArray(selected, Object.keys(x2.importer.linkFieldModelMap)) != -1 &&
                typeof x2.importer.linkedRecordDropdowns[linkedModel] !== undefined) {
            x2.importer.showLinkFieldMatchSelector ($(this));
        } else {
            // Remove the match attribute dropdown
            var children = $(this).siblings('#' + attributeId + '-linkSelector');
            $(children).remove();
            $(this).parent()
                .css ('border', '')
                .children ('span').remove();
        }
    });

    $('#add-link').click(function(e){
       e.preventDefault();
       $('#field-box').append(x2.importer.createAttrCell());
       $('.del-link').click(function(e){
            e.preventDefault();
            $(this).closest('.field-row').remove();;
        });
    });

    $('#export-map').click(function(e) {
        e.preventDefault();
        var keys = new Array();
        var attributes = new Array();
        $('.import-attribute').each(function(){
            if ($(this).val() != '') {
                // Add mapping overrides that are not marked 'DO NOT MAP'
                attributes.push($(this).val());
                keys.push($(this).attr('name'));
            }
        });
        $.ajax({
            url: 'exportMapping',
            type: 'POST',
            data: {
                model: x2.importer.model,
                name: $('#mapping-name').val(),
                attributes: attributes,
                keys: keys
            },
            success: function(data) {
                data = JSON.parse(data);
                response = x2.importer.interpretPreparationResult (data);

                if (response.success) {
                    var filename = data['map_filename'];
                    $('#download-map').attr ('data-map_filename', filename);
                    $('#download-map').show();
                    $('#prep-status-box').css({'color':'green'});
                    $('#prep-status-box').html(response.msg);
                } else {
                    $('#prep-status-box').css({'color':'red'});
                    $('#prep-status-box').html(response.msg);
                }
            }
        });
    });

    $('#download-map').click(function(e) {
        e.preventDefault();
        var downloadUrl = '". $this->createUrl('admin/downloadData') ."';
        var filename = $('#download-map').attr ('data-map_filename');
        window.location.href = downloadUrl + '?file=' + filename;
    });

    $('#editPresetMap').click(function(e) {
        e.preventDefault();
        $(this).hide();
        $('#importMapSummary').hide();
        $('#super-import-map-box').slideDown(500);
        x2.importer.modifiedPresetMap = true;
    });
", CClientScript::POS_READY);
