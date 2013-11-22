<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
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
                            (!is_null(X2Model::model($data->secondType)->findByPk($data->secondId))?CHtml::link(X2Model::model($data->secondType)->findByPk($data->secondId)->name,array("/".strtolower($data->secondType)."/".strtolower($data->secondType)."/view/id/".$data->secondId)):"Record not found."):
                            (!is_null(X2Model::model($data->firstType)->findByPk($data->firstId))?CHtml::link(X2Model::model($data->firstType)->findByPk($data->firstId)->name,array("/".strtolower($data->firstType)."/".strtolower($data->firstType)."/view/id/".$data->firstId)):"Record not found.")',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'40%'),
		),
	),
));