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




/**************************************
* Print page layout with configuration
* To add the the configuration panel,
* Create a div with the class 
* 'config-panel-content'
* and it will be appended.
***************************************/

$cs = Yii::app()->clientScript;
$cs->registerCoreScript('jquery');
$cs->registerCoreScript('jquery.migrate');
$cs->registerCoreScript('jquery.ui');
$cs->registerPackages($cs->getDefaultPackages());

$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/printPage.css');
$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/ui-elements.css');
$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/fontAwesome/css/font-awesome.css');
$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/css-loaders/load8.css');

$cs->registerScript('ConfigMenuJS', "
	$('.config-panel-content').prependTo($('#config-panel-inner'));
", CClientScript::POS_END);

mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

?>

<html>
<head>
<meta charset="UTF-8">
<title><?php echo Yii::t('app','Print') ?></title>
</head>
<body>	
<div id='content'>
		
	<!-- Start the page loading icon -->
	<script type='text/javascript'> auxlib.pageLoading(); </script>

	<!-- Screen to reveal once ready -->
	<div id='screen'></div>
	
	<!-- Configuration Panel-->
	<div id='config-panel'>
		<h3 id='config-panel-header'><?php echo Yii::t('app', 'Configuration')?></h3>
		<div id='config-panel-inner'></div>
		<div class="row">
			<button class='label x2-button' id='print-button' type="button" onClick="window.print()"><i class='fa fa-print'></i> <?php echo Yii::t('app','Print') 
			?></button>
		</div>
		<div style="clear:both" ></div>
		</div>
	</div>

	<div class='container'>
		<?php echo $content; ?>
	</div>
	
	<!-- Remove the Loader, and remove the screen -->
	<script type='text/javascript'>
	    $(function(){
	    	if ($('#config-panel-inner').children().length == 0) {
	    		$('#config-panel-header').hide();
	    	}

	    	auxlib.pageLoadingStop();
	        $('#screen').css('opacity', 0);
	        setTimeout(function(){
		        $('#screen').remove();
	        }, 400);
	    });
	</script>

</div>
</body>
</html>