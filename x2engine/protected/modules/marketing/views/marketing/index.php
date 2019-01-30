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




$this->pageTitle = Yii::t('marketing','Campaigns');
$menuOptions = array(
    'all', 'create', 'lists', 'import', 'export', 'newsletters', 'weblead', 'lists',
    
    'webtracker', 'x2flow', 
);

$plaOptions = array(
    'anoncontacts', 'fingerprints'
);
$menuOptions = array_merge($menuOptions, $plaOptions);

$this->insertMenu($menuOptions);


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
$buttons = array('advancedSearch','clearFilters','columnSelector','autoResize','showHidden');
if (Yii::app()->params->isAdmin) {
    array_unshift($buttons, 'updateBouncedEmails');
}
$this->widget('X2GridView', array(
	'id'=>'marketing-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'title'=>Yii::t('marketing','Campaigns'),
	'buttons'=> $buttons,
	'template'=> 
        '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">'.
        '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">'.
        '<div id="x2-gridview-page-title" '.
         'class="page-title icon marketing x2-gridview-fixed-title">'.
        '{title}{buttons}{filterHint}'.
        '{massActionButtons}'.
        '{summary}{topPager}{items}{pager}',
    'fixedHeader'=>true,
	'dataProvider'=>$model->search(),
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$model,
	// 'columns'=>$columns,
	'modelName'=>'Campaign',
	'viewName'=>'campaigns',
	// 'columnSelectorId'=>'contacts-column-selector',
	'defaultGvSettings'=>array(
        'gvCheckbox' => 28,
        'name' => 152,
        'listId' => 120,
        'suppressionListId' => 110,
        'subject' => 267,
        'launchDate' => 186,
        'active' => 102,
        'lastUpdated' => 167,
	),
	'specialColumns'=>array(
		'name'=>array(
			'name'=>'name',
			'value'=>'CHtml::link($data->renderAttribute("name"),array("view","id"=>$data->id))',
			'type'=>'raw',
		),
		'description'=>array(
			'name'=>'description',
			'header'=>Yii::t('marketing','Description'),
			'value'=>'Formatter::trimText($data->description)',
			'type'=>'raw',
		),
	),
	'enableControls'=>true,
	'fullscreen'=>true,
));
 ?>
