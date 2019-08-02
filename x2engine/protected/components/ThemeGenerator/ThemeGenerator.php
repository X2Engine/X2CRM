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
 * Class to generate a CSS files from profile settings, works as a templating system.
 * Templates files are .php files that return a string of css. The array $colors will be sent to 
 * the files with generated keys based on $settingsList. 
 *
 * For example, because 'text' is a key in $settingsList, 
 *       $colors[text]
 *       $colors[dark_text]
 *       $colors[darker_text]
 *       $colors[bright_text]
 *       $colors[brighter_text]
 *       $colors[light_text]
 *       $colors[lighter_text]
 *       $colors[opaque_text]
 *
 *
 * Are all avaliable to the templates.
 * light and lighter keys are 'smart' meaning it 
 * will appear brighter on dark themes and darker on dark themes.
 *
 *  In addition, the keys 
 *
 *  $colors[smart_text]
 *  $colors[smart_text2]
 *  
 *  will adapt based on the difference in contrast between highlight1 and highlight2 with text 
 *  respectively 
 * 
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
 * To use key that doesnt have the !important tag added, append _hex to the key
 *   $colors[text_hex], $colors[darker_hex]
 *
 * @see ThemeBuildCommand for how to build themes out of theme tags.
 */
class ThemeGenerator {

    /**
     * @var string Path to the folder of templates.
     */
    const TEMPLATE_PATH = 'components/ThemeGenerator/templates';

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
     * @deprecated use getProfileKeys instead
     */
    public static $settingsList = array(
        'background',
        'content',
        'text',
        'link',
        'highlight1',
        'highlight2',
    );

    private static function getCacheKey () {
        return get_called_class ().'_theme_'.Yii::app()->user->getName ();
    }

    public static function getSettingsList () {
        return array_merge (
            self::getProfileKeys (false, true, false));
    }

    public static function clearCache () {
        Yii::app()->cache->set (self::getCacheKey (), false, 0);
    }

    /**
     * Populates the array with different color option
     * @return array array filled with formatted css color strings
     */
    public static function generatePalette($preferences, $refresh=false){
        $computedTheme = Yii::app()->cache->get (self::getCacheKey ());
        if (!Yii::app()->user->isGuest && $computedTheme && !$refresh) {
            return $computedTheme;
        }

        $colors = $preferences;

        //Keys for smart text
        $colors['smart_text'] = '';
        $colors['smart_text2'] = '';

        if(isset($colors['backgroundImg']) && $colors['backgroundImg']) {
            $colors['background']='';
        }

        $settings = self::getSettingsList ();
        foreach($settings as $key){

            if (!isset ($colors[$key])) $colors[$key] = '';
            $value = $colors[$key];
            
            if (!preg_match("/#/", $value) && $value){
                $colors[$key] = '#'.$value;
            }

            if (!preg_match ('/_override$/', $key)) {
                $colors['darker_'.$key] = X2Color::brightness($value, -0.1, false);
                $colors['dark_'.$key] = X2Color::brightness($value, -0.05, false);
                
                $colors['brighter_'.$key] = X2Color::brightness($value, 0.1, false);
                $colors['bright_'.$key] = X2Color::brightness($value, 0.05, false);
        

                $colors['opaque_'.$key] = X2Color::opaque($value, 0.2);
            }
            $colors['light_'.$key] = X2Color::brightness($value, 0.05, true);
            $colors['lighter_'.$key] = X2Color::brightness($value, 0.1, true);
        }

        // generate smart text for module overrides
        foreach (array_filter ($settings, function ($key) { 
            return preg_match ('/^background_.*_override$/', $key);
        }) as $key) {
            if (isset ($colors[$key]) && $colors[$key]) {
                $colors[preg_replace ('/^background_/', 'smart_text_', $key)] = X2Color::smartText (
                    $colors[$key], $colors['text'] ? '' : '#000000');
            } else {
                $colors[preg_replace ('/^background_/', 'smart_text_', $key)] = '';
            }
        }

        # settings for most borders in the app
        $colors['border'] = $colors['lighter_content'];

        # Smart text for highlight 1 (Buttons and Windows)
        if( isset($colors['highlight1'], $colors['text']) &&
                !empty($colors['highlight1']) && !empty($colors['text']) ) {
            $colors['smart_text'] = X2Color::smartText($colors['highlight1'], $colors['text']);
        }

        # Smart text for highlight 2 (highlighted buttons)
        if( isset($colors['highlight2'], $colors['text']) &&
                !empty($colors['highlight2']) && !empty($colors['text']) ) {
            $colors['smart_text2'] = X2Color::smartText($colors['highlight2'], $colors['text']);
        }

        Yii::app()->cache->set (self::getCacheKey (), $colors, 0);
        return $colors;
    }

    /**
     * Formats a color array to be CSS-ready by adding important tags and
     * adding a key appended with hex that does not have the important tags
     * @param array Array of color keys
     * @return array Array of formatted array
     */
    public static function formatColorArray($colors) {
        foreach($colors as $key => $value){
            # keep original value in special key
            $colors[$key.'_hex'] = $value;
            # Add important tags
            $colors[$key] = self::formatColor($value);
        }

        return $colors;
    }

    /**
     * Computes the theme and registers it with Yii
     * @param array $colors If set, will render CSS with these colors
     * Otherwise, it uses colors from the users profile
     */
    public static function render() {
        self::renderThemeWithColors ();
    }

    /**
     * Loads a theme for the login page
     * @param string $themeName string of the theme to render
     * @param bool $computed whether or not to include computed theme color values
     */
    public static function loadDefault($themeName, $computed=true) {
        //In case default light was deleted
        if (Yii::app()->getEdition() == 'opensource') {
            if ($themeName == self::$defaultLight) {
                return array('themeName'=>self::$defaultLight);
            }
        }

        $media = X2Model::model('Media')->findByAttributes(
            array(
                'associationType' => 'theme',
                'fileName' => $themeName,
                'private' => 0,
            )
        );

        if (!$media) {
            $media = X2Model::model('Media')->findByAttributes(
                array(
                    'associationType' => 'theme',
                    'fileName' => self::$defaultDark
                )
            );

            if (!$media) {
                return self::loadDefault(self::$defaultLight);
            }
        }

        $theme = CJSON::decode ($media->description);
        if ($computed) {
            $colors = ThemeGenerator::generatePalette($theme, true);
        } else {
            $colors = $theme;
        }
        return $colors;
    }

   
    /**
     * Wrapper function for render and default render
     */
    public static function renderTheme($themeName=null) {
        if ($themeName) {
            $colors = self::loadDefault($themeName);
            self::renderThemeWithColors($colors);
        } else {
            self::render();
        }
    }

    public static function renderThemeColorSelector (
        $label, $key, $value, $htmlOptions=array (), $disabled=false) {

        $htmlOptions = X2Html::mergeHtmlOptions (array (
            'class' => 'row theme-color-selector',
        ), $htmlOptions);
        echo X2Html::openTag ('div', $htmlOptions);
        echo "
                <label>
                    ".CHtml::encode ($label)."
                </label>
                <input type='text' name='preferences[$key]' id='preferences_$key' value='$value'
                 class='color-picker-input theme-attr' ".($disabled ? 'disabled="disabled"': '')."> 
                </input>
              </div>";
    }

    /**
     * Renders the settings for the profile page based on the $settingsList array 
     * TODO: Move to a class for rendering the theme settings. 
     */
    public static function renderSettings(){
        $colors = self::generatePalette (Yii::app()->params->profile->getTheme());
        $translations = self::getTranslations();
        $settings = self::getSettingsList ();

        $i = 0;
        foreach($settings as $key){
            if (preg_match ('/_override$/', $key) && (!isset ($colors[$key]) || !$colors[$key])) {
                continue;
            }
            $setting = isset ($translations[$key]) ? $translations[$key] : '';
            $value = isset($colors[$key]) ? $colors[$key] : '' ;
            self::renderThemeColorSelector ($setting, $key, $value);
            if (++$i % 3 == 0)
                echo '</br>';
        }

        echo "<div style='clear:both;'></div>";
    }

    /**
     * Retrieves translated labels of field names
     */
    public static function getTranslations() {
        $translations = array(
            'background' => Yii::t('profile', 'Background'),
            'content' => Yii::t('profile', 'Content'),
            'text' => Yii::t('profile', 'Text'),
            'link' => Yii::t('profile', 'Links'),
            'highlight1' => Yii::t('profile', 'Windows and Buttons'),
            'highlight2' => Yii::t('profile', 'Highlight')
        );
        $moduleOverrideKeys = self::getModuleOverrideKeys (true);
        foreach ($moduleOverrideKeys as $key) {
            if (preg_match ('/^background_/', $key )) {
                $translations[$key] = Modules::displayName (
                    true, preg_replace ('/background_([^_]*)_override$/', '$1', $key));
            }
        }
        return $translations;
    }

    /**
     * Function to remove the color from the #content element of a page. 
     */
    public static function removeBackdrop() {
        Yii::app()->clientScript->registerScript ('RemoveBackropJS', '
            $(function() {
                $("#content").addClass("no-backdrop");
            });
        ', CClientScript::POS_END);
    }

    public static function isThemed() {
        $theme = Yii::app()->params->profile->theme['themeName'];
        return ($theme && $theme != self::$defaultLight);
    }

    /**
     * List of keys for the profile JSON fields behaviors
     */
    private static $_profileKeys;
    public static function getProfileKeys(
        $internal=true, $base=true, $computed=true) {

        if (!isset (self::$_profileKeys)) {
            $profileKeys = array(
                'internal' => array (
                    // Internal Usage Keys (Do not use in CSS)
                    'themeName', 
                ),

                /***********************
                 *  Base Color Keys 
                 ***********************/

                 'base' => array (
                
                    // The color of the background. Used in only one place
                    'background',

                     // Very common color in a theme. Background of all trays and content windows 
                     // in the app. Equates to White in the default theme.
                    'content',

                    // Text. Assumed to be in high contrast with 'content'
                    'text',

                    // Normal links. (<a> tags).
                    'link',

                    // Window headers, main menu bar, and buttons. 
                    'highlight1',

                    // Special Higlight color. Equates to highlightGreen color for
                    // the default theme. Good for highlighting save buttons. 
                    'highlight2',

                ),

                /***********************
                 *  Modifier Color Keys
                 ***********************/

                 'computed' => array (
                    // Text that will be in high contrast with highlight1. If the normal 'text' key
                    // does not have enough contrast, it will be inversed. 
                    'smart_text',

                    // See 'smart_text.' Same Idea but for highlight2
                    'smart_text2',

                    // Border color. Looks good with content colored elements. 
                    'border',

                    // Darker Keys will be 10% darker than what it is modifying
                    'darker_background',
                    'darker_content',
                    'darker_text',
                    'darker_link',
                    'darker_highlight1',
                    'darker_highlight2',

                    // Dark Keys will be 5% Darker than what it is modifying
                    'dark_background',
                    'dark_content',
                    'dark_text',
                    'dark_link',
                    'dark_highlight1',
                    'dark_highlight2',

                    // Bright keys will be 5% Lighter than what it is modifying
                    'bright_background',
                    'bright_content',
                    'bright_text',
                    'bright_link',
                    'bright_highlight1',
                    'bright_highlight2',

                    // Brighter keys will be 10% lighter than what it is modifying
                    'brighter_background',
                    'brighter_content',
                    'brighter_text',
                    'brighter_link',
                    'brighter_highlight1',
                    'brighter_highlight2',

                    // Apologies for the confusing names (brighter vs lighter)
                    // Light keys will be 5% lighter or 5% darker based on the theme
                    'light_background',
                    'light_content',
                    'light_text',
                    'light_link',
                    'light_highlight1',
                    'light_highlight2',

                    // Lighter Keys will be 10% lighter or 10% darker based on the theme
                    'lighter_background',
                    'lighter_content',
                    'lighter_text',
                    'lighter_link',
                    'lighter_highlight1',
                    'lighter_highlight2',
                        
                    // Opaque will be a a version of the color with 20% opaqueness i.e. 
                    // rgba(color, 0.2);
                    'opaque_background',
                    'opaque_content',
                    'opaque_text',
                    'opaque_link',
                    'opaque_highlight1',
                    'opaque_highlight2',
                ),
            );

            $moduleOverrideKeys = self::getModuleOverrideKeys ();
            foreach ($moduleOverrideKeys as $type => $keys) {
                $profileKeys[$type] = array_merge ($profileKeys[$type], $keys);
            }

            self::$_profileKeys = $profileKeys;
        }
        $profileKeys = self::$_profileKeys;

        $requestedKeys = array ();
        if ($internal) $requestedKeys = array_merge ($requestedKeys, $profileKeys['internal']);
        if ($base) $requestedKeys = array_merge ($requestedKeys, $profileKeys['base']);
        if ($computed) $requestedKeys = array_merge ($requestedKeys, $profileKeys['computed']);

        return $requestedKeys;
    }

    private static $_moduleOverrideKeys;
    private static function getModuleOverrideKeys ($flatten=false) {
        if (!isset (self::$_moduleOverrideKeys)) {
            $moduleNames = Modules::getModuleNames ();
            self::$_moduleOverrideKeys = 
                array (
                    'base' => array_map (function ($name) {
                        return 'background_'.$name.'_override';
                    }, $moduleNames)
                );
            self::$_moduleOverrideKeys['computed'] = 
                array_merge (
                    array_map (function ($name) {
                        return 'smart_text_'.$name.'_override';
                    }, $moduleNames),
                    array_map (function ($name) {
                        return 'light_background_'.$name.'_override';
                    }, $moduleNames),
                    array_map (function ($name) {
                        return 'lighter_background_'.$name.'_override';
                    }, $moduleNames)
                );
        }
        $moduleOverrideKeys = self::$_moduleOverrideKeys;
        if ($flatten) {
            $flattened = array ();
            foreach ($moduleOverrideKeys as $type => $keys) { 
                $flattened = array_merge ($flattened, $keys);
            }
            return $flattened;
        }
        return $moduleOverrideKeys;
    }

    /**
     * Loads and processes the tempates with an array of keys
     * @return string $rendered css files
     */
    private static function loadTemplates($colors){
        $colors = self::validateColors ($colors);

        $css = '';
        $dir = new DirectoryIterator( 
            Yii::app()->basePath.DIRECTORY_SEPARATOR.self::TEMPLATE_PATH );
        foreach ($dir as $fileinfo) {

            if (preg_match ('/generatedModuleOverrides.php$/', $fileinfo)) {
               $customModuleNames = Modules::getModuleNames (true);
               foreach ($customModuleNames as $module) {
                   $css .= include $fileinfo->getPathname();
               }
            } elseif (preg_match ('/\.php$/', $fileinfo)) {
                $css .= include $fileinfo->getPathname();
            }
        }

        return $css;
    }

    /**
     * Ensure that colors array has keys for all theme colors 
     */
    private static function validateColors (array $colors) {
        $expected = self::getProfileKeys (false, true, true);
        foreach ($expected as $key) {
            if (!isset ($colors[$key])) $colors[$key] = '';
            if (!isset ($colors[$key.'_hex'])) $colors[$key.'_hex'] = '';
        }
        return $colors;
    }

    /**
     * Loads a formatted color array into the templates and returns the generated CSS
     * @param array $colors Array of formatted colors
     * @return string string of total generated CSS 
     */
    private static function getCss($colors) {
        if (Yii::app()->getEdition() == 'opensource') {
            if (!$colors['themeName'] || $colors['themeName'] == self::$defaultLight){
                return "";
            }
        }
        else {
            if (!$colors['themeName']){
                return "";
            }
        }

        $colors = self::formatColorArray($colors);
        $css = self::loadTemplates($colors);
        return $css;
    }

    /**
     * Private helper method to reduce the number of places where the colors array is specified.
     * Allows simpler verification of colors array correctness.
     */
    private static function renderThemeWithColors ($colors=null) {
        if (!$colors) {
            $profile = Yii::app()->params->profile;

            // If no profile render the default theme
            if (!$profile) {
                self::renderTheme(self::$defaultLight);
                return;
            }

            $colors = $profile->getTheme();
            if (!$colors['themeName'])
                $colors = self::loadDefault('Default');
            $colors = self::generatePalette($colors);
        }


        $css = self::getCss($colors);

        Yii::app()->clientScript->registerCSS(
            'ProfileGeneratedCSS', $css);
    }

    /**
     * Adds !important; to each set value. If a color is not set in the profile, 
     * simply adds a semicolon to prevent errors
     * @param $value string a hash code for a color (with the hash)
     * @return string returns the formatted color string
     */
    private static function formatColor($value){
        if (!$value) $value = ';';
        if (!preg_match('/#/', $value) && !preg_match('/rgb/', $value)) {
            return $value;
        }

        if(!isset($value) || !$value){
            $value = ';';
        } else {
            $value = "$value !important;";
        }

        return $value;
    }

}

?>
