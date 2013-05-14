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

$this->pageTitle = Yii::t('marketing','Campaigns');
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('marketing','All Campaigns')),
	array('label'=>Yii::t('marketing','Create Campaign'), 'url'=>array('create')),
	array('label'=>Yii::t('contacts','Contact Lists'), 'url'=>array('/contacts/lists')),
	array('label'=>Yii::t('marketing','Newsletters'), 'url'=>array('weblist/index')),
	array('label'=>Yii::t('marketing','Web Lead Form'), 'url'=>array('webleadForm')),
	array('label'=>Yii::t('marketing','Web Tracker'), 'url'=>array('webTracker')),
	array('label'=>Yii::t('marketing','Marketing Automation'),'url'=>array('/studio/flowIndex'))//(Yii::app()->params->edition==='pro')),
));

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('marketing-grid', {
		data: $(this).serialize()
	});
	return false;
});
");

function trimText($text) {
	if(strlen($text)>150)
		return substr($text,0,147).'...';
	else
		return $text;
}
?>

<?php
foreach(Yii::app()->user->getFlashes() as $key => $message) {
	echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
} ?>

<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->
<?php

$this->widget('application.components.X2GridView', array(
	'id'=>'marketing-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<div class="page-title icon marketing"><h2>'. Yii::t('marketing','Campaigns') .'</h2><div class="title-bar">'
		.CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
		.CHtml::link(Yii::t('app','Clear Filters'),array(Yii::app()->controller->action->id,'clearFilters'=>1)) . ' | '
		.CHtml::link(Yii::t('app','Columns'),'javascript:void(0);',array('class'=>'column-selector-link')) . ' | '
		.X2GridView::getFilterHint()
		.'{summary}</div></div>{items}{pager}',
	'dataProvider'=>$model->search(),
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$model,
	// 'columns'=>$columns,
	'modelName'=>'Campaign',
	'viewName'=>'campaigns',
	// 'columnSelectorId'=>'contacts-column-selector',
	'defaultGvSettings'=>array(
		'name'=>126,
		'description'=>132,
		'listId'=>45,
		'subject'=>127,
		'launchDate'=>105,
		'active'=>50,
	),
	'specialColumns'=>array(
		'name'=>array(
			'name'=>'name',
			'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
			'type'=>'raw',
		),
		'description'=>array(
			'name'=>'description',
			'header'=>Yii::t('marketing','Description'),
			'value'=>'trimText($data->description)',
			'type'=>'raw',
		),
	),
	'enableControls'=>true,
));
 ?>
