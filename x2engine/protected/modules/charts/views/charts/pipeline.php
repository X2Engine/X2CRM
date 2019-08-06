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




Yii::app()->clientScript->registerScriptFile($this->module->getAssetsUrl ().'/js/chartManager.js',
    CClientScript::POS_BEGIN);

require_once("protected/modules/charts/chartsConfig.php");

$menuOptions = array(
    'leadVolume', 'marketing', 'pipeline', 'opportunities',
);
$this->insertMenu($menuOptions);

?>


<div class="page-title icon charts"><h2><?php echo Yii::t('charts', 'Pipeline Dashboard'); ?>&nbsp;&nbsp;</h2></div>
<div class="form">
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
		"dealstatus = 'Working'",
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
				<?php 
                echo $form->label(
                    $model, 'dateRange', 
                    array('label' => Yii::t('charts', 'Select deals closing in').
                        '&nbsp;&nbsp;&nbsp;&nbsp;')); 
				echo $form->dropDownList($model, 'dateRange', array(
					5 => Yii::t('charts', '{n} days',array('{n}'=>'5')),
					10 => Yii::t('charts', '{n} days',array('{n}'=>'10')),
					15 => Yii::t('charts', '{n} days',array('{n}'=>'15')),
					20 => Yii::t('charts', '{n} days',array('{n}'=>'20')),
					30 => Yii::t('charts', '{n} days',array('{n}'=>'30')),
					60 => Yii::t('charts', '{n} days',array('{n}'=>'60')),
					90 => Yii::t('charts', '{n} days',array('{n}'=>'90'))
                ));
				?>
				<a onclick="x2.forms.submitForm('chart');" href="#" class="x2-button"><span><?php echo Yii::t('app','Go'); ?></span></a>
			</div>
		</div>
	</div>
	<div class="x2-chart-container-controls">
		<div class="x2-chart-control">
			<div class="row">
				<?php echo $form->label($model, 'confidence', array('label' => Yii::t('charts', 'Limit to confidence is').'&nbsp;&nbsp;&nbsp;&nbsp;')); ?>
				<?php
				echo $form->dropDownList($model, 'confidence', array(
					-1 => Yii::t('charts', 'Any'),
					0 => Yii::t('charts', 'None'),
					1 => Yii::t('charts', 'Low'),
					2 => Yii::t('charts', 'Growing'),
					3 => Yii::t('charts', 'Forecast'),
					4 => Yii::t('charts', 'Committed'),
					5 => Yii::t('charts', 'In The Bag')
						)
				)
				?>
				<a onclick="x2.forms.submitForm('chart');" href="#" class="x2-button"><span><?php echo Yii::t('app','Go'); ?></span></a>
			</div>
		</div>
	</div>
	<div class="x2-chart-container-controls">
		<div class="x2-chart-control">
			<div class="row">
				<?php echo $form->label($model, 'limitTo', array('label' => Yii::t('charts', 'Limit selected deals to').'&nbsp;&nbsp;&nbsp;&nbsp;')); ?>
				<?php
				echo $form->dropDownList($model, 'limitTo', array(
					1 => Yii::t('charts', 'Smallest'),
					3 => Yii::t('charts', 'Largest'),
					2 => Yii::t('charts', 'Others'),
					0 => Yii::t('charts', 'All')
						)
				)
				?>
				<a onclick="x2.forms.submitForm('chart');" href="#" class="x2-button"><span><?php echo Yii::t('app','Go'); ?></span></a>
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
				<a onclick="x2.forms.submitForm('chart');" href="#" class="x2-button"><span><?php echo Yii::t('app','Go'); ?></span></a>
			</div>
		</div>
	</div>
	<p class="x2-chart-separator"/>
	<div class="x2-chart-container-center-large">
		<?php
		$this->widget('X2BubbleChart', array(
			'model' => $sqlView,
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
				'title' => Yii::t('charts', 'Deals'),
				'axes' => array(
					'yaxis' => array(
						'label'=>Yii::t('charts','Value'),
						'tickOptions' => array(
							'formatString'=>'$%d'
						)
					),
					'xaxis' => array(
						'label'=>Yii::t('charts','Days To Close'),
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

