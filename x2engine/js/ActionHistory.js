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




x2.ActionHistory = (function () {

function ActionHistory (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        relationshipFlag: false,
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.Widget.call (this, argsDict);
    this._init ();
}

ActionHistory.prototype = auxlib.create (x2.Widget.prototype);

ActionHistory.prototype.update = function () {
    var that = this;
    $.fn.yiiListView.update('history',{ data:{ relationships: that.relationshipFlag }});
};

ActionHistory.prototype._setUpEvents = function () {
    var that = this;
    $(document).on('change','#history-selector',function(){
        $.fn.yiiListView.update('history',{ data:{ history: $(this).val() }});
    });
    $(document).on('click','#history-collapse',function(e){
        e.preventDefault();
        $('#history .description').toggle();
    });
    $(document).on('click','#show-history-link',function(e){
        e.preventDefault();
        $.fn.yiiListView.update('history',{ data:{ pageSize: 10000 }});
    });
    $(document).on('click','#hide-history-link',function(e){
        e.preventDefault();
        $.fn.yiiListView.update('history',{ data:{ pageSize: 10 }});
    });
    $(document).on('click','#show-relationships-link',function(e){
        e.preventDefault();
        if(that.relationshipFlag){
            that.relationshipFlag=0;
        }else{
            that.relationshipFlag=1;
        }
        that.update ();
        x2.TransactionalViewWidget.relationships = that.relationshipFlag;
        x2.TransactionalViewWidget.refreshAll ();
    });
};

ActionHistory.prototype.setUpImageAttachmentBehavior = function  () {
    var that = this;
    $('.attachment-img').each (function () {
        new x2.EnlargeableImage ({
            elem: $(this)
        });                                       
    });
}

ActionHistory.prototype._init = function () {
    this._setUpEvents ();
    var that = this;
    $(document).on('ready', function(){
        that.setUpImageAttachmentBehavior ();
    });
};

return ActionHistory;

}) ();
