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

<h3>Add A Custom Field</h3>
This form allows you to add custom fields to models.
<div><br /></div>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'field-form',
	'enableAjaxValidation'=>false,
        'action'=>'addField',
)); ?>



	<div class="row">
            <?php echo $form->labelEx($model,'modelName'); ?>
            <?php echo $form->dropDownList($model,'modelName',array('Actions'=>'Actions','Contacts'=>'Contacts','Sales'=>'Sales','Accounts'=>'Accounts'),
                array(
                'empty'=>'Select a model',
                'ajax' => array(
                'type'=>'POST', //request type
                'url'=>$this->createUrl('admin/getAttributes'), //url to call.
                //Style: CController::createUrl('currentController/methodToCall')
                'update'=>'#'.CHtml::activeId($model,'fieldName'), //selector to update
                //'data'=>'js:"modelType="+$("'.CHtml::activeId($model,'modelType').'").val()' 
                //leave out the data key to pass all form values through
                ))); ?>
            <?php echo $form->error($model,'modelName'); ?>
	</div>
        
        <div class="row">
            <br /><div>Field Name <b>MUST</b> be of the format: wordWordWord. i.e. firstName
                <br />The first letter must be lowercase and each following word should have its first letter capitalized.
                <br />No spaces are allowed.</div><br />
            <?php echo $form->labelEx($model,'fieldName'); ?>
            <?php echo $form->textField($model,'fieldName'); ?>
            <?php echo $form->error($model,'fieldName'); ?>
        </div>
        
        <div class="row">
            <br /><div>Attribute Label is what you want the field to be displayed as. <br />
                So for the field firstName, the label should probably be First Name</div><Br />
            <?php echo $form->labelEx($model,'attributeLabel'); ?>
            <?php echo $form->textField($model,'attributeLabel'); ?>
            <?php echo $form->error($model,'attributeLabel'); ?>
        </div>

        
	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button')); ?>
	</div>
<?php $this->endWidget(); ?>
</div>
