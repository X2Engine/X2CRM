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




if (typeof x2 === 'undefined')
    x2 = {};

/*
actionFrames singleton
*/

x2.ActionFrames = (function () {

/*
Private properties
*/

function ActionFrames (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var that = this;
    var defaultArgs = {
        deleteActionUrl: '',
        /* required, the name of the variable in which this instance is saved. */
        instanceName: undefined, 
        afterActionUpdate: function () {
            if(typeof $.fn.yiiListView !== 'undefined' && 
               typeof $.fn.yiiListView.settings['history'] !== 'undefined') {

                $.fn.yiiListView.update('history');
                x2.TransactionalViewWidget.refreshByActionType (that.getActionType ());
            }
        }
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._lastClass = ''; // id of last clicked action index list item 
    this._frame;

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

ActionFrames.prototype.getActionType = function () {
    return $($(x2.actionFrames._frame).find ("iframe")[0].contentWindow.document).find ('form').
        attr ('data-action-type');
};

ActionFrames.prototype.setLastClass = function (lastClass) {
    this._lastClass = lastClass;
};

ActionFrames.prototype.createControls = function (id, publisher) {
    var that = this;
    if(!publisher){
        if(!$('#'+that._lastClass).prev().html()){
            $("#action-frame").contents().find('#back-button').addClass('disabled');
        }else if(!$('#'+that._lastClass).next().html()){
            $("#action-frame").contents().find('#forward-button').addClass('disabled');
        }
        $("#action-frame").contents().on('click', '.vcr-button', function(e){
            e.preventDefault();
            if($(this).attr('id') === 'back-button'){
                $('#'+that._lastClass).prev().click();
                $('#'+that._lastClass).find('a').focus();
            }else{
                $('#'+that._lastClass).next().click();
                $('#'+that._lastClass).find('a').focus();
            }
        });
    }

    $("#action-frame").contents().on('click', '.edit-button', function(e){
        e.preventDefault();
        if($("#action-frame").contents().find('.hidden-frame-form').is(':hidden')){
            $("#action-frame").contents().find('.hidden-frame-form').fadeIn();
            $("#action-frame").contents().find('.field-value').hide();
        }else{
            $("#action-frame").contents().find('.hidden-frame-form').hide();
            $("#action-frame").contents().find('.field-value').fadeIn();
        }
    });
    $("#action-frame").contents().on('click', '.complete-button', function(e){
        e.preventDefault();
        that._completeAction (id, publisher);
    });
    $("#action-frame").contents().on('click', '.uncomplete-button', function(e){
        e.preventDefault();
        that._uncompleteAction (id, publisher);
    });
    $("#action-frame").contents().on('click', '.delete-button', function(e){
        e.preventDefault();
        if(confirm("Are you sure you want to delete this action?")){
            $.ajax({
                url: that.deleteActionUrl + '?id=' + id,
                type:'POST',
                success:function(data){
                    if(data && data=='success'){
                        if(!publisher){
                            $('#'+that._lastClass).click();
                            $('#'+that._lastClass).remove();
                        }else if(typeof $.fn.yiiListView.settings['history']!='undefined'){
                            that.afterActionUpdate ();
                            $(that._frame).remove();
                        }
                    }
                }
            });
        }
    });
    $("#action-frame").contents().on('click', '.sticky-button', function(e){
        e.preventDefault();
        var link=this;
        $.ajax({
            url:yii.baseUrl+'/index.php/actions/toggleSticky?id='+id,
            success:function(data){
                if(data){
                    $(link).addClass('unsticky');
                }else{
                    $(link).removeClass('unsticky');
                }
                $('#history-'+id+' div.sticky-icon').toggle();
            }
        });
    });
};

/*
Private instance methods
*/


/**
 * @param int id
 */
ActionFrames.prototype.loadActionFrame = function (id, type, textOnly){
    textOnly = typeof textOnly === 'undefined' ? false : textOnly; 
    type = typeof type === 'undefined' ? null : type; 
    var that = this;

    var publisher=($('#publisher').html()!=null);
    var frame='<iframe id="action-frame" style="width:99%;height:99%"' +
        'src="'+yii.baseUrl+'/index.php/actions/viewAction?id='+id+'&publisher='+publisher+''+
        (textOnly ? '&textOnly=1' : '') + '" ' +
        'onload="x2.' + this.instanceName + '.createControls('+id+', true);"></iframe>';

    if(typeof that._frame !== 'undefined') {
        if($(that._frame).is(':hidden')){
            $(that._frame).remove();
        }else{
            return;
        }
    }

    that._frame = $('<div>', {
        id: 'x2-view-email-dialog'
    });

    var isResizing = false;
    var iframeFix;
    if (type === 'time') {
        var title = 'View Comment';
    } else if (type === 'call') {
        var title = 'View Call Note';
    } else if (type === 'note') {
        var title = 'View Comment';
    } else {
        var title = 'View Action';
    }
    that._frame.dialog({
        title: title,
        autoOpen: false,
        resizable: true,
        width: '650px',
        show: 'fade',
        resizeStart: function () {
            isResizing = true;
            iframeFix = new x2.IframeFixOverlay ({ elementToCover: that._frame });
            $(document).one ('mouseup', function () {
                return false;
            });
        },
        resize: function () {
            iframeFix.resize ();
        },
        resizeStop: function () {
            iframeFix.destroy ();
        }
    });

    /*
    // commented out since click outside event gets triggered on dialog resize
    // can be reintroduced after this gets fixed
    $('body').bind('click', function(e) {
        if(!isResizing && 
           $('#x2-view-email-dialog').dialog('isOpen')
           && !$(e.target).is('.ui-dialog, a')
           && !$(e.target).closest('.ui-dialog').length) {

            $('#x2-view-email-dialog').dialog('close');
        }
    });
    */

    that._frame.data('inactive', true);
    if(that._frame.data('inactive')) {
        that._frame.append(frame);
        that._frame.dialog('open').height('400px');
        that._frame.data('inactive', false);
    } else {
        that._frame.dialog('open');
    }
}

/**
 * @param ActionFrames this
 * @param int id
 * @param bool publisher
 */
ActionFrames.prototype._uncompleteAction = function (id, publisher){
    var that = this;
    var resetFlag=false;
    $.ajax({
        url:yii.baseUrl+'/index.php/actions/uncomplete',
        type:'GET',
        data:{
            'id':id
        },
        success:function(data){
            if(data === 'success'){
                if(!publisher){
                    if(that._lastClass==''){
                        that._lastClass='history-'+id;
                        resetFlag=true;
                    }
                    $('#'+that._lastClass).find('.header').html('');
                    $('#'+that._lastClass).find('.description').css('text-decoration','');
                    if(resetFlag){
                        that._lastClass='';
                    }
                }
                that.afterActionUpdate ();
                $('#action-frame').attr('src', $('#action-frame').attr('src'));
            }
        }
    });
};

/**
 * @param ActionFrames this
 * @param int id
 * @param bool publisher
 */
ActionFrames.prototype._completeAction = function (id, publisher){
    var that = this;
    var resetFlag=false;
    $("#dialog").dialog({
        autoOpen: true,
        buttons: {
            "Complete": function() {
                $(this).dialog('close');
                $.ajax({
                    url:yii.baseUrl+'/index.php/actions/complete',
                    type:'GET',
                    data:{
                        'id':id,
                        'notes':$('#completion-notes').val()
                    },
                    success:function(data){
                        if(data && data=='Success'){
                            if(!publisher){
                                if(that._lastClass==''){
                                    that._lastClass='history-'+id;
                                    resetFlag=true;
                                }
                                $('#'+that._lastClass).find('.header').html(
                                    '<span class="complete">Complete!</span>');
                                $('#'+that._lastClass).find('.description').css(
                                    'text-decoration','line-through');
                                $('#'+that._lastClass).find('.complete-box').replaceWith(
                                    '<div class="icon action-index uncomplete-box" '+
                                     'data-action-id="'+
                                        $('#'+that._lastClass).find('.complete-box').
                                            attr('data-action-id')+'">'+
                                        '<div class="icon action-index checkmark-overlay"></div>'+
                                    '</div>');
                                if(resetFlag){
                                    that._lastClass='';
                                }
                            }
                            that.afterActionUpdate ();
                            $('#action-frame').attr('src', $('#action-frame').attr('src'));
                            $('#completion-notes').val('');
                        }
                    }
                });
            },
            "Cancel":function(){
                $(this).dialog('close');
            }
        },
        show: 'fade',
        hide: 'fade',
        height:'auto',
        width:450,
        resizable:false
    });
};

ActionFrames.prototype._init = function () {
    var that = this;

    // set up frame dialog open behavior
    $(document).on('ready',function(){
        $(document).on('click','.action-frame-link',function(evt){
            var id=$(this).attr('data-action-id');
            var type=$(this).attr('data-action-type') ? 
                $(this).attr('data-action-type') : null;
            var textOnly=$(this).attr('data-text-only') ? 
                (!!(Number.parseInt ($(this).attr('data-text-only'), 10))) : null;
            evt.preventDefault ();
            that.loadActionFrame (id, type, textOnly);
        });
    });

    // action history events, these should be moved into a a different prototype
    $(document).on('click', '.complete-button', function(e){
        e.preventDefault();
        var publisher=($('#publisher-form').html()!=null);
        that._completeAction($(this).attr('data-action-id'), publisher);
    });
    $(document).on('click', '.update-button', function(e){
        e.preventDefault();
        that.loadActionFrame ($(this).attr('data-action-id'));
    });
    $(document).on('click', '.uncomplete-button', function(e){
        e.preventDefault();
        var publisher=($('#publisher-form').html()!=null);
        that._uncompleteAction($(this).attr('data-action-id'), publisher);
    });
    $(document).on('click', '.complete-box', function(e){
        e.preventDefault();
        e.stopPropagation();
        var publisher=($('#publisher-form').html()!=null);
        that._completeAction($(this).attr('data-action-id'), publisher);
    });
};

return ActionFrames;

}) ();


