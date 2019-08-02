<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




// Yii::app()->clientScript->registerScript('formEditor', "
// ",CClientScript::POS_READY);

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/x2formEditor.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/lib/colResizable/colResizable.js');

if(isset($layoutModel) && !empty($layoutModel->layout)){
    Yii::app()->clientScript->registerScript('loadForm', '
	x2.formEditor.loadFormJson(\''.preg_replace('/\\"/u', '\\\\"', addcslashes($layoutModel->layout, "'\\")).'\');
	', CClientScript::POS_READY);
}

// version navigation
Yii::app()->clientScript->registerScript('formVersionNav', "
$('#modelList').change(function() {
	if(window.layoutChanged && !confirm('".addslashes(Yii::t('admin', 'Leave without saving changes?'))."'))
		$(this).val('".$modelName."');
	else
		window.location.href = '".CHtml::normalizeUrl(array('editor'))."?model='+$(this).val();
});
$('#versionList').change(function(e) {
	if(window.layoutChanged && !confirm('".addslashes(Yii::t('admin', 'Leave without saving changes?'))."'))
		$(this).val('".$id."');
	else
		window.location.href = '".CHtml::normalizeUrl(array('editor'))."?model='+$('#modelList').val()+'&id='+$(this).val();
});
$('#newLayoutButton').click(function() {
	if(!window.layoutChanged || confirm('".addslashes(Yii::t('admin', 'Leave without saving changes?'))."')) {
		var layoutName = prompt('".addslashes(Yii::t('admin', 'Please enter a name for the new layout.'))."');
		if(layoutName != null && layoutName != '')
			window.location.href = '".CHtml::normalizeUrl(array('createFormLayout'))."?model='+$('#modelList').val()+'&newLayout=1&layoutName='+encodeURI(layoutName);
	}
});
$('#copyLayoutButton').click(function() {
	if(!window.layoutChanged || confirm('".addslashes(Yii::t('admin', 'Leave without saving changes?'))."')) {
		var layoutName = prompt('".addslashes(Yii::t('admin', 'Please enter a name for the new layout.'))."');
		if(layoutName != null && layoutName != '') {
			$('#layoutHiddenField').val(x2.formEditor.generateFormJson());
			$('#formEditorForm').attr('action','".CHtml::normalizeUrl(array('createFormLayout'))."?model='+$('#modelList').val()+'&newLayout=1&layoutName='+encodeURI(layoutName));
			$('#formEditorForm').unbind('submit').submit();
		}
	}
		// window.location.href = '".CHtml::normalizeUrl(array('deleteFormLayout'))."?id='+$('#versionList').val();
});
$('#deleteVersionButton').click(function() {
	if(confirm('".addslashes(Yii::t('admin', 'Are you sure you want to delete this layout?'))."'))
		window.location.href = '".CHtml::normalizeUrl(array('deleteFormLayout'))."?id='+$('#versionList').val();
});
", CClientScript::POS_READY);
?>
<div class="page-title"><h2><?php echo Yii::t('admin', 'Form Editor'); ?></h2></div>
<div class="form">
    <div style="width:600px;">
        <?php echo Yii::t('admin', 'Add a form row and drag and drop fields from the field list. Click save when finished.'); ?><br /><br />
        <?php echo Yii::t('admin', 'Each module can have multiple layouts, but only one view and one form can be active at any given time.'); ?>
        <?php echo Yii::t('admin', 'To choose which layout is used, select either "Default View" or "Default Form" or both depending on how you want the layout to be used.'); ?>
    </div>
</div>
<?php
echo CHtml::beginForm(array('editor', 'id' => $id), 'post', array('id' => 'formEditorForm'));
echo CHtml::hiddenField('layout', '', array('id' => 'layoutHiddenField'));
?>
<div class="form">
    <div class="row">
        <div class="cell">
            <?php echo CHtml::label(Yii::t('admin', 'Model'), 'modelList'); ?>
            <?php
            echo CHtml::dropDownList('model', $modelName, $modelList, array(
                'id' => 'modelList'
            ));
            ?>
        </div>
        <?php if(!empty($modelName)){ ?>
            <div class="cell">
                <?php echo CHtml::label(Yii::t('admin', 'Version'), 'versionList'); ?>
                <?php
                echo CHtml::dropDownList('id', $id, $versionList, array(
                    'id' => 'versionList'
                ));
                ?>
            </div>
            <div class="cell">
                <?php
                $scenarios = array();
                foreach(FormLayout::$scenarios as $scenario)
                    $scenarios[$scenario] = Yii::t('admin', $scenario);
                ?>
                <?php echo CHtml::label(Yii::t('admin', 'Scenario'), 'scenario'); ?>
                <?php
                echo CHtml::dropDownList('scenario', empty($layoutModel) ? 'Default' : $layoutModel->scenario, $scenarios, array(
                    'id' => 'scenario'
                ));
                ?>
            </div>
            <div class="cell" style="padding-top:11px;">
                <?php echo CHtml::button(Yii::t('admin', 'New'), array('id' => 'newLayoutButton', 'class' => 'x2-button small float')); ?>
            </div>
        <?php } ?>

        <?php if(count($versionList) > 1 && !empty($id)){ ?>
            <div class="cell" style="padding-top:11px;">
                <?php echo CHtml::button(Yii::t('admin', 'Copy'), array('id' => 'copyLayoutButton', 'class' => 'x2-button small float')); ?>
            </div>
            <div class="cell" style="padding-top:11px;">
                <?php echo CHtml::button(Yii::t('admin', 'Delete'), array('id' => 'deleteVersionButton', 'class' => 'x2-button small float')); ?>
            </div>
            <div class="cell">
                <?php echo CHtml::label(Yii::t('admin', 'Default View'), 'defaultView'); ?>
                <?php echo CHtml::checkbox('defaultView', $defaultView); ?>
            </div>
            <div class="cell">
                <?php echo CHtml::label(Yii::t('admin', 'Default Form'), 'defaultForm'); ?>
                <?php echo CHtml::checkbox('defaultForm', $defaultForm); ?>
            </div>
            <div class="cell right" style="padding-top:11px;">
                <?php echo CHtml::button(Yii::t('admin', 'Preview Mode'), array('id' => 'borderToggleButton', 'class' => 'x2-button right')); ?>
                <?php echo CHtml::submitButton(Yii::t('admin', 'Save'), array('class' => 'x2-button highlight right', 'style' => 'margin-right:5px;', 'id' => 'saveButton')); ?>
            </div>
        <?php } ?>
    </div>
</div>
<?php echo CHtml::endForm(); ?>
<?php if(!empty($modelName)){ ?>
    <div id="fieldListBox">
        <div id="fieldListTitle"><?php echo Yii::t('admin', 'Field List'); ?></div>
        <div id="editorFieldList" class="formSortable">
            <?php
            // get list of all fields, sort by attribute label alphabetically
            $fields = Fields::model()->findAllByAttributes(
                array('modelName' => $modelName),
                new CDbCriteria(
                    array(
                        'order' => 'attributeLabel ASC',
                        'condition' => 'keyType IS NULL OR (keyType!="PRI" AND keyType!="FIX")',
                    )
            ));
            foreach($fields as &$field){
                $type = '';
                switch($field->type){
                    case 'email':
                        $type = 'emailIcon';
                        break;
                    case 'phone':
                        $type = 'phoneIcon';
                        break;
                    case 'boolean':
                        $type = 'booleanIcon';
                        break;
                    case 'dropdown':
                        $type = 'dropdownIcon';
                        break;
                    case 'date':
                        $type = 'dateIcon';
                        break;
                    case 'text':
                        $type = 'textIcon';
                        break;
                    case 'percentage':
                        $type = 'percentageIcon';
                        break;
                    case 'credentials':
                        $type = 'dropdownIcon';
                        break;
                    default:
                        $type = 'varcharIcon';
                }

                echo '<div class="formItem leftLabel" id="formItem_'.$field->fieldName.'"><div class="formTabOrder"></div><label class="'.$type.'">'.X2Model::model($modelName)->getAttributeLabel($field->fieldName).'</label>';
                echo '<div class="formInputBox">';
                if($field->type == 'text'){
                    echo CHtml::textArea($modelName.'_'.$field->fieldName, '', array(
                        'title' => $field->attributeLabel,
                    ));
                }elseif($field->type == 'dropdown'){
                    $dropdown = Dropdowns::model()->findByPk($field->linkType);
                    if(isset($dropdown)){
                        echo CHtml::dropDownList($modelName.'_'.$field->fieldName, '', json_decode($dropdown->options), array(
                            'title' => $field->attributeLabel,
                        ));
                    }else{
                        echo CHtml::textField($modelName.'_'.$field->fieldName, '', array(
                            'title' => $field->attributeLabel,
                        ));
                    }
                }elseif($field->type == 'boolean'){
                    echo '<div class="checkboxWrapper">';
                    echo CHtml::checkBox($modelName.'_'.$field->fieldName, false, array(
                        'title' => $field->attributeLabel,
                    )).'</div>';
                }elseif($field->type == 'assignment'){
                    echo CHtml::dropDownList($field->fieldName, '', array('Users'), array(
                        'title' => $field->attributeLabel,
                    ));
                }elseif($field->type == 'visibility'){
                    echo CHtml::dropDownList($field->fieldName, '', array(1 => 'Public', 0 => 'Private', 2 => 'User\'s Groups'), array(
                        'title' => $field->attributeLabel,
                    ));
                }else{
                    echo CHtml::textField($modelName.'_'.$field->fieldName, '', array(
                        'title' => $field->attributeLabel,
                    ));
                }
                echo '</div></div>';
            }
            ?>
        </div>
    </div>
<?php } ?>
<?php if(!empty($id)){ ?>
    <div class="formContainer span-15">
        <!-- Preview Tabs -->
        <div id="preview" style='display:none'>
            <ul>
                <li>
                    <a href='#preview-form'>
                        <?php echo Yii::t('admin', 'Form')?>
                    </a>
                </li>
                <li>
                    <a href='#preview-view'>
                        <?php echo Yii::t('admin', 'View')?>
                    </a>
                </li>
            </ul>
            <div id='preview-form'>
            </div>
            <div id='preview-view'>
            </div>
        </div>

        <div class="x2-layout form-view editMode" id="formEditor">
            <div id="formEditorControls">
                <a href="javascript:void(0)" id="addRow" class="x2-button"><?php echo Yii::t('admin', 'Add Row'); ?></a>
                <a href="javascript:void(0)" id="addCollapsibleRow" class="x2-button"><?php echo Yii::t('admin', 'Add Collapsible'); ?></a>

                <span class="formItemOptions">
                    <label for="readOnly"><?php echo Yii::t('admin', 'Read-only'); ?></label>
                    <select id="readOnly">
                        <option value="0" selected="selected"><?php echo Yii::t('app', 'No'); ?></option>
                        <option value="1"><?php echo Yii::t('app', 'Yes'); ?></option>
                        <option value="mixed" disabled="disabled">---</option>
                    </select>
                    <label for="labelType"><?php echo Yii::t('admin', 'Label Position'); ?></label>
                    <select id="labelType">
                        <option value="left" selected="selected"><?php echo Yii::t('admin', 'Left'); ?></option>
                        <option value="top"><?php echo Yii::t('admin', 'Top'); ?></option>
                        <option value="inline"><?php echo Yii::t('admin', 'Inline'); ?></option>
                        <option value="none"><?php echo Yii::t('admin', 'None'); ?></option>
                        <option value="mixed" disabled="disabled">---</option>
                    </select>
                    <!--<a href="javascript:void(0)" id="setTabOrder" class="x2-button"><?php echo Yii::t('admin', 'Tab Order'); ?></a>-->
                </span>
            </div>
        </div>
    </div>
<?php } ?>
