<?php
/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
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
 **********************************************************************************/
 
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
