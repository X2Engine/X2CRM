<?php // dialog for completing a stage requiring a comment

$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
	'id'=>'workflowCommentDialog',
	// additional javascript options for the dialog plugin
	'options'=>array(
		'title'=>Yii::t('workflow','Comment Required'),
		'autoOpen'=>false,
		'resizable'=>false,
		'modal'=>true,
		'show'=>'fade',
		'hide'=>'fade',
	),
	'htmlOptions'=>array('class'=>'form no-border')
));
?>
<form>
<div class="row"><?php echo Yii::t('workflow','Please summarize how this stage was completed.'); ?></div>
<div class="row">
	<?php
		
	echo CHtml::textArea('workflowComment','',array('style'=>'width:250px;height:80px;'));

	echo CHtml::submitButton(Yii::t('app','Submit'),array(
		'id'=>'workflowCommentSubmit',
		'class'=>'x2-button highlight left',
		'onclick'=>'completeWorkflowStageComment(); return false;'
	));
	echo CHtml::resetButton(Yii::t('app','Cancel'),array(
		'class'=>'x2-button left',
		'onclick'=>'$("#workflowCommentDialog").dialog("close");'
	));
	echo CHtml::hiddenField('workflowCommentWorkflowId','',array('id'=>'workflowCommentWorkflowId'));
	echo CHtml::hiddenField('workflowCommentStageNumber','',array('id'=>'workflowCommentStageNumber'));
	?>
</div>
</form>
<?php $this->endWidget('zii.widgets.jui.CJuiDialog'); ?>


<?php // dialog to contain Workflow Stage Details

$this->widget('zii.widgets.jui.CJuiDialog', array(
	'id'=>'workflowStageDetails',
	// additional javascript options for the dialog plugin
	'options'=>array(
		'title'=>Yii::t('workflow','Comment Required'),
		'autoOpen'=>false,
		'resizable'=>false,
		'modal'=>false,
		'show'=>'fade',
		'hide'=>'fade',
	),
));
// $this->endWidget('zii.widgets.jui.CJuiDialog');

$workflowList = Workflow::getList();
?>
<div class="form">
<div class="row" style="text-align:center;"><b><?php echo Yii::t('workflow','Workflow'); ?></b>
		<?php
		echo CHtml::dropDownList('workflowId',$currentWorkflow,$workflowList,	//$model->workflow
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
		$workflowStatus = Workflow::getWorkflowStatus($currentWorkflow,$model->id,$modelName);	// true = include dropdowns
		echo Workflow::renderWorkflow($workflowStatus);
	?></div>
</div>
</div>