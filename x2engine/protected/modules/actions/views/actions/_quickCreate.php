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

<?php
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'contacts-form',
	'enableAjaxValidation'=>false,
));
?>
	<em><?php echo Yii::t('app','Fields with <span class="required">*</span> are required.'); ?></em>
	<?php
	// $isQuickCreate = true;	//signal subforms not to call beginWidget()/endWidget(), create submit buttons, etc
	//var_dump(scandir(''));
	//include('../x2engine/protected/views/contacts/_form.php');
	
	$this->widget ('FormView', array(
		'model' => $contactModel,
		'form' => $form,
		'suppressQuickCreate' => true,
	));
	// echo $this->renderPartial('application.components.views.@FORMVIEW',
	// array(
		// 'model'=>$contactModel,
		// 'modelName'=>'contacts',
		// 'users'=>$users,
		// 'isQuickCreate'=>true,
		// 'form'=>$form,
	// ));
	?>
		<?php echo $form->hiddenField($actionModel,'associationId'); ?>

		
    <h2>
        <?php echo Yii::t('actions','{module}', array(
            '{module}' => Modules::displayName(false),
        )); ?>
    </h2>


	<?php echo $form->errorSummary($actionModel); ?>
	
<table class="details">
	<tr>
		<td class="label" width="20%"><?php echo $form->labelEx($actionModel,'actionDescription'); ?></td>
		<td colspan="3" class="text-field"><div class="spacer"></div>
			<?php echo $form->textArea($actionModel,'actionDescription',array('rows'=>6, 'cols'=>50,'tabindex'=>23,'style'=>'width:460px;')); ?>
		</td>
	</tr>
	<tr>
		<td class="label" width="20%"><?php echo $form->labelEx($actionModel,'dueDate'); ?></td>
		<td width="30%">
		<?php
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
			'htmlOptions' => array('tabindex'=>24)
		));
		?>
		<?php echo $form->error($actionModel,'dueDate'); ?>
		</td>
		<td class="label" width="10%"><?php echo $form->labelEx($actionModel,'reminder'); ?></td>
		<td><?php echo $form->dropDownList($actionModel,'reminder',array('No'=>Yii::t('actions','No'),'Yes'=>Yii::t('actions','Yes')),array('tabindex'=>25)); ?> </td>
	</tr>
</table>

	<?php
	
	// echo $this->renderPartial('_form',
	// array(
		// 'actionModel'=>$actionModel,
		// 'users'=>$users,
		// 'isQuickCreate'=>true,
		// 'form'=>$form,
	// ));
	?>

	<div class="row buttons">
		<?php
            echo CHtml::htmlButton($actionModel->isNewRecord ? Yii::t('app','Submit {contact} + {action}', array(
                '{contact}' => Modules::displayName(false, "Contacts"),
                '{action}' => Modules::displayName(false),
            )) : Yii::t('app','Save'),
			array('type'=>'submit',
				'class'=>'x2-button',
				'id'=>'save-button1',
				'name'=>'submit',
				'value'=>'action',
				'tabindex'=>26
			)
		); ?>

		<?php
            echo CHtml::htmlButton(Yii::t('app','Submit {contact} + Comment', array(
                '{contact}' => Modules::displayName(false, "Contacts"),
            )),
				array(
					'type'=>'submit',
					'class'=>'x2-button',
					'id'=>'save-button2',
					'name'=>'submit',
					'value'=>'comment',
					'tabindex'=>27
				)
			); ?>
		<?php // echo CHtml::submitButton(Yii::t('app','Submit'),array('class'=>'x2-button','tabindex'=>25)); ?>
	</div>

<?php $this->endWidget(); ?>

<!-- form -->
