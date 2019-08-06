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




Yii::app()->clientScript->registerCss('flowIndexCss',"

#flow-grid {
    border-bottom: 1px solid rgb(219, 219, 219);
}

#create-flow-button {
    margin-left: 3px;
    margin-bottom: 3px;
}

");

$this->actionMenu = array(
    array('label'=>Yii::t('studio','Manage Workflows')),
    array(
        'label'=>Yii::t('studio','Create Workflow'),
        'url'=>array('flowDesigner'),
        'visible' => Yii::app()->getEdition() != 'opensource',
    ),
    array (
        'label' => Yii::t('studio', 'All Trigger Logs'),
        'url' => array ('triggerLogs'),
        'visible' => Yii::app()->getEdition() != 'opensource'
    ),
     
    array (
        'label' => Yii::t('studio', 'Import Workflow'),
        'url' => array ('importFlow'),
        'visible' => Yii::app()->getEdition() != 'opensource'
    ),
     
);

?>
<div class="flush-grid-view">
<?php

$this->widget('X2ActiveGridView', array(
	'id'=>'flow-grid',
	'buttons'=>array('clearFilters','columnSelector','autoResize'),
    'modelName' => 'X2Flow',
	'baseScriptUrl'=>
        Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
    'template'=>
        '<div class="page-title icon x2flow">'.
        '<h2>'.Yii::t('studio','X2Workflow Automation Rules').'</h2>{buttons}'.
        '{summary}</div>{items}{pager}',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
    'defaultGvSettings' => array (
        'name' => 200,
        'description' => 450,
        'active' => 60,
        'triggerType' => 120,
        'modelClass' => 90,
        'createDate' => 150,
        'lastUpdated' => 150,
    ),
    'excludedFields' => array ('id', 'flow'),
    'gvSettingsName' => 'flow-grid',
    'viewName' => 'flowIndex',
	'specialColumns'=>array(
		'name' => array(
			'name'=>'name',
			'headerHtmlOptions'=>array('style'=>'width:40%'),
			'value'=>'CHtml::link(CHtml::encode($data->name),array("/studio/flowDesigner","id"=>$data->id))',
			'type'=>'raw',
		),
        'description' => array(
			'name'=>'description',
			'headerHtmlOptions'=>array('style'=>'width:40%'),
			'value'=>'CHtml::encode($data->description)',
			'type'=>'raw',
		),
		'active' => array(
			'name'=>'active',
			'headerHtmlOptions'=>array('style'=>'width:8%'),
			'value'=>'$data->active? Yii::t("app","Yes") : Yii::t("app","No")',
			'type'=>'raw',
		),
		'triggerType' => array(
			'name'=>'triggerType',
			'headerHtmlOptions'=>array('style'=>'width:15%'),
			'value'=>'X2FlowTrigger::getTriggerTitle ($data->triggerType)',
			'type'=>'raw',
		),
		'modelClass' => array(
			'name'=>'modelClass',
			'headerHtmlOptions'=>array('style'=>'width:10%'),
		),
		// 'flow',
		'createDate' => array(
			'name'=>'createDate',
			'header'=>Yii::t('admin','Create Date'),
            'headerHtmlOptions'=>array('style'=>'width:12%'),
			'value'=>'Formatter::formatDateTime($data->createDate)',
			'type'=>'raw',
			// 'htmlOptions'=>array('width'=>'20%'),
		),
		'lastUpdated' => array(
			'name'=>'lastUpdated',
			'header'=>Yii::t('admin','Last Updated'),
            'headerHtmlOptions'=>array('style'=>'width:12%'),
			'value'=>'Formatter::formatDateTime($data->lastUpdated)',
			'type'=>'raw',
			// 'htmlOptions'=>array('width'=>'20%'),
		),
	),
));
?>
</div>
<br>
<?php
echo CHtml::link(
    Yii::t('studio','Create New Workflow'),
    array('/studio/flowDesigner'),
    array(
        'class'=>'x2-button',
        'id'=>'create-flow-button'
    ));
?>
