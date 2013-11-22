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
<div class="wide form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'users-form',
	'enableAjaxValidation'=>false,
));
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/multiselect/js/ui.multiselect.js');
Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl().'/js/multiselect/css/ui.multiselect.css','screen, projection');
Yii::app()->clientScript->registerCss('multiselectCss',"
.multiselect {
	width: 460px;
	height: 200px;
}
#switcher {
	margin-top: 20px;
}
",'screen, projection');
Yii::app()->clientScript->registerScript('renderMultiSelect',"
$(document).ready(function() {
	 $('.multiselect').multiselect();
});
",CClientScript::POS_HEAD);
?>

	<?php $model->setAttribute('updatePassword',false); ?>

	<em><?php echo Yii::t('app','Fields with <span class="required">*</span> are required.'); ?></em><br />

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'firstName'); ?>
		<?php echo $form->textField($model,'firstName',array('size'=>20,'maxlength'=>20)); ?>
		<?php echo $form->error($model,'firstName'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'lastName'); ?>
		<?php echo $form->textField($model,'lastName',array('size'=>20,'maxlength'=>40)); ?>
		<?php echo $form->error($model,'lastName'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'username'); ?>
		<?php echo $model->username=='admin'?
				$form->textField($model,'username',array('size'=>20,'maxlength'=>20,'disabled'=>'disabled')):
				$form->textField($model,'username',array('size'=>20,'maxlength'=>20)); ?>
		<?php echo $form->error($model,'username'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'password'); ?>
		<?php echo $form->passwordField($model,'password',array('size'=>20,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'password'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'userKey'); ?>
		<?php echo $form->textField($model,'userKey',array('size'=>20,'maxlength'=>30)); ?>
		<?php echo $form->error($model,'userKey'); ?>
	</div>

	<?php if((isset($flag) && !$flag) || !isset($flag)){ ?>
	<div class="row">
		<?php echo $form->labelEx($model,'title'); ?>
		<?php echo $form->textField($model,'title',array('size'=>20,'maxlength'=>20)); ?>
		<?php echo $form->error($model,'title'); ?>
	</div>
	<?php } ?>

	<?php if((isset($flag) && !$flag) || !isset($flag)){ ?>
	<div class="row">
		<?php echo $form->labelEx($model,'department'); ?>
		<?php echo $form->textField($model,'department',array('size'=>40,'maxlength'=>40)); ?>
		<?php echo $form->error($model,'department'); ?>
	</div>
	<?php } ?>

	<div class="row">
		<?php echo $form->labelEx($model,'officePhone'); ?>
		<?php echo $form->textField($model,'officePhone',array('size'=>20,'maxlength'=>20)); ?>
		<?php echo $form->error($model,'officePhone'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'cellPhone'); ?>
		<?php echo $form->textField($model,'cellPhone',array('size'=>20,'maxlength'=>20)); ?>
		<?php echo $form->error($model,'cellPhone'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'homePhone'); ?>
		<?php echo $form->textField($model,'homePhone',array('size'=>20,'maxlength'=>20)); ?>
		<?php echo $form->error($model,'homePhone'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'address'); ?>
		<?php echo $form->textField($model,'address',array('size'=>20,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'address'); ?>
	</div>

	<?php if((isset($flag) && !$flag) || !isset($flag)){ ?>
	<div class="row">
		<?php echo $form->labelEx($model,'backgroundInfo'); ?>
		<?php echo $form->textArea($model,'backgroundInfo',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'backgroundInfo'); ?>
	</div>
	<?php } ?>

	<div class="row">
		<?php echo $form->labelEx($model,'emailAddress'); ?>
		<?php echo $form->textField($model,'emailAddress',array('size'=>20,'maxlength'=>100,'disabled'=>isset($flag)&&$flag?'disabled':'')); ?>
		<?php echo $form->error($model,'emailAddress'); ?>
	</div>

	<div class="row">
        <?php if(isset($flag) && $flag){ $model->status=1; } ?>
		<?php echo $form->labelEx($model,'status'); ?>
		<?php echo $form->dropDownList($model,'status', array(1=>'Active', 0=>'Inactive'),array('disabled'=>isset($flag)&&$flag?'disabled':'')); ?>
		<?php echo $form->error($model,'status'); ?>
	</div>
       <?php if((isset($flag) && !$flag) || !isset($flag)){?>
            <label><?php echo Yii::t('users','Roles');?></label>
            <br />
            <?php
            echo CHtml::dropDownList('roles[]',$selectedRoles,$roles,array('class'=>'multiselect','multiple'=>'multiple', 'size'=>6));
            ?>
            <br />
            <label><?php echo Yii::t('app','Groups');?></label>
            <br />
            <?php
            echo CHtml::dropDownList('groups[]',$selectedGroups,$groups,array('class'=>'multiselect','multiple'=>'multiple', 'size'=>6));
            ?>
            <br />
			<?php } ?>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
