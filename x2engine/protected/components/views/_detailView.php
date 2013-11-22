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

$attributeLabels = $model->attributeLabels();

// $showSocialMedia = Yii::app()->params->profile->showSocialMedia;

// $showWorkflow = Yii::app()->params->profile->showWorkflow;

$cs = Yii::app()->getClientScript();
$cs->registerCoreScript('rating');
//$cs->registerCssFile($cs->getCoreScriptUrl().'/rating/jquery.rating.css');
$cs->registerCssFile (Yii::app()->getTheme()->getBaseUrl().'/css/rating/jquery.rating.css');

if($modelName=='contacts' || $modelName=='opportunities'){
	$cs->registerScript('toggleWorkflow', "
	function showWorkflow() {
		$('tr#workflow-row').show();
		$('tr#workflow-toggle').hide();
	}
	function hideWorkflow() {
		$('tr#workflow-row').hide();
		$('tr#workflow-toggle').show();
	}
	",CClientScript::POS_HEAD);
}

// $(function() {\n"
// .($showWorkflow? "showWorkflow();\n" : "hideWorkflow()\n")
// ."});",CClientScript::POS_HEAD);

$cs->registerScript('setFormName',"
window.formName = '$modelName';
",CClientScript::POS_HEAD);

$layoutData = Yii::app()->cache->get('form_'.$modelName);	// check the app cache for the data
$fields = array();

$scenario = isset($scenario) ? $scenario : 'Default';

// remove this later, once all models extend X2Models
if(method_exists($model,'getFields')) {
	$fields = $model->getFields(true);
} else {
	foreach(X2Model::model('Fields')->findAllByAttributes(
		array('modelName'=>ucfirst($modelName))) as $fieldModel) {

		$fields[$fieldModel->fieldName] = $fieldModel;
	}
}

if($layoutData === false) {
	$layout = FormLayout::model()->findByAttributes(
		array('model'=>ucfirst($modelName),'defaultView'=>1,'scenario'=>$scenario));

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

	if(!isset($specialFields))
		$specialFields = array();
	if(!isset($suppressFields))
		$suppressFields = array();
	
	$i = 0;
	foreach($layoutData['sections'] as &$section) {
		$noItems = true; // if no items, don't display section
		// set defaults
		if(!isset($section['title'])) $section['title'] = '';
		if(!isset($section['collapsible'])) $section['collapsible'] = false;
		if(!isset($section['rows'])) $section['rows'] = array();
		if(!isset($formSettings[$i])) {
			$formSettings[$i] = 1;
		}
	
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
			$htmlString .= '<div class="tableWrapper"';
			if($collapsed)
				$htmlString .= ' style="display:none;"';
			$htmlString .= '><table>';
	
			foreach($section['rows'] as &$row) {
				$htmlString .= '<tr class="formSectionRow">';
				if(isset($row['cols'])) {
					foreach($row['cols'] as &$col) {
	
						$width = isset($col['width'])? ' style="width:'.$col['width'].'px"' : '';
						$htmlString .= "<td$width>";
						if(isset($col['items'])) {
							foreach($col['items'] as &$item) {
	
	
								if(isset($item['name'],$item['labelType'],$item['readOnly'],$item['height'],
									$item['width'])) {

									$fieldName = preg_replace('/^formItem_/u','',$item['name']);
									if(isset($fields[$fieldName])) {
										$field = $fields[$fieldName];
	
										if(in_array ($fieldName, $suppressFields) || 
										   isset($fieldPermissions[$field->id]) && 
										   $fieldPermissions[$field->id] == 0) {
											unset($item);
											$htmlString .= '</div></div>';
											continue;
										} else {
											$noItems = false;
										}
	
										$labelType = isset($item['labelType'])? $item['labelType'] : 'top';
										switch($labelType) {
											case 'inline':	$labelClass = 'inlineLabel'; break;
											case 'none':	$labelClass = 'noLabel'; break;
											case 'left':	$labelClass = 'leftLabel'; break;
											case 'top':
											default:		$labelClass = 'topLabel';
										}
	
										$htmlString .= "<div id=\"{$field->modelName}_{$field->fieldName}_field\"".
											" class=\"formItem $labelClass\">";
										$htmlString .= CHtml::label($model->getAttributeLabel($field->fieldName),
											false);
	
										$class = 'formInputBox';
										$style = 'width:'.$item['width'].'px;';
										if($field->type == 'text') {
											$class .= ' textBox';
											$style .= 'min-height:'.$item['height'].'px;';
										}
	
										// if($field->type == 'text')
											// $style .= 'min-height:'.$item['height'].'px;';
										$htmlString .= '<div class="'.$class.'" style="'.$style.'">';
	
										if(isset($specialFields[$fieldName]))
											$fieldHtml = $specialFields[$fieldName];
										else
											$fieldHtml = $model->renderAttribute($field->fieldName,true,false);
										if(empty($fieldHtml))
											$htmlString .= '&nbsp;';
										else
											$htmlString .= $fieldHtml;
									}
								}
								unset($item);
								$htmlString .= '</div></div>';
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
	echo '</div>';
}

