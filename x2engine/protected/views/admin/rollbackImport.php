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
<div class="page-title"><h2><?php echo Yii::t('admin','Import List');?></h2></div>
<div class="form">
<div style="width:500px;">
    <?php echo Yii::t('admin','To rollback an import, find it on the list below and press the \'Rollback\' button.');?>
    <br><br>
    <?php echo Yii::t('admin','This will delete all records created by that particular import, as well as any generated records and all tags and actions associated with these records.  This operation cannot be reversed.') ?>
</div>
</div>
<?php
if(!empty($dataProvider)){
$this->widget('zii.widgets.grid.CGridView', array(
        'id' => 'lead-activity-grid',
        'baseScriptUrl' => Yii::app()->request->baseUrl . '/themes/' . Yii::app()->theme->name . '/css/gridview',
        'template' => '{items}{pager}',
        'template'=> '<div class="page-title"><h2>'.Yii::t('admin','Import Manager').'</h2><div class="title-bar">'
         //.CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
        //.CHtml::link(Yii::t('app','Clear Filters'),array(Yii::app()->controller->action->id,'clearFilters'=>1)) . ' | '
        //.CHtml::link(Yii::t('app','Columns'),'javascript:void(0);',array('class'=>'column-selector-link'))
         .'{summary}</div></div>{items}{pager}',
		 'summaryText' => Yii::t('app','<b>{start}&ndash;{end}</b> of <b>{count}</b>'),
        'dataProvider' => $dataProvider,
        'enableSorting'=>true,
        'enablePagination' => true,
        'ajaxUpdate'=>true,
        'columns' => array(
            'importId'=>array(
                'name' => 'importId',
                'header' => Yii::t('admin', "Import ID"),
                'value' => '$data["importId"]',
                'type' => 'raw',
            ),
            'records'=>array(
                'name' => 'records',
                'header' => Yii::t('admin', "# of Records Imported"),
                'value' => '$data["records"]',
                'type' => 'raw',
            ),
            'timestamp'=>array(
                'name' => 'timestamp',
                'header' => Yii::t('admin', "Timestamp"),
                'value' => 'Actions::formatCompleteDate($data["timestamp"])',
                'type' => 'raw',
            ),
            'link'=>array(
                'name' => 'link',
                'header' => Yii::t('admin', "Rollback Link"),
                'value' => '"<a href=\'rollbackImport?importId=".$data["importId"]."\' class=\'x2-button rollback-link\'>Rollback</a>"',
                'type' => 'raw',
            ),
        ),
    ));
}else{ ?>

<div class="form" style="width:600px;">
    <?php echo Yii::t('admin','To begin the rollback, click the button below and wait for the completion message.'); ?>
    <br><br>
    <?php echo Yii::t('admin','Import ID: '); ?><strong><?php echo $_GET['importId'];?></strong>
    <br>
    <?php echo Yii::t('admin','Records to be Deleted: '); ?><strong><?php echo $count; ?></strong>
    <br><br>
    <?php echo CHtml::link('Begin Rollback','#',array('id'=>'rollback-link','class'=>'x2-button'));?>
</div>
<div class="form" style="width:600px;color:green;display:none;" id="status-box">
    
</div>
<?php }
?>
<script>
    var models=JSON.parse('<?php echo json_encode($typeArray);?>');
    var importId=<?php echo isset($_GET['importId'])?$_GET['importId']:0 ?>;
    var stages=new Array('tags','relationships','actions','records','import');
    $('#rollback-link').click(function(e){
        e.preventDefault();
        $('#status-box').show();
        $('#status-box').append('Beginning import rollback...');
        rollbackStage(0,0);
    });
    function rollbackStage(model,stage){
        $.ajax({
            url:'rollbackStage',
            type:"GET",
            data:{model:models[model],stage:stages[stage],importId:importId},
            success:function(data){
                if(stages[stage]=='import'){
                    $('#status-box').append("<br>"+data+" <b>"+models[model]+"</b> successfully removed.");
                }
                if(model<models.length){
                    if(stage<stages.length-1){
                        rollbackStage(model,stage+1);
                    }else{
                        if(model!=models.length-1){
                            rollbackStage(model+1,0);
                        }else{
                            $('#status-box').append("<br><br><b>Rollback Complete</b>");
                            alert("Done!");
                        }
                    }
                }else{
                    $('#status-box').append("<br><br><b>Rollback Complete</b>");
                    alert("Done!");
                }
            }
        });
    }
</script>
