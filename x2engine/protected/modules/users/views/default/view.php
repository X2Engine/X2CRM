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
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
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
	array('label'=>Yii::t('users','Manage Users'), 'url'=>array('admin')),
	array('label'=>Yii::t('users','Create User'), 'url'=>array('create')),
	array('label'=>Yii::t('users','View User')),
	array('label'=>Yii::t('users','Update User'), 'url'=>array('update', 'id'=>$model->id)),
        array('label'=>Yii::t('contacts','Delete User'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('app','Are you sure you want to delete this item?'))),
);
?>
<h1><?php echo Yii::t('users','User: {name}',array('{name}'=>$model->firstName.' '.$model->lastName)); ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'baseScriptUrl'=>'/x2engine/themes/'.Yii::app()->theme->name.'/css/detailview',
	'attributes'=>array(
		'firstName',
		'lastName',
		'username',
		'title',
		'department',
		'officePhone',
		'cellPhone',
		'homePhone',
		'address',
		'backgroundInfo',
		'emailAddress',
		array(
			'name'=>'status',
			'type'=>'raw',
			'value'=>$model->status==1?"Active":"Inactive",
		),
	),
)); ?>

<h2>Action History</h2>

<?php
foreach($actionHistory as $action) {
	$this->widget('zii.widgets.CDetailView', array(
		'data'=>$action,
		'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/detailview',
		'attributes'=>array(
			array(
				'label'=>'Action Description',
				'type'=>'raw',
				'value'=>CHtml::link(CHtml::encode($action->actionDescription),
							 array('/actions/default/view','id'=>$action->id)),
			),
			'assignedTo',
                        array(  
                                'name'=>'dueDate',
				'label'=>'Due Date',
				'type'=>'raw',
				'value'=>date("F j, Y",$action->dueDate),
			),
			array(
				'label'=>'Complete',
				'type'=>'raw',
				'value'=>CHtml::tag("b",array(),CHtml::tag("font",$htmlOptions=array('color'=>'green'),CHtml::encode($action->complete)))
			),
			'priority',
			'type',
                        array(  
                                'name'=>'createDate',
				'label'=>'Create Date',
				'type'=>'raw',
				'value'=>date("F j, Y",$action->createDate),
			),
		),
	));
}
?><br /><br />
