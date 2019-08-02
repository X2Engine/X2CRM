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

/**
 * Utility class for CSS manipulation
 */

x2.css = (function () {

function X2CSS () {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.Component.call (this, argsDict);
}

X2CSS.prototype = auxlib.create (x2.Component.prototype);

/**
 * add inline css to element
 * @param string|object css string or rules object
 * @param bool merge whether to merge rules with existing inline style
 */
X2CSS.prototype.css = function (elem$, rules, merge) {
    merge = typeof merge === 'undefined' ? true : merge; 
    if ($.type (rules) === 'string') rules = this.parseStyle (rules);
    if (merge) rules = $.extend (this.parseStyle (elem$.attr ('style')), rules);
    var css = this.stringify (rules);
    elem$.attr ('style', css); 
    return elem$;
};

/**
 * convert a css string into a dictionary of css rules
 * @param string
 * @return object 
 */
X2CSS.prototype.parseStyle = function (style) {
    if (!style) return {};
    var rules = style.split (/;/);
    var css = {};
    for (var i in rules) {
        var rule = rules[i].split (/:/);
        if (rule.length !== 2) continue;
        css[$.trim (rule[0])] = $.trim (rule[1]);
    }
    return css;
};

/**
 * convert a dictionary of css rules into a css string
 * @param object
 * @return string
 */
X2CSS.prototype.stringify = function (dict) {
    var css = '';
    for (var attr in dict) {
        css += attr + ': ' + dict[attr] + ';';
    }
    return css;
};

/**
 * generate cross-browser compatible linear gradient css
 * @return string
 */
X2CSS.prototype.linearGradient = function (start, end) {
    return [ 
        'background: ' + tinycolor.mix (start, end) + ';', 
        'background: -moz-linear-gradient(top, ' + start + ', ' + end + ');',
        'background: -webkit-linear-gradient(top, ' + start + ', ' + end + ');',
        'background: -o-linear-gradient(top, ' + start + ', ' + end + ');',
        'background: -ms-linear-gradient(top, ' + start + ', ' + end + ');',
        'background: linear-gradient(top, ' + start + ', ' + end + ');'
    ].join (' ');

};

return new X2CSS;

}) ();
