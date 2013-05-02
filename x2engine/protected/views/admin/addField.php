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
        <?php  echo $form->dropDownList($model, 'modelName', Admin::getModelList());?>
        <?php echo $form->error($model, 'modelName'); ?>
    </div>

    <div class="row">
        <br><div><?php echo Yii::t('admin', 'Field Name <b>MUST</b> be of the format: wordWordWord. i.e. firstName'); ?>
            <br><?php echo Yii::t('admin', 'The first letter must be lowercase and each following word should have its first letter capitalized.'); ?>
            <br>No spaces are allowed.</div><br>
        <?php echo $form->labelEx($model, 'fieldName'); ?>
<?php echo $form->textField($model, 'fieldName'); ?>
<?php echo $form->error($model, 'fieldName'); ?>
    </div>

    <div class="row">
        <br><div>Attribute Label is what you want the field to be displayed as. <br>
            So for the field firstName, the label should probably be First Name</div><br>
        <?php echo $form->labelEx($model, 'attributeLabel'); ?>
<?php echo $form->textField($model, 'attributeLabel'); ?>
<?php echo $form->error($model, 'attributeLabel'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model, 'type'); ?>
        <?php
        echo $form->dropDownList($model, 'type', array(
            'varchar' => 'Single Line Text',
            'text' => 'Multiple Line Text Area',
            'date' => 'Date',
            'dateTime'=>'Date/Time',
            'dropdown' => 'Dropdown',
            'int' => 'Number',
            'email' => 'E-Mail',
            'currency' => 'Currency',
            'url' => 'URL',
            'float' => 'Decimal',
            'boolean' => 'Checkbox',
            'link' => 'Lookup',
            'rating' => 'Rating',
            'assignment' => 'Assignment'
                ), array(
            'ajax' => array(
                'type' => 'POST', //request type
                'url' => CController::createUrl('admin/getFieldType'), //url to call.
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
        <?php echo $form->labelEx($model, 'searchable'); ?>
<?php echo $form->checkBox($model, 'searchable', array('id' => 'searchable', 'onclick' => '$("#relevance_box").toggle();')); ?>
<?php echo $form->error($model, 'searchable'); ?>
    </div>

    <div class="row" id ="relevance_box" style="display:none">
        <?php echo $form->labelEx($model, 'relevance'); ?>
<?php echo $form->dropDownList($model, 'relevance', array('Low' => 'Low', "Medium" => "Medium", "High" => "High"), array("id" => "relevance", 'options' => array('Medium' => array('selected' => true)))); ?>
<?php echo $form->error($model, 'relevance'); ?> 
    </div>


    <div class="row buttons">
    <?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save'), array('class' => 'x2-button','onclick'=>'validateField();return false;')); ?>
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