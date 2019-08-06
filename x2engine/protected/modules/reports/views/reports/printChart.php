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





$this->layout = '//layouts/print';

Yii::app()->clientScript->registerCssFile(
    Yii::app()->controller->module->assetsUrl.'/css/printChart.css');

// default params
if(isset($title)){
	$title = '';
}

if(empty($charts)) {
	$charts = false;
}

if(empty($report)) {
	$report = false;
} else {
	$report->changeSetting('print', true);

	if(empty($title)) {
		$title = $report->name;
	}
}


?>

<div class='config-panel-content'>
	<div class="row">
		<span class='label'><?php echo Yii::t('charts','Title:') ?></span>
		<input type='text' name='title' id='title' value="<?php echo $title ?>" />
	</div>

	<!-- Charts Config -->
	<?php if($charts): ?>
			<h4><?php echo Yii::t('charts','Charts Options') ?></h4>
	    <div class="row">
	        <span class='label'><?php echo Yii::t('charts','Chart Height:') ?></span>
	        <input type='number' name='height' id='height' value="300" />
	    </div>
	    <div class="row">
	        <span class='label'><?php echo Yii::t('charts','Show Values:') ?></span>
	        <input type='checkbox' id='show-values' name='show-values' checked />
	    </div>
	    <?php foreach ($charts as $id => $chart):  ?>
		    <div class="row">
		        <span class='label'><?php echo $chart['title'] ?></span>
		        <input type='checkbox' class='show-chart' name='show-chart'
		        data-chart='<?php echo $id ?>' checked />
		    </div>
	    <?php endforeach; ?>
	<?php endif; ?>

	<!-- Reports Config -->
	<?php if ($report): ?>
		<?php echo "<h4>".Yii::t('charts','Report Options')."</h4>"; ?>

		<?php if ($charts): ?>
		<div class="row">
			<span class='label'><?php echo Yii::t('charts','Show Report:') ?></span>
			<input type='checkbox' name='show-report' id='show-report' checked />
		</div>
		<?php endif; ?>

		<div class="row">
			<span class='label'><?php echo Yii::t('charts','Report Rows:') ?></span>
			<input type='number' name='rows' id='rows' value="" />
		</div>
	<?php endif ?>

</div>

<h1 id='report-title'><?php echo $title ?></h1>
<!-- Charts Layout -->
<?php if($charts): ?>
<div class="charts">
	<?php foreach($charts as $index => $array)  {
		echo "<div class='chart'>";
		echo "<h2>$array[title]</h2>";
		echo "<div id='chart-$index'></div>";
		echo "</div>";
	} ?>
</div>
<?php endif; ?>

<!-- Reports Layout -->
<?php if($report): ?>
<div class='report-container'>
	<?php $report->instance->generate() ?>
</div>
<?php endif; ?>

<script type="text/javascript">	
	$('title').html('<?php echo $title?>');

	// Changes the title of this page
	$('#title').change(function() {
		if ($(this).val()) {
			$('#report-title').show().html($(this).val());
			$('title').html($(this).val());
		} else {
			$('#report-title').hide();
		}
	}).change();

	/*********************************
	* Charts Javascript
	********************************/
	<?php if($charts): ?>
	var charts = <?php echo CJSON::encode($charts) ?>;
	var chartArray = [];
	for(var c in charts) {
		var settings = JSON.parse(charts[c].settings);
		settings.bindto = "#chart-" + c;

		// Set padding
		settings.padding = {top: 50};

		// Set chart size larger if theres only one
		if(charts.length == 1) {
			settings.size = {height: 500};
		}

		// Fix for default gauge label formatting
		if (typeof settings.gauge !== 'undefined') {
			settings.gauge.label.format = function(value, ratio) {
				return value;
			}
		}

		chartArray.push(c3.generate(settings));
	}

	// Change the height of charts
	// Set default height to what it is currenly at
	$('#height').change(function() {
		for(var c in chartArray) {
			chartArray[c].resize({height: $(this).val()});
		}
	}).val($('.chart').first().height());	


	// Toggle Charts
    $('.show-chart').change(function() {
        var id = '#chart-' + $(this).data('chart');
		$(id).closest('.chart').toggle();
	});


	// Toggle Data values
	$('#show-values').change(function() {
		$('.c3-chart-texts').toggle();
	});
	<?php endif; ?>

	/*********************************
	* Reports Javascript
	********************************/
	<?php if ($report): ?>
	// Change number of report rows showing
	$('#rows').change(function() {
	    var $table = $('.report-container');
	    var $rows = $table.find('tbody tr');

	    var min = 0;
	    var max = $(this).val();

	    min = min ? min - 1 : 0;
	    max = max ? max : $rows.length;
	    $rows.hide().slice(min, max).show(); 
	});

	// Toggle Report
	$('#show-report').change(function() {
		$('.report-container').toggle();
	});
	<?php endif; ?>

	$("th").click(function () {    var html = $(this).html();
	    var input = $('<div contenteditable>'+html+'</div>');
     	    input.val(html);
 	    $(this).html(input); $(this).off()});

	$("td").click(function () {    var html = $(this).html();
	    var input = $('<div contenteditable>'+html+'</div>');
 	    input.val(html);
            $(this).html(input); $(this).off()});
</script>
