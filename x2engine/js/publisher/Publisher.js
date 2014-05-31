/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

/**
 * Manages behavior of publisher widget
 */

if(typeof x2 == 'undefined')
    x2 = {};
if(typeof x2.publisher == 'undefined')
    x2.publisher = {};
if(typeof x2.actionFrames == 'undefined')
    x2.actionFrames = {};


x2.Publisher = (function () {

function Publisher (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        translations: {}, 
        tabs: [], // PublisherTab objects
        initTabId: null, // id of initially active tab 
        publisherCreateUrl: '' // url of action to call when publisher form is submitted
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._selectedTab; // id of currently selected tab

    this._tabs = {}; // dictionary of tabs indexed by tab id
    for (var i = 0; i < this.tabs.length; ++i) {
        this._tabs[this.tabs[i].id] = this.tabs[i];
    }

    this._init ();
}

/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/

Publisher.prototype.addTab = function (tab) {
    this._tabs[tab.id] = tab;
    this.tabs.push (tab);
};

Publisher.prototype.getForm = function () {
    return this._form;
};

/**
 * "Magic getter" method which caches jQuery objects so they don't have to be
 * looked up a second time from the DOM
 */
Publisher.prototype.getElement = function (selector) {
    if(typeof this._elements[selector] == 'undefined')
        this._elements[selector] = this._form.find(selector);
    return this._elements[selector];
};

/**
 * Clears the publisher of input, i.e. after each use.
 */
Publisher.prototype.reset = function () {
    var that = this;

    this._selectedTab.reset ();
    
    // reset save button
    auxlib.getElement('#save-publisher').removeClass('highlight');

    this.blur ();
};

/**
 * Change the mode of the publisher form based on a selected tab.
 *
 * @param selectedTab ID of the tab.
 */
Publisher.prototype.switchToTab = function (selectedTabId) {
    var that = this;

    $('[aria-controls="' + selectedTabId + '"]').parent ().removeClass ('unselected-tab-row');
    $('[aria-controls="' + selectedTabId + '"]').parent ().siblings ().
        addClass ('unselected-tab-row');

    that.DEBUG && console.log ('selectedTabId = ');
    that.DEBUG && console.log (selectedTabId);

    // set field SelectedTab for use in POST request
    auxlib.getElement('#SelectedTab').val(selectedTabId);
    this._selectedTab = this._tabs[selectedTabId];

    that.DEBUG && console.log (this._selectedTab);
    // enable current tab for elements, disable inactive tab form elements
    that.DEBUG && console.log ($.extend ({}, this.tabs));
    for (var tabId in this.tabs) {
        var tab = this.tabs[tabId];
        if (this._selectedTab !== tab) {
            that.DEBUG && console.log ('disabling:');
            that.DEBUG && console.log (tab);
            tab.disable ();
            tab.blur ();
        } else {
            tab.enable ();
        }
    }
}

/**
 * Callback associated with clicking on a tab:
 */
Publisher.prototype.tabSelected = function (event, ui) {
    var that = this;
    that.DEBUG && console.log (ui.newTab);
    that.DEBUG && console.log ('tabSelected');
    that.switchToTab(ui.newTab.attr('aria-controls'));
}

/**
 * Updates to perform after publisher form gets submitted
 */
Publisher.prototype.updates = function () {
    if($('#calendar').length !== 0) // if we are in calendar module
        $('#calendar').fullCalendar('refetchEvents'); // refresh calendar

    if($('.list-view').length !== 0)
        $.fn.yiiListView.update($('.list-view').attr('id'));

     // event detected by x2chart.js
    $(document).trigger ('newlyPublishedAction');
};

/**
 * Ad-hoc quasi-validation for the publisher
 */
Publisher.prototype.beforeSubmit = function() {
    if (!this._selectedTab.validate ()) {
        return false;
    }
    return true; // form is sane: submit!
};

/**
 * Removes focus from publisher
 */
Publisher.prototype.blur = function () {
    $("#save-publisher").removeClass("highlight");
    this._selectedTab.blur ();
};

/*
Private instance methods
*/

Publisher.prototype._setUpSaveButtonBehavior = function () {
    var that = this;

    // Highlight save button when something is edited in the publisher
    $("#publisher-form input, #publisher-form select, #publisher-form textarea, #publisher").
        bind("focus.compose", function(){

        $("#save-publisher").addClass("highlight");

        // close on click outside
        $(document).unbind("click.publisher").bind("click.publisher",function(e) {
            if(!$(e.target).closest ("#publisher-form, .ui-datepicker, .fc-day").length && 
               $("#publisher-form textarea").val() === "") {
                
                that.blur ();
            }
        });

        return false;
    });

    /**
     * Submit button click handler
     */
    $('#save-publisher').click (function (evt) {
        evt.preventDefault ();
        if (!that.beforeSubmit ()) {
            return false;
        }
        that._selectedTab.submit (that, that._form);
        return false;
    });

};

Publisher.prototype._init = function () {
    var that = this;

    $(function () {
        for (var i in that.tabs) that.tabs[i].run ();
        that._form = $('#publisher-form'); // publisher form element
        that._setUpSaveButtonBehavior ();

        $("#publisher").multiRowTabs({
            activate: function(event, ui) { that.tabSelected(event, ui); },
        });
        
        if ($('[aria-controls="'+that.initTabId+'"]').hasClass ('ui-state-active')) {
            that.switchToTab (that.initTabId);
        } else {
            $('[href="#' + that.initTabId +'"]').click (); // switch to initial tab
        }

        // show the tab rows now that we've instantiated the tab widget
        $('#publisher > ul').show (); 

    });
};

return Publisher;

}) ();
