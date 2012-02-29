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

$attributeLabels = $model->attributeLabels();

$showSocialMedia = Yii::app()->params->profile->showSocialMedia;

$showWorkflow = Yii::app()->params->profile->showWorkflow;
if($modelName=='contacts' || $modelName=='sales'){
Yii::app()->clientScript->registerScript('updateWorkflow',"
function startWorkflowStage(workflowId,stageNumber) {
	$.ajax({
		url: '" . CHtml::normalizeUrl(array('workflow/startStage')) . "',
		type: 'GET',
		data: 'workflowId='+workflowId+'&stageNumber='+stageNumber+'&modelId=".$model->id."&type=".$modelName."',
		success: function(response) {
			if(response!='')
				$('#workflow-diagram').html(response);
			updateHistory();
		}
	});
}

function completeWorkflowStage(workflowId,stageNumber) {
	$.ajax({
		url: '" . CHtml::normalizeUrl(array('workflow/completeStage')) . "',
		type: 'GET',
		data: 'workflowId='+workflowId+'&stageNumber='+stageNumber+'&modelId=".$model->id."&type=".$modelName."',
		success: function(response) {
			if(response!='')
				$('#workflow-diagram').html(response);
			updateHistory();
		}
	});
}
function workflowCommentDialog(workflowId,stageNumber) {
	$('#workflowCommentWorkflowId').val(workflowId);
	$('#workflowCommentStageNumber').val(stageNumber);

	$('#workflowComment').css('border','1px solid black');
	$('#workflowComment').val('')
	$('#workflowDialog').dialog('open');
}

function completeWorkflowStageComment() {
	var comment = $.trim($('#workflowComment').val());
	if(comment.length < 1) {
		$('#workflowComment').css('border','1px solid red');
	} else {
		$.ajax({
			url: '" . CHtml::normalizeUrl(array('workflow/completeStage')) . "',
			type: 'GET',
			data: 'workflowId='+$('#workflowCommentWorkflowId').val()+'&stageNumber='+$('#workflowCommentStageNumber').val()+'&modelId=".$model->id."&type=contacts&comment='+encodeURI(comment),
			success: function(response) {
				if(response=='') return;
				$('#workflow-diagram').html(response);
				updateHistory();
			}
		});
		$('#workflowDialog').dialog('close');
	}
}

function revertWorkflowStage(workflowId,stageNumber) {
	$.ajax({
		url: '" . CHtml::normalizeUrl(array('workflow/revertStage')) . "',
		type: 'GET',
		data: 'workflowId='+workflowId+'&stageNumber='+stageNumber+'&modelId=".$model->id."&type=".$modelName."',
		success: function(response) {
			if(response!='')
				$('#workflow-diagram').html(response);
			updateHistory();
		}
	});
}
",CClientScript::POS_HEAD);


Yii::app()->clientScript->registerScript('toggleWorkflow', "
function showWorkflow() {
	$('tr#workflow-row').show();
	$('tr#workflow-toggle').hide();
}
function hideWorkflow() {
	$('tr#workflow-row').hide();
	$('tr#workflow-toggle').show();
}
$(function() {\n"
.($showWorkflow? "showWorkflow();\n" : "hideWorkflow()\n")
."});",CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScript('setFormName',"
window.formName = '$modelName';
",CClientScript::POS_HEAD);
}

$layout = FormLayout::model()->findByAttributes(array('model'=>ucfirst($modelName),'defaultView'=>1));
if(isset($layout)) {

echo '<div class="x2-layout">';

$temp=RoleToUser::model()->findAllByAttributes(array('userId'=>Yii::app()->user->getId(),'type'=>'user'));
$roles=array();
foreach($temp as $link) {
    $roles[]=$link->roleId;
}
/* x2temp */
$groups=GroupToUser::model()->findAllByAttributes(array('userId'=>Yii::app()->user->getId()));
foreach($groups as $link) {
    $tempRole=RoleToUser::model()->findByAttributes(array('userId'=>$link->groupId, 'type'=>'group'));
    if(isset($tempRole))
        $roles[]=$tempRole->roleId; 
}
/* end x2temp */

$fields = array();
foreach(Fields::model()->findAllByAttributes(array('modelName'=>ucfirst($modelName))) as $fieldModel)
	$fields[$fieldModel->fieldName] = $fieldModel;

$layoutData = json_decode($layout->layout,true);
$formSettings = ProfileChild::getFormSettings($modelName);

if(isset($layoutData['sections']) && count($layoutData['sections']) > 0) {
	$i = 0;
	foreach($layoutData['sections'] as &$section) {
		// set defaults
		if(!isset($section['title'])) $section['title'] = '';
		if(!isset($section['collapsible'])) $section['collapsible'] = false;
		if(!isset($section['rows'])) $section['rows'] = array();
		if(!isset($formSettings[$i])) $formSettings[$i] = 1;
		
		echo '<div class="formSection'.((!$formSettings[$i] && $section['collapsible'])? ' hideSection' : '').'">';
		
		if($section['collapsible'] || !empty($section['title'])) {
			echo '<div class="formSectionHeader">';
			if(!empty($section['title']))
				echo '<span class="sectionTitle">'.Yii::t(strtolower(Yii::app()->controller->id),$section['title']).'</span>';
			if($section['collapsible']) {
				echo '<a href="javascript:void(0)" class="formSectionHide">[ '.Yii::t('admin','Hide').' ]</a>';
				echo '<a href="javascript:void(0)" class="formSectionShow">[ '.Yii::t('admin','Show').' ]</a>';
			}
			echo '</div>';
		} else
			echo '<hr>';
		if(!empty($section['rows'])) {
			echo '<div class="tableWrapper"><table>';
		
			foreach($section['rows'] as &$row) {
				echo '<tr class="formSectionRow">';
				if(isset($row['cols'])) {
					foreach($row['cols'] as &$col) {
					
						$width = isset($col['width'])? ' style="width:'.$col['width'].'px"' : '';
						echo "<td$width>";
						if(isset($col['items'])) {
							foreach($col['items'] as &$item) {
								
								if(isset($item['name'],$item['labelType'],$item['readOnly'],$item['height'],$item['width'])) {
									$fieldName = preg_replace('/^formItem_/u','',$item['name']);
								
									if(isset($fields[$fieldName])) {
										$field = $fields[$fieldName];
										$fieldPerms=RoleToPermission::model()->findAllByAttributes(array('fieldId'=>$field->id));
										$perms=array();
										foreach($fieldPerms as $permission){
											$perms[$permission->roleId]=$permission->permission;
										}
										$tempPerm=2;
										foreach($roles as $role){
											if(array_search($role,array_keys($perms))!==false){
												if($perms[$role]<$tempPerm)
													$tempPerm=$perms[$role];
											}
										}
										if($tempPerm==0){
											unset($item);
											echo '</div></div>';
											continue;
										}
										
										$labelType = isset($item['labelType'])? $item['labelType'] : 'top';
										switch($labelType) {
											case 'inline':	$labelClass = 'inlineLabel'; break;
											case 'none':	$labelClass = 'noLabel'; break;
											case 'left':	$labelClass = 'leftLabel'; break;
											case 'top': 
											default:		$labelClass = 'topLabel';
										}
										
										echo '<div class="formItem '.$labelClass.'">';
										echo CHtml::label($model->getAttributeLabel($field->fieldName),false);
											
										$style = 'width:'.$item['width'].'px;';
										// if($field->type == 'text')
											// $style .= 'height:'.$item['height'].'px;';
										echo '<div class="formInputBox" style="'.$style.'">';
										if($field->type == 'date') {
											echo $this->formatLongDate($model->$fieldName).'&nbsp;';
										}elseif($field->type=='rating'){
											$this->widget('CStarRating',array(
													'model'=>$model,
													'attribute'=>$field->fieldName,
													'readOnly'=>true,
													'minRating'=>1, //minimal valuez
													'maxRating'=>5,//max value
													'starCount'=>5, //number of stars
													'cssFile'=>Yii::app()->theme->getBaseUrl().'/css/rating/jquery.rating.css',
											));
											echo '&nbsp;';
										}elseif($field->type=='assignment'){
											echo empty($model->$fieldName)?"&nbsp;":UserChild::getUserLinks($model->$fieldName);
										}elseif($field->type=='visibility'){
											switch($model->$fieldName){
												case '1':
													echo Yii::t('app','Public'); break;
												case '0': 
													echo Yii::t('app','Private'); break;
												case '2':
													echo Yii::t('app','User\'s Groups'); break;
												default:
													echo '&nbsp;';
											}
										}elseif($field->type=='email'){
										
											if(empty($model->$fieldName))
												echo '&nbsp;';
											else {
												$mailtoLabel = isset($model->name)? '"'.$model->name.'" <'.$model->$fieldName.'>' : $model->$fieldName;
												echo CHtml::mailto($model->$fieldName,$mailtoLabel);
											}
										}elseif($field->type=='url') {
											if(empty($model->$fieldName)) {
												$text = '&nbsp;';
											} elseif(!empty($field->linkType)) {
												switch($field->linkType) {
													case 'skype':
														$text = '<a href="callto:'.$model->$fieldName.'">'.$model->$fieldName.'</a>';
														break;
													case 'googleplus':
														$text = '<a href="http://plus.google.com/'.$model->$fieldName.'">'.$model->$fieldName.'</a>';
														break;
													case 'twitter':
														$text = '<a href="http://www.twitter.com/#!/'.$model->$fieldName.'">'.$model->$fieldName.'</a>';
														break;
													case 'linkedin':
														$text = '<a href="http://www.linkedin.com/in/'.$model->$fieldName.'">'.$model->$fieldName.'</a>';
														break;
													default:
														$text = '<a href="http://www.'.$field->linkType.'.com/'.$model->$fieldName.'">'.$model->$fieldName.'</a>';
												}
											} else {
												$text = trim(preg_replace(
													array(
														'/(?(?=<a[^>]*>.+<\/a>)(?:<a[^>]*>.+<\/a>)|([^="\']?)((?:https?|ftp|bf2|):\/\/[^<> \n\r]+))/iex',
														'/<a([^>]*)target="?[^"\']+"?/i',
														'/<a([^>]+)>/i',
														'/(^|\s|>)(www.[^<> \n\r]+)/iex',
													),
													array(
														"stripslashes((strlen('\\2')>0?'\\1<a href=\"\\2\" target=\"_blank\">".Yii::t($modelName,$field->attributeLabel)."</a>\\3':'\\0'))",
														'<a\\1 target="_blank"',
														'<a\\1 target="_blank">',
														"stripslashes((strlen('\\2')>0?'\\1<a href=\"http://\\2\" target=\"_blank\">".Yii::t($modelName,$field->attributeLabel)."</a>\\3':'\\0'))",
													),
													$model->$fieldName
												));
											}
											echo $text;
										}elseif($field->type=='link') {
											if(!empty($model->$fieldName) && is_numeric($model->$fieldName)){
												$type=ucfirst($field->linkType);
												eval("\$lookupModel=$type::model()->findByPk(".$model->$fieldName.");");
												if(isset($lookupModel))
													eval("echo CHtml::link($type::model()->findByPk(".$model->$fieldName.")->name,array('/".$field->linkType."/".$model->$fieldName."'),array('target'=>'_blank'));");
											}elseif(!empty($model->$fieldName)){
												echo $model->$fieldName;
											}else{
												echo '&nbsp;';
											}
										} elseif($field->type=='boolean') {
											echo CHtml::checkbox('',$model->$fieldName,array('onclick'=>'return false;', 'onkeydown'=>'return false;'));
											
										} elseif($field->type == 'currency') {
											if($model instanceof Product) // products have their own currency
												echo Yii::app()->locale->numberFormatter->formatCurrency($model->$fieldName, $model->currency);
											elseif(!empty($model->$fieldName))
												echo Yii::app()->locale->numberFormatter->formatCurrency($model->$fieldName, Yii::app()->params['currency']);
											else
												echo '&nbsp;';
										} elseif($field->type == 'dropdown') {
											echo empty($model->$fieldName)? '&nbsp;' : Yii::t(strtolower(Yii::app()->controller->id),$model->$fieldName);
										} elseif($field->type=='text'){
                                                                                        echo empty($model->$fieldName)? '&nbsp;' : $this->convertUrls($model->$fieldName);     
                                                                                }else{
											echo empty($model->$fieldName)? '&nbsp;' : $model->$fieldName;
										}
									}
								}
								unset($item);
								echo '</div></div>';
							}
						}
						echo '</td>';
					}
				}
				unset($col);
				echo '</tr>';
			}
			echo '</table></div>';
		}
		unset($row);
		echo '</div>';
		$i++;
	}
}
echo '</div>';
}
