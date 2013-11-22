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

$saveWidgetHeight = $this->controller->createUrl('/site/saveWidgetHeight');
Yii::app()->clientScript->registerCss('updateFeedWidgetCss', "
#feed-post-publisher {
    padding: 5px;
}
#feed-post-editor {
	height: 20px;
    width: 98%;
    display: block;
    margin-left: auto;
    margin-right: auto;
    margin-bottom: 5px;
    float: none;
    resize:none;
    -moz-border-radius: 3px;
    -o-border-radius: 3px;
    -webkit-border-radius: 3px;
	border: 1px solid #ddd;
	background: #fff;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	overflow: hidden;
}
#feed-post-button {
    margin-top: 0;
    margin-left: 0;
    float: left;
}
#feed-post-publisher select {
    margin-right: 4px;
}
#feed-post-publisher .post-button-row-2 {
    line-height: 26px;
}
#feed-post-subtype {
    margin-bottom: 4px;
}
#feed-post-association-id {
    margin-bottom: 4px;
}
");
Yii::app()->clientScript->registerScript('updateFeedWidgetJS', "
$(function() {
    x2.feedWidget = {};

	$('#feed-container').resizable({
		handles: 's',
		minHeight: 75,
		alsoResize: '#feed-container-fix, #feed-box, #feed-box-container',
		start: function(event, ui) {
		},
		stop: function(event, ui) {
            $('#feed-container').css ('width', '');
            $('#feed-container-fix').css ('width', '');
            $('#feed-box').css ('width', '');
            $('#feed-box-container').css ('width', '');
			// done resizing, save height to user profile for next time user visits page
			$.post(
                '$saveWidgetHeight', 
                {
                    Widget: 'ChatBox', 
                    Height: {
                        chatboxHeight: parseInt($('#feed-box').css('height')), 
                    }
                }
            );
		}
	});
	$('#feed-box-container').resizable({
		handles: 's',
		minHeight: 30,
		alsoResize: '#feed-box, #feed-container, #feed-container-fix',
		stop: function(event, ui) {
            $('#feed-box-container').css ('width', '');
            $('#feed-box').css ('width', '');
            $('#feed-container').css ('width', '');
            $('#feed-container-fix').css ('width', '');
			// done resizing, save height to user profile for next time user visits page
			$.post(
                '$saveWidgetHeight', 
                {
                    Widget: 'ChatBox',
                    Height: {
                        chatboxHeight: parseInt($('#feed-box').css('height')), 
                    }
                }
            );
		}
	});

    $('#activityFeedDropDown').change (function() {
        var feedbox = $('#feed-box');
        var scroll = feedbox.prop ('scrollHeight');
    	if(parseInt (yii.profile.activityFeedOrder) === 1) {
            yii.profile.activityFeedOrder = 0;
        } else {
            scroll = 0;
            yii.profile.activityFeedOrder = 1;
        }
    	feedbox.children().each (function (i, child) {feedbox.prepend(child)});
    	feedbox.prop ('scrollTop',scroll);
    
        feedbox.css ('background-color', feedbox.data ('background-color'));
    
    	$.ajax ({url:yii.baseUrl+'/index.php/site/activityFeedOrder'});
    })

    // minimizes editor unless there's unposted text
    x2.feedWidget.minEditor = function () {
    	if ($('#feed-post-editor').val () !== '') return;
        feedEditorHasFocus = false;
        $('#feed-post-editor').animate ({
            height: '20px'
        });
        $('#feed-post-controls').slideUp ();
    };

    /*
    Sets up post ui element behavior.
    Note that click outside/tab are detected instead of blur since blur gets triggered on
    the window resize event.
    */
    x2.feedWidget.setUpPostEditor = function () {

        // min on click outside
        $('body').on ('click', function (evt) {
            if (!$(evt.target).closest ('#feed-post-publisher').length) {
                x2.feedWidget.minEditor ();
            }
        });

        // min on tab
    	$('#feed-post-editor').on ('keydown', function (evt) { 
            if (evt.which === 9) { // tab
                x2.feedWidget.minEditor ();
            }

        });

        // highlight post button if there's unposted text
    	$('#feed-post-editor').on ('keyup', function (evt) { 
            if ($(this).val () !== '') {
                $('#feed-post-button').addClass ('highlight');
            } else {
                $('#feed-post-button').removeClass ('highlight');
            }
        });

        // max editor on focus
    	$('#feed-post-editor').on ('focus', function () { 
            feedEditorHasFocus = true;
            $(this).animate ({
                height: '40px'
            });
            $('#feed-post-controls').slideDown ();
        });

        $('#feed-post-button').on ('click', function () {
            $.ajax({
                url:'".Yii::app()->request->getScriptUrl () . '/site/publishPost'."',
                type:'POST',
                data:{
                    text:$('#feed-post-editor').val(),
                    associationId:$('#feed-post-association-id').val(),
                    visibility:$('#feed-post-visibility').val(),
                    subtype:$('#feed-post-subtype').val()
                },
                success:function(){
                    $('#feed-post-editor').val ('');
                    $('#feed-post-editor').blur ();
                    x2.feedWidget.minEditor ();
                    $('#feed-post-button').removeClass ('highlight');
                }
            });
            return false;
        });
    };

    (function feedWidgetMain () {
        x2.feedWidget.setUpPostEditor ();
    }) ();
});
",CClientScript::POS_HEAD);

// find height of chat box, chat message, and use these to find height of widget
$widgetSettings = ProfileChild::getWidgetSettings();
$feedWidgetSettings = $widgetSettings->ChatBox;

$feedboxHeight = $feedWidgetSettings->chatboxHeight;

$feedboxContainerHeight = $feedboxHeight + 2;

$feedcontainerHeight = $feedboxHeight;
$feedcontainerFixHeight = $feedcontainerHeight + 10;

?>
<div id="feed-container-fix" style="height:<?php echo $feedcontainerFixHeight; ?>px;">								<!--fix so that resize tab appears at bottom of widget-->
	<div id="feed-container" style="height:<?php echo $feedcontainerHeight; ?>px;">									<!--this is the resizable for this widget-->
		<div id="feed-box-container" 
         style="height:<?php echo $feedcontainerHeight; ?>px; margin-bottom: 5px;">	
         <!--resizable for feedbox-->
			<div id="feed-box" 
             style="padding-top:5px; height:<?php echo $feedcontainerHeight; ?>px;"></div>

		</div>
	</div>
</div>
<form id='feed-post-publisher'>
    <textarea type='text' name='name' id='feed-post-editor' 
     placeholder='<?php echo Yii::t('app', 'Enter text here...'); ?>'></textarea>
    <div id='feed-post-controls' style='display:none;'>
    <?php
        $users = User::getUserIds();
        $userIds = array_keys ($users);
        $firstUser = $userIds[0];
        echo CHtml::dropDownList(
            'subtype',1,
            array_map(
                function ($item) { return Yii::t('app', $item); },
                json_decode(Dropdowns::model()->findByPk(113)->options,true)
            ),
            array ('id'=>'feed-post-subtype')
        );
        echo CHtml::dropDownList('associationId',$firstUser,$users, 
            array ('id'=>'feed-post-association-id'));
        ?>
        <div class='post-button-row-2'>
            <button type='submit' class='x2-button' id='feed-post-button' 
             data-inline='true'><?php echo Yii::t('app', 'Submit Post'); ?></button>
            <?php 
                echo CHtml::dropDownList(
                    'visibility',1,array(
                        1=>Yii::t('actions','Public'),0=>Yii::t('actions','Private')
                    ), array ('id'=>'feed-post-visibility'));
            ?>
        </div>
    </div>
</form>
