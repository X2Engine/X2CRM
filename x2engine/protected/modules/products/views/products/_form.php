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
			        'onfocus'=>$default? 'x2.forms.toggleText(this);' : null,
			        'onblur'=>$default? 'x2.forms.toggleText(this);' : null,
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
			        'onfocus'=>$default? 'x2.forms.toggleText(this);' : null,
			        'onblur'=>$default? 'x2.forms.toggleText(this);' : null,
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
			    	'onfocus'=>$default? 'x2.forms.toggleText(this);' : null,
			    	'onblur'=>$default? 'x2.forms.toggleText(this);' : null,
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
