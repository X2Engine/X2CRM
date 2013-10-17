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

/*
Child prototype of X2Chart
*/



function X2ActionHistoryChart (argsDict) {
	X2Chart.call (this, argsDict);	

	var thisX2Chart = this;

	this.DEBUG = argsDict['DEBUG'];
	this.dataStartDate = argsDict['dataStartDate'];

	thisX2Chart.DEBUG && console.log ('dataStartDate = ' + this.dataStartDate);

	var colors;
	// color palette used for lines of action history chart
	colors = [
		'#7EB2E6', // pale blue
		'#CEC415', // mustard
		'#BC0D2C', // pomegranate
		'#45B41D', // apple green
		'#AB074F', // dark hot pink
		//'#156A86', // dark blue
		'#1B8FB5', // dark blue
		'#3D1783', // dark purple
		//'#5A1992',// deep purple
		'#AACF7A',
		'#7BB57C', // olive green
		//'#69B10A', // dark lime green
		//'#8DEB10',
		'#C87010', // red rock
		'#1D4C8C', // dark blue-purple
		'#FFC382',
		'#FFF882',
		'#FF9CAD',
		'#BAFFA1',
		//'#CA8613', // orange brown
		//'#C6B019', // dark sand
		'#19FFF4',
		'#A4F4FC',
		'#99C9FF',
		'#FFA8CE',
		'#D099FF',
		'#E1A1FF',
	];

	this.metricOptionsColors = {}; // used to pair colors with metrics
	$('#' + this.chartType + '-first-metric').find ('option').each (function () {
		thisX2Chart.metricOptionsColors[$(this).val ()] = colors.shift ();
	});

	this.cookieTypes = [
		'startDate', 'endDate', 'dateRange', 'binSize', 'firstMetric', 'showRelationships'];

	/* 
	set up event handlers which update action history chart on action 
	creation/deletion.
	*/
	$('#' + thisX2Chart.chartType + '-chart-container #' + thisX2Chart.chartType + 
	  '-rel-chart-data-checkbox').on ('change', function () {
	  	thisX2Chart.DEBUG && console.log ('checked rel checkbox');
		if (this.checked) {
			thisX2Chart.actionParams['showRelationships'] = 'true';
			thisX2Chart.getEventsBetweenDates (true);
			$.cookie (thisX2Chart.cookiePrefix + 'showRelationships', 'true');
		} else {
			thisX2Chart.actionParams['showRelationships'] = 'false';
			thisX2Chart.getEventsBetweenDates (true);
			$.cookie (thisX2Chart.cookiePrefix + 'showRelationships', 'false');
		}
	});													   

	/*
	set up event handlers which update action history chart on action 
	creation/deletion.
	*/
	$(document).on ('chartWidgetMaximized', function () {
		thisX2Chart.DEBUG && console.log ('max');
		thisX2Chart.feedChart.replot ({ resetAxes: false });
	});
	$(document).on ('newlyPublishedAction', function () {
		thisX2Chart.DEBUG && console.log ('new action');
		thisX2Chart.getEventsBetweenDates (true); 
	});
	$(document).on ('deletedAction', function () {
		thisX2Chart.DEBUG && console.log ('deleted action');
		thisX2Chart.getEventsBetweenDates (true); 
	});


	thisX2Chart.setDefaultSettings ();

	thisX2Chart.start ();

}

X2ActionHistoryChart.prototype = auxlib.create (X2Chart.prototype);

/*
Sets initial state of chart setting ui elements
*/
X2ActionHistoryChart.prototype.setDefaultSettings = function () {
	var thisX2Chart = this;

	// start date picker default
	if (thisX2Chart.dataStartDate) { 
		// default start date is beginning of action history
		$('#' + thisX2Chart.chartType + '-chart-datepicker-from').datepicker(
			'setDate', new Date (thisX2Chart.dataStartDate));
	} else {
		$('#' + thisX2Chart.chartType + '-chart-datepicker-from').datepicker(
			'setDate', new Date ());
	}

	// end date picker default
	$('#' + thisX2Chart.chartType + '-chart-datepicker-to').
		datepicker('setDate', new Date ()); // default end date

	// metric default
	$('#' + thisX2Chart.chartType + '-first-metric').children ().each (function () {
		$(this).attr ('selected', 'selected');
	});
	$('#' + thisX2Chart.chartType + '-first-metric').multiselect2 ('refresh');

};

/*
Filter function used by groupChartData to determine how chart data should be grouped
*/
X2ActionHistoryChart.prototype.chartDataFilter = function (dataPoint, type) {
	var thisX2Chart = this;

	if ((!(type === 'any' || type === '') && dataPoint['type'] !== type) ||
		(type === '' && dataPoint['type'] !== null)) {
		return true;
	} else {
		return false;
	}
};

/*
Returns dictionary with keys equal to metric types and value equal to metric type
labels
*/
X2ActionHistoryChart.prototype.getMetricTypes = function () {
	var thisX2Chart = this;

	var metricTypes = [];
	$('#' + thisX2Chart.chartType + '-first-metric').children ().each (function () {
		if (thisX2Chart.chartSubtype === 'pie' &&
			$(this).val () === 'any') return;
		metricTypes.push([$(this).val (), $(this).html ()]);
	});

	return metricTypes;
};

/*
Add pie chart specific css rules
*/
X2ActionHistoryChart.prototype.postPieChartTearDown = function () {
	var thisX2Chart = this;
	$('#' + thisX2Chart.chartType + '-chart').removeClass ('pie');
	$('#' + thisX2Chart.chartType + '-chart-legend').removeClass ('pie');
	$('#' + thisX2Chart.chartType + '-bin-size-button-set').removeClass ('pie');
	$('#' + thisX2Chart.chartType + '-datepicker-row').removeClass ('action-history-pie');
	$('#' + thisX2Chart.chartType + '-top-button-row').removeClass ('pie');
	$('#' + thisX2Chart.chartType + '-rel-chart-data-checkbox-container').removeClass ('pie');
};

/*
Remove pie chart specific css rules
*/
X2ActionHistoryChart.prototype.postPieChartSetUp = function () {
	var thisX2Chart = this;
	$('#' + thisX2Chart.chartType + '-chart').addClass ('pie');
	$('#' + thisX2Chart.chartType + '-chart-legend').addClass ('pie');
	$('#' + thisX2Chart.chartType + '-bin-size-button-set').addClass ('pie');
	$('#' + thisX2Chart.chartType + '-datepicker-row').addClass ('action-history-pie');
	$('#' + thisX2Chart.chartType + '-top-button-row').addClass ('pie');
	$('#' + thisX2Chart.chartType + '-rel-chart-data-checkbox-container').addClass ('pie');
};



