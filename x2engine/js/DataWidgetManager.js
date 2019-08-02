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
 * Class to manage the widgets on a chart dashboard
 * Quite an ugly class- I apologize
 */
x2.DataWidgetManager = (function() {

function DataWidgetManager (argsDict) {
    var defaultArgs = {
        dashboardSelector: '.chart-dashboard',
        connectedContainerSelector: '.connected-sortable-data-container',
        cssSelectorPrefix: 'data-',
        widgetType: 'data',
        showWidgetContentsUrl: yii.scriptUrl + '/profile/getWidgetContents',
        widgetList: [],
        translations: {}
    };

    auxlib.applyArgs (this, defaultArgs, argsDict);
    ProfileWidgetManager.call(this, argsDict);
}

DataWidgetManager.prototype = auxlib.create(ProfileWidgetManager.prototype);

DataWidgetManager.prototype._init = function(){
    ProfileWidgetManager.prototype._init.call(this);

    this.dashboard = $('.chart-dashboard');
    this.setUpToolBar();

};

/*********************************
* Method to print a chart
********************************/
DataWidgetManager.prototype.printCharts = function() {

    if (this.widgetList.length == 0 ){
        x2.topFlashes.displayFlash(this.translations.noWidgets, 'error');
        return;
    }

    x2.DataWidget.printCharts(this.widgetList);
}


/**
 * SortableWidgetManager Override
 */
DataWidgetManager.prototype._afterShowWidgetContents = function() {
    this._hideShowHiddenProfileWidgetsText();
};

/**
 * SortableWidgetManager Override
 */
DataWidgetManager.prototype.addWidgetToHiddenWidgetsMenu = function (widgetSelector) {
    SortableWidgetManager.prototype.addWidgetToHiddenWidgetsMenu.call (this, widgetSelector);
    this._hideShowHiddenProfileWidgetsText();
};

DataWidgetManager.prototype.refreshWidgets = function() {
    for (var i in this.widgetList) {
        widget = this.widgetList[i];
        if ($(widget.contentSelector).length !== 0) {
            widget.refresh();
        }
    }
};

DataWidgetManager.prototype.setUpToolBar = function () {
    var that = this;

    // Set up button to refresh all widgets
    this.dashboard.find('#refresh-charts-button').click(function() {
        that.refreshWidgets ();
    });

    // Set up Print / Shae Button
    this.dashboard.find('#print-charts-button').click(function() {
        that.printCharts();
    });

    // Create Popup Menu for hidden widgets
    new PopupDropdownMenu ({
        containerElemSelector: '#x2-hidden-data-widgets-menu-container',
        openButtonSelector: '#hidden-data-widgets-button'
    });

    // Set up minimize button 
    this.dashboard.find('#minimize-dashboard').click(function() {
        that.dashboard.find('.dashboard-inner').slideToggle();
        $(this).find('.fa').toggleClass('fa-caret-down fa-caret-left');
        that.dashboard.toggleClass('minimized');

        if ($(this).find('.fa').hasClass('fa-caret-down')) {
            that.refreshWidgets();			
        }
    });

    // If not on a report, add a Different Create Chart button
    if (!x2.reportForm) {
        this.popupDropdownMenu = new PopupDropdownMenu ({
            containerElemSelector: '#report-list',
            openButtonSelector: '#create-chart-button',
        });
        return;
    }

    $('.page-title').first().find('#report-update-button').click(function() {
            $(this).removeClass('highlight');
            that.dashboard.find('#create-chart-button').removeClass('disabled-link');
            that.dashboard.find('#save-chart-message').hide();
    });

    // Set up Create Chart button
    this.dashboard.find('#create-chart-button').click( function() {	
        if (x2.reportForm.isSaved()) {

            // Generate report if not generated
            if (!$('#generated-report').length) {
                x2.reportForm.
                    _settingsForm$.
                    find('.x2-button[type="submit"]').
                    trigger('click');
            }
            x2.chartCreator.open();
        } else {

            // Force user to save chart before making a chart
            x2.topFlashes.displayFlash(that.translations.saveChart, 'error');
            that.dashboard.find('#create-chart-button').addClass('disabled-link');
            $('.page-title').find('#report-update-button').addClass('highlight');
        }
    });

};

return DataWidgetManager;

})();
