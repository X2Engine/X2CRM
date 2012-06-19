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

$this->menu=array(
	array('label'=>Yii::t('quotes','Quotes List'), 'url'=>array('index')),
	array('label'=>Yii::t('quotes','Create Quote'), 'url'=>array('create')),
	array('label'=>Yii::t('quotes','View Quote'), 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>Yii::t('quotes','Update Quote'), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('quotes','Add A User'), 'url'=>array('addUser', 'id'=>$model->id)),
	array('label'=>Yii::t('quotes','Add A Contact'), 'url'=>($action=='Add')?null:array('addContact', 'id'=>$model->id)),
	array('label'=>Yii::t('quotes','Remove A User'), 'url'=>array('removeUser', 'id'=>$model->id)),
	array('label'=>Yii::t('quotes','Remove A Contact'), 'url'=>($action=='Remove')?null:array('removeContact', 'id'=>$model->id)),
);
?>

<h2><?php echo Yii::t('quotes','Update Quote:'); ?> <b><?php echo $model->name; ?></b></h2>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'acocunts-form',
	'enableAjaxValidation'=>false,
)); 
echo ($action=='Remove')? Yii::t('quotes','Please select the contacts you wish to remove.') : Yii::t('quotes','Please select the contacts you wish to add.');
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
	<?php echo CHtml::submitButton(Yii::t('quotes',$action),array('class'=>'x2-button')); ?>
</div>

<?php $this->endWidget(); ?>

</div>