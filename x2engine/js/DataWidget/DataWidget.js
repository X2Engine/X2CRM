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
 * Datawidget Abstract class
 * Implements base functionality for charts, managing: 
 * - Ajax calls / Data fetching
 * - Legend toggling
 * - Config bar delete / rename / hide buttons
 * - Chart generation settings defaults. 
 * 
 * @author Alex Rowe <alex@x2engine.com>
 */
x2.DataWidget = (function() {

var REFRESH_MINUTES = 5;

function DataWidget (argsDict) {
    var defaultArgs = {

        // Contains all of the formatted data sent by DataWidget::getChartData (php)
        chartData: [],

        // Id of the chart model this chart is attached to 
        chartId: -1,
        
        // Id of the report model this chart is attached to 
        reportId: null,

        // A string of an error message. If this is set, 
        // the chart will abort rendering
        errors: false,

        // Array of HIDDEN legend items
        legend: [],

        // Locale for moment to use
        locale: 'en',

        fetchDataUrl: yii.scriptUrl + '/reports/fetchData',
        cloneChartUrl: yii.scriptUrl + '/reports/cloneChart',
        addToDashboardUrl: yii.scriptUrl + '/reports/addToDashboard',
        callChartFunctionUrl: yii.scriptUrl + '/reports/callChartFunction',

    };

    auxlib.applyArgs (this, defaultArgs, argsDict);

    // Variable to store Settings for exporting
    this.cachedSettings = {};

    SortableWidget.call (this, argsDict);
}

DataWidget.prototype = auxlib.create (SortableWidget.prototype);

/**
 * Initialization function, private method. Child initializtion
 * should go in init() to prevent execution in case of errors.
 */
DataWidget.prototype._init = function(){
    SortableWidget.prototype._init.call(this);

    this._formatParameters();

    this.setUpAddToDashButton();
    this.setUpConfigBar();

    // Set up selectors
    this.contentSelector = this.contentContainer.selector;

    // Set up inline report
    this.inlineReport = new InlineReport (this);

    // Set up a refresh Method
    if (!x2.isMobileApp)
        this.setUpRefresh();

    // Call child initilization 
    this.init();

    if (typeof this.chartData.error !== 'undefined') {
        this.toggleError(this.chartData.error);
    } else {
        this.render();
    }
};

/*********************************
* Sets up a refreshing interval
********************************/
DataWidget.prototype.setUpRefresh = function() { 
    var that = this;
    function loop() {
        setTimeout(function() {
            that.refresh();
            loop();
        }, 1000*60*REFRESH_MINUTES);
    }

    loop();
};



/**
 * Ensures boolean parameters are javascript literals, instead of strings
 * Occasionally there have been strings passed instead of booleans
 */
DataWidget.prototype._formatParameters = function(){
    for (var i in this) {
        if (this[i] == "false") {
            this[i] = false;
        } if (this[i] == "true") {
            this[i] = true;
        }
    }

    // Ensure legend is an array rather than null
    if (!this.legend) {
        this.legend = [];
    }
};


/**
* Sets up the 'Add to Dashboard' button
*/
DataWidget.prototype.setUpAddToDashButton = function (){
    if (typeof PopupDropdownMenu === 'undefined') return; // mobile app

    var that = this;

    var containerSelector = '.add-to-dashboard-dropdown';
    var buttonSelector = '.add-to-dashboard';

    var containerIdPrefix ='add-to-dashboard-';
    var dropdownId = containerIdPrefix + this.widgetUID;

    this.element.find(containerSelector).
        appendTo ($('#content')).
        attr ('id', dropdownId).
        hide();

    new PopupDropdownMenu({
        containerElemSelector: '#'+dropdownId,
        openButtonSelector: this.elementSelector + ' .add-to-dashboard',
    });

    this.dashboardDropdown = $('#'+dropdownId);

    var bindClick = function(selector, destination) {
        that.dashboardDropdown.find (selector).click (function(){
            $.ajax({
                url: that.addToDashboardUrl,
                data: that.ajaxIdentity({
                    destination: destination
                }),
                success: function() {
                    x2.topFlashes.displayFlash(that.translations.addedToDashboard, 'success');
                }
            });
        });
    };

    bindClick('#add-to-charts', 'data');
    bindClick('#add-to-profile', 'profile');

};

/**
* Set up the buttons on the config bar this function 
* should be extended in child classes to customize the bar.
*/
DataWidget.prototype.setUpConfigBar = function (){
    var ulMenu = $(this.element).find('.widget-settings-menu-content');

    this.configBar = $(this.element).find('.config-bar');

    var that = this;

    /*****************************************
    * Datawidget uses a different standard button layout
    * than normal sortable widgets. The buttons simply
    * trigger the standard buttons
    **********************************************/

    this.configBar.find('#delete').click(function() {
        ulMenu.find('.delete-widget-button').trigger('click');
    });

    this.configBar.find('#relabel').click(function() {
        ulMenu.find('.relabel-widget-button').trigger('click');
    });

    this.configBar.find('#edit').click(function() {
        ulMenu.find('.edit-widget-button').trigger('click');
    });

    this.configBar.find('#print').click(function() {
        that.print();
    });

    var gearButton = $(this.element).find('.widget-settings-button');
    gearButton.unbind();

    var configBar = this.configBar;
    gearButton.click(function(e) {
        e.preventDefault();
        configBar.slideToggle();
    });

    this.configBar.find("#clone").click(function() {
        that.cloneWidget();
    });

};


/**
* Clones the widget and appends it to the second widget container
*/
DataWidget.prototype.cloneWidget = function() {
    var that = this;
    $.ajax({
        url: this.cloneChartUrl,
        data: this.ajaxIdentity(),
        dataType: 'json',
        success: function(data) {
            if(data.widget) {
                $('#'+that.widgetType+'-widgets-container-2').append($(data.widget));
            }
        }
    });
};


/**
* Fetches new data for the chart to render. Because often data is being
* recieved because a setting was changed, an additional settings parameter
* can be set, which modifies the models settings before data is fetched. 
* 
* @param {function} callback Function to be called back after data is received
* @param {object} settings Dictionary of settings to be changed
*/
DataWidget.prototype.fetchData = function(callback, settings) {
    var that = this;

    if (typeof settings === 'undefined') {
        settings = {};
    }

    // Start the loading spinner, stopped after chart is rendered

    $.ajax({
        url: yii.scriptUrl + '/reports/fetchData',
        data: that.ajaxIdentity({
            settings: settings
        }),
        dataType: 'json', 
        success: function(data) {
            if (typeof data.error !== 'undefined') {
                return that.toggleError(data.error);
            } else {
                that.toggleError(false);
            }

            callback.call(that, data);
        },

        failure: function() {
            // Stop spinner if server failure
        }

    });
};

DataWidget.prototype.toggleError = function(error) { 
    if(error) {
        this.element.find('.error-screen').text(error).show();
        this.contentContainer.hide();
    } else {
        this.element.find('.error-screen').hide();
        this.contentContainer.show();
    }
}


/**
 * Wrapper for c3.generate.
 * All charts should use this function instead of c3.generate to 
 * incorporate common functionality into the charts. 
 * 
 * @param {object} argsDict Dictionary of arguments to be merged into the defaults.
 */
DataWidget.prototype.generate = function(argsDict) { 
    var that = this;

    var defaultDict = {
        bindto: this.contentSelector,

        data: {
            type: this.displayType,
            onselected: function (dataPoint, element){
                that.pointClicked (dataPoint, element);
            },
            hide: this.legend,
        },

        legend: {
            item: {
                onclick: function(item) {
                    that.toggleLegend(item);
                }
            }
        },

    };


    var chartSettings = $.extend(true,  argsDict, defaultDict);

    // Fix for the current bug rendering selections on 
    // stacked area or line charts. This fix moves the select
    // handler to a on click handler if appropriate
    // Gauge displays also dont have selection and need clicking
    if (this.displayType == 'gauge' || 
        typeof chartSettings.data.groups != 'undefined' &&
        chartSettings.data.groups.length != 0 && 
        (this.displayType == 'line' || this.displayType == 'area' )){

        chartSettings.data.onclick = chartSettings.data.onselected;
        chartSettings.data.onselected = null;
        if (chartSettings.data.selection) {
            chartSettings.data.selection.enabled = false;
        }
    }

    this.cachedSettings = chartSettings;
    this.chart = c3.generate(chartSettings);
};

/**
 * Fetches and renders an inline report
 * 
 * @param  {object} conditions Object of conditions to be sent in the request 
 * @param  {option} options Object of options for the front-end portion
 */
DataWidget.prototype.fetchReport = function(conditions, options) {
    this.inlineReport.fetch (conditions, options);
};


/**
 * Toggles the visibility of a legend Item, and makes the 
 * appropriate call to change the chart setting
 */
DataWidget.prototype.toggleLegend = function(item) {
    var index = $.inArray (item, this.legend);

    if (index >= 0) {
        this.legend.splice (index, 1);
        this.chart.show (item);
    } else {
        this.legend.push (item);
        this.chart.hide (item);
    }

    // Send false if it is empty
    var legend;
    if (this.legend.length == 0) {
        legend = false;
    } else {
        legend = this.legend;
    }

    // Set the persistent property
    this.setProperty ('legend', legend);
};

/** 
 * Method to package settings for printing, can be overriden to provide different
 * settings when a chart is printed. 
 */
DataWidget.prototype.exportSettings = function() {
    this.draw();

    var settings = {};
    settings = this.cachedSettings;
    settings.data.labels = true;

    return settings;
}

/** 
 * Print just this charts using DataWidget.print
 */
DataWidget.prototype.print = function() {
    DataWidget.printCharts([this]);
}

/*********************************
* Abstract Methods
********************************/

/**
 * Child Initialization function; Not called if there are errors
 */
DataWidget.prototype.init = function(){};

/**
 * Abstract function called when a point is clicked on the chart
 */
DataWidget.prototype.pointClicked = function(dataPoint, element) {};

/*********************************
* Sortable Widget overrides
********************************/
DataWidget.prototype.onDragStop = function() {
    $('.chart-dashboard').find('.connected-sortable-'+this.widgetType+'-container').height('100%');
    $(this.element).removeClass('dragging');
};

DataWidget.prototype.onDragStart = function() {
    SortableWidget.prototype.onDragStart.call(this);
    $(this.element).addClass('dragging');
    var height = $('.chart-dashboard').height();
    $('.chart-dashboard').find('.connected-sortable-'+this.widgetType+'-container').height(height+250);
};

/*********************************
* Static Methods
********************************/
/**
 * Prints a list of charts
 * @param {array} chartList List of widgets to print
 * Submits an object looking like this: 
 * $_POST = array (
 *     'settings' => "{<chart settings as json string>}", ...
 *     'titles' => "Chart Title 1", ...
 *     'ids' => "Chart Id 1", ...
 * )
 */
DataWidget.printCharts = function(chartList) {
    var url = yii.scriptUrl + '/reports/printChart';
    var form = $('<form target="_blank" action="'+url+'" method="POST">'+
        '</form>');

    var el;
    for(var i in chartList) {
        // Get the chart object
        var chart = chartList[i];

        // Appened an input for the settings of the chart
        el = $('<input name="settings[]" type="hidden" />');
        var json = JSON.stringify(chart.exportSettings());
        el.val (json);
        el.appendTo (form);

        // Appened an input for the id of the chart
        el = $('<input name="ids[]" type="hidden" />');
        var id = chart.chartId;
        el.val (id);
        el.appendTo (form);

        // Appened an input for the title of the chart
        el = $('<input name="titles[]" type="hidden" />');
        var title = chart.element.find('.widget-title').html();
        el.val (title);
        el.appendTo (form);
    }

    el = $('<input name="YII_CSRF_TOKEN" type="hidden" />');
    el.val (x2.csrfToken);
    el.appendTo (form);

    form.submit();
}

/*********************************
* Inline Report Class
********************************/
function InlineReport (dataWidget) {
    var that = this;

    this.widget = dataWidget;

    // initialize selectors
    this.element   = this.widget.element;
    this.container = this.element.find ('.inline-report-container');
    this.tabs      = this.element.find ('.inline-report-tabs');
    this.template  = this.element.find ('.inline-report-template');

    // Initialize Tabs
    this.container.tabs();

    // Set up inline report close buttons
    this.container.on('click', '.inline-report-close', function(){
        that.container.slideToggle();
    });


    // Current number of incoming reports
    this.request = 0;

    // Timeout between report requests
    this.timeout = 1000;
}

InlineReport.prototype.incrementRequest = function() {
    var that = this;

    // If it is the first request, clear report container and tabs
    if(this.request == 0) {
        this.container.find('.inline-report').remove();
        this.tabs.find('li').remove();
    }

    // Increment request
    this.request++;

    // After a timeout, reset counter
    setTimeout(function(){
        that.request = 0;
    }, this.timeout);

    return this.request;
}

InlineReport.prototype.fetch = function(conditions, options) {
    var that = this;

    // Recieve a unique request id
    var id = this.incrementRequest();

    $.ajax({
        url: this.widget.callChartFunctionUrl,
        data: {
            chartId: this.widget.chartId,
            params: {
                conditions: conditions,
                id: id,
            },
            fnName: "renderInlineReport"
        },
        success: function (data) {
            if (!data) return;
            that.appendReport(data, id, options);
        }
    });
}

InlineReport.prototype.appendReport = function(data, id, options) {
    var reportId = 'inline-report-' + id + '-' + this.widget.widgetUID;
    var tabTitle = options.tabTitle || options.title || '';

    // Fill out tempalte and append to container
    var report = this.template.find ('.inline-report').clone();
    report.find ('.inline-report-header').css ('background', options.color);
    report.find ('.inline-report-name').html (options.title);
    report.find ('.inline-report-generated').html ($(data));
    report.attr ('id', reportId);
    report.appendTo (this.container);

    // Add a tab to the tab list
    var li = $('<li></li>').
        appendTo (this.tabs).
        css ('background', options.color).
        addClass ('no-theme'); // Prevent theme coloring


    // Change the tab title
    $('<a></a>').attr ('href', '#' + reportId).appendTo (li).html (tabTitle);

    // Hide tab list if there is only one
    if(this.tabs.find('li').length == 1) {
        this.tabs.hide();
    } else {
        this.tabs.show();
    }

    // Refresh the new tabs that have arrived
    this.container.tabs('refresh');

    // Activate the first tab
    this.tabs.find('li a').first().click();

    // Open the container
    if (this.container.is(':hidden')) {
        this.container.slideToggle();
    }
}

return DataWidget;

})();
