<?php
/*********************************************************************************
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
                'value' => 'Formatter::formatCompleteDate($data["timestamp"])',
                'type' => 'raw',
            ),
            'link'=>array(
                'name' => 'link',
                'header' => Yii::t('admin', "Rollback Link"),
                'value' => '"<a href=\'rollbackImport?importId=".$data["importId"]."\' class=\'x2-button rollback-link\'>".Yii::t("admin","Rollback")."</a>"',
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
