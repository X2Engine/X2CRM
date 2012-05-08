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
	 'source':'" . Yii::app()->createUrl("/docs/getItems") . "',
	 'select':function( event, ui ) {
		$(this).val(ui.item.value);
		$('#docview-box').load('" . Yii::app()->createUrl("/docs/getItem") . "?id=' + ui.item.id);
		return false; 
	 },
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
	},
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
	},
});"
);
?>
