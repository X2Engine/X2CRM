<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
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
$authParams['assignedTo'] = $model->assignedTo;
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('opportunities','Opportunities List'), 'url'=>array('index')),
	array('label'=>Yii::t('opportunities','Create'), 'url'=>array('create')),
	array('label'=>Yii::t('app', 'Create Multiple'), 'url'=>array('/site/createRecords', 'ret'=>'opportunities'), 'linkOptions'=>array('id'=>'x2-create-multiple-records-button', 'class'=>'x2-hint', 'title'=>Yii::t('app', 'Create a Contact, Account, and Opportunity.'))),
	array('label'=>Yii::t('opportunities','View'), 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>Yii::t('opportunities','Edit Opportunity')),
	array('label'=>Yii::t('opportunities','Add A User'), 'url'=>array('addUser', 'id'=>$model->id)),
	array('label'=>Yii::t('opportunities','Add A Contact'), 'url'=>array('addContact', 'id'=>$model->id)),
	array('label'=>Yii::t('opportunities','Remove A User'), 'url'=>array('removeUser', 'id'=>$model->id)),
	array('label'=>Yii::t('opportunities','Remove A Contact'), 'url'=>array('removeContact', 'id'=>$model->id)),
	array('label'=>Yii::t('accounts','Share Opportunity'),'url'=>array('shareOpportunity','id'=>$model->id)),
	array('label'=>Yii::t('opportunities','Delete'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
),$authParams);
?>
<?php echo CHtml::link('['.Yii::t('contacts','Show All').']','javascript:void(0)',array('id'=>'showAll','class'=>'right hide','style'=>'text-decoration:none;')); ?>
<?php echo CHtml::link('['.Yii::t('contacts','Hide All').']','javascript:void(0)',array('id'=>'hideAll','class'=>'right','style'=>'text-decoration:none;')); ?>
<h2 style="margin-bottom:0;"><?php echo Yii::t('opportunities','Update Opportunity: {name}',array('{name}'=>$model->name)); ?> <a class="x2-button" href="javascript:void(0);" onclick="$('#save-button').click();">Save</a></h2>

<?php echo $this->renderPartial('application.components.views._form', array('model'=>$model, 'users'=>$users, 'modelName'=>'Opportunity')); ?>