<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

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
$chatContainerFixHeight = $chatContainerHeight + 5;

?>

<div id="chat-container-fix" style="height:<?php echo $chatContainerFixHeight; ?>px;">								<!--fix so that resize tab appears at bottom of widget-->
	<div id="chat-container" style="height:<?php echo $chatContainerHeight; ?>px;">									<!--this is the resizable for this widget-->
		<div id="chat-box-container" style="height:<?php echo $chatboxContainerHeight; ?>px; margin-bottom: 5px;">	<!--resizable for chatbox-->
			<div id="chat-box" style="height:<?php echo $chatboxHeight; ?>px;"></div>
		</div>
		<?php echo CHtml::beginForm(); ?>
		<div class="textarea-container">
			<div id="chat-message-container" style="height:<?php echo $chatmessageContainerHeight; ?>px;">	<!--resizable for chat messages-->
				<?php echo CHtml::textArea('chat-message','', array('style'=>'height:'.$chatmessageHeight.'px;')); ?>
			</div>
			<?php
			echo CHtml::submitButton(
				Yii::t('app','Send'),
				/* array('/site/newMessage'),
				array(
					'update'=>'#chat-box',
					'success'=>"function(response) {
						//updateChat();
						$('#chat-message').val(''); //".Yii::t('app','Enter text here...')."');
						// $('#chat-message').css('color','#aaa');
						// toggleText($('#chat-message').get());
					}",
				), */
				array('id'=>'chat-submit','class'=>'x2-button')
			);
			?>
			<?php echo CHtml::endForm(); ?>
		</div>
	</div>
</div>