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

$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('groups','Group List')),
	array('label'=>Yii::t('groups','Create Group'), 'url'=>array('create')),
));

?>
<div class="flush-grid-view">
<?php 

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'roles-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<div class="page-title icon groups"><h2>'.Yii::t('groups','Groups').'</h2><div class="title-bar">'
		.'{summary}</div></div>{items}{pager}',
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		array(
            'header'=>Yii::t('groups','Name'),
			'name'=>'name',
			'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
			'type'=>'raw',
		),
		array(
            'header'=>Yii::t('groups','Users'),
			'name'=>'users',
			'value'=>'count(GroupToUser::model()->findAllByAttributes(array("groupId"=>$data->id)))',
			'type'=>'raw',
		),
	),
)); ?>
</div>
