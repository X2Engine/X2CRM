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
			url: '".$this->controller->createUrl('/site/getNotes',array('url'=>Yii::app()->request->requestUri))."',
			success:
			function (data){
				//alert('old: '+$('#note-box').html()+'<br><br>new: '+data);
				//if ($('#note-box').html().length < data.length) {	//only update if theres new data
				//alert('old: '+$('#note-box').html());
					$('#note-box').html(data);
					//$('#note-box').attr('scrollTop',$('#feed-box').attr('scrollHeight')); //scroll to bottom of window
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
		}
	});
	$('#note-message-container').resizable({
		handles: 's',
		minHeight: 30,
		alsoResize: '#note-message, #note-container, #note-container-fix',
		stop: function(event, ui) {
			// done resizing, save height to user profile for next time user visits page
			$.post('$saveWidgetHeight', {Widget: 'NoteBox', Height: {noteboxHeight: parseInt($('#note-box').css('height')), notemessageHeight: parseInt($('#note-message').css('height'))}});
		}
	});
	$('#note-box-container').resizable({
		handles: 's',
		minHeight: 30,
		alsoResize: '#note-box, #note-container, #note-container-fix',
		stop: function(event, ui) {
			// done resizing, save height to user profile for next time user visits page
			$.post('$saveWidgetHeight', {Widget: 'NoteBox', Height: {noteboxHeight: parseInt($('#note-box').css('height')), notemessageHeight: parseInt($('#note-message').css('height'))}});
		}
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
