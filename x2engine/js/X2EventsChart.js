/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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


function X2EventsChart (argsDict) {
    argsDict = $.extend (true, {prototype: X2EventsChart.prototype}, argsDict);
	X2Chart.call (this, argsDict);	

	var thisX2Chart = this;

	this.userNames = argsDict['userNames'];
	this.socialSubtypes = argsDict['socialSubtypes'];
	this.visibilityTypes = argsDict['visibilityTypes'];
	this.DEBUG = argsDict['DEBUG'];

	var colors;
	// color palette used for lines of feed chart
	colors = [
		'#7EB2E6', // pale blue
		'#94E3DF', // pastel light blue
		'#9BE081', // pastel green
		'#E8E172', // pastel yellow
		'#FFA8CE', // pastel dark pink
		'#30DD81', // saturated pastel mid blue
		'#ECBA4F', // bright orange
		'#A1C6D2', // light gray blue
		'#428DE2', // saturated pastel dark blue
		'#D099FF', // pastel dark purple
		'#B243E6', // saturated pastel light purple
		'#DB8B99', // dark pastel pink
		'#CEC415', // mustard
		'#BC0D2C', // pomegranate
		'#45B41D', // apple green
		'#AB074F', // dark hot pink
		'#6D91A5', // dark blue
		'#3D1783', // dark purple
		'#AACF7A', // light olive green
		'#7BB57C', // olive green
		'#C87010', // red rock
		'#1D4C8C' // dark blue-purple
	];

	this.metricOptionsColors = {}; // used to pair colors with metrics
	$('#' + this.chartType + '-first-metric').find ('option').each (function () {
		thisX2Chart.metricOptionsColors[$(this).val ()] = colors.shift ();
	});

	this.cookieTypes = [
		'startDate', 'endDate', 'binSize', 'firstMetric', 'chartSetting', 
		'usersFilter', 'socialSubtypesFilter', 'visibilityFilter', 'dateRange'];

	this.filterTypes = ['usersFilter', 'socialSubtypesFilter', 'visibilityFilter'];

	this.filters = {};

	thisX2Chart.setUpFilters ();

    thisX2Chart.DEBUG && console.log ('X2EventsChart: end constructor');

	thisX2Chart.start ();
}

X2EventsChart.prototype = auxlib.create (X2Chart.prototype);

/*
Sets initial state of chart setting ui elements
*/
X2EventsChart.prototype.setDefaultSettings = function () {
	var thisX2Chart = this;

	// start date picker default
	if (($.cookie (thisX2Chart.cookiePrefix + 'dateRange') === null || 
	     $.cookie (thisX2Chart.cookiePrefix + 'dateRange') !== 'Custom') &&
	    $.cookie (thisX2Chart.cookiePrefix + 'startDate') === null) {

        thisX2Chart.DEBUG && console.log ('setting default');
		// default start date 
		$('#' + thisX2Chart.chartType + '-chart-datepicker-from').
			datepicker('setDate', new Date (new Date () - X2Chart.MSPERWEEK)); 

		$.cookie (
			thisX2Chart.cookiePrefix + 'startDate', 
			$('#' + thisX2Chart.chartType + '-chart-datepicker-from').
			datepicker ('getDate').valueOf ());
	}

	// end date picker default
	if (($.cookie (thisX2Chart.cookiePrefix + 'dateRange') === null || 
	     $.cookie (thisX2Chart.cookiePrefix + 'dateRange') !== 'Custom') &&
	    $.cookie (thisX2Chart.cookiePrefix + 'endDate') === null) {
		thisX2Chart.DEBUG && console.log ('setting default for eventsChart to date');
		// default start date 
		$('#' + thisX2Chart.chartType + '-chart-datepicker-to').
			datepicker('setDate', new Date ()); // default end date
		$.cookie (
			thisX2Chart.cookiePrefix + 'endDate', 
			$('#' + thisX2Chart.chartType + '-chart-datepicker-to').
			datepicker ('getDate').valueOf ());
	}

	// metric default
	$('#' + thisX2Chart.chartType + '-first-metric').children ().first ().attr (
		'selected', 'selected');
	$('#' + thisX2Chart.chartType + '-first-metric').multiselect2 ('refresh');

};

/*
Filter function used by groupChartData to determine how chart data should be grouped
*/
X2EventsChart.prototype.chartDataFilter = function (dataPoint, type) {
	var thisX2Chart = this;

    // group by type, filter out types specified in filters
	if ((!(type === 'any' || type === '') && dataPoint['type'] !== type) ||
		(type === '' && dataPoint['type'] !== null) ||
		($.inArray (dataPoint['user'], thisX2Chart.filters['usersFilter']) !== -1 &&
		 $.inArray ('Anyone', thisX2Chart.filters['usersFilter']) !== -1) ||
		($.inArray (dataPoint['subtype'], 
			thisX2Chart.filters['socialSubtypesFilter']) !== -1) ||
		($.inArray (dataPoint['visibility'], 
			thisX2Chart.filters['visibilityFilter']) !== -1)) {
		return true;
	} else {
		return false;
	}
};

/*
Returns dictionary with keys equal to metric types and value equal to metric type
labels
*/
X2EventsChart.prototype.getMetricTypes = function () {
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
Undo pie chart specific ui. Rebind filter ui element event handlers since the
filter elements get removed from the DOM when the chart subtype is switched.
*/
X2EventsChart.prototype.postPieChartTearDown = function (uiSetUp) {
	var thisX2Chart = this;
	$('#' + thisX2Chart.chartType + '-chart').removeClass ('pie');
	$('#' + thisX2Chart.chartType + '-chart-legend').removeClass ('pie');
	$('#' + thisX2Chart.chartType + '-datepicker-row').removeClass ('pie');
	$('#' + thisX2Chart.chartType + '-top-button-row').removeClass ('feed-pie');
	$('#' + thisX2Chart.chartType + '-create-setting-button').removeClass ('pie');
	$('#' + thisX2Chart.chartType + '-predefined-settings').removeClass ('pie');
	$('#' + thisX2Chart.chartType + '-first-metric-container').show ();
	$('#' + thisX2Chart.chartType + '-bin-size-button-set').show ();
	var filterToggleContainer = 
        $('#' + thisX2Chart.chartType + '-filter-toggle-container').remove ();
	$('#' + thisX2Chart.chartType + '-first-metric-container').after (filterToggleContainer);
    thisX2Chart.bindFilterEvents ();
};

/*
Set up pie chart specific ui. Rebind filter ui element event handlers since the
filter elements get removed from the DOM when the chart subtype is switched.
*/
X2EventsChart.prototype.postPieChartSetUp = function (uiSetUp) {
	var thisX2Chart = this;
	$('#' + thisX2Chart.chartType + '-chart').addClass ('pie');
	$('#' + thisX2Chart.chartType + '-chart-legend').addClass ('pie');
	$('#' + thisX2Chart.chartType + '-datepicker-row').addClass ('pie');
	$('#' + thisX2Chart.chartType + '-top-button-row').addClass ('feed-pie');
	$('#' + thisX2Chart.chartType + '-create-setting-button').addClass ('pie');
	$('#' + thisX2Chart.chartType + '-predefined-settings').addClass ('pie');
	$('#' + thisX2Chart.chartType + '-first-metric-container').hide ();
	$('#' + thisX2Chart.chartType + '-bin-size-button-set').hide ();
	var filterToggleContainer = 
        $('#' + thisX2Chart.chartType + '-filter-toggle-container').remove ();
	$('#' + thisX2Chart.chartType + '-datepicker-row').append (filterToggleContainer);
    thisX2Chart.bindFilterEvents ();
};


