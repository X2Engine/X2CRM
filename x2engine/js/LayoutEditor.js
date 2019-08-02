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



;

/**
 * Class to manage the profile layout editor
 */
x2.LayoutEditor = (function() {

function LayoutEditor(argsDict) {
    var defaultArgs = {
        defaultWidth: 52,
        settingName: '',
        columnWidth: null,
        margin: null,
        minWidths: [25, 25], // Minimum width for left and right columns

        // selections that are resized with the first column
        column1: [
        ],

        // selections that are resized with the second column
        column2: [
        ],
        //Element that is resized / dragged
        draggable: '',

        //overall container for the widget
        container: '',
        
        // middle icon indicator
        indicator: '.indicator',

        // Button to open the editor
        editLayoutButton: '#edit-layout',

        // Button to close the editor
        closeButton: '.close-button',

        // Button to reset the columnWidth
        resetButton: '.reset-button',

        //URL for the misc settings action
        miscSettingsUrl: null 
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this.closeButton = $(this.container).find (this.closeButton);
    this.indicator = $(this.container).find (this.indicator);
    this.resetButton = $(this.container).find (this.resetButton);

    this.resize();
    this.setUpResizability();
    this.setUpButtons();

}


LayoutEditor.prototype.resize = function() {
    var width = this.columnWidth - this.margin;
    for(var i in this.column1) {
        var elem$ = $(this.column1[i]);
        if (!elem$.is ($(this.draggable)) && elem$.css ('box-sizing') === 'border-box')
            $(this.column1[i]).width (width + '%');
        else
            $(this.column1[i]).width (width + '%');
    }

    width = 100 - this.columnWidth - this.margin;
    for(var i in this.column2) {
        $(this.column2[i]).width (width + '%');
    }

}

/**
 * Save layout settings 
 */
LayoutEditor.prototype.ajaxCall= function () {
    $.ajax({
        url: this.miscSettingsUrl,
        type: 'post',
        data: {
            settingName: this.settingName,
            settingVal: this.columnWidth
        }
    });
}

LayoutEditor.prototype.setUpButtons = function (){
    var that = this;
    $(this.editLayoutButton).add (this.closeButton).click(function(){
        $(that.container).slideToggle();
        return false;
    });

    $(this.resetButton).click(function(){
        that.columnWidth = that.defaultWidth - that.margin;
        that.resize();
        that.ajaxCall();

        if (typeof SortableWidget !== 'undefined' && 
            typeof SortableWidget.sortableWidgets !== 'undefined') {
            for (var i in SortableWidget.sortableWidgets) {
                SortableWidget.sortableWidgets[i].refresh();
            }
        }
        
    });
}

/**
 * Resize all the proper elements based on 
 * percentage rather than pixels. This function calculates 
 * the percentage then resizes all the proper elements
 */
LayoutEditor.prototype.resizeEventHandler = function (event, ui) {
    var that = this;
    // get the width of the parent 
    var parentWidth = $(that.draggable).parent().width();
    
    $(that.draggable + ' .ui-resizable-handle').width(parentWidth - 100);
    
    var percentWidth = ui.size.width / parentWidth * 100;

    // Check for min widths
    if (percentWidth < that.minWidths[0]) {
        percentWidth = that.minWidths[0];
    }

    if (100 - percentWidth < that.minWidths[1]) {
        percentWidth = 100 - that.minWidths[1];
    }

    this.columnWidth = percentWidth;
    that.resize();
};

LayoutEditor.prototype.setUpResizability = function (){
    var that = this;

    $(this.draggable).resizable({
        handles: 'e',
        start: function() {
            $(that.indicator).find('span').css('opacity', 0.0);
        },
        resize: function (event, ui) { that.resizeEventHandler (event, ui); },
        stop: function (event, ui) {
            that.resizeEventHandler (event, ui);
            that.ajaxCall();
        }
    });
}

return LayoutEditor;

})();
