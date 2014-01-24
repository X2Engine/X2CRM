<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

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
                resizable: false,
                modal: false,
                show: "fade",
                hide: "fade",
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
			$("#workflowCommentDialog").dialog({
				autoOpen:false,
                resizable: false,
                modal: true,
                show: "fade",
                hide: "fade",
				width:400,
				buttons:{
					submit: {
                        click: function() {
                            completeWorkflowStageComment(); return false;
					    },
                        text: "'.addslashes(Yii::t('app','Submit')).'",
                        "class": "highlight"
                    },
					cancel: {
                        text: "'.addslashes(Yii::t('app','Cancel')).'",
                        click: function() {
                            $(this).dialog("close");
					    }
                    }
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
