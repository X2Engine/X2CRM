/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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

function createControls(id, publisher){
    if(!publisher){
        if(!$('#'+lastClass).prev().html()){
            $("#action-frame").contents().find('#back-button').addClass('disabled');
        }else if(!$('#'+lastClass).next().html()){
            $("#action-frame").contents().find('#forward-button').addClass('disabled');
        }
        $("#action-frame").contents().on('click', '.vcr-button', function(e){
            e.preventDefault();
            if($(this).attr('id')=='back-button'){
                $('#'+lastClass).prev().click();
                $('#'+lastClass).find('a').focus();
            }else{
                $('#'+lastClass).next().click();
                $('#'+lastClass).find('a').focus();
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
        completeAction(id, publisher);
    });
    $("#action-frame").contents().on('click', '.uncomplete-button', function(e){
        e.preventDefault();
        uncompleteAction(id, publisher);
    });
    $("#action-frame").contents().on('click', '.delete-button', function(e){
        e.preventDefault();
        if(confirm("Are you sure you want to delete this action?")){
            $.ajax({
                url:yii.baseUrl+'/index.php/actions/delete?id='+id,
                type:'POST',
                data:{
                    'id':id
                },
                success:function(data){
                    if(data && data=='Success'){
                        if(!publisher){
                            $('#'+lastClass).click();
                            $('#'+lastClass).remove();
                        }else if(typeof $.fn.yiiListView.settings['history']!='undefined'){
                            $.fn.yiiListView.update('history');
                            $(x2ViewEmailDialog).remove();
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
}
function loadActionFrame(id){
    var publisher=($('#publisher-form').html()!=null);
    var frame='<iframe id="action-frame" style="width:99%;height:99%" src="'+yii.baseUrl+'/index.php/actions/viewAction?id='+id+'&publisher='+publisher+'" onload="createControls('+id+', true);"></iframe>';
    if(typeof x2ViewEmailDialog != 'undefined') {
        if($(x2ViewEmailDialog).is(':hidden')){
            $(x2ViewEmailDialog).remove();

        }else{
            return;
        }
    }

    x2ViewEmailDialog = $('<div></div>', {
        id: 'x2-view-email-dialog'
    });

    x2ViewEmailDialog.dialog({
        title: 'View Action',
        autoOpen: false,
        resizable: true,
        width: '650px',
        show: 'fade'
    });
    jQuery('body')
    .bind('click', function(e) {
        if(jQuery('#x2-view-email-dialog').dialog('isOpen')
            && !jQuery(e.target).is('.ui-dialog, a')
            && !jQuery(e.target).closest('.ui-dialog').length
            ) {
            jQuery('#x2-view-email-dialog').dialog('close');
        }
    });

    x2ViewEmailDialog.data('inactive', true);
    if(x2ViewEmailDialog.data('inactive')) {
        x2ViewEmailDialog.append(frame);
        x2ViewEmailDialog.dialog('open').height('400px');
        x2ViewEmailDialog.data('inactive', false);
    } else {
        x2ViewEmailDialog.dialog('open');
    }
}
function uncompleteAction(id, publisher){
    resetFlag=false;
    $.ajax({
        url:yii.baseUrl+'/index.php/actions/uncomplete',
        type:'GET',
        data:{
            'id':id
        },
        success:function(data){
            if(data){
                data=JSON.parse(data);
                if(!publisher){
                    if(lastClass==''){
                        lastClass='history-'+id;
                        resetFlag=true;
                    }
                    $('#'+lastClass).find('.header').html('');
                    $('#'+lastClass).find('.description').css('text-decoration','');
                    $('#'+lastClass).find('.uncomplete-box').replaceWith('<div class="icon action-index complete-box" style="'+data[1]+'" data-action-id="'+$('#'+lastClass).find('.uncomplete-box').attr('data-action-id')+'"></div>');
                    if(resetFlag){
                        lastClass='';
                    }
                }else if(typeof $.fn.yiiListView.settings['history']!='undefined'){
                    $.fn.yiiListView.update('history');
                }
                $('#action-frame').attr('src', $('#action-frame').attr('src'));
            }
        }
    });
}

function completeAction(id, publisher){
    resetFlag=false;
    $("#dialog").dialog({
        autoOpen: true,
        buttons: {
            "Yes": function() {
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
                                if(lastClass==''){
                                    lastClass='history-'+id;
                                    resetFlag=true;
                                }
                                $('#'+lastClass).find('.header').html('<span class="complete">Complete!</span>');
                                $('#'+lastClass).find('.description').css('text-decoration','line-through');
                                $('#'+lastClass).find('.complete-box').replaceWith('<div class="icon action-index uncomplete-box" data-action-id="'+$('#'+lastClass).find('.complete-box').attr('data-action-id')+'"><div class="icon action-index checkmark-overlay"></div></div>');
                                if(resetFlag){
                                    lastClass='';
                                }
                            }else if(typeof $.fn.yiiListView.settings['history']!='undefined'){
                                $.fn.yiiListView.update('history');
                            }
                            $('#action-frame').attr('src', $('#action-frame').attr('src'));
                            $('#completion-notes').val('');
                        }
                    }
                });
            },
            "No":function(){
                $(this).dialog('close');
                $.ajax({
                    url:yii.baseUrl+'/index.php/actions/complete',
                    type:'GET',
                    data:{
                        'id':id
                    },
                    success:function(data){
                        if(data && data=='Success'){
                            if(!publisher){
                                if(lastClass==''){
                                    lastClass='history-'+id;
                                    resetFlag=true;
                                }
                                $('#'+lastClass).find('.header').html('<span class="complete">Complete!</span>');
                                $('#'+lastClass).find('.description').css('text-decoration','line-through');
                                $('#'+lastClass).find('.complete-box').replaceWith('<div class="icon action-index uncomplete-box" data-action-id="'+$('#'+lastClass).find('.complete-box').attr('data-action-id')+'"><div class="icon action-index checkmark-overlay"></div></div>');
                                if(resetFlag){
                                    lastClass='';
                                }
                            }else if(typeof $.fn.yiiListView.settings['history']!='undefined'){
                                $.fn.yiiListView.update('history');
                            }
                            $('#action-frame').attr('src', $('#action-frame').attr('src'));
                            $('#completion-notes').val('');
                        }
                    }
                });
            }
        },
        height:'auto',
        width:450,
        resizable:false
    });
}
$(document).on('ready',function(){
    var timer;
    $(document).on('mouseenter','.action-frame-link',function(){
        var id=$(this).attr('data-action-id');
        timer=setTimeout(function(){
            loadActionFrame(id)
            },500);
    });
    $(document).on('mouseleave','.action-frame-link',function(){
        clearTimeout(timer);
    });
});
$(document).on('click', '.complete-button', function(e){
        e.preventDefault();
        var publisher=($('#publisher-form').html()!=null);
        completeAction($(this).attr('data-action-id'), publisher);
});
$(document).on('click', '.uncomplete-button', function(e){
        e.preventDefault();
        var publisher=($('#publisher-form').html()!=null);
        uncompleteAction($(this).attr('data-action-id'), publisher);
});
$(document).on('click', '.complete-box', function(e){
        e.preventDefault();
        e.stopPropagation();
        var publisher=($('#publisher-form').html()!=null);
        completeAction($(this).attr('data-action-id'), publisher);
        //$(this).replaceWith('<div class="icon action-index uncomplete-box" data-action-id="'+$(this).attr('data-action-id')+'"><div class="icon action-index checkmark-overlay"></div></div>');
});
$(document).on('click', '.uncomplete-box', function(e){
        e.preventDefault();
        e.stopPropagation();
        var publisher=($('#publisher-form').html()!=null);
        uncompleteAction($(this).attr('data-action-id'), publisher);
        //$(this).replaceWith('<div class="icon action-index complete-box" data-action-id="'+$(this).attr('data-action-id')+'"></div>');
});