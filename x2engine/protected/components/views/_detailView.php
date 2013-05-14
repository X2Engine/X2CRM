<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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

$attributeLabels = $model->attributeLabels();

// $showSocialMedia = Yii::app()->params->profile->showSocialMedia;

// $showWorkflow = Yii::app()->params->profile->showWorkflow;

$cs = Yii::app()->getClientScript();
$cs->registerCoreScript('rating');
$cs->registerCssFile($cs->getCoreScriptUrl().'/rating/jquery.rating.css');

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

// remove this later, once all models extend X2Models
if(method_exists($model,'getFields')) {
	$fields = $model->getFields(true);
} else {
	foreach(X2Model::model('Fields')->findAllByAttributes(array('modelName'=>ucfirst($modelName))) as $fieldModel)
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

if(!Yii::app()->params->isAdmin && !empty(Yii::app()->params->roles)) {
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
if(!isset($specialFields))
	$specialFields = array();

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



									$class = 'formInputBox';
									$style = 'width:'.$item['width'].'px;';
									if($field->type == 'text') {
										$class .= ' textBox';
										$style .= 'min-height:'.$item['height'].'px;';
									}

									// if($field->type == 'text')
										// $style .= 'min-height:'.$item['height'].'px;';
									echo '<div class="',$class,'" style="',$style,'">';

									if(isset($specialFields[$fieldName]))
										$fieldHtml = $specialFields[$fieldName];
									else
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
