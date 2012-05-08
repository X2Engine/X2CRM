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
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
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

// find height of note box, note message, and use these to find height of widget
$widgetSettings = ProfileChild::getWidgetSettings();
$noteSettings = $widgetSettings->NoteBox;

$noteboxHeight = $noteSettings->noteboxHeight;
$notemessageHeight = $noteSettings->notemessageHeight;

$noteboxContainerHeight = $noteboxHeight + 2;
$notemessageContainerHeight = $notemessageHeight + 6;

$noteContainerHeight = $noteboxHeight + $notemessageHeight + 45;
$noteContainerFixHeight = $noteContainerHeight + 5;

?>
<div id="note-container-fix" style="height: <?php echo $noteContainerFixHeight; ?>px">
<div id="note-container" style="height: <?php echo $noteContainerHeight; ?>px">

<div id="note-box-container" style="height: <?php echo $noteboxHeight; ?>px; margin-bottom: 5px">
	<div id="note-box" style="height: <?php echo $noteboxHeight; ?>px"><?php if(isset($data) && count($data)>0){
	foreach($data as $item)
		echo $item->data.'<br /><br />';
	}
	?></div>
</div>

<?php
$saveWidgetHeight = $this->controller->createUrl('/site/saveWidgetHeight');
Yii::app()->clientScript->registerScript('updateNote', "
	$(document).ready(updateNotes());	//update on page load
	function updateNotes(){
		$.ajax({
			type: 'POST',
			url: '".$this->controller->createUrl('/site/getNotes?url='.Yii::app()->request->requestUri)."',
			success:
			function (data){
				//alert('old: '+$('#note-box').html()+'<br><br>new: '+data);
				//if ($('#note-box').html().length < data.length) {	//only update if theres new data
				//alert('old: '+$('#note-box').html());
					$('#note-box').html(data);
					//$('#note-box').attr('scrollTop',$('#chat-box').attr('scrollHeight')); //scroll to bottom of window
				//}
			}
		});
	}
	
$(function() {
	$('#note-container').resizable({
		handles: 's',
		minHeight: 75,
		alsoResize: '#note-container-fix, #note-box, #note-box-container',
		start: function(event, ui) {
			$('#note-container').resizable('option', 'minHeight', parseInt($('#note-message-container').css('height'), 10) + 67);
		},
		stop: function(event, ui) {
			// done resizing, save height to user profile for next time user visits page
			$.post('$saveWidgetHeight', {Widget: 'NoteBox', Height: {noteboxHeight: parseInt($('#note-box').css('height')), notemessageHeight: parseInt($('#note-message').css('height'))}});
		},
	});
	$('#note-message-container').resizable({
		handles: 's',
		minHeight: 30,
		alsoResize: '#note-message, #note-container, #note-container-fix',
		stop: function(event, ui) {
			// done resizing, save height to user profile for next time user visits page
			$.post('$saveWidgetHeight', {Widget: 'NoteBox', Height: {noteboxHeight: parseInt($('#note-box').css('height')), notemessageHeight: parseInt($('#note-message').css('height'))}});
		},
	});
	$('#note-box-container').resizable({
		handles: 's',
		minHeight: 30,
		alsoResize: '#note-box, #note-container, #note-container-fix',
		stop: function(event, ui) {
			// done resizing, save height to user profile for next time user visits page
			$.post('$saveWidgetHeight', {Widget: 'NoteBox', Height: {noteboxHeight: parseInt($('#note-box').css('height')), notemessageHeight: parseInt($('#note-message').css('height'))}});
		},
	});
});	

",CClientScript::POS_HEAD);
?>

<?php echo CHtml::beginForm(); ?>

<div id="note-message-container" style="height: <?php echo $notemessageContainerHeight; ?>px">
	<?php echo CHtml::textArea('note-message', '', array('style'=>"height: ". $notemessageHeight . "px;")); ?>
</div>

<?php
echo CHtml::ajaxSubmitButton(
	Yii::t('app','Add Note'),
	array('/site/addPersonalNote'),
	array(
		'update'=>'#note-box',
		'success'=>"function(response) {
			updateNotes();
			$('#note-message').val('');
		}",
	),
	array('class'=>'x2-button')
);

echo CHtml::endForm();

echo "</div>";
echo "</div>";

?>