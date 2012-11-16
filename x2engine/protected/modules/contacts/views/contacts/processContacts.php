<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('contacts','All Contacts'),'url'=>array('index')),
	array('label'=>Yii::t('contacts','Lists'),'url'=>array('lists')),
	array('label'=>Yii::t('contacts','Create Contact'),'url'=>array('create')),
	// array('label'=>Yii::t('contacts','Import from Outlook')),
	array('label'=>Yii::t('contacts','Import Contacts')),
	array('label'=>Yii::t('contacts','Export to CSV'),'url'=>array('export')),
));
?>
<script>
var record=0;
</script>
<style>
    .clean-link{
        text-decoration:none;
    }    
</style>
<h2><?php echo Yii::t('admin','Contacts Import'); ?></h2>
<div class="form" style="width:600px;">
<?php echo Yii::t('admin',"First, we'll need to make sure your fields have mapped properly for import. "); ?>
<?php echo Yii::t('admin','Below is a list of our fields, the fields you provided, and a few sample records that you are importing.');?><br /><br />
<?php echo Yii::t('admin','If the ID field is selected to be imported, the import tool will attempt to overwrite pre-existing records with that ID.  Do not map the ID field if you don\'t want this to happen.') ?>
<br /><br />
<?php echo Yii::t('contacts','Selecting "DO NOT MAP" will ignore the field from your CSV, and selecting "CREATE NEW FIELD" will generate a new text field within X2 and map your field to it.') ?>
</div><div><br /></div>
<div id="import-container">
<div id="super-import-map-box">
<h2><a href="#" class="clean-link" onclick="$('.import-hide').toggle();">[-]</a> <span class="import-hide">Import Map</span></h2>
<div id="import-map-box" class="import-hide form" style="width:600px">
    
<table id="import-map" >
    <tr>
        <td><strong>Your Field</strong></td>
        <td><strong>Our Field</strong></td>
        <td><strong>Sample Contact</strong> <a href="#" class="clean-link" onclick="prevContact();">[Prev]</a> <a href="#" class="clean-link" onclick="nextContact();">[Next]</a></td>
    </tr>
<?php 
    foreach($meta as $attribute){
        echo "<tr>";
        echo "<td style='width:33%'>$attribute</td>";
        echo "<td style='width:33%'>".CHtml::dropDownList($attribute,
                isset($importMap[$attribute])?$importMap[$attribute]:'',
                array_merge(array(''=>'DO NOT MAP','createNew'=>'CREATE NEW FIELD'),Contacts::model()->attributeLabels()),
                array('class'=>'import-attribute')
                )."</td>";
        echo "<td style='width:33%'>";
        for($i=0;$i<5;$i++){
            if(isset($sampleRecords[$i])){
                if($i>0){
                    echo "<span class='record-$i' id='record-$i-$attribute' style='display:none;'>".$sampleRecords[$i][$attribute]."</span>";
                }else{
                    echo "<span class='record-$i' id='record-$i-$attribute'>".$sampleRecords[$i][$attribute]."</span>";
                }
            }
        }
        echo "</td>";
        echo "</tr>";
    }
    
    
?>
</table>
<br />
</div>
</div>
<br /><br />
<h2><?php echo Yii::t('admin','Process Import Data'); ?></h2>
<div class="form" style="width:600px">
    <div class="row">
        <div class="cell"><?php echo "<span class='x2-hint' title='This will attempt to create a record for any field that links to another record type (e.g. Account)'>[?]</span>"; ?></div>
        <div class="cell"><strong><?php echo Yii::t('contacts','Create records for link fields?'); ?></strong></div>
        <div class="cell"><?php echo CHtml::checkBox('create-records-box','checked');?></div>
    </div>
    <div class="row">
        <div class="cell"><?php echo "<span class='x2-hint' title='These tags will be applied to any contact created by the import.<br /><em>Example: web,newlead,urgent</em>'>[?]</span>"; ?></div>
        <div class="cell"><strong><?php echo Yii::t('marketing','Tags'); ?></strong></div>
        <div class="cell"><?php echo CHtml::textField('tags'); ?></div>
    </div>
    <div class="row">
        <div class="cell"><?php echo "<span class='x2-hint' title='These fields will be applied to all imported contacts and override their respective mapped fields from the import.'>[?]</span>"; ?></div>
        <div class="cell"><strong><?php echo Yii::t('admin','Automatically fill certain fields?'); ?></strong></div>
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
        <div class="cell"><?php echo "<span class='x2-hint' title=\"Anything entered here will be created as a comment and logged as an Action in the imported record's history.\">[?]</span>"; ?></div>
        <div class="cell"><strong><?php echo Yii::t('admin','Automatically log a comment on these records?'); ?></strong></div>
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
        <div class="cell"><?php echo "<span class='x2-hint' title=\"If this box is checked, all contacts will be assigned to users based on your lead routing settings.\">[?]</span>"; ?></div>
        <div class="cell"><strong><?php echo Yii::t('admin','Assign records via lead-routing?'); ?></strong></div>
        <div class="cell"><?php echo CHtml::checkBox('lead-routing-box');?></div>
    </div>
</div>
<br /><br />
<?php echo CHtml::link("Process Import","#",array('id'=>'process-link','class'=>'x2-button highlight'));?>
<br /><br />
</div>
<h3 id="import-status" style="display:none;"><?php echo Yii::t('admin','Import Status'); ?></h3>
<div id="prep-status-box" style="color:green">
    
</div>
<br />
<div id="status-box" style="color:green">
    
</div>
<div id="failures-box" style="color:red">
    
</div>
<script>
    var attributeLabels = <?php echo json_encode(Contacts::model()->attributeLabels(), false);?>;
    $('#process-link').click(function(){
       prepareImport(); 
    });
    $('#fill-fields-box').change(function(){
        $('#fields').toggle();
    });
    $('#log-comment-box').change(function(){
       $('#comment-form').toggle(); 
    });
    
    
    
    function prepareImport(){
        $('#import-container').hide();
        var attributes=new Array();
        var keys=new Array();
        var forcedAttributes=new Array();
        var forcedValues=new Array();
        var comment="";
        var routing=0;
        $('.import-attribute').each(function(){
            attributes.push($(this).val());
            keys.push($(this).attr('name'));
        });
        if($('#fill-fields-box').attr('checked')=='checked'){
            $('.forced-attribute').each(function(){
            forcedAttributes.push($(this).val()); 
            });
            $('.forced-value').each(function(){
                forcedValues.push($(this).val());
            });
        }
        if($('#log-comment-box').attr('checked')=='checked'){
            comment=$("#comment").val();
        }
        if($('#lead-routing-box').attr('checked')=='checked'){
            routing=1;
        }
        $.ajax({
            url:'prepareImport',
            type:"POST",
            data:{
                attributes:attributes, 
                keys:keys, 
                forcedAttributes:forcedAttributes, 
                forcedValues:forcedValues,
                createRecords:$('#create-records-box').attr('checked')=='checked'?'checked':'',
                tags:$('#tags').val(),
                comment:comment,
                routing:routing
            },
            complete:function(data){
                if(data[0]!=2){
                    $('#import-status').show();
                    var str="Import setup completed successfully...<br />Beginning import.";
                    importData(25);
                    $('#prep-status-box').html(str);
                }else{
                    var str="Import preparation failed.  Aborting import.";
                    $('#prep-status-box').css({'color':'red'});
                    $('#prep-status-box').html(str);
                }
                
            }
        });
    }
    function importData(count){
        $.ajax({
            url:'importRecords',
            type:"POST",
            data:{
                count:count
            },
            success:function(data){
                data=JSON.parse(data);
                if(data[0]!=1){
                    str=data[1]+" <b>Contacts</b> have been successfully imported.";
                    created=JSON.parse(data[3]);
                    for(type in created){
                        if(created[type]>0){
                            str+="<br />"+created[type]+" <b>"+type+"</b> were created and linked to Contacts.";
                        }
                    }
                    $('#status-box').html(str);
                    if(data[2]>0){
                        str=data[2]+" <b>Contacts</b> have failed validation and were not imported.";
                        $("#failures-box").html(str);
                    }
                    importData(count);
                }else{
                    str=data[1]+" <b>Contacts</b> have been successfully imported.";
                    created=JSON.parse(data[3]);
                    for(type in created){
                        if(created[type]>0){
                            str+="<br />"+created[type]+" <b>"+type+"</b> were created and linked to Contacts.";
                        }
                    }
                    $('#status-box').html(str);
                    if(data[2]>0){
                        str=data[2]+" <b>Contacts</b> have failed validation and were not imported. Click here to recover them: <a href=\"#\" id=\"download-link\" class=\"x2-button\">Download</a>";
                        $("#failures-box").html(str);
                        $('#download-link').click(function(e) {
                            e.preventDefault();  //stop the browser from following
                            window.location.href = '<?php echo $this->createUrl('/admin/downloadData',array('file'=>'failedContacts.csv')); ?>';
                        });
                    }
                    $.ajax({
                        url:'cleanUpImport',
                        complete:function(){
                            var str="<strong>Import Complete.</strong>";
                            $('#prep-status-box').html(str);
                            alert('Import Complete!');
                        }
                    });
                }
            }
        });
    }
    function prevContact(){
        $('.record-'+record).hide();
        if(record==0){
            record=4;
        }else{
            record--;
        }
        $('.record-'+record).show();
    }
    
    function nextContact(){
        $('.record-'+record).hide();
        if(record==4){
            record=0;
        }else{
            record++;
        }
        $('.record-'+record).show();
    }
    
    function createDropdown(list, ignore) {
        var sel = $(document.createElement('select'));
        $.each(list, function(key, value) {
            if ($.inArray(key, ignore) == -1) {
                sel.append('<option value=\"' + key  + '\">' + value + '</option>');
            }
        });
        return sel;
    }
    
    function createAttrCell(){
        var div = $(document.createElement('div'));
        div.attr('class', 'field-row');
        var dropdown = createDropdown(attributeLabels);
        dropdown.attr('class', 'forced-attribute');
        var input = $('<input size="30" type="text" value="" class="forced-value">');
        input.attr('name', 'force-values[]');
        var link= $('<a href="#" class="del-link clean-link">[x]</a>');
        return div.append(dropdown).append(input).append(link);
    }
    $('#add-link').click(function(e){
       e.preventDefault();
       $('#field-box').append(createAttrCell());
       $('.del-link').click(function(e){
            e.preventDefault();
            $(this).closest('.field-row').remove();;
        });
    });
    
</script>