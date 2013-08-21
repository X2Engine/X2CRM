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


function X2EventsChart (argsDict) {
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
		'#FFC382', // pastel orange
		'#E8E172', // pastel yellow
		'#FF9CAD', // pastel pink
		'#BAFFA1', // pastel green
		//'#CA8613', // orange brown
		//'#C6B019', // dark sand
		'#94E3DF', // pastel light blue
		'#56D6E3', // pastel mid blue
		'#99C9FF', // pastel dark blue
		'#FFA8CE', // pastel dark pink
		'#D099FF', // pastel dark purple
		'#E1A1FF', // pastel light purple
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
	];

	this.metricOptionsColors = {}; // used to pair colors with metrics
	$('#' + this.chartType + '-first-metric').find ('option').each (function () {
		thisX2Chart.metricOptionsColors[$(this).val ()] = colors.shift ();
	});

	this.cookieTypes = [
		'startDate', 'endDate', 'binSize', 'firstMetric', 'chartSetting', 
		'usersFilter', 'socialSubtypesFilter', 'visibilityFilter'];

	this.filterTypes = ['usersFilter', 'socialSubtypesFilter', 'visibilityFilter'];

	this.filters = {};


	/*if ($.cookie (thisX2Chart.cookiePrefix + 'chartIsShown') === 'true') {
		$('#' + this.chartType + '-chart-container').show ();
		$('#' + this.chartType + '-show-chart').hide ();
		$('#' + this.chartType + '-hide-chart').show ();
	}*/

	thisX2Chart.setUpFilters ();

	thisX2Chart.start ();
}

X2EventsChart.prototype = Object.create (X2Chart.prototype);

X2EventsChart.prototype.setDefaultSettings = function () {
	var thisX2Chart = this;

	// start date picker default
	if ($.cookie (thisX2Chart.cookiePrefix + 'startDate') === null) {
		// default start date 
		$('#' + thisX2Chart.chartType + '-chart-datepicker-from').
			datepicker('setDate', '-7d'); 
		$.cookie (
			thisX2Chart.cookiePrefix + 'startDate', 
			$('#' + thisX2Chart.chartType + '-chart-datepicker-from').
			datepicker ('getDate').valueOf ());
	}

	// end date picker default
	if ($.cookie (thisX2Chart.cookiePrefix + 'endDate') === null) {
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

X2EventsChart.prototype.chartDataFilter = function (dataPoint, type) {
	var thisX2Chart = this;

	if ((!(type === 'any' || type === '') && dataPoint['type'] !== type) ||
		(type === '' && dataPoint['type'] !== null) ||
		($.inArray (dataPoint['user'], thisX2Chart.filters['usersFilter']) !== -1 &&
		 $.inArray ('Anyone', thisX2Chart.filters['usersFilter']) !== -1) ||
		($.inArray (dataPoint['subtype'], 
			thisX2Chart.filters['socialSubtypesFilter']) !== -1) ||
		($.inArray (dataPoint['visibility'], 
			thisX2Chart.filters['visibilityFilter']) !== -1)) {

		if (($.inArray (dataPoint['user'], thisX2Chart.filters['usersFilter']) === -1) ||
		($.inArray (dataPoint['subtype'], thisX2Chart.filters['socialSubtypesFilter']) === -1) ||
		($.inArray (dataPoint['visibility'], thisX2Chart.filters['visibilityFilter']) === -1)) {
			/*console.log ('content filtered, user, subtype, visibility = ');
			thisX2Chart.DEBUG && console.log (dataPoint['user'] + ', ' + dataPoint['subtype'] + ',' + dataPoint['visibility']);*/
		}
		return true;
	} else {
		return false;
	}
};


