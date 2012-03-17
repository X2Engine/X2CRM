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
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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

$showSocialMedia = Yii::app()->params->profile->showSocialMedia;
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/x2forms.js');
Yii::app()->clientScript->registerScript('formUIScripts',"
$('.x2-layout.form-view :input').change(function() {
	$('#save-button, #save-button1, #save-button2, h2 a.x2-button').addClass('highlight');
});
",CClientScript::POS_READY);

Yii::app()->clientScript->registerScript('setFormName',"
window.formName = '$modelName';
",CClientScript::POS_HEAD);
if((isset($isQuickCreate) && !$isQuickCreate) || !isset($isQuickCreate)){
$form=$this->beginWidget('CActiveForm', array(
	'id'=>$modelName.'-form',
	'enableAjaxValidation'=>false,
));
}
echo '<em style="display:block;margin:5px;">'.Yii::t('app','Fields with <span class="required">*</span> are required.')."</em>\n";


$layout = FormLayout::model()->findByAttributes(array('model'=>ucfirst($modelName),'defaultForm'=>1));
if(isset($layout)) {

echo '<div class="x2-layout form-view">';

// $temp=RoleToUser::model()->findAllByAttributes(array('userId'=>Yii::app()->user->getId(),'type'=>'user'));
// $roles=array();
// foreach($temp as $link){
    // $roles[]=$link->roleId;
// }
// /* x2temp */
// $groups=GroupToUser::model()->findAllByAttributes(array('userId'=>Yii::app()->user->getId()));
// foreach($groups as $link){
    // $tempRole=RoleToUser::model()->findByAttributes(array('userId'=>$link->groupId, 'type'=>'group'));
    // if(isset($tempRole))
        // $roles[]=$tempRole->roleId; 
// }
/* end x2temp */


$fields = array();
$fieldModels = Fields::model()->findAllByAttributes(array('modelName'=>ucfirst($modelName)));
foreach($fieldModels as &$fieldModel)
	$fields[$fieldModel->fieldName] = $fieldModel;
unset($fieldModel);

echo $form->errorSummary($model).' ';

$layoutData = json_decode($layout->layout,true);
$formSettings = ProfileChild::getFormSettings($modelName);

if(isset($layoutData['sections']) && count($layoutData['sections']) > 0) {

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
										$field = &$fields[$fieldName];
										// $fieldPerms=RoleToPermission::model()->findAllByAttributes(array('fieldId'=>$field->id));
										// $perms=array();
										// foreach($fieldPerms as $permission){
											// $perms[$permission->roleId]=$permission->permission;
										// }
										// $tempPerm=2;
										// foreach($roles as $role){
											// if(array_search($role,array_keys($perms))!==false){
												// if($perms[$role]<$tempPerm)
													// $tempPerm=$perms[$role];
											// }
										// }
										// if($tempPerm==0){
										if(isset($fieldPermissions[$field->id])) {
											if($fieldPermissions[$field->id] == 0) {
												unset($item);
												echo '</div></div>';
												continue;
											}
											elseif($fieldPermissions[$field->id] == 1)
												$item['readOnly']=true;
										}
										
										$labelType = isset($item['labelType'])? $item['labelType'] : 'top';
										switch($labelType) {
											case 'inline':	$labelClass = 'inlineLabel'; break;
											case 'none':	$labelClass = 'noLabel'; break;
											case 'left':	$labelClass = 'leftLabel'; break;
											case 'top': 
											default:		$labelClass = 'topLabel';
										}

										// set value of field to label if this is an inline labeled field
										if(empty($model->$fieldName) && $labelType == 'inline')
											$model->$fieldName = $field->attributeLabel;
										
										if($field->type!='text')
											$item['height'] = 'auto';
										else
											$item['height'] .= 'px';
										
										echo '<div class="formItem '.$labelClass.'">';
										echo $form->labelEx($model,$field->fieldName);
										echo '<div class="formInputBox" style="width:'.$item['width'].'px;height:'.$item['height'].';">';
										$default=$model->$fieldName==$field->attributeLabel;
										if($field->type == 'varchar' || $field->type=='email' || $field->type=='url' ||
																						$field->type=='int' || $field->type=='float'|| $field->type=='currency') {
											echo $form->textField($model,$field->fieldName,array(
													'tabindex'=>isset($item['tabindex'])? $item['tabindex'] : null,
													'disabled'=>$item['readOnly']? 'disabled' : null,
													'title'=>$field->attributeLabel,
													'style'=>$default?'color:#aaa;':null,
											));
										} else if($field->type == 'text') {
											echo $form->textArea($model,$field->fieldName,array(
												'tabindex'=>isset($item['tabindex'])? $item['tabindex'] : null,
												'disabled'=>$item['readOnly']? 'disabled' : null,
												'title'=>$field->attributeLabel,
												'style'=>$default?'color:#aaa;':null,
										));
									} else if($field->type == 'text') {
										echo $form->textArea($model,$field->fieldName,array(
											'tabindex'=>isset($item['tabindex'])? $item['tabindex'] : null,
											'disabled'=>$item['readOnly']? 'disabled' : null,
											'title'=>$field->attributeLabel,
											'onfocus'=>$default? 'toggleText(this);' : null,
											'onblur'=>$default? 'toggleText(this);' : null,
											'style'=>$default?'color:#aaa;':null,
										));
									} elseif($field->type=='date') {
										$model->$fieldName = $this->formatDate($model->$fieldName);
										Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
										$this->widget('CJuiDateTimePicker',array(
											'model'=>$model, //Model object
											'attribute'=>$field->fieldName, //attribute name
											'mode'=>'date', //use "time","date" or "datetime" (default)
												'options'=>array(
													'dateFormat'=>$this->formatDatePicker(),

												), // jquery plugin options
												'htmlOptions'=>array(
													'tabindex'=>isset($item['tabindex'])? $item['tabindex'] : null,
													'disabled'=>$item['readOnly']? 'disabled' : null,
													'title'=>$field->attributeLabel,
												),
												'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
											)); 
										} elseif($field->type=='dropdown') {
											$dropdown=Dropdowns::model()->findByPk($field->linkType);
											
											$dropdowns = json_decode($dropdown->options,true);
											foreach(array_keys($dropdowns) as $key)
												$dropdowns[$key] = Yii::t(strtolower(Yii::app()->controller->id),$dropdowns[$key]);
											
											echo $form->dropDownList($model,$field->fieldName,$dropdowns, array(
													'tabindex'=>isset($item['tabindex'])? $item['tabindex'] : null,
													'disabled'=>$item['readOnly']? 'disabled' : null,
													'title'=>$field->attributeLabel,
											));
											
										} elseif($field->type=='link') {
											$default = empty($model->$fieldName);
											if($default) 
												$model->$fieldName = "";
											echo CHtml::hiddenField($fieldName."_id",'',array('id'=>$fieldName."_id"));
											$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
													'name'=>'autoselect_'.$fieldName,
													'source' => $this->createUrl("/".$field->linkType.'/getItems'),
													'value'=>$model->$fieldName,
													'options'=>array(
														'minLength'=>'2',
														
														'select'=>'js:function( event, ui ) {
															$("#'.$fieldName.'_id").val(ui.item.id);
															$(this).val(ui.item.value);
															return false;
														}',

													),
													'htmlOptions'=>array(
														'tabindex'=>isset($item['tabindex'])? $item['tabindex'] : null,
														'disabled'=>$item['readOnly']? 'disabled' : null,
														'title'=>$field->attributeLabel,
													),
											));

									} elseif($field->type=='rating') {
										$this->widget('CStarRating',array(
												'model'=>$model,
												'attribute'=>$field->fieldName,
												//'callback'=>'highlightSave',
												'minRating'=>1, //minimal valuez
												'maxRating'=>5,//max value
												'starCount'=>5, //number of stars
												'cssFile'=>Yii::app()->theme->getBaseUrl().'/css/rating/jquery.rating.css',
										)); 
									} elseif($field->type=='boolean') {
											echo '<div class="checkboxWrapper">';
										echo $form->checkBox($model,$field->fieldName,array(
											'unchecked'=>0,
											'tabindex'=>isset($item['tabindex'])? $item['tabindex'] : null,
											'disabled'=>$item['readOnly']? 'disabled' : null,
											'title'=>$field->attributeLabel,
											)).'</div>';
									} elseif($field->type=='assignment') {
										if($field->linkType!='multiple')
											echo $form->dropDownList($model, $fieldName, $users, array(
												'tabindex'=>isset($item['tabindex'])? $item['tabindex'] : null,
												'disabled'=>$item['readOnly']? 'disabled' : null,
												'title'=>$field->attributeLabel,
												'id'=>$field->modelName .'_'. $fieldName .'_assignedToDropdown',
											));
										else
											echo $form->dropDownList($model, $fieldName, $users, array(
												'tabindex'=>isset($item['tabindex'])? $item['tabindex'] : null,
												'disabled'=>$item['readOnly']? 'disabled' : null,
												'title'=>$field->attributeLabel,
												'id'=>$field->modelName .'_'. $fieldName .'_assignedToDropdown',
												'multiple'=>'multiple',
											));
                                                                                
								   /* x2temp */
                                                                                echo '<div class="checkboxWrapper">';
										echo CHtml::checkBox('group','',array(
												'tabindex'=>isset($item['tabindex'])? $item['tabindex'] : null,
												'disabled'=>$item['readOnly']? 'disabled' : null,
												'title'=>$field->attributeLabel,
												'id'=>$field->modelName .'_'. $fieldName .'_groupCheckbox',
												'ajax'=>array(
													'type'=>'POST', //request type
														'url'=>CController::createUrl('/groups/getGroups'), //url to call.
														//Style: CController::createUrl('currentController/methodToCall')
														'update'=>'#'.$field->modelName .'_'. $fieldName .'_assignedToDropdown', //selector to update
														'complete'=>'function(){
															if($("#'.$field->modelName .'_'. $fieldName .'_groupCheckbox").attr("checked")!="checked"){
																$("#'.$field->modelName .'_'. $fieldName .'_groupCheckbox").attr("checked","checked");
																$("#'.$field->modelName .'_'. $fieldName .'_visibility option[value=\'2\']").remove();
															}else{
																$("#'.$field->modelName .'_'. $fieldName .'_groupCheckbox").removeAttr("checked");
																$("#'.$field->modelName .'_'. $fieldName .'_visibility").append(
																	$("<option></option>").val("2").html("User\'s Groups")
																);
															}
														}'
												)
											)).'</div><label for="group" class="groupLabel">'.Yii::t('app','Group?').'</label>';
										/* end x2temp */  
										} elseif($field->type=='association') {
											if($field->linkType!='multiple')
												echo $form->dropDownList($model, $fieldName, $contacts, array(
																'tabindex'=>isset($item['tabindex'])? $item['tabindex'] : null,
																'disabled'=>$item['readOnly']? 'disabled' : null,
																'title'=>$field->attributeLabel,
												));
											else
												echo $form->listBox($model, $fieldName, $contacts, array(
																'tabindex'=>isset($item['tabindex'])? $item['tabindex'] : null,
																'disabled'=>$item['readOnly']? 'disabled' : null,
																'title'=>$field->attributeLabel,
																'multiple'=>'multiple',
												));
										} elseif($field->type=='visibility') {
												echo $form->dropDownList($model,$field->fieldName,array(1=>'Public',0=>'Private',2=>'User\'s Groups'), array(
																'tabindex'=>isset($item['tabindex'])? $item['tabindex'] : null,
																'disabled'=>$item['readOnly']? 'disabled' : null,
																'title'=>$field->attributeLabel,
																'id'=>$field->modelName."_visibility",
												));
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
?>
</div>
<?php
}
if((isset($isQuickCreate) && !$isQuickCreate) || !isset($isQuickCreate)){
echo '	<div class="row buttons">'."\n";
echo '		'.CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button','id'=>'save-button','tabindex'=>24))."\n";
echo "	</div>\n";

$this->endWidget();
}
?>
