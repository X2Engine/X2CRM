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





/**
* This page renders the theme selector and the appropriate javascript
*/

Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/themes/x2engine/css/profile/themeSelector.css');

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/ThemeSelector.js', CClientScript::POS_END);

$user = Yii::app()->user->name;

$params = CJSON::encode(array(
	'defaults' => array(ThemeGenerator::$defaultLight, ThemeGenerator::$defaultDark),
	'active' => $selected,
	'user' => $user,
	'isAdmin' => Yii::app()->params->isAdmin ? 1 : 0,
	'translations' => array(
		'createNew' => Yii::t('profile', 'Create a new theme to edit'),
	)
));


Yii::app()->clientScript->registerScript('schemeJS', "


$(function () {
    new x2.ThemeSelector($params);
});

", CClientScript::POS_END);

echo "<input type='hidden' name='regenerate-theme' value='1'>";

echo "<div class='theme-picker' id='theme-picker'>";

$settings = ThemeGenerator::getSettingsList ();

$themes = $myThemes->data;
foreach($themes as $theme){
	$scheme = CJSON::decode ($theme->description);
	if (!is_array($scheme)){
		continue;
	}

	$fileName = $theme->fileName;
	$uploadedBy = $theme->uploadedBy;

	echo CHtml::openTag ('div', array(
		'class'=>"scheme-container",
		'name'=> $fileName,
		'data-id'=> $theme->id,
    ));
		echo CHtml::openTag ('div', array( 
			'class'=> 'scheme-container-inner', 
			'style' => "
				background: #$scheme[content];
				color: #$scheme[text];"
			)
		);

		echo "<div id='name' > $fileName </div> ";
		if ($fileName == ThemeGenerator::$defaultLight || 
            $fileName == ThemeGenerator::$defaultDark) {

			$uploadedByName = '';
		} else {
			$uploadedByName = $uploadedBy;
		}
		echo "<div id='uploadedBy' value='$uploadedBy' >$uploadedByName</div>";
		echo "<div class='clear'></div>";

			foreach($scheme as $key => $color){
				if (!in_array($key, $settings) || 
                    preg_match ('/_override$/', $key) && !$color) {

					continue;
                }

				$display = in_array($key, array ('text', 'content')) ||
                    preg_match ('/_override$/', $key) ? 'display: none;' : '';

				echo CHtml::tag ('div', array(
					'class'=>"scheme-color", 
					'name' => "$key",
					'color'=> $color,
					'style'=>"background: #$color; $display")
				, ' ');
			}

		// echo "<div class='hidden' id='backgroundTiling' value='$scheme[backgroundTiling]' ></div>";
		// echo "<div class='hidden' id='backgroundImg' value='$scheme[backgroundImg]' ></div>";
		echo "<div class='clear'></div>";
		echo '</div>';
	// echo CHtml::button('edit', array('id' =>'edit')); 
	echo "</div>";
}

?>

</div>
