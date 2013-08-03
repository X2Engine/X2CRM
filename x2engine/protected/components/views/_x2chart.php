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

Yii::app()->clientScript->registerScriptFile(
	Yii::app()->getBaseUrl().'/js/x2chart.js', CClientScript::POS_END);
Yii::app()->clientScript->registerCssFile(
	Yii::app()->getTheme()->getBaseUrl().'/css/x2chart.css');

Yii::app()->clientScript->registerScriptFile(
	Yii::app()->request->baseUrl . '/js/jqplot/jquery.jqplot.js');
Yii::app()->clientScript->registerCssFile(
	Yii::app()->request->baseUrl . '/js/jqplot/jquery.jqplot.css');
Yii::app()->clientScript->registerScriptFile(
	Yii::app()->request->baseUrl . '/js/jqplot/plugins/jqplot.categoryAxisRenderer.js');
Yii::app()->clientScript->registerScriptFile(
	Yii::app()->request->baseUrl . '/js/jqplot/plugins/jqplot.pointLabels.js');
Yii::app()->clientScript->registerScriptFile(
	Yii::app()->request->baseUrl . '/js/jqplot/plugins/jqplot.dateAxisRenderer.js');
Yii::app()->clientScript->registerScriptFile(
	Yii::app()->request->baseUrl . '/js/jqplot/plugins/jqplot.highlighter.js');
Yii::app()->clientScript->registerScriptFile(
	Yii::app()->request->baseUrl . '/js/jqplot/plugins/jqplot.enhancedLegendRenderer.js');
Yii::app()->clientScript->registerCoreScript('cookie');

if ($chartType === 'multiLine') {
	Yii::app()->clientScript->registerScriptFile(
		Yii::app()->request->baseUrl . '/js/checklistDropdown/jquery.multiselect.js');
	Yii::app()->clientScript->registerCssFile(
		Yii::app()->request->baseUrl . '/js/checklistDropdown/jquery.multiselect.css');
}

$passVarsToClientScript = "
	x2.chart = {};
	x2.chart.chartType = '".$chartType."';
	x2.chart.chartPage = '".$chartPage."';
	x2.chart.hideByDefault = ".($hideByDefault ? 'true' : 'false').";
	x2.chart.suppressChartSettings = ".($suppressChartSettings ? 'true' : 'false').";
	x2.chart.getChartDataActionName = '".$getChartDataActionName."';
	x2.chart.DEBUG = ".(YII_DEBUG ? 'true' : 'false').";
	x2.chart.translations = {};
";

if (isset ($actionsStartDate)) {
	$passVarsToClientScript .= "
		x2.chart.actionsStartDate = ".$actionsStartDate." * 1000;";
}

if (isset ($chartData)) {
	$passVarsToClientScript .= "
		x2.chart.chartData = ".CJSON::encode ($chartData).";";
}

$longMonthNames = Yii::app()->getLocale ()->getMonthNames ();
$shortMonthNames = Yii::app()->getLocale ()->getMonthNames ('abbreviated');

$translations = array (
	'Create' => Yii::t('app','Create'),
	'Cancel' => Yii::t('app','Cancel'),
	'Create Chart Setting' => Yii::t('app','Create Chart Setting'),
	'Check all' => Yii::t('app','Check all'),
	'Uncheck all' => Yii::t('app','Uncheck all'),
	'metric(s) selected' => Yii::t('app','metric(s) selected')
);

$englishMonthNames =
	array ('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August',
	'September', 'October', 'November', 'December');
$englishMonthAbbrs =
	array ('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov',
	'Dec');

foreach ($longMonthNames as $key=>$val) {
	$translations[$englishMonthNames[$key - 1]] = $val;
}
foreach ($shortMonthNames as $key=>$val) {
	$translations[$englishMonthAbbrs[$key - 1]] = $val;
}

// pass array of predefined theme uploadedBy attributes to client
foreach ($translations as $key=>$val) {
	$passVarsToClientScript .= "x2.chart.translations['".
		$key. "'] = '" . $val . "';\n";
}

if (!$suppressChartSettings) {
	$passVarsToClientScript .= "x2.chart.chartSettings = {};\n";

	foreach ($chartSettingsDataProvider->data as $chartSetting) {
		$passVarsToClientScript .= 
			"x2.chart.chartSettings['" . $chartSetting->name . "'] = " .
			CJSON::encode ($chartSetting) . ";\n";
	}
}

$passVarsToClientScript .= "x2.chart.actionParams = {};\n";

foreach ($actionParams as $paramName => $paramVal) {
	$passVarsToClientScript .= 
		"x2.chart.actionParams['" . $paramName . "'] = " .
		CJSON::encode ($paramVal) . ";\n";
}

Yii::app()->clientScript->registerScript(
	'passVarsToX2chartScript', $passVarsToClientScript,
	CClientScript::POS_HEAD);

?>

<div id="chart-container" class='form' 
 <?php echo $hideByDefault ? "style='display: none;'" : ""; ?>>

	<div class="row top-button-row">
		<select id="first-metric" 
		 <?php echo $chartType === 'multiLine' ? 'multiple="multiple"' : ''; ?>>

		<?php
		foreach ($metricTypes as $key=>$type) { 
		?>
			<option value='<?php echo $key; ?>'>
			<?php echo $type; ?>
			</option>
		<?php 
		} 
		?>
		</select>

		<?php 
		if ($chartType === 'twoLine') {
			echo Yii::t('app', 'vs.'); 
		?>
			<select id="second-metric">
				<option value="" id="second-metric-default">
					<?php echo Yii::t('app', '- Select an event type -'); ?>
				</option>
			<?php
			foreach ($metricTypes as $key=>$type) { 
			?>
				<option value='<?php echo $key; ?>'>
				<?php echo $type; ?>
				</option>
			<?php 
			} 
			?>
		</select>
		<a href="#" class='x2-hint' id="clear-metric-button" style='display: none;'
		 title='<?php echo Yii::t('app', 'Clear second metric'); ?>'>
			[x]
		</a>
		<?php 
		}
		?>

		<div id='bin-size-button-set' class="x2-button-group right">
			<a href="#" id='hour-bin-size' class='x2-button '>
				<?php echo Yii::t('app', 'Per Hour'); ?>
			</a>
			<a href="#" id='day-bin-size' class='disabled-link x2-button'>
				<?php echo Yii::t('app', 'Per Day'); ?>
			</a>
			<a href="#" id='week-bin-size' class='x2-button '>
				<?php echo Yii::t('app', 'Per Week'); ?>
			</a>
			<a href="#" id='month-bin-size' class='x2-button '>
				<?php echo Yii::t('app', 'Per Month'); ?>
			</a>
		</div>
	</div>

	<div class="row datepicker-row">
		<div class="left">
		<input id="chart-datepicker-from">
		</input>
		-
		<input id="chart-datepicker-to">
		</input>
		</div>


		<?php
		if (!$suppressChartSettings) {
		?>

		<button id='create-setting-button' class='right x2-button x2-small-button'>
			<?php echo Yii::t ('app', 'Create Chart Setting'); ?>
		</button>
		<a href="#" id="delete-setting-button" class='right x2-hint'
		 style='display: none;'
		 title='<?php echo Yii::t('app', 'Delete predefined chart setting'); ?>'>
			[x]
		</a>
		<select id="predefined-settings" class='right'>
			<option value="" id="custom-settings-option">
				<?php echo Yii::t('app', 'Custom'); ?>
			</option>
			<?php foreach ($chartSettingsDataProvider->data as $chartSetting) { ?>
			<option value="<?php echo $chartSetting->name; ?>">
				<?php echo $chartSetting->name; ?>
			</option>
			<?php } ?>
		</select>

		<?php
		}
		?>
	</div>


	<div id="chart" class='jqplot-target'>
	</div>

	<table id='chart-legend'>
		<tbody>
		</tbody>
	</table>

	<div id='chart-tooltip' style='display: none;'>
	</div>


</div>

<?php
if (!$suppressChartSettings) {
?>

<div id="create-chart-setting-dialog">
	<div class='chart-setting-name-input-container'>
		<span class='left'> <?php echo Yii::t('app', 'Setting Name'); ?>: </span>
		<input id="chart-setting-name"> </input>
	</div>
	<br/>
</div>

<?php
}
?>

