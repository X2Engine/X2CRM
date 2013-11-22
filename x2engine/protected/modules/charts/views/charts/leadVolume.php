<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/
?>
<?php
$this->actionMenu = $this->formatMenu(array(
	array('label' => Yii::t('charts', 'Lead Volume')),
	// array('label' => Yii::t('charts', 'Lead Activity'), 'url' => array('leadActivity')),
	// array('label' => Yii::t('charts', 'Lead Performance'), 'url' => array('leadPerformance')),
	// array('label' => Yii::t('charts', 'Lead Sources'), 'url' => array('leadSources')),
	// array('label' => Yii::t('charts', 'Workflow'), 'url' => array('workflow')),
	array('label' => Yii::t('charts', 'Marketing'), 'url' => array('marketing')),
	array('label' => Yii::t('charts', 'Pipeline'), 'url' => array('pipeline')),
	array('label' => Yii::t('charts', 'Opportunities'), 'url' => array('sales')),
));
Yii::app()->clientScript->registerScript('leadVolume',"
	$('#startDate,#endDate').change(function() {
		$('#dateRange').val('custom');
	});
",CClientScript::POS_READY);
?>
<div class="page-title icon charts"><h2><?php echo Yii::t('charts', 'Lead Volume'); ?></h2></div>

<div class="form">

	<?php
	$form = $this->beginWidget('CActiveForm', array(
		'action'=>$this->createUrl('leadVolume'),
		'id'=>'dateRangeForm',
		'enableAjaxValidation'=>false,
		'method'=>'get'
	));

	// $range = 30; //$model->dateRange;
	
	// echo $startDate .' '.$endDate;
	$userName = Yii::app()->user->getName();
	$filters = array(
		'createDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end']
	);
	?>
	<div class="row">
		<div class="cell">
			<?php echo CHtml::label(Yii::t('charts', 'Start Date'),'startDate'); ?>
			<?php
			Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
			
			$this->widget('CJuiDateTimePicker',array(
				'name'=>'start',
				// 'value'=>$startDate,
				'value'=>Formatter::formatDate($dateRange['start']),
				// 'title'=>Yii::t('app','Start Date'),
				// 'model'=>$model, //Model object
				// 'attribute'=>$field->fieldName, //attribute name
				'mode'=>'date', //use "time","date" or "datetime" (default)
				'options'=>array(
					'dateFormat'=>Formatter::formatDatePicker(),

				), // jquery plugin options
				'htmlOptions'=>array('id'=>'startDate','width'=>20),
				'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
			));
			?>
		</div>
		<div class="cell">
			<?php echo CHtml::label(Yii::t('charts', 'End Date'),'startDate'); ?>
			<?php
			$this->widget('CJuiDateTimePicker',array(
				'name'=>'end',
				'value'=>Formatter::formatDate($dateRange['end']),
				// 'value'=>$endDate,
				'mode'=>'date', //use "time","date" or "datetime" (default)
				'options'=>array(
					'dateFormat'=>Formatter::formatDatePicker(),
				),
				'htmlOptions'=>array('id'=>'endDate','width'=>20),
				'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
			));
			?>
		</div>
		<div class="cell">
			<?php echo CHtml::label(Yii::t('charts', 'Date Range'),'range'); ?>
			<?php
			echo CHtml::dropDownList('range',$dateRange['range'],array(
				'custom'=>Yii::t('charts','Custom'),
				'thisWeek'=>Yii::t('charts','This Week'),
				'thisMonth'=>Yii::t('charts','This Month'),
				'lastWeek'=>Yii::t('charts','Last Week'),
				'lastMonth'=>Yii::t('charts','Last Month'),
				// 'lastQuarter'=>Yii::t('charts','Last Quarter'),
				'thisYear'=>Yii::t('charts','This Year'),
				'lastYear'=>Yii::t('charts','Last Year'),
				
			),array('id'=>'dateRange'));
			?>
		</div>
		<div class="cell">
			<?php
			echo CHtml::submitButton(Yii::t('charts','Go'),array('class'=>'x2-button','style'=>'margin-top:13px;'));

			?>
		</div>
	</div>
	<?php
	$form = $this->endWidget();
	
	if(isset($dataProvider)) {
	
		$this->widget('zii.widgets.grid.CGridView', array(
			'id'=>'lead-activity-grid',
			'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
			'template'=> '{items}{pager}',
			// 'template'=> '<h2>'.Yii::t('charts','Lead Activity').'</h2><div class="title-bar">'
				// .CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
				// .CHtml::link(Yii::t('app','Clear Filters'),array(Yii::app()->controller->action->id,'clearFilters'=>1)) . ' | '
				// .CHtml::link(Yii::t('app','Columns'),'javascript:void(0);',array('class'=>'column-selector-link'))
				// .'{summary}</div>{items}{pager}',
			'dataProvider'=>$dataProvider,
			// 'enableSorting'=>false,
			// 'model'=>$model,
			//'filter'=>$model,
			// 'columns'=>$columns,
			//'modelName'=>'Contacts',
			// 'viewName'=>'leadactcontacts',
			'columns'=>array(
				'user'=>array(
					'name'=>'name',
					'header'=>Yii::t('contacts','User'),
					// 'value'=>'empty($data["id"])? $data["name"] : CHtml::link($data["name"],array("/users/".$data["id"]))',
					'type'=>'raw',
					'headerHtmlOptions'=>array('style'=>'width:40%')
				),
				'leads'=>array(
					'name'=>'count',
					'header'=>Yii::t('contacts','Leads'),
				)
			),
		));
	}
	?>
</div>








