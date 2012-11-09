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

$pieces = $model->editPermissions;
$user = Yii::app()->user->getName();

$authParams['assignedTo']=$model->createdBy;
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('docs','List Docs'), 'url'=>array('index')),
	array('label'=>Yii::t('docs','Create Doc'), 'url'=>array('create')),
	array('label'=>Yii::t('docs','View'), 'url'=>array('view','id'=>$model->id)),
    array('label'=>Yii::t('docs','Edit Doc'), 'url'=>array('update', 'id'=>$model->id)),
    array('label'=>Yii::t('docs','Delete Doc'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('docs','Are you sure you want to delete this item?')))
),$authParams);


if(array_search($user,$pieces)!=false || $user==$model->editPermissions || $user=='admin' || $user==$model->createdBy)
	$this->actionMenu[]=array('label'=>Yii::t('docs','Edit Doc Permissions'));
	
$this->actionMenu[] = array('label'=>Yii::t('docs','Export Doc'),'url'=>array('exportToHtml','id'=>$model->id));
	
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'docs-form',
	'enableAjaxValidation'=>false,
));?>
<h2><?php echo Yii::t('docs','Edit Doc Permissions');?></h2>
<?php echo Yii::t('docs','Please select which users are allowed to edit the document.  Use Control + Click to select or deselect individual users.');?>
	<div class="form" id="doc-form"><br /><?php
	echo $form->label($model,'editPermissions');
	echo $form->dropDownList($model,'editPermissions',$users,array('multiple'=>'multiple','size'=>'5'));
	echo $form->error($model,'editPermissions');
	echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create') : Yii::t('app','Save'),array('class'=>'x2-button'));
	?></div><?php
	$this->endWidget();
?>