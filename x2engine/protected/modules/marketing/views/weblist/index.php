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






$this->pageTitle = Yii::t('marketing','Newsletters');

$menuOptions = array(
    'all', 'create', 'lists', 'newsletters', 'weblead',
    
     'webtracker',
);

$plaOptions = array(
    'anoncontacts', 'fingerprints'
);
$menuOptions = array_merge($menuOptions, $plaOptions);

$this->insertMenu($menuOptions);


Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('contacts-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>
<div class="page-title icon marketing"><h2><?php echo Yii::t('marketing','Newsletters'); ?></h2></div>
<div class="form">
<h4><?php echo Yii::t('marketing','Create Newsletter') .':'; ?></h4>
<?php
foreach(Yii::app()->user->getFlashes() as $key => $message) {
    echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
}

$model = new X2List;
$model->assignedTo = Yii::app()->user->getName();
$users = User::getNames();
$form = $this->beginWidget('CActiveForm', array(
	'id'=>'weblist-form',
	'action'=>'create',
	'enableClientValidation'=>true,
	'clientOptions'=>array('validateOnSubmit'=>true),
));
?>

<div class="row">
	<div class="cell">
		<?php echo $form->labelEx($model,'name'); ?>
		<?php echo $form->textField($model,'name',array('size'=>30,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'name'); ?>
	</div>
	<div class="cell">
		<?php echo $form->labelEx($model,'description'); ?>
		<?php echo $form->textField($model,'description',array('size'=>30,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'description'); ?>
	</div>
	<div class="cell">
		<?php echo $form->labelEx($model,'assignedTo'); ?>
		<?php echo $form->dropDownList($model,'assignedTo',$users); ?>
		<?php echo $form->error($model,'assignedTo'); ?>
	</div>
	<div class="cell">
		<?php echo $form->labelEx($model,'visibility'); ?>
		<?php echo $form->dropDownList($model,'visibility',array( 1=>Yii::t('contacts','Public'), 0=>Yii::t('contacts','Private'))); ?>
	</div>
	<div class="cell">
		<?php /*
		echo CHtml::ajaxSubmitButton(Yii::t('app','Create'), 'create', 
			array('success'=>'function() { $.fn.yiiGridView.update("weblist-grid"); $("#weblist-form").each(function() { this.reset(); });}'), 
			array('class'=>'x2-button','id'=>'save-button','style'=>'margin-top: 1em;')
		); */?>
		<?php echo CHtml::submitButton(Yii::t('app','Create'), array('class'=>'x2-button','id'=>'save-button','style'=>'margin-top: 1em;')); ?>
	</div>
</div>
<?php $this->endWidget(); ?>
</div>

<style>div.contact-lists table.items tbody tr:last-child td {font-weight:normal;}</style>
<?php
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'weblist-grid',
	'enableSorting'=>false,
	'baseScriptUrl'=>Yii::app()->theme->getBaseUrl().'/css/gridview',
	'htmlOptions'=>array('class'=>'grid-view contact-lists'),
	'template'=> '{summary}{items}{pager}',
	'dataProvider'=>$dataProvider,
	// 'filter'=>$model,
	'columns'=>array(
		array(
			'name'=>'name',
			'type'=>'raw',
			'value'=>'CHtml::link($data->name, Yii::app()->controller->createUrl("view", array("id"=>$data->id)))',
			'headerHtmlOptions'=>array('style'=>'width:25%;'),
		),
		array(
			'name'=>'description',
			'type'=>'raw',
			'headerHtmlOptions'=>array('style'=>'width:40%;'),
		),
		array(
			'name'=>'assignedTo',
			'type'=>'raw',
			'value'=>'User::getUserLinks($data->assignedTo)',
		),
		array(
			'name'=>'count',
			'headerHtmlOptions'=>array('class'=>'contact-count'),
			'htmlOptions'=>array('class'=>'contact-count'),
			'value'=>'Yii::app()->locale->numberFormatter->formatDecimal($data->count)',
			'headerHtmlOptions'=>array('style'=>'width:10%;'),
		),
	),
)); ?>
