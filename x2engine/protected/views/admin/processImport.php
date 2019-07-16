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