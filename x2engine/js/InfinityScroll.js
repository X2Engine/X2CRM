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




x2.InfinityScroll = (function () {

function InfinityScroll (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        /**
         * Function which retrieves next or previous page of results
         * @param bool next Whether next or previous page of results should be retrieved 
         */
        callback: function (next) {}
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.Widget.call (this, argsDict);
    this.id = InfinityScroll.instanceCount++;
    this._init ();
}

/**
 * Used to generate unique ids 
 */
InfinityScroll.instanceCount = 0;

InfinityScroll.prototype = auxlib.create (x2.Widget.prototype);

InfinityScroll.prototype._init = function () {
    var that = this;
    var pause = null;
    var prevScrollPos = 0;
    var movingDown = true;
    var mousedown = false;
    $(document).on ('mousedown.InfinityScroll::setUpEvents' + this.id, function () {
        mousedown = true;
    });
    $(document).on ('mouseup.InfinityScroll::setUpEvents' + this.id, function () {
        mousedown = false;
    });

    this.element$.scroll (function () {
        var scrollHeight = $(this).prop ('scrollHeight'); 
        var clientHeight = $(this).prop ('clientHeight'); 
        var scrollPos = $(this).scrollTop ();
        var movingDown = scrollPos >= prevScrollPos;
        var movingUp = scrollPos <= prevScrollPos;

        if (!pause) {
            var setPause = false;
            if (movingDown && scrollHeight - scrollPos - clientHeight < 10) {
                //console.log ('next page');
                that.callback (true);
                setPause = true;
            } else if (movingUp && scrollPos < 10) {
                //console.log ('prev page');
                that.callback (false);
                setPause = true;
            }
            if (setPause) {
                pause = window.setTimeout (function () { 
                    pause = null;
                    // Check again to see if we're still past the pagination threshold.
                    // This check is necessary if user drags and holds scroll bar to the bottom
                    // of the container.
                    if (mousedown) that.element$.scroll (); 
                }, 300)
            }
        }
        prevScrollPos = scrollPos;
    });
};

return InfinityScroll;

}) ();
