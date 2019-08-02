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

x2.geometry = {};

x2.geometry.Point = (function () {

/**
 * Specify either cartesian xor polar coordinates
 * 
 * @param number x 
 * @param number y 
 * @param number r 
 * @param number theta (radians)
 * @param bool polar 
 */
function Point (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;

    var defaultArgs = {
        DEBUG: false && x2.DEBUG,
        x: null,
        y: null,
        r: null,
        theta: null,
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    var that = this;

    if (this.x !== null && this.y !== null) {
        this._cartToPolar ();
    } else if (this.r !== null && this.theta !== null) {
        this._polarToCart ();
    } else {
        throw new Error ('Precondition violated');
    }

    this._init ();
}



/*
Public static methods
*/

/**
 * @param Point pointa
 * @param Point pointb
 * @param Point pointc
 * @param Point pointd
 * @return Point the intersect of the lines (point1a, point1b) and (point2a, point2b)
 */
Point.getIntersect = function (point1a, point1b, point2a, point2b) {
    var intersect = new Point ({
        x: ((point1a.x * point1b.y - point1a.y * point1b.x) * (point2a.x - point2b.x) - 
                (point1a.x - point1b.x) * (point2a.x * point2b.y - point2a.y * point2b.x)) /
            ((point1a.x - point1b.x) * (point2a.y - point2b.y) - 
                (point1a.y - point1b.y) * (point2a.x - point2b.x)),
        y: ((point1a.x * point1b.y - point1a.y * point1b.x) * (point2a.y - point2b.y) - 
                (point1a.y - point1b.y) * (point2a.x * point2b.y - point2a.y * point2b.x)) /
            ((point1a.x - point1b.x) * (point2a.y - point2b.y) - 
                (point1a.y - point1b.y) * (point2a.x - point2b.x))
    });
    return intersect;
};

/*
Private static methods
*/

/*
Public instance methods
*/

/**
 * Treat points as vectors and add them 
 * @param Point point
 */
Point.prototype.addAsVectors = function (point) {
    return new Point ({
        x: this.x + point.x,
        y: this.y + point.y
    });
};


/**
 * Overrides Object's to string method to produce a formatted version of the point
 */
Point.prototype.toString = function () {
    return '(' + this.x + ', ' + this.y + ')';
};

/*
Private instance methods
*/

/**
 * Calculates theta and r based on values of x and y 
 */
Point.prototype._cartToPolar = function () {
    this.theta = Math.atan2 (this.y, this.x);
    this.r = Math.sqrt (this.x * this.x + this.y * this.y);
};


/**
 * Calculates x and y based on values of theta and r 
 */
Point.prototype._polarToCart = function () {
    this.x = (Math.cos (this.theta) * this.r);
    this.y = (Math.sin (this.theta) * this.r);
};


Point.prototype._init = function () {
    var that = this;

};

return Point;

}) ();

