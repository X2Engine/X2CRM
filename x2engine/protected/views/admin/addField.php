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
?>

<h3><?php echo Yii::t('admin',"Add A Custom Field");?></h3>
<?php echo Yii::t('admin',"This form allows you to add custom fields to models.");?>
<div><br /></div>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'field-form',
	'enableAjaxValidation'=>false,
        'action'=>'addField',
));
echo $form->errorSummary($model);
?>



	<div class="row">
            <?php echo $form->labelEx($model,'modelName'); ?>
            <?php 
			$modelList = array();
			foreach(CActiveRecord::model('Modules')->findAllByAttributes(array('editable'=>true)) as $module) {
				if(array_key_exists($module->name,X2Model::$associationModels))
					$modelName = X2Model::$associationModels[$module->name];
				else
					$modelName = ucfirst($module->name);

				$modelList[$modelName] = $module->title;
			}
            echo $form->dropDownList($model,'modelName',$modelList); ?>
            <?php echo $form->error($model,'modelName'); ?>
	</div>
        
        <div class="row">
            <br /><div><?php echo Yii::t('admin','Field Name <b>MUST</b> be of the format: wordWordWord. i.e. firstName');?>
                <br /><?php echo Yii::t('admin','The first letter must be lowercase and each following word should have its first letter capitalized.');?>
                <br />No spaces are allowed.</div><br />
            <?php echo $form->labelEx($model,'fieldName'); ?>
            <?php echo $form->textField($model,'fieldName'); ?>
            <?php echo $form->error($model,'fieldName'); ?>
        </div>
        
        <div class="row">
            <br /><div>Attribute Label is what you want the field to be displayed as. <br />
                So for the field firstName, the label should probably be First Name</div><Br />
            <?php echo $form->labelEx($model,'attributeLabel'); ?>
            <?php echo $form->textField($model,'attributeLabel'); ?>
            <?php echo $form->error($model,'attributeLabel'); ?>
        </div>
    
        <div class="row">
            <?php echo $form->labelEx($model,'type'); ?>
            <?php echo $form->dropDownList($model,'type',
                    array(
                        'varchar'=>'Single Line Text',
                        'text'=>'Multiple Line Text Area',
                        'date'=>'Date',
                        'dropdown'=>'Dropdown',
                        'int'=>'Number',
                        'email'=>'E-Mail',
                        'currency'=>'Currency',
                        'url'=>'URL',
                        'float'=>'Decimal',
                        'boolean'=>'Checkbox',
                        'link'=>'Lookup',
                        'rating'=>'Rating',
                        'assignment'=>'Assignment'
                    ),
                array(
                'ajax' => array(
                'type'=>'POST', //request type
                'url'=>CController::createUrl('admin/getFieldType'), //url to call.
                //Style: CController::createUrl('currentController/methodToCall')
                'update'=>'#dropdown', //selector to update
                //'data'=>'js:"modelType="+$("'.CHtml::activeId($model,'modelType').'").val()' 
                //leave out the data key to pass all form values through
                ))); ?>
            <?php echo $form->error($model,'type'); ?> 
        </div>
    
        <div class="row" id="dropdown">

        </div>
    
        <div class="row">
            <?php echo $form->labelEx($model,'required');?>
            <?php echo $form->checkBox($model,'required');?>
            <?php echo $form->error($model,'required');?>
        </div>
    
        <div class="row">
            <?php echo $form->labelEx($model,'searchable');?>
            <?php echo $form->checkBox($model,'searchable',array('id'=>'searchable','onclick'=>'$("#relevance_box").toggle();'));?>
            <?php echo $form->error($model,'searchable');?>
        </div>
        
        <div class="row" id ="relevance_box" style="display:none">
            <?php echo $form->labelEx($model,'relevance'); ?>
            <?php echo $form->dropDownList($model,'relevance',array('Low'=>'Low',"Medium"=>"Medium","High"=>"High"),array("id"=>"relevance",'options'=>array('Medium'=>array('selected'=>true)))); ?>
            <?php echo $form->error($model,'relevance'); ?> 
        </div>

        
	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button')); ?>
	</div>
<?php $this->endWidget(); ?>
</div>
<script>
    $('#field-form').submit(function(){
         if($('#Fields_fieldName').val()=="" || $('#Fields_attributeLabel').val()==""){
           alert("You must enter a field name and attribute label.");
           return false; 
       }
    });

</script>