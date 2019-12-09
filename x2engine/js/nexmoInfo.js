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
 * @edition: ent
 */

/*
 * Nexmo Integration
 * Calls every 5 seconds to check
 * action - Marketing Controller(actionNexmoInfo) 
 * @return none
 */
$(function() {

    var setUpInboundInfo = function () { //set default Inbound Info Box
        var info = $('<div />');
        info.prop('id', 'phoneInfo');
        info.prop('title', 'Phone Call Information');
        return info;
    }
	
    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    function bufferWindow (){
        var pageTitle = $("title").text();
        $(window).blur(function() {

            setInterval(async function(){
                $("title").text(pageTitle);
                await sleep(1000);
                $("title").text("You have a notification from....");
            }, 1000);

        });

        // Change page title back on focus
        $(window).focus(function() {
          $("title").text(pageTitle);
        });
    }

    setInterval(function(){

    var url = yii.scriptUrl + '/marketing/nexmoCheck';
    $.ajax({
        url: url,
        type: 'GET',
        data: {'username': yii.profile.username},
        success: function(response){
            var message = '';
            var data = JSON.parse(response);
            if(data.check == 1){
		 bufferWindow();
                 var info = setUpInboundInfo();
                 info.dialog({
                     modal: true,
                     resizable: false,
                     height: 300,
                     width: 300,
                 });
                info.html(data.message);
            }
        }
    });

    }, 4000);

});
