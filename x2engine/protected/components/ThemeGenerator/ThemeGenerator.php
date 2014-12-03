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




/**
 * Class to generate a CSS files from profile settings, works as a templating system.
 * Templates files are .php files that return a string of css. The array $colors will be sent to the files with generated keys
 * based on $settingsList. 
 *
 * For example, because 'text' is a key in $settingsList, 
 *       $colors[text]
 *       $colors[dark_text]
 *       $colors[darker_text]
 *       $colors[light_text]
 *       $colors[lighter_text]
 *       $colors[opaque_text]
 *
 *
 * Are all avaliable to the templates.
 *
 *  In addition, the keys 
 *
 *  $colors[smart_text]
 *  $colors[smart_text2]
 *  
 *  will adapt based on the difference in contrast between highlight1 and highlight2 with text respecitvely 
 * 
 * It is also important to note that
 *       $colors[none]
 *       $colors[inherit]
 * are avaliable, which renders as "none !important;" only if 'content' is set in the theme.
 * this allows for transparency without ruining the default look.
 *
 * An entry in the color array automatically adds the !important tag and a semicolon. 
 * Example Template entry: 
 * 
 *   #content a:hover {
 *      color: $colors[darker_link]  
 *   }
 *   
 *   #content span {
 *      background: $colors[highlight2]
 *      color: $colors[smart_text2]
 *   }
 * 
 * To use  key that doesnt have the !important tag added, append _hex to the key
 *   $colors[text_hex], $colors[darker_hex]
 *
 */
class ThemeGenerator {

    /**
    * @var array pointer for Yii::app()->params->profile->preferences,
    * or a custom array of theme attributes
    */
    public $preferences;

    /**
    * @var name of default light theme
    */
    public static $defaultLight = 'Default';

    /**
    * @var name of default dark theme
    */
    public static $defaultDark = 'Terminal';

    /**
     * @var array list of the profile setting keys and their descriptions
     * This list is used in the Profile Model to set up the the theme behavior
     */
    protected $settingsList = array(
        'background' => 'Background',
        'content' => 'Content',
        'text' => 'Text',
        'link' => 'Links',
        'highlight1' => 'Windows and Buttons',
        'highlight2' => 'Highlight'
        );

    /**
     * @var string Path to the folder of templates.
     */
    const TEMPLATE_PATH = 'components/ThemeGenerator/templates';

    /**
     * Contructor
     */
    public function __construct(){
        foreach($this->settingsList as $key => $setting) {
            $this->settingsList[$key] = Yii::t('profile', $setting);
        }

    }

    /**
     * Loads and processes the tempates with an array of keys
     * @return string $rendered css files
     */
    public function loadTemplates($colors){
        $css = '';

        $dir = new DirectoryIterator( 
            Yii::app()->basePath.DIRECTORY_SEPARATOR.self::TEMPLATE_PATH );
        foreach ($dir as $fileinfo) {
            if (preg_match ('/\.php$/', $fileinfo)) {
                $css .= include $fileinfo->getPathname();
            }
        }
        return $css;
    }

    /**
     * Adds !important; to each set value. If a color is not set in the profile, 
     * simply adds a semicolon to prevent errors
     * @param $value string a hash code for a color (with the hash)
     * @return string returns the formatted color string
     */
    public function formatColor($value){
        if(!isset($value) || !$value){
            $value = ';';
        } else {
            $value = "$value !important;";
        }

        return $value;
    }

    /**
     * Populates the array with different color option
     * @return array array filled with formatted css color strings
     */
    public function getColorArray(){
        $colors = array();
        $colors['none'] = '';
        $colors['smart_text'] = '';
        $colors['smart_text2'] = '';
        $colors['inherit'] = '';

        if($this->preferences['backgroundImg']) {
            $this->preferences['background']='';
        }

        foreach($this->settingsList as $key => $value){

            $value = isset( $this->preferences[$key]) ? 
                $this->preferences[$key] : '';
            
            $colors[$key] = '#'.$value;
            $colors['darker_'.$key] = X2Color::brightness($value, -0.1, false);
            $colors['dark_'.$key] = X2Color::brightness($value, -0.05, false);
            
            $colors['brighter_'.$key] = X2Color::brightness($value, 0.1, false);
            $colors['bright_'.$key] = X2Color::brightness($value, 0.05, false);
  
            $colors['lighter_'.$key] = X2Color::brightness($value, 0.1, true);
            $colors['light_'.$key] = X2Color::brightness($value, 0.05, true);


            $colors['opaque_'.$key] = X2Color::opaque($value, 0.2);
        }


        # settings for most borders in the app
        $colors['border'] = $colors['lighter_content'];

        # transparent overide if theme is set
        if( $this->preferences['content'] ) {
            $colors['none'] = 'none';
            $colors['inherit'] = 'inherit';
        }

        # Smart text for highlight 1 (Buttons and Windows)
        if( $this->preferences['highlight1'] && $this->preferences['text'] ) {
            $colors['smart_text'] = X2Color::smartText($colors['highlight1'], $colors['text']);
        }

        # Smart text for highlight 2 (highlighted buttons)
        if( $this->preferences['highlight2'] && $this->preferences['text'] ) {
            $colors['smart_text2'] = X2Color::smartText($colors['highlight2'], $colors['text']);
        }

        # Add important tags
        foreach($colors as $key => $value){
            $colors[$key.'_hex'] = $value;
            $colors[$key] = $this->formatColor($value);
        }

        return $colors;
    }

    public function getCss() {
        $colors = $this->getColorArray();
        $css = $this->loadTemplates($colors);
        return $css;
    }

    /**
     * Computes the theme and registers it with Yii
     */
    public function render() {
        $profile = Yii::app()->params->profile;

        if (!$profile) 
            return;
        
        $this->preferences = $profile->getTheme ();
        if (!$this->preferences['themeName'] || $this->preferences['themeName'] == self::$defaultLight){
            return;
        }

        $css = $this->getCss();
        Yii::app()->clientScript->registerCSS('ProfileGeneratedCSS', $css, 'screen', CClientScript::POS_HEAD);
    }

    /**
     * Loads a default theme owned by the admin
     */
    public static function loadDefault($themeName) {
        $media = X2Model::model('Media')->findByAttributes(
            array(
                'associationType' => 'theme',
                'fileName' => $themeName,
                'private' => 0,
            )
        );

        if( !$media ) {
            $media = X2Model::model('Media')->findByAttributes(
                array(
                    'associationType' => 'theme',
                    'fileName' => self::$defaultDark
                )
            );
        }

        $json = CJSON::decode( $media->description );

        return $json;
    }

    /**
     * Renders a default theme
     */
    public function renderDefault($themeName) {
        if( $this->preferences['themeName'] == self::$defaultLight) {
            return;
        }

        $this->preferences = $this->loadDefault($themeName);

        if(!$this->preferences) {
            return;
        }

        $css = $this->getCss();
        Yii::app()->clientScript->registerCSS('ThemeCSS'+$themeName, $css, 'screen');    }

    /**
     * static wrapper function for render and default render
     */
    public static function renderTheme($themeName=null) {
        $tg = new ThemeGenerator();

        if( $themeName ) {
            $tg->renderDefault($themeName);
        } else {
            $tg->render();
        }
    }

    /**
     * Renders the settings for the profile page based on the $settingsList array 
     * TODO: Move to a class for rendering the theme settings. 
     */
    public function renderSettings(){
        $preferences = $this->preferences;

        $i = 0;
        foreach($this->settingsList as $key => $setting){
            $value = isset($preferences[$key]) ? $preferences[$key] : '' ;
            echo "<div class='row' style='display:inline-block; margin-right:15px;'>
                    <label for='pageHeaderBgColor'>
                        $setting
                    </label>
                    <input  type='text'
                           name='preferences[$key]'
                           id='preferences_$key'
                           value='$value'
                           class='color-picker-input theme-attr'> 
                    </input>
                  </div>";

            if (++$i % 3 == 0)
                echo '</br>';
        }

        echo "<div style='clear:both;'></div>";
    }

    /**
     * Wrapper method to retutn the settings list
     */
    public static function getSettings(){
        $tg = new ThemeGenerator;
        return $tg->settingsList;
    }

    public static function removeBackdrop() {
        Yii::app()->clientScript->registerScript ('RemoveBackropJS', '
            $(function() {
                $("#content").addClass("no-backdrop");
            });
        ', CClientScript::POS_END);
    }


}

?>
