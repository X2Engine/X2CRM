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
$this->menu = array(
	array('label' => Yii::t('dashboard', 'Lead Volume')),
	// array('label' => Yii::t('dashboard', 'Lead Activity'), 'url' => array('leadActivity')),
	// array('label' => Yii::t('dashboard', 'Lead Performance'), 'url' => array('leadPerformance')),
	array('label' => Yii::t('dashboard', 'Marketing'), 'url' => array('marketing')),
	// array('label' => Yii::t('dashboard', 'Pipeline'), 'url' => array('pipeline')),
	// array('label' => Yii::t('dashboard', 'Sales'), 'url' => array('sales')),
);
Yii::app()->clientScript->registerScript('leadVolume',"
	$('#startDate,#endDate').change(function() {
		$('#dateRange').val('custom');
	});

	$('#lead-volume-chart').bind('jqplotDataHighlight', function (e, seriesIndex, pointIndex, data) {
			$('#pie-tooltip').html('<div style=\"font-size:14px;font-weight:bold;padding:5px;color:#000;background:white;background:rgba(255,255,255);\">' + data[0] + '</span>');
			$('#pie-tooltip').show();
			
			var chart_left = $('#lead-volume-chart').position().left;
				var chart_top = $('#lead-volume-chart').position().top;
		
			$(document).bind('mousemove.pieChart',function(e) {
				x = e.pageX; //plot.axes.xaxis.u2p(data[0]),  // convert x axis unita to pixels
				y = e.pageY; //plot.axes.yaxis.u2p(data[1]);  // convert y axis units to pixels
				$('#pie-tooltip').css({left:x-chart_left+5, top:y-50});
			});
		});

		// Bind a function to the unhighlight event to clean up after highlighting.
		$('#lead-volume-chart').bind('jqplotDataUnhighlight', 
		function (e, seriesIndex, pointIndex, data) {
			$(document).unbind('mousemove.pieChart');
			$('#pie-tooltip').empty();
			$('#pie-tooltip').hide();
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
					'changeMonth'=>true,
					'changeYear'=>true,

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
					'changeMonth'=>true,
					'changeYear'=>true,
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
	<div id="pie-tooltip" style="position:absolute;z-index:5;"></div>
	<div style="height:650px">
		<?php
		$this->widget('X2PieChart', array(
			'model' => 'x2_bi_leads',
			'options' => array(
				'other-threshold' => 1,
				'x-axis' => array('column' => 'assignedTo')
			),
			'htmlOptions'=>array(
				'id'=>'lead-volume-chart',
			),
			'filters' => $filters,
			'chartOptions' => array(
				'seriesDefaults'=>array(
					'rendererOptions'=>array(
						'dataLabels' => 'value',
						'dataLabelPositionFactor' => 0.7
					),
				),
				// 'title' => Yii::t('dashboard', 'Lead Volume'),
				'legend' => array('show' => true, 'location' => 'ne', 'placement' => 'insideGrid'),
				'highlighter'=>array(
					'show'=>true,
					'showTooltip'=>true,
					'formatString'=>'%s', 
					'sizeAdjust'=>'0.5', 
					// 'tooltipAxes'=>'x', 
					// 'tooltipLocation'=>'sw', 
					'useAxesFormatters'=>false
				)
			)
		));
		?>
	</div>
	<?php
	$form = $this->endWidget();
	?>
</div>








