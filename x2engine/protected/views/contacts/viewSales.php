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

$this->menu=array(
	array('label'=>Yii::t('contacts','Contacts Lists'),'url'=>array('index')),
	array('label'=>Yii::t('contacts','Create Contact'),'url'=>array('create')),
	array('label'=>Yii::t('contacts','Create Lead'),'url'=>array('actions/quickCreate')),
	array('label'=>Yii::t('contacts','View Contact'),'url'=>array('view','id'=>$model->id)),
	array('label'=>Yii::t('contacts','View Sales')),
	array('label'=>Yii::t('contacts','Delete Contact'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('app','Are you sure you want to delete this item?'))),
);
if (Yii::app()->user->getName() == $model->assignedTo || Yii::app()->user->getName() == 'admin' || $model->assignedTo == 'Anyone') {
	$this->menu[] = array('label'=>Yii::t('contacts','Update Contact'), 'url'=>array('update', 'id'=>$model->id));
}
?>

<?php 
	$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'sales-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<h2>'.Yii::t('sales','Sales for Contact: '.$model->name).'</h2><div class="title-bar">'
		.'{summary}</div>{items}{pager}',
	'dataProvider'=>new CArrayDataProvider($sales),
	'columns'=>array(
		array(
			'name'=>'name',
                        'header'=>Yii::t("sales",'Name'),
			'value'=>'CHtml::link($data->name,array("sales/view","id"=>$data->id))',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'40%'),
		),
		//'description',
		array(
			'name'=>'quoteAmount',
                        'header'=>Yii::t("sales",'Quote Amount'),
			'value'=>'Yii::app()->locale->numberFormatter->formatCurrency($data->quoteAmount,Yii::app()->params->currency)',
			'type'=>'raw',
		),
		array(
			'name'=>'salesStage',
                        'header'=>Yii::t("sales",'Sales Stage'),
			'value'=>'Yii::t("sales",$data->salesStage)',
			'type'=>'raw',
		),
		
		array(
			'name'=>'expectedCloseDate',
                        'header'=>Yii::t("sales",'Expected Close Date'),
			'value'=>'empty($data->expectedCloseDate)?"":date("Y-m-d",$data->expectedCloseDate)',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'13%'),
		),
                array(
                    'name'=>'probability',
                    'header'=>Yii::t("sales",'Probability'),
                    'value'=>'$data->probability',
                    'type'=>'raw',
                ),
		array(
			'name'=>'assignedTo',
                        'header'=>Yii::t("sales",'Assigned To'),
			'value'=>'empty($data->assignedTo)?Yii::t("app","Anyone"):$data->assignedTo',
			'type'=>'raw',
		),
		/*
		'leadSource',
		
		'createDate',
		'notes',
		*/
	),
));