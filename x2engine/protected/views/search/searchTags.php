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
Yii::app()->clientScript->registerScript('set-tag-cookie',"
$('#content').on('mouseup','#tag-search a',function(e) {
	document.cookie = 'vcr-list=".urlencode ($term)."; expires=0; path=/';
});    
");
?>
<div class='flush-grid-view'>
<?php

$this->widget('zii.widgets.grid.CGridView', array(
	'dataProvider' => $tags,
    'id'=>'tag-search',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=>'<div class="page-title"><h2>'.Yii::t('app','Search Results').'</h2>'
		.CHtml::link(Yii::t('marketing','Email These Contacts'),
			array('/marketing/marketing/createFromTag','tag'=>$term),
			array('class'=>'x2-button left','style'=>'margin-bottom:2px;'))
		.'<div class="title-bar">{summary}</div></div>{items}{pager}',
	'summaryText'=>Yii::t('app','<b>{start}&ndash;{end}</b> of <b>{count}</b>'),
	'columns' => array(
		array(
			'name' => Yii::t('app','Record'),
			'type' => 'raw',
			'value' => 'X2Model::getModelLink($data->itemId,$data->type)', 
		),
		array(
			'name' => Yii::t('app','Record Type'),
			'type' => 'raw',
			'value' => '$data->type', 
		),
	),
));
?>
</div>
