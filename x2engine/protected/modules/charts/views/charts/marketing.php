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
	array('label' => Yii::t('dashboard', 'Lead Volume'), 'url' => array('leadVolume')),
	// array('label' => Yii::t('dashboard', 'Lead Activity'), 'url' => array('leadActivity')),
	// array('label' => Yii::t('dashboard', 'Lead Performance'), 'url' => array('leadPerformance')),
	// array('label' => Yii::t('dashboard', 'Lead Sources'), 'url' => array('leadSources')),
	// array('label' => Yii::t('dashboard', 'Workflow'), 'url' => array('workflow')),
	array('label' => Yii::t('dashboard', 'Marketing')),
	array('label' => Yii::t('dashboard', 'Pipeline'), 'url' => array('pipeline')),
	array('label' => Yii::t('dashboard', 'Opportunities'), 'url' => array('sales'))
));
?>


<div class="form">
	<h1><?php echo Yii::t('app', 'Marketing Dashboard'); ?>&nbsp;&nbsp;</h1>
	<br>

	<?php
	$form = $this->beginWidget('CActiveForm', array('id' => 'chart'));
	$range = $model->dateRange;
	$userName = Yii::app()->user->getName();
	$filters = array(
		"leadDate > (unix_timestamp() - ($range*24*3600))",
		"((visibility = 0 AND assignedTo='$userName') OR (visibility = 1) OR (visibility = 2 and assignedTo='$userName'))"
	);
	?>
	<div class="x2-chart-container-controls">
		<div class="x2-chart-control">
			<div class="row">
				<?php echo $form->label($model, 'dateRange', array('label' => Yii::t('dashboard', 'Select leads received in the last &nbsp;&nbsp;&nbsp;&nbsp;'))); ?>
				<?php
				echo $form->radioButtonList($model, 'dateRange', array(
					10 => Yii::t('dashboard', '10 days'),
					30 => Yii::t('dashboard', '30 days'),
					60 => Yii::t('dashboard', '60 days'),
					90 => Yii::t('dashboard', '90 days'),
					120 => Yii::t('dashboard', '120 days'),
					360 => Yii::t('dashboard', '360 days')
						), array(
					'separator' => '&nbsp;&nbsp;|&nbsp;&nbsp;'
						)
				)
				?>
				<a onclick="submitForm('chart');" href="#" class="x2-button"><span>Go</span></a>
			</div>
		</div>
	</div>
	<p class="x2-chart-separator"/>
	<div class="x2-chart-container">
		<div class="x2-chart-container-left">
			<?php
			$range = $model->dateRange;
			$chart1 = $this->widget('X2PieChart', array(
				'model' => 'x2_bi_leads',
				'options' => array(
					'other-threshold' => 3,
					'x-axis' => array('column' => 'leadSource')),
				'filters' => $filters,
				'chartOptions' => array(
					'title' => Yii::t('dashboard', 'Lead Source'),
					'legend' => array('show' => true, 'location' => 'ne', 'placement' => 'insideGrid')
				)
					));
			?>
		</div>
		<div class="x2-chart-container-right">
			<?php
			$this->widget('X2PieChart', array(
				'model' => 'x2_bi_leads',
				'options' => array(
					'other-threshold' => 1,
					'x-axis' => array('column' => 'leadType')
				),
				'filters' => $filters,
				'chartOptions' => array(
					'title' => Yii::t('dashboard', 'Lead Type'),
					'legend' => array('show' => true, 'location' => 'ne', 'placement' => 'insideGrid')
				)
			));
			?>
		</div>
	</div>
	<p class="x2-chart-separator"/>
	<div class="x2-chart-container-center-large">
		<?php
		$this->widget('X2StackedBarChart', array(
			'model' => 'x2_bi_leads',
			'options' => array(
				'other-threshold' => 2,
				'x-axis' => array('column' => 'assignedToName'),
				'x-axis1' => array('column' => 'leadStatus')
			),
			'filters' => $filters,
			'chartOptions' => array(
				'title' => Yii::t('dashboard', 'Lead Distribution and Status'),
				'axes' => array(
					'xaxis' => array('label' => Yii::t('dashboard', 'Assigned To')),
					'yaxis' => array('label' => Yii::t('dashboard', 'Count'))
				)
			)
		));
		?>
	</div>
	<p class="x2-chart-separator"/>
	<div class="x2-chart-container-center-large">
		<?php
		$this->widget('X2StackedBarChart', array(
			'model' => 'x2_bi_leads',
			'options' => array(
				'other-threshold' => 0,
				'orderby' => 'leadDate asc',
				'x-axis' => array('column' => "from_unixtime(leadDate,'%b-%Y')"),
				'x-axis1' => array('column' => 'dealStatus')
			),
			'filters' => $filters,
			'chartOptions' => array(
				'title' => Yii::t('dashboard', 'Lead Conversion'),
				'axes' => array(
					'xaxis' => array('label' => Yii::t('dashboard', 'Lead Received')),
					'yaxis' => array('label' => Yii::t('dashboard', 'Count'))
				)
			)
		));
		?>
	</div>
	<p class="x2-chart-separator"/>
	<?php
	$form = $this->endWidget();
	?>
</div>

