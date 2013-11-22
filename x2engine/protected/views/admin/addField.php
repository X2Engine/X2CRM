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
?>
<div class="page-title"><h2><?php echo Yii::t('admin', "Add A Custom Field"); ?></h2></div>
<div class="form">
    <?php echo Yii::t('admin', "This form allows you to add custom fields to models."); ?>
    <br><br>
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'field-form',
        'enableAjaxValidation' => false,
        'action' => 'addField',
            ));
    echo $form->errorSummary($model);
    ?>



    <div class="row">
        <?php echo $form->labelEx($model, 'modelName'); ?>
        <?php echo $form->dropDownList($model, 'modelName', Admin::getModelList()); ?>
        <?php echo $form->error($model, 'modelName'); ?>
    </div>

    <div class="row">
        <br><div><?php echo Yii::t('admin', 'No spaces are allowed.'); ?></div><br>
        <?php echo $form->labelEx($model, 'fieldName'); ?>
        <?php echo $form->textField($model, 'fieldName'); ?>
        <?php echo $form->error($model, 'fieldName'); ?>
    </div>

    <div class="row">
        <br><div><?php echo Yii::t('admin', 'Attribute Label is what you want the field to be displayed as.'); ?><br>
            <?php echo Yii::t('admin', 'So for the field firstName, the label should probably be First Name'); ?></div><br>
        <?php echo $form->labelEx($model, 'attributeLabel'); ?>
        <?php echo $form->textField($model, 'attributeLabel'); ?>
        <?php echo $form->error($model, 'attributeLabel'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model, 'type'); ?>
        <?php
        echo $form->dropDownList($model, 'type', Fields::getFieldTypes('title'), array(
            'ajax' => array(
                'type' => 'POST', //request type
                'url' => CController::createUrl('/admin/getFieldType'), //url to call.
                //Style: CController::createUrl('currentController/methodToCall')
                'update' => '#dropdown', //selector to update
            //'data'=>'js:"modelType="+$("'.CHtml::activeId($model,'modelType').'").val()'
            //leave out the data key to pass all form values through
                )));
        ?>
        <?php echo $form->error($model, 'type'); ?>
    </div>

    <div class="row" id="dropdown">

    </div>

    <div class="row">
        <?php echo $form->labelEx($model, 'required'); ?>
        <?php echo $form->checkBox($model, 'required'); ?>
        <?php echo $form->error($model, 'required'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model, 'uniqueConstraint'); ?>
        <?php echo $form->checkBox($model, 'uniqueConstraint'); ?>
        <?php echo $form->error($model, 'uniqueConstraint'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model, 'searchable'); ?>
        <?php echo $form->checkBox($model, 'searchable', array('id' => 'searchable', 'onclick' => '$("#relevance_box").toggle();')); ?>
        <?php echo $form->error($model, 'searchable'); ?>
    </div>

    <div class="row" id ="relevance_box" style="display:none">
        <?php echo $form->labelEx($model, 'relevance'); ?>
        <?php echo $form->dropDownList($model, 'relevance', array('Low' => Yii::t('app','Low'), "Medium" => Yii::t('app',"Medium"), "High" => Yii::t('app',"High")), array("id" => "relevance", 'options' => array('Medium' => array('selected' => true)))); ?>
        <?php echo $form->error($model, 'relevance'); ?>
    </div>


    <div class="row buttons">
        <?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save'), array('class' => 'x2-button', 'onclick' => 'validateField();return false;')); ?>
    </div>
    <?php $this->endWidget(); ?>
</div>
<script>
    var validationFlag=false;
    function validateField(){
        if($('#Fields_fieldName').val()=="" || $('#Fields_attributeLabel').val()==""){
            alert("You must enter a field name and attribute label.");
            return false;
        }else{
            var fieldName=$('#Fields_fieldName').val();
            var modelName=$('#Fields_modelName').val();
            $.ajax({
                url:'validateField',
                type:'GET',
                data:{fieldName:fieldName, modelName:modelName},
                success:function(data){
                    if(data==0){
                        validationFlag=true;
                        $('#field-form').submit();
                    }else{
                        alert(data);
                        return false;
                    }
                }
            });
        }
    }
    $('#field-form').submit(function(e){
        if(validationFlag==false){
            validateField();
        }
    });

</script>
