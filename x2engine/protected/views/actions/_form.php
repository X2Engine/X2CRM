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

Yii::app()->clientScript->registerScript('validate','
$(document).ready(function(){
	$("#actions-newCreate-form").submit(function(){
		if($("#'.CHtml::activeId($actionModel,'associationType').'").val()=="contacts" || $("#'.CHtml::activeId($actionModel,'associationType').'").val()=="accounts"
			|| $("#'.CHtml::activeId($actionModel,'associationType').'").val()=="sales"){
			if($("#'.CHtml::activeId($actionModel,'associationId').'").val()==""){
				alert("Please enter a valid association");
				return false;
			}
		}
	}
	);
}
);');
Yii::app()->clientScript->registerScript('highlightSaveAction',"
$(function(){
	$('#action-form input, #action-form select, #action-form textarea').change(function(){
		$('#save-button, #save-button1, #save-button2').css('background','yellow');
	}
	);
}
);");

$fields=Fields::model()->findAllByAttributes(array('modelName'=>'Actions'));
$nonCustom=array();
$custom=array();
foreach($fields as $field){
    if($field->custom==0){
        $nonCustom[$field->fieldName]=$field;
    }else{
        $custom[$field->fieldName]=$field;
    }
}

$inlineForm = (isset($inlineForm)); // true if this is in the InlineActionForm
$quickCreate = $inlineForm? false : ($this->getAction()->getId() == 'quickCreate');	// true if we're inside the quickCreate view
if(isset($_GET['inline']))
    $inlineForm=$_GET['inline'];
$action = $inlineForm? array('actions/create','inline'=>1) : null;

// check if this form is being recycled in the quickCreate view
if (!$quickCreate) {
	echo '<div class="form" id="action-form">';
	$form=$this->beginWidget('CActiveForm', array(
		'action'=>$action,
		'id'=>'actions-newCreate-form',
		'enableAjaxValidation'=>false,
	));
	//echo '<em>'.Yii::t('app','Fields with <span class="required">*</span> are required.')."</em>\n";
}
echo $form->errorSummary($actionModel);
?>
<?php /*
<div class="top row">
	<?php echo $form->labelEx($actionModel,'type'); ?>
	<?php echo $form->textField($actionModel,'type',array('size'=>20,'maxlength'=>20)); ?>
	<?php echo $form->error($actionModel,'type'); ?>
</div> */?>
<?php if($nonCustom['actionDescription']->visible==1){ ?>
<div class="row">
	<b><?php echo $form->labelEx($actionModel,'actionDescription'); ?></b>
	<?php //echo $form->label($actionModel,'actionDescription'); ?>
	<?php echo $form->textArea($actionModel,'actionDescription',array('rows'=>($inlineForm?3:6), 'cols'=>50)); ?>
	<?php //echo $form->error($actionModel,'actionDescription'); ?>
</div>
<?php } ?>
<div class="row">
	<?php
	if (!$quickCreate) {
		if ($inlineForm) {
			echo $form->hiddenField($actionModel,'associationType');
		} else {
	?>
<div class="row">
        <?php if($nonCustom['associationType']->visible==1){ ?>
	<div class="cell">
	<?php echo $form->label($actionModel,'associationType'); ?>
	<?php echo $form->dropDownList($actionModel,'associationType',
		array(
			'none'=>Yii::t('actions','None'),
			'contacts'=>Yii::t('actions','Contact'),
			'sales'=>Yii::t('actions','Sale'),
			'accounts'=>Yii::t('actions','Account'),
		),
		array(
			'ajax' => array(
				'type'=>'POST', //request type
				'url'=>CController::createUrl('actions/parseType'), //url to call.
				//Style: CController::createUrl('currentController/methodToCall')
				'update'=>'#', //selector to update
				'success'=>'function(data){
						window.location="?param='.Yii::app()->user->getName().';"+data+":0";
					}'
				)
			)
		);
		echo $form->error($actionModel,'associationType'); ?>
	</div>
        <?php } ?>
        <?php if($nonCustom['associationName']->visible==1){ ?>
	<div class="cell" id="auto_complete">
		<?php
		echo $form->label($actionModel,'associationName');
		$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
			'name'=>'auto_select',
			'value'=>$actionModel->associationName,
			'source' => $this->createUrl('actions/getTerms',array('type'=>$actionModel->associationType)),
			'options'=>array(
				'minLength'=>'2',
				'select'=>'js:function( event, ui ) {
					$("#'.CHtml::activeId($actionModel,'associationId').'").val(ui.item.id);
					$(this).val(ui.item.value);
					return false;
				}',
			),
		));
		//echo $form->error($actionModel,'associationName');
		?>
	</div>
        <?php } ?>
</div>
	<?php
		}
	}
	?>
        
	<div class="cell">
		<?php echo $form->hiddenField($actionModel,'associationId'); ?>
            <?php if($nonCustom['dueDate']->visible==1){ ?>
		<?php echo $form->label($actionModel,'dueDate');
		if ($actionModel->isNewRecord)
			$actionModel->dueDate = date('Y-m-d',time()).' 23:59';	//default to tomorow for new actions
		else
			$actionModel->dueDate = date('Y-m-d H:i',$actionModel->dueDate);	//format date from DATETIME

		Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
		$this->widget('CJuiDateTimePicker',array(
			'model'=>$actionModel, //Model object
			'attribute'=>'dueDate', //attribute name
			'mode'=>'datetime', //use "time","date" or "datetime" (default)
			'options'=>array(
				'dateFormat'=>'yy-mm-dd',
			), // jquery plugin options
			'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
		));
		?>
		<?php echo $form->error($actionModel,'dueDate'); ?>
            <?php } ?>
	</div>
        <?php if($nonCustom['priority']->visible==1){ ?>
	<div class="cell">
		<?php echo $form->label($actionModel,'priority'); ?>
		<?php echo $form->dropDownList($actionModel,'priority',array(
			'Low'=>Yii::t('actions','Low'),
			'Medium'=>Yii::t('actions','Medium'),
			'High'=>Yii::t('actions','High')));
		//echo $form->error($actionModel,'priority'); ?>
	</div>
        <?php } ?>
        <?php if($nonCustom['assignedTo']->visible==1){ ?>
	<div class="cell">
		<?php echo $form->label($actionModel,'assignedTo'); ?>
		<?php echo $form->dropDownList($actionModel,'assignedTo',$users); ?>
		<?php //echo $form->error($actionModel,'assignedTo'); ?>
	</div>
        <?php } ?>
        <?php if($nonCustom['visibility']->visible==1){ ?>
	<div class="cell">
		<?php echo $form->label($actionModel,'visibility'); ?>
		<?php echo $form->dropDownList($actionModel,'visibility',array('1'=>Yii::t('actions','Public'),'0'=>Yii::t('actions','Private'))); ?>
		<?php //echo $form->error($actionModel,'visibility'); ?>
	</div>
        <?php } ?>
        <?php if($nonCustom['reminder']->visible==1){ ?>
	<div class="cell">
		<?php echo $form->label($actionModel,'reminder'); ?>
		<?php //echo $form->checkBox($actionModel,'reminder',array('value'=>'Yes','uncheckedValue'=>'No')); ?>
		<?php echo $form->dropDownList($actionModel,'reminder',array('No'=>Yii::t('actions','No'),'Yes'=>Yii::t('actions','Yes'))); ?> 
	</div>
        <?php } ?>
        <?php 
        
            foreach($custom as $fieldName=>$field){
                
                if($field->visible==1){ 
                    ?>
                    <div class="row">
                    <div class="cell">
                        <?php echo $form->label($actionModel,$fieldName); ?>
                        <?php echo $form->textField($actionModel,$fieldName,array('size'=>'70')); ?>
                        <?php echo $form->error($actionModel,$fieldName); ?>
                    </div>
                    </div>
                    <?php
                        }
                }
        
            ?>
</div>
<?php
if (!$quickCreate) {	//if we're not in quickCreate, end the form
?>
	<div class="row buttons">
		<?php echo CHtml::htmlButton($actionModel->isNewRecord ? Yii::t('app','Save Action'):Yii::t('app','Save'),
				array('type'=>'submit','class'=>'x2-button','id'=>'save-button1','name'=>'submit','value'=>'action')); ?>
		<?php if($actionModel->isNewRecord && $inlineForm)
				echo CHtml::htmlButton(Yii::t('app','Save Comment'),array('type'=>'submit','class'=>'x2-button','id'=>'save-button2','name'=>'submit','value'=>'comment'));
		?>
	</div>
<?php
$this->endWidget();
echo "</div>\n";
} 
?>