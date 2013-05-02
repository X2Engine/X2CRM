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

$this->actionMenu = array(
	array('label'=>Yii::t('studio','Manage Flows')),
	array('label'=>Yii::t('studio','Create Flow'), 'url'=>array('flowDesigner'),'visible'=>(Yii::app()->params->edition==='pro')),
);

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'changelog-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
        'template'=>'<div class="page-title"><h2>'.Yii::t('studio','Automation Flows').'</h2><div class="title-bar">'
		// .CHtml::link(Yii::t('app','Clear Filters'),array('viewChangelog','clearFilters'=>1))
		.'{summary}</div></div>{items}{pager}',
	'summaryText'=>Yii::t('app','<b>{start}&ndash;{end}</b> of <b>{count}</b>'),
    'dataProvider'=>CActiveRecord::model('X2Flow')->search(),
    // 'filter'=>$model,
    // 'afterAjaxUpdate'=>'refreshQtipHistory',
	'columns'=>array(
		array(
			'name'=>'name',
			'headerHtmlOptions'=>array('style'=>'width:20%'),
			'value'=>'CHtml::link($data->name,array("/studio/flowDesigner/".$data->id))',
			'type'=>'raw',
		),
		array(
			'name'=>'active',
			'headerHtmlOptions'=>array('style'=>'width:8%'),
			'value'=>'$data->active? Yii::t("app","Yes") : Yii::t("app","No")',
			'type'=>'raw',
		),
		array(
			'name'=>'triggerType',
			'headerHtmlOptions'=>array('style'=>'width:20%'),
			'value'=>'$data->triggerType',
			'type'=>'raw',
		),
		array(
			'name'=>'modelClass',
			'headerHtmlOptions'=>array('style'=>'width:10%'),
		),
		// 'flow',
		array(
			'name'=>'createDate',
			'header'=>Yii::t('admin','Create Date'),
			'value'=>'Formatter::formatDateTime($data->createDate)',
			'type'=>'raw',
			// 'htmlOptions'=>array('width'=>'20%'),
		),
		array(
			'name'=>'lastUpdated',
			'header'=>Yii::t('admin','Last Updated'),
			'value'=>'Formatter::formatDateTime($data->lastUpdated)',
			'type'=>'raw',
			// 'htmlOptions'=>array('width'=>'20%'),
		),
	),
));
?><br>
<?php
if(Yii::app()->params->edition==='pro')
	echo CHtml::link('Create New Flow',array('/studio/flowDesigner'),array('class'=>'x2-button'));
?>