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

$isAdmin = (Yii::app()->params->isAdmin);
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('workflow','All Workflows')),
	array('label'=>Yii::t('app','Create'), 'url'=>array('create'), 'visible'=>$isAdmin),
));

?>
<div class='flush-grid-view'>
<?php

$this->widget('zii.widgets.grid.CGridView', array(
	'dataProvider'=>$dataProvider,
	'baseScriptUrl'=>Yii::app()->theme->getBaseUrl().'/css/gridview',
	'template'=> '<div class="page-title icon workflow"><h2>'.Yii::t('workflow','Workflows').'</h2><div class="title-bar">{summary}</div></div>{items}',
	'summaryText' => Yii::t('app','<b>{start}&ndash;{end}</b> of <b>{count}</b>'),
	'enableSorting'=>false,
	'columns'=>array(
		array(
			'name'=>'name',
			'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
			'type'=>'raw',
			'headerHtmlOptions'=>array('style'=>'width:65%;'),
		),
		array(
			'name'=>'isDefault',
			'value'=>'$data->isDefault? Yii::t("app","Yes") : ""',
			'type'=>'raw',
		),
		array(
			'name'=>Yii::t('workflow','Stages'),
			'value'=>'X2Model::model("WorkflowStage")->countByAttributes(array("workflowId"=>$data->id))',
			'type'=>'raw',
		),
	),
)); ?>
</div>
