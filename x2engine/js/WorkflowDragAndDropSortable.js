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
 * Extends functionality of jQuery UI sortable for the purposes of the workflow drag and drop UI.
 */


$.widget ("x2.workflowDragAndDropSortable", $.ui.sortable, {
    /**
     * Overrides parent method
     */
	_mouseStart: function(event, overrideHandle, noActivation) {
        // keep track of the original sort list  
        this.originalContainer = this;

        // determine whether dragged list item is the last item in the list
        if ($(this.originalContainer.element).children ().last ().get (0) === this.currentItem.get (0))
            this.isLastItem = true;
        else
            this.isLastItem = false;
        this._super (event, overrideHandle, noActivation);
    },
    /**
     * Overrides parent method so that sort lists have the following behavior:
     *  -within the original list, the list item remains in its original location
     *  -within other lists, the list item will always be prepended to the list, maintaining 
     *   lastUpdated ordering
     */
	_rearrange: function(event, i /* item with least distance */, a /* container */, hardRefresh) {
        var currList = i ? $(i.item).closest ('.list-view') : a.closest ('.list-view');
        var origList = this.originalContainer.element.closest ('.list-view');

        if (currList.attr ('id') !== origList.attr ('id') && this.isLastItem) {
            // item is last item in list, append to list

            originalRearrange.call (this, event, null, currList.find ('.items'), hardRefresh, true);
        } else if (currList.attr ('id') !== origList.attr ('id')) {
            // item is being dropped into another container, prepend it to that container's list

            originalRearrange.call (this, event, null, currList.find ('.items'), hardRefresh);
        } else if (this.currentItem.next () || this.currentItem.prev ()) {
            // item is being dropped into original container, return it to its original position

            var prev;
            if (this.currentItem.next ()) {
                var sibling = this.currentItem.next ();
                prev = false;
            } else {
                var sibling = this.currentItem.prev ();
                prev = true;
            }
            //console.log ('sibling = ');
            //console.log (sibling);

            originalRearrange.call (this, event, {item: [$(sibling).get (0)]}, null, hardRefresh, prev);
        } 

        /*!
         * This function is a modified version of a base jQuery UI function
         * jQuery UI Sortable @VERSION
         * http://jqueryui.com
         *
         * Copyright 2012 jQuery Foundation and other contributors
         * Released under the MIT license.
         * http://jquery.org/license
         *
         * http://api.jqueryui.com/sortable/
         */
        function originalRearrange (event, i, a, hardRefresh, append, prev) {
            //console.log (i);
            /* x2modstart */ 
            // modified so that place holder is prepended, instead of appended
            var append = typeof append === 'undefined' ? false : append; 
            if (append)
                a ? $(a[0]).append($(this.placeholder[0])) : i.item[0].parentNode.insertBefore(this.placeholder[0], (!prev ? i.item[0] : i.item[0].nextSibling));
            else 
                a ? $(a[0]).prepend($(this.placeholder[0])) : i.item[0].parentNode.insertBefore(this.placeholder[0], (!prev ? i.item[0] : i.item[0].nextSibling));

            /* x2modend */ 
            //Various things done here to improve the performance:
            // 1. we create a setTimeout, that calls refreshPositions
            // 2. on the instance, we have a counter variable, that get's higher after every append
            // 3. on the local scope, we copy the counter variable, and check in the timeout, if it's still the same
            // 4. this lets only the last addition to the timeout stack through
            this.counter = this.counter ? ++this.counter : 1;
            var counter = this.counter;

            this._delay(function() {
                if(counter == this.counter) this.refreshPositions(!hardRefresh); //Precompute after each DOM insertion, NOT on mousemove
            });
        }
    }
});



