<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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

$this->pageTitle = Yii::app()->settings->appName . ' - Group Chat';

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
