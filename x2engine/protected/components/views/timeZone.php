<?php 
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * California 95067, USA. or at email address contact@x2engine.com.
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
 *****************************************************************************************/
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/clockWidget.js');
Yii::app()->clientScript->registerCss('clockWidgetCss',"

	#clock-widget-gear-menu .option {
		background-color: inherit;
		border-radius: 2px;
		padding:1px;
	}

	#clock-widget-gear-menu .option:hover[value='false'] {
		background-color: #DDDDDD;
	}
	
	#clock-widget-gear-menu .option[value='true'] {
		background-color: #407BC9;
		color: white;
	}

	#tzClockDigital {
		color: #555;
		font-size: 50pt;
		   font-family: 'HelveticaNeue-Light', 'Helvetica Neue Light', 'Helvetica Neue', Helvetica, Arial, 'Lucida Grande', sans-serif; 
		color: #2E2E2E;
	}

	#widget_TimeZone {
		background-color: #FFF;
	}

	#widget_TimeZone #clock-ampm { 
		font-size:30pt;
	}

	#clock-gear-img-container {
		float: left;
		opacity: 0.3;
	}
	
	#clock-gear-img-container:hover {
		opacity: 1.0;
	}

	");

$imgUrl = Yii::app()->theme->baseUrl."/images/widgets.png";
$analog = Yii::t('app', 'Analog');
$digital = Yii::t('app', 'Digital');
$digital24 = Yii::t('app', 'Digital 24h');
?>

<script type='text/javascript'>
// Options: analog, digital, digital24
var setting = '<?php echo $widgetSettings ?>';

$(function() { 
	if ($('#clock-gear-img-container').length > 0)
		return;

	// Add the gear icon menu
		$('<span id="clock-gear-img-container"> <img src="<?php echo $imgUrl ?>" /></span>').
		prependTo("#widget_TimeZone #widget-dropdown").
		wrap('<a href="#"></a>');


	// Add the dropdown list
	// Options for the dropdown value='true' means it is selected
	$('<ul class="closed" id="clock-widget-gear-menu"></ul>').appendTo("#widget_TimeZone #widget-dropdown").
	append('<div class="option" value="false" id="analog"><?php echo $analog ?></div>').
	append('<div class="option" value="false" id="digital"><?php echo $digital ?></div>').
	append('<div class="option" value="false" id="digital24"><?php echo $digital24 ?></div>');

	// Handle opening and closing of the menu
	$('#widget_TimeZone #clock-gear-img-container').on('click', function(){
		if( $('#clock-widget-gear-menu').hasClass('open') ){
			$('#clock-widget-gear-menu').addClass('closed');
			$('#clock-widget-gear-menu').removeClass('open');
		} else {
			$('#clock-widget-gear-menu').removeClass('closed');
			$('#clock-widget-gear-menu').addClass('open');
		}
		
	});

	// Set the currently selected option to true
	$('#clock-widget-gear-menu .option[id='+setting+']').attr('value','true');

	// click handling setup
	$('#clock-widget-gear-menu .option').bind('click',function(){
		$('#clock-widget-gear-menu .option').attr('value','false');
		$(this).attr('value','true');
		switchSetting($(this).attr('id'));
	});

	// Make the ajax call to save the setting in the profile
	function switchSetting(id){
		setting = id;
		$.ajax({
			url: "<?php echo Yii::app()->createUrl('/site/widgetSetting') ?>",
			data: { widget: 'TimeZone',
					setting: 'clockType',
					value: id
				  }
		}); 
	}
	



});




</script>