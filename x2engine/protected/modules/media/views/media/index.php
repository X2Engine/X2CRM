<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/
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

		if(typeof mediaId != null && mediaId.length) {
			$(this).qtip({
				content: {
					text: "'.addslashes(Yii::t('app','loading...')).'",
					ajax: {
						url: yii.scriptUrl+"/media/qtip",
						data: { id: mediaId[0] },
						method: "get",
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

$heading=Yii::t('media','Media & File Library');
$this->widget('application.components.X2GridView', array(
	'id' => 'media-grid',
	 	'template'=> '<div class="page-title icon media"><h2>'.$heading.'</h2><div class="title-bar">'
		.CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
		.CHtml::link(Yii::t('app','Clear Filters'),array(Yii::app()->controller->action->id,'clearFilters'=>1)) . ' | '
		.CHtml::link(Yii::t('app','Columns'),'javascript:void(0);',array('class'=>'column-selector-link')) . ' | '
		.X2GridView::getFilterHint()
		.'{summary}</div></div>{items}{pager}',
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
		'fileName'=> 80,
		'associationType' => 20,
		'createDate'=>40,
		'uploadedBy'=>20,
		'filesize' => 20,
		'mimetype' => 80
	),
	'modelName'=>'Media',
	'specialColumns' => array(
		'fileName' => array(
			'name' => 'fileName',
			'header' => Yii::t('media','File Name'),
			'type' => 'raw',
			'value' => 'CHtml::link(CHtml::encode($data["fileName"]), array("view","id"=>$data->id), array("class" => "media-name"))',
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
));

?>