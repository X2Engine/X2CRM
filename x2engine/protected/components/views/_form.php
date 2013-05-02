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

if(!isset($specialFields))
	$specialFields = array();

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
									if( ($field->fieldName == "company" || $field->fieldName == "accountName") && isset($hideAccount) && $hideAccount == true)
										continue;

									if(isset($fieldPermissions[$field->id])) {
										if($fieldPermissions[$field->id] == 0) {
											unset($item);
											echo '</div></div>';
											continue;
										} elseif($fieldPermissions[$field->id] == 1) {
											$item['readOnly']=true;
										}
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

									if(isset($specialFields[$fieldName])) {
										echo $specialFields[$fieldName];
									} else {
										echo $model->renderInput($fieldName,array(
											'tabindex'=>isset($item['tabindex'])? $item['tabindex'] : null,
											'disabled'=>$item['readOnly']? 'disabled' : null,
										));
									}
                                    echo "</div>";
                                    if($field->fieldName == 'company') // add button to Acount label to create new account
                                        echo '<span class="create-account">+</span>';

                                    if($modelName == "services" && $field->fieldName == 'contactId')
                                        echo '<span id="create-contact">+</span>';

								}

							}
							unset($item);
							echo '</div>';
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
	if($renderFormTags) {
		echo '	<div class="row buttons">'."\n";
		echo '		'.CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button','id'=>'save-button','tabindex'=>24))."\n";
		echo "	</div>\n";

		$this->endWidget();
	}
}
?>
