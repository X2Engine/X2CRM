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
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('media', 'All Media')),
	array('label'=>Yii::t('media', 'Upload'), 'url'=>array('upload')),
));

// init qtip for media filenames
Yii::app()->clientScript->registerScript('media-qtip', '
function refreshQtip() {
	$(".media-name").each(function (i) {
		var mediaId = $(this).attr("href").match(/\\d+$/);

		if(mediaId !== null && mediaId.length) {
			$(this).qtip({
				content: {
					text: "'.addslashes(Yii::t('app','loading...')).'",
					ajax: {
						url: yii.scriptUrl+"/media/qtip",
						data: { id: mediaId[0] },
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

$this->widget('application.components.X2GridView', array(
	'id' => 'media-grid',
	'title'=>Yii::t('media','Media & File Library'),
	'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize'),
	'template'=> '<div class="page-title icon media">{title}{buttons}{filterHint}{summary}</div>{items}{pager}',
	/*
	'template'=>'<div class="page-title"><h2>'.$heading.'</h2><div class="title-bar">'
		.CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
		.CHtml::link(Yii::t('app','Clear Filters'),array(Yii::app()->controller->action->id,'clearFilters'=>1)) . ' | '
		.X2GridView::getFilterHint()
		.'{summary}</div></div>{items}{pager}',
	 */
	'dataProvider' => $model->search(),
	'summaryText' => Yii::t('app','<b>{start}&ndash;{end}</b> of <b>{count}</b>')
		. '<div class="form no-border" style="display:inline;"> '
		. CHtml::dropDownList('resultsPerPage', Profile::getResultsPerPage(), Profile::getPossibleResultsPerPage(), array(
		    	'ajax' => array(
		    		'url' => $this->createUrl('/profile/setResultsPerPage'),
		    		'complete' => "function(response) { $.fn.yiiGridView.update('media-grid', {data: {'id_page': 1}}) }",
		    		'data' => "js: {results: $(this).val()}",
		    	),
		    	'style' => 'margin: 0;',
		    ))
		. ' </div>'
		. Yii::t('app', 'results per page.'),
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'filter'=>$model,
	'defaultGvSettings'=>array(
		'fileName' => 285,
		'associationType' => 85,
		'createDate' => 94,
		'uploadedBy' => 114,
		'filesize' => 75,
	),
	'modelName'=>'Media',
	'specialColumns' => array(
		'fileName' => array(
			'name' => 'fileName',
			'header' => Yii::t('media','File Name'),
			'type' => 'raw',
			'value' => '$data["drive"]?CHtml::link($data["title"],array("view","id"=>$data->id), array("class" => "media-name")):CHtml::link(CHtml::encode($data["fileName"]), array("view","id"=>$data->id), array("class" => "media-name"))',
		),
		'uploadedBy' => array(
			'name' => 'uploadedBy',
			'header' => Yii::t('media','Uploaded By'),
			'type' => 'raw',
			'value' => 'User::getUserLinks($data["uploadedBy"])'
		),
		'associationType' => array(
			'name' => 'associationType',
			'header' => Yii::t('media','Association'),
			'type' => 'raw',
			'value' => 'CHtml::encode($data["associationType"])'
		),
		'createDate' => array(
			'name' => 'createDate',
			'header' => Yii::t('media','Create Date'),
			'type' => 'raw',
			'value' => 'Formatter::formatLongDate($data->createDate)'
		),
		'filesize' => array(
			'name' => 'filesize',
			'header' => Yii::t('media','File Size'),
			'type' => 'raw',
			'value' => '$data->fmtSize'
		),
		'dimensions' => array(
			'name' => 'dimensions',
			'header' => Yii::t('media','Dimensions'),
			'type' => 'raw',
			'value' => '$data->fmtDimensions'
		),
	),
	'fullscreen'=>true,
));

?>
