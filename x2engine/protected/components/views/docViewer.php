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

//Reset widget settings if DocViewer code is new to your build
if (!isset(ProfileChild::getWidgetSettings()->DocViewer)) {
	Yii::app()->params->profile->widgetSettings = null;
}

// find height of chat box, chat message, and use these to find height of widget
$widgetSettings = ProfileChild::getWidgetSettings();
$docSettings = $widgetSettings->DocViewer;

$docBoxHeight = $docSettings->docboxHeight;

$docBoxOuterHeight = $docBoxHeight + 2;
$docMessageHeight = 13;
$docContainerHeight = $docBoxHeight + $docMessageHeight + 45;
$docContainerOuterHeight = $docContainerHeight + 5;

// convert heights to pixels
$docBoxHeight .= 'px';
$docMessageHeight .= 'px';
$docBoxOuterHeight .= 'px';
$docContainerHeight .= 'px';
$docContainerOuterHeight .= 'px';

?>

<div id="docview-container-outer" style="height: <?php echo $docContainerOuterHeight; ?>">
	<div id="docview-container" style="height: <?php echo $docContainerHeight; ?>">
		<div id="docview-box-outer" style="height: <?php echo $docBoxOuterHeight; ?>; margin-bottom: 5px;">
			<div id="docview-box" style="height: <?php echo $docBoxHeight; ?>"></div>
		</div>
		<form id="docview-input">
			<label><?php echo Yii::t("app", "Enter Title"); ?></label>
			<input id="docview-title" type="text">
		</form>
	</div>
</div>

<?php
Yii::app()->clientScript->registerScript('docViewerLoad',
"$('#docview-title').autocomplete(
	{'minLength':'1',
	 'source':'" . Yii::app()->createUrl("/docs/docs/getItems") . "',
	 'select':function( event, ui ) {
		$(this).val(ui.item.value);
		$('#docview-box').load('" . Yii::app()->createUrl("/docs/docs/getItem") . "?id=' + ui.item.id);
		return false; 
	 }
	});"
);

Yii::app()->clientScript->registerScript('docViewerResize',"
$('#docview-container').resizable({
	handles: 's',
	minHeight: 75,
	alsoResize: '#docview-container-outer, #docview-box, #docview-box-outer',
	stop: function(event, ui) {
		// done resizing, save height to user profile for next time user visits page
		$.post('" . Yii::app()->createUrl("/site/saveWidgetHeight") . "', 
			{Widget: 'DocViewer', 
			 Height: { docboxHeight: parseInt($('#docview-box').css('height')) }
			});
	}
});
$('#docview-box-outer').resizable({
	handles: 's',
	minHeight: 30,
	alsoResize: '#docview-box, #docview-container, #docview-container-outer',
	stop: function(event, ui) {
		// done resizing, save height to user profile for next time user visits page
		$.post('" . Yii::app()->createUrl("/site/saveWidgetHeight") . "', 
			{Widget: 'DocViewer', 
			 Height: { docboxHeight: parseInt($('#docview-box').css('height')) }
			});
	}
});
$(document).on('submit','#docview-input',function(e){
    e.preventDefault();
});"
);
?>
