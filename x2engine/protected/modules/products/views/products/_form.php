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
?>
<div class="form no-border" style="float:left;width:580px;overflow:visible">

<?php  
include("protected/modules/products/productConfig.php");

$form=$this->beginWidget('CActiveForm', array(
	'id'=>'product-form',
	'enableAjaxValidation'=>false,
)); 
$fields=Fields::model()->findAllByAttributes(array('modelName'=>'Product'));

// build a list of required fields (used to append red '*' to required fields)
$required = array();
$rules = $model->rules();
foreach($rules as $rule) {
	if($rule[1] == 'required') {
		$required[] = $rule[0];
	}
}
?>
<div class="span-15" id="form-box" style="position:relative;overflow:hidden;height:250px;width:575px;">
<?php
foreach($fields as $field) {
    if($field->fieldName!="id"){ 
        $size=$field->size;
        $pieces=explode(":",$size);
        $width=$pieces[0];
        $height=$pieces[1];
        $position=$field->coordinates;
        $pieces=explode(":",$position);
        $left=$pieces[0];
        $top=$pieces[1];     
		?> 
		<div class="draggable" style="padding:10px;border:solid;border-width:1px;position:absolute;left:<?php echo $left;?>px;top:<?php echo $top;?>px;" id="<?php echo $field->fieldName ?>">
			<div class="label">
				<label for="Contacts_<?php echo $field->fieldName;?>">
					<?php 
						echo Yii::t('contacts',$field->attributeLabel);
						if(in_array($field->fieldName, $required)) {
							echo '<span class="required" style="font-size:1.25em;">*</span>';
						}
					?>
				</label>
			</div>
			
			<?php
			$fieldName=$field->fieldName;(isset($editor) && $editor)?$disabled='disabled':$disabled="";
			
			if($field->type=='varchar'){
			
			    $default = empty($model->$fieldName);
			    if($default) {
			        $model->$fieldName = Yii::t('contacts',$field->attributeLabel);
			    }
			    echo $form->textField($model, $fieldName, array(
			        'maxlength'=>40,
			        'class'=>'resizable',
			        'style'=>($default?'color:#aaa;':'')."height:".$height.";width:".$width.";",
			        'onfocus'=>$default? 'toggleText(this);' : null,
			        'onblur'=>$default? 'toggleText(this);' : null,
			        'tabindex'=>$field->tabOrder,
			        'disabled'=>$disabled,
			    ));
			                
			} elseif($field->type=='text') {
			
			    $default = empty($model->$fieldName);
			    if($default) {
			        $model->$fieldName = Yii::t('contacts',$field->attributeLabel);
			    }
			    echo $form->textArea($model, $fieldName, array(
			        'maxlength'=>40,
			        'class'=>'resizable',
			        'style'=>($default?'color:#aaa;':'')."height:".$height.";width:".$width.";",
			        'onfocus'=>$default? 'toggleText(this);' : null,
			        'onblur'=>$default? 'toggleText(this);' : null,
			        'tabindex'=>$field->tabOrder,
			        'disabled'=>$disabled,
			    )); 
			    
			} elseif($field->type=='date') {
			
			    $default = empty($model->$fieldName);
			    if($default) {
			    	$model->$fieldName = date("Y-m-d H:i:s");
			    }
			    Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
			    $this->widget('CJuiDateTimePicker',array(
			        'model'=>$model, //Model object
			        'attribute'=>$field->fieldName, //attribute name
			        'mode'=>'datetime', //use "time","date" or "datetime" (default)
			        'options'=>array(
			        'dateFormat'=>'yy-mm-dd',
			        ), // jquery plugin options
			        'htmlOptions'=>array(
			            'class'=>'resizable',
			            'disabled'=>$disabled,
			            'style'=>"height:".$height.";width:".$width.";",
			            'tabindex'=>$field->tabOrder,
			        ),
			        'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
			    )); 
			    
			} elseif(preg_match('/dropdown/',$field->type)) {
			
			    $pieces=explode(":",$field->type);
			    $id=$pieces[1];
			    $dropdown=Dropdowns::model()->findByPk($id);
			    $default = empty($model->$fieldName);
			    if($default) {
			    	$model->$fieldName = Yii::t('contacts',$field->attributeLabel);
			    }
			    echo $form->dropDownList($model, $fieldName, json_decode($dropdown->options), array(
			    	'maxlength'=>40,
			    	'class'=>'resizable',
			    	'style'=>"height:".$height.";width:".$width.";",
			    	'onfocus'=>$default? 'toggleText(this);' : null,
			    	'onblur'=>$default? 'toggleText(this);' : null,
			    	'tabindex'=>$field->tabOrder,
			    	'disabled'=>$disabled,
			    ));
			}
			?>
		</div>
        <?php 
        if($field->visible==0) {
            Yii::app()->clientScript->registerScript($field->fieldName,'
                $("#'.$field->fieldName.'").css({"visibility":"hidden"});
            ');
        }
	}
}
?>
</div>

<div class="row buttons">
    <?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button')); ?>
</div>

<?php $this->endWidget(); ?>
</div><!-- form -->
<?php
if(isset($editor)){
    if($editor){
        ?>
        <script>
            $(function(){
                $('.draggable').draggable({ grid: [10, 10], containment:'parent' });
                $('.resizable').resizable({ grid: [5, 5] });
            });
        </script>
        <?php
    }
}else{
    ?>
    <script>
        $(".draggable").css({border: 'none'});
    </script>
    <?php
}
?>