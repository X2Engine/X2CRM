<?php
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




// find height of note box, note message, and use these to find height of widget
$widgetSettings = Profile::getWidgetSettings();
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
		echo CHtml::encode($item->data).'<br /><br />';
	}
	?></div>
</div>

<?php
$saveWidgetHeight = $this->controller->createUrl('/site/saveWidgetHeight');
Yii::app()->clientScript->registerScript('updateNote', "
    $(function () { updateNotes (); });	//update on page load
	function updateNotes(){

		$.ajax({
			type: 'POST',
			url: '".$this->controller->createUrl('/site/getNotes',array('url'=>Yii::app()->request->requestUri))."',
			success: function (data){
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
	<?php echo CHtml::textArea('note-message', '', array('class'=> 'x2-textarea', 
						'style'=>"height: ". $notemessageHeight . "px;")); ?>
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
