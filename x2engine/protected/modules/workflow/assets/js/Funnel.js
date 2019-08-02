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




if (typeof x2 === 'undefined') x2 = {};

x2.Funnel = (function () {

var Point = x2.geometry.Point;

/**
 * Funnel used on the workflow funnel view page
 */
function Funnel (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;

    var defaultArgs = {
        stageValues: null, // array of projected deal values for each stage
        totalValue: null, // formatted sum of stageValues
        recordsPerStage: null, // array of record counts per stage
        stageNameLinks: null, // array of links which open stage details
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.BaseFunnel.call (this, argsDict);

    this._stageHeight = 32; // temporary. replace when stage heights are depend on status

    this._init ();
}

Funnel.prototype = auxlib.create (x2.BaseFunnel.prototype);


/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/

/*
Private instance methods
*/

/**
 * Place stage counts on top of funnel
 */
Funnel.prototype._addStageCounts = function () {
    var that = this;
    /*var canvasTopLeft = new Point ({
        x: $(this.containerSelector).position ().left,
        y: $(this.containerSelector).position ().top,
    });*/

    for (var i = 0; i < this.stageCount; i++) {

        // create a container element for the stage count and position it near the centroid of the
        // stage trapezoid.
        var stageCountContainer = $('<span>', {
            'class': 'funnel-stage-count',
            html: '<b>' + this.recordsPerStage[i] + '</b>',
            css: {
                position: 'absolute',
                width: '100px',
                'text-align': 'center',
                left: this._stageCentroids[i].x - 50,
                top: this._stageCentroids[i].y - 10,
                'font-size': '30px',
                'margin-top': '-8px',
                'text-shadow': 'rgba(250,250,250,0.5) 0px 2px 0px'
            }
        });

        $(this.containerSelector).append (stageCountContainer);

        // click the stage name link when the corresponding stage count is clicked
        $(stageCountContainer).click ((function () {
            var j = i; 
            return function () {
                $('.stage-name-link-' + j).click ();
                return false;
            };
        }) ());
    }
};

/**
 * Place stage name links to the left of the funnel with y coordinate aligned with stage centroid 
 */
Funnel.prototype._addStageNameLinks = function () {
    var that = this;
    for (var i = 0; i < this.stageCount; i++) {
        var link = $(this.stageNameLinks[i]);
        $(link).addClass ('stage-name-link-' + i);
        $(link).addClass ('stage-name-link');
        $(link).css ({
            top: this._stageCentroids[i].y - 7 
        });
        $(this.containerSelector).append (link);
    }

    // retrieve max width of stage name links and shift all links over by that amount
    var maxWidth = Math.max.apply (null, auxlib.map (function (a) {
        return $(a).width ();
    }, $.makeArray ($(this.containerSelector).find ('.stage-name-link'))));

    var extraSpace = 20;
    $(this.containerSelector).find ('.stage-name-link').each (function (i, elem) {
        $(elem).css ('left', -maxWidth - extraSpace);
    });

    var extraMargin = 18;
    $(this.containerSelector).css (
        'margin-left', maxWidth + extraSpace + extraMargin);

};

/**
 * Place stage values in a column to the right of the funnel with y coordinate aligned with stage 
 * centroid 
 */
Funnel.prototype._addStageValues = function () {
    var that = this;
    for (var i = 0; i < this.stageCount; i++) {
        var stageValueContainer = $('<span>', {
            'class': 'funnel-stage-value',
            html: '<b>' + this.stageValues[i] + '</b>',
            css: {
                position: 'absolute',
                right: -(this._funnelW1 / 2) - 15,
                top: this._stageCentroids[i].y - 10,
            }
        });
        $(this.containerSelector).append (stageValueContainer);
    }
};

/**
 * Add totals row below the funnel 
 */
Funnel.prototype._addTotals = function () {
    var that = this;
    var totalRecordsContainer = $('<span>', {
        'class': 'funnel-total-records',
        html: this.translations['Total Records'] + ': <b>' +
            auxlib.reduce (function (a, b) { return a + b; }, 
            auxlib.map (function (a) { return parseInt (a, 10); }, this.recordsPerStage)) + '</b>',
        css: {
            position: 'absolute',
            left: $(this.containerSelector).find ('.stage-name-link').last ().css ('left'),
            top: this._funnelHeight + 10,
        }
    });
    $(this.containerSelector).append (totalRecordsContainer);

};


/**
 * Populate _stageHeights property with heights of individual stages 
 */
Funnel.prototype._calculateStageHeights = function () {
    var that = this;
    // calculate stage heights
    this._stageHeights = [];

    // each stage is given the same height
    for (var i = 0; i < this.stageCount; i++) {
        this._stageHeights.push (this._stageHeight);
    }
};

Funnel.prototype._calculateFunnelHeight = function () {
    this._funnelHeight = this._stageHeight * this.stageCount;
};

/**
 * Overrides parent method. Adds stage height calculation
 */
Funnel.prototype._calculatePreliminaryData = function () {
    var that = this; 
    this._calculateStageHeights (); 
    this._calculateFunnelHeight (); 
    x2.BaseFunnel.prototype._calculatePreliminaryData.call (this);
};

Funnel.prototype._init = function () {
    var that = this;

    x2.BaseFunnel.prototype._init.call (this);
    that._addStageCounts ();
    that._addStageNameLinks ();
    that._addStageValues ();
    that._addTotals ();
};

return Funnel;

}) ();

