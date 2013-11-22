<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

$this->pageTitle = Yii::app()->name . ' - Group Chat';

// add script to poll for new messageses
Yii::app()->clientScript->registerScript('updateChat', "
	setInterval ('updateChat()', 1000);	//update every 1 second
	$(document).ready(updateChat());	//update on page load
	function updateChat(){
		$.ajax({
			type: 'POST',
			url: '".$this->createUrl('/mobile/site/getMessages')."',
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
	array('/site/newMessage'),
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
