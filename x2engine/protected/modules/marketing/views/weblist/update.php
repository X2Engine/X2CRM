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






$this->pageTitle = $model->name; 
$authParams['X2Model'] = $model;

$menuOptions = array(
    'all', 'create', 'lists', 'newsletters', 'view', 'edit', 'delete', 'weblead', 'webtracker',
);

$plaOptions = array(
    'anoncontacts', 'fingerprints'
);
$menuOptions = array_merge($menuOptions, $plaOptions);

$this->insertMenu($menuOptions, $model, $authParams);


?>
<div class="page-title icon marketing">
<h2><span class="no-bold"><?php echo Yii::t('module','Update'); ?>:</span> <?php echo $model->renderAttribute('name'); ?></h2>
</div>
<div class="form">
<?php
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'contacts-form',
	'enableAjaxValidation'=>false));
?>
<em><?php echo Yii::t('app','Fields with <span class="required">*</span> are required.'); ?></em>

<div class="row">
	<div class="cell">
		<?php echo $form->labelEx($model,'name'); ?>
		<?php echo $form->textField($model,'name',array('size'=>30,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'name'); ?>
	</div>
	<div class="cell">
		<?php echo $form->labelEx($model,'assignedTo'); ?>
		<?php
			if(empty($model->assignedTo)) {
				$model->assignedTo = Yii::app()->user->getName();
                        }
			echo $form->dropDownList($model,'assignedTo', User::getNames(), array('tabindex'=>null)); ?>
		<?php echo $form->error($model,'assignedTo'); ?>
	</div>
	<div class="cell">
		<?php echo $form->labelEx($model,'visibility'); ?>
		<?php
			echo $form->dropDownList($model,'visibility',array(
				1=>Yii::t('contacts','Public'),
				0=>Yii::t('contacts','Private')
			),array('tabindex'=>null));
		?>
	</div>
</div>

<?php
$validateName = <<<EOE
$('#save-button').click(function(e) {
	if ($.trim($('#X2List_name').val()).length == 0) {
		$('#X2List_name').addClass('error');
		$('[for="X2List_name"]').addClass('error');
		$('#X2List_name').after('<div class="errorMessage">Name cannot be blank.</div>');
		e.preventDefault();
	}
});
EOE;
Yii::app()->clientScript->registerScript('validateName', $validateName, CClientScript::POS_READY);
?>

<div class="row buttons">
	<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button','id'=>'save-button','tabindex'=>24)); ?>
</div>

<?php
$this->endWidget();
?>

</div>
