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
	array('label' => Yii::t('dashboard', 'Marketing'), 'url' => array('marketing')),
	array('label' => Yii::t('dashboard', 'Pipeline')),
	array('label' => Yii::t('dashboard', 'Opportunities'), 'url' => array('sales')),
));
?>


<div class="form">
	<h1><?php echo Yii::t('app', 'Pipeline Dashboard'); ?>&nbsp;&nbsp;</h1>
	<br>
	<br>

	<?php
	$form = $this->beginWidget('CActiveForm', array('id' => 'chart'));
	$range = $model->dateRange;
	$assignedTo = $model->assignedTo;
	$confidence = $model->confidence;
	$userName = Yii::app()->user->getName();
	$slice = $model->limitTo;
	$filters = array(
		"closeDate > unix_timestamp()",
		"closeDate < (unix_timestamp() + ($range*24*3600))",
		"dealstatus = 'Pending'",
		"((visibility = 0 AND assignedTo='$userName') OR (visibility = 1
		) OR (visibility = 2 and assignedTo='$userName'))"
	);
	if (strcmp($assignedTo,'0') != 0)
		$filters[] = "(assignedTo = '$assignedTo')";
	if ($confidence >= 0)
		$filters[] = "(confidence = $confidence)";
	?>
	<div class="x2-chart-container-controls">
		<div class="x2-chart-control">
			<div class="row">
				<?php echo $form->label($model, 'dateRange', array('label' => Yii::t('dashboard', 'Select deals closing in&nbsp;&nbsp;&nbsp;&nbsp;'))); ?>
				<?php
				echo $form->radioButtonList($model, 'dateRange', array(
					5 => Yii::t('dashboard', '5 days'),
					10 => Yii::t('dashboard', '10 days'),
					15 => Yii::t('dashboard', '15 days'),
					20 => Yii::t('dashboard', '20 days'),
					30 => Yii::t('dashboard', '30 days'),
					60 => Yii::t('dashboard', '60 days'),
					90 => Yii::t('dashboard', '90 days')
						), array(
					'separator' => '&nbsp;&nbsp;|&nbsp;&nbsp;'
						)
				)
				?>
				<a onclick="submitForm('chart');" href="#" class="x2-button"><span>Go</span></a>
			</div>
		</div>
	</div>
	<div class="x2-chart-container-controls">
		<div class="x2-chart-control">
			<div class="row">
				<?php echo $form->label($model, 'confidence', array('label' => Yii::t('dashboard', 'Limit to confidence is&nbsp;&nbsp;&nbsp;&nbsp;'))); ?>
				<?php
				echo $form->radioButtonList($model, 'confidence', array(
					-1 => Yii::t('dashboard', 'Any'),
					0 => Yii::t('dashboard', 'None'),
					1 => Yii::t('dashboard', 'Low'),
					2 => Yii::t('dashboard', 'Growing'),
					3 => Yii::t('dashboard', 'Forecast'),
					4 => Yii::t('dashboard', 'Committed'),
					5 => Yii::t('dashboard', 'In The Bag')
						), array(
					'separator' => '&nbsp;&nbsp;|&nbsp;&nbsp;'
						)
				)
				?>
				<a onclick="submitForm('chart');" href="#" class="x2-button"><span>Go</span></a>
			</div>
		</div>
	</div>
	<div class="x2-chart-container-controls">
		<div class="x2-chart-control">
			<div class="row">
				<?php echo $form->label($model, 'limitTo', array('label' => Yii::t('dashboard', 'Limit selected deals to&nbsp;&nbsp;&nbsp;&nbsp;'))); ?>
				<?php
				echo $form->radioButtonList($model, 'limitTo', array(
					1 => Yii::t('dashboard', 'Smallest'),
					3 => Yii::t('dashboard', 'Largest'),
					2 => Yii::t('dashboard', 'Others'),
					0 => Yii::t('dashboard', 'All')
						), array(
					'separator' => '&nbsp;&nbsp;|&nbsp;&nbsp;'
						)
				)
				?>
				<a onclick="submitForm('chart');" href="#" class="x2-button"><span>Go</span></a>
			</div>
		</div>
	</div>
	<div class="x2-chart-container-controls">
		<div class="x2-chart-control">
			<div class="row">
				<?php echo $form->label($model, 'assignedTo', array('label' => Yii::t('dashboard', 'Select deals assigned to&nbsp;&nbsp;&nbsp;&nbsp;'))); ?>
				<?php
				echo $form->dropDownList($model, 'assignedTo', array_merge(array('0' => 'All'), Groups::getNames(), User::getNames()));
				?>
				<a onclick="submitForm('chart');" href="#" class="x2-button"><span>Go</span></a>
			</div>
		</div>
	</div>
	<p class="x2-chart-separator"/>
	<div class="x2-chart-container-center-large">
		<?php
		$this->widget('X2BubbleChart', array(
			'model' => 'x2_bi_leads',
			'options' => array(
				'other-threshold' => 2,
				'statistic' => 'none',
				'orderby' => 'dealValue asc',
				'slice' => array('part' => $slice),
				'x-axis' => array('column' => "round((closeDate-unix_timestamp())/(24*3600))"),
				'y-axis' => array('column' => 'round(dealValue)'),
				'r-axis' => array(
					'column' => 'confidence',
					'label' => "concat('$',format(dealValue,2))")
			),
			'filters' => $filters,
			'chartOptions' => array(
				'title' => Yii::t('dashboard', 'Deals'),
				'axes' => array(
					'yaxis' => array(
						'label'=>Yii::t('dashboard','Value'),
						'tickOptions' => array(
							'formatString'=>'$%d'
						)
					),
					'xaxis' => array(
						'label'=>Yii::t('dashboard','Days To Close'),
					)
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

