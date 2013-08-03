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


var DEBUG = x2.chart.DEBUG;

/*
Removes an error div created by createErrorBox ().  
Parameters:
	parentElem - a jQuery element which contains the error div
*/
function destroyErrorBox (parentElem) {
	var $errorBox = $(parentElem).find ('.error-summary-container');
	if ($errorBox.length !== 0) {
		$errorBox.remove ();
	}
}

/*
Returns a jQuery element corresponding to an error box. The error box will
contain the specified errorHeader and a bulleted list of the specified error
messages.
Parameters:
	errorHeader - a string
	errorMessages - an array of strings
*/
function createErrorBox (errorHeader, errorMessages) {
	var errorBox = $('<div>', {'class': 'error-summary-container'}).append (
		$("<div>", { 'class': "error-summary"}).append (
			$("<p>", { text: errorHeader }),
			$("<ul>")
	));
	for (var i in errorMessages) {
		var msg = errorMessages[i];
		$(errorBox).find ('.error-summary').
			find ('ul').append ($("<li> " + msg + " </li>"));
	}
	return errorBox;
}

function setupChartBehavior (suppressChartSettings) {

	var eventData = null; // the ajax returned data to plot
	var feedChart = null; // the jqplot chart object
	var msPerHour = 3600 * 1000;
	var msPerDay = 86400 * 1000;
	var msPerWeek = 7 * 86400 * 1000;
	var cookiePrefix = x2.chart.chartPage; // used to differentiate chart settings

	if (x2.chart.chartType === 'multiLine') {
		if (x2.chart.chartPage === 'recordView') {	
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
		} else if (x2.chart.chartPage === 'activityFeed') {	
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
		}
		var metricOptionsColors = {};
		$('#first-metric').find ('option').each (function () {
			metricOptionsColors[$(this).val ()] = colors.shift ();
		});
	}

	/*
	Ask server for all events between user specified dates.
	Replot data on server response.
	Parameters:
		redraw - Boolean, determines whether plotData will clear the plot before drawing
	*/
	function getEventsBetweenDates (redraw) {
		var binSize = $('#bin-size-button-set a.disabled-link').attr ('id');
		var tsDict = getStartEndTimestamp (binSize);
		var startTimestamp = tsDict['startTimestamp'];
		var endTimestamp = tsDict['endTimestamp'];

		DEBUG && console.log ('getting events between ' + startTimestamp + ' and ' + endTimestamp);

		var data = {
			'startTimestamp': startTimestamp / 1000,
			'endTimestamp': endTimestamp / 1000
		}

		if (x2.chart.actionParams) {
			$.extend (data, x2.chart.actionParams);
		}

		if (x2.chart.chartData) {
			eventData = x2.chart.chartData;
			plotData ({'redraw': redraw});
			x2.chart.chartData = null;
			return;
		}

		$.ajax ({
			'url': x2.chart.getChartDataActionName,
			'data': data,
			'success': function (data) {
				eventData = JSON.parse (data);
				plotData ({'redraw': redraw});
			}
		});
	}

	/*
	Returns the string of the specified width padded on the left with zeroes.
	Precondition: width >= str.length
	*/
	function padTimeField (str, width) {
		if (str.length === width) return str;

		return (new Array (width - str.length + 1)).join ('0') + str;
	}

	/*
	Returns an array of jqplot entries with y values equal to 0 and x values
	between timestamp1 and timestamp2. x values increase by interval.
	Parameters
		inclusiveBegin - a boolean, whether to include the entry corresponding to
			timestamp1 in the returned array
		inclusiveEnd - a boolean, whether to include the entry corresponding to
			timestamp2 in the returned array
		showMarker - if this is set to false, the returned array will have at most
			2 entries.
	*/
	function getZeroEntriesBetween (
		timestamp1, timestamp2, interval, inclusiveBegin , inclusiveEnd , showMarker) {

		if (timestamp2 <= timestamp1) {
			return [];
		}

		var entries = [];

		if (inclusiveBegin)
			entries.push ([timestamp1, 0]);

		switch (interval) {
			case 'hour':
				var msPerHour = 3600 * 1000;

				if (!showMarker) {
					var intermediateTimestamp1 = timestamp1;
					var intermediateTimestamp2 = timestamp2;
					intermediateTimestamp1 += msPerHour;
					intermediateTimestamp2 -= msPerHour;
					if (intermediateTimestamp1 < intermediateTimestamp2) {
						entries.push ([intermediateTimestamp1, 0]);
						entries.push ([intermediateTimestamp2, 0]);
					} else if (intermediateTimestamp1 < timestamp2) {
						entries.push ([intermediateTimestamp1, 0]);
					}
					if (inclusiveEnd) {
						entries.push ([timestamp2, 0]);
					}
				} else {
					var intermediateTimestamp = timestamp1;
					while (true) {
						intermediateTimestamp += msPerHour;
						if ((intermediateTimestamp < timestamp2 && !inclusiveEnd) ||
							(intermediateTimestamp <= timestamp2 && inclusiveEnd)) {
							entries.push ([intermediateTimestamp, 0]);
						} else {
							break;
						}
					}
				}
				break;
			case 'day':
				var msPerDay = 86400 * 1000;

				if (!showMarker) {
					var intermediateTimestamp1 = timestamp1;
					var intermediateTimestamp2 = timestamp2;
					intermediateTimestamp1 += msPerDay;
					intermediateTimestamp2 -= msPerDay;
					if (intermediateTimestamp1 < intermediateTimestamp2) {
						entries.push ([intermediateTimestamp1, 0]);
						entries.push ([intermediateTimestamp2, 0]);
					} else if (intermediateTimestamp1 < timestamp2) {
						entries.push ([intermediateTimestamp1, 0]);
					}
					if (inclusiveEnd) {
						entries.push ([timestamp2, 0]);
					}
				} else {
					var intermediateTimestamp = timestamp1;
					while (true) {
						intermediateTimestamp += msPerDay;
						if ((intermediateTimestamp < timestamp2 && !inclusiveEnd) ||
							(intermediateTimestamp <= timestamp2 && inclusiveEnd)) {
							entries.push ([intermediateTimestamp, 0]);
						} else {
							break;
						}
					}
				}

				break;
			case 'week':
				var msPerWeek = 7 * 86400 * 1000;

				if (!showMarker) {
					var intermediateTimestamp1 = timestamp1;
					var intermediateTimestamp2 = timestamp2;
					intermediateTimestamp1 += msPerWeek;
					intermediateTimestamp2 -= msPerWeek;
					if (intermediateTimestamp1 < intermediateTimestamp2) {
						entries.push ([intermediateTimestamp1, 0]);
						entries.push ([intermediateTimestamp2, 0]);
					} else if (intermediateTimestamp1 < timestamp2) {
						entries.push ([intermediateTimestamp1, 0]);
					}
				} else {
					var intermediateTimestamp = timestamp1;
					while (true) {
						intermediateTimestamp += msPerWeek;
						if ((intermediateTimestamp < timestamp2 && !inclusiveEnd) ||
							(intermediateTimestamp <= timestamp2 && inclusiveEnd)) {
							entries.push ([intermediateTimestamp, 0]);
						} else {
							break;
						}
					}
				}

				break;
			case 'month':
				var date1 = new Date (timestamp1);
				var date2 = new Date (timestamp2);
				var M1 = date1.getMonth () + 1;
				var D1 = date1.getDate ();
				var Y1 = date1.getFullYear ();
				var M2 = date2.getMonth () + 1;
				var D2 = date2.getDate ();
				var Y2 = date2.getFullYear ();
				var endMonth = date2.getMonth ();
				var endYear = date2.getYear ();
				var beginString = (M1 + '-' + 1 + '-' + Y1);
				var endString = (M2 + '-' + 1 + '-' + Y2);
				var isFirst = true;


				var dateString, timestamp;
				while (true) {

					M1++;
					if (M1 === 13) {
						Y1++;
						M1 = 1;
					}

					beginString = M1 + '-' + 1 + '-' + Y1;
					nextMonth = M1 + 1;
					nextYear = Y1;
					if (nextMonth === 13) {
						nextYear++;
						nextMonth = 1;
					}
					nextString = nextMonth + '-' + 1 + '-' + nextYear;

					if ((inclusiveEnd) ||
						(!inclusiveEnd && beginString !== endString)) {
						timestamp = (new Date (Y1, M1 - 1, 1, 0, 0, 0, 0)).getTime ();
						if (isFirst) {
							entries.push ([timestamp, 0]);
							isFirst = false;
						} else if (showMarker ||
							nextString === endString) {
							entries.push ([timestamp, 0]);
						} else {

						}
					}
					if (beginString === endString) {
						break;
					}
				}

				break;
		}
		return entries;
	}

	/*
	Returns an array which can be passed to jqplot. Each entry in the array corresponds
	to the number of events of a given type and at a certain time (hour, day, week, or
	month depending on the bin size)
	Parameters:
		eventData - an array set by getEventsBetween
		binSize - a string
		type - a string. The type of event that will get plotted.
	*/
	function groupChartData (eventData, binSize, type, showMarker) { 

		var chartData = [];

		DEBUG && console.log ('eventData = ');
		DEBUG && console.log (eventData);

		// group chart data into bins and keep count of the number of entries in each bin
		switch (binSize) {
			case 'hour-bin-size':
				var hour, day, month, year, evt, dateString, timestamp, count;
				for (var i in eventData) {
					evt = eventData[i];
					count = evt['count'] === '0' ? 1 : parseInt (evt['count'], 10);
					if ((!(type === 'any' || type === '') && evt['type'] !== type) ||
						(type === '' && evt['type'] !== null)) continue;
					if (evt['year'] === year &&
						evt['month'] === month &&
						evt['day'] === day &&
						evt['hour'] === hour) {
						chartData[chartData.length - 1][1] += count;
					} else {
						year = evt['year'];
						month = evt['month'];
						day = evt['day'];
						hour = evt['hour'];

						timestamp = (new Date (
							year, month - 1, day, hour, 0, 0, 0)).getTime ();
						chartData.push ([timestamp, count]);
					}

				}
				break;
			case 'day-bin-size':
				var day, month, year, evt, dateString, timestamp, count;
				for (var i in eventData) {
					evt = eventData[i];
					count = evt['count'] === '0' ? 1 : parseInt (evt['count'], 10);
					DEBUG && console.log (count);
					if ((!(type === 'any' || type === '') && evt['type'] !== type) ||
						(type === '' && evt['type'] !== null)) continue;
					if (evt['year'] === year &&
						evt['month'] === month &&
						evt['day'] === day) {
						chartData[chartData.length - 1][1] += count;
					} else {
						year = evt['year'];
						month = evt['month'];
						day = evt['day'];

						timestamp = (new Date (
							year, month - 1, day, 0, 0, 0, 0)).getTime ();
						chartData.push ([timestamp, count]);
					}
				}
				break;
			case 'week-bin-size':
				var week, year, evt, dateString, timestamp, date, day, msPerWeek, count;
				for (var i in eventData) {
					evt = eventData[i];
					count = evt['count'] === '0' ? 1 : parseInt (evt['count'], 10);
					if ((!(type === 'any' || type === '') && evt['type'] !== type) ||
						(type === '' && evt['type'] !== null)) continue;
					if (evt['year'] === year &&
						evt['week'] === week) {
						chartData[chartData.length - 1][1] += count;
					} else {
						year = evt['year'];
						week = evt['week'];
						timestamp = (new Date (
							year, evt['month'] - 1, evt['day'], 0, 0, 0, 0)).getTime ();
						date = new Date (timestamp);
						day = date.getDay ();
						msPerWeek = 86400 * 1000;
						timestamp -= day * msPerWeek;

						chartData.push ([timestamp, count]);
					}
				}
				break;
			case 'month-bin-size':
				var month, year, evt, dateString, timestamp, count;
				for (var i in eventData) {
					evt = eventData[i];
					count = evt['count'] === '0' ? 1 : parseInt (evt['count'], 10);
					if ((!(type === 'any' || type === '') && evt['type'] !== type) ||
						(type === '' && evt['type'] !== null)) continue;
					if (evt['year'] === year &&
						evt['month'] === month) {
						chartData[chartData.length - 1][1] += count;
					} else {
						year = evt['year'];
						month = evt['month'];

						timestamp = (new Date (
							year, month - 1, 1, 0, 0, 0, 0)).getTime ();
						chartData.push ([timestamp, count]);
					}
				}
				break;
		}

		// insert entries with y value equal to 0 into chartData at the specified interval
		chartData.reverse ();
		var chartDataIndex = 0;
		var timestamp1, timestamp2, arr1, arr2, intermArr;
		while (chartData.length !== 0 && chartDataIndex < chartData.length - 1) {

			timestamp1 = chartData[chartDataIndex][0];
			timestamp2 = chartData[chartDataIndex + 1][0];

			switch (binSize) {
				case 'hour-bin-size':
					arr1 = chartData.slice (0, chartDataIndex + 1);
					arr2 = chartData.slice (chartDataIndex + 1, chartData.length);
					intermArr = getZeroEntriesBetween (
						timestamp1, timestamp2, 'hour', false, false, showMarker);
					if (intermArr.length !== 0)
						chartData = arr1.concat (intermArr, arr2);
					chartDataIndex += intermArr.length + 1;
					break;
				case 'day-bin-size':
					arr1 = chartData.slice (0, chartDataIndex + 1);
					arr2 = chartData.slice (chartDataIndex + 1, chartData.length);
					intermArr = getZeroEntriesBetween (
						timestamp1, timestamp2, 'day', false, false, showMarker);
					if (intermArr.length !== 0)
						chartData = arr1.concat (intermArr, arr2);
					chartDataIndex += intermArr.length + 1;
					break;
				case 'week-bin-size':
					arr1 = chartData.slice (0, chartDataIndex + 1);
					arr2 = chartData.slice (chartDataIndex + 1, chartData.length);
					intermArr = getZeroEntriesBetween (
						timestamp1, timestamp2, 'week', false, false, showMarker);
					if (intermArr.length !== 0)
						chartData = arr1.concat (intermArr, arr2);
					chartDataIndex += intermArr.length + 1;
					break;
				case 'month-bin-size':
					arr1 = chartData.slice (0, chartDataIndex + 1);
					arr2 = chartData.slice (chartDataIndex + 1, chartData.length);
					intermArr = getZeroEntriesBetween (
						timestamp1, timestamp2, 'month', false, false, showMarker);
					if (intermArr.length !== 0)
						chartData = arr1.concat (intermArr, arr2);
					chartDataIndex += intermArr.length + 1;
					break;
			}

		}


		return {
			chartData: chartData
		};
	}

	/*
	Helper function for jqplot used to widen the date range if the user selected
	begin and end dates are the same.
	*/
	function shiftTimeStampOneInterval (timestamp, binSize, forward) {
		var newTimestamp = timestamp;
		switch (binSize) {
			case 'hour-bin-size':
			case 'day-bin-size':
				var msPerDay = 86400 * 1000;
				if (forward)
					newTimestamp += msPerDay;
				else
					newTimestamp -= msPerDay;
				break;
			case 'week-bin-size':
				var msPerWeek = 7 * 86400 * 1000;
				if (forward)
					newTimestamp += msPerWeek;
				else
					newTimestamp -= msPerWeek;
				break;
			case 'month-bin-size':
				var date = new Date (timestamp);
				var M = date.getMonth () + 1;
				var Y = date.getFullYear ();
				if (forward) {
					M++;
					if (M === 13) {
						M = 0;
						Y++;
					}
				} else {
					M--;
					if (M === 0) {
						M = 12;
						Y--;
					}
				}
				newTimestamp = (new Date (Y, M - 1, 1, 0, 0, 0, 0)).getTime ();
				break;
		}
		return newTimestamp;
	}

	/*
	Calls getZeroEntriesBetween () to pad the left and right side of the chart data
	with entries having y values equal to 0 and x values increasing by the bin size.
	Parameter:
		binSize - user selected, determines x value spacing
		showMarker - if false, a maximum of two entries will be added to the left
			and right side of the chart data.
	*/
	function fillZeroEntries (
		startTimestamp, endTimestamp, binSize, chartData, showMarker) {

		var binType = binSize.match (/^[^-]+/)[0];

		if (chartData[0] === null) {
			DEBUG && console.log ('data is null, filling zeroes');
			chartData = getZeroEntriesBetween (
				startTimestamp, endTimestamp, binType, true, true, showMarker);
			return chartData;
		}

		var chartStartTimestamp = chartData[0][0];
		var chartEndTimestamp = chartData[chartData.length - 1][0];
		if (startTimestamp < chartStartTimestamp) {
			var arr = getZeroEntriesBetween (
				startTimestamp, chartStartTimestamp, binType, true, false, showMarker);
			if (arr.length !== 0) {
				chartData = arr.concat (chartData);
			}
		}
		if (endTimestamp > chartEndTimestamp) {
			var arr = getZeroEntriesBetween (
				chartEndTimestamp, endTimestamp, binType, false, true, showMarker);
			if (arr.length !== 0)
				chartData = chartData.concat (arr);
		}

		return chartData;
	}


	/*
	Returns a dictionary containing the number of hours, days, months, and years between
	the start and end timestamps.
	*/
	function countHoursDaysMonthsYears (startTimestamp, endTimestamp) {

		var dateRange =
			endTimestamp - startTimestamp;


		// get starting and ending months and years
		var startDate = new Date (startTimestamp);
		var startMonth = startDate.getMonth () + 1;
		var startYear = startDate.getFullYear ();
		var endDate = new Date (endTimestamp);
		var endMonth = endDate.getMonth () + 1;
		var endYear = endDate.getFullYear ();


		// count hours, days, weeks, months
		var hours = dateRange / 1000 / 60 / 60;
		var days = hours / 24;
		var weeks = days / 7;
		var months;
		var yearCount = endYear - startYear;
		if (yearCount === 0) {
			months = endMonth - startMonth + 1;
		} else if (yearCount === 1) {
			months = endMonth + ((12 - startMonth) + 1) + 1;
		} else { // yearCount > 1
			months = (endMonth + ((12 - startMonth) + 1)) + (12 * (yearCount - 2)) + 1;
		}

		if (hours === 0) hours = 24;
		if (days === 0) hours = 1;
		if (weeks === 0) weeks = 1;
		if (months === 0) months = 1;

		return {
			'hours': hours,
			'days': days,
			'months': months,
			'years': yearCount + 1
		};
	}

	// returns timestamp of nearest previous day at 12am
	function getRoundedDayTs (timestamp, curr) {
		var date = new Date (timestamp);
		var M = date.getMonth () + 1;
		var Y = date.getFullYear ();
		var D = date.getDate ();
		var newTimestamp = (new Date (Y, M - 1, D, 0, 0, 0, 0)).getTime ();
		if (!curr) {
			newTimestamp += msPerDay;
		}
		return newTimestamp;
	}

	// returns timestamp of nearest previous Sunday at 12am
	function getRoundedWeekTs (timestamp, curr) {
		var date = new Date (timestamp);
		var M = date.getMonth () + 1;
		var D = date.getDate ();
		var Y = date.getFullYear ();
		var newTimestamp = (new Date (Y, M - 1, D, 0, 0, 0, 0)).getTime ();
		var date = new Date (newTimestamp);
		var day = date.getDay ();
		var msPerWeek = 86400 * 1000;
		newTimestamp -= day * msPerWeek;
		if (!curr) {
			newTimestamp += msPerWeek;
		}
		return newTimestamp;
	}

	// returns timestamp of nearest previous 1st of month at 12am
	function getRoundedMonthTs (timestamp, curr) {
		var date = new Date (timestamp);
		var M = date.getMonth () + 1;
		var Y = date.getFullYear ();
		if (!curr) {
			M++;
			if (M > 12) {
				M = 1;
				Y++;
			}
		}
		var newTimestamp = (new Date (Y, M - 1, 1, 0, 0, 0, 0)).getTime ();
		return newTimestamp;
	}

	/*
	Retrieves the user selected start and end timestamps from the DOM.
	Parameter:
		binSize - if set, the start and end timestamps will be rounded down to the
			nearest hour, day, week, or month, respectively
	*/
	function getStartEndTimestamp (binSize /* optional */) {
		var startTimestamp =
			($('#chart-datepicker-from').datepicker ('getDate').valueOf ());
		var endTimestamp =
			($('#chart-datepicker-to').datepicker ('getDate').valueOf ());
		if (endTimestamp < startTimestamp)
			endTimestamp = startTimestamp;


		// round dates to nearest interval boundary
		if (typeof binSize !== 'undefined') {
			switch (binSize) {
				case 'hour-bin-size':
					break;
				case 'day-bin-size':
					startTimestamp = getRoundedDayTs (startTimestamp, true);
					endTimestamp = getRoundedDayTs (endTimestamp, true) + msPerDay;
					break;
				case 'week-bin-size':
					startTimestamp = getRoundedWeekTs (startTimestamp, true);
					endTimestamp = getRoundedWeekTs (endTimestamp, true);
					break;
				case 'month-bin-size':
					startTimestamp = getRoundedMonthTs (startTimestamp, true);
					endTimestamp = getRoundedMonthTs (endTimestamp, true);
					break;
			}
		}

		DEBUG && console.log ('getStartEndTimestamp: ');
		DEBUG && console.log (startTimestamp, endTimestamp);

		return {
			'startTimestamp': startTimestamp,
			'endTimestamp': endTimestamp
		}

	}

	/*
	Helper function for plotData. Determines the resolution of the graph.
	Returns false if the date range must be sliced into more than the set number of
	intervals, true otherwise.
	If this function returns false, markers should not be displayed.
	*/
	function getShowMarkerSetting (binSize, countDict) {
		var hours = countDict['hours'];
		var days = countDict['days'];
		var months = countDict['months'];
		var weeks = Math.floor (days / 7);
		var years = countDict['years'];

		var showMarker = true;
		switch (binSize) {
			case 'hour-bin-size':
				if (hours > 110)
					showMarker = false;
				break;
			case 'day-bin-size':
				if (days > 110)
					showMarker = false;
				break;
			case 'week-bin-size':
				if (weeks > 110)
					showMarker = false;
				break;
			case 'month-bin-size':
				if (months > 110)
					showMarker = false;
				break;
		}
		return showMarker;
	}

	function getLongMonthName (monthNum) {
		monthNum = + monthNum % 12;
		var monthName = "";
		switch (monthNum) {
			case 1:
				monthName = 'January';
				break;
			case 2:
				monthName = 'February';
				break;
			case 3:
				monthName = 'March';
				break;
			case 4:
				monthName = 'April';
				break;
			case 5:
				monthName = 'May';
				break;
			case 6:
				monthName = 'June';
				break;
			case 7:
				monthName = 'July';
				break;
			case 8:
				monthName = 'August';
				break;
			case 9:
				monthName = 'September';
				break;
			case 10:
				monthName = 'October';
				break;
			case 11:
				monthName = 'November';
				break;
			case 12:
				monthName = 'December';
				break;
		}
		return monthName;
	}

	function getShortMonthName (monthNum) {
		monthNum = + monthNum;
		var monthName = "";
		switch (monthNum) {
			case 1:
				monthName = 'Jan';
				break;
			case 2:
				monthName = 'Feb';
				break;
			case 3:
				monthName = 'Mar';
				break;
			case 4:
				monthName = 'Apr';
				break;
			case 5:
				monthName = 'May';
				break;
			case 6:
				monthName = 'Jun';
				break;
			case 7:
				monthName = 'Jul';
				break;
			case 8:
				monthName = 'Aug';
				break;
			case 9:
				monthName = 'Sep';
				break;
			case 10:
				monthName = 'Oct';
				break;
			case 11:
				monthName = 'Nov';
				break;
			case 12:
				monthName = 'Dec';
				break;
			default:
				DEBUG && console.log ('Error: getShortMonthName: default switch ' + monthNum);
		}
		return monthName;
	}

	/*
	Given a format string and a timestamp, returns a label with information extracted
	from the timestamp. Used to create tooltip text.
	*/
	function getTooltipFormattedLabel (formatStr, timestamp) {

		var fmtLabel = '';
		var tokens = formatStr.split (' ');
		var date = new Date (timestamp);

		var monthStr, D, M, hours, H, period, Y, endTimestamp, endDate;
		for (var i in tokens) {
			switch (tokens[i]) {
				case 'shortMonth':
					M = date.getMonth () + 1;
					monthStr = x2.chart.translations [getShortMonthName (M)];
					fmtLabel += monthStr;
					break;
				case 'day':
					D = date.getDate ();
					fmtLabel += D;
					break;
				case 'plusSixDays':
					endTimestamp = timestamp + 6 * msPerDay;
						
					fmtLabel += '- ' + getTooltipFormattedLabel (
						formatStr.split ('plusSixDays')[0], endTimestamp);
					formatStr.replace (/^.*plusSixDays/, '');
					break;
				case 'hours':
					hours = date.getHours (); 

					H = hours % 12 !== 0 ? hours % 12 : 12;
					period = hours >= 12 ? 'PM' : 'AM';
					fmtLabel += H + ':00 ' + period;
					break;
				case 'year':
					Y = date.getFullYear ();
					fmtLabel += Y;
					break;
				case 'longMonth':
					M = date.getMonth () + 1;
					monthStr = x2.chart.translations[getLongMonthName (M)];
					fmtLabel += monthStr;
					break;
				case '':
					break;
				default:
					DEBUG && console.log (tokens[i]);
					DEBUG && console.log ('Error: getTooltipFormattedLabel: switch default');
			}
			if (i !== tokens.length)
				fmtLabel += ' ';
		}
		return fmtLabel;

	}


	/*
	Returns an array of ticks acceptable as input to jqplot. The number of ticks
	and the ticks' labels depend on the user selected bin size and date range.
	*/
	function getTicks (startTimestamp, endTimestamp, binSize, countDict) {
		DEBUG && console.log ('getTicks');	

		var hours = countDict['hours'];
		var days = countDict['days'];
		var months = countDict['months'];
		var weeks = Math.floor (days / 7);
		var years = countDict['years'];


		/*
		Returns an array of tick entries which is acceptable to jqplot. Ticks will
		be labelled with the month and day from their corresponding timestamp.
		Tick entries will be between specified timestamps increasing at the
		specified interval.
		Parameters:
			interval - the number of days between each tick
		*/
		function getDayTicksBetween (startTimestamp, endTimestamp, interval) {
			var date = new Date (startTimestamp);
			var D = date.getDate ();
			var M = date.getMonth () + 1;
			var monthStr = x2.chart.translations [getShortMonthName (M)];
			ticks.push ([startTimestamp, monthStr + ' ' + D]);
			var timestamp = startTimestamp;
			timestamp += interval;
			while (timestamp <= endTimestamp) {
				date = new Date (timestamp);
				D = date.getDate ();
				M = date.getMonth () + 1;
				monthStr = x2.chart.translations[getShortMonthName (M)];
				ticks.push ([timestamp, monthStr + ' ' + D]);

				timestamp += interval;

				if (timestamp > endTimestamp)
					ticks.push ([endTimestamp, '']);
			}
			return ticks;
		}

		/*
		Returns an array of tick entries which is acceptable to jqplot. Ticks will
		be labelled with the month from their corresponding timestamp.
		Tick entries will be between specified timestamps increasing at the
		specified interval. If suppressYear is true, the tick's label will not
		include the year.
		Parameters:
			interval - the number of months between each tick
			suppressYear - a boolean
		Precondition: interval <= 12
		*/
		function getMonthTicksBetween (
			startTimestamp, endTimestamp, interval, suppressYear) {

			var date1 = new Date (startTimestamp);
			var date2 = new Date (endTimestamp);
			var M1 = date1.getMonth () + 1;
			var D1 = date1.getDate ();
			var Y1 = date1.getFullYear ();
			var M2 = date2.getMonth () + 1;
			var D2 = date2.getDate ();
			var Y2 = date2.getFullYear ();
			var endMonth = date2.getMonth ();
			var endYear = date2.getYear ();
			var beginString = (M1 + '-' + 1 + '-' + Y1);
			var endString = (M2 + '-' + 1 + '-' + Y2);
			var monthStr = x2.chart.translations[getShortMonthName (M1)];
			if (!suppressYear) monthStr += ' ' + Y1;
			var timestamp = (new Date (Y1, M1 - 1, 1, 0, 0, 0, 0)).getTime ();
			var roundedEndTimestamp = (new Date (Y2, M2 - 1, 1, 0, 0, 0, 0)).getTime ();
			if (timestamp === startTimestamp)
				ticks.push ([startTimestamp, monthStr]);
			else
				ticks.push ([startTimestamp, '']);

			DEBUG && console.log ('getting month ticks between ' + timestamp + ' and ' + roundedEndTimestamp);
			DEBUG && console.log ('endString = ' + endString);

			while (true) {
				M1 += interval;
				if (M1 > 12) {
					Y1 = Y1 + Math.floor (M1 / 12);
					M1 = (M1 % 12);
					M1 = M1 === 0 ? 12 : M1;
				}

				beginString = M1 + '-' + 1 + '-' + Y1;

				timestamp = (new Date (Y1, M1 - 1, 1, 0, 0, 0, 0)).getTime ();
				monthStr = x2.chart.translations[getShortMonthName (M1)];
				if (!suppressYear) monthStr += ' ' + Y1;

				if (beginString === endString || Y1 > Y2 || (Y1 === Y2 && M1 > M2)) {
					if (timestamp <= endTimestamp)
						ticks.push ([timestamp, monthStr]);
					if (beginString !== endString || roundedEndTimestamp !== endTimestamp)
						ticks.push ([endTimestamp, ""]);
					break;
				} else {
					ticks.push ([timestamp, monthStr]);
				}
			}
			return ticks;
		}

		var ticks = [];
		var labelFormat;
		DEBUG && console.log ('binSize = ' + binSize);
		switch (binSize) {
			case 'hour-bin-size':
				if (hours < 72) {
					var date = new Date (startTimestamp);
					ticks.push ([startTimestamp, '12:00 AM']);
					var timestamp = startTimestamp;
					var interval = msPerDay / 2;
					var period = 'PM';
					timestamp += interval;
					while (timestamp <= endTimestamp) {
						ticks.push ([timestamp, '12:00 ' + period]);
						if (period === 'PM')
							period = 'AM';
						else
							period = 'PM';
						timestamp += interval;
					}
					labelFormat = 'longMonth day hours';
				} else if (days <= 7) {
					ticks =
						getDayTicksBetween (startTimestamp, endTimestamp, msPerDay);
					labelFormat = 'longMonth day hours';
				} else if (days <= 62) {
					ticks = getDayTicksBetween (
						startTimestamp, endTimestamp, Math.ceil (days / 7) * msPerDay);
					labelFormat = 'longMonth day hours';
				} else if (days <= 182) {
					ticks = getDayTicksBetween (
						startTimestamp, endTimestamp, Math.ceil (weeks / 7) *  7 * msPerDay);
					labelFormat = 'longMonth day hours';
				} else if (days < 365) {
					ticks =
						getMonthTicksBetween (startTimestamp, endTimestamp, 1, true);
					labelFormat = 'longMonth day hours';
				} else {
					ticks = getMonthTicksBetween (
						startTimestamp, endTimestamp, Math.ceil (months / 7), false);
					labelFormat = 'longMonth day year hours';
				}
				break;
			case 'day-bin-size':
				if (days <= 7) {
					ticks =
						getDayTicksBetween (startTimestamp, endTimestamp, msPerDay);
					labelFormat = 'longMonth day';
				} else if (days <= 62) {
					ticks = getDayTicksBetween (
						startTimestamp, endTimestamp, Math.ceil (days / 7) * msPerDay);
					labelFormat = 'longMonth day';
				} else if (days <= 182) {
					ticks = getDayTicksBetween (
						startTimestamp, endTimestamp, Math.ceil (weeks / 7) *  7 * msPerDay);
					labelFormat = 'longMonth day';
				} else if (days < 365) {
					ticks =
						getMonthTicksBetween (startTimestamp, endTimestamp, 1, true);
					labelFormat = 'longMonth day';
				} else {
					ticks = getMonthTicksBetween (
						startTimestamp, endTimestamp, Math.ceil (months / 7), false);
					labelFormat = 'longMonth day year';
				}
				break;
			case 'week-bin-size':
				if (days <= 45) {
					ticks =
						getDayTicksBetween (startTimestamp, endTimestamp, 7 * msPerDay);
					labelFormat = 'longMonth day plusSixDays';
				} else if (days <= 62) {
					ticks = getDayTicksBetween (
						startTimestamp, endTimestamp, Math.ceil (days / 7) * msPerDay);
					labelFormat = 'longMonth day plusSixDays';
				} else if (days <= 182) {
					ticks = getDayTicksBetween (
						startTimestamp, endTimestamp, Math.ceil (weeks / 7) * 7 * msPerDay);
					labelFormat = 'longMonth day plusSixDays';
				} else if (days < 365) {
					ticks =
						getMonthTicksBetween (startTimestamp, endTimestamp, 1, true);
					labelFormat = 'longMonth day plusSixDays';
				} else {
					ticks = getMonthTicksBetween (
						startTimestamp, endTimestamp, Math.ceil (months / 7), false);
					labelFormat = 'longMonth day year plusSixDays';
				}
				break;
			case 'month-bin-size':
				if (days < 365) {
					ticks = getMonthTicksBetween (
						startTimestamp, endTimestamp, Math.ceil (months / 7), true);
					labelFormat = 'longMonth';
				} else {
					ticks = getMonthTicksBetween (
						startTimestamp, endTimestamp, Math.ceil (months / 7), false);
					labelFormat = 'longMonth year';
				}
				break;
			default: 
				DEBUG && console.log ('Error: getTicks: switch default');
		}

		DEBUG && console.log ('getTicks ret ' + labelFormat);

		return {
			'ticks': ticks,
			'startTimestamp': startTimestamp,
			'endTimestamp': endTimestamp,
			'labelFormat': labelFormat
		}

	}

	/*
	Sets up event functions to display tooltips on point mouse over.
	*/
	function setupTooltipBehavior (labelFormat, showMarker, chartData, typesText) {

		// bypass bug in jqplot
		for (var i in feedChart.series) {
			feedChart.series[i].highlightMouseOver = true;

		}

		// remove trailing 'px'
		function rStripPx (str) {
			return str.replace (/px$/, '');
		}

		// convert css value in pixels to an int
		function pxToInt (str) {
			return parseInt (rStripPx (str), 10);

		}

		/*var pointsDictSeries0 = {};
		var pointsDictSeries1 = {};
		for (var i in chartData[0]) {
			pointsDictSeries0[chartData[0][i][0]] = chartData[0][i];
		}
		if (chartData.length === 2) {
			for (var i in chartData[1]) {
				pointsDictSeries1[chartData[1][i][0]] = chartData[1][i];
			}
		}*/

		// create a data structure to optimize searching for points by x value
		var pointsDict = [];
		for (var i in chartData) {
			pointsDict.push ({});
			for (var j in chartData[i]) {
				pointsDict[i][chartData[i][j][0]] = chartData[i][j];
			}
		}

		// used to store location of highlighted point
		var pointXPrev = null;
		var pointYPrev = null;

		// display the tooltip
		$('#chart').unbind ('jqplotDataHighlight');
		$('#chart').bind ('jqplotDataHighlight', 
			function (ev, seriesIndex, pointIndex, data) {
				DEBUG && console.log ('jqplotDataHighlight');
				DEBUG && console.log ([ev, seriesIndex, pointIndex, data]);
				DEBUG && console.log ('showmarker = ' + showMarker);

				var chartLeft = $(this).offset ().left;
				var chartTop = $(this).offset ().top;
				var	pointX = feedChart.axes.xaxis.u2p (data[0]);
				var	pointY = feedChart.axes.yaxis.u2p (data[1]);
				var tooltip = $('#chart-tooltip');

				DEBUG && console.log (chartLeft, chartTop, pointX, pointY);

				// save for calculating distance between mouse and point
				pointXPrev = chartLeft + pointX;
				pointYPrev = chartTop + pointY;

				// insert tooltip text
				DEBUG && console.log ('getTooltipFormattedLabel ret: ' + 
					getTooltipFormattedLabel (labelFormat, data[0]));
				$(tooltip).html ($('<span>', {
					class: 'chart-tooltip-date',
					text: getTooltipFormattedLabel (labelFormat, data[0])
				}));
				$(tooltip).append ($('<br>'));

				DEBUG && console.log ('seriesIndex = ' + seriesIndex);

				// only show actions with 0 y values on two line chart
				if (x2.chart.chartType === 'twoLine' || data[1] !== 0) {
	
					for (var i = 0; i < feedChart.series.length; ++i) {
						if (i === seriesIndex) {
							$(tooltip).append ($('<span>', {
								text: typesText[i] + ': ' + data[1]
							}));
							$(tooltip).append ($('<br>'));
						} else if (
							(pointsDict[i][data[0]] &&
							 pointsDict[i][data[0]][1] === data[1]) ||
							// overlapping point should exist
							(data[1] === 0 && !showMarker && 
							 !pointsDict[i][data[0]])) {
	
							$(tooltip).append ($('<span>', {
								text: typesText[i] + ': ' + data[1]
							}));
							$(tooltip).append ($('<br>'));
						}
					}
				}

				/*if (seriesIndex === 0) {
					$(tooltip).append ($('<span>', {
						text: $('#first-metric').find (':selected').html () + ': ' + data[1]
					}));

					if (data[1] === 0 && !showMarker && chartData.length === 2 &&
						!pointsDict[1][data[0]]) { // overlapping point should exist
						$(tooltip).append ($('<br>'));
						$(tooltip).append ($('<span>', {
							text: $('#second-metric').find (':selected').html () + 
								': ' + data[1]
						}));
					}
				} else { // look for an overlapping point on the line for the other metric
					if ((pointsDict[0][data[0]] &&
						 pointsDict[0][data[0]][1] === data[1]) ||
						(data[1] === 0 && !showMarker && // overlapping point should exist
						 !pointsDict[0][data[0]])) {
						$(tooltip).append ($('<span>', {
							text: $('#first-metric').find (':selected').html () + 
								': ' + data[1]
						}));
						$(tooltip).append ($('<br>'));
					}
					$(tooltip).append ($('<span>', {
						text: $('#second-metric').find (':selected').html () + ': ' + data[1]
					}));
				}*/

				// determine where to place the tooltip
				var marginLeft, marginRight;
				marginLeft = 11;
				marginTop = 11;
				if (pointXPrev + pxToInt ($(tooltip).css ('width')) >
					chartLeft + pxToInt ($('#chart').css ('width'))) {
					DEBUG && console.log ('xoverflow');
					marginLeft = 
						- (pxToInt ($(tooltip).css ('width')) + marginLeft);
				}
				if (pointYPrev + pxToInt ($(tooltip).css ('height')) >
					chartTop + pxToInt ($('#chart').css ('height'))) {
					DEBUG && console.log ('yoverflow');
					marginTop = 
						- (pxToInt ($(tooltip).css ('height')) + marginTop);
				}

				$(tooltip).css ({
					position: 'absolute',
					left: pointXPrev,
					top: pointYPrev,
					'margin-left': marginLeft,
					'margin-top': marginTop
				});
				$(tooltip).show ();

			});

		function distance (x1, y1, x2, y2) {
			var dist = (Math.sqrt (Math.pow (x1 - x2, 2) + Math.pow (y1 - y2, 2)));
			return dist;
		}

		// hide tooltip when mouse moves away from data point
		function unhighlight (mouseX, mouseY) {
			//DEBUG && console.log ('jqplotDataUnhighlight');

			var tooltip = $('#chart-tooltip');

			if ($(tooltip).is (':visible') &&
				mouseX !== null && mouseY !== null &&
				pointXPrev !== null && pointYPrev !== null &&
				distance (
					mouseX, mouseY, pointXPrev, pointYPrev) > 12) {

				DEBUG && console.log ('hiding tooltip');

				//$('#chart-tooltip').empty ();
				$(tooltip).hide ();
			}

		}

		$('#chart').unbind ('mousemove');
		$('#chart').bind ('mousemove', function (event) {
			//DEBUG && console.log ('mouse');
			var mouseX = event.pageX;
			var mouseY = event.pageY;
			unhighlight (mouseX, mouseY);
		});

		$('#chart-tooltip').unbind ('mousemove');
		$('#chart-tooltip').bind ('mousemove', function (event) {
			//DEBUG && console.log ('chart-tooltip mouse');
			var mouseX = event.pageX;
			var mouseY = event.pageY;
			unhighlight (mouseX, mouseY);
		});

		$('#chart').unbind ('mouseout');
		$('#chart').bind ('mouseout', function (event) {
			$('#chart-tooltip').hide ();
		});

	}

	function buildChartLegend (typesText, color) {
		$('#chart-legend tbody').empty ();
		var makeNewRow = true;
		var currRow, currCell;
		for (var i in typesText) {
			if (makeNewRow) {
				DEBUG && console.log ('make new row');
				currRow = $('<tr>');
				$('#chart-legend tbody').append (currRow);
				makeNewRow = false;
			} else if ((i + 1) % 3 === 0) {
				makeNewRow = true;
			}
			DEBUG && console.log ('currRow = ');
			DEBUG && console.log ($(currRow));
				currCell = $('<td>').append (
					$('<div>', {
						class: 'chart-color-swatch'
					}),
					$('<span>', {
						text: typesText[i],
						class: 'chart-color-label'
					})
				)
			DEBUG && console.log ('setting background-color to ' + color[i]);
			$(currCell).find ('div').css ('background-color', color[i]);
			$(currRow).append (currCell);
		}
		if (typesText.length === 2) {
			$(currRow).append ($('<td>')); // dummy cell
		}
	}


	/*
	Plots event data retrieved by getEventsBetweenDates ().
	If two metrics are selected by the user, plotData will plot two lines.
	Parameter:
		args - a dictionary containing optional parameters.
			redraw - an optional parameter which can be contained in args. If set to
				true, the chart will be cleared before the plotting.
	*/
	function plotData (args /* optional */) {
		if (typeof args !== 'undefined') {
			redraw = typeof args['redraw'] === 'undefined' ?
				false : args['redraw'];
		} else { // defaults
			redraw = false;
		}

		// retrieve user selected values
		var binSize = $('#bin-size-button-set a.disabled-link').attr ('id');
		var tsDict = getStartEndTimestamp (binSize);
		var startTimestamp = tsDict['startTimestamp'];
		var endTimestamp = tsDict['endTimestamp'];

		// default settings
		var yTickCount = 3;
		var showXTicks = true;
		var showMarker = false;
		var tickInterval = null;

		// graph at least 1 interval
		if (startTimestamp === endTimestamp)
			endTimestamp = shiftTimeStampOneInterval (endTimestamp, binSize, true);

		var min = startTimestamp;
		var max = endTimestamp;

		// determine label format and number of ticks based on data
		var countDict = countHoursDaysMonthsYears (min, max);
		var ticksDict = getTicks (min, max, binSize, countDict);
		var ticks = ticksDict['ticks'];
		var labelFormat = ticksDict['labelFormat'];
		min = ticksDict['startTimestamp'];
		max = ticksDict['endTimestamp'];
		DEBUG && console.log ('ticks = ');
		DEBUG && console.log (ticks);
		showMarker = getShowMarkerSetting (binSize, countDict);

		if (ticks[0][0] < min)
			min = ticks[0][0]
		if (ticks[ticks.length - 1][0] > max)
			max = ticks[ticks.length - 1][0];

		// get user selected metrics
		var types;
		if (x2.chart.chartType === 'twoLine') {
			types = [];
			types.push ($('#first-metric').val ());
			types.push ($('#second-metric').val ());
		} else if (x2.chart.chartType === 'multiLine') {
			types = $('#first-metric').val ();
		}

		// get chartData for each user specified type
		var color = []; 
		var chartData = [];
		if (x2.chart.chartType === 'twoLine') { 
			color.push ('#7EB2E6'); // color of line 1
			var dataDict = groupChartData (eventData, binSize, types[0], showMarker);
			chartData.push (dataDict['chartData']);
			if (types[1] !== '') {
				color.push ('#C2597C'); // color of line 2
				dataDict = groupChartData (eventData, binSize, types[1], showMarker);
				chartData.push (dataDict['chartData']);
			}
		} else if (x2.chart.chartType === 'multiLine') {
			DEBUG && console.log ('types = ' + types);
			if (types === null) {
				chartData.push ([]);
			} else {
				var type;
				for (var i in types) {
					type = types[i];
					DEBUG && console.log ('type = ' + type);
					color.push (metricOptionsColors[types[i]]); // color of line 
					dataDict = groupChartData (eventData, binSize, type, showMarker);
					chartData.push (dataDict['chartData']);
				}
			}
			DEBUG && console.log ('metricOptionsColors = ');
			DEBUG && console.log (metricOptionsColors);
				
		}

		// if no chartData exists of specified type, don't plot it
		var noChartData = true;
		for (var i in chartData) {
			if (chartData[i].length === 0) {
				chartData[i] = [null];
			} else {
				noChartData = false;
			}
		}

		// pad left and right side of data with entries having y value equal to 0
		if (x2.chart.chartType === 'twoLine' ||
			(x2.chart.chartType === 'multiLine' && $('#first-metric').val () !== null)) {
			for (var i in chartData) {
				chartData[i] = fillZeroEntries (
					min, max, binSize, chartData[i], showMarker);
			}
		}

		DEBUG && console.log ('filled chartData = ');
		DEBUG && console.debug (chartData.toString ());

		DEBUG && console.log ('min = ' + min);
		DEBUG && console.log ('max = ' + max);

		jqplotConfig =  {
			seriesDefaults: {
				showMarker: showMarker,
				shadow: false,
				shadowAngle: 0,
				shadowOffset: 0,
				shadowDepth: 0,
				shadowAlpha: 0,
				markerOptions: {
					shadow: false,
					shadowAngle: 0,
					shadowOffset: 0,
					shadowDepth: 0,
					shadowAlpha: 0
				}
			},
			axesDefaults: {
				x2axis: {
					show: false
				}
			},
			seriesColors: color,
			series:[{
				label: 'Events',
				}
			],
			legend: {
				show: false,
				/*renderer: $.jqplot.EnhancedLegendRenderer,
				rendererOptions: {
					numberRows: 4
				},
				showSwatch: true,
				location: 's',
				showLabels: true,
				placement: 'outsideGrid',
				marginTop: '200px',
				marginLeft: '200px',
				border: 'none'*/
			},
			grid: {
				drawGridLines: false,
				gridLineColor: '#ffffff',
				borderColor: '#999',
				borderWidth: 1,
				background: '#ffffff',
				shadow: false
			},
			axes: {
				xaxis: {
					renderer: $.jqplot.DateAxisRenderer,
					tickOptions: {
						angle: -90
					},
					showTicks: showXTicks,
					ticks: ticks,
					min: min,
					max: max,
					padMin: 150,
					padMax: 150
				},
				yaxis: {
					pad: 1.05,
					numberTicks: yTickCount,
					tickOptions: {formatString: '%d'},
					min: 0
				}
			},
			highlighter: {
				show: true,
				showTooltip: false,
				sizeAdjust: 2.5

			}
		}

		if (noChartData) {
			jqplotConfig.axes.yaxis['max'] = 1;
			jqplotConfig.axes.yaxis['numberTicks'] = 2;
		}

		// plot chartData
		feedChart = $.jqplot ('chart', chartData, jqplotConfig);

		DEBUG && console.log ('chartData.length = ' + chartData.length);
		DEBUG && console.log ('labelFormat = ' + labelFormat);


		if (redraw) {
			feedChart.replot (); // clear previous plot and plot again
		}

		// used to display type labels in tooltips and legend
		var typesText = [];
		if (x2.chart.chartType === 'twoLine') {
			typesText.push ($('#first-metric').find (':selected').html ());
			typesText.push ($('#second-metric').find (':selected').html ());
		} else if (x2.chart.chartType === 'multiLine') {
			$('#first-metric').find (":selected").each (function () {
				typesText.push ($(this).html ());
			});
		}

		if (!noChartData)
			setupTooltipBehavior (labelFormat, showMarker, chartData, typesText);

		if (x2.chart.chartType === 'multiLine')
			buildChartLegend (typesText, color);

	}

	/*
	Changes the chart settings to match the settings specified in the parameters.
	*/
	function applyChartSetting (
		startDate, endDate, binSize, firstMetric, secondMetric, chartSetting) {

		if (startDate !== null) {
			DEBUG && console.log ('applying start date' + startDate);
			var startDate = new Date (parseInt (startDate, 10));
			$('#chart-datepicker-from').datepicker ('setDate', startDate);
		}
		if (endDate !== null) {
			DEBUG && console.log ('applying end date');
			var endDate = new Date (parseInt (endDate, 10));
			$('#chart-datepicker-to').datepicker ('setDate', endDate);
		}
		if (binSize !== null) {
			$('#chart-container a.disabled-link').removeClass ('disabled-link');
			$('#chart-container #' + binSize).addClass ('disabled-link');
		}
		if (firstMetric !== null) {

			DEBUG && console.log ('setting firstMetric');
			DEBUG && console.log ('typeof firstMetric = ' + typeof firstMetric);

			if (x2.chart.chartType === 'twoLine') {
				$('#first-metric').find ('option:selected').removeAttr ('selected');
				$('#first-metric').children ().each (function () {
					if ($(this).val () === firstMetric) {
						$(this).attr ('selected', 'selected');
						return false;
					}
				});
			} else if (x2.chart.chartType === 'multiLine') {

				$('#first-metric').find ('option').each (function () {
					$(this).removeAttr ('selected');
				});
				$('#first-metric').multiselect2 ('refresh');
				if (firstMetric !== 'none') {
					DEBUG && console.log ('setting firstMetric obj');
					if (typeof firstMetric === 'string')
						firstMetric = firstMetric.split (',');
					DEBUG && console.log ('firstMetric = ');
					DEBUG && console.log (firstMetric);
					for (var i in firstMetric) {
						$('#first-metric').find ('option').each (function () {
							if ($(this).val () === firstMetric[i]) {
								$(this).attr ('selected', 'selected');
							}
						});
						$('#first-metric').multiselect2 ('refresh');
					}
				}
			}
		}
		if (secondMetric !== null) {
			$('#second-metric').find ('option:selected').removeAttr ('selected');
			if (secondMetric === '') {
				$('#second-metric').children ().first ().attr ('selected', 'selected');
			} else {
				$('#second-metric').children ().each (function () {
					if ($(this).val () === secondMetric) {
						$(this).attr ('selected', 'selected');
						return false;
					}
				});
			}
		} 
		if (chartSetting !== null) {
			setChartSettingName (chartSetting);
		}
	}


	/*
	Extracts saved settings from cookie and sets chart settings to them.
	*/
	function setSettingsFromCookie () {
		var startDate, endDate, firstMetric, secondMetric;
		if (x2.chart.chartPage === 'activityFeed') {
			startDate = $.cookie (cookiePrefix + 'startDate');
			endDate = $.cookie (cookiePrefix + 'endDate');
			firstMetric = $.cookie (cookiePrefix + 'firstMetric');
			secondMetric = $.cookie (cookiePrefix + 'secondMetric');
		} else if (x2.chart.chartPage === 'recordView') {
			startDate = null;
			endDate = null;
			firstMetric = $.cookie (cookiePrefix + 'firstMetric');
			secondMetric = null;
		}
		var binSize = $.cookie (cookiePrefix + 'binSize');
		var chartSetting = $.cookie (cookiePrefix + 'chartSetting');
		DEBUG && console.log ('applying settings ');
		DEBUG && console.log ([
			startDate, endDate, binSize, firstMetric, secondMetric, chartSetting]);
		applyChartSetting (
			startDate, endDate, binSize, firstMetric, secondMetric, chartSetting);
	}

	if (!suppressChartSettings) {

		/*
		Selects chart setting from drop down. If the setting is not the custom setting,
		the delete button is displayed.
		*/
		function setChartSettingName (chartSetting) {
			$('#predefined-settings').find ('option:selected').removeAttr ('selected');
			$('#predefined-settings').children ().each (function () {
				if ($(this).val () === chartSetting) {
					$(this).attr ('selected', 'selected');
					return false;
				}
			});
			DEBUG && console.log ('setChartSettingName: chartSetting = ' + chartSetting);
			if (chartSetting === '') {
				$('#delete-setting-button').hide ();
			} else {
				$('#delete-setting-button').fadeIn ();
			}
		}

		/*
		Performs a request to save a new chart setting to the server. Also applies
		the new chart setting.
		*/
		function createChartSetting (settingName) {
			var chartSettingAttributes = {};
			chartSettingAttributes['name'] = settingName;

			chartSettingAttributes['settings'] = {
				'startDate' : ($('#chart-datepicker-from').datepicker ('getDate').valueOf ()) / 1000,
				'endDate' : ($('#chart-datepicker-to').datepicker ('getDate').valueOf ()) / 1000,
				'binSize' : $('#bin-size-button-set a.disabled-link').attr ('id')
			}

			if (x2.chart.chartType === 'twoLine') {
				chartSettingAttributes['settings']['metric1'] = 
					[$('#first-metric').val (), $('#second-metric').val ()];
			} else if (x2.chart.chartType === 'multiLine') {
				chartSettingAttributes['settings']['metric1'] = $('#first-metric').val ();
			}

			$.ajax ({
				url: "createChartSetting",
				data: {
					'chartSettingAttributes': JSON.stringify (chartSettingAttributes)
				},
				success: function (data) {
	
					if (data === '') { // successful creation
						DEBUG && console.log ('createChartSetting ajax success');
						x2.chart.chartSettings[settingName] = chartSettingAttributes;
						$('#create-chart-setting-dialog').dialog ("close");
	
						// select new chart setting from drop down
						$('#predefined-settings').children ().removeAttr ('selected');
						$('#predefined-settings').append ($('<option>', {
							'value': settingName,
							'text': settingName
						}));
						setChartSettingName (settingName);
						$('#predefined-settings').change ();
	
					} else { // creation failed
						DEBUG && console.log (data);
						var respObj = JSON.parse (data);
						DEBUG && console.log (respObj);
						DEBUG && console.debug (respObj);
						DEBUG && console.log ('createChartSetting ajax failure');
	
						// display error messages
						destroyErrorBox ($('#create-chart-setting-dialog'));
	
						var errMsgs = Object.keys (respObj).map (function (key) { 
								return respObj[key]; 
							});
						var errorBox = createErrorBox ('', errMsgs);
						$('.chart-setting-name-input-container').after ($(errorBox));
						$('#chart-setting-name').addClass ('error');
	
					}
	
				}
			});
	
		}
	
		function dialogSaveButtonFocus (dialog) {
			var $buttonpane = $(dialog).next ();
	
			if ($buttonpane.find ('.dialog-cancel-button').
				hasClass ('highlight')) {
	
				$buttonpane.find ('button').
					removeClass ('highlight');	
				$buttonpane.find ('.dialog-save-button').addClass ('highlight');
			}
		}
	
		function dialogCancelButtonFocus (dialog) {
			DEBUG && console.log ('dialogCancelButtonFocus');
			var $buttonpane = $(dialog).next ();
			DEBUG && console.log ($buttonpane);
	
			if ($buttonpane.find ('.dialog-save-button').
				hasClass ('highlight')) {
	
				DEBUG && console.log ('if');
				$buttonpane.find ('button').
					removeClass ('highlight');	
				$buttonpane.find ('.dialog-cancel-button').addClass ('highlight');
			}
		}
	
		function toggleDialogButtonFocus (dialog) {
			var $buttonpane = $(dialog).next ();
	
	
			if ($buttonpane.find ('.dialog-cancel-button').
				hasClass ('highlight')) {
	
				$buttonpane.find ('button').
					removeClass ('highlight');	
				$buttonpane.find ('.dialog-save-button').addClass ('highlight');
			} else { /* ($buttonpane.find ('.dialog-save-button').
				hasClass ('highlight') */
	
				$buttonpane.find ('button').
					removeClass ('highlight');	
				$buttonpane.find ('.dialog-cancel-button').addClass ('highlight');
			}
	
		}

		/*
		Sets up behavior of chart creation dialog box.
		*/
		(function setupChartSettingCreationDialog () {
			$('#create-chart-setting-dialog').hide();
	
			function clickChartSettingCreateButton () {
				var settingName = $('#chart-setting-name').val ();
				if (settingName === '') {
					$('#chart-setting-name').addClass ('error');
					destroyErrorBox ($('#create-chart-setting-dialog'));
					dialogCancelButtonFocus ($('#create-chart-setting-dialog'));
				} else {
					createChartSetting (settingName); 
				}
			}
	
			$("#create-chart-setting-dialog").find ("input").change (function () {
				DEBUG && console.log ('change');
				var $dialog = $('#create-chart-setting-dialog');
				dialogSaveButtonFocus ($('#create-chart-setting-dialog'));
			});
	
			$('#create-setting-button').click (function () {
				$("#create-chart-setting-dialog").dialog ({
					title: x2.chart.translations['Create Chart Setting'],
					autoOpen: true,
					height: "auto",
					width: 850,
					resizable: false,
					show: 'fade',
					hide: 'fade',
					buttons: [
						{ 
							text: x2.chart.translations['Create'],
							click: clickChartSettingCreateButton,
							class: 'dialog-save-button'
						},
						{ 
							text: x2.chart.translations['Cancel'],
							click: function () {
								$('#create-chart-setting-dialog').dialog ("close");
							},
							class: 'highlight dialog-cancel-button'
						}
					],
					close: function (event, ui) {
						$('#chart-setting-name').removeClass ('error');
						$('#chart-setting-name').val ('');
						destroyErrorBox ($('#create-chart-setting-dialog'));
					}
				});
			});
		}) ();

		$('#delete-setting-button').click (function (evt) {
			evt.preventDefault();
			var settingName = $('#predefined-settings').val ();
			$.ajax ({
				url: "deleteChartSetting",
				data: {
					'chartSettingName': settingName
				},
				success: function (data) {
					DEBUG && console.log ('delete-settings-button ajax call');
					DEBUG && console.log (data);
					if (data === 'success') {
						setChartSettingName ('');  
						$('#predefined-settings').change ();
						$('#predefined-settings').find (
							'[value="' + settingName + '"]').remove ();
					} 
				}
			});
		});

		/*
		Sets up behavior for predifined chart setting selection.
		*/
		$('#predefined-settings').change (function () {
			DEBUG && console.log ('predefined-settings: change');
			if ($(this).find (':selected').attr ('id') !== 'custom-settings-option') {
				$('#delete-setting-button').fadeIn ();
	
				// extract chart settings
				var settingName = $(this).find (':selected').val ();
				DEBUG && console.log ('predefined-setting selected, name = ' + settingName);
				var chartSetting = x2.chart.chartSettings[settingName]['settings'];
				DEBUG && console.debug (chartSetting);
				var startDate = chartSetting['startDate'] * 1000;
				var endDate = chartSetting['endDate'] * 1000;
				var binSize = chartSetting['binSize'];
				var metric1 = chartSetting['metric1'];
				metric1 = metric1 === null ? [] : metric1;
	
				DEBUG && console.log ('applying chart settings ');
				DEBUG && console.log ([
					startDate,
					endDate,
					binSize,
					metric1,
					null,
					null]);
	
				applyChartSetting (
					startDate,
					endDate,
					binSize,
					metric1,
					null,
					null);
	
				getEventsBetweenDates (true);
	
				// update cookies with chart settings
				$.cookie (cookiePrefix + 'startDate', startDate);
				$.cookie (cookiePrefix + 'endDate', endDate);
				$.cookie (cookiePrefix + 'binSize', binSize);
				$.cookie (cookiePrefix + 'firstMetric', metric1);
				//$.cookie (cookiePrefix + 'secondMetric', metric2);
			} else {
				$('#delete-setting-button').hide ();
			}
			$.cookie (cookiePrefix + 'chartSetting', $(this).val ());
		});
	}
	
	if (x2.chart.hideByDefault) {
		/*
		Show the chart when the show chart button is clicked
		*/
		$('#show-chart').click (function (evt) {
			evt.preventDefault();
			$('#chart-container').slideDown (450);
			if (feedChart !== null)
				feedChart.replot ({ resetAxes: false });
			$(this).hide ();
			$('#hide-chart').show ();
			$.cookie (cookiePrefix + 'chartIsShown', true);
		});
	
		/*
		Hide the chart when the hide chart button is clicked
		*/
		$('#hide-chart').click (function (evt) {
			evt.preventDefault();
			$('#chart-container').slideUp (450);
			$(this).hide ();
			$('#show-chart').show ();
			$.cookie (cookiePrefix + 'chartIsShown', false);
		});
	}

	// bin size button set behavior
	$('#chart-container a.x2-button').click (function (evt) {
		evt.preventDefault ();
		if (!$(this).hasClass ('disabled-link')) {
			$('#chart-container a.disabled-link').removeClass ('disabled-link');
			$(this).addClass ('disabled-link');
			if (eventData !== null) {
				plotData ({redraw: true});
			}
			var binSize = $('#bin-size-button-set a.disabled-link').attr ('id');
			$.cookie (cookiePrefix + 'binSize', binSize);
			if (!suppressChartSettings) {
				setChartSettingName ('');  
				$('#predefined-settings').change ();
			}
		}
	});

	if (x2.chart.chartType === 'twoLine') {
		// clear second metric and redraw graph using only first metric
		$('#clear-metric-button').click (function (evt) {
			evt.preventDefault();
			$('#second-metric-default').attr ('selected', 'selected');
			plotData ({redraw: true});
			$.cookie (cookiePrefix + 'secondMetric', '');
			if (!suppressChartSettings) {
				setChartSettingName ('');  
				$('#predefined-settings').change ();
			}
		});
		// setup metric selectors behavior
		$('#first-metric').change (function () {
			plotData ({redraw: true});
			$.cookie (cookiePrefix + 'firstMetric', $(this).val ());
			if (!suppressChartSettings) {
				setChartSettingName ('');  
				$('#predefined-settings').change ();
			}
		});
		$('#second-metric').change (function () {
			plotData ({redraw: true});
			$.cookie (cookiePrefix + 'secondMetric', $(this).val ());
			if (!suppressChartSettings) {
				setChartSettingName ('');  
				$('#predefined-settings').change ();
			}
		});
	} else if (x2.chart.chartType === 'multiLine') {
		// initialize dropdown checklist
		$('#first-metric').multiselect2 ({
			'checkAllText': x2.chart.translations['Check all'],
			'uncheckAllText': x2.chart.translations['Uncheck all'],
			'selectedText': '# ' + x2.chart.translations['metric(s) selected']
		});
		// setup metric selector behavior
		$('#first-metric').bind ("multiselect2close", function (evt, ui) {
			var firstMetricVal = $(this).val ();
			firstMetricVal = firstMetricVal === null ? 'none' : firstMetricVal;
			$.cookie (cookiePrefix + 'firstMetric', firstMetricVal);
			DEBUG && console.log ('close multiselect');
			plotData ({redraw: true});
		});

		// default setting
		if (x2.chart.chartPage === 'recordView') {
			$('#first-metric').children ().each (function () {
				$(this).attr ('selected', 'selected');
			});
		} else if (x2.chart.chartPage === 'activityFeed') {
			$('#first-metric').children ().first ().attr ('selected', 'selected');
		}
		$('#first-metric').multiselect2 ('refresh');
	}

	DEBUG && console.log (yii.datePickerFormat);

	// setup datepickers and initialize range to previous week
	$('#chart-datepicker-from').datepicker({
				constrainInput: false,
				showOtherMonths: true,
				selectOtherMonths: true,
				dateFormat: yii.datePickerFormat
	});
	$('#chart-datepicker-from').datepicker('setDate', new Date ());
	if (x2.chart.chartPage === 'activityFeed' &&
		$.cookie (cookiePrefix + 'startDate') === null) {
		// default start date 
		$('#chart-datepicker-from').datepicker('setDate', '-7d'); 
		$.cookie (
			cookiePrefix + 'startDate', 
			$('#chart-datepicker-from').datepicker ('getDate').valueOf ());
	} else if (x2.chart.actionsStartDate) { 
		// default start date is beginning of action history
		$('#chart-datepicker-from').datepicker(
			'setDate', new Date (x2.chart.actionsStartDate));
	}
	$('#chart-datepicker-to').datepicker({
				constrainInput: false,
				showOtherMonths: true,
				selectOtherMonths: true,
				dateFormat: yii.datePickerFormat
	});
	if (x2.chart.chartPage === 'activityFeed' &&
		$.cookie (cookiePrefix + 'endDate') === null) {
		// default start date 
		$('#chart-datepicker-to').datepicker('setDate', new Date ()); // default end date
		$.cookie (
			cookiePrefix + 'endDate', 
			$('#chart-datepicker-to').datepicker ('getDate').valueOf ());
	} else if (x2.chart.chartPage === 'recordView') {
		$('#chart-datepicker-to').datepicker('setDate', new Date ()); // default end date
	}

	/*
	Save setting in cookie and replot
	*/
	$('#chart-datepicker-from').datepicker ('option', 'onSelect', function () {
		DEBUG && console.log ('from date selected');
		getEventsBetweenDates (true);
		$.cookie (
			cookiePrefix + 'startDate', 
			$('#chart-datepicker-from').datepicker ('getDate').valueOf ());
		if (!suppressChartSettings) {
			setChartSettingName ('');  
			$('#predefined-settings').change ();
		}
	});

	/*
	Save setting in cookie and replot
	*/
	$('#chart-datepicker-to').datepicker ('option', 'onSelect', function () {
		getEventsBetweenDates (true);
		$.cookie (
			cookiePrefix + 'endDate', 
			$('#chart-datepicker-to').datepicker ('getDate').valueOf ());

		if (!suppressChartSettings) {
			setChartSettingName ('');  
			$('#predefined-settings').change ();
		}
	});

	if (x2.chart.chartPage === 'recordView') {
		$(document).on ('chartWidgetMaximized', function () {
			DEBUG && console.log ('max');
			feedChart.replot ({ resetAxes: false });
		});
		$(document).on ('newlyPublishedAction', function () {
			DEBUG && console.log ('new action');
			getEventsBetweenDates (true); 
		});
		$(document).on ('deletedAction', function () {
			DEBUG && console.log ('deleted action');
			getEventsBetweenDates (true); 
		});
	}

	// redraw graph on window resize
	$(window).on ('resize', function () {
		if ($('#chart-container').is (':visible') && feedChart !== null)
			feedChart.replot ({ resetAxes: false });
	});

	setSettingsFromCookie (); // fill settings with saved settings

	if (x2.chart.chartPage === 'activityFeed' && 
		$.cookie (cookiePrefix + 'chartIsShown') === 'true') {
			$('#chart-container').show ();
			$('#show-chart').hide ();
			$('#hide-chart').show ();
	}

	getEventsBetweenDates (false); // populate default graph

}


$(document).on ('ready', function x2chartMain () {
	setupChartBehavior (x2.chart.suppressChartSettings);
});



