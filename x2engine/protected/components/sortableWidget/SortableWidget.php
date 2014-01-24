<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
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

 Yii::import ('application.components.X2Widget');

/**
 * Base widget class for all of the profile widgets
 * 
 * @package X2CRM.components
 */
abstract class SortableWidget extends X2Widget {

    /**
     * @var string The type of widget that this is (profile). This value is used to detect the view 
     *  files and the profile model JSON property which stores the widget layout for widgets of 
     *  this type.
     * 
     *  The shared view file must have the following name:
     *      <widget type>Widget.php
     *
     *  The profile model widget layout JSON property must have the following name
     *      <widget type>WidgetLayout
     */
    public $widgetType;

    /**
     * @var string A description of the widget
     */
    public $info = '';

    /**
     * @var object The profile model associated with the widget 
     */
    public $profile;
     
    /**
     * @var boolean Set to true when the current request is an ajax request
     */
    public $isAjaxRequest = false;

    /**
     * @var string The name of the view file containing the widget contents
     */
    public $viewFile = '';

    /**
     * A mixture of html and attributes inside curly braces. This gets used by renderWidget to 
     * render widget elements specified in child classes. As with X2GridView, each attribute inside
     * curly braces should have a corresponding method called render<attribute_name>. 
     * @var string Specifies the widget layout.
     */
    public $template = '<div class="submenu-title-bar widget-title-bar">{widgetLabel}{closeButton}{minimizeButton}</div>{widgetContents}';

    /**
     * Packages which will be registered when the widget content gets rendered.
     */
    protected $_packages;

    /**
     * @var string This script gets registered when the widget content gets rendered.
     */
    protected $_setupScript;

    /**
     * @var string This css gets registered when the widget container gets rendered.
     */
    protected $_sharedCss;

    /**
     * @var string This css gets registered when the widget content gets rendered.
     */
    protected $_css;

    /**
     * @var array Parameters to be passed to the specified view file referred to by the property
     *  $viewFile
     */
    protected $_viewFileParams;

    /**
     * @var string The name of the view file shared by all widgets of a certain type. This is the 
     *  view file from which the view file specified in $viewFile gets rendered.
     */
    protected $_sharedViewFile; 

    /**
     * @var array  
     */
    protected $_settingsFormFields;

    /**
     * @var array Contains the structure and default values of the widget's settings. Used by
     *  WidgetLayoutJSONFieldsBehavior to determine the structure of the widget layout JSON
     *  string.
     */
    private static $_JSONPropertiesStructure;


    /***********************************************************************
    * Public Static Methods
    ***********************************************************************/

    /**
     * Used to render a widget and its associated scripts during an AJAX request 
     * @param object $controller The instance of the controller that called this method. This allows
     *  us to call renderPartial, which is necessary if we want registered scripts to be included
     *  in the AJAX response.
     * @param object $profile The profile model of the user for which this widget will be rendered
     * @param string $widgetType The type of widget that this is (profile)
     */
    public static function getWidgetContents ($controller, $profile, $widgetType) {

        $controller->renderPartial (
            'application.components.sortableWidget.views._ajaxWidgetContents',
            array (
                'widgetClass' => get_called_class (), 
                'widgetType' => $widgetType,
                'profile' => $profile
            ), false, true);
    }

    /**
     * @returns array the property _JSONPropertiesStructure
     */
    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array (
                'label' => '', // required
                'hidden' => false, // required
                'minimized' => false, // required
                'containerNumber' => 1
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    /**
     * Sets the order of widgets specified in the widget layout JSON property of the Profile
     * model
     * @param object $profile The profile model 
     * @param array $widgetOrder An array of strings where each string is a profile widget 
     *  class name
     * @param string $widgetType The type of widget that this is (profile)
     * @returns boolean True for update success, false otherwise
     */
    public static function setSortOrder ($profile, $widgetOrder, $widgetType) {
        $widgetLayoutName = $widgetType . 'WidgetLayout';
        $layout = $profile->$widgetLayoutName;
        $newLayout = array ();

        // remove entries from old layout in the specified order, pushing them onto the new layout
        foreach ($widgetOrder as $widgetClass) {
            if (in_array ($widgetClass, array_keys ($layout))) {
                $newLayout[$widgetClass] = $layout[$widgetClass];
                unset ($layout[$widgetClass]);
            }
        }

        // push any remaining widgets not specified in the widget order
        foreach ($layout as $widgetClass) {
            $newLayout[$widgetClass] = $layout[$widgetClass];
        }

        $profile->$widgetLayoutName = $newLayout;
        if ($profile->save ()) {
            return true;
        }
        return false;
    }

    /**
     * Sets the value of a property, for the current widget class, of the widget layout JSON 
     * object.
     * @param object $profile The profile model
     * @param string $key The name of the JSON property
     * @param string $value The value the the JSON property will be set to
     * @param string $widgetType The type of widget that this is (profile)
     * @returns boolean True for update success, false otherwise
     */
    public static function setJSONProperty ($profile, $key, $value, $widgetType) {
        $widgetLayoutName = $widgetType . 'WidgetLayout';
        $widgetClass = get_called_class ();
        $layout = $profile->$widgetLayoutName;
        if (isset ($layout[$widgetClass])) {
            if (in_array ($key, array_keys ($layout[$widgetClass]))) {
                $layout[$widgetClass][$key] = $value;
                $profile->$widgetLayoutName = $layout;
                if ($profile->save ()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Retrieves the value of a property, for the current widget class, of the widget layout 
     * JSON object.
     * @param object $profile The profile model
     * @param string $key The name of the JSON property
     * @param string $widgetType The type of widget that this is (profile)
     * @returns mixed null if the property cannot be retrieved, other the value of the property
     */
    public static function getJSONProperty ($profile, $key, $widgetType) {
        $widgetClass = get_called_class ();
        $widgetLayoutName = $widgetType . 'WidgetLayout';
        $layout = $profile->$widgetLayoutName;
        if (isset ($layout[$widgetClass])) {
            if (in_array ($key, array_keys ($layout[$widgetClass]))) {
                return $layout[$widgetClass][$key];
            }
        }
        return null;
    }


    /***********************************************************************
    * Non-public Static Methods 
    ***********************************************************************/

    /**
     * Used by renderWidgetContents to sort template elements
     */
    private static function compareOffset ($a, $b) {
        return $a[1] > $b[1];
    }


    /***********************************************************************
    * Public Instance Methods 
    ***********************************************************************/

    public function getSharedViewFile () {
        if (!isset ($this->_sharedViewFile)) {
            $this->_sharedViewFile = $this->widgetType . 'Widget';
        }
        return $this->_sharedViewFile;
    }

    /**
     * Magic getter. Returns this widget's packages. 
     */
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array (
                'auxlib' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/auxlib.js',
                    ),
                ),
                'SortableWidgetJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/sortableWidgets/SortableWidget.js',
                    ),
                    'depends' => array ('auxlib')
                ),
            );
        }
        return $this->_packages;
    }

    /**
     * Magic setter
     * @param array $packages
     */
    public function setPackages ($packages) {
        $this->_packages = $packages;
    }

    /**
     * Registers this widgets packages. 
     */
    public function registerPackages () {
        $packages = $this->packages;
        Yii::app()->clientScript->packages = $packages;
        Yii::app()->clientScript->coreScriptPosition = CClientScript::POS_END;
        foreach (array_keys ($packages) as $packageName) {
            Yii::app()->clientScript->registerPackage ($packageName);
        }
        Yii::app()->clientScript->coreScriptPosition = CClientScript::POS_HEAD;
    }

    /**
     * Add a package to the array of registered packages 
     * @param array $package
     */
    public function addPackage ($package) {
        $this->packages = array_merge ($this->packages, $package);
    }

    /**
     * Magic getter. Returns this widget's setup script.
     * @return string JS string which gets registered when widget content gets rendered
     */
    public function getSetupScript () {
        if (!isset ($this->_setupScript)) {
            $widgetClass = get_called_class ();
            $this->_setupScript = "
                $(function () {
                    x2.".$widgetClass." = new SortableWidget ({
                        'widgetClass': '".$widgetClass."',
                        'setPropertyUrl': '".Yii::app()->controller->createUrl (
                            '/profile/setWidgetSetting')."',
                        'cssSelectorPrefix': '".$this->widgetType."',
                        'widgetType': '".$this->widgetType."'
                    });
                });
            ";
        }
        return $this->_setupScript;
    }

    /**
     * Used to register all shared css before the widget container gets rendered.
     */
    public function registerSharedCss () {
        $sharedCss = $this->sharedCss;
        foreach ($sharedCss as $cssName => $css) {
           Yii::app()->clientScript->registerCss($cssName, $css);
        }
    }

    /**
     * Used to register all css before the widget gets rendered.
     */
    public function registerCss () {
        $css = $this->css;
        foreach ($css as $cssName => $css) {
           Yii::app()->clientScript->registerCss($cssName, $css);
        }
    }

    /**
     * Magic getter.
     * Returns an array of parameters which should be passed to the view file associated with this
     *  widget.
     * @return array The value stored in $_viewFileParams 
     */
    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $this->_viewFileParams = array (
                'isAjaxRequest' => $this->isAjaxRequest
            );
        }
        return $this->_viewFileParams;
    } 

    /**
     * Renders widget components by parsing the template string and calling rendering methods
     * for each template item. HTML contained in the template gets echoed out.
     */
    public function renderWidget () {

        // don't render hidden widgets to prevent page load slow down
        $hidden = self::getJSONProperty ($this->profile, 'hidden', $this->widgetType);
        if ($hidden !== null && $hidden) return;

        $this->registerCss ();

        // extract html strings and template strings from template
        $itemMatches = array ();
        $htmlMatches = array ();
        preg_match_all ("/(?:^([^{]+)\{)|(?:\}([^{]+)\{)|(?:\}([^{]+)$)/", $this->template, 
            $htmlMatches, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);
        preg_match_all ("/{([^}]+)}/", $this->template, $itemMatches,
            PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);

        
        $templateHTML = array ();
        $templateItems = array ();

        // organize html string matches into a 2d array
        for ($i = 1; $i < sizeof ($htmlMatches); ++$i) {
            for ($j = 0; $j < sizeof ($htmlMatches[$i]); ++$j) {
                if (is_array ($htmlMatches[$i][$j]) && $htmlMatches[$i][$j][1] >= 0) {
                    $templateHTML[] = array_merge ($htmlMatches[$i][$j], array ('html'));
                }
            }
        }

        // organize template string matches into a 2d array
        for ($i = 0; $i < sizeof ($itemMatches[1]); ++$i) {
            if (is_array ($itemMatches[1][$i]) && $itemMatches[1][$i][1] >= 0) {
                $templateItems[] = array_merge ($itemMatches[1][$i], array ('item'));
            }
        }
        //AuxLib::debugLogR ($templateItems);
        //AuxLib::debugLogR ($templateHTML);

        // merge the 2 arrays and sort them by string offset
        $allTemplateItems = array_merge ($templateItems, $templateHTML);
        usort ($allTemplateItems, array ('self', 'compareOffset'));

        //AuxLib::debugLogR ($allTemplateItems);

        // echo html, call functions corresponding to template items
        for ($i = 0; $i < sizeof ($allTemplateItems); ++$i) {
            if ($allTemplateItems[$i][2] == 'html') {
                echo $allTemplateItems[$i][0];
            } else { // $allTemplateItems[$i][2] === 'item'
                $fnName = 'render' . ucfirst ($allTemplateItems[$i][0]); 
                if (method_exists ($this, $fnName)) {
                    $this->$fnName ();
                }
            }
        }
    }

    /**
     * Renders widget contents contained in the view file pointed to by the viewFile property. 
     * This gets called if {widgetContents} is contained in the template string.
     */
    public function renderWidgetContents () {
        $this->registerPackages ();

        /*
        If it's an ajax request, script must be placed at the end for it to be exectuted upon
        ajax response. Otherwise, place script at POS_BEGIN to allow dependent script files to be
        inserted afterwards.
        */
        Yii::app()->clientScript->registerScript (
            get_called_class ().'Script', $this->setupScript, 
            ($this->isAjaxRequest ? CClientScript::POS_END: CClientScript::POS_BEGIN));

        $minimized = self::getJSONProperty ($this->profile, 'minimized', $this->widgetType);
        echo "<div id='".get_called_class ()."-widget-content-container'".
            ($minimized ? "style='display: none;'" : '').">";
        $this->render ($this->viewFile, $this->getViewFileParams ());
        echo "</div>";
    }

    /**
     * Renders the widget label saved in the profile JSON widget settings property
     * This gets called if {widgetLabel} is contained in the template string.
     */
    public function renderWidgetLabel () {
        $label = self::getJSONProperty ($this->profile, 'label', $this->widgetType);
        echo "<div class='widget-title'>".htmlspecialchars($label)."</div>";
    }

    /**
     * Override in child class. This content will be turned into a popup dropdown menu with the
     * PopupDropdownMenu JS prototype.
     */
    public function getSettingsMenuContent () {
        return '<div class="widget-settings-menu-content" style="display:none;"></div>';
    }

    /**
     * Renders the show/hide settings menu icon as well as the settings menu content 
     */
    public function renderSettingsMenu () {
        $this->addPackage (array (
            'popupDropdownMenu' => array (
                'baseUrl' => Yii::app()->baseUrl,
                'js' => array (
                    'js/PopupDropdownMenu.js',
                ),
                'depends' => array ('auxlib')
            )
        ));

        $themeUrl = Yii::app()->theme->getBaseUrl();
        $htmlStr = 
            "<a href='#' class='widget-settings-button x2-icon-button' style='display:none;'>";
        $htmlStr .= CHtml::image(
            $themeUrl.'/images/widgets.png', Yii::t('app', 'Maximize Widget'),
            array (
                'title' => Yii::t('app', 'Show Widget Settings')
            ));
        $htmlStr .= '</a>';
        echo $htmlStr;
        echo $this->settingsMenuContent;
    }

    /**
     * Renders a button which allows the user to minimize/maximize the widget.
     * This gets called if {minimizeButton} is contained in the template string.
     */
    public function renderMinimizeButton () {
        $themeUrl = Yii::app()->theme->getBaseUrl();
        $htmlStr = 
            "<a href='#' class='widget-minimize-button x2-icon-button' style='display:none;'>";
        $minimized = self::getJSONProperty ($this->profile, 'minimized', $this->widgetType);
        $htmlStr .= CHtml::image(
            $themeUrl.'/images/icons/Expand_Widget.png', Yii::t('app', 'Maximize Widget'),
            array (
                'title' => Yii::t('app', 'Maximize Widget'),
                'style' => ($minimized ? '': 'display: none;')
            ));
        $htmlStr .= CHtml::image(
            $themeUrl.'/images/icons/Collapse_Widget.png', Yii::t('app', 'Minimize Widget'),
            array (
                'title' => Yii::t('app', 'Minimize Widget'),
                'style' => ($minimized ? 'display: none;' : '')
            ));
        $htmlStr .= '</a>';
        echo $htmlStr;
    }

    /**
     * Renders a button which allows the user to hide/show the widget.
     * This gets called if {closeButton} is contained in the template string.
     */
    public function renderCloseButton () {
        $themeUrl = Yii::app()->theme->getBaseUrl();
        echo "<a class='widget-close-button x2-icon-button' href='#' style='display:none;'>";
        echo CHtml::image(
            $themeUrl.'/images/icons/Close_Widget.png', Yii::t('app', 'Close Widget'),
            array ('title' => Yii::t('app', 'Close Widget'))); 
        echo "</a>";
    }

    /**
     * Render the widget container view
     */
    public function run () {

        $hidden = self::getJSONProperty ($this->profile, 'hidden', $this->widgetType);
        if ($hidden === null) $hidden = false;

        $this->registerSharedCss ();
        $this->render ($this->sharedViewFile, array (
            'widgetClass' => get_called_class (),
            'profile' => $this->profile,
            'hidden' => $hidden,
        ));
    }


    /***********************************************************************
    * Non-public instance methods 
    ***********************************************************************/

    /**
     * Magic getter. Returns this widget's shared css
     * @return array key is the proposed name of the css string which should be passed as the first
     *  argument to yii's registerCss. The value is the css string.
     */
    protected function getSharedCss () {
        if (!isset ($this->_sharedCss)) {
            $this->_sharedCss = array (
                'sortableWidgetSharedCss' => "
                    .sortable-widget-container {
                        border: 1px solid #c5c5c5;
                        border-radius:            4px 4px 4px 4px;
                        -moz-border-radius:        4px 4px 4px 4px;
                        -webkit-border-radius:    4px 4px 4px 4px;
                        -o-border-radius:        4px 4px 4px 4px ;
                        margin-top: 10px;
                    }

                    .widget-title-bar {
                        padding:3px;
                        border-radius:            3px 3px 0px 0px;
                        -moz-border-radius:        3px 3px 0px 0px;
                        -webkit-border-radius:    3px 3px 0px 0px;
                        -o-border-radius:        3px 3px 0px 0px ;
                        height: 22px;
                    }
                    
                    .widget-title {
                        display:block;
                        font-size:11pt;
                        font-weight:bold;
                        line-height:22px;
                        color:#333;
                        cursor:default;
                        margin-left: 10px;
                        float: left;
                    }
                    
                    .widget-close-button {
                        margin-right: 4px;
                        margin-top: 3px;
                        float: right;
                    }
                    
                    .widget-minimize-button {
                        margin-right: 7px;
                        margin-top: 4px;
                        float: right;
                    }

                    .widget-settings-button {
                        margin-right: 10px;
                        margin-top: 1px;
                        float: right;
                    }

                    .widget-resize-helper {
                        width: 100%;
                        height: 5px;
                        margin-top: -5px;
                        display: block;
                    }

                    ");
        }
        return $this->_sharedCss;
    }

    /**
     * Magic getter. Returns this widget's css
     * @return array key is the proposed name of the css string which should be passed as the first
     *  argument to yii's registerCss. The value is the css string.
     */
    protected function getCss () {
        if (!isset ($this->_css)) {
            $this->_css = array ();
        }
        return $this->_css;
    }
}
?>
