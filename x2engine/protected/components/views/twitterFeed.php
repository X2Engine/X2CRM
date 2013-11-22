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
 
if($twitter != null) {
?>

<?php //Yii::app()->clientScript->registerScriptFile('http://widgets.twimg.com/j/2/widget.js',CClientScript::POS_HEAD); ?>
<?php Yii::app()->clientScript->registerCssFile(Yii::app()->theme->getBaseUrl().'/css/twitter.css','screen'); ?>

<ul id="twitter_update_list">
	<li class="load-ind"><img src="http://www.barneyb.com/s/loading_indicator.gif" /></li>
</ul>

<script type="text/javascript">
$(document).ready(function() {
	$('body').append(
		'<sc'+'ript type="text/javascript" src="http://twitter.com/javascripts/blogger.js"></sc'+'ript>' +
		'<sc'+'ript type="text/javascript" src="http://twitter.com/statuses/user_timeline/<?php echo $twitter; ?>.json?callback=twitterCallback2&amp;count=5"></sc'+'ript>'
	);
});
/* new TWTR.Widget({
	version: 2,
	type: 'profile',
	rpp: 5,
	interval: 30000,
	width: 'auto',
	height: 150,
	theme: {
		shell: {
			background: '#ffffff',
			color: '#000000'
		},
		tweets: {
			background: '#ffffff',
			color: '#000000',
			links: '#06c'
		}
	},
	features: {
		scrollbar: true,
		loop: false,
		live: false,
		hashtags: true,
		timestamp: false,
		avatars: false,
		behavior: 'all'
	}
}).render().setUser('<?php echo $twitter; ?>').start(); */
</script>
<?php
} else {
	Yii::app()->getClientScript()->registerCss('hideTwitter',"#widget_TwitterFeed{display:none;}",'all');
}
?>
