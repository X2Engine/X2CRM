<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
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
<div class="page-title"><h2><?php echo Yii::t('admin','Import Data from Template'); ?></h2></div>
<?php echo Yii::t('admin','To import your data a CSV file, please  upload the file here using the form below.'); ?>
<br><br>
<?php echo Yii::t('admin','This import has a very specific style of data formatting required to be used. To get a better example of the formatting, export a set of data and look at how it is formatted.  A brief description is also provided here.'); ?>
<br><br>
<?php echo Yii::t('admin','The first cell of the CSV should be the version from which data was exported.  If it is a fresh set of data or was not exported, use the current version.');?>
<br><br>
<?php echo Yii::t('admin','Each record type should have a set of column names as metadata with the type of record (e.g. "Contacts" or "Accounts" at the end.  Each record should also have the record type as the last column.'); ?>
<br><br>
<h2><?php echo Yii::t('admin','Process Import Data'); ?></h2>
<?php echo CHtml::link("Click Here!","#",array('id'=>'process-link','class'=>'x2-button'));?>
<br><br>
<h3><?php echo Yii::t('admin','Models Imported'); ?></h3>
<div id="status-box" style="color:green">
    
</div>
<div id="overwrite-failure-box" style="color:orange">
    
</div>
<div id="failures-box" style="color:red">
    
</div>
<script>
    $('#process-link').click(function(){
       prepareImport(); 
    });
    function prepareImport(){
        $.ajax({
            url:'prepareImport',
            success:function(data){
                importData(50);
            }
        });
    }
    function importData(count){
        $.ajax({
            url:'globalImport',
            type:"POST",
            data:{
                count:count
            },
            success:function(data){
                data=JSON.parse(data);
                if(data[0]!=0){
                    counts=JSON.parse(data[1]);
                    overwriten=JSON.parse(data[2]);
                    var str="";
                    $.each(counts,function(index,value){
                        if(overwriten[index]==undefined){
                            overwriten[index]=0;
                        }
                        str=str+value+" <b>"+index+"</b> successfully imported ("+overwriten[index]+" overwriten).<br>";
                    });
                    $('#status-box').html(str);
                    failures=data[3];
                    if(failures > 0){
                        $('#failures-box').html(failures+" record(s) were not imported successfully.");
                    }
                    overwriteFailures=JSON.parse(data[4]);
                    var ofStr="";
                    $.each(overwriteFailures,function(index,value){
                       if(overwriteFailures[index]>0){
                           ofStr=ofStr+value+" <b>"+index+"</b> records were unable to be overwriten.<br>";
                       } 
                    });
                    $('#overwrite-failure-box').html(ofStr);
                    importData(count);
                }else{
                    counts=JSON.parse(data[1]);
                    overwriten=JSON.parse(data[2]);
                    var str="";
                    $.each(counts,function(index,value){
                        if(overwriten[index]==undefined){
                            overwriten[index]=0;
                        }
                        str=str+value+" <b>"+index+"</b> successfully imported ("+overwriten[index]+" overwriten).<br>";
                    });
                    failures=data[3];
                    if(failures > 0){
                        $('#failures-box').html(failures+' record(s) were not imported successfully. To recover failed records click here: <a href="#" id="download-link" class="x2-button">Download</a>');
                        $('#download-link').click(function(e) {
                            e.preventDefault();  //stop the browser from following
                            window.location.href = 'downloadData?file=failedImport.csv';
                        });
                    }
                    $('#status-box').html(str);
                    overwriteFailures=JSON.parse(data[4]);
                    var ofStr="";
                    $.each(overwriteFailures,function(index,value){
                       if(overwriteFailures[index]>0){
                           ofStr=ofStr+value+" <b>"+index+"</b> records were unable to be overwriten.<br>";
                       } 
                    });
                    $('#overwrite-failure-box').html(ofStr);
                    $.ajax({
                        url:'cleanUpImport',
                        success:function(){
                            alert("Import Finished");
                        }
                    });
                    
                }
            }
        });
    }
    
</script>