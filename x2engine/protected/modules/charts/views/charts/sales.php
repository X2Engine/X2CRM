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
require_once("protected/modules/charts/chartsConfig.php");
$this->actionMenu = $this->formatMenu(array(
	array('label' => Yii::t('charts', 'Lead Volume'), 'url' => array('leadVolume')),
	// array('label' => Yii::t('charts', 'Lead Activity'), 'url' => array('leadActivity')),
	// array('label' => Yii::t('charts', 'Lead Performance'), 'url' => array('leadPerformance')),
	// array('label' => Yii::t('charts', 'Lead Sources'), 'url' => array('leadSources')),
	// array('label' => Yii::t('charts', 'Workflow'), 'url' => array('workflow')),
	array('label' => Yii::t('charts', 'Marketing'), 'url' => array('marketing')),
	array('label' => Yii::t('charts', 'Pipeline'), 'url' => array('pipeline')),
	array('label' => Yii::t('charts', 'Opportunities'))
));
?>


<div class="page-title icon charts"><h2><?php echo Yii::t('charts', 'Opportunities Dashboard'); ?>&nbsp;&nbsp;</h2></div>
<div class="form">
	<br>

	<?php
	$form = $this->beginWidget('CActiveForm', array('id' => 'chart'));
	$range = $model->dateRange;
	$assignedTo = $model->assignedTo;
	$dealStatus = $model->dealStatus;
	$userName = Yii::app()->user->getName();
	$filters = array(
		"leadDate > (unix_timestamp() - ($range*24*3600))",
		"((visibility = 0 AND assignedTo='$userName') OR (visibility = 1
		) OR (visibility = 2 and assignedTo='$userName'))"
	);
	if (strcmp($assignedTo, '0') != 0)
		$filters[] = "(assignedTo = '$assignedTo')";
	if (strcmp($dealStatus, '0') != 0)
		$filters[] = "(dealStatus = '$dealStatus')";
	?>
	<div class="x2-chart-container-controls">
		<div class="x2-chart-control">
			<div class="row">
				<?php echo $form->label($model, 'dateRange', array('label' => Yii::t('charts', 'Select leads received in the last').'&nbsp;&nbsp;&nbsp;&nbsp;')); ?>
				<?php
				echo $form->radioButtonList($model, 'dateRange', array(
					10 => Yii::t('charts', '{n} days',array('{n}'=>'10')),
					30 => Yii::t('charts', '{n} days',array('{n}'=>'30')),
					60 => Yii::t('charts', '{n} days',array('{n}'=>'60')),
					90 => Yii::t('charts', '{n} days',array('{n}'=>'90')),
					120 => Yii::t('charts', '{n} days',array('{n}'=>'120')),
					360 => Yii::t('charts', '{n} days',array('{n}'=>'360'))
						), array(
					'separator' => '&nbsp;&nbsp;|&nbsp;&nbsp;'
						)
				)
				?>
				<a onclick="submitForm('chart');" href="#" class="x2-button"><span><?php echo Yii::t('app','Go'); ?></span></a>
			</div>
		</div>
	</div>
	<div class="x2-chart-container-controls">
		<div class="x2-chart-control">
			<div class="row">
				<?php echo $form->label($model, 'dealStatus', array('label' => Yii::t('charts', 'Select deals with status').'&nbsp;&nbsp;&nbsp;&nbsp;')); ?>
				<?php
				echo $form->radioButtonList($model, 'dealStatus', array(
					'Pending' => Yii::t('charts', 'Pending'),
					'Won' => Yii::t('charts', 'Won'),
					'Lost' => Yii::t('charts', 'Lost'),
					'0' => Yii::t('charts', 'Any')
						), array(
					'separator' => '&nbsp;&nbsp;|&nbsp;&nbsp;'
						)
				)
				?>
				<a onclick="submitForm('chart');" href="#" class="x2-button"><span><?php echo Yii::t('app','Go'); ?></span></a>
			</div>
		</div>
	</div>
	<div class="x2-chart-container-controls">
		<div class="x2-chart-control">
			<div class="row">
				<?php echo $form->label($model, 'assignedTo', array('label' => Yii::t('charts', 'Select deals assigned to').'&nbsp;&nbsp;&nbsp;&nbsp;')); ?>
				<?php
				echo $form->dropDownList($model, 'assignedTo', array_merge(array('0' => 'All'), Groups::getNames(), User::getNames()));
				?>
				<a onclick="submitForm('chart');" href="#" class="x2-button"><span><?php echo Yii::t('app','Go'); ?></span></a>
			</div>
		</div>
	</div>
	<p class="x2-chart-separator"/>
	<div class="x2-chart-container-center-large">
		<?php
		$this->widget('X2StackedBarChart', array(
			'model' => $sqlView,
			'options' => array(
				'other-threshold' => 0,
				'statistic' => 'sum',
				'orderby' => 'closeDate asc',
				'x-axis' => array('column' => "from_unixtime(closeDate,'%b-%Y')"),
				'x-axis1' => array('column' => 'interest'),
				'y-axis' => array('column' => 'dealValue')
			),
			'filters' => $filters,
			'chartOptions' => array(
				'title' => Yii::t('charts', 'Deal Value'),
				'axes' => array(
					'xaxis' => array('label' => Yii::t('charts', 'Date Closing')),
					'yaxis' => array('label' => Yii::t('charts', 'Value'),
						'tickOptions' => array(
							'formatString' => '$%d'
						)
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

