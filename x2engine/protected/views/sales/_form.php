<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/
?>
<div class="form">
<?php
$fields=Fields::model()->findAllByAttributes(array('modelName'=>'Sales'));
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
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'sales-form',
	'enableAjaxValidation'=>false,
)); ?>

<em><?php echo Yii::t('app','Fields with <span class="required">*</span> are required.'); ?></em><br />

<?php echo $form->errorSummary($model); ?>

<div class="row">
        <?php if($nonCustom['name']->visible==1){ ?>
	<div class="cell">
		<?php echo $form->labelEx($model,'name'); ?>
		<?php echo $form->textField($model,'name',array('size'=>48,'maxlength'=>40)); ?>
		<?php echo $form->error($model,'name'); ?>
	</div>
        <?php } ?>
        <?php if($nonCustom['accountName']->visible==1){ ?>
	<div class="cell">
		<?php
		echo '<label for="accountAutoComplete">'. Yii::t('sales','Account').' ('.Yii::t('app','Optional').')<label>';
		echo $form->hiddenField($model,'accountName');
		$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
			'name'=>'accountAutoComplete',
			'source' => $this->createUrl('contacts/getTerms'),
			'htmlOptions'=>array('size'=>25,'maxlength'=>100,'tabindex'=>3),
			'options'=>array(
				'minLength'=>'2',
				'select'=>'js:function( event, ui ) {
					$("#'.CHtml::activeId($model,'accountId').'").val(ui.item.id);
					$(this).val(ui.item.value);
					$("#'.CHtml::activeId($model,'accountName').'").val(ui.item.value);
					return false;
				}',
			),
		));
		echo $form->error($model,'accountName');
		echo $form->hiddenField($model,'accountId');
		?>
	</div>
        <?php } ?>
</div>
<!--<div class="row">
	<?php //echo $form->labelEx($model,'assignedTo'); ?>
	<?php //echo $form->dropDownList($model,'assignedTo',$users); ?>
	<?php //echo $form->error($model,'assignedTo'); ?>
</div>-->
<div class="row">
        <?php if($nonCustom['quoteAmount']->visible==1){ ?>
	<div class="cell">
		<?php echo $form->labelEx($model,'quoteAmount'); ?>
		<?php echo $form->textField($model,'quoteAmount'); ?>
		<?php echo $form->error($model,'quoteAmount'); ?>
	</div>
        <?php } ?>
        <?php if($nonCustom['salesStage']->visible==1){ ?>
	<div class="cell">
		<?php echo $form->labelEx($model,'salesStage'); ?>
		<?php echo $form->dropDownList($model,'salesStage',
				array(
					'Working'=>Yii::t('sales','Working'),
					'Won'=>Yii::t('sales','Won'),
					'Lost'=>Yii::t('sales','Lost'))
				); ?>
		<?php echo $form->error($model,'salesStage'); ?>
	</div>
        <?php } ?>
        <?php if($nonCustom['leadSource']->visible==1){ ?>
	<div class="cell">
		<?php echo $form->labelEx($model,'leadSource'); ?>
		<?php echo $form->dropDownList($model,'leadSource',
				array(
					'Website'=>Yii::t('sales','Website'), 
					'Cold Call'=>Yii::t('sales','Cold Call'), 
					"E-Mail"=>Yii::t('sales','E-Mail'), 
					"Store"=>Yii::t('sales','Store')
				)); ?>
		<?php echo $form->error($model,'leadSource'); ?>
	</div>
        <?php } ?>
</div>
<div class="row">
        <?php if($nonCustom['expectedCloseDate']->visible==1){ ?>
	<div class="cell">
		<?php echo $form->labelEx($model,'expectedCloseDate'); ?>
		<?php Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
		$this->widget('CJuiDateTimePicker',array(
			'model'=>$model, //Model object
			'attribute'=>'expectedCloseDate', //attribute name
			'mode'=>'datetime', //use "time","date" or "datetime" (default)
			'options'=>array(
				'dateFormat'=>'yy-mm-dd',
			), // jquery plugin options
			'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
		));?>
		<?php echo $form->error($model,'expectedCloseDate'); ?>
	</div>
        <?php } ?>
        <?php if($nonCustom['probability']->visible==1){ ?>
	<div class="cell">
		<?php echo $form->labelEx($model,'probability'); ?>
		<?php echo $form->textField($model,'probability'); ?>
		<?php echo $form->error($model,'probability'); ?>
	</div>
        <?php } ?>
</div>
<div class="row">
        <?php if($nonCustom['assignedTo']->visible==1){ ?>
	<div class="cell">
		<?php echo $form->labelEx($model,'assignedTo'); ?>
		<?php echo $form->dropDownList($model,'assignedTo',$users,array('multiple'=>'multiple', 'size'=>7)); ?>
		<?php echo $form->error($model,'assignedTo'); ?>
	</div>
        <?php } ?>
        <?php if($nonCustom['associatedContacts']->visible==1){ ?>
	<div class="cell">
		<?php echo $form->labelEx($model,'associatedContacts'); ?>
		<?php echo $form->dropDownList($model,'associatedContacts',$contacts,array('multiple'=>'multiple', 'size'=>7)); ?>
		<?php echo $form->error($model,'associatedContacts'); ?>
	</div>
        <?php } ?>
	<div class="cell">
		<span class="information"><?php echo Yii::t('sales','Hold Control or Command key to select multiple items.'); ?></span> 
	</div>
</div>
<?php if($nonCustom['description']->visible==1){ ?>
<div class="row">
	<?php echo $form->labelEx($model,'description'); ?>
	<?php echo $form->textArea($model,'description',array('rows'=>6, 'cols'=>50)); ?>
	<?php echo $form->error($model,'description'); ?>
</div>
<?php } ?>
<?php 
        
            foreach($custom as $fieldName=>$field){
                
                if($field->visible==1){ 
                    ?>
                    <div class="row">
                    <div class="cell">
                        <?php echo $form->label($model,$fieldName); ?>
                        <?php echo $form->textField($model,$fieldName,array('size'=>'70')); ?>
                        <?php echo $form->error($model,$fieldName); ?>
                    </div>
                    </div>
                    <?php
                        }
                }
        
            ?>
<div class="row buttons">
	<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button')); ?>
</div>
<?php $this->endWidget(); ?>
</div><!-- form -->