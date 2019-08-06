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




/*
Used primarily to manage the right and left widgets. It's also used to manage the record view 
widgets section in the hidden widgets menu.
*/

$(function() {
    $('#content-widgets').droppable({ // allow widgets to be dropped into content widgets list
        accept: '.x2-widget-menu-item',
        activeClass: 'x2-state-active',
        hoverClass: 'x2-state-hover',
        drop: function(event, ui) { // add a tag to this model
            // from the server get the widget and add it to the center widgets list
            $.post(yii.scriptUrl+'/site/showWidget', {
                    name: ui.draggable.attr('id'),
                    block: 'center',
                    modelType: $('body').data('modelType'),
                    modelId: $('body').data('modelId'),
                    moduleName: x2.currModule
                }, 
                function(response) {
                    $('#' + ui.draggable.attr('id')).parent().remove(); // remove widget from menu
                    $('#content-widgets').append(response); // add widget to center widgets list
            });
        }
    });
    
    // handle when user rearranges widgets
    $('#content-widgets').sortable({
        update: function(event, ui) {
            $.post(yii.scriptUrl+'/site/reorderWidgets',
                $(this).sortable('serialize') + '&block=center');
        },
        handle: $(this).find ('.x2widget-header')
    });

    // handle when user rearranges left widgets
    $('#sidebar-left-widget-box').sortable({
        update: function(event, ui) {
            $.post(yii.scriptUrl+'/site/reorderWidgets',
                $(this).sortable('serialize') + '&block=left');
        },
        handle: $(this).find ('.sidebar-left > div:first-child .portlet-title')
    });
    
    $('.x2-widget-menu-item').draggable({
        revert: 'invalid', 
        helper:'clone', 
        revertDuration:200, 
        appendTo:'#x2-hidden-widgets-menu',
        iframeFix:true
    });
    
    $('.x2-hidden-widgets-menu-item.widget-right').click(function() {
        return handleWidgetRightMenuItemClick($(this));
    });
});

/**
 * Add/remove dividers between sections in hidden widgets menu
 */
function hideShowHiddenWidgetSubmenuDividers () {
    var hiddenCenter = 
        $('#x2-hidden-recordView-widgets-menu').find ('.x2-hidden-widgets-menu-item').length;
    var hiddenRight = 
        $('#x2-hidden-right-widgets-menu').find ('.x2-hidden-widgets-menu-item').length;
    var hiddenProfile =
        $('#x2-hidden-profile-widgets-menu').find ('.x2-hidden-widgets-menu-item').length;

    if (hiddenCenter && (hiddenRight || hiddenProfile)) {
        $('#x2-hidden-right-widgets-menu').find ('.x2-hidden-widgets-menu-divider').show ();
    } else {
        $('#x2-hidden-right-widgets-menu').find ('.x2-hidden-widgets-menu-divider').hide ();
    }
    if (hiddenRight && hiddenProfile) {
        $('#x2-hidden-profile-widgets-menu').find ('.x2-hidden-widgets-menu-divider').show ();
    } else {
        $('#x2-hidden-profile-widgets-menu').find ('.x2-hidden-widgets-menu-divider').hide ();
    }
}

function handleWidgetMenuItemClick(menuItem) {
    $.post(yii.scriptUrl+'/site/showWidget', {
            name: menuItem.attr('id'),
            block: 'center',
            modelType: $('body').data('modelType'),
            modelId: $('body').data('modelId')
        }, 
        function(response) {
            $('#' + menuItem.attr('id')).parent().remove(); // remove widget from menu

            hideShowHiddenWidgetSubmenuDividers ();

            $('#content-widgets').prepend (response); // add widget to center widgets list
            $.post(yii.scriptUrl+'/site/reorderWidgets', $('#content-widgets').
                sortable('serialize') + '&block=center');
    });
    
    return true;
};

function removeChartWidget () {
    if (x2.actionHistoryChart) {
        x2.actionHistoryChart.chart.tearDown ();
    }
    /*if (x2.campaignChart) {
        delete x2.campaignChart;
    }*/
};

// adds a widget to the right side widget bar
function handleWidgetRightMenuItemClick(menuItem) {
    $.post(yii.scriptUrl+'/site/showWidget', {
            name: menuItem.attr('id'),
            block: 'right'
        }, 
        function(response) {
            window.location.reload(true);
      //      $('#' + menuItem.attr('id')).parent().remove(); // remove widget from menu
      //      $('#content-widgets').append(response); // add widget to center widgets list
      //      $.post(yii.scriptUrl+'/site/reorderWidgets', $('#content-widgets').sortable('serialize') + '&block=center');
    });
    
    return true;
};


$.fn.hideWidgetRight = function() {
    $(this).each(function() {
        var widget = $(this);

        // slice of the "x2widget_" from the id to get widget name
        var widgetName = $(this).attr('id').slice(7); 
        $.post(
            yii.scriptUrl+'/site/hideWidget', 
            {
                name: widgetName,
                position: 'right'
            }, 
            function(response) {

                widget.slideUp(function() {
                    widget.remove();
                    $('#x2-hidden-widgets-menu').replaceWith(response);
                //    $('.x2-widget-menu-item').draggable({revert: 'invalid', helper:'clone', revertDuration:200, appendTo:'#x2-hidden-widgets-menu',iframeFix:true});
                    $('.x2-hidden-widgets-menu-item.widget-center').click(function() {
                        return handleWidgetMenuItemClick($(this));
                    });
                    $('.x2-hidden-widgets-menu-item.widget-right').click(function() {
                        return handleWidgetRightMenuItemClick($(this));
                    });
                    if($('#sidebar-right .portlet').length == 0 && window.fullscreen == false) {
                        $('#fullscreen-button').trigger('click');
                    }
                });
            });
    });
};


$.fn.minimizeWidget = function() {
    $(this).each(function() {
        var widget = $(this);

        // slice of the "x2widget_" from the id to get widget name
        var widgetName = $(this).attr('id').slice(9); 
        var min = widget.find('.x2widget-container').is(':hidden') == false; // are we minimizing?

        $.post(yii.scriptUrl+'/site/minimizeWidget', {name: widgetName, minimize: min}, 
            function(response) {

            if(min == true) {
                widget.find('.x2widget-container').slideUp();
                widget.find('.x2widget-minimize').
                    html('<img src="'+yii.themeBaseUrl+'/images/icons/Expand_Widget.png" />');
            } else {
                widget.find('.x2widget-container').slideDown({
                    done: function () {
                        if (widgetName === 'RecordViewChart') {

                            // event detected by centerWidget.php
                            $(document).trigger ('chartWidgetMaximized'); 
                        }
                    }
                });        
                widget.find('.x2widget-minimize').
                    html('<img src="'+yii.themeBaseUrl+'/images/icons/Collapse_Widget.png" />');
            }
        });
    });
};

/**
* Adds a config method to a right widget
* @param jqueryObject target A jquery object that will be added to 
*/
$.fn.addConfigMenu = function(options, callback) {
    var dropdown = $(this).find('#widget-dropdown');

    var target = $('<span class="gear-img-container fa fa-cog fa-lg"></span>')
    .height(18).width(18);
    target.appendTo(dropdown);
    var ul = $('<ul class="closed widget-gear-menu"></ul>').appendTo(dropdown);

    for (var key in options){
        $('<div class="widget-gear-option" value="'+key+'">'+options[key]+'</div>').
        appendTo(ul).
        click( function(element) {
            return callback(element);
        });
    }


    // Handle opening and closing of the menu
    target.on('click', function(){
        if( ul.hasClass('open') ){
            ul.addClass('closed');
            ul.removeClass('open');
        } else {
            ul.removeClass('closed');
            ul.addClass('open');
        }
        
    });

    return dropdown;

};
