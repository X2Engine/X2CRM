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
<div class="form">

<?php 

$fields=Fields::model()->findAllByAttributes(array('modelName'=>'Accounts'));
$nonCustom=array();
$custom=array();
foreach($fields as $field){
    if($field->custom==0){
        $nonCustom[$field->fieldName]=$field;
    }else{
        $custom[$field->fieldName]=$field;
    }
}

$form=$this->beginWidget('CActiveForm', array(
	'id'=>'accounts-form',
	'enableAjaxValidation'=>false,
)); ?>

	<em><?php echo Yii::t('app','Fields with <span class="required">*</span> are required.'); ?></em><br />

	<?php echo $form->errorSummary($model); ?>
        
	<div class="row">
                <?php if($nonCustom['name']->visible==1){ ?>
		<div class="cell">
			<?php echo $form->labelEx($model,'name'); ?>
			<?php echo $form->textField($model,'name',array('size'=>30,'maxlength'=>40)); ?>
			<?php echo $form->error($model,'name'); ?>
		</div>
                <?php } ?>
                <?php if($nonCustom['type']->visible==1){ ?>
		<div class="cell">
			<?php echo $form->labelEx($model,'type'); ?>
			<?php echo $form->textField($model,'type',array('size'=>20,'maxlength'=>40)); ?>
			<?php echo $form->error($model,'type'); ?>
            	</div>
                <?php } ?>
	</div>

	<div class="row">
                <?php if($nonCustom['phone']->visible==1){ ?>
		<div class="cell">
			<?php echo $form->labelEx($model,'phone'); ?>
			<?php echo $form->textField($model,'phone',array('size'=>20,'maxlength'=>40)); ?>
			<?php echo $form->error($model,'phone'); ?>
		</div>
                <?php } ?>
                <?php if($nonCustom['website']->visible==1){ ?>
		<div class="cell">
			<?php echo $form->labelEx($model,'website'); ?>
			<?php echo $form->textField($model,'website',array('size'=>30,'maxlength'=>40)); ?>
			<?php echo $form->error($model,'website'); ?>
		</div>
                <?php } ?>
	</div>
	<div class="row">
                <?php if($nonCustom['annualRevenue']->visible==1){ ?>
		<div class="cell">
			<?php echo $form->labelEx($model,'annualRevenue'); ?>
			<?php echo $form->textField($model,'annualRevenue',array('size'=>20,'maxlength'=>10)); ?>
			<?php echo $form->error($model,'annualRevenue'); ?>

		</div>
                <?php } ?>
                <?php if($nonCustom['employees']->visible==1){ ?>
		<div class="cell">
			<?php echo $form->labelEx($model,'employees'); ?>
			<?php echo $form->textField($model,'employees',array('size'=>13,'maxlength'=>10)); ?>
			<?php echo $form->error($model,'employees'); ?>

		</div>
                <?php } ?>
                <?php if($nonCustom['tickerSymbol']->visible==1){ ?>
		<div class="cell">
			<?php echo $form->labelEx($model,'tickerSymbol'); ?>
			<?php echo $form->textField($model,'tickerSymbol',array('size'=>10,'maxlength'=>10)); ?>
			<?php echo $form->error($model,'tickerSymbol'); ?>
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
		<div class="row">
		<div class="cell">
			<span class="information"><?php echo Yii::t('sales','Hold Control or Command key to select multiple items.'); ?></span>
		</div>
		</div>
	</div><br />
        <?php if($nonCustom['description']->visible==1){ ?>
	<div class="row">
		<?php echo $form->labelEx($model,'description'); ?>
		<?php echo $form->textArea($model,'description',array('rows'=>4, 'cols'=>50)); ?>
		<?php echo $form->error($model,'description'); ?>
	</div>
        <?php } ?>
        <?php 
        
            foreach($custom as $fieldName=>$field){
                
                if($field->visible==1){ 
                    ?>
                    <div class="row">
                        <div class="cell">
                            <?php echo $form->labelEx($model,$fieldName); ?>
                            <?php echo $form->textField($model,$fieldName,array('size'=>'70')); ?>
                            <?php echo $form->error($model,$fieldName); ?>
                        </div>
                    </div><?php
                        }
                }
        
        ?>
	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button')); ?>
	</div>
<?php $this->endWidget(); ?>
</div>