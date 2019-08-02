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




//Reset widget settings if DocViewer code is new to your build
if (!isset(Profile::getWidgetSettings()->DocViewer)) {
    Yii::app()->params->profile->widgetSettings = null;
}

// find height of chat box, chat message, and use these to find height of widget
$widgetSettings = Profile::getWidgetSettings();
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
        <form id="docview-input"><!-- GET request, doesn't need csrf token -->
            <label><?php echo Yii::t("app", "Enter Title"); ?></label>
            <input class="x2-textfield" id="docview-title" type="text">
        </form>
    </div>
</div>

<?php
Yii::app()->clientScript->registerScript('docViewerLoad',
"$('#docview-title').autocomplete({
    'minLength':'1',
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
