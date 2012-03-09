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

include("protected/config/productConfig.php");

$this->menu = array(
	array('label'=>Yii::t('module','{X} List',array('{X}'=>$moduleConfig['recordName'])), 'url'=>array('index')),
	array('label'=>Yii::t('module','Create',array('{X}'=>$moduleConfig['recordName'])), 'url'=>array('create')),
	array('label'=>Yii::t('module','View',array('{X}'=>$moduleConfig['recordName']))),
	array('label'=>Yii::t('module','Update',array('{X}'=>$moduleConfig['recordName'])), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('module','Delete',array('{X}'=>$moduleConfig['recordName'])), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('app','Are you sure you want to delete this item?'))),
);
?>
<?php echo CHtml::link('['.Yii::t('contacts','Show All').']','javascript:void(0)',array('id'=>'showAll','class'=>'right hide','style'=>'text-decoration:none;')); ?>
<?php echo CHtml::link('['.Yii::t('contacts','Hide All').']','javascript:void(0)',array('id'=>'hideAll','class'=>'right','style'=>'text-decoration:none;')); ?>
<h2><?php echo Yii::t('products','Product:'); ?> <b><?php echo $model->name; ?></b> <a class="x2-button" href="update/<?php echo $model->id;?>">Edit</a></h2>

<?php $this->renderPartial('application.components.views._detailView',array('model'=>$model,'modelName'=>'products')); ?>

<?php
$this->widget('InlineActionForm',
		array(
			'associationType'=>'product',
			'associationId'=>$model->id,
			'assignedTo'=>Yii::app()->user->getName(),
			'users'=>$users,
			'startHidden'=>false
		)
);
?>
<div id="attachment-form">
	<?php $this->widget('Attachments',array('type'=>'product','associationId'=>$model->id)); ?>
</div>

<?php
$this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$actionHistory,
	'itemView'=>'../actions/_view',
	'htmlOptions'=>array('class'=>'action list-view'),
	'template'=> '<h3>'.Yii::t('app','History').'</h3>{summary}{sorter}{items}{pager}',
));
?>