<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/



include("protected/modules/bugReports/bugReportsConfig.php");

$this->actionMenu = $this->formatMenu(array(
    array('label'=>Yii::t('module','{X} List',array('{X}'=>Modules::itemDisplayName()))),
    array('label'=>Yii::t('module','Create {X}',array('{X}'=>Modules::itemDisplayName())), 'url'=>array('create')),
    array('label'=>Yii::t('module','{X} Lists',array('{X}'=>Modules::itemDisplayName())), 'url'=>array('lists')),
    array('label'=>Yii::t('module','Import {X}', array('{X}'=>Modules::itemDisplayName())),
        'url'=>array('admin/importModels', 'model'=>ucfirst($moduleConfig['moduleName'])), 'visibility'=>Yii::app()->params->isAdmin),
    array('label'=>Yii::t('module','Export {X}', array('{X}'=>Modules::itemDisplayName())),
        'url'=>array('admin/exportModels', 'model'=>ucfirst($moduleConfig['moduleName'])), 'visibility'=>Yii::app()->params->isAdmin),
));

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('opportunities-grid', {
		data: $(this).serialize()
	});
	return false;
});
");

?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->
<?php

$this->widget('X2GridView', array(
	'id'=>'bugReports-grid',
	'title'=>$moduleConfig['title'],
	'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize','showHidden'),
	'template'=> '<div class="page-title">{title}{buttons}{filterHint}{summary}</div>{items}{pager}',
	'dataProvider'=>$model->searchWithStatusFilter(),
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$model,
	// 'columns'=>$columns,
	'modelName'=>'bugReports',
	'viewName'=>'bugReports',
	// 'columnSelectorId'=>'contacts-column-selector',
	'defaultGvSettings'=>array(
		'subject'=>100,
		'severity'=>65,
		'status'=>65,
        'type'=>65,
        'description'=>180,
        'assignedTo'=>100,
	),
	'specialColumns'=>array(
		'name'=>array(
			'name'=>'name',
			'value'=>'CHtml::link($data->renderAttribute("name"),array("view","id"=>$data->id))',
			'type'=>'raw',
		),
        'subject'=>array(
			'name'=>'subject',
			'value'=>'CHtml::link($data->renderAttribute("subject"),array("view","id"=>$data->id))',
			'type'=>'raw',
		),
		'description'=>array(
			'name'=>'description',
			'header'=>Yii::t('app','Description'),
			'value'=>'Formatter::trimText($data->renderAttribute("description"))',
			'type'=>'raw',
		),
        'severity'=>array(
            'name'=>'severity',
            'header'=>Yii::t('app','Severity'),
            'value'=>'X2Model::model("Dropdowns")->getDropdownValue(116,$data->renderAttribute("severity"))',
            'type'=>'raw',
        )
	),
	'enableControls'=>true,
	'fullscreen'=>true,
));
 ?>
