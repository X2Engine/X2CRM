<?php
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

$saveWidgetHeight = $this->controller->createUrl('/site/saveWidgetHeight');
Yii::app()->clientScript->registerScript('updateChatJs', "
$(function() {
	$('#chat-container').resizable({
		handles: 's',
		minHeight: 75,
		alsoResize: '#chat-container-fix, #chat-box, #chat-box-container',
		start: function(event, ui) {
			// when resizing starts, calculate min size of widget based on height of two resizables inside the widget
			$('#chat-container').resizable('option', 'minHeight', parseInt($('#chat-message-container').css('height'), 10) + 67);
		},
		stop: function(event, ui) {
			// done resizing, save height to user profile for next time user visits page
			$.post('$saveWidgetHeight', {Widget: 'ChatBox', Height: {chatboxHeight: parseInt($('#chat-box').css('height')), chatmessageHeight: parseInt($('#chat-message').css('height'))}});
		}
	});
	$('#chat-message-container').resizable({
		handles: 's',
		minHeight: 30,
		alsoResize: '#chat-message, #chat-container, #chat-container-fix',
		stop: function(event, ui) {
			// done resizing, save height to user profile for next time user visits page
			$.post('$saveWidgetHeight', {Widget: 'ChatBox', Height: {chatboxHeight: parseInt($('#chat-box').css('height')), chatmessageHeight: parseInt($('#chat-message').css('height'))}});
		},
	});
	$('#chat-box-container').resizable({
		handles: 's',
		minHeight: 30,
		alsoResize: '#chat-box, #chat-container, #chat-container-fix',
		stop: function(event, ui) {
			// done resizing, save height to user profile for next time user visits page
			$.post('$saveWidgetHeight', {Widget: 'ChatBox', Height: {chatboxHeight: parseInt($('#chat-box').css('height')), chatmessageHeight: parseInt($('#chat-message').css('height'))}});
		},
	});
});
",CClientScript::POS_HEAD);

// find height of chat box, chat message, and use these to find height of widget
$widgetSettings = ProfileChild::getWidgetSettings();
$chatSettings = $widgetSettings->ChatBox;

$chatboxHeight = $chatSettings->chatboxHeight;
$chatmessageHeight = $chatSettings->chatmessageHeight;

$chatboxContainerHeight = $chatboxHeight + 2;
$chatmessageContainerHeight = $chatmessageHeight + 6;

$chatContainerHeight = $chatboxHeight + $chatmessageHeight + 45;
$chatContainerFixHeight = $chatContainerHeight + 10;

?>

<script>
            $("#activityFeedDropDown").change(function() {
                $.ajax({
                    url:yii.baseUrl+"/index.php/site/activityFeedOrder",
                    success:function(){
                        $('#chat-box').empty();
                        var profile = JSON.parse(yii.profile);
                        if(profile['activityFeedOrder']==1){
                            profile['activityFeedOrder']=0;
                        }else{
                            profile['activityFeedOrder']=1;
                        }
                        yii.profile=JSON.stringify(profile);
                        lastEventId=0;
                        lastTimestamp=0;
                    }
                });
            })
</script>
<div id="chat-container-fix" style="height:<?php echo $chatContainerFixHeight; ?>px;">								<!--fix so that resize tab appears at bottom of widget-->
	<div id="chat-container" style="height:<?php echo $chatContainerHeight; ?>px;">									<!--this is the resizable for this widget-->
		<div id="chat-box-container" style="height:<?php echo $chatContainerHeight; ?>px; margin-bottom: 5px;">	<!--resizable for chatbox-->
			<div id="chat-box" style="padding-top:5px; height:<?php echo $chatContainerHeight; ?>px;"></div>
		</div>
	</div>
</div>
