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




 Yii::import ('application.components.X2Widget');
 Yii::import ('application.components.sortableWidget.dataWidgets');

/**
 * Base widget class for all of the profile widgets
 * 
 * @package application.components.sortableWidget
 */
abstract class SortableWidget extends X2Widget {

    const PROFILE_WIDGET_PATH_ALIAS = 'application.components.sortableWidget.profileWidgets';
    const RECORD_VIEW_WIDGET_PATH_ALIAS = 
        'application.components.sortableWidget.recordViewWidgets';
     
    const DATA_WIDGET_PATH_ALIAS = 'application.components.sortableWidget.dataWidgets';
     

    public static $createByDefault = true;

    public static $canBeCreated = true;

    /**
     * @var string The type of widget that this is (profile). This value is used to detect the 
     *  view files and the profile model JSON property which stores the widget layout for widgets 
     *  of this type.
     * 
     *  The shared view file must have the following name:
     *      <widget type>Widget.php
     *
     *  The profile model widget layout JSON property must have the following name
     *      <widget type>WidgetLayout
     */
    public $widgetType;

    /**
     * @var SortableWidgetManager $widgetManager
     */
    public $widgetManager; 

    /**
     * @var string JS class which is used to manage the front-end behavior of this widget. This is 
     *  the class which gets instantiated by the setup script.
     */
    public $sortableWidgetJSClass = 'SortableWidget';

    public $defaultTitle;

    /**
     * @var string Used to distinguish widget clones from eachother
     */
    public $widgetUID = '';

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
     * @var bool If true, the widget can be relabeled by the user from the widget settings menu 
     */
    public $relabelingEnabled = false;

    /**
     * @var bool If true, the widget can be deleted by the user from the widget settings menu 
     */
    public $canBeDeleted = false;

    /**
     * A mixture of html and attributes inside curly braces. This gets used by renderWidget to 
     * render widget elements specified in child classes. As with X2GridView, each attribute inside
     * curly braces should have a corresponding method called render<attribute_name>. 
     * @var string Specifies the widget layout.
     */
    public $template = '<div class="submenu-title-bar widget-title-bar">{widgetLabel}{closeButton}{minimizeButton}</div>{widgetContents}';

    /**
     * @var array properties which will get passed to the constructor of the JS SortableWidget
     *  class or subclass which corresponds with this widget. 
     */
    protected $_JSSortableWidgetParams;

    /**
     * @var array translations to be passed along with $_JSSortableWidgetParams to the JS Sortable
     *  Widget class constructor
     */
    protected $_translations;

    /**
     * @var string CSS class to be added to the container element
     */
    protected $containerClass = 'sortable-widget-container x2-layout-island';

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


    private $_widgetLabel;

    private $_widgetProperties;

    /**
     * Used for situations where widget type should resolve to parent type (e.g for 
     * module-specific record view layouts)
     */
    public static function getParentType ($widgetType) {
        if (!in_array ($widgetType, array ('data', 'recordView', 'profile'))) {
            return 'recordView';
        } else {
            return $widgetType;
        }
    }

    /**
     * Returns path alias of directory that contains widgets of the specified type
     * @param string $widgetType
     */
    public static function getPathAlias ($widgetType) {
        switch ($widgetType) {
            case 'profile':
                $pathAlias = self::PROFILE_WIDGET_PATH_ALIAS;
                break;
            case 'topics':
            case 'recordView':
                $pathAlias = self::RECORD_VIEW_WIDGET_PATH_ALIAS;
                break;
             
            case 'data':
                $pathAlias = self::DATA_WIDGET_PATH_ALIAS;
                break;
             
            default:
                throw new CException ('invalid widget type');
        }
        return $pathAlias;
    }

    /**
     * @var string $widgetType
     * @return string array of instantiatable widget class names
     * @throws CException
     */
    public static function getWidgetSubtypes ($widgetType) {
        static $cache = array ();

        if (!in_array ($widgetType, array ('profile', 'topics', 'recordView'
            , 'data'))) {

            throw new CException ('invalid widget type');
        }

        
        // For other purposes, charts holds a list of the different 
        // Possible chart types, so we will just re-use this. 
        if ($widgetType == 'data') {
            return Charts::getWidgets();
        }
        

        if (!isset ($cache[$widgetType])) {
            $excludeList = array ('TemplatesGridViewProfileWidget.php');
            $cache[$widgetType] = array_map (function ($file) {
                    return preg_replace ("/\.php$/", '', $file);
                },
                array_filter (
                    scandir(Yii::getPathOfAlias(self::getPathAlias ($widgetType))),
                    function ($file) use ($excludeList) {
                        return !in_array ($file, $excludeList) && preg_match ("/\.php$/", $file);
                    }
                ));
        }
        return $cache[$widgetType];
    }

    public static function subtypeIsValid ($widgetType, $widgetSubtype) {
         
        if (in_array($widgetSubtype, SortableWidget::getWidgetSubtypes('data'))) {
            return true;
        }
         

        if ($widgetType === 'profile' && $widgetSubtype === 'TemplatesGridViewProfileWidget' ||
            in_array ($widgetSubtype, SortableWidget::getWidgetSubtypes ($widgetType))) {

            return true;
        } else {
            return false;
        }
    }

    public static function getCreatableWidgetOptions ($widgetType) {
        $widgetSubtypes = self::getWidgetSubtypeOptions ($widgetType);
        $filtered = array ();
        foreach ($widgetSubtypes as $type => $label) {
            if (preg_match ('/TemplatesGridViewProfileWidget$/', $type)) {
                $className = 'TemplatesGridViewProfileWidget';
            } else {
                $className = $type;
            }
            if (class_exists ($className) && $className::$canBeCreated) $filtered[$type] = Yii::t('app',$label);
        }    
        return $filtered;
    }    

    /**
     * @var string $widgetType
     * @return array associative array with widget class names as keys and widget labels as values
     */
    public static function getWidgetSubtypeOptions ($widgetType) {
        static $cache = array ();
        if (!isset ($cache[$widgetType])) {
            $widgetSubtypes = self::getWidgetSubtypes ($widgetType);

            $cache[$widgetType] = array_combine (
                $widgetSubtypes,
                array_map (function ($widgetType) {
                    $jsonPropertiesStruct = $widgetType::getJSONPropertiesStructure ();
                    return $jsonPropertiesStruct['label'];
                }, $widgetSubtypes)
            );

            // add custom module summary pseudo-subtypes
            if ($widgetType === 'profile') {
                $customModules = Modules::model ()->getCustomModules (true);

                foreach ($customModules as $module) {
                    $modelName = ucfirst ($module->name);
                    if ($module->name !== 'document' && class_exists ($modelName)) {
                        // prefix widget class name with custom module model name and a delimiter
                        $cache[$widgetType][$modelName.'::TemplatesGridViewProfileWidget'] =
                            Yii::t(
                                'app', '{modelName} Summary', array ('{modelName}' => Modules::displayName(true, $module->name)));
                    }
                }
            }
        }
        return $cache[$widgetType];
    }

    /**
     * Used to render a widget and its associated scripts during an AJAX request 
     * @param object $controller The instance of the controller that called this method. This 
     *  allows us to call renderPartial, which is necessary if we want registered scripts to be 
     *  included in the AJAX response.
     * @param object $profile The profile model of the user for which this widget will be rendered
     * @param string $widgetType The type of widget that this is (profile)
     * @param array $extraWidgetParams Extra params to pass to the widget (optional)
     */
    public static function getWidgetContents (
        $controller, $profile, $widgetType, $widgetUID, $extraWidgetParams=array ()) {

        return CJSON::encode (array (
            'uid' => $widgetUID,
            'widget' => $controller->renderPartial (
                'application.components.sortableWidget.views._ajaxWidgetContents',
                array (
                    'widgetClass' => get_called_class (), 
                    'widgetType' => $widgetType,
                    'profile' => $profile,
                    'widgetUID' => $widgetUID,
                    'extraWidgetParams' => $extraWidgetParams,
                ), true, true)));
    }

    /**
     * @returns array the property _JSONPropertiesStructure
     */
    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array (
                'label' => '', // required
                'uid' => '', // required
                'hidden' => false, // required
                'minimized' => false, // required
                'containerNumber' => 1,
                'softDeleted' => false,
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    /**
     * Instantiates the widget
     * @param string $widgetLayoutKey Key in widget layout associative array. Contains the widget
     *  class name as well as the uid
     * @param object profile
     */
    public static function instantiateWidget (
        $widgetLayoutKey, $profile, $widgetType = 'profile', $options = array()) {

        list($widgetClass, $widgetUID) = SortableWidget::parseWidgetLayoutKey ($widgetLayoutKey);
        if ($widgetClass::getJSONProperty (
            $profile, 'softDeleted', $widgetType, $widgetUID)) {

            return;
        }
        return Yii::app()->controller->widget(
            'application.components.sortableWidget.'.$widgetClass, array_merge(
                array(
                    'widgetUID' => $widgetUID,
                    'profile' => $profile,
                    'widgetType' => $widgetType,
                )
        , $options));
    }

    /**
     * @param $layoutKey The key in the widget layout associated with the widgets json properties.
     *  The key contains both the widget class name and the widget's unique id
     * @return array (<widget class name>, <widget uid>)
     */
    public static function parseWidgetLayoutKey ($widgetLayoutKey) {
        if (preg_match ("/_(\w*)$/", $widgetLayoutKey, $matches)) {
            $widgetUID = $matches[1];
        } else {
            $widgetUID = '';
        }

        $widgetClass = preg_replace ("/_\w*/", '', $widgetLayoutKey);
        return array ($widgetClass, $widgetUID);
    }

    /**
     * @param object $profile The profile model
     * @param string $widgetType The type of the widget 
     * @param string $widgetLayoutName The name of the layout that the widget belongs to
     * @param array $widgetSettings (optional) Widget setting values indexed by setting names. 
     *  Used to set initial values of widget settings
     * @return array (<success>, <uid>)
     */
    public static function createSortableWidget (
        $profile, $widgetClass, $widgetType, $widgetSettings=array ()) {

        if (!self::subtypeIsValid ($widgetType, $widgetClass)) {
            return array (false, null);
        }

        $widgetLayoutPropertyName = $widgetType . 'WidgetLayout';
        $layout = $profile->$widgetLayoutPropertyName;

        // first look for a widget which has been soft deleted
        if (isset ($layout[$widgetClass]) && $layout[$widgetClass]['softDeleted']) {
            $layout[$widgetClass]['softDeleted'] = false;

            $profile->$widgetLayoutPropertyName = $layout;
            if ($profile->save ()) {
                return array (true, '');
            } else {
                return array (false, null);
            }
        }

        $uniqueId = uniqid ();
        $widgetUniqueName = $widgetClass.'_'.$uniqueId;
        while (true) {
            if (!isset ($layout[$widgetUniqueName])) {
                break;
            }
            $uniqueId = uniqid ();
            $widgetUniqueName = $widgetClass.'_'.$uniqueId;
        }

        $layout[$widgetUniqueName] = array_merge (
            array (
                'hidden' => false,
                'minimized' => false,
            ), $widgetSettings);

        $profile->$widgetLayoutPropertyName = $layout;

        if ($profile->update ()) {
            return array (true, $uniqueId);
        } else {
            return array (false, null);
        }
    }

    /**
     * @param object $profile The profile model
     * @param string $widgetClass 
     * @param string $widgetUID 
     * @param string $widgetLayoutName 
     * @return bool true for success, false otherwise
     */
    public static function deleteSortableWidget (
        $profile, $widgetClass, $widgetUID, $widgetLayoutName) {

        $widgetLayoutPropertyName = $widgetLayoutName . 'WidgetLayout';
        $layout = $profile->$widgetLayoutPropertyName;
        if ($widgetUID !== '')
            $widgetKey = $widgetClass.'_'.$widgetUID;
        else 
            $widgetKey = $widgetClass;

        if (!isset ($layout[$widgetKey])) {
            return false;
        }

        if ($widgetUID === '') {
            // default widgets don't actually get removed form the layout
            $layout[$widgetKey]['softDeleted'] = true;
        } else {
            unset ($layout[$widgetKey]);
        }

        $profile->$widgetLayoutPropertyName = $layout;

        if ($profile->update ()) {
            return true;
        } else {
            return false;
        }
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
        foreach ($widgetOrder as $widgetKey) {
            if (in_array ($widgetKey, array_keys ($layout))) {
                $newLayout[$widgetKey] = $layout[$widgetKey];
                unset ($layout[$widgetKey]);
            }
        }

        // push any remaining widgets not specified in the widget order
        foreach ($layout as $widgetClass => $settings) {
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
     * @return boolean True for update success, false otherwise
     * @deprecated use setJSONProperties instead
     */
    public static function setJSONProperty ($profile, $key, $value, $widgetType, $widgetUID) {
        return self::setJSONProperties (
            $profile, array ($key => $value), $widgetType, $widgetUID);
    }

    /**
     * Sets the values of a properties, for the current widget class, of the widget layout JSON 
     * object.
     * @param object $profile The profile model
     * @param array $props
     * @param string $widgetType The type of widget that this is (profile)
     * @return boolean True for update success, false otherwise
     */
    public static function setJSONProperties ($profile, array $props, $widgetType, $widgetUID) {
        $widgetLayoutName = $widgetType . 'WidgetLayout';
        $widgetClass = get_called_class ();

        if ($widgetUID !== '')
            $widgetKey = $widgetClass.'_'.$widgetUID;
        else 
            $widgetKey = $widgetClass;

        $layout = $profile->$widgetLayoutName;
        $update = false;
        foreach ($props as $key => $value) {
            if (isset ($layout[$widgetKey])) {
                if (in_array ($key, array_keys ($layout[$widgetKey]))) {
                    $layout[$widgetKey][$key] = $value;
                    $update = true;
                }
            }
        }
        if ($update) {
            $profile->$widgetLayoutName = $layout;
            if ($profile->update ()) {
                return true;
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
     * @return mixed null if the property cannot be retrieved, other the value of the property
     */
    public static function getJSONProperty ($profile, $key, $widgetType, $widgetUID) {
        $properties = self::getJSONProperties($profile, $widgetType, $widgetUID);
        if (isset($properties[$key])) {
            return $properties[$key];
        }
        return null;



        // $widgetClass = get_called_class ();
        // $widgetLayoutName = $widgetType . 'WidgetLayout';

        // if ($widgetUID !== '')
        //     $widgetKey = $widgetClass.'_'.$widgetUID;
        // else 
        //     $widgetKey = $widgetClass;

        // $layout = $profile->$widgetLayoutName;
        // if (isset ($layout[$widgetKey])) {
        //     if (in_array ($key, array_keys ($layout[$widgetKey]))) {
        //         return $layout[$widgetKey][$key];
        //     }
        // }
        // return null;
    }

    /**
     * Retrieves the value of a property, for the current widget class, of the widget layout 
     * JSON object.
     * @param object $profile The profile model
     * @param string $key The name of the JSON property
     * @param string $widgetType The type of widget that this is (profile)
     * @return mixed null if the property cannot be retrieved, other the value of the property
     */
    public static function getJSONProperties ($profile, $widgetType, $widgetUID) {
        $widgetClass = get_called_class ();
        $widgetLayoutName = $widgetType . 'WidgetLayout';

        if ($widgetUID !== '')
            $widgetKey = $widgetClass.'_'.$widgetUID;
        else 
            $widgetKey = $widgetClass;

        $layout = $profile->$widgetLayoutName;

        if (isset ($layout[$widgetKey])) {
            return $layout[$widgetKey];
        }
        return null;
    }

    /**
     * Used by renderWidgetContents to sort template elements
     */
    private static function compareOffset ($a, $b) {
        return $a[1] > $b[1];
    }

    /**
     * @return string key which uniquely identifies this widget 
     */
    public function getWidgetKey () {
        return get_called_class () . '_' . $this->widgetUID;
    }

    /**
     * Non-static wrapper around getJSONProperty which adds caching
     * @param string $key The name of the JSON property
     */
    public function getWidgetProperty ($key) {
        if (!isset ($this->_widgetProperties)) {
            $this->getWidgetProperties ();
        }
        if (!isset ($this->_widgetProperties[$key])) {
            return null;
        }
        return $this->_widgetProperties[$key];
    }

    public function getWidgetProperties () {
        if (!isset ($this->_widgetProperties)) {
            $this->_widgetProperties = self::getJSONProperties (
                $this->profile, $this->widgetType, $this->widgetUID);
        }
        return $this->_widgetProperties;
    }

    /**
     * Non-static wrapper around setJSONProperties which caches the property value for 
     * getWidgetProperty ()
     * @param array $props
     */
    public function setWidgetProperties (array $props) {
        if (self::setJSONProperties (
            $this->profile, $props, $this->widgetType, $this->widgetUID)) {

            foreach ($props as $key => $value) {
                $this->_widgetProperties[$key] = $value;
            }
            return true;
        }
        return false;
    }

    /**
     * Non-static wrapper around setJSONProperty which caches the property value for 
     * getWidgetProperty ()
     * @param string $key The name of the JSON property
     * @param string $value 
     */
    public function setWidgetProperty ($key, $value) {
        if (self::setJSONProperties (
            $this->profile, array ($key => $value), $this->widgetType, $this->widgetUID)) {

            $this->_widgetProperties[$key] = $value;
            return true;
        }
        return false;
    }

    /**
     * @return string widget label 
     */
    public function getWidgetLabel () {
        return Yii::t('app',$this->getWidgetProperty ('label'));
    }

    public function getSharedViewFile () {
        if (!isset ($this->_sharedViewFile)) {
            $this->_sharedViewFile = self::getParentType ($this->widgetType) . 'Widget';
        }
        return $this->_sharedViewFile;
    }

    public function setSharedViewFile ($sharedViewFile) {
        $this->_sharedViewFile = $sharedViewFile;
    }

    /**
     * Magic getter. Returns this widget's packages. 
     */
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages (), array (
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
                    'depends' => array ('auxlib', 'X2Widget')
                ),
            ));
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
     * Add a package to the array of registered packages 
     * @param array $package
     */
    public function addPackage ($package) {
        $this->packages = array_merge ($this->packages, $package);
    }

    public function getJSInstanceName () {
        $widgetClass = get_called_class ();
        return $widgetClass.$this->widgetUID;
    }

    /**
     * Magic getter. Returns this widget's setup script.
     * @return string JS string which gets registered when widget content gets rendered
     */
    public function getSetupScript () {
        if (!isset ($this->_setupScript)) {
            $this->_setupScript = "
                $(function () {
                    x2.".$this->getJSInstanceName ()." = new $this->sortableWidgetJSClass (".
                        CJSON::encode ($this->getJSSortableWidgetParams ()).
                    ");
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
        foreach ($this->sharedCssFileNames as $filename) {
            Yii::app()->clientScript->registerCssFile(
                Yii::app()->theme->baseUrl.'/css/'.$filename); 
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

    private $_errors = array ();
    public function addError ($message) {
        $this->_errors[] = $message;
    }

    public function hasError () {
        return count ($this->_errors);
    }

    /**
     * Renders widget components by parsing the template string and calling rendering methods
     * for each template item. HTML contained in the template gets echoed out.
     */
    public function renderWidget () {
        // don't render hidden widgets to prevent page load slow down
        $hidden = self::getJSONProperty (
            $this->profile, 'hidden', $this->widgetType, $this->widgetUID);

        if ($hidden !== null && $hidden) return;

        $this->registerCss ();

        // extract html strings and template strings from template
        $itemMatches = array ();
        $htmlMatches = array ();
        // TODO: rename template property _template and add getTemplate method to base class
        if (method_exists ($this, 'getTemplate')) {
            $template = $this->getTemplate ();
        } else {
            $template = $this->template;
        }
        preg_match_all ("/(?:^([^{]+)\{)|(?:\}([^{]+)\{)|(?:\}([^{]+)$)/", $template, 
            $htmlMatches, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);
        preg_match_all ("/{([^}]+)}/", $template, $itemMatches,
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

    private $_minimized;
    public function getMinimized () {
        if (!isset ($this->_minimized)) {
            $this->_minimized = self::getJSONProperty (
                $this->profile, 'minimized', $this->widgetType, $this->widgetUID);
        }
        return $this->_minimized;
    }

    public function setMinimized ($minimized) {
        $this->_minimized = $minimized;
    }

    /**
     * Renders widget contents contained in the view file pointed to by the viewFile property. 
     * This gets called if {widgetContents} is contained in the template string.
     */
    public function renderWidgetContents () {
        Yii::app()->clientScript->registerPackages ($this->packages);

        /*
        If it's an ajax request, script must be placed at the end for it to be exectuted upon
        ajax response. Otherwise, place script at POS_BEGIN to allow dependent script files to be
        inserted afterwards.
        */
        Yii::app()->clientScript->registerScript (
            get_called_class ().$this->widgetUID.'Script', $this->setupScript, 
            ($this->isAjaxRequest ? CClientScript::POS_END: CClientScript::POS_BEGIN));

        $minimized = $this->getMinimized ();
        echo "<div id='".get_called_class ()."-widget-content-container-".$this->widgetUID."'".
            ($minimized ? " style='display: none;'" : '').">";

        if ($this->hasError ()) {
            $this->renderErrors ();
        } elseif ($this->viewFile) {
            $this->render (
                'application.components.sortableWidget.views.'.$this->viewFile,
                $this->getViewFileParams ());
        }

        echo "</div>";
    }

    public function renderErrors () {
        Yii::app()->controller->renderPartial (
            'application.components.sortableWidget.views._widgetError', array (
            'errors' => $this->_errors,
        ));
    }

    /**
     * Renders the widget label saved in the profile JSON widget settings property
     * This gets called if {widgetLabel} is contained in the template string.
     */
    public function renderWidgetLabel () {
        $label = $this->getWidgetLabel ();
        echo "<div class='widget-title'>".htmlspecialchars($label)."</div>";
    }

    /**
     * Renders the show/hide settings menu icon as well as the settings menu content 
     */
    public function renderSettingsMenu () {
        $themeUrl = Yii::app()->theme->getBaseUrl();
        $htmlStr = 
            "<a href='#' class='widget-settings-button x2-icon-button' style='display:none;'>";
        $htmlStr .= CHtml::tag(
            'span', 
            array (
                'title' => Yii::t('app', 'Show Widget Settings'),
                'class' => 'fa fa-cog fa-lg'

            ), ' ');
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
        $minimized = self::getJSONProperty (
            $this->profile, 'minimized', $this->widgetType, $this->widgetUID);
        $htmlStr .= CHtml::openTag(
            'span', 
            array (
                'class' => 'fa fa-caret-left fa-lg',
                'title' => Yii::t('app', 'Maximize Widget'),
                'style' => ($minimized ? '': 'display: none;')
            ));

        $htmlStr .= '</span>';
        $htmlStr .= CHtml::openTag(
            'span', 
            array (
                'class' => 'fa fa-caret-down fa-lg',
                'title' => Yii::t('app', 'Minimize Widget'),
                'style' => ($minimized ? 'display: none;' : '')
            ));

        $htmlStr .= '</span>';
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
        echo CHtml::tag('span',
            array (
                'class' => 'fa fa-times fa-lg',
                'title' => Yii::t('app', 'Close Widget')
            ), ' ');
        echo "</a>";
    }

    /**
     * Render the widget container view
     */
    public function run () {
        $hidden = self::getJSONProperty (
            $this->profile, 'hidden', $this->widgetType, $this->widgetUID);
        if ($hidden === null) $hidden = false;
        $this->registerSharedCss ();
        $this->render ('application.components.sortableWidget.views.'.$this->sharedViewFile,
            array (
                'widgetClass' => get_called_class (),
                'profile' => $this->profile,
                'hidden' => $hidden,
                'widgetUID' => $this->widgetUID,
            ));
    }


    /***********************************************************************
    * Non-public instance methods 
    ***********************************************************************/

    /**
     * Override in child class. This content will be turned into a popup dropdown menu with the
     * PopupDropdownMenu JS prototype.
     */
    protected function getSettingsMenuContent () {
        $htmlStr = 
            '<div class="widget-settings-menu-content" style="display:none;">
                <ul>'. 
                    $this->getSettingsMenuContentEntries ().
                '</ul>
            </div>';
        $htmlStr .= $this->getSettingsMenuContentDialogs ();
        return $htmlStr;
    }


    /**
     * @return string HTML string containing settings menu options
     */
    protected function getSettingsMenuContentEntries () {
        return ($this->relabelingEnabled ? 
            '<li class="relabel-widget-button">'.
                X2Html::fa('fa-edit').
                Yii::t('app', 'Rename Widget').
            '</li>' : '').
        ($this->canBeDeleted ? 
            '<li class="delete-widget-button">'.
                X2Html::fa('fa-trash').
                Yii::t('app', 'Delete Widget').
            '</li>' : '');
    }

    /**
     * @return string HTML string containing dialog containers used by settings menu options
     */
    protected function getSettingsMenuContentDialogs () {
        $htmlStr = '';
        if ($this->relabelingEnabled) {
            $htmlStr .= 
                '<div id="relabel-widget-dialog-'.$this->widgetUID.'" style="display: none;">
                    <div>'.Yii::t('app', 'Enter a new name:').'</div>  
                    <input class="new-widget-name">
                </div>';
        }
        if ($this->canBeDeleted) {
            $htmlStr .= 
                '<div id="delete-widget-dialog-'.$this->widgetUID.'" style="display: none;">
                    <div>'.
                        Yii::t('app', 'Performing this action will cause this widget\'s settings '.
                            'to be lost. This action cannot be undone.').
                    '</div>  
                </div>';
        }
        return $htmlStr;
    }

    /**
     * Returns paths of shared css files relative to themes/x2engine/css. 
     */
    protected $_sharedCssFileNames;
    public function getSharedCssFileNames () {
        if (!isset ($this->_sharedCssFileNames)) {
            $this->_sharedCssFileNames = array (
                'components/sortableWidget/SortableWidget.css',
            );
        }
        return $this->_sharedCssFileNames;
    }

    /**
     * Magic getter. Returns this widget's shared css
     * @return array key is the proposed name of the css string which should be passed as the first
     *  argument to yii's registerCss. The value is the css string.
     */
    protected function getSharedCss () {
        if (!isset ($this->_sharedCss)) {
            $this->_sharedCss = array ();
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

    /**
     * @return array translations to pass to JS objects 
     */
    protected function getTranslations () {
        if (!isset ($this->_translations )) {
            $this->_translations = array ();
            if ($this->relabelingEnabled) {
                $this->_translations = array_merge ($this->_translations, array (
                    'Rename Widget' => Yii::t('app', 'Rename Widget'),
                    'Cancel' => Yii::t('app', 'Cancel'),
                    'Rename' => Yii::t('app', 'Rename'),
                ));
            }
            if ($this->canBeDeleted) {
                $this->_translations = array_merge ($this->_translations, array (
                    'Cancel' => Yii::t('app', 'Cancel'),
                    'Delete' => Yii::t('app', 'Delete'),
                    'Are you sure you want to delete this widget?' => 
                        Yii::t('app', 'Are you sure you want to delete this widget?'),
                ));
            }
        }
        return $this->_translations;
    }

    protected function getJSSortableWidgetParams () {
        if (!isset ($this->_JSSortableWidgetParams)) {
            $this->_JSSortableWidgetParams = array (
                'widgetClass'  => get_called_class (),
                'setPropertyUrl' => Yii::app()->controller->createUrl (
                    '/profile/setWidgetSetting'),
                'cssSelectorPrefix' => $this->widgetType,
                'widgetType' => $this->widgetType,
                'widgetUID' => $this->widgetUID,
                'translations' => $this->getTranslations (),
                'deleteWidgetUrl' =>  Yii::app()->controller->createUrl (
                    '/profile/deleteSortableWidget'),
                'hasError' => $this->hasError ()
            );
        }
        return $this->_JSSortableWidgetParams;
    }

    public function init () {
        if (!isset ($this->namespace))
            $this->namespace = $this->getWidgetKey ();
        parent::init ();
    }

}
?>
