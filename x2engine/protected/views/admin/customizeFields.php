<?php
/* * *******************************************************************************
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
 * ****************************************************************************** */
?><div class="page-title"><h2><?php echo Yii::t('admin', 'Customize Fields'); ?></h2></div>
<div class="form">
    <div style="width:600px">
        <?php echo Yii::t('admin', 'This form will allow you to rename or show/hide any field on any customizable module.  Changing the type of a default field is <b>strongly</b> discouraged.'); ?><br><br>

        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'criteria-form',
            'enableAjaxValidation' => false,
            'action' => 'customizeFields',
                ));
        ?>
        <em><?php echo Yii::t('app', 'Fields with <span class="required">*</span> are required.'); ?></em><br>
        <div class="row">
            <?php echo $form->labelEx($model, 'modelName'); ?>
            <?php
            $modelList = array();
            foreach(X2Model::model('Modules')->findAllByAttributes(array('editable' => true)) as $module){
                if(!($modelName = X2Model::getModelName($module->name))){
                    $modelName = ucfirst($module->name);
                }

                $modelList[$modelName] = Yii::t('app', $module->title);
            }
            echo $form->dropDownList($model, 'modelName', $modelList, array(
                'empty' => Yii::t('admin', 'Select a model'),
                'ajax' => array(
                    'type' => 'POST', //request type
                    'url' => CController::createUrl('/admin/getAttributes'), //url to call.
                    //Style: CController::createUrl('currentController/methodToCall')
                    'update' => '#dynamicFields', //selector to update
                //'data'=>'js:"modelType="+$("'.CHtml::activeId($model,'modelType').'").val()'
                //leave out the data key to pass all form values through
                    )));
            ?>
            <?php echo $form->error($model, 'modelName'); ?>
        </div>

        <div class="row">
            <?php echo $form->labelEx($model, 'fieldName'); ?>
            <?php
            echo $form->dropDownList($model, 'id', array(), array('empty' => Yii::t('admin', 'Select a model first'), 'id' => 'dynamicFields',
                'ajax' => array(
                    'type' => 'POST', //request type
                    'url' => CController::createUrl('/admin/getFieldData'), //url to call.
                    'success' => 'updateFields', //selector to update
                //'data'=>'js:"modelType="+$("'.CHtml::activeId($model,'modelType').'").val()'
                //leave out the data key to pass all form values through
                    )));
            ?>
            <?php echo $form->error($model, 'id'); ?>
        </div>
        <br>
        <div class="row">
            <div>
                <?php echo Yii::t('admin', 'Please enter the new name for your chosen field.'); ?><br>
                <?php echo Yii::t('admin', "Leave blank if you don't want to change it."); ?></div><br>
            <?php echo $form->labelEx($model, 'attributeLabel'); ?>
            <?php echo $form->textField($model, 'attributeLabel', array('id' => 'attributeLabel')); ?>
            <?php echo $form->error($model, 'attributeLabel'); ?>
        </div>

        <div class="row">
            <?php echo $form->labelEx($model, 'type'); ?>
            <?php
            echo $form->dropDownList($model, 'type', Fields::getFieldTypes('title'), array(
                'id' => 'fieldType',
                'ajax' => array(
                    'type' => 'POST', //request type
                    'url' => CController::createUrl('/admin/getFieldType'), //url to call.
                    'update' => '#edit_dropdown', //selector to update
                //leave out the data key to pass all form values through
                    )));
            ?>
            <?php echo $form->error($model, 'type'); ?>
        </div>

        <div class="row" id="edit_dropdown">

        </div>

        <div class="row">
            <?php echo $form->checkBox($model, 'required', array('id' => 'required')); ?>
            <?php echo $form->labelEx($model, 'required', array('style' => 'display:inline;')); ?>
            <?php echo $form->error($model, 'required'); ?>
        </div>

        <div class="row">
            <?php echo $form->checkBox($model, 'uniqueConstraint', array('id' => 'uniqueConstraint')); ?>
            <?php echo $form->labelEx($model, 'uniqueConstraint', array('style' => 'display:inline;')); ?>
            <?php echo $form->error($model, 'uniqueConstraint'); ?>
        </div>

        <div class="row">
            <?php echo $form->checkBox($model, 'searchable', array('id' => 'searchable-custom', 'onclick' => '$("#relevance_box_custom").toggle();')); ?>
            <?php echo $form->labelEx($model, 'searchable', array('style' => 'display:inline;')); ?>
            <?php echo $form->error($model, 'searchable'); ?>
        </div>

        <div class="row" id ="relevance_box_custom" style="display:none">
            <?php echo $form->labelEx($model, 'relevance'); ?>
            <?php echo $form->dropDownList($model, 'relevance', array('Low' => Yii::t('app', 'Low'), "Medium" => Yii::t('app', "Medium"), "High" => Yii::t('app', "High")), array("id" => "relevance-custom", 'options' => array('Medium' => array('selected' => true)))); ?>
            <?php echo $form->error($model, 'relevance'); ?>
        </div>
        <br>
        <div class="row buttons">
            <?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app', 'Save') : Yii::t('app', 'Save'), array('class' => 'x2-button')); ?>
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
        if(data.dropdown){
            $('#link_dropdown').val(data.linkType);
        }
        if(data.required==1){
            $('#required').attr("checked",true);
        }else{
            $('#required').attr("checked",false);
        }
        if(data.uniqueConstraint==1){
            $('#uniqueConstraint').attr("checked",true);
        }else{
            $('#uniqueConstraint').attr("checked",false);
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
