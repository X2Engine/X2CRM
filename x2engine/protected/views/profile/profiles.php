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




Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('profiles-grid', {
		data: $(this).serialize()
	});
	return false;
});
");

Yii::app()->clientScript->registerCss ('profilesStyle', "
    #profiles-grid .summary {
        margin-left: 5px;
    }
");

$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('profile','Social Feed'),'url'=>array('index')),
	array('label'=>Yii::t('profile','People')),
));
?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model, 
)); ?>
</div>
<div class='flush-grid-view'>
<?php
/*$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'profiles-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<div class="page-title"><h2>'.Yii::t('profile','People').'</h2><div class="title-bar">'
		.CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
		.CHtml::link(Yii::t('app','Clear Filters'),array('index','clearFilters'=>1))
		.'{summary}</div></div>{items}{pager}',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		array(
			'name'=>'fullName',
			'value'=>'CHtml::link($data->fullName,array("view","id"=>$data->id))',
			'headerHtmlOptions'=>array('style'=>'width:35%;'),
			'type'=>'raw',
			),
		'tagLine',
	),
));*/

$this->widget('X2ActiveGridView', array(
	'id'=>'profiles-grid',
	'title'=>Yii::t('profile', 'People'),
	'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize'),
	'template'=> 
        '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">'.
        '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">'.
        '<div id="x2-gridview-page-title" '.
         'class="page-title icon contacts x2-gridview-fixed-title">'.
        '{title}{buttons}{filterHint}{summary}{topPager}{items}{pager}',
    'fixedHeader'=>true,
	'dataProvider'=>$model->search (),
	'filter'=>$model,
	'pager'=>array('class'=>'CLinkPager','maxButtonCount'=>10),
	'modelName'=>'Profile',
	'viewName'=>'profiles',
	'defaultGvSettings'=>array(
		'fullName' => 125,
		'tagLine' => 165,
		'isActive' => 80,
	),
    'includedFields'=>array (
        'tagLine', 'username', 'officePhone', 'cellPhone', 'emailAddress', 'googleId'
    ),
	'specialColumns'=>array(
		'fullName'=>array(
			'name'=>'fullName',
			'header'=>Yii::t('profile', 'Full Name'),
			'value'=>'CHtml::link(CHtml::encode($data->fullName),array("view","id"=>$data->id))',
			'type'=>'raw',
		),
		'tagLine'=>array(
			'name'=>'tagLine',
			'header'=>Yii::t('profile', 'Tag Line'),
			'value'=>'CHtml::encode($data->tagLine)',
			'type'=>'raw',
		),
		'isActive'=>array(
			'name'=>'isActive',
			'header'=>Yii::t('profile', 'Active'),
			'value'=>'"<span title=\''.
                '".(Session::isOnline ($data->username) ? '.
                 '"'.Yii::t('profile', 'Active User').'" : "'.Yii::t('profile', 'Inactive User').'")."\''.
                ' class=\'".(Session::isOnline ($data->username) ? '.
                '"active-indicator" : "inactive-indicator")."\'></span>"',
			'type'=>'raw',
		),
	),
	'enableControls'=>false,
	'fullscreen'=>true,
));
?>
</div>
