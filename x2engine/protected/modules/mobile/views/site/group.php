<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
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

$this->pageTitle = Yii::app()->name . ' - Group Chat';

// add script to poll for new messageses
Yii::app()->clientScript->registerScript('updateChat', "
	setInterval ('updateChat()', 1000);	//update every 1 second
	$(document).ready(updateChat());	//update on page load
	function updateChat(){
		$.ajax({
			type: 'POST',
			url: '".$this->createUrl('site/getMessages')."',
			success:
			function (data){
				//alert('old: '+$('#chat-box').html()+'<br><br>new: '+data);
				//if ($('#chat-box').html().length < data.length) {	//only update if theres new data
				//alert('old: '+$('#chat-box').html());
					$('#chat-box').html(data);
					$('#chat-box').attr('scrollTop',$('#chat-box').attr('scrollHeight')); //scroll to bottom of window
				//}
			}
		});
	}
",CClientScript::POS_HEAD);
?>
<h2><?php echo Yii::t('app','Group Chat'); ?></h2>
<div id="chat" class="full-screen">
<div id="chat-box"></div>
<?php

echo CHtml::beginForm();
echo CHtml::textArea('chat-message', '');

echo CHtml::ajaxSubmitButton(
	'Send',
	array('site/newMessage'),
	array(
		'update'=>'#chat-box',
		'success'=>"function(response) {
				updateChat();
				$('#chat-message').val('');
		}",
	),
	array('class'=>'x2-button')
);
echo CHtml::endForm(); ?>
</div>