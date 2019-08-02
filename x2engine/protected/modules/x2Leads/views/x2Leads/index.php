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




$this->pageTitle = CHtml::encode (
    Yii::app()->settings->appName . ' - '.Yii::t('x2Leads', 'Leads'));

$menuOptions = array(
    'index', 'create', 'import', 'export', 'lists',
);
$this->insertMenu($menuOptions);

 //these hidden field is here to stop google auto fill from filling in the grid
$ConFields = X2Model::model("X2Leads")->getFields();
foreach($ConFields as $field){
    echo '<input type="hidden" id="X2Leads[' . $field->fieldName . ']" name="X2Leads[' . $field->fieldName . ']">';      
}  
    
    
$enableSelectAllOnAllPages = true;


Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('x2Leads-grid', {
		data: $(this).serialize()
	});
	return false;
});
");

$this->widget('X2GridView', array(
	'id'=>'x2Leads-grid',
	'title'=>Yii::t('x2Leads','{leads}', array('{leads}' => Modules::displayName())),
        'enableSelectAllOnAllPages' => $enableSelectAllOnAllPages,
	'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize','showHidden'),
	'template'=>
        '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">'.
        '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">'.
        '<div id="x2-gridview-page-title" '.
         'class="page-title icon x2Leads x2-gridview-fixed-title">'.
        '{title}{buttons}{filterHint}'.
        '{massActionButtons}'.
        '{summary}{topPager}{items}{pager}',
    'fixedHeader'=>true,
	'dataProvider'=>$model->search(),
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$model,
	'pager'=>array('class'=>'CLinkPager','maxButtonCount'=>10),
	// 'columns'=>$columns,
	'modelName'=>'X2Leads',
	'viewName'=>'x2Leads',
	// 'columnSelectorId'=>'contacts-column-selector',
	'defaultGvSettings'=>array(
        'gvCheckbox' => 30,
		'name' => 164,
		'quoteAmount' => 95,
		'probability' => 77,
		'expectedCloseDate' => 125,
		'createDate' => 78,
		'lastActivity' => 79,
		'assignedTo' => 119,
	),
    'excludedColumns' => array ('convertedToType', 'convertedToId'),
    'dataColumnClass' => 'application.modules.x2Leads.components.X2LeadsDataColumn',
	'specialColumns'=>array(
		'name'=>array(
			'name'=>'name',
			'header'=>Yii::t('x2Leads','Name'),
			'value'=>'CHtml::link($data->renderAttribute("name"),array("view","id"=>$data->id))',
			'type'=>'raw',
		),
        'id'=>array(
			'name'=>'id',
			'header'=>Yii::t('x2Leads','ID'),
			'value'=>'CHtml::link($data->id,array("view","id"=>$data->id))',
			'type'=>'raw',
		),
	),
        'massActions' => array(
        'MassDelete', 'MassTag', 'MassTagRemove', 'MassUpdateFields', 
        'MergeRecords', 'MassPublishNote', 'MassPublishCall', 'MassPublishTime', 
        'MassPublishAction', 'MassAddRelationship', 
        'MassAddToList', 'NewListFromSelection', 'MassExecuteMacro'
        ),
	'enableControls'=>true,
	'fullscreen'=>true,
));

?>
