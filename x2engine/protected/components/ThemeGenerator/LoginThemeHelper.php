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




Yii::import ('application.components.ThemeGenerator.*');

/**
 * Class to assist the login theme cookies and Javascript.
 * The login page has both the ability to change the background color and 
 * ability to change overall theme of the page. The cookie saved will be the background color
 * and the theme name. 
 *
 * If the user is on default theme and changes the theme to dark theme, The app will change this
 * users theme to the dark theme. 
 * If the user is on a custom theme and logs in with the default theme, the theme of the app will
 * not change, to respect the option to have a default login page but a themed app. 
 *
 * The right most background color option will be the color of the theme background. 
 * 
 * @package application.components
 * @author Alex Rowe <alex@x2engine.com>
 */
class LoginThemeHelper extends LoginThemeHelperBase {

	/**
	 * Registers necessary JS and passes is the proper arguments
	 * Checks for POST
	 */
	public function registerJS() {
		Yii::app()->clientScript->registerCoreScript('cookie', CClientScript::POS_READY);
		Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl.'/js/LoginThemeHelper.js', CClientScript::POS_END);		

		/* This part will create a part of the theme selector specific to the current theme */
		if( $this->currentTheme == ThemeGenerator::$defaultLight ) {
			$theme = ThemeGenerator::loadDefault( $this->nextTheme );
		} else {
			$theme = ThemeGenerator::loadDefault( $this->currentTheme );
		}

		if (!isset($theme['background'])) {
			$theme['background'] = '#000';
		}

		$themeBG = array ( 
			$theme['background'],
			X2Color::brightness( $theme['background'], -0.1),
			$theme['background'],
		);

		$JSON = array(
			'themeColorCookie' => self::LOGIN_BACKGROUND_COOKIE,
			'cookieLength' => self::$cookieLength,
			'open' => isset($_POST[self::LOGIN_THEME_COOKIE]),
			'loginFormDark' => isset($_COOKIE[self::LOGIN_DARK_COOKIE]),
                        'loginFormDarkCookie' => self::LOGIN_DARK_COOKIE,
			'currentColor' => $this->currentColor,
			'currentThemeBG' => $themeBG
		);

		$JSON = CJSON::encode($JSON);

		Yii::app()->clientScript->registerScript('LoginThemeHelperJS', "
			new x2.LoginThemeHelper($JSON);
		", CClientScript::POS_END);
	}

}

?>
