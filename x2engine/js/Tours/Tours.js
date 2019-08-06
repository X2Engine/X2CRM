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




/************************************************
* Static Tours object to manage tours
*************************************************/
x2.Tours = (function() {

    var Tours = {};

    /**
     * Static List of all Tours
     */
    Tours.list = [];

    /**
     * Dictionary of children classes.
     * The key is the selector to identify the tips 
     * that should be instantiated as these classes
     * Populate this object within the child class
     */
    Tours.classes = {
        /**
         * <selector>: JS Class Object
         * .popup: PopupTip
         * .block: BlockTip
         * .flash: FlashTip
         */
    };

    /**
     * Initialize all Tours on the page. 
     */
    Tours.initialize = function () {
        $('.x2-tour').each(function(){
            // Create tip object
            var tip = Tours.create (this);
            // Append it to the list
            Tours.list.push(tip);
        });

        // Hide the "Hide tips on this page"
        // If there is only one tip
        if (Tours.list.length == 1) {
            $('.x2-tour .enough').hide();
        }

    };

    /**
     * Creates a tip object from a tip element with the correct class
     * @param  .x2-tour element to create the tip from
     * @return tip object
     */
    Tours.create = function (element) {
        var tipClass = null;

        // Choose child class based on html class
        for(var selector in Tours.classes) {
            if($(element).is(selector)) {
                tipClass = Tours.classes[selector];
                break;
            }
        }

        if (tipClass == null) return;

        // Intantiate class
        var tip = new tipClass(element);
        return tip;
    };

    // Remove a tour from the list
    Tours['delete'] = function(tour) {
        var index = Tours.list.indexOf(tour);
        if (index >= 0) {
            delete Tours.list[index];
        }
    };

    /**
     * Opens the next Tour in the sequence
     */
    Tours.openNext = function() {
        if (Tours.list.length == 0 ){
            return;
        }

        Tours.list.shift().open();
    };

    /**
     * Marks all tips as seen;
     */
    Tours.closeAll = function () {
        for (var i in Tours.list) {
            Tours.list[i].seen();
            Tours.list[i].close();
        };

        Tours.list = [];
    };

    // Once DOM is ready intialize the tours
    $(function(){
        // Set up all tours
        Tours.initialize();
        Tours.openNext();
    });

    return Tours;
})();


