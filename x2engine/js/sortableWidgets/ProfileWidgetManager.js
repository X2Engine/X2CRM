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
 * Manages behavior of profile widgets as a set. Behavior of individual profile widgets is managed
 * in separate Widget prototypes.
 */

/**
 * Constructor 
 * @param dictionary argsDict A dictionary of arguments which can be used to override default values
 *  specified in the defaultArgs dictionary.
 */
function ProfileWidgetManager (argsDict) {
    var defaultArgs = {
        cssSelectorPrefix: 'profile-', 
        widgetType: 'profile',
        connectedContainerSelector: '', // class shared by all columns containing sortable widgets
        createProfileWidgetUrl: '',
        
        createChartingWidgetUrl: ''
        
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

	TwoColumnSortableWidgetManager.call (this, argsDict);	
}

ProfileWidgetManager.prototype = auxlib.create (TwoColumnSortableWidgetManager.prototype);

/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/

/**
 * Override parent method. In addition to parent behavior, check if widget layout should be changed 
 */
ProfileWidgetManager.prototype.addWidgetToHiddenWidgetsMenu = function (widgetSelector) {
    TwoColumnSortableWidgetManager.prototype.addWidgetToHiddenWidgetsMenu.call (
        this, widgetSelector);
    x2.profile.checkRemoveWidgetsColumn ();
};

/*
Private instance methods
*/

ProfileWidgetManager.prototype._setUpAddProfileWidgetMenu = function () {
};

/**
 * Show text in hidden profile widget menu indicating that there aren't any hidden widgets 
 */
ProfileWidgetManager.prototype._hideShowHiddenProfileWidgetsText = function () {
    if (this.hiddenWidgetsMenuIsEmpty ())
        $(this._hiddenWidgetsMenuSelector).find ('.no-hidden-'+this.cssSelectorPrefix+'widgets-text').show ();
    else
        $(this._hiddenWidgetsMenuSelector).find ('.no-hidden-'+this.cssSelectorPrefix+'widgets-text').hide ();
};

ProfileWidgetManager.prototype._afterCloseWidget = function () {
    this._hideShowHiddenProfileWidgetsText ();
};

/**
 * Check if layout should be rearranged after widget is added to layout 
 */
ProfileWidgetManager.prototype._afterShowWidgetContents = function () {
    this._hideShowHiddenProfileWidgetsText ();
    x2.profile.checkAddWidgetsColumn (); 
};

ProfileWidgetManager.prototype._createProfileWidget = function (widgetType, callback) {
    var that = this;
    $.ajax ({
        url: this.createProfileWidgetUrl,
        data: {
            'widgetType': widgetType,
            'widgetLayoutName': this.widgetType
        },
        type: 'POST',
        dataType: 'json',
        success: function (data) {
            if (data !== 'failure') {
                $(that._widgetsBoxSelector).append (data.widget);
                hideShowHiddenWidgetSubmenuDividers ();
                that._afterShowWidgetContents ();
                callback ();
            }
        }
    });
};


/**
 * Creates a charting widget on the dashboard. 
 * Since the options are the charting layouts in reports, 
 * We use all the information necessary to call add to dashbaord
 * in the reports controller
 */
ProfileWidgetManager.prototype._createChartingWidget = function (settings,callback) {
    var that = this;

    $.ajax ({
        url: this.createChartingWidgetUrl,
        data: {
            widgetClass: settings['widgetClass'],
            widgetUID: settings['widgetUID'],
            destination: 'profile',
            widgetType: 'data',
            settingsModelName: 'Reports',
            settingsModelId: settings['modelId']
        },
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data !== 'failure') {
                $(that._widgetsBoxSelector).append (data.widget);
                hideShowHiddenWidgetSubmenuDividers ();
                that._afterShowWidgetContents ();
                callback ();
            }
        }
    });
};



ProfileWidgetManager.prototype._setUpCreateWidgetDialog = function () {
    var that = this;
    var dialog$ = $('#create-'+this.cssSelectorPrefix+'widget-dialog').dialog ({
        title: this.translations['createProfileWidgetDialogTitle'],
        autoOpen: false,
        width: 500,
        buttons: [
            {
                text: that.translations['Cancel'],
                click: function () {
                    $(this).dialog ('close');
                }
            },
            {
                text: that.translations['Create'],
                'class': 'highlight',
                click: function () {
                    var widgetType = $(this).find ('#widgetType').val ();
                    var callback = function (){
                        dialog$.dialog ('close'); }; 

                    
                    // Create a special case for a datawidget
                    if (widgetType == 'DataWidget') {
                        var settings = JSON.parse($(this).find('#chartName').val());
                        that._createChartingWidget(settings, callback);
                        return;
                    }

                    

                    that._createProfileWidget (widgetType, callback);
                }
            }
        ]
    });

    
    dialog$.find('#widgetType').change(function (){
        dialog$.find('#chart-name-container').toggle ($(this).val() == 'DataWidget');
    })
    

    // create-profile-widget-button
    $('#create-'+this.cssSelectorPrefix+'widget-button').click (function () {
        dialog$.dialog ('open');
    });

};


ProfileWidgetManager.prototype._init = function () {
    this._setUpAddProfileWidgetMenu ();
    this._setUpCreateWidgetDialog ();
    TwoColumnSortableWidgetManager.prototype._init.call (this);
};
