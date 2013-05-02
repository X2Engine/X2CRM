<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

$authParams['assignedTo'] = $model->assignedTo;
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('opportunities','Opportunities List'), 'url'=>array('index')),
	array('label'=>Yii::t('opportunities','Create'), 'url'=>array('create')),
	array('label'=>Yii::t('opportunities','View'), 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>Yii::t('opportunities','Edit Opportunity'), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('accounts','Share Opportunity'),'url'=>array('shareOpportunity','id'=>$model->id)),
	array('label'=>Yii::t('opportunities','Add A User'), 'url'=>array('addUser', 'id'=>$model->id)),
	array('label'=>Yii::t('opportunities','Add A Contact'), 'url'=>($action=='Add')?null:array('addContact', 'id'=>$model->id)),
	array('label'=>Yii::t('opportunities','Remove A User'), 'url'=>array('removeUser', 'id'=>$model->id)),
	array('label'=>Yii::t('opportunities','Remove A Contact'), 'url'=>($action=='Remove')?null:array('removeContact', 'id'=>$model->id)),
),$authParams);
?>
<div class="page-title icon opportunities">
	<h2><span class="no-bold"><?php echo Yii::t('opportunities','Update'); ?>:</span> <?php echo $model->name; ?></h2>
</div>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'acocunts-form',
	'enableAjaxValidation'=>false,
)); 
echo ($action=='Remove')? Yii::t('opportunities','Please select the contacts you wish to remove.') : Yii::t('opportunities','Please select the contacts you wish to add.');
?>
<br /><br />
<div class="row">
	<?php $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
				'name'=>'auto_select',
				'source' => $this->createUrl('/contacts/getContacts'),
				'htmlOptions'=>array('size'=>25,'maxlength'=>100,'tabindex'=>3),
				'options'=>array(
					'minLength'=>'2',
					'select'=>'js:function( event, ui ) {
						$("#'.CHtml::activeId($model,'associatedContacts').'").val(ui.item.id);
						$(this).val(ui.item.value);
						return false;
					}',
				),
			)); ?><br />
	<?php echo $form->dropDownList($model,'associatedContacts',$contacts,array("multiple"=>"multiple", 'size'=>8)); ?>
	<?php echo $form->error($model,'associatedContacts'); ?>
</div>

<div class="row buttons">
	<?php echo CHtml::submitButton(Yii::t('opportunities',$action),array('class'=>'x2-button')); ?>
</div>

<?php $this->endWidget(); ?>

</div>