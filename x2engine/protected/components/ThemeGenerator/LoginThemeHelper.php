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

class LoginThemeHelper {

	/**
	 * @var constant name of cookie that saves the current profile theme
	 */
	const PROFILE_COOKIE = 'profileTheme';
	
	/**
	 * @var constant Name of the cookie tha is the current theme of the login screen
	 */
	const LOGIN_THEME_COOKIE = 'themeName';

	/**
	 * @var constant Name of the cookie that defines the login background color
	 */
	const LOGIN_BACKGROUND_COOKIE= 'loginBackground';

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
	 * The constructor does most of the work. Handles Posting expected on the login screen. 
	 */
	public function __construct() {
		$loginTheme = ThemeGenerator::$defaultLight;
		$darkTheme = ThemeGenerator::$defaultDark;


		// Set the dark theme to be a different than default
		if ( isset( $_COOKIE[self::PROFILE_COOKIE]) && 
			$_COOKIE[self::PROFILE_COOKIE] != ThemeGenerator::$defaultLight) {
			$darkTheme = $_COOKIE[ self::PROFILE_COOKIE ];
		}

		// Check if the login theme is set
		if ( isset($_POST[self::LOGIN_THEME_COOKIE] ) ) {
			
			//Set a cookie if a post was mode
			AuxLib::setCookie(  self::LOGIN_THEME_COOKIE, $_POST[self::LOGIN_THEME_COOKIE], self::$cookieLength);
			$loginTheme = $_POST[self::LOGIN_THEME_COOKIE];

		} else if ( isset($_COOKIE[self::LOGIN_THEME_COOKIE] ) ) {
			$loginTheme = $_COOKIE[self::LOGIN_THEME_COOKIE];	
		} 


		
		// get the button post value; The opposite of what theme is set. 
		$nextTheme = ( $loginTheme == ThemeGenerator::$defaultLight ) ? $darkTheme : ThemeGenerator::$defaultLight;

		$this->currentTheme = $loginTheme;
		$this->nextTheme = $nextTheme;

		$this->currentColor = null;	
		if ( isset($_COOKIE[self::LOGIN_BACKGROUND_COOKIE]) )
			$this->currentColor = $_COOKIE[self::LOGIN_BACKGROUND_COOKIE];


		$this->registerJS();
	}

	public static function render(){
		$th = new LoginThemeHelper;
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
		if ( !isset($_POST[self::LOGIN_THEME_COOKIE]) ) {
			return;
		}

		$themeName = $_POST[self::LOGIN_THEME_COOKIE];
	    $profile = X2Model::model('Profile')->findByPk(Yii::app()->user->id);

	    if( $profile->theme['themeName'] == '' || $profile->theme['themeName'] == ThemeGenerator::$defaultLight) {
	        $profile->theme = ThemeGenerator::loadDefault( $themeName );
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
		Yii::app()->clientScript->registerCoreScript('cookie', 		  CClientScript::POS_READY);
		Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/LoginThemeHelper.js', CClientScript::POS_END);		

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
		);

		$JSON = array(
			'themeColorCookie' => self::LOGIN_BACKGROUND_COOKIE,
			'cookieLength' => self::$cookieLength,
			'open' => isset($_POST[self::LOGIN_THEME_COOKIE]),
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
