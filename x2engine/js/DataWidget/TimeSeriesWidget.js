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




/**
 * Welcome to the TimeSeriesWidget!
 * This widget will create a histogram from a list of time-oriented data
 * this chart expects the data in the following form:
 * 
 * chartData: {
 *     timeField: [144123114, 144123271, 144123578 ...]
 *     labelField: ['won', 'working', 'won' ...]
 *     timeFrame: {
 *         start: 144123092
 *         end: 144129042
 *     }
 * }
 * 
 * timeField is the list of unix time stamps to be histogrammed
 * labelField (optional) is what category each time stamp belongs to 
 * timeFrame is an object letting the chart know what range to plot
 * 
 * Note that many of the functions in this class are heavily object oriented
 * and do not take arguments.
 *
 * @author Alex Rowe <alex@x2engine.com>
 */
x2.TimeSeriesWidget = (function() {

var MAX_CATEGORIES = 20;
var MAX_TICKS = 50;
var MAX_POINTS = 20;

function TimeSeriesWidget (argsDict) {
    var defaultArgs = {

        filterType: 'trailing',
        filter: 'week',
        timeBucket: 'day',
        displayType: 'line',
        filterFrom: null,
        filterTo: null,
        subchart: false,

        formattedData: [],
        sortedData: [],
        ticks: [],
        gauge: false,

        primaryModelType: '',

        tickFormats: {
            custom: 'MMM D',
            year: 'MMM D YYYY',
            quarter: 'MMM D',
            month: 'MMM D',
            week: 'MMM D',
            day: 'MMM D HH:mm',
            full: 'lll'
        },

        tickCounts: {
            custom: 6,
            year: 6,
            quarter: 6,
            month: 6,
            week: 7,
            day: 4
        },

        allowedBuckets: {
            day: ['hour'],
            week: ['day', 'hour'],
            month: ['week', 'day', 'hour'], 
            quarter: ['month', 'week', 'day'],
            year: ['quarter', 'month', 'week', 'day'],
            custom: ['year', 'quarter', 'month', 'week', 'day', 'hour']
        }

    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.DataWidget.call (this, argsDict); 
}

TimeSeriesWidget.prototype = auxlib.create (x2.DataWidget.prototype);


/**************************************************
 * Set up all handlers for the config bar.
 * Many aspects in this function are steamlined-
 * IDs of config bar items often correspond to keywords
 * that c3 or d3 understand, such as chart types or time buckets
 **************************************************/
TimeSeriesWidget.prototype.setUpConfigBar = function(){
    x2.DataWidget.prototype.setUpConfigBar.call(this);
    this.filterMenu = this.element.find('.filter-menu');
    this.bucketMenu = this.element.find('.time-bucket');

    var that = this;

    /**********************************
     * Chart type Menu
     **********************************/
    var options = ['line', 'pie', 'gauge', 'bar', 'area'];

    auxlib.map(function(d){ 
        that.configBar.find('#'+d).click(function(e){
            e.preventDefault();
            $(this).siblings('.display-type').removeClass('active');
            $(this).addClass('active');

            that.setProperty('displayType', d);
            that.displayType = d;

            that.draw();
        });
    }, options);

    /**********************************
     * Subchart Button
     **********************************/
    that.configBar.find('#subchart').click(function(e) {
        e.preventDefault();
        $(this).toggleClass('active');
        that.subchart = !that.subchart;
        that.setProperty('subchart', that.subchart);
        that.draw();
    });


    /**********************************
     * Time Filter menu
     **********************************/
    // that.configBar.find('#filter').click(function(e) {
    //     that.filterMenu.slideToggle();
    //     $(this).toggleClass('active');
    // });

    options = ['trailing', 'this','custom'];

    auxlib.map(function(d){ 
        that.filterMenu.find('#'+d).click(function(e){
            e.preventDefault();
            $(this).siblings('.filter-option').removeClass('active');
            $(this).addClass('active');
            
            that.filterType = d;

            that.fetchAndRender({filterType: d});

            if( d == 'custom') {
                that.filterMenu.find('.input-container').toggle(true);
                that.filterMenu.find('.time-unit').toggle(false);
            } else {
                that.filterMenu.find('.input-container').toggle(false);
                that.filterMenu.find('.time-unit').toggle(true);
            }
        });
    }, options);


    /**********************************
     * Time Filter Options
     **********************************/
    options = ['day', 'week', 'month', 'quarter', 'year'];

    auxlib.map(function(d){ 
        that.filterMenu.find('.filter-option#'+d).click(function(e){
            e.preventDefault();
            $(this).siblings('.filter-option').removeClass('active');
            $(this).addClass('active');

            that.filter = d;

            that.fetchAndRender({filter: d});
        });
    }, options);

    /**********************************
     * Bucket size menu
     **********************************/
    options = ['hour', 'day', 'week', 'month'];

    auxlib.map(function(d){ 
        that.bucketMenu.find('#'+d).click(function(e){
            e.preventDefault();
            $(this).siblings('.bucket-option').removeClass('active');
            $(this).addClass('active');

            that.setProperty('timeBucket', d);
            that.timeBucket = d;

            // redraw without resorting the data,
            // just rebucketing
            that.formattedData = that.bucketData (that.sortedData);

            that.draw();
        });
    }, options);

    /**********************************
     * Custom Date Picker
     **********************************/
     if (typeof $.fn.datepicker !== 'undefined') { // not in mobile app
        this.filterMenu.find('.filter-field').datepicker();

        options = ['filterFrom', 'filterTo'];

        auxlib.map(function(d){ 
            that.filterMenu.find('.'+d).change(function(){
                // var timestamp = moment ($(this).val()).unix()
                var timestamp = $(this).val(); 
                that[d] = timestamp;

                var params = {};
                params[d] = timestamp;

                that.fetchAndRender(params);
            });
        }, options);
    }

    /************************************
     * Select all options currently active
     ************************************/
     if( this.subchart )
         this.configBar.find('#subchart').addClass('active');
     this.configBar.find('#'+this.displayType).addClass('active');
     this.bucketMenu.find('#'+this.timeBucket).addClass('active');
     this.filterMenu.find('.filter-option#'+this.filter).addClass('active');
     this.filterMenu.find('.filter-option#'+this.filterType).addClass('active');

     // Hide or show correct time filter options
     if(this.filterType == "custom") {
         that.filterMenu.find('.input-container').toggle(true);
         that.filterMenu.find('.time-unit').toggle(false);
     } else {
         that.filterMenu.find('.input-container').toggle(false);
         that.filterMenu.find('.time-unit').toggle(true);
     }

     this.filterMenu.find('.filterFrom').val(this.filterFrom);
     this.filterMenu.find('.filterTo').val(this.filterTo);

     this.filterMenu.appendTo(this.configBar).show();


};


/**************************************************
 * Calculate the x scale ticks
 **************************************************/
TimeSeriesWidget.prototype.xScale = function(){

    // Retrieves an object indicting the starting and 
    // ending times for the chart domain
    var timeFrame = this.getTimeFrame();

    // Generate a d3 timescale object
    var domain = [timeFrame.start, timeFrame.end];
    var x = d3.time.scale().domain(domain);

    // d3 time object for 1 hour, 1 day, 1 month..
    var d3time = d3.time[this.timeBucket];

    // Generate the ticks based on the bucketing
    var ticks = x.ticks(d3time, 1);

    // Fallback to 2 ticks if the timeframe is 0 for example
    if (ticks.length  === 0)
        ticks = x.ticks(2);

    return ticks;
};

/**************************************************
 * Calculate the start and end of the domain of the cart
 **************************************************/
TimeSeriesWidget.prototype.getTimeFrame = function() {

    var unixStart = this.chartData.timeFrame.start;
    var unixEnd = this.chartData.timeFrame.end;

    // Round to the neared timebuckets
    var start = moment (unixStart * 1000).subtract(1, this.timebucket).startOf(this.timeBucket);
    var end = moment (unixEnd * 1000).add(1, this.timeBucket).startOf (this.timeBucket);

    return {start: start, end: end};
};


/**************************************************
 * Sort data into categories
 * Takes the raw chart data from this: 
 *     
 *     labelField:     ['cat1' , 'cat2' , 'cat1' ... ],
 *     timeField:      [1412312, 1412532, 1415312 ... ]
 *     aggregateField: [     23,     123,      54, ... ]
 *
 * and transforms it into this: 
 *
 *      cat1: [{timestamp: 1412312, value: 23}, {timestamp: 1412532, value: 123} ... ]
 *      cat2: [{timestamp: 1415312, value: 54}, ... ]
 *
 * this.chartData -> this.sortedData
 **************************************************/
TimeSeriesWidget.prototype.sortData = function(data) {
    var sortedData = [];

    // True if maximum categories are reached
    var maximized = false;

    for (var i in data.timeField) {
        // If there are no labels or too many categories to display, 
        // We scrap the sorting and put all data into 1 category.
        if (auxlib.keys(sortedData).length > MAX_CATEGORIES || !data.labelField) {
            maximized = true;
            break;
        }
        
        var timestamp = data.timeField[i];
        var label = data.labelField[i];

        // Add the category if it hasn't been added already
        if (typeof sortedData[ label ] === 'undefined') {
            if (label === null || label.length === 0) {
                label = 'null';
            }
            sortedData[ label ] = [ ]; 
        }
        
        // Create a data item with the value and time
        // default value is one, defaulting to 'Count'
        var item = { 
            timestamp: timestamp * 1000,
            value: 1
        }

        if(data.aggregateField) {
            item.value = Number(data.aggregateField[i]);
            if (!item.value) {
                item.value = 0;
            }
        }

        sortedData[ label ].push( item );
    }

    // If number of categories is OK, return constructed array
    if (maximized) {
        // If number of categories is too much, construct a new array
        sortedData = {all:[]};
        for(var j in data.timeField) {
            var item = {
                timestamp: data.timeField[j]*1000,
                value: 1
            };

            if(data.aggregateField) {
                item.value = Number(data.aggregateField[j]);
                if (!item.value) {
                    item.value = 0;
                }
            }

            sortedData.all.push(item);
        }
    }

    return sortedData;
};

/*****************************************
 * Split the sorted data into histogram data
 * Sorted data looks like this: 
 * 
 *     cat1: [1412312, 1415312 ... ]
 *     cat2: [1412532, ... ]
 *
 * And gets formatted to this: 
 *
 *     cat1: [0, 3, 8, 1, ...]
 *     cat2: [1, 4, 9, 4, ...]
 *     
 * this.sortedData -> this.formattedData
 *****************************************/
TimeSeriesWidget.prototype.bucketData = function(sortedData) {

    // Create the ticks based on the 
    var ticks = this.xScale();

    var formattedData = {};
    for (var i in sortedData) {
        formattedData[i] = this.formatData( sortedData[i], ticks);
    }

    if (auxlib.length(formattedData) === 0) {
        formattedData.all = auxlib.emptyNumArray(ticks.length);
    }
    
    // ticks.shift();
    formattedData.ticks = ticks;

    return formattedData;
};

/**************************************************
 * Format an array of points into histogram data
 * This is called on each entry in sorted data
 * 
 * @param array data array of data points timestamps
 * @param array ticks array of ticks to sort by
 * @return array see bucketData
 **************************************************/
TimeSeriesWidget.prototype.formatData = function(data, ticks) {
    // This sorts the data into the buckets. Very important function!
    var histData = d3.layout.
        histogram ().
        bins (ticks).value (function(d) {
            return new Date(d.timestamp)
        }) (data);

    // We just need the height of each list of sorted objects
    var ydata = auxlib.map( function(d) {
        var sum = 0;
        for (var i=0; i<d.length; i++) {
            sum += d[i].value;
        }
        return sum;
    }, histData);

    return ydata;
};


/*****************************************
 * Average data over span for gauge 
 *****************************************/
TimeSeriesWidget.prototype.averageData = function(formattedData) {
    var that = this;

    var sum = 0;
    var current = 0;
    var label = '';

    // Variable to keep track of the current bucket index
    var currentBucketIndex; 

    // Function to find the sum of an array
    reduceFunction = function(prev, cur, index) {
        // If the bucket is the last bucket, hold this for the current
        // Value of the gauge
        if (index == currentBucketIndex){
            current += cur;
        }

        return prev + cur;
    };


    for(var i in formattedData) { 

        // Dont average in the x-axis ticks
        if (i == 'ticks') 
            continue;

        // If the category is hidden in the legend, skip it
        if ($.inArray (i, this.legend) >= 0) {
            continue;
        }

        // Build a label that is descriptive of the categories counted
        if (label) label += ', ';
        label += i;
        
        // Set the index of the last bucket in the list
        currentBucketIndex = formattedData[i].length - 1;

        // Sum over all the buckets using reduce
        sum += formattedData[i].reduce (reduceFunction);
    }

    // Normalize to the number of buckets
    var length = formattedData.ticks.length ;
    length = (length === 0) ? 1 : length;
    var average = sum / length;

    return {
        average: average, 
        current: current,
        label: label
    };
};

/*****************************************
 * Generate color spectrum for the gauge
 *****************************************/
TimeSeriesWidget.prototype.colorScale = function() {
    var values = [0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100];

    var colorScale = d3.scale.linear()
      .domain([0,50])
      .interpolate(d3.interpolateRgb)
      .range(['#FF0000', '#F6C600']);

    var colorScale2 = d3.scale.linear()
      .domain([50,100])
      .interpolate(d3.interpolateRgb)
      .range(['#F6C600', '#60B044']);

    var colors = auxlib.map(function(d) { 
        if( d <= 50)
            return colorScale(d);
        else 
            return colorScale2(d);
    }, values);


    return {values: values, colors: colors};
};

/*****************************************
 * Load wrapper to account for gauge type
 *****************************************/
TimeSeriesWidget.prototype.load = function() {
    if (this.displayType == 'gauge') {
        var gaugeData = this.averageData (this.formattedData);
        var columns = [[gaugeData.label, gaugeData.current]];
        this.chart.load ({columns: columns});
    } else {
        var json = this.formattedData;
        this.chart.load({json: json});
    }
};

/*****************************************
 * Gauge Draw function
 *****************************************/
TimeSeriesWidget.prototype.drawGauge = function() {
    var gaugeData = this.averageData (this.formattedData);
    var color = this.colorScale();
    this.generate({
        data: {
            columns: [[gaugeData.label, gaugeData.current]],
        },
        gauge: {
            label: {
                format: function(value, ratio) {
                   return value;
                },
            },
            min: 0,
            max: Math.round(gaugeData.average * 200) / 100,
            units: this.primaryModelType +" "+ this.translations['this '+ this.timeBucket],
        },
        color: {
            pattern: color.colors,
            threshold: {
                values: color.values,
                unit: 'percent',
                max: Math.round(gaugeData.average * 200) / 100,
            }
        },
        padding: {
            bottom: 25
        }

    });


};

/*****************************************
 * Primary Draw function
 *****************************************/
TimeSeriesWidget.prototype.draw = function() {
    var type = this.displayType;
    var that = this;

    /**
     * call the special gauge render function if gauge is selected
     */
    if( type == 'gauge') {
        this.drawGauge();
        return;
    }

    /**
     * Group data if area is selected
     */
    var groups = [];
    if (this.displayType == 'area') {
        groups = [auxlib.keys(this.formattedData)];
    }

    /**
     * Limit ticks if there are greater than MAX_TICKS
     */
    var tickCount = 'auto';
    if (this.formattedData.ticks.length > MAX_TICKS) {
        if (this.filterType == 'custom') {
            tickCount = this.tickCounts.custom;
        } else {
            tickCount = this.tickCounts[this.filter];
        }
    }

    /**
     * Hide points if there are more than 20
     */
    var points = true;
    if (this.formattedData.ticks.length > MAX_POINTS) {
        points = false;
    }

    /**********************************
     * Chart Generation
     *********************************/
    this.generate({
        data: {
            x: 'ticks',
            json: this.formattedData,
            groups: groups,
            selection: {
                enabled: true,
                multiple: false
            }
        },
        axis: {
            x: {
                label: this.chartData.labels.timeField,
                type: 'timeseries',
                tick: {
                    count: tickCount,
                    // culling: {
                    //     max: 10
                    // },
                    fit: true,
                    format: function (d) {
                        return moment(d).format (
                            that.tickFormats[that.filter]
                        );
                    }
                }
            },
            y: {
                label: this.chartData.labels.aggregateField,
                tick: {
                    format: d3.format ('d')
                }
            }
        },
        bar: {
            width: {
                ratio: 0.8
            }
        },
        tooltip: {
            format: {
                title: function (d) { 
                    return moment(d).format (
                        that.tickFormats.full
                    );
                }
            }
        },
        point: {
            show: points
        },
        subchart: {
            show: this.subchart
        }
    });
    
};

/**
 * Triggers when a point is clicked to fetch the drilldown report
 * see DataWidget PointClicked
 */
TimeSeriesWidget.prototype.pointClicked = function(dataPoint, element) { 
    // conditions to send in the request
    var condition = {}, 

        options = {},

    // Title of the report
        title = '', 

    // Start and end time stamps
        start, end, 

    // Name of the point category
        name;

    if (this.displayType == 'gauge') {
        start = moment().startOf(this.timeBucket).unix();
        end = moment().endOf(this.timeBucket).unix();
        name = dataPoint.id;
        condition = {
            start: start,
            end: end,
            name: name
        };
        title = dataPoint.id + " " + this.translations['this '+ this.timeBucket];

    } else if (this.displayType == 'pie') {
        condition = {
            name: dataPoint.id
        };
        title = dataPoint.id;

    } else {

        start = moment (dataPoint.x).unix();
        end = moment (dataPoint.x).add(1, this.timeBucket).unix();        
        name  = dataPoint.id; 
        condition = {
            start: start,
            end: end,
            name: name
        };

        // Construct an informative title
        // Titles appear as so:
        // hour: webactivity at Feb 7 14:30
        // day: webactivity on Feb 7
        // week: webactivity on the week of Feb 7
        // month: webactivity on the month of Feb 7
        if (this.timeBucket == 'hour') {
            title = dataPoint.id + ' at ';
            var dateString = moment (dataPoint.x).format (this.tickFormats.day);
        } else {
            title = dataPoint.id + ' on ';
            var dateString = moment (dataPoint.x).format (this.tickFormats[this.filter]);
        }

        if (this.timeBucket == 'week' || this.timeBucket == 'month') {
            title += " the " + this.timeBucket + ' of ';
        }

        title += dateString;
    }

    // Convert nulls to actual nulls
    // If theres only one category, don't filter by category
    if(condition.name === 'null' || auxlib.length(this.sortedData) == 1) {
        condition.name = '';
    }

    // Display options of the report
    var options = {
        title: title,
        color: $(element).css('fill'),
        tabTitle: dataPoint.id
    };

    // Fetch the report
    this.fetchReport (condition, options);
};


/**************************************************
 * Redraw entire chart with new data
 * When a new time filter is selected, all of the data
 * needs to be retrieved and chart needs to redrawn
 **************************************************/
TimeSeriesWidget.prototype.fetchAndRender = function(params, load) {
    var that = this;

    this.fetchData(function(data){
        that.render(data, load);
    }, params);
};


/************************************
* Overrides sortable widget refresh. 
* Fetches fresh data with a callback 
* to load.
************************************/
TimeSeriesWidget.prototype.refresh = function() { 
    this.fetchAndRender();
    // var that = this;

    // this.fetchData(function(data){
    //     that.render(data, true);
    // });
};


/******************************************
* Full Render of chart
* data array data to be assigned to chartData
* load boolean Whether to redraw or just load data
*******************************************/
TimeSeriesWidget.prototype.render = function(data, load) {
    if (typeof data !== 'undefined') {
        this.chartData = data;
    }

    if (typeof load === 'undefined') {
        load = false;
    }

    // Standard sequence to render from raw data
    this.sortedData = this.sortData      (this.chartData);
    this.formattedData = this.bucketData (this.sortedData);

    if (load)
        this.load();
    else
        this.draw();
};

/**
 * Extends set to format the chart properly
 */
TimeSeriesWidget.prototype.exportSettings = function() {
    var settings = x2.DataWidget.prototype.exportSettings.call(this);

    if (this.displayType == 'gauge') return settings;

    // Format the dates 
    var ticks = settings.data.json.ticks;
    for (var i in ticks) {
        ticks[i] = moment(ticks[i]).unix()*1000;
    }
    
    // Remove hidden Legend Items
    for (var i in this.legend) {
        delete settings.data.json[this.legend[i]];
    }

    settings.data.json.ticks = ticks;
    settings.axis.x.tick.fit = true;
    settings.axis.x.tick.format = "%e %b %y";

    return settings;
}

/**
 * Initialization Implementation
 */
TimeSeriesWidget.prototype.init = function() {
    // Set up moment's Locale
    moment.locale(this.locale);
};

return TimeSeriesWidget;
})();
