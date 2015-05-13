/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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
        publisherCreateUrl: '', // url of action to call when publisher form is submitted
        isCalendar: false,
        renderTabs: true
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._selectedTabId; // id of currently selected tab

    this._tabs = {}; // dictionary of tabs indexed by tab id
    for (var i = 0; i < this.tabs.length; ++i) {
        this._tabs[this.tabs[i].id] = this.tabs[i];
    }

    x2.Widget.call (this, argsDict);
    this._init ();
}

Publisher.prototype = auxlib.create (x2.Widget.prototype);

/*
Public static methods
*/

Publisher.translations = { 'View History Item': 'View History Item' };
Publisher.loadFrame = function (id,type){
    if(type !== 'Action' && type !== 'QuotePrint') {
        var frame=
            '<iframe style=\"width:99%;height:99%\" ' +
              'src=\"' + yii.scriptUrl + '/actions/actions/viewEmail' + '?id='+id+'\"></iframe>';
    }else if(type=='Action'){
        var frame=
            '<iframe style=\"width:99%;height:99%\" ' +
              'src=\"' + yii.scriptUrl + '/actions/actions/viewAction' +
                '?id='+id+'&publisher=true\"></iframe>';
    } else if(type=='QuotePrint'){
        var frame=
            '<iframe style=\"width:99%;height:99%\" ' +
              'src=\"' + yii.scriptUrl + '/quotes/quotes/print' +
                '?id='+id+'&inline=1\"></iframe>';
    }
    if(typeof x2.actionFrames.viewEmailDialog != 'undefined') {
        if($(x2.actionFrames.viewEmailDialog).is(':hidden')){
            $(x2.actionFrames.viewEmailDialog).remove();
        }else{
            return;
        }
    }

    x2.actionFrames.viewEmailDialog = $('<div></div>', {id: 'x2-view-email-dialog'});

    x2.actionFrames.viewEmailDialog.dialog({
        title: Publisher.translations['View History Item'],
        autoOpen: false,
        resizable: true,
        width: '650px',
        show: 'fade'
    });
    $('body')
        .bind('click', function(e) {
            if($('#x2-view-email-dialog').dialog('isOpen')
                && !$(e.target).is('.ui-dialog, a')
                && !$(e.target).closest('.ui-dialog').length
            ) {
                $('#x2-view-email-dialog').dialog('close');
            }
        });

    x2.actionFrames.viewEmailDialog.data('inactive', true);
    if(x2.actionFrames.viewEmailDialog.data('inactive')) {
        x2.actionFrames.viewEmailDialog.append(frame);
        x2.actionFrames.viewEmailDialog.dialog('open').height('400px');
        x2.actionFrames.viewEmailDialog.data('inactive', false);
    } else {
        x2.actionFrames.viewEmailDialog.dialog('open');
    }
};


/*
Private static methods
*/

/*
Public instance methods
*/

Publisher.prototype.addTab = function (tab) {
    tab.publisher = this;
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

    this.getSelectedTab ().reset ();
    
    // reset save button
    auxlib.getElement(this.resolveId ('save-publisher')).removeClass('highlight');

    this.blur ();
};

Publisher.prototype.getSelectedTab = function () {
    return this._tabs[this._selectedTabId];
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
    auxlib.getElement(this.resolveId ('SelectedTab')).val(selectedTabId);
    this._selectedTabId = selectedTabId;

    // enable current tab for elements, disable inactive tab form elements
    that.DEBUG && console.log ($.extend ({}, this.tabs));
    for (var tabId in this.tabs) {
        var tab = this.tabs[tabId];
        if (this.getSelectedTab () !== tab) {
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
    if($(this.resolveId ('calendar')).length !== 0) // if we are in calendar module
        $(this.resolveId ('calendar')).fullCalendar('refetchEvents'); // refresh calendar

    if($('.list-view').length !== 0) {
        $.fn.yiiListView.update($('.list-view').attr('id'));
        this.updateTransactionalView ();
    }

     // event detected by x2chart.js
    $(document).trigger ('newlyPublishedAction');
};

Publisher.prototype.updateTransactionalView = function () {
    switch (this._selectedTabId) {
        case 'new-action':     
            x2.TransactionalViewWidget.refresh ('ActionsWidget'); 
            break;
        case 'log-a-call':     
            x2.TransactionalViewWidget.refresh ('CallsWidget'); 
            break;
        case 'new-event':     
            x2.TransactionalViewWidget.refresh ('EventsWidget'); 
            break;
        case 'new-comment':     
            x2.TransactionalViewWidget.refresh ('CommentsWidget'); 
            break;
        case 'products':     
            x2.TransactionalViewWidget.refresh ('ProductsWidget'); 
            break;
        case 'log-time-spent':     
            x2.TransactionalViewWidget.refresh ('LoggedTimeWidget'); 
            break;
    }
};

/**
 * Ad-hoc quasi-validation for the publisher
 */
Publisher.prototype.beforeSubmit = function() {
    if (!this.getSelectedTab ().validate ()) {
        return false;
    }
    return true; // form is sane: submit!
};

/**
 * Removes focus from publisher
 */
Publisher.prototype.blur = function () {
    $(this.resolveId ("save-publisher")).removeClass("highlight");
    this.getSelectedTab ().blur ();
};

/*
Private instance methods
*/

Publisher.prototype._setUpSaveButtonBehavior = function () {
    var that = this;

    // Highlight save button when something is edited in the publisher
    $(this.resolveIds (
        "#publisher-form input, #publisher-form select, #publisher-form textarea, #publisher")).
        bind("focus.compose", function(){

        $(that.resolveId ("save-publisher")).addClass("highlight");

        // close on click outside
        $(document).unbind("click.publisher").bind("click.publisher",function(e) {
            if(!$(e.target).closest (that.resolveIds ('#publisher-form' + 
                ', .ui-datepicker, .fc-day')).length && 
               $(that.resolveIds ("#publisher-form textarea")).val() === "") {
                
                that.blur ();
            }
        });

        return false;
    });

    /**
     * Submit button click handler
     */
    $(this.resolveId ('save-publisher')).click (function (evt) {
        evt.preventDefault ();
        if (!that.beforeSubmit ()) {
            return false;
        }
        that.getSelectedTab ().submit (that, that._form);
        return false;
    });

};

Publisher.prototype._init = function () {
    var that = this;

    $(function () {
        for (var i in that.tabs) that.tabs[i].run ();
        that._form = $(that.resolveId ('publisher-form')); // publisher form element
        that._setUpSaveButtonBehavior ();

        if (that.renderTabs) {
            $(that.resolveId ("publisher")).multiRowTabs({
                activate: function(event, ui) { that.tabSelected(event, ui); },
            });
            
            if ($('[aria-controls="'+that.initTabId+'"]').hasClass ('ui-state-active')) {
                that.switchToTab (that.initTabId);
            } else {
                $('[href="#' + that.initTabId +'"]').click (); // switch to initial tab
            }

            // show the tab rows now that we've instantiated the tab widget
            $(that.resolveId ('publisher') + ' > ul').show (); 
        } else {
            that._selectedTabId = that.initTabId;
        }

    });
};

return Publisher;

}) ();
