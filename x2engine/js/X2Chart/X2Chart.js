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




/*
Base prototype. Should not be instantiated. 
*/


function X2Chart (argsDict) {

    // properties that can be set with constructor arguments
    var defaultArgs = {
        chartType: null, // (e.g. campaignChart, usersChart, eventsChart)
        actionParams: null, // parameters sent to data request action
        chartData: null, // used to store data returned by data request action
        getChartDataActionName: null, // name of data request action
        suppressChartSettings: true, // suppresses ui feature
        suppressDateRangeSelector: false, // suppresses ui feature
        translations: null, // used for various chart text
        chartSubtype: 'line', // (e.g. line, pie)
        chartSettings: null, // predefined chart settings
        // a function which will save a chart setting with a given key and value. 
        saveChartSetting: function (key, value, callback) {}, 
        lastChartSettings: {}, // chart settings that the user last set
        prototype: X2Chart.prototype, // used to convert between pie and line chart
        widgetUID: '', // used to uniquely identify this widget
        DEBUG: false && x2.DEBUG
    };

    auxlib.applyArgs (this, defaultArgs, argsDict);

    this.metricOptionsColors = null; // set in child prototype
    this.filterTypes = null; // set in child prototype
    this.eventData = null; // the ajax-returned data to plot
    this.feedChart = null; // the jqplot chart object
    this._setUpElementProperties ();
    this.metricTypes = this.getMetricTypes (); 

    this._multiSelectOptions = {
        'checkAllText': this.translations['Check all'],
        'uncheckAllText': this.translations['Uncheck all'],
        'classes': 'x2-chart-multiselect'
    };

    var thisX2Chart = this;

    this.setChartSubtype (this.chartSubtype, false, false, true);

    thisX2Chart.setUpSettingsUI ();

    if (Modernizr.canvas) {
        x2[thisX2Chart.chartType + this.widgetUID].windowResizeFunction = X2Chart.x2ChartResize (
            thisX2Chart);
    } else {
        x2[thisX2Chart.chartType + this.widgetUID].windowResizeFunction = 
            X2Chart.x2ChartNoCanvasResize (thisX2Chart);
    }

    // redraw graph on window resize
    $(window).unbind ('resize.' + this.chartType + this.widgetUID);
    $(window).bind (
        'resize.' + this.chartType + this.widgetUID, 
        x2[thisX2Chart.chartType + this.widgetUID].windowResizeFunction);

}

/************************************************************************************
Static Properties
************************************************************************************/

X2Chart.MSPERHOUR = 3600 * 1000;
X2Chart.MSPERDAY = 86400 * 1000;
X2Chart.MSPERWEEK = 7 * 86400 * 1000;


/************************************************************************************
Static Methods
************************************************************************************/

/**
 * This is a workaround to allow multiple instances of the same chart type to be present on the
 * same page. Since chart subtype switching uses dynamic class modification (methods get swapped)
 * each instance requires its own singleton subtype. That way, when a chart's subtype is switched,
 * it doesn't affect all charts of the same type.
 * @param Function constructor The constructor of the class being subtyped
 * @param Object argsDict arguments which should be passed to that constructor
 */
X2Chart.instantiateTemporarySubtype = function (constructor, argsDict) {
    function TemporarySubtype (argsDict) {
        constructor.call (this, argsDict);
    }
    TemporarySubtype.prototype = auxlib.create (constructor.prototype);
    argsDict.prototype = TemporarySubtype.prototype;
    return new TemporarySubtype (argsDict);
};


X2Chart.x2ChartNoCanvasResize = function (thisX2Chart) {
    var chartTarget = 
        thisX2Chart._chartContainer$.find ('.chart.jqplot-target');
    return function () {
        thisX2Chart.DEBUG && console.log ('resize');
        if (thisX2Chart._chartContainer$.is (':visible') && 
            thisX2Chart.feedChart !== null && !x2.isAndroid && !x2.isIPad) {
            $(chartTarget).width (thisX2Chart._chartContainer$.width ()); 
            thisX2Chart.DEBUG && console.log (thisX2Chart.feedChart);
            thisX2Chart.feedChart.replot ({ resetAxes: false });
        }
    };
};


X2Chart.x2ChartResize = function (thisX2Chart) {
    return function () {
        thisX2Chart.DEBUG && console.log ('resize');
        if (thisX2Chart._chartContainer$.is (':visible') && 
            thisX2Chart.feedChart !== null && !x2.isAndroid && !x2.isIPad) {
            thisX2Chart.DEBUG && console.log (thisX2Chart.feedChart);
            thisX2Chart.feedChart.replot ({ resetAxes: false });
        }
    };
};
    

/*
This can replace the plotData method of the X2Chart prototype. 
If plotData is replaced with this function, an empty chart will be drawn regardless
of user specified chart settings.
*/
X2Chart.plotEmptyChart = function (redraw) {
    var thisX2Chart = this;

    if (typeof args !== 'undefined') {
        redraw = typeof args['redraw'] === 'undefined' ?
            false : args['redraw'];
    } else { // defaults
        redraw = false;
    }
    var min = + new Date ();
    var max = + new Date ();
    jqplotConfig = thisX2Chart.getJqplotConfig ({
        'ticks': [], 
        'min': min, 
        'max': max, 
        'showMarker': false, 
        'color': [],
        'showXTicks': false
    });

    jqplotConfig.axes.yaxis['max'] = 1;
    jqplotConfig.axes.yaxis['numberTicks'] = 2;

    thisX2Chart.feedChart = 
        $.jqplot (thisX2Chart.chartType + '-chart-' + thisX2Chart.widgetUID, [[null]], 
        jqplotConfig);
    if (redraw) {
        thisX2Chart.feedChart.replot (); // clear previous plot and plot again
    }
};


/*
This can replace the getJqplotConfig method of the X2Chart prototype.
*/
X2Chart.getJqplotPieConfig = function (argsDict) {
    var min = argsDict['min'];
    var max = argsDict['max'];
    var color = argsDict['color'];
    var startAngle = argsDict['startAngle'];
    var diameter = argsDict['diameter'];

    var jqplotConfig = {
        seriesDefaults: {
            renderer: jQuery.jqplot.PieRenderer,
            rendererOptions: {
                //sliceMargin: 3,
                dataLabelThreshold: 5.5,
                diameter: diameter,
                showDataLabels: true,
                shadowOffset: 0,
                shadowDepth: 0,
                shadowAlpha: 0,
                startAngle: startAngle
            }
        },
        seriesColors: color,
        grid: {
            drawGridLines: false,
            gridLineColor: 'rgba(0,0,0,0)',
            borderWidth: 0,
            background: 'rgba(0,0,0,0)',
            shadow: false
        }
    };

    return jqplotConfig;
};

/*
This can replace the groupChartData method of the X2Chart prototype.
Parameters:
    thisX2Chart.eventData - an array set by getEventsBetween
    type - a string. The type of event that will get plotted.
*/
X2Chart.groupPieChartData = function (eventData, type) { 
    var thisX2Chart = this;

    var chartData = [];

    thisX2Chart.DEBUG && console.log ('thisX2Chart.eventData = ');
    thisX2Chart.DEBUG && console.log (thisX2Chart.eventData);

    var evt, count;
    for (var i in thisX2Chart.eventData) {
        evt = thisX2Chart.eventData[i];
        count = evt['count'] === '0' ? 1 : parseInt (evt['count'], 10);
        if (thisX2Chart.chartDataFilter (evt, type)) continue;
        if (chartData.length > 0) {
            chartData[1] += count;
        } else {
            chartData = [type, count];
        }
    }
    if (chartData.length === 0) {
        chartData = [type, 0];
    }

    return {
        chartData: chartData
    };
};

/*
This can replace the plotData method of the X2Chart prototype.
*/
X2Chart.plotPieData = function (args /* optional */) {

    var thisX2Chart = this;
    if (typeof args !== 'undefined') {
        redraw = typeof args['redraw'] === 'undefined' ?
            false : args['redraw'];
    } else { // defaults
        redraw = false;
    }

    // retrieve user selected values
    var tsDict = thisX2Chart.getStartEndTimestamp ();
    var startTimestamp = tsDict['startTimestamp'];
    var endTimestamp = tsDict['endTimestamp'];

    thisX2Chart.DEBUG && console.log ('thisX2Chart.plotData: startTimestamp, endTimestamp = ');
    thisX2Chart.DEBUG && console.log ([startTimestamp, endTimestamp]);

    var min = startTimestamp;
    var max = endTimestamp;

    thisX2Chart.DEBUG && console.log ('min = ' + min);
    thisX2Chart.DEBUG && console.log ('max = ' + max);

    // get user selected metrics
    var types = [];
    var metricTypes = thisX2Chart.getMetricTypes ();
    for (var i in metricTypes) types.push (metricTypes[i][0]);

    // get chartData for each user specified type
    var color = []; 
    var chartData = [];
    thisX2Chart.DEBUG && console.log ('types = ' + types);
    if (types === null) {
        chartData.push ([]);
    } else {
        var type;
        for (var i in types) {
            type = types[i];
            thisX2Chart.DEBUG && console.log ('type = ' + type);
            //color.push (thisX2Chart.metricOptionsColors[types[i]]); // color of line 
            dataDict = thisX2Chart.groupChartData (thisX2Chart.eventData, type);
            if (dataDict['chartData'].length !== 0) {
                chartData.push (dataDict['chartData']);
            } 
        }
    }

    function chartDataCompare (elemA, elemB) {
        thisX2Chart.DEBUG && console.log (elemA);
        thisX2Chart.DEBUG && console.log (elemB);
        thisX2Chart.DEBUG && console.log (elemA[1] < elemB[1]);
        if (elemA[1] > elemB[1]) {
            return -1;
        } else if (elemA[1] === elemB[1]) {
            return 0;
        } else {
            return 1;
        }
    }
    thisX2Chart.DEBUG && console.log ($.extend (true, [], chartData));
    chartData.sort (chartDataCompare);
    thisX2Chart.DEBUG && console.log ($.extend (true, [], chartData));

    thisX2Chart.DEBUG && console.log ('chartData = ');
    thisX2Chart.DEBUG && console.debug ($.extend (true, [], chartData));
    
    var unconvertedChartData = $.extend (true, [], chartData);

    thisX2Chart.DEBUG && console.log ('unconvertedChartData = ');
    thisX2Chart.DEBUG && console.debug (unconvertedChartData.toString ());

    var eventCount = thisX2Chart.getEventCount (chartData);
    thisX2Chart.convertCountsToPercents (chartData, eventCount);

    thisX2Chart.DEBUG && console.log ('unconvertedChartData = ');
    thisX2Chart.DEBUG && console.debug (unconvertedChartData.toString ());


    thisX2Chart.DEBUG && console.log ('metricOptionsColors = ');
    thisX2Chart.DEBUG && console.log (thisX2Chart.metricOptionsColors);

    // if no chartData exists of specified type, don't plot it
    var noChartData = true;

    if (chartData.length === 0) {
        chartData[0] = [null];
    } else {
        for (var i in chartData) {
            if (chartData[i].length === 0) {
                chartData[i] = [null];
            } else if ((chartData[i].length === 1 && chartData[i][1] !== 0) ||
                       chartData[i].length !== 1) {
                noChartData = false;
            }
        }
    }

    thisX2Chart.DEBUG && console.log ('chartData = ');
    thisX2Chart.DEBUG && console.debug ($.extend (true, [], chartData));

    thisX2Chart.DEBUG && console.log ('min = ' + min);
    thisX2Chart.DEBUG && console.log ('max = ' + max);

    var startAngle = '-45';
    var diameter = '200';
    jqplotConfig = thisX2Chart.getJqplotConfig ({
        'min': min, 
        'max': max, 
        'color': color,
        'startAngle': startAngle,
        'diameter': diameter
    });

    thisX2Chart.DEBUG && console.log ('jqplotConfig = ');
    thisX2Chart.DEBUG && console.log (jqplotConfig);

    thisX2Chart.preJqplotPlotPieData (chartData);

    for (var i in chartData) {
        color.push (thisX2Chart.metricOptionsColors[chartData[i][0]]); // color of line
    }

    // plot chartData
    thisX2Chart.feedChart = 
        $.jqplot (thisX2Chart.chartType + '-chart-' + thisX2Chart.widgetUID, [chartData], 
        jqplotConfig);

    thisX2Chart.DEBUG && console.log ('chartData.length = ' + chartData.length);
    //thisX2Chart.DEBUG && console.log ('labelFormat = ' + labelFormat);

    if (redraw) {
        thisX2Chart.feedChart.replot (); // clear previous plot and plot again
    }

    // used to display type labels in tooltips and legend
    var typesText = thisX2Chart.getMetricTypesText ();

    thisX2Chart.DEBUG && console.log ('typesText = ');
    thisX2Chart.DEBUG && console.debug ($.extend (true, {}, typesText));

    if (types !== null) {
        thisX2Chart.setupTooltipBehavior (
            chartData, unconvertedChartData, typesText, - parseFloat (startAngle, 10), 
            parseInt (diameter, 10));
    }

    thisX2Chart.updatePieChartEventCount (eventCount);

    if (!noChartData) {
        // remove text and colors corresponding to types with an event count of 0
        var filteredColors = $.extend (true, [], color);
        var filteredTypes = [];
        var i = 0, j = 0;
        while (true) {
            if (chartData[j][1] === 0) {
                filteredColors.splice (i, 1);
            } else {
                filteredTypes.push (chartData[j][0]);
                ++i;
            }
            j++;
            if (j >= chartData.length) break;
        }
    
        thisX2Chart.buildChartLegend (filteredTypes, typesText, filteredColors);
    }

    if (!Modernizr.canvas) {
        thisX2Chart.resizeChartNoCanvas ();
    }
};


/*
*/
X2Chart.setupPieTooltipBehavior = function (
    chartData, unconvertedChartData, typesText, startAngle, diameter) {

    var thisX2Chart = this;

    var chartData = $.extend (true, [], chartData);
    chartData.reverse ();
    var unconvertedChartData = unconvertedChartData.slice ();

    thisX2Chart.DEBUG && console.log ('setupPieTooltipBehavior: ');
    thisX2Chart.DEBUG && console.log (unconvertedChartData.toString ());
    thisX2Chart.DEBUG && console.log (chartData.toString ());

    thisX2Chart.DEBUG && console.log ('setupTooltipBehavior');

    // returns an array containing the x and y coordinate of the center of the pie chart
    function getPieCenter () {
        var pieCenterX, pieCenterY, pieOffset, pieWidth, pieHeight;
        var pieOffset = thisX2Chart._chart$.offset ();
        pieHeight = thisX2Chart._chart$.height ();
        pieWidth = thisX2Chart._chart$.width ();
        pieCenterY = pieOffset.top + pieHeight / 2.0;
        pieCenterX = pieOffset.left + pieWidth / 2.0;
        return [pieCenterX, pieCenterY];
    }

    // Finds a location near the center of the current pie slice and
    // returns an array containing x and y coordinates where the tooltip should be placed.
    function getTooltipLocation (type, pieCenter) {
        thisX2Chart.DEBUG && console.log ('diameter = ' + diameter);
        var tooltipAngle = startAngle;
        thisX2Chart.DEBUG && console.log ('startAngle = ' + tooltipAngle);
        thisX2Chart.DEBUG && console.log ('chartData = ');
        thisX2Chart.DEBUG && console.log (chartData);
        for (var i in chartData) {
            thisX2Chart.DEBUG && console.log ('type, chartData type = ' + type + ',' + chartData[i][0]);
            if (chartData[i][0] === type) {
                thisX2Chart.DEBUG && console.log ('adding ' + chartData[i][1] / 2.0);
                tooltipAngle += 360.0 * ((parseFloat (chartData[i][1], 10) / 2.0) / 100.0);
                break;
            } else {
                thisX2Chart.DEBUG && console.log ('adding ' + parseFloat (chartData[i][1], 10));
                tooltipAngle += 360.0 * (parseFloat (chartData[i][1], 10) / 100.0);
            }
        }
        thisX2Chart.DEBUG && console.log ('tooltipAngle = ' + tooltipAngle);
        tooltipAngle *= Math.PI / 180;
        thisX2Chart.DEBUG && console.log ('tooltipAngle = ' + tooltipAngle);
        var tooltipX, tooltipY;
        tooltipX = pieCenter[0] + Math.cos (tooltipAngle) * (diameter / 2.4);
        tooltipY = pieCenter[1] - 9 - Math.sin (tooltipAngle) * (diameter / 2.4);
        thisX2Chart.DEBUG && console.log ('tooltipLoc = ');
        thisX2Chart.DEBUG && console.log ([tooltipX, tooltipY]);
        return [tooltipX, tooltipY];
    }

    var currType;

    // display the tooltip
    thisX2Chart._chart$.unbind ('jqplotDataHighlight');
    thisX2Chart._chart$.bind ('jqplotDataHighlight', 
        function (ev, seriesIndex, pointIndex, data) {
            thisX2Chart.DEBUG && console.log ('jqthisX2Chart.plotDataHighlight');
            thisX2Chart.DEBUG && console.log ([ev, seriesIndex, pointIndex, data]);

            var tooltip = thisX2Chart._chartTooltip$;

            thisX2Chart.DEBUG && console.log ("$(tooltip).is (':visible')) = " + $(tooltip).is (':visible'));
            if (currType === data[0] && $(tooltip).is (':visible')) return;

            currType = data[0];

            var pieCenter = getPieCenter ();
            thisX2Chart.DEBUG && console.log ('pieCenter = ');
            thisX2Chart.DEBUG && console.log (pieCenter);
            var tooltipLoc = getTooltipLocation (data[0], pieCenter);

            $(tooltip).empty (); // clear previous tooltip

            thisX2Chart.DEBUG && console.log ('highlight: tooltiptype, typesText = ' + data[0] + ', ' + typesText[data[0]]);

            // create tooltip text
            $(tooltip).append ($('<span>', {
                text: typesText[data[0]] + ': ' + unconvertedChartData[pointIndex][1] + 
                    ' (' + data[1].toFixed (2) + '%)'
            }));
            $(tooltip).append ($('<br>'));

            thisX2Chart.DEBUG && console.log ('pieX = ' + pieCenter[0]);

            // determine tooltip orientation
            function getMarginLeft () {
                var marginLeft = 0;
                if (tooltipLoc[0] < pieCenter[0]) {
                    marginLeft = 
                        - (auxlib.pxToInt ($(tooltip).css ('width')) + 
                           2 * auxlib.pxToInt ($(tooltip).css ('padding')));
                }
                return marginLeft;
            }

            // style and position tooltip
            $(tooltip).css ({
                position: 'absolute',
                left: tooltipLoc[0],
                top: tooltipLoc[1],
                'margin-left': getMarginLeft (),
                'margin-top': 0
            });

            $(tooltip).show ();

            thisX2Chart.DEBUG && console.log ("$(tooltip).is (':visible')) = " + $(tooltip).is (':visible'));

        });

    /* 
    Used to determine when to hide/show the tooltip. This should only be used when the 
    cursor is inside the tooltip since jqplot's built in mouseout detection is more 
    accurate.
    Returns true if cursor is outside of slice, false otherwise
    */
    function isOutsideSlice (mouseX, mouseY) {
        var pieCenter = getPieCenter ();
        var dist = (Math.sqrt (Math.pow (mouseX - pieCenter[0], 2) + 
                    Math.pow (mouseY - pieCenter[1], 2)));
        var a = mouseX - pieCenter[0];
        var b = (- (mouseY - pieCenter[1]));
        /*console.log ('a = ' + a);
        thisX2Chart.DEBUG && console.log ('b = ' + b);
        thisX2Chart.DEBUG && console.log ('dist = ' + dist);*/

        // rotate points about center of circle
        var aPrime = Math.cos (- startAngle * (Math.PI / 180)) * a + 
            (- Math.sin (- startAngle * (Math.PI / 180)) * b);
        var bPrime = Math.sin (- startAngle * (Math.PI / 180)) * a + 
            (Math.cos (- startAngle * (Math.PI / 180)) * b);

        a = aPrime;
        b = bPrime;
        var mouseAngle = (Math.acos (a / dist)) * (180 / Math.PI);

        /*console.log ('a = ' + a);
        thisX2Chart.DEBUG && console.log ('b = ' + b);*/
        if (a > 0 && b > 0) {
        } else if (a < 0 && b > 0) {
        } else if (a < 0 && b < 0) {
            mouseAngle = 180 + (180 - mouseAngle);
        } else if (a > 0 && b < 0) {
            mouseAngle = 180 + (180 - mouseAngle);
            //mouseAngle += 180;
        }

        var currAngle = 0;
        var typeStartAngle, typeEndAngle;
        /*console.log ('chartData = ');
        thisX2Chart.DEBUG && console.log (chartData);*/
        for (var i in chartData) {
            if (chartData[i][0] === currType) {
                typeStartAngle = currAngle;
                /*console.log (parseFloat (chartData[i][1], 10));
                thisX2Chart.DEBUG && console.log ('adding ' + 360.0 * (parseFloat (chartData[i][1], 10) / 100.0));*/
                currAngle += 360.0 * (parseFloat (chartData[i][1], 10) / 100.0);
                typeEndAngle = currAngle;
                break;
            } else {
                currAngle += 360.0 * (parseFloat (chartData[i][1], 10) / 100.0);
            }
        }

        /*console.log ('startAngle, endAngle = ' + typeStartAngle + ',' + typeEndAngle);
        thisX2Chart.DEBUG && console.log (typeStartAngle);
        thisX2Chart.DEBUG && console.log (typeEndAngle);
        thisX2Chart.DEBUG && console.log ('startAngle === endAngle = ' + typeStartAngle === typeEndAngle);
        thisX2Chart.DEBUG && console.log ('mouseAngle = ' + mouseAngle);*/
        if (mouseAngle > typeEndAngle || mouseAngle < typeStartAngle)
            return true;
        else 
            return false;
    }

    // returns true if cursor is outside of pie, false otherwise
    function isOutsideCircle (mouseX, mouseY) {
        var pieCenter = getPieCenter ();
        var dist = (Math.sqrt (Math.pow (mouseX - pieCenter[0], 2) + 
                    Math.pow (mouseY - pieCenter[1], 2)));
        if (dist > (diameter + 20) / 2.0)
            return true;
        else 
            return false;
    }

    // hide tooltip when mouse moves away from pie slice
    function unhighlight (mouseX, mouseY) {
        //thisX2Chart.DEBUG && console.log ('jqthisX2Chart.plotDataUnhighlight');

        var tooltip = thisX2Chart._chartTooltip$;

        if ($(tooltip).is (':visible') &&
            mouseX !== null && mouseY !== null &&
            (isOutsideCircle (mouseX, mouseY) || 
             (isInsideToolTip && isOutsideSlice (mouseX, mouseY)))) {

            thisX2Chart.DEBUG && console.log ('hiding tooltip');

            //thisX2Chart._chartTooltip$.empty ();
            $(tooltip).hide ();
        }
    }

    thisX2Chart._chart$.unbind ('mousemove');
    thisX2Chart._chart$.bind ('mousemove', function (event) {
        //thisX2Chart.DEBUG && console.log ('mouse');
        var mouseX = event.pageX;
        var mouseY = event.pageY;
        unhighlight (mouseX, mouseY);
    });

    thisX2Chart._chartTooltip$.unbind ('mousemove');
    thisX2Chart._chartTooltip$.bind ('mousemove', function (event) {
        //thisX2Chart.DEBUG && console.log ('chart-tooltip mouse');
        isInsideToolTip = true;
        var mouseX = event.pageX;
        var mouseY = event.pageY;
        unhighlight (mouseX, mouseY);
    });

    var isInsideToolTip = false;
    thisX2Chart._chartTooltip$.unbind ('mouseout');
    thisX2Chart._chartTooltip$.bind ('mouseout', function (event) {
        isInsideToolTip = false;
    });

    thisX2Chart._chartContainer$.unbind ('mouseleave');
    thisX2Chart._chartContainer$.bind ('mouseleave', function (event) {
        thisX2Chart._chartTooltip$.hide ();
    });
};

/*
This can replace the plotData method of the X2Chart prototype.
Plots event data retrieved by thisX2Chart.getEventsBetweenDates ().
If two metrics are selected by the user, thisX2Chart.plotData will plot two lines.
Parameter:
    args - a dictionary containing optional parameters.
        redraw - an optional parameter which can be contained in args. If set to
            true, the chart will be cleared before the plotting.
*/
X2Chart.plotLineData = function (args /* optional */) {
    var thisX2Chart = this;
    if (typeof args !== 'undefined') {
        redraw = typeof args['redraw'] === 'undefined' ?
            false : args['redraw'];
    } else { // defaults
        redraw = false;
    }

    // retrieve user selected values
    var binSize = 
        thisX2Chart._getBinSizeFromButtonElem (
            thisX2Chart._binSizeButtonSet$.find ('a.disabled-link'));
    var tsDict = thisX2Chart.getStartEndTimestamp ();
    var startTimestamp = tsDict['startTimestamp'];
    var endTimestamp = tsDict['endTimestamp'];

    thisX2Chart.DEBUG && console.log ('thisX2Chart.plotData: startTimestamp, endTimestamp = ');
    thisX2Chart.DEBUG && console.log ([startTimestamp, endTimestamp]);

    // default settings
    var showMarker = false;
    var tickInterval = null;

    /* 
    graph at least 1 interval, hour bin size is a special case since it is the only
    case for which there are multiple bins when start and and timestamp are equal.
    */
    if (binSize === 'hour-bin-size')
        endTimestamp = thisX2Chart.shiftTimeStampOneInterval (endTimestamp, binSize, true);

    var min = startTimestamp;
    var max = endTimestamp;

    thisX2Chart.DEBUG && console.log ('min = ' + min);
    thisX2Chart.DEBUG && console.log ('max = ' + max);

    var countDict = thisX2Chart.countBins (min, max);

    // single bin is a special case, isolated point should be shown
    var onlyOneBin = thisX2Chart.checkOnlyOneBin (binSize, countDict);
    if (onlyOneBin) {
        thisX2Chart.DEBUG && console.log ('onlyOneBin = true');
        min = thisX2Chart.shiftTimeStampOneInterval (min, binSize, false);
        max = thisX2Chart.shiftTimeStampOneInterval (max, binSize, true);
        countDict = thisX2Chart.countBins (min, max); // recount for new interval
    } else {
        thisX2Chart.DEBUG && console.log ('onlyOneBin = false');
    }

    // determine label format and number of ticks based on data
    var ticksDict = thisX2Chart.getTicks (
        min, max, binSize, countDict);
    var ticks = ticksDict['ticks'];
    var labelFormat = ticksDict['labelFormat'];
    thisX2Chart.DEBUG && console.log ('ticks = ');
    thisX2Chart.DEBUG && console.log (ticks);
    showMarker = thisX2Chart.getShowMarkerSetting (binSize, countDict);

    thisX2Chart.DEBUG && console.log ('min = ' + min);
    thisX2Chart.DEBUG && console.log ('max = ' + max);

    /*if (ticks[0][0] < min)
        min = ticks[0][0]
    if (ticks[ticks.length - 1][0] > max)
        max = ticks[ticks.length - 1][0];*/

    // get user selected metrics
    var types;
    types = thisX2Chart._firstMetric$.val ();

    // get chartData for each user specified type
    var color = []; 
    var chartData = [];
    thisX2Chart.DEBUG && console.log ('types = ' + types);
    if (types === null) {
        chartData.push ([]);
    } else {
        var type;
        for (var i in types) {
            type = types[i];
            thisX2Chart.DEBUG && console.log ('type = ' + type);
            dataDict = thisX2Chart.groupChartData (
                thisX2Chart.eventData, binSize, type, showMarker, onlyOneBin);
            chartData.push (dataDict['chartData']);
        }
    }
    thisX2Chart.DEBUG && console.log ('metricOptionsColors = ');
    thisX2Chart.DEBUG && console.log (thisX2Chart.metricOptionsColors);

    // if no chartData exists of specified type, don't plot it
    var noChartData = true;
    for (var i in chartData) {
        if (chartData[i].length === 0) {
            chartData[i] = [null];
        } else if ((chartData[i].length === 1 && chartData[i][1] !== 0) ||
                   chartData[i].length !== 1) {
            noChartData = false;
        }
    }

    thisX2Chart.DEBUG && console.log ('noChartData = ' + noChartData);
    thisX2Chart.DEBUG && console.log ('chartData = ');
    thisX2Chart.DEBUG && console.debug ($.extend (true, [], chartData));

    // pad left and right side of data with entries having y value equal to 0
    if (!onlyOneBin &&
        thisX2Chart._firstMetric$.val () !== null) {
        thisX2Chart.DEBUG && console.log ('filling chart data');
        for (var i in chartData) {
            chartData[i] = thisX2Chart.fillZeroEntries (
                min, max, binSize, chartData[i], showMarker);
        }
    }

    thisX2Chart.DEBUG && console.log ('filled chartData = ');
    thisX2Chart.DEBUG && console.debug ($.extend (true, [], chartData));

    thisX2Chart.DEBUG && console.log ('min = ' + min);
    thisX2Chart.DEBUG && console.log ('max = ' + max);

    jqplotConfig = thisX2Chart.getJqplotConfig ({
        'ticks': ticks, 
        'min': min, 
        'max': max, 
        'showMarker': showMarker, 
        'color': color,
        'showXTicks': true
    });

    if (noChartData) {
        jqplotConfig.axes.yaxis['max'] = 1;
        jqplotConfig.axes.yaxis['numberTicks'] = 2;
    }

    thisX2Chart.preJqplotPlotLineData (types);

    for (var i in types) {
        color.push (thisX2Chart.metricOptionsColors[types[i]]); // color of line 
    }

    // plot chartData
    thisX2Chart.feedChart = 
        $.jqplot (thisX2Chart.chartType + '-chart-' + thisX2Chart.widgetUID, chartData, 
        jqplotConfig);

    thisX2Chart.DEBUG && console.log ('chartData.length = ' + chartData.length);
    thisX2Chart.DEBUG && console.log ('labelFormat = ' + labelFormat);

    if (redraw) {
        thisX2Chart.feedChart.replot (); // clear previous plot and plot again
    }

    // used to display type labels in tooltips and legend
    var typesTextDict = {};
    var typesTextArr = [];
    thisX2Chart._firstMetric$.find (":selected").each (
        function () {

        typesTextDict[$(this).val ()] = $(this).html ();
        typesTextArr.push ($(this).html ());
    });

    thisX2Chart._chartLegend$.find ('tbody').empty ();
    if (types !== null) {
        thisX2Chart.setupTooltipBehavior (labelFormat, showMarker, chartData, typesTextArr);
        thisX2Chart.buildChartLegend (types, typesTextDict, color);
    }

    if (!Modernizr.canvas) {
        thisX2Chart.resizeChartNoCanvas ();
    }

};

/*
Sets up event functions to display tooltips on point mouse over.
Parameters:
    labelFormat - format string accepted by thisX2Chart.getTooltipFormattedLabel
    showMarker - boolean
    chartData - array of arrays
    typesText - metric names shown in tooltips
*/
X2Chart.setupLineTooltipBehavior = function (
    labelFormat, showMarker, chartData, typesText) {

    var thisX2Chart = this;

    thisX2Chart.DEBUG && console.log ('setupTooltipBehavior');

    // bypass bug in jqplot
    for (var i in thisX2Chart.feedChart.series) {
        thisX2Chart.feedChart.series[i].highlightMouseOver = true;
    }

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
    thisX2Chart._chart$.unbind ('jqplotDataHighlight');
    thisX2Chart._chart$.bind ('jqplotDataHighlight', 
        function (ev, seriesIndex, pointIndex, data) {
            thisX2Chart.DEBUG && console.log ('jqthisX2Chart.plotDataHighlight');
            thisX2Chart.DEBUG && console.log ([ev, seriesIndex, pointIndex, data]);
            thisX2Chart.DEBUG && console.log ('showmarker = ' + showMarker);

            var chartLeft = $(this).offset ().left;
            var chartTop = $(this).offset ().top;
            var    pointX = thisX2Chart.feedChart.axes.xaxis.u2p (data[0]);
            var    pointY = thisX2Chart.feedChart.axes.yaxis.u2p (data[1]);
            var tooltip = thisX2Chart._chartTooltip$;

            thisX2Chart.DEBUG && console.log (chartLeft, chartTop, pointX, pointY);

            // save for calculating distance between mouse and point
            pointXPrev = chartLeft + pointX;
            pointYPrev = chartTop + pointY;

            thisX2Chart.DEBUG && console.log ('data[0] = ' + data[0]);
            thisX2Chart.DEBUG && console.log ('chartData[0][0] = ' + chartData[0][0][0]);

            var isLastPoint = false;
            var isFirstPoint = false;
            if (data[0] === chartData[0][chartData[0].length - 1][0]) {
                thisX2Chart.DEBUG && console.log ('isLastPoint');
                isLastPoint = true;
            } else if (data[0] === chartData[0][0][0]) {
                thisX2Chart.DEBUG && console.log ('isFirstPoint');
                isFirstPoint = true;
            }

            // insert tooltip text
            thisX2Chart.DEBUG && console.log ('thisX2Chart.getTooltipFormattedLabel ret: ' + 
                thisX2Chart.getTooltipFormattedLabel (
                    labelFormat, data[0], isFirstPoint, isLastPoint));

            $(tooltip).html ($('<span>', {
                'class': 'chart-tooltip-date',
                text: thisX2Chart.getTooltipFormattedLabel (
                    labelFormat, data[0], isFirstPoint, isLastPoint)
            }));
            $(tooltip).append ($('<br>'));

            thisX2Chart.DEBUG && console.log ('seriesIndex = ' + seriesIndex);

            // don't show types with 0 y values 
            if (data[1] !== 0) {

                for (var i = 0; i < thisX2Chart.feedChart.series.length; ++i) {
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

            // determine where to place the tooltip
            var marginLeft, marginRight;
            marginLeft = 11;
            marginTop = 11;
            if (pointXPrev + auxlib.pxToInt ($(tooltip).css ('width')) >
                chartLeft + auxlib.pxToInt (thisX2Chart._chart$.
                    css ('width'))) {
                thisX2Chart.DEBUG && console.log ('xoverflow');
                marginLeft = 
                    - (auxlib.pxToInt ($(tooltip).css ('width')) + marginLeft);
            }
            if (pointYPrev + auxlib.pxToInt ($(tooltip).css ('height')) >
                chartTop + auxlib.pxToInt (thisX2Chart._chart$.
                    css ('height'))) {
                thisX2Chart.DEBUG && console.log ('yoverflow');
                marginTop = 
                    - (auxlib.pxToInt ($(tooltip).css ('height')) + marginTop);
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
        //thisX2Chart.DEBUG && console.log ('jqthisX2Chart.plotDataUnhighlight');

        var tooltip = thisX2Chart._chartTooltip$;

        if ($(tooltip).is (':visible') &&
            mouseX !== null && mouseY !== null &&
            pointXPrev !== null && pointYPrev !== null &&
            distance (
                mouseX, mouseY, pointXPrev, pointYPrev) > 12) {

            thisX2Chart.DEBUG && console.log ('hiding tooltip');

            //thisX2Chart._chartTooltip$.empty ();
            $(tooltip).hide ();
        }

    }

    thisX2Chart._chart$.unbind ('mousemove');
    thisX2Chart._chart$.bind ('mousemove', function (event) {
        //thisX2Chart.DEBUG && console.log ('mouse');
        var mouseX = event.pageX;
        var mouseY = event.pageY;
        unhighlight (mouseX, mouseY);
    });

    thisX2Chart._chartTooltip$.unbind ('mousemove');
    thisX2Chart._chartTooltip$.bind ('mousemove', function (event) {
        //thisX2Chart.DEBUG && console.log ('chart-tooltip mouse');
        var mouseX = event.pageX;
        var mouseY = event.pageY;
        unhighlight (mouseX, mouseY);
    });

    thisX2Chart._chart$.unbind ('mouseout');
    thisX2Chart._chart$.bind ('mouseout', function (event) {
        thisX2Chart._chartTooltip$.hide ();
    });

};

/*
This can replace the getJqplotConfig method of the X2Chart prototype.
*/
X2Chart.getJqplotLineConfig = function (argsDict) {
    var ticks = argsDict['ticks'];
    var min = argsDict['min'];
    var max = argsDict['max'];
    var showMarker = argsDict['showMarker'];
    var color = argsDict['color'];
    var showXTicks = argsDict['showXTicks'];

    var jqplotConfig = {
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
            show: false
        },
        grid: {
            drawGridLines: false,
            gridLineColor: 'rgba(0,0,0,0)',
            borderColor: '#999',
            borderWidth: 1,
            background: 'rgba(0,0,0,0)',
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
                numberTicks: 3,
                tickOptions: {formatString: '%d'},
                min: 0
            }
        },
        highlighter: {
            show: true,
            showTooltip: false,
            sizeAdjust: 2.5

        }
    };
    return jqplotConfig;
};

/*
This can replace the groupChartData method of the X2Chart prototype.
Returns an array which can be passed to jqplot. Each entry in the array corresponds
to the number of events of a given type and at a certain time (hour, day, week, or
month depending on the bin size)
Parameters:
    thisX2Chart.eventData - an array set by getEventsBetween
    binSize - a string
    type - a string. The type of event that will get plotted.
*/
X2Chart.groupLineChartData = function (
    eventData, binSize, type, showMarker, onlyOneBin) { 
    var thisX2Chart = this;

    var chartData = [];

    thisX2Chart.DEBUG && console.log ('thisX2Chart.eventData = ');
    thisX2Chart.DEBUG && console.log (thisX2Chart.eventData);

    // group chart data into bins and keep count of the number of entries in each bin
    switch (binSize) {
        case 'hour-bin-size':
            var hour, day, month, year, evt, dateString, timestamp, count;
            for (var i in thisX2Chart.eventData) {
                evt = thisX2Chart.eventData[i];
                count = evt['count'] === '0' ? 1 : parseInt (evt['count'], 10);
                if (thisX2Chart.chartDataFilter (evt, type)) continue;
                /*if ((!(type === 'any' || type === '') && evt['type'] !== type) ||
                    (type === '' && evt['type'] !== null)) continue;*/
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
            for (var i in thisX2Chart.eventData) {
                evt = thisX2Chart.eventData[i];
                count = evt['count'] === '0' ? 1 : parseInt (evt['count'], 10);
                thisX2Chart.DEBUG && console.log (count);
                if (thisX2Chart.chartDataFilter (evt, type)) continue;
                /*if ((!(type === 'any' || type === '') && evt['type'] !== type) ||
                    (type === '' && evt['type'] !== null)) continue;*/
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
            var week, year, evt, dateString, timestamp, date, day, count;
            for (var i in thisX2Chart.eventData) {
                evt = thisX2Chart.eventData[i];
                count = evt['count'] === '0' ? 1 : parseInt (evt['count'], 10);
                if (thisX2Chart.chartDataFilter (evt, type)) continue;
                /*if ((!(type === 'any' || type === '') && evt['type'] !== type) ||
                    (type === '' && evt['type'] !== null)) continue;*/
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
                    timestamp -= day * X2Chart.MSPERDAY;
                    timestamp = thisX2Chart.roundForDaylightSavings (timestamp);

                    chartData.push ([timestamp, count]);
                }
            }
            break;
        case 'month-bin-size':
            var month, year, evt, dateString, timestamp, count;
            for (var i in thisX2Chart.eventData) {
                evt = thisX2Chart.eventData[i];
                count = evt['count'] === '0' ? 1 : parseInt (evt['count'], 10);
                if (thisX2Chart.chartDataFilter (evt, type)) continue;
                /*if ((!(type === 'any' || type === '') && evt['type'] !== type) ||
                    (type === '' && evt['type'] !== null)) continue;*/
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

    chartData = thisX2Chart.fillChartDataGaps (chartData, binSize, onlyOneBin, showMarker);

    return {
        chartData: chartData
    };
};

/************************************************************************************
Instance Methods
************************************************************************************/

X2Chart.prototype.setDefaultSettings = function () {}; // override in child prototype

X2Chart.prototype.tearDown = function () { 
    var thisX2Chart = this;
    $(window).unbind ('resize.' + this.chartType, x2[thisX2Chart.chartType].windowResizeFunction);
}

X2Chart.prototype.updatePieChartEventCount = function (eventCount) { 
    var thisX2Chart = this;

    var countContainer = thisX2Chart._pieChartCountContainer$;
    $(countContainer).find ('.pie-chart-count').text (eventCount);
};

X2Chart.prototype.getMetricTypesText = function () {
    var thisX2Chart = this;

    var metricTypesText = {};
    thisX2Chart._firstMetric$.children ().each (function () {
        metricTypesText[$(this).val ()] = $(this).html ();
    });
    return metricTypesText;
};

X2Chart.prototype.getMetricTypes = function () { 
    var thisX2Chart = this;

    var metricTypes = [];
    thisX2Chart._firstMetric$.children ().each (function () {
        metricTypes.push([$(this).val (), $(this).html ()]);
    });
    return metricTypes;
};

X2Chart.prototype.setChartSubtype = function (chartSubtype, plot, uiSetUp, force) {
    var thisX2Chart = this;

    thisX2Chart.DEBUG && console.log ('setChartSubtype: chartSubtype = ' + chartSubtype);

    plot = typeof plot === 'undefined' ? true : plot;
    uiSetUp = typeof uiSetUp === 'undefined' ? true : uiSetUp;
    force = typeof force === 'undefined' ? true : force;

    var diffChartSubtype = chartSubtype !== thisX2Chart.chartSubtype;
    if (!diffChartSubtype && !force) return;

    thisX2Chart.chartSubtype = chartSubtype;
    
    if (chartSubtype === 'line') {
        if (diffChartSubtype) thisX2Chart.pieChartTearDown (uiSetUp);
        this.prototype.plotData = X2Chart.plotLineData;
        this.prototype.getJqplotConfig = X2Chart.getJqplotLineConfig;
        this.prototype.groupChartData = X2Chart.groupLineChartData;
        this.prototype.setupTooltipBehavior = X2Chart.setupLineTooltipBehavior;
        thisX2Chart.postLineChartSetUp ();
    } else if (chartSubtype === 'pie') {
        if (diffChartSubtype) thisX2Chart.lineChartTearDown ();
        this.prototype.plotData = X2Chart.plotPieData;
        this.prototype.getJqplotConfig = X2Chart.getJqplotPieConfig;
        this.prototype.groupChartData = X2Chart.groupPieChartData;
        this.prototype.setupTooltipBehavior = X2Chart.setupPieTooltipBehavior;
        thisX2Chart.postPieChartSetUp (uiSetUp);
    }

    if (plot) thisX2Chart.plotData ({redraw: true});
};

/*
Override in child prototype
*/
X2Chart.prototype.postLineChartSetUp = function () {
};

X2Chart.prototype.lineChartTearDown = function () {
    var thisX2Chart = this;
    thisX2Chart._chart$.unbind ('jqplotDataHighlight');
    thisX2Chart._chart$.unbind ('mousemove');
    thisX2Chart._chartTooltip$.unbind ('mousemove');
    thisX2Chart._chart$.unbind ('mouseout');
    thisX2Chart._pieChartCountContainer$.show ();
    thisX2Chart.postLineChartTearDown ();
};

/*
Override in child prototype
*/
X2Chart.prototype.postLineChartSetUp = function () {
};

/*
Override in child prototype
*/
X2Chart.prototype.postLineChartTearDown = function () {
};

X2Chart.prototype.pieChartTearDown = function (uiSetUp) {
    var thisX2Chart = this;
    thisX2Chart._chart$.unbind ('jqplotDataHighlight');
    thisX2Chart._chart$.unbind ('mousemove');
    thisX2Chart._chartTooltip$.unbind ('mousemove');
    thisX2Chart._chartTooltip$.unbind ('mouseout');
    thisX2Chart._chartContainer$.unbind ('mouseleave');
    thisX2Chart._pieChartCountContainer$.hide ();
    thisX2Chart.postPieChartTearDown (uiSetUp);
};

/*
Override in child prototype
*/
X2Chart.prototype.postPieChartSetUp = function () {
};

/*
Override in child prototype
*/
X2Chart.prototype.postPieChartTearDown = function () {
};

/*
Used by pie chart to count the number of events between the start and end date
*/
X2Chart.prototype.getEventCount = function (chartData) {
    var totalCount = 0;
    for (var i in chartData) {
        totalCount += chartData[i][1];
    }
    return totalCount;
};

/*
Used by pie chart to convert data from event counts to percents.
*/
X2Chart.prototype.convertCountsToPercents = function (chartData, totalCount) {
    if (totalCount === 0) return chartData;
    for (var i in chartData) {
        chartData[i][1] = (chartData[i][1] / totalCount) * 100;
    }
    return chartData;
};

/*
Call setup functions for chart settings ui elements.
*/
X2Chart.prototype.setUpSettingsUI = function () {
    var thisX2Chart = this;

    if (!thisX2Chart.suppressChartSettings) {
        thisX2Chart.setUpChartSettings ();
    }

    thisX2Chart.setUpBinSizeSelection ();
    thisX2Chart.setUpDatepickers ();
    thisX2Chart.setUpMetricSelection ();

    if (!thisX2Chart.suppressDateRangeSelector) {
        thisX2Chart.setUpDateRangeSelector ();
    }
};

/*
Set up initial chart settings and request chart data
*/
X2Chart.prototype.start = function () {
    var thisX2Chart = this;

    thisX2Chart.setDefaultSettings ();

    thisX2Chart.setSettingsFromCookie (); // fill settings with saved settings
    
    thisX2Chart.getEventsBetweenDates (false); // populate default graph
};

/*
Checks a subset of options in a jquery multiselect widget.
Parameters:
    selector - used to retrieve the multiselect element
    settings - an array of strings. Each string should be the value of an option in
        the select element
*/
X2Chart.prototype.applyMultiselectSettings = function (selector, settings) {
    var thisX2Chart = this;

    thisX2Chart.DEBUG && console.log ('applyMultiselectSettings ' + selector);

    $(selector).find ('option').each (function () {
        $(this).removeAttr ('selected');
    });
    $(selector).multiselect2 ('refresh');
    if (settings !== 'none') {
        thisX2Chart.DEBUG && console.log ('setting settings obj');
        if (typeof settings === 'string')
            settings = settings.split (',');
        thisX2Chart.DEBUG && console.log ('settings = ');
        thisX2Chart.DEBUG && console.log (settings);
        for (var i in settings) {
            $(selector).find ('option').each (function () {
                if ($(this).val () === settings[i]) {
                    $(this).attr ('selected', 'selected');
                }
            });
            $(selector).multiselect2 ('refresh');
        }
        thisX2Chart.DEBUG && console.log ('done applyMultiselectSettings');
    }
};

/*
Instantiates multiselect widgets for each of the filter types defined in the filterTypes
property. Also binds event functions to those widgets.
*/
X2Chart.prototype.setUpFilters = function () {
    var thisX2Chart = this;

    /*
    Event function bound to each of the filter multiselect widgets. Sets the filter property
    and replots the chart.
    */
    function multiselectCloseHandler (element, possibleVals, filterName) {
        var checkedValues = $(element).val ();
        var settingVal = (checkedValues === null) ? 'none' : checkedValues;
        var checkedValues = (checkedValues === null) ? [] : checkedValues;
        
        var filterVal = $(possibleVals).not (checkedValues);

        thisX2Chart.saveChartSetting (filterName, settingVal)

        thisX2Chart.DEBUG && console.log ('checkedValues = ');
        thisX2Chart.DEBUG && console.log (checkedValues);

        thisX2Chart.DEBUG && console.log ('close multiselect');
        thisX2Chart.filters[filterName] = filterVal;
        thisX2Chart.plotData ({redraw: true});
        if (!thisX2Chart.suppressChartSettings) {
            thisX2Chart.setChartSettingName ('');  
        }

    }

    for (var i in thisX2Chart.filterTypes) {
        switch (thisX2Chart.filterTypes[i]) {
            case 'eventsFilter':
                thisX2Chart._eventsChartFilter$.multiselect2 (
                    $.extend ({}, thisX2Chart._multiSelectOptions, { 
                        'selectedText': '# ' + this.translations['event type(s) selected'] }));
                thisX2Chart._eventsChartFilter$.multiselect2 ('checkAll');

                thisX2Chart.filters['eventsFilter'] = [];
                thisX2Chart._eventsChartFilter$.bind (
                    "multiselect2close", function (evt, ui) {
                    multiselectCloseHandler (
                        $(this), thisX2Chart.eventTypes, 'eventsFilter');
                });

                break;
            case 'usersFilter':
                this._usersChartFilter$.multiselect2 (
                    $.extend ({}, thisX2Chart._multiSelectOptions, { 
                        'selectedText': '# ' + this.translations['user(s) selected'] }));
                this._usersChartFilter$.multiselect2 ('checkAll');

                thisX2Chart.filters['usersFilter'] = [];

                thisX2Chart._usersChartFilter$.bind (
                    "multiselect2close", function (evt, ui) {
                    multiselectCloseHandler (
                        $(this), thisX2Chart.userNames, 'usersFilter');
                });
                break;
            case 'socialSubtypesFilter':
                thisX2Chart._socialSubtypesChartFilter$.multiselect2 (
                    $.extend ({}, thisX2Chart._multiSelectOptions, { 
                        'selectedText': '# ' + this.translations['event subtype(s) selected'] }));
                thisX2Chart._socialSubtypesChartFilter$.
                    multiselect2 ('checkAll');

                thisX2Chart.filters['socialSubtypesFilter'] = [];

                thisX2Chart._socialSubtypesChartFilter$.bind (
                    "multiselect2close", function (evt, ui) {
                    multiselectCloseHandler (
                        $(this), thisX2Chart.socialSubtypes, 'socialSubtypesFilter');
                });
                break;
            case 'visibilityFilter':
                // initialize dropdown checklist
                thisX2Chart._visibilityChartFilter$.multiselect2 (
                    $.extend ({}, thisX2Chart._multiSelectOptions, { 
                        'selectedText': '# ' + 
                            this.translations['visibility setting(s) selected'] }));
                thisX2Chart._visibilityChartFilter$.
                    multiselect2 ('checkAll');

                thisX2Chart.filters['visibilityFilter'] = [];

                thisX2Chart._visibilityChartFilter$.bind (
                    "multiselect2close", function (evt, ui) {
                    multiselectCloseHandler (
                        $(this), thisX2Chart.visibilityTypes, 'visibilityFilter');
                });
                break;
            default:
                throw new Error ('setUpFilters: default on switch');
        }

    }

    // setup filter selector behavior

    thisX2Chart._showChartFiltersButton$.click (function () {
        thisX2Chart.DEBUG && console.log ('show-chart-filters click');
        $(this).hide ();
        thisX2Chart._hideChartFiltersButton$.show ();
        thisX2Chart._chartContainer$.find ('.chart-filters-container').
            slideDown (200);
    });

    thisX2Chart._hideChartFiltersButton$.click (function () {
        thisX2Chart.DEBUG && console.log ('show-chart-filters click');
        $(this).hide ();
        thisX2Chart._showChartFiltersButton$.show ();
        thisX2Chart._chartContainer$.find ('.chart-filters-container').
            slideUp (200);
    });

    thisX2Chart.bindFilterEvents ();

};

/*
Binds event functions related to filter settings ui element
*/
X2Chart.prototype.bindFilterEvents = function () {
    var thisX2Chart = this;
    thisX2Chart._showChartFiltersButton$.click (function () {
        thisX2Chart.DEBUG && console.log ('show-chart-filters click');
        $(this).hide ();
        thisX2Chart._hideChartFiltersButton$.show ();
        thisX2Chart._chartContainer$.find ('.chart-filters-container').
            slideDown (200);
    });

    thisX2Chart._hideChartFiltersButton$.click (function () {
        thisX2Chart.DEBUG && console.log ('show-chart-filters click');
        $(this).hide ();
        thisX2Chart._showChartFiltersButton$.show ();
        thisX2Chart._chartContainer$.find ('.chart-filters-container').
            slideUp (200);
    });
};

/*
Rebinds event functions related to filter settings ui element
*/
X2Chart.prototype.rebindFilterEvents = function () {
    var thisX2Chart = this;
    thisX2Chart._hideChartFiltersButton$.unbind ('click');
    thisX2Chart._showChartFiltersButton$.unbind ('click');
    thisX2Chart.bindFilterEvents ();
};

X2Chart.prototype._getBinSizeFromButtonElem = function (buttonElem$) {
    var thisX2Chart = this;
   thisX2Chart.DEBUG && console.log ('buttonElem$ = ');
    thisX2Chart.DEBUG && console.log (buttonElem$);
    thisX2Chart.DEBUG && console.log (buttonElem$.attr ('id').replace (this.chartType + '-', '').
        replace (/-size-.*$/, '-size'));

    return buttonElem$.attr ('id').replace (this.chartType + '-', '').
        replace (/-size-.*$/, '-size');
};


/*
Ask server for all events between user specified dates.
Replot data on server response.
Parameters:
    redraw - Boolean, determines whether thisX2Chart.plotData will clear the plot before 
        drawing
*/
X2Chart.prototype.getEventsBetweenDates = function (redraw) {
    var thisX2Chart = this;
    var binSize = this._getBinSizeFromButtonElem (
        thisX2Chart._binSizeButtonSet$.find ('a.disabled-link'));
    var tsDict = thisX2Chart.getStartEndTimestamp ();
    var startTimestamp = tsDict['startTimestamp'];
    var endTimestamp = tsDict['endTimestamp'] + X2Chart.MSPERDAY - 1000;

    thisX2Chart.DEBUG && console.log (
        'getting events between ' + startTimestamp + ' and ' + endTimestamp);

    var data = {
        'startTimestamp': startTimestamp / 1000,
        'endTimestamp': endTimestamp / 1000
    }

    if (this.actionParams) {
        $.extend (data, this.actionParams);
    }

    if (this.chartData) {
        thisX2Chart.eventData = this.chartData;
        thisX2Chart.plotData ({'redraw': redraw});
        this.chartData = null;
        return;
    }

    thisX2Chart.DEBUG && console.log ('calling ' + this.getChartDataActionName + ' with params ');
    thisX2Chart.DEBUG && console.debug (data);

    $.ajax ({
        'url': this.getChartDataActionName,
        'data': data,
        'success': function (data) {
            thisX2Chart.eventData = JSON.parse (data);
            thisX2Chart.DEBUG && console.log ('ajax ret, thisX2Chart.eventData = ');
            thisX2Chart.DEBUG && console.debug (thisX2Chart.eventData);

            thisX2Chart.plotData ({'redraw': redraw});
        }
    });
};

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
X2Chart.prototype.getZeroEntriesBetween = function (
    timestamp1, timestamp2, interval, inclusiveBegin , inclusiveEnd , showMarker) {
    var thisX2Chart = this;

    if (timestamp2 <= timestamp1) {
        return [];
    }

    var entries = [];

    if (inclusiveBegin)
        entries.push ([timestamp1, 0]);

    switch (interval) {
        case 'hour':
            if (!showMarker) {
                var intermediateTimestamp1 = timestamp1;
                var intermediateTimestamp2 = timestamp2;
                intermediateTimestamp1 += X2Chart.MSPERHOUR;
                intermediateTimestamp2 -= X2Chart.MSPERHOUR;
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
                    intermediateTimestamp += X2Chart.MSPERHOUR;
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
            if (!showMarker) {
                var intermediateTimestamp1 = timestamp1;
                var intermediateTimestamp2 = timestamp2;
                intermediateTimestamp1 += X2Chart.MSPERDAY;
                intermediateTimestamp2 -= X2Chart.MSPERDAY;
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
                    intermediateTimestamp += X2Chart.MSPERDAY;
                    intermediateTimestamp = thisX2Chart.roundForDaylightSavings (intermediateTimestamp);

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
            if (!showMarker) {
                thisX2Chart.DEBUG && console.log ('getZeroEntriesBetween: week: startTimestamp, endTimestamp = ' +
                    timestamp1 + ', ' + timestamp2);
                var intermediateTimestamp1 = 
                    thisX2Chart.getRoundedTimestamp (timestamp1, 'week-bin-size', false);
                //var intermediateTimestamp1 = timestamp1;
                var intermediateTimestamp2 = timestamp2;
                intermediateTimestamp1 += X2Chart.MSPERWEEK;
                intermediateTimestamp2 -= X2Chart.MSPERWEEK;
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
                thisX2Chart.DEBUG && console.log ('thisX2Chart.fillZeroEntries: week, timestamp1 = ' +
                             timestamp1);
                thisX2Chart.DEBUG && console.log ('timestamp2 = ' + timestamp2);
                var rounded = false;
                var intermediateTimestamp = 
                    thisX2Chart.getRoundedTimestamp (timestamp1, 'week-bin-size', false);
                thisX2Chart.DEBUG && console.log (
                    'thisX2Chart.fillZeroEntries: week, intermediateTimestamp = ' +
                     intermediateTimestamp);
                if (intermediateTimestamp !== timestamp1) {
                    thisX2Chart.DEBUG && console.log ('setting rounded to true');
                    rounded = true;
                }
                while (true) {
                    if (!rounded) {
                        intermediateTimestamp += X2Chart.MSPERWEEK;
                        intermediateTimestamp = thisX2Chart.roundForDaylightSavings (
                            intermediateTimestamp);
                    }
                    rounded = false;

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
};

/*
Fills in each gap in the chart data with (x, 0) entries spaced at appropriate intervals.
*/
X2Chart.prototype.fillChartDataGaps = function (
    chartData, binSize, onlyOneBin, showMarker) {

    var thisX2Chart = this;

    var chartData = chartData.slice ();
    chartData.reverse ();

    var startTimestamp = (thisX2Chart._chartDatepickerFrom$.
        datepicker ('getDate').valueOf ());

    if (onlyOneBin && chartData.length === 0) {
        chartData.push ([startTimestamp, 0]);
        return chartData;
    }

    // shift position of first bin forward to starting timestamp
    if ((binSize === 'week-bin-size' || binSize === 'month-bin-size') &&
        chartData.length !== 0 && chartData[0][0] < startTimestamp) 
        chartData[0][0] = startTimestamp;

    // insert entries with y value equal to 0 into chartData at the specified interval
    var chartDataIndex = 0;
    var timestamp1, timestamp2, arr1, arr2, intermArr;
    while (chartData.length !== 0 && chartDataIndex < chartData.length - 1) {

        timestamp1 = chartData[chartDataIndex][0];
        timestamp2 = chartData[chartDataIndex + 1][0];

        switch (binSize) {
            case 'hour-bin-size':
                arr1 = chartData.slice (0, chartDataIndex + 1);
                arr2 = chartData.slice (chartDataIndex + 1, chartData.length);
                intermArr = thisX2Chart.getZeroEntriesBetween (
                    timestamp1, timestamp2, 'hour', false, false, showMarker);
                if (intermArr.length !== 0)
                    chartData = arr1.concat (intermArr, arr2);
                chartDataIndex += intermArr.length + 1;
                break;
            case 'day-bin-size':
                arr1 = chartData.slice (0, chartDataIndex + 1);
                arr2 = chartData.slice (chartDataIndex + 1, chartData.length);
                intermArr = thisX2Chart.getZeroEntriesBetween (
                    timestamp1, timestamp2, 'day', false, false, showMarker);
                if (intermArr.length !== 0)
                    chartData = arr1.concat (intermArr, arr2);
                chartDataIndex += intermArr.length + 1;
                break;
            case 'week-bin-size':
                arr1 = chartData.slice (0, chartDataIndex + 1);
                arr2 = chartData.slice (chartDataIndex + 1, chartData.length);
                intermArr = thisX2Chart.getZeroEntriesBetween (
                    timestamp1, timestamp2, 'week', false, false, showMarker);
                if (intermArr.length !== 0)
                    chartData = arr1.concat (intermArr, arr2);
                chartDataIndex += intermArr.length + 1;
                break;
            case 'month-bin-size':
                arr1 = chartData.slice (0, chartDataIndex + 1);
                arr2 = chartData.slice (chartDataIndex + 1, chartData.length);
                intermArr = thisX2Chart.getZeroEntriesBetween (
                    timestamp1, timestamp2, 'month', false, false, showMarker);
                if (intermArr.length !== 0)
                    chartData = arr1.concat (intermArr, arr2);
                chartDataIndex += intermArr.length + 1;
                break;
        }

    }

    return chartData;
};


/*
Should be overridden in child prototypes to filter chart data
*/
X2Chart.prototype.chartDataFilter = function (dataPoint, type) {
    return false;
};

/*
Gets replaced with respective line or pie functions based on chart subtype.
*/
X2Chart.prototype.groupChartData = function () {};

/*
Helper function for jqplot used to widen the date range if the user selected
begin and end dates are the same.
*/
X2Chart.prototype.shiftTimeStampOneInterval = function (timestamp, binSize, forward) {
    var thisX2Chart = this;
    var newTimestamp = timestamp;
    switch (binSize) {
        case 'hour-bin-size':
            if (forward) {
                newTimestamp += (X2Chart.MSPERDAY);
                newTimestamp = thisX2Chart.roundForDaylightSavings (newTimestamp);
                newTimestamp -= X2Chart.MSPERHOUR;
            } else {
                newTimestamp -= X2Chart.MSPERDAY;
            }
            break;
        case 'day-bin-size':
            if (forward)
                newTimestamp += X2Chart.MSPERDAY;
            else
                newTimestamp -= X2Chart.MSPERDAY;
            break;
        case 'week-bin-size':
            if (forward)
                newTimestamp += X2Chart.MSPERWEEK;
            else
                newTimestamp -= X2Chart.MSPERWEEK;
            break;
        case 'month-bin-size':
            var date = new Date (timestamp);
            var M = date.getMonth () + 1;
            var Y = date.getFullYear ();
            if (forward) {
                M++;
                if (M === 13) {
                    M = 1;
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
};

/*
Calls thisX2Chart.getZeroEntriesBetween () to pad the left and right side of the chart data
with entries having y values equal to 0 and x values increasing by the bin size.
Parameter:
    binSize - user selected, determines x value spacing
    showMarker - if false, a maximum of two entries will be added to the left
        and right side of the chart data.
*/
X2Chart.prototype.fillZeroEntries = function (
    startTimestamp, endTimestamp, binSize, chartData, showMarker) {
    var thisX2Chart = this;

    thisX2Chart.DEBUG && console.log ('thisX2Chart.fillZeroEntries: startTimestamp, endTimestamp = ');
    thisX2Chart.DEBUG && console.log (startTimestamp, endTimestamp);

    var binType = binSize.match (/^[^-]+/)[0];

    if (chartData[0] === null) {
        thisX2Chart.DEBUG && console.log ('data is null, filling zeroes');
        endTimestamp = thisX2Chart.getRoundedTimestamp (endTimestamp, binSize, true);
        if (showMarker) {
            chartData = thisX2Chart.getZeroEntriesBetween (
                startTimestamp, endTimestamp, binType, true, true, showMarker);
        } else {
            chartData = [[startTimestamp, 0], [endTimestamp, 0]];
        }
        return chartData;
    }

    var chartStartTimestamp = chartData[0][0];
    var chartEndTimestamp = chartData[chartData.length - 1][0];
    if (startTimestamp < chartStartTimestamp) {
        //startTimestamp = thisX2Chart.getRoundedTimestamp (startTimestamp, binSize, true);
        var arr = thisX2Chart.getZeroEntriesBetween (
            startTimestamp, chartStartTimestamp, binType, true, false, showMarker);
    
        // splice extraneous data point
        if (!showMarker && arr.length > 2 && arr[1][1] === 0) arr.splice (1, 1);

        if (arr.length !== 0) {
            chartData = arr.concat (chartData);
        }
    }
    if (endTimestamp > chartEndTimestamp) {
        thisX2Chart.DEBUG && console.log ('fillZeroEntries: endTimestamp > chartEndTimestamp');
        endTimestamp = thisX2Chart.getRoundedTimestamp (endTimestamp, binSize, true);
        var arr = thisX2Chart.getZeroEntriesBetween (
            chartEndTimestamp, endTimestamp, binType, false, true, showMarker);

        thisX2Chart.DEBUG && console.log ('arr = ');
        thisX2Chart.DEBUG && console.log (arr);

        // splice extraneous data point
        if (!showMarker && arr.length > 2 && arr[arr.length - 1][1] === 0) 
            arr.splice (arr.length - 2, 1);

        if (arr.length !== 0)
            chartData = chartData.concat (arr);
    }

    return chartData;
};


/*
Returns a dictionary containing the number of hours, days, months, and years between
the start and end timestamps.
*/
X2Chart.prototype.countBins = function (startTimestamp, endTimestamp) {
    var thisX2Chart = this;
    endTimestamp += X2Chart.MSPERDAY - 1;

    var dateRange =
        (endTimestamp + 1) - startTimestamp;

    // get starting and ending months and years
    var startDate = new Date (startTimestamp);
    var startMonth = startDate.getMonth () + 1;
    var startYear = startDate.getFullYear ();
    var endDate = new Date (endTimestamp);
    var endMonth = endDate.getMonth () + 1;
    var endYear = endDate.getFullYear ();

    thisX2Chart.DEBUG && console.log ('countBins:');
    thisX2Chart.DEBUG && console.log ('startTimestamp = ' + startTimestamp);
    thisX2Chart.DEBUG && console.log ('endTimestamp = ' + endTimestamp);
    thisX2Chart.DEBUG && console.log ('startMonth = ' + startMonth);
    thisX2Chart.DEBUG && console.log ('endMonth = ' + endMonth);


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

    weeks = Math.ceil (days / 7);

    return {
        'hours': hours,
        'days': days,
        'weeks': weeks,
        'months': months,
        'years': yearCount + 1
    };
};

X2Chart.prototype.roundForDaylightSavings = function (timestamp) {
    var thisX2Chart = this;
    var date = new Date (timestamp);
    var hours = date.getHours ();
    if (hours === 1) {
        timestamp -= X2Chart.MSPERHOUR;
    } else if (hours === 23) {
        timestamp += X2Chart.MSPERHOUR;
    }

    date = new Date (timestamp);
    hours = date.getHours ();

    if (thisX2Chart.DEBUG && hours !== 0) {
        alert ('Error: roundForDaylightSavings: incorrect rounding');
    }

    return timestamp;
};

// returns timestamp of nearest day at 12am
X2Chart.prototype.getRoundedDayTs = function (timestamp, prev) {
    var thisX2Chart = this;
    var date = new Date (timestamp);
    var M = date.getMonth () + 1;
    var Y = date.getFullYear ();
    var D = date.getDate ();
    var newTimestamp = (new Date (Y, M - 1, D, 0, 0, 0, 0)).getTime ();
    if (!prev) {
        newTimestamp += X2Chart.MSPERDAY;
    }
    return newTimestamp;
};

// returns timestamp of nearest Sunday at 12am
X2Chart.prototype.getRoundedWeekTs = function (timestamp, prev) {
    var thisX2Chart = this;
    var date = new Date (timestamp);
    var M = date.getMonth () + 1;
    var D = date.getDate ();
    var Y = date.getFullYear ();
    var newTimestamp = (new Date (Y, M - 1, D, 0, 0, 0, 0)).getTime ();
    var date = new Date (newTimestamp);
    var day = date.getDay ();
    newTimestamp -= day * X2Chart.MSPERDAY;
    if (!prev && day !== 0) {
        newTimestamp += X2Chart.MSPERWEEK;
    }
    return newTimestamp;
};

// returns timestamp of nearest 1st of month at 12am
X2Chart.prototype.getRoundedMonthTs = function (timestamp, prev) {
    var thisX2Chart = this;
    var date = new Date (timestamp);
    var M = date.getMonth () + 1;
    var Y = date.getFullYear ();
    if (!prev) {
        M++;
        if (M > 12) {
            M = 1;
            Y++;
        }
    }
    var newTimestamp = (new Date (Y, M - 1, 1, 0, 0, 0, 0)).getTime ();
    return newTimestamp;
};

X2Chart.prototype.getRoundedTimestamp = function (timestamp, binSize, prev) {
    var thisX2Chart = this;
    //thisX2Chart.DEBUG && console.log ('thisX2Chart.getRoundedTimestamp: ');
    var roundedTimestamp;
    switch (binSize) {
        case 'hour-bin-size':
            roundedTimestamp = timestamp;
            break;
        case 'day-bin-size':
            roundedTimestamp = thisX2Chart.getRoundedDayTs (timestamp, prev);
            break;
        case 'week-bin-size':
            roundedTimestamp = thisX2Chart.getRoundedWeekTs (timestamp, prev);
            break;
        case 'month-bin-size':
            roundedTimestamp = thisX2Chart.getRoundedMonthTs (timestamp, prev);
            break;
    }
    //thisX2Chart.DEBUG && console.log ('timestamp, roundedTimestamp = ');
    //thisX2Chart.DEBUG && console.log (timestamp, roundedTimestamp);
    return roundedTimestamp;
};

/*
Retrieves the user selected start and end timestamps from the DOM.
Parameter:
    binSize - if set, the start and end timestamps will be rounded down to the
        nearest hour, day, week, or month, respectively
*/
X2Chart.prototype.getStartEndTimestamp = function () {
    var thisX2Chart = this;
    thisX2Chart.DEBUG && console.log ('getStartEndTimestamp: this = '); 
    thisX2Chart.DEBUG && console.debug (thisX2Chart);

    var startDate = 
        thisX2Chart._chartDatepickerFrom$.datepicker ('getDate');
    var startTimestamp;
    if (startDate) 
        startTimestamp = startDate.valueOf ();
    var endDate = 
        thisX2Chart._chartDatepickerTo$.datepicker ('getDate');
    var endTimestamp;
    if (endDate) 
        endTimestamp = endDate.valueOf ();
    if (endTimestamp < startTimestamp)
        endTimestamp = startTimestamp;

    thisX2Chart.DEBUG && console.log ('thisX2Chart.getStartEndTimestamp: ');
    thisX2Chart.DEBUG && console.log (startTimestamp, endTimestamp);

    return {
        'startTimestamp': startTimestamp,
        'endTimestamp': endTimestamp
    }

};

/*
Helper function for thisX2Chart.plotData. Determines the resolution of the graph.
Returns false if the date range must be sliced into more than the set number of
intervals, true otherwise.
If this function returns false, markers should not be displayed.
*/
X2Chart.prototype.getShowMarkerSetting = function (binSize, countDict) {
    var thisX2Chart = this;
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
};

X2Chart.prototype.getLongMonthName = function (monthNum) {
    var thisX2Chart = this;
    monthNum = + monthNum;
    if (monthNum % 12 === 0) {
        monthNum = 12;
    } else if (monthNum > 12) {
        monthNum = monthNum % 12;
    }
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
};

X2Chart.prototype.getShortMonthName = function (monthNum) {
    var thisX2Chart = this;
    monthNum = + monthNum;
    if (monthNum % 12 === 0) {
        monthNum = 12;
    } else if (monthNum > 12) {
        monthNum = monthNum % 12;
    }
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
            thisX2Chart.DEBUG && console.log (
                'Error: thisX2Chart.getShortMonthName: default switch ' + monthNum);
    }
    return monthName;
};

/*
Given a format string and a timestamp, returns a label with information extracted
from the timestamp. Used to create tooltip text.
*/
X2Chart.prototype.getTooltipFormattedLabel = function (
    formatStr, timestamp, isFirstPoint, isLastPoint) {

    var thisX2Chart = this;
    thisX2Chart.DEBUG && console.log ('thisX2Chart.getTooltipFormattedLabel: params = ');
    thisX2Chart.DEBUG && console.log (formatStr, timestamp, isFirstPoint, isLastPoint);

    var fmtLabel = '';
    var tokens = formatStr.split (' ');
    var date = new Date (timestamp);

    var monthStr, D, M, hours, H, period, Y, endTimestamp, endDate ;
    for (var i in tokens) {
        switch (tokens[i]) {
            case 'shortMonth':
                M = date.getMonth () + 1;
                monthStr = thisX2Chart.translations [thisX2Chart.getShortMonthName (M)];
                fmtLabel += monthStr;
                break;
            case 'day':
                D = date.getDate ();
                fmtLabel += D;
                break;
            case 'toLastDayOfMonth':
                if (isLastPoint) {
                    endTimestamp = (thisX2Chart._chartDatepickerTo$.
                        datepicker ('getDate').valueOf ());
                    if (endTimestamp < timestamp)
                        endTimestamp = timestamp;
                } else {
                    Y = date.getFullYear ();
                    M = date.getMonth () + 1;
                    M++;
                    if (M > 12) {
                        M = 0;
                        Y++;
                    }
                    thisX2Chart.DEBUG && console.log ('Y, M = ');
                    thisX2Chart.DEBUG && console.log (Y, M);
                    endTimestamp = (new Date (Y, M - 1, 1, 0, 0, 0, 0)).getTime ();
                    endTimestamp -= X2Chart.MSPERDAY;
                    thisX2Chart.DEBUG && console.log (endTimestamp);
                }
                    
                fmtLabel += '- ' + thisX2Chart.getTooltipFormattedLabel (
                    formatStr.split ('toLastDayOfMonth')[0], endTimestamp, false, false);
                formatStr.replace (/^.*toLastDayOfMonth/, '');
                break;

            case 'plusSixDays':
                if (isFirstPoint) {
                    var day = date.getDay ();
                    endTimestamp = timestamp + (7 - (day + 1)) * X2Chart.MSPERDAY;
                } else if (isLastPoint) {
                    endTimestamp = (thisX2Chart._chartDatepickerTo$.
                        datepicker ('getDate').valueOf ());
                    if (endTimestamp < timestamp)
                        endTimestamp = timestamp;
                } else {
                    endTimestamp = timestamp + 6 * X2Chart.MSPERDAY;
                    //endTimestamp -= timestampDiff; 
                }
                    
                fmtLabel += '- ' + thisX2Chart.getTooltipFormattedLabel (
                    formatStr.split ('plusSixDays')[0], endTimestamp, false, false);
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
                monthStr = thisX2Chart.translations[thisX2Chart.getLongMonthName (M)];
                fmtLabel += monthStr;
                break;
            case '':
                break;
            default:
                thisX2Chart.DEBUG && console.log (tokens[i]);
                thisX2Chart.DEBUG && console.log (
                    'Error: thisX2Chart.getTooltipFormattedLabel: switch default');
        }
        if (i !== tokens.length)
            fmtLabel += ' ';
    }
    return fmtLabel;

};


/*
Returns an array of ticks acceptable as input to jqplot. The number of ticks
and the ticks' labels depend on the user selected bin size and date range.
*/
X2Chart.prototype.getTicks = function (startTimestamp, endTimestamp, binSize, countDict) {
    var thisX2Chart = this;
    thisX2Chart.DEBUG && console.log ('thisX2Chart.getTicks:');    

    var rounded = false;
    var roundedTimestamp = thisX2Chart.getRoundedTimestamp (endTimestamp, binSize, true);
    if (roundedTimestamp !== endTimestamp)
        rounded = true;
    endTimestamp = roundedTimestamp;

    thisX2Chart.DEBUG && console.log ('startTimestamp, endTimestamp = ');
    thisX2Chart.DEBUG && console.log ([startTimestamp, endTimestamp]);

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
        var date, D, M, monthStr;

        thisX2Chart.DEBUG && console.log ('getDayTicksBetween: count by ' + interval);

        date = new Date (startTimestamp);

        var day = date.getDay ();
        // place dummy tick and find next week boundary
        if (day !== 0 && interval >= X2Chart.MSPERWEEK) { 
            ticks.push ([startTimestamp,'']);
            startTimestamp += (7 - day) * X2Chart.MSPERDAY;
            date = new Date (startTimestamp);
            D = date.getDate ();
            M = date.getMonth () + 1;
            monthStr = thisX2Chart.translations [thisX2Chart.getShortMonthName (M)];
        } else { // timestamp is at week boundary
            D = date.getDate ();
            M = date.getMonth () + 1;
            monthStr = thisX2Chart.translations [thisX2Chart.getShortMonthName (M)];
        }

        ticks.push ([startTimestamp, monthStr + ' ' + D]);

        var timestamp = startTimestamp;
        timestamp += interval;
        while (timestamp <= endTimestamp) {
            date = new Date (timestamp);
            D = date.getDate ();
            M = date.getMonth () + 1;
            monthStr = thisX2Chart.translations[thisX2Chart.getShortMonthName (M)];
            ticks.push ([timestamp, monthStr + ' ' + D]);

            timestamp += interval;

            if (timestamp > endTimestamp)// && binSize !== 'week-bin-size')
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
        var monthStr = thisX2Chart.translations[thisX2Chart.getShortMonthName (M1)];
        if (!suppressYear) monthStr += ' ' + Y1;
        var timestamp = (new Date (Y1, M1 - 1, 1, 0, 0, 0, 0)).getTime ();
        var roundedEndTimestamp = (new Date (Y2, M2 - 1, 1, 0, 0, 0, 0)).getTime ();
        if (timestamp === startTimestamp)
            ticks.push ([startTimestamp, monthStr]);
        else
            ticks.push ([startTimestamp, '']);

        thisX2Chart.DEBUG && console.log ('getting month ticks between ' + timestamp + ' and ' + 
                              roundedEndTimestamp);
        thisX2Chart.DEBUG && console.log ('endString = ' + endString);

        while (true) {
            M1 += interval;
            if (M1 > 12) {
                Y1 = Y1 + Math.floor (M1 / 12);
                M1 = (M1 % 12);
                M1 = M1 === 0 ? 12 : M1;
            }

            beginString = M1 + '-' + 1 + '-' + Y1;

            timestamp = (new Date (Y1, M1 - 1, 1, 0, 0, 0, 0)).getTime ();
            monthStr = thisX2Chart.translations[thisX2Chart.getShortMonthName (M1)];
            if (!suppressYear) monthStr += ' ' + Y1;

            if (beginString === endString || Y1 > Y2 || (Y1 === Y2 && M1 > M2)) {
                if (timestamp <= endTimestamp)
                    ticks.push ([timestamp, monthStr]);
                if (/*binSize !== 'month-bin-size' && */
                    (beginString !== endString || roundedEndTimestamp !== endTimestamp)) {
                    ticks.push ([endTimestamp, ""]);
                }
                break;
            } else {
                ticks.push ([timestamp, monthStr]);
            }
        }
        return ticks;
    }

    var ticks = [];
    var labelFormat;
    thisX2Chart.DEBUG && console.log ('binSize = ' + binSize);
    switch (binSize) {
        case 'hour-bin-size':
            if (hours < 72) {
                var date = new Date (startTimestamp);
                ticks.push ([startTimestamp, '12:00 AM']);
                var timestamp = startTimestamp;
                var interval = X2Chart.MSPERDAY / 2;
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
                if (timestamp > endTimestamp)// && binSize !== 'week-bin-size')
                    ticks.push ([endTimestamp, '']);
                labelFormat = 'longMonth day hours';
            } else if (days <= 7) {
                ticks =
                    getDayTicksBetween (startTimestamp, endTimestamp, X2Chart.MSPERDAY);
                labelFormat = 'longMonth day hours';
            } else if (days <= 62) {
                ticks = getDayTicksBetween (
                    startTimestamp, endTimestamp, Math.ceil (days / 7) * X2Chart.MSPERDAY);
                labelFormat = 'longMonth day hours';
            } else if (days <= 182) {
                ticks = getDayTicksBetween (
                    startTimestamp, endTimestamp, Math.ceil (weeks / 7) *  7 * X2Chart.MSPERDAY);
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
                    getDayTicksBetween (startTimestamp, endTimestamp, X2Chart.MSPERDAY);
                labelFormat = 'longMonth day';
            } else if (days <= 49) {
                ticks = getDayTicksBetween (
                    startTimestamp, endTimestamp, Math.ceil (days / 7) * X2Chart.MSPERDAY);
                labelFormat = 'longMonth day';
            } else if (days <= 182) {
                ticks = getDayTicksBetween (
                    startTimestamp, endTimestamp, Math.ceil (weeks / 7) *  7 * X2Chart.MSPERDAY);
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
            if (days < 21) {
                ticks = getDayTicksBetween (
                    startTimestamp, endTimestamp, Math.ceil (days / 7) * X2Chart.MSPERDAY);
                labelFormat = 'longMonth day plusSixDays';
            } else if (days <= 49) {
                ticks =
                    getDayTicksBetween (startTimestamp, endTimestamp, 7 * X2Chart.MSPERDAY);
                labelFormat = 'longMonth day plusSixDays';
            } /*else if (days <= 62) {
                ticks = getDayTicksBetween (
                    startTimestamp, endTimestamp, Math.ceil (days / 7) * 7 * X2Chart.MSPERDAY);
                labelFormat = 'longMonth day plusSixDays';
            } */else if (days <= 182) {
                ticks = getDayTicksBetween (
                    startTimestamp, endTimestamp, Math.ceil (weeks / 7) * 7 * X2Chart.MSPERDAY);
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
                if (ticks.length === 2 &&
                    (ticks[0][1] === '' || ticks[1][1] === '')) {
                    ticks = thisX2Chart.getTicks (
                        startTimestamp, endTimestamp, 'day-bin-size', 
                        countDict)['ticks'];

                }
                labelFormat = 'longMonth day toLastDayOfMonth';
            } else {
                ticks = getMonthTicksBetween (
                    startTimestamp, endTimestamp, Math.ceil (months / 7), false);
                labelFormat = 'longMonth day year toLastDayOfMonth';
            }
            break;
        default: 
            thisX2Chart.DEBUG && console.log ('Error: thisX2Chart.getTicks: switch default');
    }

    thisX2Chart.DEBUG && console.log ('thisX2Chart.getTicks ret ' + labelFormat);

    // remove some ticks for mobile layout to prevent overlap
    if ($('body').hasClass ('x2-mobile-layout') && ticks.length > 8) {
        for (var i = 0; i < ticks.length; i += 2) {
            ticks[i][1] = '';
        }
    }

    return {
        'ticks': ticks,
        'labelFormat': labelFormat
    }

};

/*
Returns true if there is only one bin between the start and end timestamps
*/
X2Chart.prototype.checkOnlyOneBin = function (binSize, countDict) {
    var thisX2Chart = this;
    var onlyOneBin = false;
    thisX2Chart.DEBUG && console.log ('thisX2Chart.checkOnlyOneBin');
    thisX2Chart.DEBUG && console.debug (countDict);
    switch (binSize) {
        case 'hour-bin-size':
            break;
        case 'day-bin-size':
            if (countDict['days'] === 1)
                onlyOneBin = true;
            break;
        case 'week-bin-size':
            if (countDict['weeks'] === 1)
                onlyOneBin = true;
            break;
        case 'month-bin-size':
            if (countDict['months'] === 1)
                onlyOneBin = true;
            break;
    }    
    return onlyOneBin;
};

X2Chart.prototype.setupTooltipBehavior = function () {};

/*
Builds chart legend with an entry for each type in types. Legend is inserted into the DOM.
*/
X2Chart.prototype.buildChartLegend = function (types, typesText, color) {
    var thisX2Chart = this;
    thisX2Chart._chartLegend$.find ('tbody').empty ();
    var makeNewRow = true;
    var currRow, currCell;
    for (var i in types) {
        if (makeNewRow) {
            thisX2Chart.DEBUG && console.log ('make new row');
            currRow = $('<tr>');
            thisX2Chart._chartLegend$.find ('tbody').append (currRow);
            makeNewRow = false;
        } else if ((i + 1) % 3 === 0) {
            makeNewRow = true;
        }
        thisX2Chart.DEBUG && console.log ('currRow = ');
        thisX2Chart.DEBUG && console.log ($(currRow));
            currCell = $('<td>').append (
                $('<div>', {
                    'class': 'chart-color-swatch'
                }),
                $('<span>', {
                    text: typesText[types[i]],
                    'class': 'chart-color-label'
                })
            )
        thisX2Chart.DEBUG && console.log ('setting background-color to ' + color[i]);
        $(currCell).find ('div').css ('background-color', color[i]);
        $(currRow).append (currCell);
        if (i > 22) break;
    }
    if (types.length === 2) {
        $(currRow).append ($('<td>')); // dummy cell
    }
};

// override in child prototype
X2Chart.prototype.preJqplotPlotPieData = function (chartData) {};

// override in child prototype
X2Chart.prototype.preJqplotPlotLineData = function (chartData) {};

// gets replaced depending on chart subtype
X2Chart.prototype.getJqplotConfig = function (argsDict) {};

// gets replaced depending on chart subtype
X2Chart.prototype.plotData = function () {};

/*
Changes the chart settings to match the settings specified settingsDict
*/
X2Chart.prototype.applyChartSetting = function (settingsDict) {
    var thisX2Chart = this;

    thisX2Chart.DEBUG && console.log ('applyChartSetting: settingsDict = ');
    thisX2Chart.DEBUG && console.debug (settingsDict);

    function applyStartDate (startDate) {
        thisX2Chart.DEBUG && console.log ('applying start date' + startDate);
        var startDate = new Date (parseInt (startDate, 10));
        thisX2Chart._chartDatepickerFrom$.
            datepicker ('setDate', startDate);
    }

    function applyEndDate (endDate) {
        thisX2Chart.DEBUG && console.log ('applying end date');
        var endDate = new Date (parseInt (endDate, 10));
        thisX2Chart._chartDatepickerTo$.
            datepicker ('setDate', endDate);
    }

    function applyDateRange (selector, dateRange, typeSelector, dateRangeType) {
        thisX2Chart.DEBUG && console.log ('applying date range');
        thisX2Chart.applyDateRange (dateRange, dateRangeType);
        auxlib.selectOptionFromSelector (selector, dateRange, true);
        auxlib.selectOptionFromSelector (typeSelector, dateRangeType, true);
    }

    function applyBinSize (binSize) {
        if (binSize === 'hour-bin-size' || binSize === 'day-bin-size' ||
            binSize === 'week-bin-size' || binSize === 'month-bin-size') {
            thisX2Chart._chartContainer$.find ('a.disabled-link').
                removeClass ('disabled-link');
            thisX2Chart._chartContainer$.find (
                '#' + thisX2Chart.chartType + '-' + binSize + '-' + thisX2Chart.widgetUID).
                addClass ('disabled-link');
        }
    }

    function applyFirstMetric (firstMetric) {
        thisX2Chart.DEBUG && console.log ('setting firstMetric');
        thisX2Chart.DEBUG && console.log ('typeof firstMetric = ' + typeof firstMetric);

        thisX2Chart.applyMultiselectSettings (
            '#' + thisX2Chart.chartType + '-first-metric-' + thisX2Chart.widgetUID, firstMetric);
    }

    function applyFilter (selector, filterName, possibleVals, settings) {
        thisX2Chart.applyMultiselectSettings (selector, settings);
        var checkedValues = $(selector).val ();
        checkedValues = checkedValues === null ? [] : checkedValues;
        var filterVal = $(possibleVals).not (checkedValues);
        thisX2Chart.filters[filterName] = filterVal;
    }

    // date range takes precedence over start and end date settings
    if (settingsDict['dateRange'] !== undefined &&
        settingsDict['dateRange'] !== null &&
        settingsDict['dateRange'] !== 'Custom') {
        if (settingsDict['startDate'] !== undefined) delete settingsDict['startDate'];
        if (settingsDict['endDate'] !== undefined) delete settingsDict['endDate'];
    }

    for (var i in settingsDict) {
        if (settingsDict[i] === null) continue;
        thisX2Chart.DEBUG && console.log ('for: ' + i);
        switch (i) {
            case 'startDate':        
                applyStartDate (settingsDict[i]);
                break;
            case 'endDate':        
                applyEndDate (settingsDict[i]);
                break;
            case 'binSize':        
                applyBinSize (settingsDict[i]);
                break;
            case 'firstMetric':        
                applyFirstMetric (settingsDict[i]);
                break;
            case 'chartSetting':        
                thisX2Chart.setChartSettingName (settingsDict[i]);
                break;
            case 'showRelationships':        
                thisX2Chart._relChartDataCheckbox$.prop (
                    'checked', (settingsDict[i] === 'true' ? true : false));
                break;
            case 'eventsFilter':        
                applyFilter (
                    thisX2Chart._eventsChartFilter$, 'eventsFilter', thisX2Chart.eventTypes, 
                    settingsDict[i]);
                break;
            case 'usersFilter':        
                applyFilter (
                    thisX2Chart._usersChartFilter$, 'usersFilter', thisX2Chart.userNames, 
                    settingsDict[i]);
                break;
            case 'socialSubtypesFilter':        
                applyFilter (
                    thisX2Chart._socialSubtypesChartFilter$, 'socialSubtypesFilter', 
                    thisX2Chart.socialSubtypes, settingsDict[i]);
                break;
            case 'visibilityFilter':        
                applyFilter (
                    thisX2Chart._visibilityChartFilter$, 'visibilityFilter', 
                    thisX2Chart.visibilityTypes, settingsDict[i]);
                break;
            case 'dateRange':        
                var dateRange = thisX2Chart._dateRangeSelector$;
                var dateRangeType = thisX2Chart._dateRangeTypeSelector$;
                applyDateRange (dateRange, settingsDict[i], dateRangeType, settingsDict.dateRangeType);
                break;
            default:
                thisX2Chart.DEBUG && console.log ('Error: applyMultiselectSettings: default on switch');
        }
    }
};

/*
Sets to and from date picker ui elements based on date range.
*/
X2Chart.prototype.applyDateRange = function (dateRange, dateRangeType) {
    if (typeof dateRangeType === 'undefined' || dateRangeType === null) {
        dateRangeType = this._dateRangeTypeSelector$.val();
    }
    
    if (dateRangeType == 'custom') {
        return;
    }

    var thisX2Chart = this;

    thisX2Chart.DEBUG && console.log ('dateRange = ' + dateRange);
    var fromDatepicker = thisX2Chart._chartDatepickerFrom$;
    var toDatepicker = thisX2Chart._chartDatepickerTo$;

    var momentFunction = {
        trailing: function(range){ return moment().subtract(1, range); },
        'this': function(range){ return moment().startOf(range); },
        last: function(range){ return moment().subtract(1, range).startOf(range); },
    }[dateRangeType];

    var start = momentFunction(dateRange);
    var end = moment();

    if (dateRangeType == 'last') {
        end = moment().subtract(1, dateRange).endOf(dateRange);
    }

    $(fromDatepicker).datepicker ('setDate', new Date(start));
    $(toDatepicker).datepicker ('setDate', new Date(end));

    return;
};

/*
Set up behavior of date range selector ui element. 
Pre: setUpDatepickers has already been called.
*/
X2Chart.prototype.setUpDateRangeSelector = function () {
    var thisX2Chart = this;

    thisX2Chart.DEBUG && console.log ('setUpDateRangeSelector');

    thisX2Chart._dateRangeSelector$.on ('change', function () {
        var dateRange = $(this).val ();
        thisX2Chart.applyDateRange (dateRange);
        thisX2Chart.saveChartSetting ('dateRange', dateRange);
        thisX2Chart.getEventsBetweenDates (true);
        thisX2Chart.setChartSettingName ('');  
    });

    thisX2Chart._dateRangeTypeSelector$.on ('change', function () {
        var dateRangeType = $(this).val();
        var isCustom = (dateRangeType == 'custom');
        var opacity = isCustom ? 0.0 : 1.0;
        thisX2Chart._dateRangeSelector$.css('opacity', opacity);
        $(this).toggleClass('rounded', isCustom);
        
        thisX2Chart.saveChartSetting ('dateRangeType', dateRangeType);
        thisX2Chart._dateRangeSelector$.trigger('change');
    });
};

/*
Instantiate jquery datepickers and set to default values. Set up datepicker behavior.
*/
X2Chart.prototype.setUpDatepickers = function () {
    var thisX2Chart = this;

    thisX2Chart.DEBUG && console.log ('setUpDatepickers');

    // setup datepickers and initialize range to previous week
    thisX2Chart._chartDatepickerFrom$.datepicker(
        $.extend (
            {}, $.datepicker.regional[yii.language], {
                constrainInput: false,
                showOtherMonths: true,
                selectOtherMonths: true,
                dateFormat: yii.datePickerFormat,
        }));

    thisX2Chart._chartDatepickerTo$.datepicker(
        $.extend (
            {}, $.datepicker.regional[yii.language], {
                constrainInput: false,
                showOtherMonths: true,
                selectOtherMonths: true,
                dateFormat: yii.datePickerFormat,
        }));

    /*
    Save setting and replot
    */
    thisX2Chart._chartDatepickerFrom$.datepicker (
        'option', 'onSelect', function () {

        thisX2Chart.DEBUG && console.log ('from date selected');
        thisX2Chart.getEventsBetweenDates (true);
        thisX2Chart.saveChartSetting ('startDate', 
            thisX2Chart._chartDatepickerFrom$.datepicker ('getDate').valueOf ());
        if (!thisX2Chart.suppressChartSettings) {
            thisX2Chart.setChartSettingName ('');  
        }
        if (!thisX2Chart.suppressDateRangeSelector) {
            auxlib.selectOptionFromSelector (
                thisX2Chart._dateRangeSelector$, 'Custom');
            thisX2Chart._dateRangeSelector$.trigger ('change');
        }
    });

    /*
    Save setting and replot
    */
    thisX2Chart._chartDatepickerTo$.datepicker (
        'option', 'onSelect', function () {

        thisX2Chart.getEventsBetweenDates (true);
        thisX2Chart.saveChartSetting ('endDate',
            thisX2Chart._chartDatepickerTo$.datepicker ('getDate').valueOf ());

        if (!thisX2Chart.suppressChartSettings) {
            thisX2Chart.setChartSettingName ('');  
        }

        if (!thisX2Chart.suppressDateRangeSelector) {
            auxlib.selectOptionFromSelector (
                thisX2Chart._dateRangeSelector$, 'Custom');
            thisX2Chart._dateRangeSelector$.trigger ('change');
        }
    });

};

/*
Override in child prototype
*/
X2Chart.prototype.postMetricSelectionSetup = function () {
};

/*
Instantiates metric selection elements for various chart types. Sets up metric 
selection behavior.
*/
X2Chart.prototype.setUpMetricSelection = function () {
    var thisX2Chart = this;

    // initialize dropdown checklist
    thisX2Chart._firstMetric$.multiselect2 (
        $.extend ({}, thisX2Chart._multiSelectOptions, { 
        'selectedText': '# ' + thisX2Chart.translations['metric1Label'] }));

    // setup metric selector behavior
    thisX2Chart._firstMetric$.bind (
        "multiselect2close", function (evt, ui) {

        var firstMetricVal = $(this).val ();
        firstMetricVal = firstMetricVal === null ? 'none' : firstMetricVal;
        thisX2Chart.saveChartSetting ('firstMetric', firstMetricVal);
        thisX2Chart.plotData ({redraw: true});
        if (!thisX2Chart.suppressChartSettings) {
            thisX2Chart.setChartSettingName ('');  
        }
    });

    thisX2Chart.postMetricSelectionSetup ();

};

/*
Saves all chart settings present in settingsDict.
*/
X2Chart.prototype.setCookiesFromSettings = function (settingsDict) {
    var thisX2Chart = this;

    for (var settingName in thisX2Chart.lastChartSettings) {
        if (typeof settingsDict[settingName] === 'undefined') {
            continue;
        }
        thisX2Chart.saveChartSetting (settingName, settingsDict[settingName]);
    }

};

/*
Override in child prototype
*/
X2Chart.prototype.postSetSettingsFromCookie = function () {};

/*
Extracts saved settings from cookie and sets chart settings to them.
*/
X2Chart.prototype.setSettingsFromCookie = function () {
    var thisX2Chart = this;

    var settingsDict = {};

    for (var settingName in thisX2Chart.lastChartSettings) {
        settingsDict[settingName] = thisX2Chart.lastChartSettings[settingName];
    }
    thisX2Chart.DEBUG && console.log ('applying settings ');
    thisX2Chart.DEBUG && console.log (settingsDict);

    thisX2Chart.applyChartSetting (settingsDict);
    thisX2Chart.postSetSettingsFromCookie ();
};

/*
Selects chart setting from drop down. If the setting is not the custom setting,
the delete button is displayed.
*/
X2Chart.prototype.setChartSettingName = function (chartSetting) {
    var thisX2Chart = this;
    thisX2Chart._predefinedSettings$.find ('option:selected').
        removeAttr ('selected');
    var foundSetting = false;
    thisX2Chart._predefinedSettings$.children ().each (function () {
        if ($(this).val () === chartSetting) {
            $(this).attr ('selected', 'selected');
            foundSetting = true;
            return false;
        }
    });
    thisX2Chart.DEBUG && console.log ('thisX2Chart.setChartSettingName: chartSetting = ' + chartSetting);
    if (chartSetting === '' || !foundSetting) {
        thisX2Chart._deleteSettingButton$.hide ();
    } else {
        thisX2Chart._deleteSettingButton$.fadeIn ();
    }
    thisX2Chart._predefinedSettings$.change ();
};

/*
Sets up behavior of ui elements related to chart setting selection, deletion, and 
creation. 
*/
X2Chart.prototype.setUpChartSettings = function () {
    var thisX2Chart = this;

    /*
    Performs a request to save a new chart setting to the server. Also applies
    the new chart setting.
    */
    function createChartSetting (chartSettingName) {
        var chartSettingAttributes = {};
        chartSettingAttributes['name'] = chartSettingName;
        chartSettingAttributes['chartType'] = thisX2Chart.chartType;
        chartSettingAttributes['settings'] = {};

        // collect chart settings
        for (var settingName in thisX2Chart.lastChartSettings) {
            switch (settingName) {
                case 'startDate':
                    chartSettingAttributes['settings']['startDate'] = 
                        (thisX2Chart._chartDatepickerFrom$.
                            datepicker ('getDate').valueOf ());
                    break;
                case 'endDate':
                    chartSettingAttributes['settings']['endDate'] = 
                        (thisX2Chart._chartDatepickerTo$.
                            datepicker ('getDate').valueOf ());
                    break;
                case 'binSize':
                    chartSettingAttributes['settings']['binSize'] = 
                        thisX2Chart._getBinSizeFromButtonElem (
                            thisX2Chart._binSizeButtonSet$.find ('a.disabled-link'));
                    break;
                case 'firstMetric':
                    chartSettingAttributes['settings']['firstMetric'] = 
                        thisX2Chart._firstMetric$.val ();
                    break;
                case 'visibilityFilter':
                    chartSettingAttributes['settings']['visibilityFilter'] = 
                        thisX2Chart._visibilityChartFilter$.val ();
                    break;
                case 'usersFilter':
                    chartSettingAttributes['settings']['usersFilter'] = 
                        thisX2Chart._usersChartFilter$.val ();
                    break;
                case 'eventsFilter':
                    chartSettingAttributes['settings']['eventsFilter'] = 
                        thisX2Chart._eventsChartFilter$.val ();
                    break;
                case 'socialSubtypesFilter':
                    chartSettingAttributes['settings']['socialSubtypesFilter'] = 
                        thisX2Chart._socialSubtypesChartFilter$.
                            val ();
                    break;
                case 'dateRange':
                    chartSettingAttributes['settings']['dateRange'] = 
                        thisX2Chart._dateRangeSelector$.val ();
                    break;
                case 'dateRangeType':
                    chartSettingAttributes['settings']['dateRangeType'] = 
                        thisX2Chart._dateRangeTypeSelector$.val ();
                    break;
                default: 
                    thisX2Chart.DEBUG && console.log ('Error: createChartSetting: default switch');
            }
        }

        thisX2Chart.DEBUG && console.log ('post request, chartSettingAttributes = ');
        thisX2Chart.DEBUG && console.debug (chartSettingAttributes);

        $.ajax ({
            url: "createChartSetting",
            type: "POST",
            data: {
                'chartSettingAttributes': chartSettingAttributes
            },
            success: function (data) {

                if (data === '') { // successful creation
                    thisX2Chart.DEBUG && console.log ('createChartSetting ajax success');
                    thisX2Chart.chartSettings[chartSettingName] = chartSettingAttributes;
                    thisX2Chart._createChartSettingDialog$.
                        dialog ("close");

                    // select new chart setting from drop down
                    thisX2Chart._predefinedSettings$.children ().
                        removeAttr ('selected');
                    thisX2Chart._predefinedSettings$.
                        append ($('<option></option>', {

                        'value': chartSettingName,
                        'text': chartSettingName
                    }));
                    thisX2Chart.setChartSettingName (chartSettingName);

                } else { // creation failed
                    thisX2Chart.DEBUG && console.log (data);
                    var respObj = JSON.parse (data);
                    thisX2Chart.DEBUG && console.log (respObj);
                    thisX2Chart.DEBUG && console.debug (respObj);
                    thisX2Chart.DEBUG && console.log ('createChartSetting ajax failure');

                    // display error messages
                    auxlib.destroyErrorBox (
                        thisX2Chart._createChartSettingDialog$);

                    var errMsgs = auxlib.keys (respObj).map (function (key) { 
                            return respObj[key]; 
                        });
                    var errorBox = auxlib.createErrorBox ('', errMsgs);
                    $('.chart-setting-name-input-container').after ($(errorBox));
                    thisX2Chart._chartSettingName$.addClass ('error');

                }

            }
        });

    }

    // highlight save button
    function dialogSaveButtonFocus (dialog) {
        var $buttonpane = $(dialog).next ();

        if ($buttonpane.find ('.dialog-cancel-button').
            hasClass ('highlight')) {

            $buttonpane.find ('button').
                removeClass ('highlight');    
            $buttonpane.find ('.dialog-save-button').addClass ('highlight');
        }
    }

    // highlight cancel button
    function dialogCancelButtonFocus (dialog) {
        thisX2Chart.DEBUG && console.log ('dialogCancelButtonFocus');
        var $buttonpane = $(dialog).next ();
        thisX2Chart.DEBUG && console.log ($buttonpane);

        if ($buttonpane.find ('.dialog-save-button').
            hasClass ('highlight')) {

            thisX2Chart.DEBUG && console.log ('if');
            $buttonpane.find ('button').removeClass ('highlight');    
            $buttonpane.find ('.dialog-cancel-button').addClass ('highlight');
        }
    }

    /*
    Sets up behavior of chart creation dialog box.
    */
    (function setupChartSettingCreationDialog () {
        thisX2Chart._createChartSettingDialog$.hide();

        /*
        Validate chart setting name. If Valid, create chart setting.
        */
        function clickChartSettingCreateButton () {
            var settingName = thisX2Chart._chartSettingName$.val ();
            if (settingName === '') {
                thisX2Chart._chartSettingName$.addClass ('error');
                auxlib.destroyErrorBox (
                    thisX2Chart._createChartSettingDialog$);
                dialogCancelButtonFocus (
                    thisX2Chart._createChartSettingDialog$);
            } else {
                createChartSetting (settingName); 
            }
        }

        thisX2Chart._createChartSettingDialog$.find ("input").change (function () {
            thisX2Chart.DEBUG && console.log ('change');
            var $dialog = thisX2Chart._createChartSettingDialog$;
            dialogSaveButtonFocus (
                thisX2Chart._createChartSettingDialog$);
        });

        /*
        Set up chart setting creation dialog
        */
        thisX2Chart._createSettingButton$.click (function () {
            thisX2Chart._createChartSettingDialog$.dialog ({
                title: thisX2Chart.translations['Create Chart Setting'],
                autoOpen: true,
                height: "auto",
                width: 550,
                resizable: false,
                show: 'fade',
                hide: 'fade',
                buttons: [
                    { 
                        text: thisX2Chart.translations['Create'],
                        click: clickChartSettingCreateButton,
                        'class': 'dialog-save-button'
                    },
                    { 
                        text: thisX2Chart.translations['Cancel'],
                        click: function () {
                            thisX2Chart._createChartSettingDialog$.
                                dialog ("close");
                        },
                        'class': 'highlight dialog-cancel-button'
                    }
                ],
                close: function (event, ui) {
                    thisX2Chart._chartSettingName$.removeClass ('error');
                    thisX2Chart._chartSettingName$.val ('');
                    auxlib.destroyErrorBox (
                        thisX2Chart._createChartSettingDialog$);
                }
            });
        });
    }) ();

    /*
    Delete chart setting
    */
    thisX2Chart._deleteSettingButton$.click (function (evt) {
        evt.preventDefault();
        var settingName = thisX2Chart._predefinedSettings$.val ();
        $.ajax ({
            url: "deleteChartSetting",
            data: {
                'chartSettingName': settingName
            },
            success: function (data) {
                thisX2Chart.DEBUG && console.log ('delete-settings-button ajax call');
                thisX2Chart.DEBUG && console.log (data);
                if (data === 'success') {
                    thisX2Chart.setChartSettingName ('');  
                    thisX2Chart._predefinedSettings$.find (
                        '[value="' + settingName + '"]').remove ();
                } 
            }
        });
    });

    /*
    Sets up behavior for predifined chart setting selection.
    */
    thisX2Chart._predefinedSettings$.change (function () {
        thisX2Chart.DEBUG && console.log ('predefined-settings: change');
        if ($(this).find (':selected').attr ('id') !== 
            thisX2Chart.chartType + '-custom-settings-option-' + thisX2Chart.widgetUID) {

            thisX2Chart._deleteSettingButton$.fadeIn ();

            // extract chart settings
            var settingName = $(this).find (':selected').val ();
            thisX2Chart.DEBUG && console.log (
                'predefined-setting selected, name = ' + settingName);
            
            var chartSetting = thisX2Chart.chartSettings[settingName]['settings'];
            thisX2Chart.DEBUG && console.debug (chartSetting);

            thisX2Chart.DEBUG && console.log ('applying chart settings ');
            thisX2Chart.applyChartSetting (chartSetting);

            thisX2Chart.getEventsBetweenDates (true);

            // update cookies with chart settings
            thisX2Chart.setCookiesFromSettings (chartSetting);

        } else {
            thisX2Chart._deleteSettingButton$.hide ();
        }

        /** 
         * Temporary Fix 
         * There is a parallel request going out since many 'change' triggers are
         * called. They 'accumulate' the changes of the previous calls, so only the 
         * final one is necessary. 
         */
        var element = this;
        setTimeout(function(){
            thisX2Chart.saveChartSetting ('chartSetting', $(element).val ());
        }, 1000);
    });
};

/*
Hide the chart
*/
X2Chart.prototype.hide = function () {
    var thisX2Chart = this;

    thisX2Chart.saveChartSetting ('chartIsShown', false);
    thisX2Chart._chartContainer$.hide ();
};

/*
Show the chart
*/
X2Chart.prototype.show = function () {
    var thisX2Chart = this;
    x2.DEBUG && console.log ('X2Chart.show');
    thisX2Chart._chartContainer$.show ();
    thisX2Chart.saveChartSetting ('chartIsShown', true);

};

/*
Used to stretch chart width to size of container when canvas is not supported by browser
*/
X2Chart.prototype.resizeChartNoCanvas = function () {
    var thisX2Chart = this;
    var chartTarget = 
        thisX2Chart._chartContainer$.find ('.chart.jqplot-target');
    $(chartTarget).width (thisX2Chart._chartContainer$.width ()); 
};

/*
Replot the chart
*/
X2Chart.prototype.replot = function () {
    var thisX2Chart = this;

    if (thisX2Chart.feedChart !== null) {
        if (!Modernizr.canvas) {
            thisX2Chart.resizeChartNoCanvas ();
        }
        thisX2Chart.feedChart.replot ({ resetAxes: false });
    }
};

/*
Set up behavior of bin size selection ui element
*/
X2Chart.prototype.setUpBinSizeSelection = function () {
    var thisX2Chart = this;
    thisX2Chart._chartContainer$.find ('a.x2-button').click (function (evt) {
        evt.preventDefault ();
        if (!$(this).hasClass ('disabled-link')) {
            thisX2Chart._chartContainer$.find ('a.disabled-link').
                removeClass ('disabled-link');
            $(this).addClass ('disabled-link');
            if (thisX2Chart.eventData !== null) {
                thisX2Chart.plotData ({redraw: true});
            }
            var binSize = 
                thisX2Chart._getBinSizeFromButtonElem (
                    thisX2Chart._binSizeButtonSet$.find ('a.disabled-link'));
            thisX2Chart.saveChartSetting ('binSize', binSize);
            if (!thisX2Chart.suppressChartSettings) {
                thisX2Chart.setChartSettingName ('');  
            }
        }
    });
};

X2Chart.prototype._setUpElementProperties = function () {
    var thisX2Chart = this;

    this._chartContainer$ = $('#' + this.chartType + '-chart-container-' + this.widgetUID);
    this._usersChartFilter$ = $('#' + this.chartType + '-users-chart-filter-' + this.widgetUID);
    this._socialSubtypesChartFilter$ = 
        $('#' + this.chartType + '-social-subtypes-chart-filter-' + this.widgetUID);
    this._visibilityChartFilter$ = 
        $('#' + this.chartType + '-visibility-chart-filter-' + this.widgetUID);
    this._eventsChartFilter$ = 
        $('#' + this.chartType + '-events-chart-filter-' + this.widgetUID);
    this._topButtonRow$ = 
        $('#' + this.chartType + '-top-button-row-' + this.widgetUID);
    this._firstMetricContainer$ = 
        $('#' + this.chartType + '-first-metric-container-' + this.widgetUID);
    this._firstMetric$ = 
        $('#' + this.chartType + '-first-metric-' + this.widgetUID);
    this._filterToggleContainer$ = 
        $('#' + this.chartType + '-filter-toggle-container-' + this.widgetUID);
    this._showChartFiltersButton$ = 
        $('#' + this.chartType + '-show-chart-filters-button-' + this.widgetUID);
    this._hideChartFiltersButton$ = 
        $('#' + this.chartType + '-hide-chart-filters-button-' + this.widgetUID);
    this._binSizeButtonSet$ = 
        $('#' + this.chartType + '-bin-size-button-set-' + this.widgetUID);
    this._datepickerRow$ = 
        $('#' + this.chartType + '-datepicker-row-' + this.widgetUID);
    this._chartDatepickerFrom$ = 
        $('#' + this.chartType + '-chart-datepicker-from-' + this.widgetUID);
    this._chartDatepickerTo$ = 
        $('#' + this.chartType + '-chart-datepicker-to-' + this.widgetUID);
    this._dateRangeSelector$ = 
        $('#' + this.chartType + '-date-range-selector-' + this.widgetUID);
    this._dateRangeTypeSelector$ =
        $('#' + this.chartType + '-date-range-type-selector-' + this.widgetUID);
    this._createSettingButton$ = 
        $('#' + this.chartType + '-create-setting-button-' + this.widgetUID);
    this._deleteSettingButton$ = 
        $('#' + this.chartType + '-delete-setting-button-' + this.widgetUID);
    this._predefinedSettings$ = 
        $('#' + this.chartType + '-predefined-settings-' + this.widgetUID);
    this._relChartDataCheckbox$ = 
        $('#' + this.chartType + '-rel-chart-data-checkbox-' + this.widgetUID);
    this._relChartDataCheckboxContainer$ = 
        $('#' + this.chartType + '-rel-chart-data-checkbox-container-' + this.widgetUID);
    this._chart$ = 
        $('#' + this.chartType + '-chart-' + this.widgetUID);
    this._pieChartCountContainer$ = 
        $('#' + this.chartType + '-pie-chart-count-container-' + this.widgetUID);
    this._chartLegend$ = 
        $('#' + this.chartType + '-chart-legend-' + this.widgetUID);
    this._chartTooltip$ = 
        $('#' + this.chartType + '-chart-tooltip-' + this.widgetUID);
    this._createChartSettingDialog$ = 
        $('#' + this.chartType + '-create-chart-setting-dialog-' + this.widgetUID);
    this._chartSettingName$ = 
        $('#' + this.chartType + '-chart-setting-name-' + this.widgetUID);
};

