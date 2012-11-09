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
?>
<script>
var record=0;
</script>
<h2><?php echo Yii::t('admin','Contacts Import'); ?></h2>
<?php echo Yii::t('admin',"First, we'll need to make sure your fields have mapped properly for import. "); ?>
<?php echo Yii::t('admin','Below is a list of our fields, the fields you provided, and one of the contacts you are importing.');?><br /><br />
<?php echo Yii::t('admin','If the ID field is selected to be imported, the import tool will attempt to overwrite pre-existing records with that ID.  Do not map the ID field if you don\'t want this to happen.') ?>
<div><br /></div>
<div id="super-import-map-box">
<h3><a href="#" onclick="$('.import-hide').toggle();">[-]</a> <span class="import-hide">Import Map</span></h3>
<div id="import-map-box" class="import-hide">
    
<table id="import-map" style="width:50%">
    <tr>
        <td><strong>Your Field</strong></td>
        <td><strong>Our Field</strong></td>
        <td><strong>Sample Contact</strong> <a href="#" onclick="prevContact();">[Prev]</a> <a href="#" onclick="nextContact();">[Next]</a></td>
    </tr>
<?php 
    foreach($meta as $attribute){
        echo "<tr>";
        echo "<td>$attribute</td>";
        echo "<td>".CHtml::dropDownList($attribute,
                isset($importMap[$attribute])?$importMap[$attribute]:'',
                array_merge(array(''=>'DO NOT MAP','createNew'=>'CREATE NEW FIELD'),Contacts::model()->attributeLabels()),
                array('class'=>'import-attribute')
                )."</td>";
        echo "<td>";
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
<strong><?php echo CHtml::label('Create Records for Link Fields','create-records-box'); ?></strong>
<?php echo CHtml::checkBox('create-records-box','checked');?>
<?php echo "<span class='x2-hint' title='This will attempt to create a record for any field that links to another record type (e.g. Account)'>[?]</span>"; ?>
<br />
<div class="row">
		<strong><?php echo CHtml::label(Yii::t('marketing','Tags'), 'tags'); ?></strong>
		<?php echo CHtml::textField('tags'); ?>
		<p class="fieldhelp"><em><?php echo Yii::t('marketing','Example') .': web,newlead,urgent'; ?></em> <?php echo "<span class='x2-hint' title='These tags will be applied to any contact created by the import.'>[?]</span>"; ?></p>
	</div> 
<br />
<?php echo CHtml::link("Click Here!","#",array('id'=>'process-link','class'=>'x2-button'));?>
<br /><br />
<h3><?php echo Yii::t('admin','Contacts Imported'); ?></h3>
<div id="prep-status-box" style="color:green">
    
</div>
<div id="status-box" style="color:green">
    
</div>
<div id="failures-box" style="color:red">
    
</div>
<script>
    $('#process-link').click(function(){
       prepareImport(); 
    });
    function prepareImport(){
        $('#super-import-map-box').hide();
        var attributes=new Array();
        var keys=new Array();
        $('.import-attribute').each(function(){
            attributes.push($(this).val());
            keys.push($(this).attr('name'));
        });
        $.ajax({
            url:'prepareImport',
            type:"POST",
            data:{
                attributes:attributes, keys:keys, 
                createRecords:$('#create-records-box').attr('checked')=='checked'?'checked':'',
                tags:$('#tags').val()
            },
            success:function(data){
                if(data[0]!=2){
                    importData(25);
                }else{
                    
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
                        str=data[2]+" <b>Contacts</b> have failed validation and were not imported.";
                        $("#failures-box").html(str);
                    }
                    alert('Import Complete!');
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
    
</script>