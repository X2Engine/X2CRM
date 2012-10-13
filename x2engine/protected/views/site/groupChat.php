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
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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

$this->pageTitle=Yii::app()->name . ' - Chat';

$admin = Admin::model()->findByPk(1);
$updateInterval = $admin->chatPollTime;

$ajaxUrl = $this->createUrl('notifications/getMessages');
Yii::app()->clientScript->registerScript('groupChat', "
	var apples = 1;
	chatUpdateInterval = " . $updateInterval . ";
	chatAjaxUrl = '".$ajaxUrl . "';
	$(document).ready(function() {
		updateChat();							//update on page load
		$('#widget_ChatBox').hide();		// hide chat widget
		$('#widget_ChatBox').attr('id','hidden-chatbox');	// change the widget's id so it doesn't compete with the big chat box
	});",CClientScript::POS_HEAD);
?>

<h2><?php echo Yii::t('app','Chat'); ?></h2>
<?php echo Yii::t('profile','A larger Chat Box');?>
<div id="chat" class="full-screen">
<div id="chat-box"></div>
<?php

echo CHtml::beginForm();
//echo CHtml::textArea('chat-message',Yii::t('app','Enter text here...'),array('onfocus'=>'toggleText(this);','onblur'=>'toggleText(this);','style'=>'color:#aaa;'));
echo CHtml::textArea('chat-message',''); //,array('style'=>'color:#aaa;'));

echo CHtml::ajaxSubmitButton(
	Yii::t('app','Send'),
	array('site/newMessage'),
	array(
		'update'=>'#chat-box',
		'success'=>"function(response) {
				updateChat();
				$('#chat-message').val(''); //".Yii::t('app','Enter text here...')."');
				// $('#chat-message').css('color','#aaa');
				// toggleText($('#chat-message').get());
		}",
	),
	array('class'=>'x2-button')
);
echo CHtml::endForm(); ?>
</div>