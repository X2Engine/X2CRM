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
<div class="wide form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'users-form',
	'enableAjaxValidation'=>false,
));
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/multiselect/js/ui.multiselect.js');
Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl().'/js/multiselect/css/ui.multiselect.css','screen, projection');
Yii::app()->clientScript->registerCss('createUserCss',"

.input-warning {
    display: inline-block;
    color: red;
    margin-bottom: 8px;
}
.input-warning + input {
    float: left;
}

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
		<?php
        $unameAttr = $update?'userAlias':'username';
        echo $form->labelEx($model,$unameAttr);
		echo $form->textField($model,$unameAttr,array('size'=>20,'maxlength'=>20));
		echo $form->error($model,$unameAttr);
        ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'password'); ?>
		<?php echo $form->passwordField($model,'password',array('size'=>20,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'password'); ?>
	</div>

	<?php if((isset($create) && !$create) || !isset($create)){ ?>
	<div class="row">
		<?php echo $form->labelEx($model,'userKey'); ?>
		<?php echo $form->textField($model,'userKey',array('size'=>20,'maxlength'=>30)); ?>
		<?php echo $form->error($model,'userKey'); ?>
	</div>
    <?php } ?>

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
		<?php echo $form->textField($model,'department',array('class'=>'x2-wide-input')); ?>
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
		<?php echo $form->textArea($model,'backgroundInfo',array('class'=>'x2-wide-input')); ?>
		<?php echo $form->error($model,'backgroundInfo'); ?>
	</div>
	<?php } ?>

	<div class="row">
		<?php echo $form->labelEx($model,'emailAddress'); ?>
		<?php echo $form->textField($model,'emailAddress',array('size'=>20,'maxlength'=>100,'disabled'=>isset($flag)&&$flag?'disabled':'')); ?>
		<?php echo $form->error($model,'emailAddress'); ?>
	</div>

    <?php if((isset($create) && !$create) || !isset($create)) { ?>
	<div class="row">
        <?php if(isset($flag) && $flag){ $model->status=1; } ?>
		<?php echo $form->labelEx($model,'status'); ?>
		<?php echo $form->dropDownList($model,'status', array(1=>'Active', 0=>'Inactive'),array('disabled'=>isset($flag)&&$flag?'disabled':'')); ?>
		<?php echo $form->error($model,'status'); ?>
	</div>
    <?php } ?>
       <?php if((isset($flag) && !$flag) || !isset($flag)){?>
            <label><?php echo Yii::t('users','Roles');?></label>
            <br />
            <?php
            echo CHtml::dropDownList('roles[]',$selectedRoles,$roles,array('class'=>'multiselect','multiple'=>'multiple', 'size'=>6));
            ?>
            <br />
            <label><?php echo Yii::t('app','{groups}', array('{groups}'=>Modules::displayName(true, "Groups")));?></label>
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
