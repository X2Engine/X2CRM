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
?><div class="page-title"><h2><?php echo Yii::t('admin','Customize Fields'); ?></h2></div>
<div class="form">
<div style="width:600px">
<?php echo Yii::t('admin','This form will allow you to rename or show/hide any field on any customizable module.  Changing the type of a default field is <b>strongly</b> discouraged.'); ?><br><br>

	<?php $form=$this->beginWidget('CActiveForm', array(
		'id'=>'criteria-form',
		'enableAjaxValidation'=>false,
		'action'=>'customizeFields',
	)); ?>
	<em><?php echo Yii::t('app','Fields with <span class="required">*</span> are required.'); ?></em><br>
	<div class="row">
		<?php echo $form->labelEx($model,'modelName'); ?>
		<?php 
			$modelList = array();
			foreach(X2Model::model('Modules')->findAllByAttributes(array('editable'=>true)) as $module) {
				if(!($modelName=X2Model::getModelName($module->name))){
					$modelName = ucfirst($module->name);
                }

				$modelList[$modelName] = $module->title;
			}
			echo $form->dropDownList($model,'modelName',$modelList,array(
				'empty'=>'Select a model',
				'ajax' => array(
				'type'=>'POST', //request type
				'url'=>CController::createUrl('admin/getAttributes'), //url to call.
				//Style: CController::createUrl('currentController/methodToCall')
				'update'=>'#dynamicFields', //selector to update
				//'data'=>'js:"modelType="+$("'.CHtml::activeId($model,'modelType').'").val()' 
				//leave out the data key to pass all form values through
			))); ?>
		<?php echo $form->error($model,'modelName'); ?>
	</div>
	
	<div class="row">
		<?php echo $form->labelEx($model,'fieldName'); ?>
		<?php echo $form->dropDownList($model,'id',array(),array('empty'=>'Select a model first','id'=>'dynamicFields',
			'ajax' => array(
			'type'=>'POST', //request type
			'url'=>CController::createUrl('admin/getFieldData'), //url to call.
			//Style: CController::createUrl('currentController/methodToCall')
			'success'=>'updateFields', //selector to update
			//'data'=>'js:"modelType="+$("'.CHtml::activeId($model,'modelType').'").val()' 
			//leave out the data key to pass all form values through
			))); ?>
		<?php echo $form->error($model,'id'); ?>
	</div>
	<br>
	<div class="row">
		<div>
		Please enter the new name for your chosen field.<br>
		Leave blank if you don't want to change it.</div><br>
		<?php echo $form->labelEx($model,'attributeLabel'); ?>
		<?php echo $form->textField($model,'attributeLabel', array('id'=>'attributeLabel')); ?>
		<?php echo $form->error($model,'attributeLabel'); ?>
	</div>
	
	<div class="row">
            <?php echo $form->labelEx($model,'type'); ?>
            <?php echo $form->dropDownList($model,'type',
                    array(
                        'varchar'=>'Single Line Text',
                        'text'=>'Multiple Line Text Area',
                        'date'=>'Date',
                        'dateTime'=>'Date/Time',
                        'dropdown'=>'Dropdown',
                        'int'=>'Number',
                        'email'=>'E-Mail',
                        'currency'=>'Currency',
                        'url'=>'URL',
                        'float'=>'Decimal',
                        'boolean'=>'Checkbox',
                        'link'=>'Lookup',
                        'rating'=>'Rating',
                        'assignment'=>'Assignment',
                        'percentage' => 'Percentage'
                    ),
                array(
                'id'=>'fieldType',
                'ajax' => array(
                'type'=>'POST', //request type
                'url'=>CController::createUrl('admin/getFieldType'), //url to call.
                //Style: CController::createUrl('currentController/methodToCall')
                'update'=>'#edit_dropdown', //selector to update
                //'data'=>'js:"modelType="+$("'.CHtml::activeId($model,'modelType').'").val()' 
                //leave out the data key to pass all form values through
                ))); ?>
            <?php echo $form->error($model,'type'); ?> 
        </div>
    
        <div class="row" id="edit_dropdown">

        </div>
	
	<div class="row">
		<?php echo $form->checkBox($model,'required',array('id'=>'required'));?>
		<?php echo $form->labelEx($model,'required',array('style'=>'display:inline;'));?>
		<?php echo $form->error($model,'required');?>
	</div>
        
        <div class="row">
            <?php echo $form->checkBox($model,'searchable',array('id'=>'searchable-custom','onclick'=>'$("#relevance_box_custom").toggle();'));?>
            <?php echo $form->labelEx($model,'searchable',array('style'=>'display:inline;'));?>
            <?php echo $form->error($model,'searchable');?>
        </div>
        
        <div class="row" id ="relevance_box_custom" style="display:none">
            <?php echo $form->labelEx($model,'relevance'); ?>
            <?php echo $form->dropDownList($model,'relevance',array('Low'=>'Low',"Medium"=>"Medium","High"=>"High"),array("id"=>"relevance-custom",'options'=>array('Medium'=>array('selected'=>true)))); ?>
            <?php echo $form->error($model,'relevance'); ?> 
        </div>
	<br>
	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Save'):Yii::t('app','Save'),array('class'=>'x2-button')); ?>
	</div>
	</div>
<?php $this->endWidget(); ?>
</div>
<script>
    function updateFields(data){
        data=$.parseJSON(data);
        $('#attributeLabel').val(data.attributeLabel);
        $('#fieldType').val(data.type);
        $('#edit_dropdown').html(data.dropdown);
        if(data.required==1){
            $('#required').attr("checked",true);
        }else{
            $('#required').attr("checked",false);
        }
        if(data.searchable==1){
            $('#relevance_box_custom').show();
            $('#searchable-custom').attr("checked",true);
        }else{
            $('#relevance_box_custom').hide();
            $('#searchable-custom').attr("checked",false);
        }
        $('#relevance-custom').val(data.relevance)
    }
</script>