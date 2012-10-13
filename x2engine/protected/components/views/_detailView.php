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

$attributeLabels = $model->attributeLabels();

$showSocialMedia = Yii::app()->params->profile->showSocialMedia;

$showWorkflow = Yii::app()->params->profile->showWorkflow;
if($modelName=='contacts' || $modelName=='opportunities'){

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

$layoutData = Yii::app()->cache->get('form_'.$modelName);	// check the app cache for the data
$fields = array();

// remove this later, once all models extend X2Models
if(method_exists($model,'getFields')) {
	$fields = $model->getFields(true);
} else {
	foreach(CActiveRecord::model('Fields')->findAllByAttributes(array('modelName'=>ucfirst($modelName))) as $fieldModel)
		$fields[$fieldModel->fieldName] = $fieldModel;
}
if($layoutData === false) {
	$layout = FormLayout::model()->findByAttributes(array('model'=>ucfirst($modelName),'defaultView'=>1));
	
	if(isset($layout)) {
		$layoutData = json_decode($layout->layout,true);
		Yii::app()->cache->set('form_'.$modelName,$layoutData,0);	// cache the data
	}
}
	
if($layoutData !== false && isset($layoutData['sections']) && count($layoutData['sections']) > 0) {
?>
<div class="x2-layout<?php if(isset($halfWidth) && $halfWidth) echo ' half-width'; ?>">
<?php
$formSettings = ProfileChild::getFormSettings($modelName);

$fieldPermissions = array();
if(!empty(Yii::app()->params->roles)) {
	$rolePermissions = Yii::app()->db->createCommand()
		->select('fieldId, permission')
		->from('x2_role_to_permission')
		->join('x2_fields','x2_fields.modelName="'.$modelName.'" AND x2_fields.id=fieldId AND roleId IN ('.implode(',',Yii::app()->params->roles).')')
		->queryAll();

	foreach($rolePermissions as &$permission) {
		if(!isset($fieldPermissions[$permission['fieldId']]) || $fieldPermissions[$permission['fieldId']] < (int)$permission['permission'])
			$fieldPermissions[$permission['fieldId']] = (int)$permission['permission'];
	}
}

$i = 0;
foreach($layoutData['sections'] as &$section) {
	// set defaults
	if(!isset($section['title'])) $section['title'] = '';
	if(!isset($section['collapsible'])) $section['collapsible'] = false;
	if(!isset($section['rows'])) $section['rows'] = array();
	if(!isset($formSettings[$i])) $formSettings[$i] = 1;
	
	$collapsed = !$formSettings[$i] && $section['collapsible'];
	
	echo '<div class="formSection';
	if($section['collapsible'])
		echo ' collapsible';
	if(!$collapsed)
		echo ' showSection';
	echo '">';
	
	if($section['collapsible'] || !empty($section['title'])) {
		echo '<div class="formSectionHeader">';
		if($section['collapsible']) {
			echo '<a href="javascript:void(0)" class="formSectionHide">[&ndash;]</a>';
			echo '<a href="javascript:void(0)" class="formSectionShow">[+]</a>';
		}
		if(!empty($section['title']))
			echo '<span class="sectionTitle" title="',addslashes($section['title']),'">',Yii::t(strtolower(Yii::app()->controller->id),$section['title']),'</span>';
		echo '</div>';
	}
	if(!empty($section['rows'])) {
		echo '<div class="tableWrapper"';
		if($collapsed)
			echo ' style="display:none;"';
		echo '><table>';
	
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
									
										if(isset($fieldPermissions[$field->id]) && $fieldPermissions[$field->id] == 0) {
											unset($item);
											echo '</div></div>';
											continue;
										}
									// $fieldPerms=RoleToPermission::model()->findAllByAttributes(array('fieldId'=>$field->id));
									// $perms=array();
									// foreach($fieldPerms as $permission){
										// $perms[$permission->roleId]=$permission->permission;
									// }
									// $tempPerm=2;
									// foreach(Yii::app()->params->roles as $role){
										// if(array_search($role,array_keys($perms))!==false){
											// if($perms[$role]<$tempPerm)
												// $tempPerm=$perms[$role];
										// }
									// }
									// if($tempPerm==0){
										// unset($item);
										// echo '</div></div>';
										// continue;
									// }
									
									$labelType = isset($item['labelType'])? $item['labelType'] : 'top';
									switch($labelType) {
										case 'inline':	$labelClass = 'inlineLabel'; break;
										case 'none':	$labelClass = 'noLabel'; break;
										case 'left':	$labelClass = 'leftLabel'; break;
										case 'top': 
										default:		$labelClass = 'topLabel';
									}
									
									echo "<div id=\"{$field->modelName}_{$field->fieldName}_field\" class=\"formItem $labelClass\">";
									//echo '<div id="'.$modelName.'_'.$fieldName.'_inputBox" class="formItem '.$labelClass.'">';
									echo CHtml::label($model->getAttributeLabel($field->fieldName),false);
										
									$style = 'width:'.$item['width'].'px;';
									if($field->type == 'text')
										$style .= 'min-height:'.$item['height'].'px;';
									echo '<div class="formInputBox" style="'.$style.'">';
									
									$fieldHtml = $model->renderAttribute($field->fieldName,true,false);
									if(empty($fieldHtml))
										echo '&nbsp;';
									else
										echo $fieldHtml;
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
echo '</div>';
}
