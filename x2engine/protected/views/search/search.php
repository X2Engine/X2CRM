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

Yii::app()->clientScript->registerScript('contact-qtip', '
function refreshQtip() {
	$(".contact-name").each(function (i) {
		var contactId = $(this).attr("href").match(/\\d+$/);

		if(contactId !== null && contactId.length) {
			$(this).qtip({
				content: {
					text: "'.addslashes(Yii::t('app','loading...')).'",
					ajax: {
						url: yii.baseUrl+"/index.php/contacts/qtip",
						data: { id: contactId[0] },
						method: "get"
					}
				},
				style: {
				}
			});
		}
	});
}

$(function() {
	refreshQtip();
});
');
?>
<div class='flush-grid-view'>
<?php $this->widget('zii.widgets.grid.CGridView', array(
	'dataProvider' => $dataProvider,
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=>'<div class="page-title"><h2>'.Yii::t('app','Search Results').'</h2><div class="title-bar">{summary}</div></div>{items}{pager}',
	'summaryText'=>Yii::t('app','<b>{start}&ndash;{end}</b> of <b>{count}</b>'),
	'columns' => array(
		array(
			'name' => Yii::t('app','Name'),
			'type' => 'raw',
			'value' => 'CHtml::link(CHtml::encode($data["name"]), "'.Yii::app()->request->baseUrl.'/index.php".$data["link"],$data["type"]=="Contact"?array("class"=>"contact-name"):"")', 
		),
		array(
			'name' => Yii::t('app','Type'),
			'type' => 'raw',
			'value' => '$data["type"]', 
		),
		array(
			'name' => Yii::t('app','Description'), 
			'type' => 'raw',
			'value' => 'Formatter::truncateText(CHtml::encode($data["description"]),140)'
		),
        array(
			'name' => Yii::t('app','Assigned To'),
			'type' => 'raw',
			'value' => 'isset($data["assignedTo"])?$data["assignedTo"]:""', 
		),
	),
));
?>
</div>
