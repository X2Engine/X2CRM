<?php
/* * *******************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this
 *   list of conditions and the following disclaimer in the documentation and/or
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be
 *   used to endorse or promote products derived from this software without
 *   specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 * ****************************************************************************** */
?>
<?php
$this->actionMenu = $this->formatMenu(array(
	array('label' => Yii::t('dashboard', 'Lead Volume')),
	// array('label' => Yii::t('dashboard', 'Lead Activity'), 'url' => array('leadActivity')),
	// array('label' => Yii::t('dashboard', 'Lead Performance'), 'url' => array('leadPerformance')),
	// array('label' => Yii::t('dashboard', 'Lead Sources'), 'url' => array('leadSources')),
	// array('label' => Yii::t('dashboard', 'Workflow'), 'url' => array('workflow')),
	array('label' => Yii::t('dashboard', 'Marketing'), 'url' => array('marketing')),
	array('label' => Yii::t('dashboard', 'Pipeline'), 'url' => array('pipeline')),
	array('label' => Yii::t('dashboard', 'Opportunities'), 'url' => array('sales')),
));
Yii::app()->clientScript->registerScript('leadVolume',"
	$('#startDate,#endDate').change(function() {
		$('#dateRange').val('custom');
	});
",CClientScript::POS_READY);
?>
<div class="form">
	<h2><?php echo Yii::t('dashboard', 'Lead Volume'); ?></h2>

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
			<?php echo CHtml::label(Yii::t('dashboard', 'Start Date'),'startDate'); ?>
			<?php
			Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
			
			$this->widget('CJuiDateTimePicker',array(
				'name'=>'start',
				// 'value'=>$startDate,
				'value'=>$this->formatDate($dateRange['start']),
				// 'title'=>Yii::t('app','Start Date'),
				// 'model'=>$model, //Model object
				// 'attribute'=>$field->fieldName, //attribute name
				'mode'=>'date', //use "time","date" or "datetime" (default)
				'options'=>array(
					'dateFormat'=>$this->formatDatePicker(),

				), // jquery plugin options
				'htmlOptions'=>array('id'=>'startDate','width'=>20),
				'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
			));
			?>
		</div>
		<div class="cell">
			<?php echo CHtml::label(Yii::t('dashboard', 'End Date'),'startDate'); ?>
			<?php
			$this->widget('CJuiDateTimePicker',array(
				'name'=>'end',
				'value'=>$this->formatDate($dateRange['end']),
				// 'value'=>$endDate,
				'mode'=>'date', //use "time","date" or "datetime" (default)
				'options'=>array(
					'dateFormat'=>$this->formatDatePicker(),
				),
				'htmlOptions'=>array('id'=>'endDate','width'=>20),
				'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
			));
			?>
		</div>
		<div class="cell">
			<?php echo CHtml::label(Yii::t('dashboard', 'Date Range'),'range'); ?>
			<?php
			echo CHtml::dropDownList('range',$dateRange['range'],array(
				'custom'=>Yii::t('dashboard','Custom'),
				'thisWeek'=>Yii::t('dashboard','This Week'),
				'thisMonth'=>Yii::t('dashboard','This Month'),
				'lastWeek'=>Yii::t('dashboard','Last Week'),
				'lastMonth'=>Yii::t('dashboard','Last Month'),
				// 'lastQuarter'=>Yii::t('dashboard','Last Quarter'),
				'thisYear'=>Yii::t('dashboard','This Year'),
				'lastYear'=>Yii::t('dashboard','Last Year'),
				
			),array('id'=>'dateRange'));
			?>
		</div>
		<div class="cell">
			<?php
			echo CHtml::submitButton(Yii::t('dashboard','Go'),array('class'=>'x2-button','style'=>'margin-top:13px;'));

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
			// 'template'=> '<h2>'.Yii::t('dashboard','Lead Activity').'</h2><div class="title-bar">'
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








