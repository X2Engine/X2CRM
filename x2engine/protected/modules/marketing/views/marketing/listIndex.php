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




$heading = Yii::t('contacts','{module} Lists', array('{module}'=>Modules::displayName(false))); 
$this->pageTitle = $heading;

$opportunityModule = Modules::model()->findByAttributes(array('name'=>'opportunities'));
$accountModule = Modules::model()->findByAttributes(array('name'=>'accounts'));

$menuOptions = array(
    'all', 'lists', 'create', 'createList',
);
if ($opportunityModule->visible && $accountModule->visible)
    $menuOptions[] = 'quick';
$this->insertMenu($menuOptions);


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

<?php
$attributeLabels = CActiveRecord::model('X2List')->attributeLabels();

$this->widget('X2GridViewGeneric', array(
	'id'=>'lists-grid',
	//'enableSorting'=>tru,
	//'baseScriptUrl'=>Yii::app()->theme->getBaseUrl().'/css/gridview',
	//'htmlOptions'=>array('class'=>'grid-view contact-lists fullscreen'),
	'template'=> '<div class="page-title icon contacts"><h2>'.$heading.'</h2>{buttons}{filterHint}{summary}</div>{items}{pager}',

    'buttons' => array('clearFilters', 'autoResize'),
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
    'filter' => $filter,
    'gvSettingsName' => 'listsGrid',
	// 'filter'=>$model,
	//'rowCssClassExpression'=>'$data["id"]==="all"?"bold":"$this->rowCssClass[$row%"',
	'rowCssClassExpression'=>'$this->rowCssClass[$row%2].($data["id"]==="all"?" bold":"")',
    'defaultGvSettings' => array (
        'name' => 180,
        'type' => 180,
        'assignedTo' => 180,
        'count' => 180,
        'gvControls' => 75,
    ),
	'columns'=>array(
		array(
			'name'=>'name',
			'header'=>$attributeLabels['name'],
			'type'=>'raw',
			'value'=>'CHtml::link($data["name"],X2List::getRoute($data["id"]))',
		),
		array(
			'name'=>'type',
			'header'=>$attributeLabels['type'],
			'type'=>'raw',
			'value'=>'$data["type"]=="static"? Yii::t("contacts","Static") : Yii::t("contacts","Dynamic")',
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
            'filter' => '',
            // Show estimated count for dynamic lists to avoid multiple expensive calculations
			'value'=>'Yii::app()->locale->numberFormatter->formatDecimal(($data["type"] == "dynamic") ? $data["count"] : $data->calculateCount ())',
		),
        array (
            'id' => 'C_gvControls',
            'class' => 'X2ButtonColumn',
            'header' => Yii::t('app','Tools'),
            'updateButtonUrl' => 
                "Yii::app()->createUrl ('/contacts/updateList', array ('id' => \$data['id']))",
            'cssClassExpression' =>
                "!is_numeric (\$data['id']) ? 'hide-edit-delete-buttons' : ''",
            'viewButtonUrl' => 
                "X2List::getRoute (\$data['id'])",
            'deleteButtonUrl' => 
                "Yii::app()->createUrl ('/contacts/deleteList', array ('id' => \$data['id']))",
        ),
	),
)); ?>
<div class="form">
<?php echo CHtml::link('<span>'.Yii::t('app','New List').'</span>',array('/marketing/marketing/createList'),array('class'=>'x2-button')); ?>
</div>
