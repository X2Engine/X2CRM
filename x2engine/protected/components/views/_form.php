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

$showSocialMedia = Yii::app()->params->profile->showSocialMedia;

Yii::app()->clientScript->registerScript('formUIScripts',"
$('.x2-layout.form-view :input').change(function() {
	$('#save-button, #save-button1, #save-button2, h2 a.x2-button').addClass('highlight');
});
",CClientScript::POS_READY);

Yii::app()->clientScript->registerScript('datePickerDefault',"
    $.datepicker.setDefaults( $.datepicker.regional[ ''] );
",CClientScript::POS_READY);

Yii::app()->clientScript->registerScript('setFormName',"
window.formName = '$modelName';
",CClientScript::POS_HEAD);

$renderFormTags = !isset($form);

if((isset($isQuickCreate) && !$isQuickCreate) || !isset($isQuickCreate)){

	if($renderFormTags) {
		$form=$this->beginWidget('CActiveForm', array(
			'id'=>$modelName.'-form',
			'enableAjaxValidation'=>false,
		));
	}
}

echo '<em style="display:block;margin:5px;">'.
	Yii::t('app','Fields with <span class="required">*</span> are required.')."</em>\n";

// Construct criteria for finding the right form layout.
$attributes = array('model'=>ucfirst($modelName),'defaultForm'=>1);
// If the $scenario variable is set in the rendering context, a special
// different form should be retrieved.
$attributes['scenario'] = isset($scenario) ? $scenario : 'Default';
$layout = FormLayout::model()->findByAttributes($attributes);

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
		
		if(!isset($specialFields))
			$specialFields = array();
		
		if(!Yii::app()->params->isAdmin && !empty(Yii::app()->params->roles)) {
			$rolePermissions = Yii::app()->db->createCommand()
				->select('fieldId, permission')
				->from('x2_role_to_permission')
				->join('x2_fields','x2_fields.modelName="'.$modelName.
					'" AND x2_fields.id=fieldId AND roleId IN ('.implode(',',Yii::app()->params->roles).')')
				->queryAll();
		
			foreach($rolePermissions as &$permission) {
				if(!isset($fieldPermissions[$permission['fieldId']]) || 
				   $fieldPermissions[$permission['fieldId']] < (int)$permission['permission']) {
					$fieldPermissions[$permission['fieldId']] = (int)$permission['permission'];
				}
			}
		}
		
		$i = 0;
		foreach($layoutData['sections'] as &$section) {
			$noItems = true; // if no items, don't display section
			// set defaults
			if(!isset($section['title'])) $section['title'] = '';
			if(!isset($section['collapsible'])) $section['collapsible'] = false;
			if(!isset($section['rows'])) $section['rows'] = array();
			if(!isset($formSettings[$i])) $formSettings[$i] = 1;
		
			$collapsed = !$formSettings[$i] && $section['collapsible'];

			$htmlString = '';
		
			$htmlString .= '<div class="formSection';
			if($section['collapsible'])
				$htmlString .= ' collapsible';
			if(!$collapsed)
				$htmlString .= ' showSection';
			$htmlString .= '">';
		
			$htmlString .= '<div class="formSectionHeader">';
			if($section['collapsible']) {
				$htmlString .= '<a href="javascript:void(0)" class="formSectionHide">[&ndash;]</a>';
				$htmlString .= '<a href="javascript:void(0)" class="formSectionShow">[+]</a>';
			}
			if(!empty($section['title'])) {
				$htmlString .= '<span class="sectionTitle" title="'.addslashes($section['title']).'">'.
					Yii::t(strtolower(Yii::app()->controller->id),$section['title']).'</span>';
			} else {
				$htmlString .= '<span class="sectionTitle"></span>';
			}
			$htmlString .= '</div>';
		
			if(!empty($section['rows'])) {
				$htmlString .= '<div class="tableWrapper"><table>';
		
				foreach($section['rows'] as &$row) {
					$htmlString .= '<tr class="formSectionRow">';
					if(isset($row['cols'])) {
						foreach($row['cols'] as &$col) {
		
							$width = isset($col['width'])? ' style="width:'.$col['width'].'px"' : '';
							$htmlString .= "<td$width>";
							if(isset($col['items'])) {
								foreach($col['items'] as &$item) {
		
									if(isset($item['name'],$item['labelType'],$item['readOnly'],
										$item['height'],$item['width'])) {

										$fieldName = preg_replace('/^formItem_/u','',$item['name']);
		
										if(isset($fields[$fieldName])) {
											$field = &$fields[$fieldName];
		
											if(($field->fieldName == "company" || 
											   $field->fieldName == "accountName") && 
											   isset($hideAccount) && $hideAccount == true) {
												continue;
											}
		
											if(isset($fieldPermissions[$field->id])) {
												if($fieldPermissions[$field->id] == 0) {
													unset($item);
													$htmlString .= '</div></div>';
													continue;
												} elseif($fieldPermissions[$field->id] == 1) {
													$item['readOnly']=true;
												}
											}
                                            $noItems = false;
		
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
		
											$htmlString .= '<div class="formItem '.$labelClass.'">';
											$htmlString .= $form->labelEx($model,$field->fieldName);
											$htmlString .= '<div class="formInputBox" style="width:'.$item['width'].
												'px;height:'.$item['height'].';">';
											$default=$model->$fieldName==$field->attributeLabel;
		
											if(isset($specialFields[$fieldName])) {
												$htmlString .= $specialFields[$fieldName];
											} else {
												$htmlString .= $model->renderInput($fieldName,array(
													'tabindex'=>isset($item['tabindex'])? $item['tabindex'] : null,
													'disabled'=>$item['readOnly']? 'disabled' : null,
												));
											}
		                                    $htmlString .= "</div>";

                                            if($field->type == 'link' && in_array($field->linkType,array('Accounts','Contacts'))) {
                                                // As the design currently stands, only the Accounts and Contacts controllers'
                                                // create actions were written to account for the use case where their forms are loaded
                                                // via ajax. As such, only those model types get the magic "+" button for lookup-type fields.
                                                // The way it was in version 3.5.6 and earlier was hard-coded conditional references to the
                                                // "company" field in contacts, and "contactId" field in services (i.e. it would only show
                                                // up next to the inputs for these two fields). The criteria were expanded in 3.6 to include
                                                // all link type fields of contact/account link types to make the + button available in custom
                                                // modules.
                                                //
                                                // It would be useful, in the future, to refactor all the code dealing with AJAX vs. full-page
                                                // create/update forms into x2base and come up with a consolidated, unified method of loading
                                                // forms asynchronously, and generalize the JavaScript that mediates the creation of a linked
                                                // record on-the-fly. Needless to say, this is (again) a project for the future.
                                                $htmlString .= '<span class="create-'.lcfirst(rtrim($field->linkType,'s')).'">+</span>';
                                            }		
										}
		
									}
									unset($item);
									$htmlString .= '</div>';
								}
							}
							$htmlString .= '</td>';
						}
					}
					unset($col);
					$htmlString .= '</tr>';
				}
				$htmlString .= '</table></div>';
			}
			unset($row);
			$htmlString .= '</div>';
			if (!$noItems) echo $htmlString;
			$i++;

		}
	}
	?>
	</div>
<?php
}

if((isset($isQuickCreate) && !$isQuickCreate) || !isset($isQuickCreate)){
	if($renderFormTags) {
		echo '	<div class="row buttons">'."\n";
		echo '		'.CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),
			array('class'=>'x2-button','id'=>'save-button','tabindex'=>24))."\n";
		echo "	</div>\n";

		$this->endWidget();
	}
}

Yii::app()->clientScript->registerScript('mask-currency','
    $(".currency-field").maskMoney("mask");
',CClientScript::POS_READY);

?>

