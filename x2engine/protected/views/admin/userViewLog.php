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
?>
<?php

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'sessions-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<div class="page-title"><h2>'.Yii::t('admin','User View Log').'</h2><div class="title-bar">'
		.'{summary}</div></div>{items}{pager}',
	'summaryText'=>Yii::t('app','<b>{start}&ndash;{end}</b> of <b>{count}</b>'),
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		array(
            'name'=>'user',
            'header'=>Yii::t('admin','User'),
            'type'=>'raw',
            'value'=>'User::getUserLinks($data->user)',
        ),
         array(
            'name'=>'link',
            'header'=>Yii::t('admin','Link'),
            'type'=>'raw',
            'value'=>'X2Model::getModelLink($data->recordId,X2Model::getModelName($data->recordType))',
        ),
        array(
            'name'=>'timestamp',
            'header'=>Yii::t('admin','Timestamp'),
            'type'=>'raw',
            'value'=>'Formatter::formatCompleteDate($data->timestamp)',
        ),
        array(
            'name'=>'recordType',
            'header'=>Yii::t('admin','Record Type'),
            'type'=>'raw',
        ),
        array(
            'name'=>'recordId',
            'header'=>Yii::t('admin','Record ID'),
            'type'=>'raw',
        ),
	),
));
echo "<br>";
echo CHtml::link(Yii::t('admin','Clear View History'),'clearViewHistory',array('class'=>'x2-button','confirm'=>'Are you sure you want to delete the user view history?'));