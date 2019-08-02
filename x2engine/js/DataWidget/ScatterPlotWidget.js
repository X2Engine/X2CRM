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

x2.ScatterPlotWidget  = (function() {

var MAX_CATEGORIES = 20;

function ScatterPlotWidget (argsDict) {
    var defaultArgs = {
        grid: true
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.DataWidget.call (this, argsDict); 
}

ScatterPlotWidget.prototype = auxlib.create (x2.DataWidget.prototype);

ScatterPlotWidget.prototype.setUpConfigBar = function(){
    x2.DataWidget.prototype.setUpConfigBar.call(this);

    var that = this;

    this.configBar.find('#grid').click(function(e){
        that.grid = !that.grid;
        that.setProperty('grid', that.grid);
        $(this).toggleClass('active', that.grid);
        that.draw();
    });
};

/**
 * this function is called when the chart is told to refresh
 */
ScatterPlotWidget.prototype.refresh = function() {
    var that = this;
    // fetch data with refreshData as a callback
    this.fetchData(function(data){
        that.chartData = data;
        that.draw();
    });
};


ScatterPlotWidget.prototype.draw = function() {

    var showLegend = true;
    if (this.chartData.size > MAX_CATEGORIES) {
        showLegend = false;
    }

    this.generate({
        data: {
            xs: this.chartData.xs,
            json: this.chartData.json,
            type: 'scatter'
        },
        axis: {
            x: {
                label: this.chartData.labels.x,
                tick: {
                    fit: false
                }
            },
            y: {
                label: this.chartData.labels.y
            }
        },
        grid: {
            x: {
                show: this.grid
            },
            y: {
                show: this.grid
            }
        },
        legend: {
            show: showLegend
        }
    });
};


ScatterPlotWidget.prototype.render = function() {
    this.draw();
};

return ScatterPlotWidget;
})();
