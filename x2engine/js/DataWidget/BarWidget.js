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
 * 
 * ChartData Structure: 
 * {   
 *     data {
 *          ['categories', 'item2', 'item2'...],
 *          ['admin', 123, 4123, ...]
 *          ['chames', 513, 712, ...]
 *          ...
 *     },
 *     labels: {
 *          categories: 'assignedTo',
 *          values: 'count'
 *          groups: 'Lead Source' 
 *     }
 * }
 *
 * @author Alex Rowe <alex@x2engine.com>
 */

x2.BarWidget = (function() {

var MAX_TICKS = 20;
var MAX_CATEGORIES = 20;

function BarWidget (argsDict) {
    var defaultArgs = {
        displayType: null,
        orientation: null,
        stack: false,
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.DataWidget.call (this, argsDict); 
}

BarWidget.prototype = auxlib.create (x2.DataWidget.prototype);

BarWidget.prototype.setUpConfigBar = function(){
    x2.DataWidget.prototype.setUpConfigBar.call(this);

    var that = this;

    var options = ['bar', 'line', 'pie', 'area'];

    auxlib.map(function(d){ 
        that.configBar.find('#'+d).click(function(){
            $(this).siblings('.display-type').removeClass('active');
            $(this).addClass('active');

            that.displayType = d;
            that.draw();
            that.setProperty('displayType', d);
        });
    }, options);

    that.configBar.find('#orientation').click (function(){
        if (that.orientation == 'rows') {
            that.orientation = 'columns';
        } else {
            that.orientation = 'rows';
        }

        that.setProperty('orientation', that.orientation);
        that.draw();
    });

    that.configBar.find('#stack').click (function(){
        that.stack = !that.stack;

        $(this).toggleClass('active', that.stack);

        that.setProperty('stack', that.stack);
        that.chart.groups(that.getData().groups);
        // that.draw();
    });

    this.configBar.find('#stack').toggleClass ('active', that.stack);
    this.configBar.find('#'+this.displayType).addClass('active');
};

/**
 * this function is called when the chart is told to refresh
 */
BarWidget.prototype.refresh = function() {
    // fetch data with refreshData as a callback
    this.fetchData(this.refreshData);
};

BarWidget.prototype.refreshData = function(data) {
    this.chartData = data;
    this.chart.load(this.getData().data);
};


BarWidget.prototype.getData = function() {
    var data = {
        x: 'categories',
        groups: [],
        selection: {
            enabled: true,
            multiple: false
        }
    };

    data[this.orientation] = this.chartData.data;

    // Put data into groups if stacked
    if (this.stack) {
        if (this.orientation == 'rows') {
            data.groups = [this.chartData.data[0]];
        } else {
            data.groups = [auxlib.map(function(d){return d[0];}, this.chartData.data)];
        }
    }

    return data;
};


BarWidget.prototype.draw = function() {

    var displayPoints = true;

    var tickCount;
    if (this.chartData.data[0].length >= MAX_TICKS || 
        this.chartData.data.length    >= MAX_TICKS) {
        tickCount = MAX_TICKS;
        displayPoints = false;
    }

    var displayLegend = true;
    if (this.chartData.data.length >= MAX_CATEGORIES) {
        displayLegend = false;
    }

    this.generate({
        data: this.getData(),
        bar: {
            width: {
                ratio: 0.75
            }
        },
        axis: {
            x: {
                label: this.chartData.labels[this.orientation],
                tick: {
                    count: tickCount,
                    culling: {
                        max: MAX_TICKS
                    }
                },
                type: 'category',
            },
            y: {
                label: this.chartData.labels.values
            }
        },
        point: {
            show: displayPoints
        },
        legend: {
            show: displayLegend
        }
    });

    // this.fitTickLabels();
};


/**
 * Drilldown functionality for summation report type reports
 */
BarWidget.prototype.pointClicked = function(dataPoint, element) {
    var group = null;
    var name = null;

    if (this.orientation == 'rows') {
        group = dataPoint.name;
        if (this.displayType != 'pie') {
            name = this.chartData.data[dataPoint.index+1][0];
        }
    } else {
        name = dataPoint.name;
        if (this.displayType != 'pie') {
            group = this.chartData.data[0][dataPoint.index+1];
        }
    }

    // Construct an informative title
    var title = '';
    if (name) {
        title = name;
    }

    if (group) {
        if (name) {
            title += ' by '
        }
        title += group;
    }

    var conditions = {
        name: name,
        group: group
    };

    var options = {
        color: $(element).css('fill'),
        title: title,
        tabTitle: dataPoint.id
    };

    this.fetchReport (conditions, options);
};

/**
 * Render Implementation
 */
BarWidget.prototype.render = function() {
    this.draw();
};

return BarWidget;
})();
