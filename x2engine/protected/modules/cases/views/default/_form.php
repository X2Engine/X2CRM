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
<div class="form no-border" style="float:left;width:590px;">

<?php  
include("protected/config/casesConfig.php");
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'cases-form',
	'enableAjaxValidation'=>false,
)); 
$fields=Fields::model()->findAllByAttributes(array('modelName'=>'Cases'));
$nonCustom=array();
$custom=array();
foreach($fields as $field){
    if($field->custom==0){
        $nonCustom[$field->fieldName]=$field;
    }else{
        $custom[$field->fieldName]=$field;
    }
}

?>
<div class="span-15" id="form-box" style="position:relative;overflow:hidden;height:700px;">
<?php
foreach($fields as $field){ ?>
    <?php if($field->fieldName!="id"){ 
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
    <div class="label"><label for="Contacts_<?php echo $field->fieldName;?>"><?php echo Yii::t('contacts',$field->attributeLabel); ?><span class="required">*</span></label></div>
                <?php
                    $fieldName=$field->fieldName;(isset($editor) && $editor)?$disabled='disabled':$disabled="";
                    if($field->type=='varchar'){
			$default = empty($model->$fieldName);
			if($default) 
				$model->$fieldName = Yii::t('contacts',$field->attributeLabel);
			echo $form->textField($model, $fieldName, array(
				'maxlength'=>40,
                                'class'=>'resizable',
				'style'=>($default?'color:#aaa;':'')."height:".$height.";width:".$width.";",
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'tabindex'=>$field->tabOrder,
                                'disabled'=>$disabled,
			));
                        
                        }elseif($field->type=='text'){
                           $default = empty($model->$fieldName);
			if($default) 
				$model->$fieldName = Yii::t('contacts',$field->attributeLabel);
			echo $form->textArea($model, $fieldName, array(
				'maxlength'=>40,
                                'class'=>'resizable',
				'style'=>($default?'color:#aaa;':'')."height:".$height.";width:".$width.";",
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'tabindex'=>$field->tabOrder,
                                'disabled'=>$disabled,
			)); 
                        }elseif($field->type=='date'){
                            $default = empty($model->$fieldName);
                            if($default) 
                                    $model->$fieldName = date("Y-m-d H:i:s");
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
                        }elseif(preg_match('/dropdown/',$field->type)){
                            $pieces=explode(":",$field->type);
                            $id=$pieces[1];
                            $dropdown=Dropdowns::model()->findByPk($id);
                            $default = empty($model->$fieldName);
                            if($default) 
                                    $model->$fieldName = Yii::t('contacts',$field->attributeLabel);
                            echo $form->dropDownList($model, $fieldName,json_decode($dropdown->options), array(
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
                        if($field->visible==0){
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