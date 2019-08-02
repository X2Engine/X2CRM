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






if (typeof x2 == "undefined")
    x2 = {};
if (typeof x2.actionTimersForm == "undefined")
    x2.actionTimersForm = {};

x2.actionTimersForm.elements = {};

x2.actionTimersForm.getElement = function(selector) {
    if(typeof this.container == 'undefined') {
        this.container = $("#action-timers-form");
    }
    if(typeof this.elements[selector] == 'undefined') {
        this.elements[selector] = this.container.find(selector);
    }
    return this.elements[selector];
}

x2.actionTimersForm.recalculateLine = function (line) {
    var start = Math.round(line.find('.time-at-timestamp').datetimepicker('getDate').getTime()/1000);
    var end = Math.round(line.find('.time-at-endtime').datetimepicker('getDate').getTime()/1000);
    line.find('input.timer-total').val(end-start).trigger('change');
}

x2.actionTimersForm.recalculateTotal = function() {
    var total = 0;
    this.getElement('tr.timer-record').each(function(){
        total += parseInt($(this).find('input.timer-total').val());
    });
    this.getElement('input.timer-total.all-timers-total').val(total).trigger('change');
}

jQuery(document).ready(function () {
    $("table.action-timers-form").on("click","a.delete-timer",function(){
        $(this).parents('tr').each(function(index){
            $(this).find('input.timer-total').val("0");
        }).remove();
        x2.actionTimersForm.recalculateTotal();
    });
    
    $("table.action-timers-form").on("change",".time-input",function(){
        x2.actionTimersForm.recalculateLine($(this).parents('tr.timer-record').first());
    });

    $("table.action-timers-form").on("change","input.timer-total",function() {
        var that = $(this);
        var t_s = that.val();
        var seconds = t_s%60;
        var minutes = Math.floor(t_s/60)%60;
        var hours = Math.floor(t_s/3600);
        that.siblings("span.timer-total").each(function(){
            if(t_s < 0) {
                $(this).addClass("negative").text("< 0");
            } else {
                var pad = function(i) {
                    return (i < 10) ? "0" + i : i;
                }
                $(this).removeClass("negative").text(pad(hours)+":"+pad(minutes)+":"+pad(seconds));
            }
        });
        if(!$(this).hasClass('all-timers-total')) {
            x2.actionTimersForm.recalculateTotal();
        }
    });
});

