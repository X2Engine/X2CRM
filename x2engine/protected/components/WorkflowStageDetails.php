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

/**
 * Displays the details of a workflow stage.
 * 
 * @package X2CRM.components 
 */
class WorkflowStageDetails extends X2Widget {
	public $model;
	public $modelName;
	public $currentWorkflow;


	public function init() {
		

 		Yii::app()->clientScript->registerScript('workflowDialog_'.$this->id,'
		// $.extend($.ui.dialog.prototype, {editMode:function(){alert("herp derp"); }});
		
		
		$.fn.extend({loading:function(){
			$(this).html("<img src=\""+yii.themeBaseUrl+"/images/loading.gif\" class=\"loading\">");
		}});

		$(function() {
			$("#workflowStageDetails").dialog({
				autoOpen:false,
				closeOnEscape:true,
				width:400,
				buttons:{
					"'.addslashes(Yii::t('app','Save')).'": function() { saveWorkflowStageDetails(); },
					"'.addslashes(Yii::t('app','Edit')).'": function() {
						$(this).addClass("editMode");
						$(this).parent().find(".ui-dialog-buttonpane button:nth-child(1)").show();	// save
						$(this).parent().find(".ui-dialog-buttonpane button:nth-child(2)").hide();	// edit
						$(this).parent().find(".ui-dialog-buttonpane button:nth-child(3)").show();	// cancel
					},
					"'.addslashes(Yii::t('app','Cancel')).'": function() {
						$(this).removeClass("editMode");
						$(this).parent().find(".ui-dialog-buttonpane button:nth-child(1)").hide(); 	// save
						$(this).parent().find(".ui-dialog-buttonpane button:nth-child(2)").show();	// edit
						$(this).parent().find(".ui-dialog-buttonpane button:nth-child(3)").hide(); 	// cancel
					},
					"'.addslashes(Yii::t('app','Close')).'": function() { $(this).dialog("close"); }
					
					
				}
			});
		});
		
		function saveWorkflowStageDetails() {
			$.ajax({
				url: $("#workflowDetailsForm").attr("action"),
				type: "POST",
				data: $("#workflowDetailsForm").serialize(),
				beforeSend: function() { $("#workflowStageDetails").loading(); },
				success: function(response) {
					if(response!="")
						$("#workflow-diagram").html(response);
					$("#workflowStageDetails").dialog("close");
					$("#workflowSelector").change();
					x2.Notifs.updateHistory();
				}
			});
		}

		function startWorkflowStage(workflowId,stageNumber) {
			$.ajax({
				url: "' . CHtml::normalizeUrl(array('/workflow/workflow/startStage')) . '",
				type: "GET",
				data: "workflowId="+workflowId+"&stageNumber="+stageNumber+"&modelId='.$this->model->id.'&type='.$this->modelName.'",
				success: function(response) {
					if(response!="")
						$("#workflow-diagram").html(response);
					x2.Notifs.updateHistory();
				}
			});
		}
		'."
		function completeWorkflowStage(workflowId,stageNumber) {
			$.ajax({
				url: '" . CHtml::normalizeUrl(array('/workflow/workflow/completeStage')) . "',
				type: 'GET',
				data: 'workflowId='+workflowId+'&stageNumber='+stageNumber+'&modelId=".$this->model->id."&type=".$this->modelName."',
				success: function(response) {
					if(response!='')
						$('#workflow-diagram').html(response);
					x2.Notifs.updateHistory();
				}
			});
		}
		
		function workflowCommentDialog(workflowId,stageNumber) {

			$('#workflowCommentDialog').dialog('option','title','".addslashes(Yii::t('workflow','Comment Required'))."');

			$('#workflowCommentWorkflowId').val(workflowId);
			$('#workflowCommentStageNumber').val(stageNumber);
			
			$('#workflowComment').css('border','1px solid black');
			$('#workflowComment').val('')
			$('#workflowCommentDialog').dialog('open');
		}
		".'
		function workflowStageDetails(workflowId,stageNumber) {
		
			var dialogBox = $("#workflowStageDetails");
		
			var dialogTitle = "'.addslashes(Yii::t('workflow','Stage {n}')).'".replace("{n}",stageNumber);
		
			var stageLabels = $("#workflow-diagram .workflow-funnel-stage b");
			if(stageLabels.length >= stageNumber)
				dialogTitle += ": "+$(stageLabels[stageNumber-1]).html();
				
		
			dialogBox.dialog("option","title",dialogTitle);
			
			dialogBox.removeClass("editMode");

			$("#workflowDetails_createDate, #workflowDetails_startDate").datepicker("destroy");
			dialogBox.parent().find(".ui-dialog-buttonpane button:nth-child(1)").hide(); 	// save
			dialogBox.parent().find(".ui-dialog-buttonpane button:nth-child(2)").hide();	// edit
			dialogBox.parent().find(".ui-dialog-buttonpane button:nth-child(3)").hide(); 	// cancel
			
			dialogBox.dialog("open");
			
			dialogBox.loading();
			
			$.ajax({
				url: "' . CHtml::normalizeUrl(array('/workflow/workflow/getStageDetails')) . '",
				type: "GET",
				data: "workflowId="+workflowId+"&stage="+stageNumber+"&modelId='.$this->model->id.'&type='.$this->modelName.'",
				success: function(response) {
					if(response=="") return;
					$("#workflowStageDetails").html(response);
					
					if($("#workflowStageDetails #workflowDetailsForm").length)	// remove the edit button if theres no form
						dialogBox.parent().find(".ui-dialog-buttonpane button:nth-child(2)").show();
					else
						dialogBox.parent().find(".ui-dialog-buttonpane button:nth-child(2)").hide();
					
				}
			});
		}
		'."

		function completeWorkflowStageComment() {
			var comment = $.trim($('#workflowComment').val());
			if(comment.length < 1) {
				$('#workflowComment').css('border','1px solid red');
			} else {
				$.ajax({
					url: '" . CHtml::normalizeUrl(array('/workflow/workflow/completeStage')) . "',
					type: 'GET',
					data: 'workflowId='+$('#workflowCommentWorkflowId').val()+'&stageNumber='+$('#workflowCommentStageNumber').val()+'&modelId=".$this->model->id."&type=".$this->modelName."&comment='+encodeURI(comment),
					success: function(response) {
						if(response=='') return;
						$('#workflow-diagram').html(response);
						x2.Notifs.updateHistory();
					}
				});
				$('#workflowCommentDialog').dialog('close');
			}
		}

		function revertWorkflowStage(workflowId,stageNumber) {
			$.ajax({
				url: '" . CHtml::normalizeUrl(array('/workflow/workflow/revertStage')) . "',
				type: 'GET',
				data: 'workflowId='+workflowId+'&stageNumber='+stageNumber+'&modelId=".$this->model->id."&type=".$this->modelName."',
				success: function(response) {
					if(response!='')
						$('#workflow-diagram').html(response);
					x2.Notifs.updateHistory();
				}
			});
		}
		
		
		",CClientScript::POS_HEAD);
		

		parent::init();
	}

	public function run() {
		$this->render('_workflow',array('model'=>$this->model,'modelName'=>$this->modelName,'currentWorkflow'=>$this->currentWorkflow));
		// $action = new InlineEmailAction($this->controller,'inlineEmail');
		// $action->model = &$this->model;
		// $action->run(); 
	}
}
