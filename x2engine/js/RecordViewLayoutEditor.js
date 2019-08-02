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
x2.RecordViewLayoutEditor = (function() {

function RecordViewLayoutEditor (argsDict) {
    var defaultArgs = {
        mainColumnSelector: null,
        responsiveCssSelector: null,
        mainColumnResponsiveRange: [0, 0],
        singleColumnThresholdNoWidgets: 1130,
        singleColumnThreshold: 1407,
        //minWidth: 400,
        dimensions: {
            singleColumnThresholdNoWidgets: 1130, 
            singleColumnThreshold: 1407, 
            extraContentWidth: 170, 
            rightWidgetWidth: 280, 
            formLayoutWidthThreshold: 610 
        }
    };
    this._hasEdited = false;
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this.dimensions = auxlib.map (function (a) { return parseFloat (a, 10); }, this.dimensions);
    //this.dimensions.formLayoutWidthThreshold += 20;
    x2.LayoutEditor.call (this, argsDict);
}

RecordViewLayoutEditor.prototype = auxlib.create (x2.LayoutEditor.prototype);

RecordViewLayoutEditor.prototype.resize = function() {
    x2.LayoutEditor.prototype.resize.call (this);
}

RecordViewLayoutEditor.prototype.getFormLayoutResponsiveThreshold = function (rightWidgets) {
    rightWidgets = typeof rightWidgets === 'undefined' ? true : rightWidgets; 
    var extraContentWidth = this.dimensions.extraContentWidth;
    var columnWidthRatio = this.columnWidth / 100;
    if (rightWidgets) {
        extraContentWidth += this.dimensions.rightWidgetWidth;
    }
    return extraContentWidth + this.dimensions.formLayoutWidthThreshold + 
        (this.dimensions.formLayoutWidthThreshold * (1 - columnWidthRatio)) / columnWidthRatio;
}

RecordViewLayoutEditor.prototype.getSingleColumnThreshold = function (noWidgets) {
    if (noWidgets) {    
        return this.dimensions.singleColumnThresholdNoWidgets;
    } else {
        return this.dimensions.singleColumnThreshold;
    }
};

/**
 * Converts between detail view single column and multi-column layouts
 */
RecordViewLayoutEditor.prototype.detailViewResizeHandler = function () {
    var windowWidth = window.innerWidth || $(window).width ();

    //console.log ('thresholds');
    //console.log (this.getSingleColumnThreshold (noWidgets));
    //console.log (this.getFormLayoutResponsiveThreshold (!noWidgets));

    var noWidgets = $('body').hasClass ('no-widgets');
    if (windowWidth > this.getSingleColumnThreshold (noWidgets) &&
        windowWidth < this.getFormLayoutResponsiveThreshold (!noWidgets)) {

        $(this.mainColumnSelector).addClass ('force-single-column');
    } else {
        $(this.mainColumnSelector).removeClass ('force-single-column');
    }
};

RecordViewLayoutEditor.prototype.resizeEventHandler = function (event, ui) {
    var that = this;
    if (!this._hasEdited) {
        $(this.responsiveCssSelector).remove ();
        this._hasEdited = true;
        $(window).bind ('resize.RecordViewLayoutEditor.resizeEventHandler', function () {
            that.detailViewResizeHandler ();
        });
    }
    x2.LayoutEditor.prototype.resizeEventHandler.apply (this, arguments);
};

return RecordViewLayoutEditor;

})();
