<!-- dialog for completing a stage requiring a comment-->
<div id='workflowCommentDialog'>
<form>
<div class="row"><?php echo Yii::t('workflow','Please summarize how this stage was completed.'); ?></div>
<div class="row">
    <?php
        
    echo CHtml::textArea('workflowComment','',array('style'=>'width:260px;height:80px;'));

    echo CHtml::hiddenField('workflowCommentWorkflowId','',array('id'=>'workflowCommentWorkflowId'));
    echo CHtml::hiddenField('workflowCommentStageNumber','',array('id'=>'workflowCommentStageNumber'));
    ?>
</div>
</form>
</div>

<div id="workflowStageDetails"></div>

<?php // dialog to contain Workflow Stage Details
$workflowList = Workflow::getList();
?>
<div class="row" style="text-align:center;">
        <?php
        echo CHtml::dropDownList('workflowId',$currentWorkflow,$workflowList,    //$model->workflow
            array(
                'ajax' => array(
                    'type'=>'GET', //request type
                    'url'=>CHtml::normalizeUrl(array('/workflow/workflow/getWorkflow','modelId'=>$model->id,'type'=>$modelName)), //url to call.
                    //Style: CController::createUrl('currentController/methodToCall')
                    'update'=>'#workflow-diagram', //selector to update
                    'data'=>array('workflowId'=>'js:$(this).val()')
                    //leave out the data key to pass all form values through
                ),
                'id'=>'workflowSelector'
            )
        ); 
        ?>
</div>
<div class="row">
    <div id="workflow-diagram">
        <?php
        $workflowStatus = Workflow::getWorkflowStatus($currentWorkflow,$model->id,$modelName);    // true = include dropdowns
        echo Workflow::renderWorkflow($workflowStatus);
    ?></div>
</div>
