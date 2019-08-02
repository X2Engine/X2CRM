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
            'type'=>array(
                'name' => 'type',
                'header' => Yii::t('admin', 'Type of Record Imported'),
                'value' => 'CHtml::encode($data["type"])',
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
    <?php echo Yii::t('admin','Import ID: '); ?><strong><?php echo CHtml::encode ($importId);?></strong>
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
    var importId=<?php echo isset($importId)? addslashes ($importId):0 ?>;
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
                            $('#rollback-link').hide();
                            alert("Done!");
                        }
                    }
                }else{
                    $('#status-box').append("<br><br><b>Rollback Complete</b>");
                    $('#rollback-link').hide();
                    alert("Done!");
                }
            }
        });
    }
</script>
