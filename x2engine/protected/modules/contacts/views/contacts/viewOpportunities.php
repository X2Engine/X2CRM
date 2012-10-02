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

$authParams['assignedTo'] = $model->assignedTo;
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('contacts','All Contacts'),'url'=>array('index')),
	array('label'=>Yii::t('contacts','Lists'),'url'=>array('lists')),
	array('label'=>Yii::t('contacts','Create Contact'),'url'=>array('create')),
	array('label'=>Yii::t('contacts','View'),'url'=>array('view', 'id'=>$model->id)),
    array('label'=>Yii::t('contacts','Edit Contact'),'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('contacts','Share Contact'),'url'=>array('shareContact','id'=>$model->id)),
	array('label'=>Yii::t('contacts','View Relationships')),
	array('label'=>Yii::t('contacts','Delete Contact'),'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
),$authParams);

?>

<?php 
	$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'opportunities-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<h2>'.Yii::t('opportunities','Relationships for Contact: '.$model->name).'</h2><div class="title-bar">'
		.'{summary}</div>{items}{pager}',
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		array(
			'name'=>'secondType',
                        'header'=>Yii::t("contacts",'Type'),
			'value'=>'($data->firstType=="Contacts" && $data->firstId=="'.$model->id.'")?$data->secondType:$data->firstType',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'40%'),
		),
                array(
			'name'=>'name',
                        'header'=>Yii::t("contacts",'Record'),
			'value'=>'($data->firstType=="Contacts" && $data->firstId=="'.$model->id.'")?
                            (!is_null(CActiveRecord::model($data->secondType)->findByPk($data->secondId))?CHtml::link(CActiveRecord::model($data->secondType)->findByPk($data->secondId)->name,array("/".strtolower($data->secondType)."/".strtolower($data->secondType)."/view/id/".$data->secondId)):"Record not found."):
                            (!is_null(CActiveRecord::model($data->firstType)->findByPk($data->firstId))?CHtml::link(CActiveRecord::model($data->firstType)->findByPk($data->firstId)->name,array("/".strtolower($data->firstType)."/".strtolower($data->firstType)."/view/id/".$data->firstId)):"Record not found.")',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'40%'),
		),
	),
));