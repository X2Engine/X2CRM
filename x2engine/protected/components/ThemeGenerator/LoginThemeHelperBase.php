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
abstract class LoginThemeHelperBase {

	/**
	 * @var constant name of cookie that saves the current profile theme
	 */
	const PROFILE_COOKIE = 'profileTheme';
	
	/**
	 * @var constant Name of the cookie that is the current theme of the login screen. This gets 
     *  set by clicking the dark/light theme toggle
	 */
	const LOGIN_THEME_COOKIE = 'themeName';

	/**
	 * @var constant Name of the cookie that defines the login background color
	 */
	const LOGIN_BACKGROUND_COOKIE = 'loginBackground';

	/**
	 * @var constant Name of the cookie that defines the login background and form color to dark.
	 */
	const LOGIN_DARK_COOKIE = 'loginDarkBackground';

	/**
	 * @var int length of the cookies set
	 */
	public static $cookieLength = 1209600; // Two weeks

	/**
	 * @var string name of the next theme. This will be the dark theme if the current theme is default
	 */
	public $nextTheme;

	/**
	 * @var string name of the currently applied theme.
	 */
	public $currentTheme;

	/**
	 * @var string color name of the background color currently set.
	 */
	public $currentBackground;

    /**
     * Whether or not user has a dark theme selected
     */
    public $usingDarkTheme;

	/**
	 * The constructor does most of the work. Handles Posting expected on the login screen. 
	 */
	public function __construct() {
        Yii::app()->clientScript->registerPackage('X2CSS');

		$loginTheme = ThemeGenerator::$defaultLight;
		$darkTheme = ThemeGenerator::$defaultDark;

		// Set the dark theme to be a different than default
		if (isset ($_COOKIE[self::PROFILE_COOKIE]) && 
			$_COOKIE[self::PROFILE_COOKIE] != ThemeGenerator::$defaultLight) {
			$darkTheme = $_COOKIE[self::PROFILE_COOKIE];
		}

		// Check if the login theme is set
		if (isset ($_POST[self::LOGIN_THEME_COOKIE])) {
			
			//Set a cookie if a post was mode
			AuxLib::setCookie(
                self::LOGIN_THEME_COOKIE, $_POST[self::LOGIN_THEME_COOKIE], self::$cookieLength);
			$loginTheme = $_POST[self::LOGIN_THEME_COOKIE];

		} else if (isset ($_COOKIE[self::LOGIN_THEME_COOKIE])) {
			$loginTheme = $_COOKIE[self::LOGIN_THEME_COOKIE];	
		} 
		
		// get the button post value; The opposite of what theme is set. 
		$nextTheme = ( $loginTheme == ThemeGenerator::$defaultLight ) ? $darkTheme : ThemeGenerator::$defaultLight;
        $this->usingDarkTheme = $loginTheme !== ThemeGenerator::$defaultLight;

		$this->currentTheme = $loginTheme;
		$this->nextTheme = $nextTheme;

		$this->currentColor = null;	
		if ( isset($_COOKIE[self::LOGIN_BACKGROUND_COOKIE]) )
			$this->currentColor = $_COOKIE[self::LOGIN_BACKGROUND_COOKIE];

		$this->registerJS();
	}

    private static $_singleton;
    public static function singleton () {
        if (!self::$_singleton) 
            throw new CException ('LoginThemeHelper not yet initialized');
        return self::$_singleton;
    }

    public static function init () {
        if (self::$_singleton) 
            throw new CException ('LoginThemeHelper can only be initialized once');
        $calledClass = get_called_class ();
        self::$_singleton = new $calledClass;
    }

	public static function render(){
		$th = self::$_singleton;
		ThemeGenerator::renderTheme($th->currentTheme);
		echo $th->formHtml();
	}

	public function formHtml(){
		$html  = '';
		$html .= CHtml::beginForm('','post', array(
			'id'=>'dark-theme-form',
		));
		$html .= CHtml::hiddenField('themeName', $this->nextTheme);
		$html .= CHtml::endForm();
		return $html;

	}

	/**
	 * Helper action upon login
	 * expects a post of the theme and sets it to be the current theme ONLY if the current theme is not already set.
	 */
	public static function login() {
		if (!isset ($_POST[self::LOGIN_THEME_COOKIE])) {
			return;
		}

		$themeName = $_POST[self::LOGIN_THEME_COOKIE];
	    $profile = X2Model::model('Profile')->findByPk(Yii::app()->user->id);

	    if ($profile->theme['themeName'] == '' || $profile->theme['themeName'] == ThemeGenerator::$defaultLight) {
	        $profile->theme = ThemeGenerator::loadDefault($themeName, false);
	        $profile->save();
	    }

	}

	/**
	 * Saves a profile Theme to the cookies
	 * @param string $themeName name of the theme to be set. 
	 */
	public static function saveProfileTheme($themeName){
		//Set a cookie for the profile theme set
		if( $themeName != ThemeGenerator::$defaultLight) {
		    AuxLib::setCookie(self::PROFILE_COOKIE, $themeName, self::$cookieLength);
		}

		// Set a cookie for the login screen 
		if( isset($_COOKIE[self::LOGIN_THEME_COOKIE]) ) {
		    AuxLib::setCookie(self::LOGIN_THEME_COOKIE, $themeName, self::$cookieLength);
		}

	}

	/**
	 * Registers necessary JS and passes is the proper arguments
	 * Checks for POST
	 */
	public function registerJS() {
	}

}

?>
