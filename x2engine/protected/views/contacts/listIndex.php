<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/

$heading = Yii::t('contacts','Contacts Lists'); 
// $dataProvider = $model->searchAll();

$this->menu=array(
	array('label'=>Yii::t('contacts','Contacts Lists')),
	array('label'=>Yii::t('contacts','Create Contact'),'url'=>array('create')),
	array('label'=>Yii::t('contacts','Create Lead'),'url'=>array('actions/quickCreate')),
	array('label'=>Yii::t('contacts','Import Contacts from Outlook'),'url'=>array('importContacts')),
	array('label'=>Yii::t('contacts','Import Contacts from Template'),'url'=>array('importExcel')),
	array('label'=>Yii::t('contacts','Export Contacts'),'url'=>array('export')),
);

// $this->menu=array(
	// array('label'=>Yii::t('contacts','Contact List')),
	// array('label'=>Yii::t('contacts','Create Contact'),'url'=>array('create')),
	// array('label'=>Yii::t('contacts','Create Lead'),'url'=>array('actions/quickCreate')),
	// array('label'=>Yii::t('contacts','Import Contacts from Outlook'),'url'=>array('importContacts')),
	// array('label'=>Yii::t('contacts','Import Contacts from Template'),'url'=>array('importExcel')),
	// array('label'=>Yii::t('contacts','Export Contacts'),'url'=>array('export')),
// );

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

<div class="search-form" style="display:none">
<?php /* $this->renderPartial('_search',array(
	'model'=>$model, 
        'users'=>UserChild::getNames(),
)); */ ?> 
</div><!-- search-form -->
<h2><?php echo $heading; ?></h2>
<?php
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'contacts-grid',
	'enableSorting'=>false,
	'baseScriptUrl'=>Yii::app()->theme->getBaseUrl().'/css/gridview',
	'htmlOptions'=>array('class'=>'grid-view contact-lists'),
	'template'=> '{items}',
	'dataProvider'=>$contactLists,
	// 'filter'=>$model,
	'columns'=>array(
		//'id',
		array(
			'name'=>'name',
			'type'=>'raw',
			'value'=>'CHtml::link($data->name,ContactList::getRoute($data->id))',
		),
		array(
			'name'=>'count',
			'headerHtmlOptions'=>array('class'=>'contact-count'),
			'htmlOptions'=>array('class'=>'contact-count'),
			'value'=>'Yii::app()->locale->numberFormatter->formatDecimal($data->count)',
		),
	),
));