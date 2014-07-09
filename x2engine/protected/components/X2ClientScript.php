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

Yii::import('application.extensions.NLSClientScript');

/**
 * Custom extension of CClientScript used by the app.
 *
 * @property bool $fullscreen Whether to render in full screen mode
 * @package application.components 
 */
class X2ClientScript extends NLSClientScript {

    private $_admin;
    private $_baseUrl;
    private $_fullscreen;
    private $_isGuest;
    private $_profile;
    private $_scriptUrl;
    private $_themeUrl;
    private $_cacheBuster;
    public $packages;

    public function getPackages () {
        if (!isset ($this->packages)) {
            $this->packages = array (
                'auxlib' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/auxlib.js',
                    ),
                ),
            );
        }
        return $this->packages;
    }

    /**
     * @param string returns cache buster value. Append this value to names of files upon 
     *  registration to avoid retrieving the cached file.
     */
    private function getCacheBuster() {
        if (!isset ($this->_cacheBuster)) {
            if (YII_DEBUG) {
                /*
                Cache is refreshed once per session for debugging. It shouldn't be refreshed 
                every page load or it will cause issues with NLSClientScript.
                */
                if (!isset ($_SESSION['cacheBuster'])) {
                    $_SESSION['cacheBuster'] = ((string) time ());
                }
                // always bust caches in debug mode
                $this->_cacheBuster = $_SESSION['cacheBuster'];
            } else {
                // bust cache on update/upgrade
                $this->_cacheBuster = Yii::app()->params->buildDate;
            }
        }
        return $this->_cacheBuster;
    }
    
    /**
     * Inserts the scripts at the beginning of the body section.
     * @param boolean $includeScriptFiles whether to include external files, or just dynamic scripts
     * @return string the output to be inserted with scripts.
     */
    public function renderOnRequest($includeScriptFiles = false) {
        $html='';
        if($includeScriptFiles) {
            foreach($this->scriptFiles as $scriptFiles) {
                foreach($scriptFiles as $scriptFile)
                    $html.=CHtml::scriptFile($scriptFile)."\n";
            }
        }
        foreach($this->scripts as $script)    // the good stuff!
            $html.=CHtml::script(implode("\n",$script))."\n";

        if($html!=='')
            return $html;
    }

    /**
     * Echoes out registered scripts and the necessary JavaScript to load
     * all prerequisite script files.
     *
     * Useful for loading UI elements via AJAX that require registering scripts.
     */
    public function echoScripts(){
        $cs = $this;
        $scripts = '';
        $endScripts = '';
        foreach($cs->cssFiles as $url => $type){
            $scripts .= '
                if($("head link[href=\''.$url.'\']").length == 0) {
                    $.ajax({type:"GET",url:"'.$url.'"}).done(function(response) {
                        $(\'<link rel="stylesheet" type="text/css" href="'.$url.'">\').
                            appendTo("head");
                    });
                }';
        }
        foreach($cs->scriptFiles as $position => $scriptFiles){
            foreach($scriptFiles as $key => $url){
                $scripts .= '
                    $.ajax({
                        type:"GET",
                        dataType:"script",
                        url:"'.$url.'"
                    }).always(function(){';
                $endScripts .= '})';
            }
        }
        if(array_key_exists(CCLientScript::POS_READY, Yii::app()->clientScript->scripts)){
            foreach(Yii::app()->clientScript->scripts[CClientScript::POS_READY] as $id => $script){
                if(strpos($id, 'logo') === false)
                    $scripts .= "$script\n";
            }
        }

        echo $scripts.$endScripts.';';
    }

    /**
     * Registers a set of packages at the specified position
     * @param Array $packages 
     * @param Integer $position 
     * @param bool $useCorePackages 
     */
    public function registerPackages ($packages, $position=null) {
        if ($position === null) {
            $position = CClientScript::POS_END;
        }
        $oldPackages = Yii::app()->clientScript->packages;
        Yii::app()->clientScript->packages = array_merge ($this->getPackages (), $packages);
        $oldCoreScriptPosition = Yii::app()->clientScript->coreScriptPosition;
        Yii::app()->clientScript->coreScriptPosition = $position;
        foreach (array_keys ($packages) as $packageName) {
            Yii::app()->clientScript->registerPackage ($packageName);
        }
        Yii::app()->clientScript->coreScriptPosition = $oldCoreScriptPosition;
        Yii::app()->clientScript->packages = $oldPackages;
    }

    public function getCurrencyConfigScript () {
        // Declare currency format(s) from Yii for the jQuery maskMoney plugin
        $locale = Yii::app()->locale;

        $decSym = $locale->getNumberSymbol('decimal');
        $grpSym = $locale->getNumberSymbol('group');
        $curSym = Yii::app()->getLocale()->getCurrencySymbol(Yii::app()->params['currency']); 

        // Declare:
        $cldScript = 
            '(function($) {
                x2.currencyInfo = '.CJSON::encode(array(
                    'prefix' => isset($curSym)? $curSym : "",
                    'decimal' => $decSym,
                    'thousands' => $grpSym,
                )).";
            })(jQuery);";

        return $cldScript;
    }

    /**
     * Returns a cache busting url suffix to be appended to JS/CSS files before registration
     * Checks for presence of query string to determine the appropriate separator between the 
     * url and the cache buster string.
     * @return string suffix
     */
    public function getCacheBusterSuffix ($url=null) {
        $cacheBuster = $this->getCacheBuster ();
        if ($url === null) {
            return '?'.$cacheBuster;
        } else if (preg_match ("/\?/", $url)) {
            return '&'.$cacheBuster;
        } else {
            return '?'.$cacheBuster;
        }
    }

    /**
     * Allows css containing media queries to be added conditionally 
     */
    public function registerResponsiveCssFile ($url, $media='') {
        if (RESPONSIVE_LAYOUT) {
            $this->registerCssFile (
                $url.$this->getCacheBusterSuffix ($url), $media);
        }
    }

    /**
     * Allows css containing media queries to be added conditionally 
     */
    public function registerResponsiveCss ($id, $css, $media='') {
        if (RESPONSIVE_LAYOUT) {
            $this->registerCss ($id, $css, $media);
        }
    }

    /**
     * Overrides parent method to add cache buster parameter 
     */
    public function registerScriptFile ($url, $position=null, array $htmlOptions=array()) {
        return parent::registerScriptFile (
            $url.$this->getCacheBusterSuffix ($url), $position,
            $htmlOptions);
    }

	/**
	 * Overrides parent method to add cache busting suffix to package files
	 */
	public function renderCoreScripts()
	{
		if($this->coreScripts===null)
			return;
		$cssFiles=array();
		$jsFiles=array();
		foreach($this->coreScripts as $name=>$package)
		{
			$baseUrl=$this->getPackageBaseUrl($name);
			if(!empty($package['js']))
			{
                /* x2modstart */ 
				foreach($package['js'] as $js)
					$jsFiles[$baseUrl.'/'.$js.$this->getCacheBusterSuffix ($js)]=$baseUrl.'/'.$js;
                /* x2modend */ 
			}
			if(!empty($package['css']))
			{
                /* x2modstart */ 
				foreach($package['css'] as $css)
					$cssFiles[$baseUrl.'/'.$css.$this->getCacheBusterSuffix ($css)]='';
                /* x2modend */ 
			}
		}
		// merge in place
		if($cssFiles!==array())
		{
			foreach($this->cssFiles as $cssFile=>$media)
				$cssFiles[$cssFile]=$media;
			$this->cssFiles=$cssFiles;
		}
		if($jsFiles!==array())
		{
			if(isset($this->scriptFiles[$this->coreScriptPosition]))
			{
				foreach($this->scriptFiles[$this->coreScriptPosition] as $url => $value)
					$jsFiles[$url]=$value;
			}
			$this->scriptFiles[$this->coreScriptPosition]=$jsFiles;
		}
	}

    /**
     * Overrides parent method to add cache buster parameter 
     */
    public function registerCssFile ($url, $media='') {
        return parent::registerCssFile (
            $url.$this->getCacheBusterSuffix ($url), $media);
    }

    /**
     * Registers a set of css files using cache busting.
     * For ie < 10, files are imported using css import statements within style tags. This is done
     * to get around the 31 stylesheet limit in ie 6-9.
     * @param string id CSS script unique id
     * @param array $filenames array of filename strings
     * @param bool if true, theme url + '/css/' will be prepended to each filename
     */
    public function registerCssFiles ($id, array $filenames, $prependThemeUrl=true, $media='') {
        $cssUrl = '';
        if ($prependThemeUrl) {
            $cssUrl = $this->getThemeUrl ().'/css/';
        }
        $ieVer = Auxlib::getIEVer ();
        if ($ieVer < 10) {
            $cacheBuster = $this->getCacheBuster ();
            $cssStr = '';
            foreach ($filenames as $file) {
                $cssStr .= '@import url("'.$cssUrl.$file.'?'.$cacheBuster.'");'."\n";
            }
            $this->registerCss ($id, $cssStr, $media);
        } else {
            foreach ($filenames as $file) {
                $this->registerCssFile ($cssUrl.$file, $media);
            }
        }
    }

    /**
     * Registers css for responsive title bar. Since title bar logo width can change, the
     * media query that determines the appearance of the title bar must be set in accordance
     * with the width of the currently uploaded logo.
     */
    private function registerResponsiveTitleBarCss () {
        $logo = Media::model()
            ->findByAttributes(array('associationId' => 1, 'associationType' => 'logo'));

        if (isset ($logo)) {
            $dimensions = CJSON::decode ($logo->resolveDimensions ());
            if (is_array ($dimensions)) {
                $imgWidth = floor ($dimensions['width'] * (30 / $dimensions['height']));
                Yii::app()->clientScript->registerScript('logoWidthScript',"
                if (typeof x2 === 'undefined') x2 = {};
                x2.logoWidth = ".$imgWidth.";
                ", CClientScript::POS_HEAD);
            }
        }

        if (isset ($imgWidth)) {
            $threshold = 915 + $imgWidth;
        } else {
            $threshold = 915;
        }

        Yii::app()->clientScript->registerResponsiveCss('responsiveTitleBar',"
        /*
        Step between full title bar and mobile title bar. Search bar minimizes and expands to make
        room for user menu links
        */
        @media (max-width: ".$threshold."px) {
            #search-bar-box {
                display: none;
                width: 180px;
            }
            #search-bar button.x2-button {
                border-radius: 3px 3px 3px 3px;
                -moz-border-radius: 3px 3px 3px 3px;
                -webkit-border-radius: 3px 3px 3px 3px;
                -o-border-radius: 3px 3px 3px 3px;
            }
        }

        @media (min-width: ".$threshold."px) {
            #user-menu > li {
                display: block !important;
            }
            #search-bar-box {
                display: block !important;
            }
        }
        ");

    }

    /**
     * Registers a set of css files which are used for all pages with the main layout. 
     */
    private function registerCombinedCss () {
        $ieVer = Auxlib::getIEVer ();
        $cssUrl = $this->getThemeUrl ().'/css';

        $cssFiles = array (
            'screen.css',
            'auxlib.css',
            'jquery-ui.css',
            'dragtable.css',
            'main.css',
            'ui-elements.css',
            'layout.css',
            'details.css',
            'x2forms.css',
            'form.css',
            'publisher.css',
            'sortableWidgets.css',
            '../../../js/bgrins-spectrum-2c2010c/spectrum.css',
            '../../../js/qtip/jquery.qtip.min.css',
            '../../../js/checklistDropdown/jquery.multiselect.css',
            'rating/jquery.rating.css',
        );

        $responsiveCssFiles = array (
            'responsiveLayout.css',
            'responsiveUIElements.css',
            'responsiveX2Forms.css',
        );

        $this->registerResponsiveTitleBarCss ();

        $this->registerCssFiles ('combinedCss', $cssFiles, 'screen, projection');

        if (RESPONSIVE_LAYOUT) {
            $this->registerCssFiles ('responsiveCombinedCss', 
                $responsiveCssFiles, 'screen, projection');
        }
    }

    /**
     * Instantiates the Flashes utility class 
     */
    public function registerX2Flashes () {
        $this->registerScriptFile($this->baseUrl.'/js/X2Flashes.js', CClientScript::POS_END);
        $this->registerScript ('registerX2Flashes', "
        (function () {
            x2.flashes = new x2.Flashes ({
                containerSelector: 'x2-flashes-container',
                expandWidgetSrc: '".Yii::app()->getTheme()->getBaseUrl().
                    '/images/icons/Expand_Widget.png'."',
                collapseWidgetSrc: '".Yii::app()->getTheme()->getBaseUrl().
                    '/images/icons/Collapse_Widget.png'."',
                closeWidgetSrc: '".Yii::app()->getTheme()->getBaseUrl().
                    '/images/icons/Close_Widget.png'."',
                translations: ".CJSON::encode (array (
                    'noticeFlashList' => Yii::t('app', 'Action exectuted with'),
                    'errorFlashList' => Yii::t('app', 'Action exectuted with'),
                    'noticeItemName' => Yii::t('app', 'warnings'),
                    'errorItemName' => Yii::t('app', 'errors'),
                    'successItemName' => Yii::t('app', 'Close'),
                    'close' => Yii::t('app', 'Close'),
                ))."
            });
        }) ();
        ", CClientScript::POS_READY);
    }

    private function registerX2ModelMappingsScript () {
        $this->registerScript('x2ModelMappingsScript',"
            x2.associationModels = ".CJSON::encode (X2Model::$associationModels).";
            x2.modelNameToModuleName = ".CJSON::encode (X2Model::$modelNameToModuleName).";
        ", CClientScript::POS_READY);
    }


    /**
     * Instantiates the x2.Forms utitility class
     */
    private function registerX2Forms () {
        $this->registerScriptFile($this->baseUrl.'/js/X2Forms.js');
        $this->registerScript('registerX2Forms',"
            x2.forms = new x2.Forms ({
                translations: ".CJSON::encode (array (
                    'Check All' => Yii::t('app', 'Check All'),
                    'Uncheck All' => Yii::t('app', 'Uncheck All'),
                    'selected' => Yii::t('app', 'selected'),
                ))."
            });
        ", CClientScript::POS_END);
    }

    /**
     * Passes locale-specific date format strings to JS. 
     */
    private function registerDateFormats () {
        $this->registerScript('registerDateFormats',"
            x2.dateFormats = {
                dateFormat: '".Formatter::formatDatePicker()."',
                timeFormat: '".Formatter::formatTimePicker()."',
                ampm: '".Formatter::formatAMPM()."'
            };
        ", CClientScript::POS_END);
    }

    /**
     * Performs all the necessary JavaScript/CSS initializations for most parts of the app.
     */
    public function registerMain(){
        foreach(array('IS_IPAD','RESPONSIVE_LAYOUT') as $layoutConst) {
            defined($layoutConst) or define($layoutConst,false);
        }

        $cs = $this;
        $fullscreen = $this->fullscreen;
        $profile = $this->profile;
        
        $baseUrl = $this->baseUrl;
        $themeUrl = $this->themeUrl;
        $scriptUrl = $this->scriptUrl;
        $admin = $this->admin;
        $isGuest = $this->isGuest;

        // jQuery and jQuery UI libraries
        $cs->registerCoreScript('jquery')
           ->registerCoreScript('jquery.ui');

        $cldScript = $this->getCurrencyConfigScript ();

        AuxLib::registerPassVarsToClientScriptScript('auxlib', array(
            'saveMiscLayoutSettingUrl' =>
            "'".addslashes(Yii::app()->createUrl('/profile/saveMiscLayoutSetting'))."'"
                ), 'passAuxLibVars'
        );

        $cs->registerX2ModelMappingsScript ();
        $cs->registerX2Forms ();
        $cs->registerDateFormats ();

        // custom scripts
        $cs->registerScriptFile($baseUrl.'/js/json2.js')
                ->registerScriptFile($baseUrl.'/js/webtoolkit.sha256.js')
                ->registerScriptFile($baseUrl.'/js/main.js', CCLientScript::POS_HEAD)
                ->registerScriptFile($baseUrl.'/js/auxlib.js', CClientScript::POS_HEAD)
                ->registerScriptFile($baseUrl.'/js/IframeFixOverlay.js', CClientScript::POS_HEAD)
                ->registerScriptFile($baseUrl.'/js/LayoutManager.js')
                //->registerScriptFile($baseUrl.'/js/X2Select.js')
                ->registerScriptFile($baseUrl.'/js/media.js')
                ->registerScript('formatCurrency-locales', $cldScript, CCLientScript::POS_HEAD)
                ->registerScriptFile($baseUrl.'/js/modernizr.custom.66175.js')
                ->registerScriptFile($baseUrl.'/js/widgets.js')
                ->registerScriptFile($baseUrl.'/js/qtip/jquery.qtip.min.js')
                ->registerScriptFile($baseUrl.'/js/ActionFrames.js')
                ->registerScriptFile($baseUrl.'/js/bgrins-spectrum-2c2010c/spectrum.js')
                ->registerScriptFile($baseUrl.'/js/ColorPicker.js', CCLientScript::POS_END)
                ->registerScriptFile($baseUrl.'/js/PopupDropdownMenu.js', CCLientScript::POS_END)
                ->registerScriptFile($baseUrl.'/js/checklistDropdown/jquery.multiselect.js');

        if(IS_IPAD){
            $cs->registerScriptFile($baseUrl.'/js/jquery.mobile.custom.js');
        }
        $this->registerInitScript ();

        if(Yii::app()->session['translate'])
            $cs->registerScriptFile($baseUrl.'/js/translator.js');

        $cs->registerScriptFile($baseUrl.'/js/backgroundFade.js');
        $cs->registerScript('datepickerLanguage', "
            $.datepicker.setDefaults( $.datepicker.regional[ '' ] );
        ");
        $mmPath = Yii::getPathOfAlias('application.extensions.moneymask.assets');
        $aMmPath = Yii::app()->getAssetManager()->publish($mmPath);
        $cs->registerScriptFile("$aMmPath/jquery.maskMoney.js");
        $cs->registerCssFile($baseUrl.'/css/normalize.css', 'all')
            ->registerCssFile($themeUrl.'/css/print.css', 'print')
            ->registerCoreScript('cookie');
        $this->registerCombinedCss ();
        if(!RESPONSIVE_LAYOUT && IS_ANDROID) {
            $cs->registerCssFile(
                $themeUrl.'/css/androidLayout.css', 'screen, projection');
        } elseif (IS_IPAD) {
            $cs->registerCssFile($themeUrl.'/css/ipadLayout.css', 'screen, projection');
        }

        $cs->registerScript('fullscreenToggle', '
            window.enableFullWidth = '.(!Yii::app()->user->isGuest ? 
                ($profile->enableFullWidth ? 'true' : 'false') : 'true').';
            window.fullscreen = '.($fullscreen ? 'true' : 'false').';
        ', CClientScript::POS_HEAD);

        if(is_object(Yii::app()->controller->module)){
            $cs->registerScript('saveCurrModule', "
                x2.currModule = '".Yii::app()->controller->module->name."';
            ", CClientScript::POS_HEAD);
        }

        if(!$isGuest){
            $cs->registerScript('notificationsParams', "
                x2.notifications = new x2.Notifs ({
                    disablePopup: ".($profile->disableNotifPopup ? 'true' : 'false').",
                    translations: {
                        clearAll:
                            '".addslashes(Yii::t('app', 'Permanently delete all notifications?'))."'
                    }
                });
            ", CClientScript::POS_READY);
            $cs->registerScriptFile($baseUrl.'/js/jstorage.min.js')
               ->registerScriptFile(
                $baseUrl.'/js/notifications.js', CClientScript::POS_BEGIN);
        }

        if(!$isGuest && ($profile->language == 'he' || $profile->language == 'fa'))
            $cs->registerCss('rtl-language', 'body{text-align:right;}');

        $cs->registerCoreScript('rating');
    }

    public function getAdmin() {
        if(!isset($this->_admin)) {
            $this->_admin = Yii::app()->settings;
        }
        return $this->_admin;
    }

    public function setAdmin(Admin $value) {
        $this->_admin = $value;
    }

    public function getBaseUrl(){
        if(!isset($this->_baseUrl)){
            $this->_baseUrl = Yii::app()->baseUrl;
        }
        return $this->_baseUrl;
    }

    public function setBaseUrl($value){
        $this->_baseUrl = $value;
    }

    public function getFullscreen() {
        if(!isset($this->_fullscreen)) {
            $this->_fullscreen = Yii::app()->user->isGuest || $this->profile->fullscreen;
        }
        return $this->_fullscreen;
    }

    public function setFullscreen($value) {
        $this->_fullscreen = $value;
    }

    public function getIsGuest() {
        if(!isset($this->_isGuest)) {
            $this->_isGuest = Yii::app()->user->isGuest;
        }
        return $this->_isGuest;
    }
    public function setIsGuest($value) {
        $this->_isGuest = $value;
    }

    public function getProfile() {
        if(!isset($this->_profile)) {
            $this->_profile = Yii::app()->params->profile;
        }
        return $this->_profile;

    }

    public function setProfile(Profile $value) {
        $this->_profile = $value;
    }

    public function getScriptUrl() {
        if(!isset($this->_scriptUrl)) {
            $this->_scriptUrl = Yii::app()->request->scriptUrl;
        }
        return $this->_scriptUrl;
    }

    public function setScriptUrl( $value) {
        $this->_scriptUrl = $value;
    }

    public function getThemeUrl() {
        if(!isset($this->_themeUrl)) {
            $this->_themeUrl = Yii::app()->theme->baseUrl;
        }
        return $this->_themeUrl;
    }
    public function setThemeUrl($value) {
        $this->_themeUrl = $value;
    }

    private function registerInitScript () {
        Yii::app()->clientScript->registerScript ('X2ClientScriptInitScript',"
            (function () {
                var actionFramesName = 'actionFrames';
                x2[actionFramesName] = new x2.ActionFrames ({ 
                    instanceName: actionFramesName,
                    deleteActionUrl: '".
                        Yii::app()->controller->createUrl ('/actions/actions/delete')."'
                });
            }) ();
        ", CClientScript::POS_HEAD);
    }


}
