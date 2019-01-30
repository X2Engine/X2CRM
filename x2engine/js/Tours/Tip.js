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




/********************************************************
* Tours Tip  Abstract Class
*
* Depends on: Tours.js
* Child Classes: PopopTip.js, BlockTip.js, FlashTip.js
********************************************************/
x2.Tip = (function(){

    var Tip = function (element) {
        var defaultArgs = {
            id: null, 
            category: 'default'
        };

        // Tip default properties work different from the conventional way
        // All properties are taken from the data- html attributes on the container
        this.element = $(element);
        auxlib.applyArgs (this, defaultArgs, this.element.data());

        this._init();
    }

    /**
     * Function to create a child of Tours
     * @param  obj         Child Object
     * @param  element     Tours element
     * @param  defaultArgs default Args of the child
     */
    Tip.child = function (obj, element, defaultArgs) {
        Tip.call(obj, element);
        if (typeof defaultArgs === 'undefined') {
            return;
        }

        auxlib.applyArgs(obj, defaultArgs, obj.element.data());
    }

    /**
     * Base Initialization Method
     * Sets up Closing
     */
    Tip.prototype._init = function() {
        var that = this;

        // The checkbox labeled 'Hide all tips on this page'
        this.enoughCheckbox = this.element.find('.enough input').first();

        // Bind Events to close buttons
        this.element.find('.close, .got-it').click(function(){

            // If this tip is not part of a specific tour, 
            // register it as seen
            that.seen();

            // Close tip
            that.close();

            // Open the next tip
            if (!that.enoughCheckbox.is(':checked')) {
                x2.Tours.openNext();
            } else {
                x2.Tours.closeAll();
            }
        });

        // Set up the disable button
        this.element.find('.disable').click(function(){
            that.disableAll();
            that.seen();
            that.close();
            x2.Tours.closeAll();
        });

        // Call child initialization function
        this.init();
    };


    /**
     * Sends a request to mark this tip as seen
     */
    Tip.prototype.seen = function () {
        var that = this;
        
        // Request for tour seen, with the option of
        // supressing future tips
        $.ajax ({
            url: yii.scriptUrl + '/site/tourSeen',
            data: {
                id: this.id,
            },
        });
    }

    /**
     * Sends a request to mark this tip as seen
     */
    Tip.prototype.disableAll = function () {

        // Disables this profile from seeing anymore tips
        $.ajax ({
            url: yii.scriptUrl + '/profile/disableTours',
        });
    }

    /************************************************
    * Abstract Methods
    *************************************************/

    /**
     * Initializes this tip
     */
    Tip.prototype.init = function(){}

    /**
     * Hides this tip
     */
    Tip.prototype.hide = function () {}

    /**
     * Closes this tip
     */
    Tip.prototype.close = function () {}

    /**
     * Opens this tip
     */
    Tip.prototype.open = function () {}

    return Tip;
})();
