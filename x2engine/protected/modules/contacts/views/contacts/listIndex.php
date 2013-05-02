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

$heading = Yii::t('contacts','Contact Lists'); 
$this->pageTitle = $heading;

$menuItems = array(
	array('label'=>Yii::t('contacts','All Contacts'),'url'=>array('index')),
	array('label'=>Yii::t('contacts','Lists')),
	array('label'=>Yii::t('contacts','Create Contact'),'url'=>array('create')),
	array('label'=>Yii::t('contacts','Create List'),'url'=>array('createList')),
);

$opportunityModule = Modules::model()->findByAttributes(array('name'=>'opportunities'));
$accountModule = Modules::model()->findByAttributes(array('name'=>'accounts'));

if($opportunityModule->visible && $accountModule->visible)
	$menuItems[] = 	array('label'=>Yii::t('app', 'Quick Create'), 'url'=>array('/site/createRecords', 'ret'=>'contacts'), 'linkOptions'=>array('id'=>'x2-create-multiple-records-button', 'class'=>'x2-hint', 'title'=>Yii::t('app', 'Create a Contact, Account, and Opportunity.')));

$this->actionMenu = $this->formatMenu($menuItems);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('contacts-grid', {
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
<?php /* $this->renderPartial('_search',array(
	'model'=>$model, 
        'users'=>User::getNames(),
)); */ ?> 
</div><!-- search-form -->
<?php
$attributeLabels = CActiveRecord::model('X2List')->attributeLabels();

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'lists-grid',
	'enableSorting'=>false,
	'baseScriptUrl'=>Yii::app()->theme->getBaseUrl().'/css/gridview',
	'htmlOptions'=>array('class'=>'grid-view contact-lists'),
	'template'=> '<div class="page-title icon contacts"><h2>'.$heading.'</h2><div class="title-bar">{summary}</div></div>{items}{pager}',
	'summaryText' => Yii::t('app','<b>{start}&ndash;{end}</b> of <b>{count}</b>')
		. '<div class="form no-border" style="display:inline;"> '
		. CHtml::dropDownList('resultsPerPage', Profile::getResultsPerPage(),Profile::getPossibleResultsPerPage(),array(
				'ajax' => array(
					'url' => $this->createUrl('/profile/setResultsPerPage'),
					'data' => 'js:{results:$(this).val()}',
					'complete' => 'function(response) { $.fn.yiiGridView.update("lists-grid"); }',
				),
				// 'style' => 'margin: 0;',
			))
		. ' </div>',
	'dataProvider'=>$contactLists,
	// 'filter'=>$model,
	'rowCssClassExpression'=>'$data["id"]==="all"?"bold":""',
	'columns'=>array(
		//'id',
		array(
			'name'=>'name',
			'header'=>$attributeLabels['name'],
			'type'=>'raw',
			'value'=>'CHtml::link($data["name"],X2List::getRoute($data["id"]))',
			'headerHtmlOptions'=>array('style'=>'width:40%;'),
		),
		array(
			'name'=>'type',
			'header'=>$attributeLabels['type'],
			'type'=>'raw',
			'value'=>'$data["type"]=="static"? Yii::t("contacts","Static") : Yii::t("contacts","Dynamic")',
			'headerHtmlOptions'=>array('style'=>'width:15%;'),
		),
		array(
			'name'=>'assignedTo',
			'header'=>$attributeLabels['assignedTo'],
			'type'=>'raw',
			'value'=>'User::getUserLinks($data["assignedTo"])',
		),
		array(
			'name'=>'count',
			'header'=>$attributeLabels['count'],
			'headerHtmlOptions'=>array('class'=>'contact-count'),
			'htmlOptions'=>array('class'=>'contact-count'),
			'value'=>'Yii::app()->locale->numberFormatter->formatDecimal($data["count"])',
			'headerHtmlOptions'=>array('style'=>'width:20%;'),
		),
	),
)); ?>
<br>
<?php
echo CHtml::link('<span class="add-button">'.Yii::t('app','New List').'</span>',array('/contacts/createList'),array('class'=>'x2-button'));
