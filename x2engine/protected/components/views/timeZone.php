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





$imgUrl = Yii::app()->theme->baseUrl."/images/widgets.png";
$analog = Yii::t('app', 'Analog');
$digital = Yii::t('app', 'Digital');
$digital24 = Yii::t('app', 'Digital 24h');
?>

<script type='text/javascript'>
;(function () {
x2 = typeof x2 === 'undefined' ? {} : x2; 
x2.clockWidget = {};
x2.clockWidget.setting = '<?php echo $widgetSettings ?>';

$(function() { 
// Options: analog, digital, digital24


	function callback(li){
		li = $(li.target);
		li.siblings().removeClass('option-active');
		li.addClass('option-active');
		switchSetting(li.attr('value'));
	}


	// Add the gear icon menu
	// var imgUrl= '<?php echo $imgUrl ?>';

	// Create the Config menu
	if( $("#widget_TimeZone").find('.gear-img-container').length == 0 ) {
		var dropdown = $("#widget_TimeZone").addConfigMenu({
            analog: '<?php echo CHtml::encode ($analog); ?>',
            digital: '<?php echo CHtml::encode ($digital); ?>',
            digital24: '<?php echo CHtml::encode ($digital24); ?>'
        }, callback);

		// // Set the currently blue option to true
		dropdown.find("div[value='"+x2.clockWidget.setting+"']").addClass('option-active');
	}

	// Make the ajax call to save the setting in the profile
	function switchSetting(id){
		x2.clockWidget.setting = id;
		$.ajax({
			url: "<?php echo Yii::app()->createUrl('/site/widgetSetting') ?>",
			data: { 
                widget: 'TimeZone',
                setting: 'clockType',
                value: id
            }
		}); 
	}
	

});

}) ();
</script>
