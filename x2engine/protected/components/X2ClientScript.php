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




Yii::import('application.extensions.NLSClientScript');

/**
 * Custom extension of CClientScript used by the app.
 *
 * @property bool $fullscreen Whether to render in full screen mode
 * @package application.components 
 */
class X2ClientScript extends NLSClientScript {

    public $useAbsolutePaths = false;
    private $_admin;
    private $_baseUrl;
    private $_fullscreen;
    private $_isGuest;
    private $_profile;
    private $_scriptUrl;
    private $_themeUrl;
    private $_cacheBuster;
    private $_defaultPackages;

    public function getDefaultPackages () {
        if (!isset ($this->_defaultPackages)) {
            $this->_defaultPackages = array_merge (
                $this->getIEDefaultPackages(), 
                array(
                    'auxlib' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array(
                            'js/auxlib.js'
                        )
                    )
                )
            );
        }
        return $this->_defaultPackages;
    }

    public function getIEDefaultPackages() {
        if (AuxLib::getIEVer() >= 9)  {
            return array();
        }
        return array ();
//        return array(
//            'aight' => array(
//                'baseUrl' => Yii::app()->request->baseUrl,
//                'js' => array(
//                    'js/lib/aight/aight.js',
//                ),
//                'depends' => array('jquery'),
//            ),
//        );     
    }

    /**
     * @param string returns cache buster value. Append this value to names of files upon 
     *  registration to avoid retrieving the cached file.
     */
    public function getCacheBuster() {
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
    public function echoScripts($captureScripts = false){
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
        if($captureScripts){
            return $scripts.$endScripts.';';
        }
        echo $scripts.$endScripts.';';
    }

    /**
     * Registers a set of packages
     * @param Array $packages 
     * @param bool $useDefaultPackages 
     */
    public function registerPackages ($packages, $useDefaultPackages=false) {
        $oldPackages = Yii::app()->clientScript->packages;
        if ($useDefaultPackages) {
            Yii::app()->clientScript->packages = array_merge (
                $this->getDefaultPackages (), $packages);
        } else {
            Yii::app()->clientScript->packages = $packages;
        }
        
        foreach (array_keys ($packages) as $packageName) {
            Yii::app()->clientScript->registerPackage ($packageName);
        }
        Yii::app()->clientScript->packages = $oldPackages;
    }

    /**
     * Modified Function from CClientScript 
     * to add custom packages
     * @see CClientScript::registerCoreScript
     */
    public function registerCoreScript($name)
    {
        if(isset($this->coreScripts[$name]))
            return $this;
        if(isset($this->packages[$name]))
            $package=$this->packages[$name];
        else
        {
            if($this->corePackages===null) {
                $this->corePackages=require(YII_PATH.'/web/js/packages.php');
                /* x2modstart */
                $this->corePackages = array_merge(
                    $this->corePackages, 
                    require(implode (DIRECTORY_SEPARATOR, array (
                        Yii::app()->basePath,
                        'data/packages.php'
                    ))));
                /* x2modend */
            }
            if(isset($this->corePackages[$name]))
                $package=$this->corePackages[$name];
        }
        if(isset($package))
        {
            if(!empty($package['depends']))
            {
                foreach($package['depends'] as $p)
                    $this->registerCoreScript($p);
            }
            $this->coreScripts[$name]=$package;
            $this->hasScripts=true;
            $params=func_get_args();
            $this->recordCachingAction('clientScript','registerCoreScript',$params);
        }
        elseif(YII_DEBUG)
            throw new CException('There is no CClientScript package: '.$name);
        else
            Yii::log('There is no CClientScript package: '.$name,CLogger::LEVEL_WARNING,'system.web.CClientScript');

        return $this;
    }

    public function getCurrencyConfigScript () {
        // Declare currency format(s) from Yii for the jQuery maskMoney plugin
        $locale = Yii::app()->locale;

        $decSym = $locale->getNumberSymbol('decimal');
        $grpSym = $locale->getNumberSymbol('group');
        $curSym = Yii::app()->getLocale()->getCurrencySymbol(Yii::app()->params['currency']); 

        // Declare:
        $cldScript = 
            ';(function($) {
                x2.currencyInfo = '.CJSON::encode(array(
                    'prefix' => isset($curSym)? $curSym : Yii::app()->params['currency'],
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
        if (AuxLib::getLayoutType () === 'responsive') {
            $this->registerCssFile (
                $url.$this->getCacheBusterSuffix ($url), $media);
        }
    }

    /**
     * Allows css containing media queries to be added conditionally 
     */
    public function registerResponsiveCss ($id, $css, $media='') {
        if (AuxLib::getLayoutType () === 'responsive') {
            $this->registerCss ($id, $css, $media);
        }
    }

    public function makeUrlAbsolute ($url) {
        $hostInfo = Yii::app()->request->getHostInfo ();
// phonegap testing
//        if (YII_DEBUG && Yii::app()->params->isPhoneGap &&
//            preg_match ('/^http:\/\/mobile/', $absoluteBaseUrl)) {
//
//            $absoluteBaseUrl = 'http://mobile';
//        }
        if (!preg_match ('/^https?:\/\//', $url) && 
            !preg_match ('/^'.preg_quote ($hostInfo, '/').'/', $url)) {

            $url = $hostInfo.$url;
        }
        return $url;
    }

    /**
     * Overrides parent method to add cache buster parameter and substitute the asset
     * domain if requested
     */
    public function registerScriptFile ($url, $position=null, array $htmlOptions=array()) {
        if ($this->useAbsolutePaths) $url = $this->makeUrlAbsolute ($url);
        $url = $this->getJSPathname ($url);
        if (X2AssetManager::$enableAssetDomains)
            $url = Yii::app()->assetManager->substituteAssetDomain ($url);
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
            if ($this->useAbsolutePaths) $baseUrl = $this->makeUrlAbsolute ($baseUrl);
			if(!empty($package['js']))
			{
				/* x2modstart */
				foreach($package['js'] as $js) {
					if (X2AssetManager::$enableAssetDomains)
						$baseUrl = Yii::app()->assetManager->substituteAssetDomain ($this->getPackageBaseUrl($name));
					$jsFiles[$baseUrl.'/'.$js.$this->getCacheBusterSuffix ($js)]=$baseUrl.'/'.$js;
				}
				/* x2modend */
			}
			if(!empty($package['css']))
			{
				/* x2modstart */
				foreach($package['css'] as $css) {
					if (X2AssetManager::$enableAssetDomains && !in_array($css, X2AssetManager::$skipAssets))
						$baseUrl = Yii::app()->assetManager->substituteAssetDomain ($this->getPackageBaseUrl($name));
					$cssFiles[$baseUrl.'/'.$css.$this->getCacheBusterSuffix ($css)]='';
				}
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
     * Used by X2Touch to return JS resources during ajax requests
     * @return string rendered JS in all positions 
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
     */
    public function renderX2TouchAssets () {
		$html='';
        $fullPage = 1;

        // adds core scripts to scriptFiles array
        $this->renderCoreScripts ();

//		foreach($this->linkTags as $link)
//			$html.=CHtml::linkTag(null,null,null,null,$link)."\n";
//
//        foreach($this->cssFiles as $url=>$media)
//            $html.=CHtml::cssFile($url,$media)."\n";
//
//        foreach($this->css as $css) {
//            $text = $css[0];
//            $media = $css[1];
//
//            if (is_array ($text) && isset ($text['text']) && isset ($text['htmlOptions'])) {
//                // special case for css registered with html options
//                $html.=X2Html::css ($text['text'], $media, $text['htmlOptions']);
//                continue;
//            }
//            $html.=CHtml::css($text, $media)."\n";
//        }

        if(isset($this->scriptFiles[self::POS_HEAD]))
        {
            foreach($this->scriptFiles[self::POS_HEAD] as $scriptFileValueUrl=>$scriptFileValue)
            {
                if(is_array($scriptFileValue))
                    $html.=CHtml::scriptFile($scriptFileValueUrl,$scriptFileValue)."\n";
                else
                    $html.=CHtml::scriptFile($scriptFileValueUrl)."\n";
            }
        }

        if(isset($this->scripts[self::POS_HEAD]))
            $html.=$this->renderScriptBatch($this->scripts[self::POS_HEAD]);

		if(isset($this->scriptFiles[self::POS_BEGIN]))
		{
			foreach($this->scriptFiles[self::POS_BEGIN] as $scriptFileUrl=>$scriptFileValue)
			{
				if(is_array($scriptFileValue))
					$html.=CHtml::scriptFile($scriptFileUrl,$scriptFileValue)."\n";
				else
					$html.=CHtml::scriptFile($scriptFileUrl)."\n";
			}
		}
		if(isset($this->scripts[self::POS_BEGIN]))
			$html.=$this->renderScriptBatch($this->scripts[self::POS_BEGIN]);

		if(isset($this->scriptFiles[self::POS_END]))
		{
			foreach($this->scriptFiles[self::POS_END] as $scriptFileUrl=>$scriptFileValue)
			{
				if(is_array($scriptFileValue))
					$html.=CHtml::scriptFile($scriptFileUrl,$scriptFileValue)."\n";
				else
					$html.=CHtml::scriptFile($scriptFileUrl)."\n";
			}
		}
		$scripts=isset($this->scripts[self::POS_END]) ? $this->scripts[self::POS_END] : array();
		if(isset($this->scripts[self::POS_READY]))
		{
			if($fullPage)
				$scripts[]="jQuery(function($) {\n".implode("\n",$this->scripts[self::POS_READY])."\n});";
			else
				$scripts[]=implode("\n",$this->scripts[self::POS_READY]);
		}
		if(isset($this->scripts[self::POS_LOAD]))
		{
			if($fullPage)
				$scripts[]="jQuery(window).on('load',function() {\n".implode("\n",$this->scripts[self::POS_LOAD])."\n});";
			else
				$scripts[]=implode("\n",$this->scripts[self::POS_LOAD]);
		}
		if(!empty($scripts))
			$html.=$this->renderScriptBatch($scripts);

        return $html;
    }

	/**
	 * Inserts the scripts in the head section.
	 * @param string $output the output to be inserted with scripts.
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
	 */
	public function renderHead(&$output)
	{
        parent::renderHead ($output);
		$html='';
		foreach($this->metaTags as $meta)
			$html.=CHtml::metaTag($meta['content'],null,null,$meta)."\n";
		foreach($this->linkTags as $link)
			$html.=CHtml::linkTag(null,null,null,null,$link)."\n";
        /* x2modstart */ 
        if (Auxlib::getIEVer () < 10) { 
            // group registered css files using import statements
            $mergedCss = '';
            $mediaType = null;
            foreach ($this->cssFiles as $url => $media) {
                if ($mediaType === null) { 
                    $mediaType = $media;
                }
                $text = '@import url("'.$url.'");';
                if ($media !== $mediaType) {
                    $html .= CHtml::css($mergedCss,$mediaType)."\n";
                    $mergedCss = '';
                    $mediaType = $media;
                } 
                $mergedCss .= "\n".$text;
            }
            if ($mergedCss)
                $html .= CHtml::css($mergedCss,$mediaType)."\n";
        } else {
            foreach($this->cssFiles as $url=>$media)
                $html.=CHtml::cssFile($url,$media)."\n";
        }
        if (Auxlib::getIEVer () < 10) { 
            // merge inline css
            $mergedCss = '';
            $mediaType = null;
            foreach ($this->css as $css) {
                $text = $css[0];
                $media = $css[1];
                if (is_array ($text) && isset ($text['text'])) {
                    $text = $text['text'];
                }
                if ($mediaType === null) { 
                    $mediaType = $media;
                }

                if (preg_match ('/@import/', $text)) {
                    if ($mergedCss)
                        $html .= CHtml::css($mergedCss,$mediaType)."\n";
                    $mergedCss = '';
                    $mediaType = null;
                    $html .= CHtml::css($text,$media)."\n";
                    continue;
                }

                if ($media !== $mediaType) {
                    $html .= CHtml::css($mergedCss,$mediaType)."\n";
                    $mergedCss = '';
                    $mediaType = $media;
                }
                $mergedCss .= "\n".$text;
            }
            if ($mergedCss)
                $html .= CHtml::css($mergedCss,$mediaType)."\n";
        } else {
            foreach($this->css as $css) {
                $text = $css[0];
                $media = $css[1];

                if (is_array ($text) && isset ($text['text']) && isset ($text['htmlOptions'])) {
                    // special case for css registered with html options
                    $html.=X2Html::css ($text['text'], $media, $text['htmlOptions']);
                    continue;
                }
                $html.=CHtml::css($text, $media)."\n";
            }
        }

        // prevent global css from being applied if this is an admin or guest request
        if (!(Yii::app()->controller instanceof AdminController) && 
            !Yii::app()->user->isGuest && 
            !Yii::app()->params->isMobileApp) {

            $globalCssUrl = GlobalCSSFormModel::getGlobalCssUrl ();
            $html.=CHtml::cssFile($globalCssUrl.$this->getCacheBusterSuffix ($globalCssUrl))."\n";
        }
/* x2modend */ 
		if($this->enableJavaScript)
		{
			if(isset($this->scriptFiles[self::POS_HEAD]))
			{
				foreach($this->scriptFiles[self::POS_HEAD] as $scriptFileValueUrl=>$scriptFileValue)
				{
					if(is_array($scriptFileValue))
						$html.=CHtml::scriptFile($scriptFileValueUrl,$scriptFileValue)."\n";
					else
						$html.=CHtml::scriptFile($scriptFileValueUrl)."\n";
				}
			}
			if(isset($this->scripts[self::POS_HEAD]))
				$html.=$this->renderScriptBatch($this->scripts[self::POS_HEAD]);
		}
		if($html!=='')
		{
			$count=0;
			$output=preg_replace('/(<title\b[^>]*>|<\\/head\s*>)/is','<###head###>$1',$output,1,$count);
			if($count)
				$output=str_replace('<###head###>',$html,$output);
			else
				$output=$html.$output;
		}
	}

    /**
     * Modified to prevent duplicate rendering of scripts.
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
     */
    private $renderedScripts = array ();
	protected function renderScriptBatch(array $scripts)
	{
		$html = '';
		$scriptBatches = array();
        /* x2modstart */ 
		foreach($scripts as $scriptName => $scriptValue)
		{
            // scripts with numeric names are assumed to have been added in renderBodyEnd
            if (!is_numeric ($scriptName) && isset ($this->renderedScripts[$scriptName])) continue;
            $this->renderedScripts[$scriptName] = true;
        /* x2modend */ 
			if(is_array($scriptValue))
			{
				$scriptContent = $scriptValue['content'];
				unset($scriptValue['content']);
				$scriptHtmlOptions = $scriptValue;
				ksort($scriptHtmlOptions);
			}
			else
			{
				$scriptContent = $scriptValue;
				$scriptHtmlOptions = array();
			}
			$key=serialize($scriptHtmlOptions);
			$scriptBatches[$key]['htmlOptions']=$scriptHtmlOptions;
			$scriptBatches[$key]['scripts'][]=$scriptContent;
		}
		foreach($scriptBatches as $scriptBatch)
			if(!empty($scriptBatch['scripts']))
				$html.=CHtml::script(implode("\n",$scriptBatch['scripts']),$scriptBatch['htmlOptions'])."\n";

		return $html;
	}

    /**
     * Overrides parent method to add cache buster parameter and substitute the asset
     * domain if requested
     */
    public function registerCssFile ($url, $media='') {
        if ($this->useAbsolutePaths) $url = $this->makeUrlAbsolute ($url);
        $url = $this->getCSSPathname ($url);
        if (X2AssetManager::$enableAssetDomains)
            $url = Yii::app()->assetManager->substituteAssetDomain ($url);
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
            '../../../js/qtip/jquery.qtip.css',
            '../../../js/checklistDropdown/jquery.multiselect.css',
            'rating/jquery.rating.css',
            'fontAwesome/css/font-awesome.css',
            'bootstrap/bootstrap.css',
            'css-loaders/load8.css',
        );

         
        if (get_class (Yii::app()->controller) !== 'EmailInboxesController')
         
            $cssFiles[] = 'recordView.css';

        $responsiveCssFiles = array (
            'responsiveLayout.css',
            'responsiveUIElements.css',
            'responsiveX2Forms.css',
        );

        $this->registerResponsiveTitleBarCss ();

        $this->registerCssFiles ('combinedCss', $cssFiles, 'screen, projection');

        if (AuxLib::getLayoutType () === 'responsive') {
            $this->registerCssFiles ('responsiveCombinedCss', 
                $responsiveCssFiles, 'screen, projection');
        }
    }

    /**
     * Instantiates the Flashes utility class 
     */
    public function registerX2Flashes () {
        $this->registerScriptFile($this->baseUrl.'/js/TopFlashes.js', CClientScript::POS_END);
        $this->registerScriptFile($this->baseUrl.'/js/X2Flashes.js', CClientScript::POS_END);
        $this->registerScript ('registerX2Flashes', "
        ;(function () {
            x2.flashes = new x2.Flashes ({
                containerId: 'x2-flashes-container',
                expandWidgetSrc: '".Yii::app()->getTheme()->getBaseUrl().
                    '/images/icons/Expand_Widget.png'."',
                collapseWidgetSrc: '".Yii::app()->getTheme()->getBaseUrl().
                    '/images/icons/Collapse_Widget.png'."',
                closeWidgetSrc: '".Yii::app()->getTheme()->getBaseUrl().
                    '/images/icons/Close_Widget.png'."',
                translations: ".CJSON::encode (array (
                    'noticeFlashList' => Yii::t('app', '{Action} exectuted with', array(
                        '{Action}'=>Modules::displayName(false, 'Actions')
                    )),
                    'errorFlashList' => Yii::t('app', '{Action} exectuted with', array(
                        '{Action}'=>Modules::displayName(false, 'Actions')
                    )),
                    'noticeItemName' => Yii::t('app', 'warnings'),
                    'errorItemName' => Yii::t('app', 'errors'),
                    'successItemName' => Yii::t('app', 'successes'),
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

    private function registerX2QuickCRUD () {
        $this->registerPackages (array (
            'QuickCRUD' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/X2Widget.js',
                    'js/X2QuickCRUD.js',
                    'js/X2QuickCreate.js',
                    'js/X2QuickRead.js',
                ),
            ),
        ));
        $modelsWhichSupportQuickCreate = 
            QuickCreateRelationshipBehavior::getModelsWhichSupportQuickCreate (true);
        $createUrls = QuickCreateRelationshipBehavior::getCreateUrlsForModels (
            $modelsWhichSupportQuickCreate);
        $viewUrls = QuickCRUDBehavior::getUrlsForModels (
            QuickCRUDBehavior::getModelsWhichSupportQuickView (), 'view');
        $dialogTitles = QuickCreateRelationshipBehavior::getDialogTitlesForModels (
            $modelsWhichSupportQuickCreate);
        $this->registerScript('registerQuickCreate',"
            x2.QuickCreate.createRecordUrls = ".CJSON::encode ($createUrls).";
            x2.QuickCreate.dialogTitles = ".CJSON::encode ($dialogTitles).";
            x2.QuickRead.viewRecordUrls = ".CJSON::encode ($viewUrls).";
            x2.QuickRead.translations = ".CJSON::encode (array (
                'View inline record details' => Yii::t('app', 'View inline record details'),
            )).";
            x2.QuickRead.dialogTitles = ".CJSON::encode ($dialogTitles).";
        ", CClientScript::POS_END);
    }

    private function registerAttachments () {
        $this->registerScriptFile($this->baseUrl.'/js/Attachments.js');
        $this->registerScript('X2ClientScript.registerAttachments',"
            x2.attachments = new x2.Attachments ({
                translations: ".CJSON::encode (array (
                    'filetypeError' => Yii::t('app', '"{X}" is not an allowed filetype.'),
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
                dateFormat: ".CJSON::encode(Formatter::formatDatePicker()).",
                timeFormat: ".CJSON::encode(Formatter::formatTimePicker()).",
                ampm: ".CJSON::encode(Formatter::formatAMPM())."
            };
        ", CClientScript::POS_END);
    }

    private function registerAuxLibTranslationsScript () {
        $this->registerScript('registerAuxLibTranslations',"
            auxlib.translations = ".CJSON::encode (array (
                'Are you sure you want to delete this item?' => 
                    Yii::t('app', 'Are you sure you want to delete this item?'), 
                'Delete item?' => Yii::t('app', 'Delete item?'), 
                'Cancel' => Yii::t('app','Cancel'),
                'Confirm' => Yii::t('app', 'Confirm')
            )).";
        ", CClientScript::POS_END);
    }

    private function registerTestingScripts () {
        if (YII_UNIT_TESTING) {
            Yii::app()->clientScript->registerScript('unitTestingErrorHandler',"
            ;(function () {
                if (typeof x2 === 'undefined') return;
                if (x2.UNIT_TESTING) {
                    var oldErrorHandler = window.onerror;
                    window.onerror = function (errorMessage, url, lineNumber) {
                        $('body').attr (
                            'x2-js-error', 'Javascript Error: ' + url + ': ' + lineNumber + ': ' +
                                errorMessage);

                        if (oldErrorHandler) 
                            return oldErrorHandler (errorMessage, url, lineNumber);
                        return false;
                    };
                }
            }) ();
            ", self::POS_HEAD);
        }
    }

    private function registerDebuggingScripts () {
        if (YII_DEBUG) {
            Yii::app()->clientScript->registerScript('debuggingErrorHandler',"
            ;(function () {
                if (typeof x2 === 'undefined') return;
                if (x2.DEBUG) {
                    var oldErrorHandler = window.onerror;
                    window.onerror = function (errorMessage, url, lineNumber) {
//                        alert (
//                            'JavaScript Error: ' + url + ': ' + lineNumber + ': ' + errorMessage +
//                            '.\\nTo turn these messages off, set YII_DEBUG to false.');
                        if (oldErrorHandler) return oldErrorHandler (errorMessage, url, lineNumber);
                        return false;
                    };
                }
            }) ();
            ", self::POS_HEAD);
        }
    }

    /**
     * Register the geolocation JavaScript
     * @param bool $onLocationButton Whether to register the geolocation on the location button
     * @param bool $multiple whether to operate on multiple geoCoords inputs
     * @param const $pos CClientScript position
     */
    public function registerGeolocationScript($onLocationButton = false, $multiple = false, $pos = CClientScript::POS_READY) {
        $selector = $multiple ? "input[name=geoCoords]" : "#geoCoords";
        $enableGeolocation = Yii::app()->settings->enableGeolocation;
        $noDNT = (!isset ($_SERVER['HTTP_DNT']) || $_SERVER['HTTP_DNT'] != 1);
        if ($onLocationButton) {
            if (Yii::app()->settings->enableMaps) {
                $key = Yii::app()->settings->getGoogleApiKey('geocoding');
                $assetUrl = 'https://maps.googleapis.com/maps/api/js';
                if (!empty($key))
                    $assetUrl .= '?key='.$key;
                Yii::app()->clientScript->registerScriptFile($assetUrl, CClientScript::POS_END);
            }
            Yii::app()->clientScript->registerScript('geolocationJs', '
                $("#toggle-location-button").click(function (evt) {
                    evt.preventDefault();
                    if ($("#toggle-location-button").data("location-enabled") === true) {
                        // Clear geoCoords field and reset style
                        $("#checkInComment").slideUp();
                        $("#toggle-location-comment-button").slideUp().css("color", "");
                        $("'.$selector.'").val("");
                        $("#toggle-location-button")
                            .data("location-enabled", false)
                            .css("color", "");
                    } else {
                        // Populate geoCoords field and highlight blue
                        $("#toggle-location-comment-button").slideDown();
                        $("#toggle-location-button")
                            .data("location-enabled", true)
                            .css("color", "blue");'.
                        (($enableGeolocation && isset($_SERVER['HTTPS']) && $noDNT) ?
                        'if ("geolocation" in navigator) {
                            navigator.geolocation.getCurrentPosition(function(position) {
                            var pos = {
                              lat: position.coords.latitude,
                              lon: position.coords.longitude,
                              locationEnabled: true
                            };
                            $("'.$selector.'").val(JSON.stringify (pos));

                            if (typeof google !== "undefined") {
                                var latLng = {
                                    lat: position.coords.latitude,
                                    lng: position.coords.longitude
                                }
                                var geocoder = new google.maps.Geocoder();
                                geocoder.geocode( {"location": latLng}, function(results, status) {
                                    if (status == google.maps.GeocoderStatus.OK) {
                                        var pos = JSON.parse($("'.$selector.'").val());
                                        pos.address = results[0].formatted_address;
                                        $("'.$selector.'").val(JSON.stringify (pos));
                                    }
                                });
                            }
                          }, function() {
                            console.log("error fetching geolocation data");
                          });
                        }' : '$("'.$selector.'").val(JSON.stringify ({locationEnabled: true}));').
                    '}
                });
                $("#toggle-location-comment-button").click(function(evt) {
                    evt.preventDefault();
                    if ($("#checkInComment").is(":visible")) {
                        $("#checkInComment").slideUp();
                        $("#toggle-location-comment-button").css("color", "");
                    } else {
                        $("#toggle-location-comment-button").css("color", "blue");
                        $("#checkInComment").slideDown();
                    }
                });
            ', $pos);
        } else if ($enableGeolocation && isset($_SERVER['HTTPS']) && $noDNT) {
            Yii::app()->clientScript->registerScript('geolocationJs', '
                if ("geolocation" in navigator) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                    var pos = {
                      lat: position.coords.latitude,
                      lon: position.coords.longitude
                    };

                    $("'.$selector.'").val(JSON.stringify (pos));
                  }, function() {
                    console.log("error fetching geolocation data");
                  });
                };
            ', $pos);
        }
    }

    /**
     * Register the check-in JavaScript
     * @param string $submitSelector CSS selector of the associated submit button
     * @param bool $multiple whether to operate on multiple geoCoords inputs
     * @param const $pos CClientScript position
     */
    public function registerCheckinScript($submitSelector, $onByDefault = false, $multiple = false, $pos = CClientScript::POS_READY) {
        $selector = $multiple ? "input[name=geoCoords]" : "#geoCoords";
        Yii::app()->clientScript->registerScript('checkInJs', '
            $("#checkInComment").on("blur", function() {
                var comment = $(this).val();
                var coordsVal = $("'.$selector.'").val();
                var coords = {};
                if (coordsVal) {
                    coords = JSON.parse(coordsVal);
                    if (!coords) {
                        coords = {};
                    }
                }
                coords.comment = comment;
                $("'.$selector.'").val(JSON.stringify(coords));
            });
            $("'.$submitSelector.'").click(function () {
                $("#checkInComment")
                    .blur()
                    .val("");
            });
        ', $pos);
        if ($onByDefault) {
            Yii::app()->clientScript->registerScript('startCheckInJs', '
                $("#toggle-location-button").click();
            ', CClientScript::POS_READY);
        }
    }

    /**
     * Performs all the necessary JavaScript/CSS initializations for most parts of the app.
     */
    public function registerMain(){
        $fullscreen = $this->fullscreen;
        $profile = $this->profile;
        $baseUrl = $this->baseUrl;
        $themeUrl = $this->themeUrl;
        $scriptUrl = $this->scriptUrl;
        $admin = $this->admin;
        $isGuest = $this->isGuest;


        // jQuery and jQuery UI libraries
        $this->registerCoreScript('jquery')
           ->registerCoreScript('jquery.ui')
           ->registerCoreScript('jquery.migrate')
           ->registerCoreScript('bbq')
           ;

       $this->registerPackages($this->getDefaultPackages());

        $cldScript = $this->getCurrencyConfigScript ();

        AuxLib::registerPassVarsToClientScriptScript('auxlib', array(
            'saveMiscLayoutSettingUrl' =>
            "'".addslashes(Yii::app()->createUrl('/profile/saveMiscLayoutSetting'))."'"
                ), 'passAuxLibVars'
        );
        
        $this->registerX2ModelMappingsScript ();
        $this->registerX2Forms ();
        $this->registerX2QuickCRUD ();
        $this->registerX2Flashes ();

        Yii::app()->clientScript->registerScript('csrfTokenScript', "
            x2.csrfToken = '".Yii::app()->request->getCsrfToken ()."';
        ", CClientScript::POS_HEAD);

        $this->registerAttachments ();
        $this->registerDateFormats ();
        if (YII_DEBUG) $this->registerScriptFile($baseUrl.'/js/Timer.js');

        Yii::app()->clientScript->registerPackage('spectrum');

        // custom scripts
        $this->registerScriptFile($baseUrl.'/js/json2.js')
            ->registerScriptFile(
                $baseUrl.'/js/lib/lodash/lodash.js', self::POS_END)
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
            ->registerScriptFile($baseUrl.'/js/qtip/jquery.qtip.js')
            ->registerScriptFile($baseUrl.'/js/ActionFrames.js')
            ->registerScriptFile($baseUrl.'/js/ColorPicker.js', CCLientScript::POS_END)
            ->registerScriptFile($baseUrl.'/js/PopupDropdownMenu.js', CCLientScript::POS_END)
            ->registerScriptFile($baseUrl.'/js/jQueryOverrides.js', CCLientScript::POS_END)
            ->registerScriptFile($baseUrl.'/js/checklistDropdown/jquery.multiselect.js');

        $this->registerTestingScripts ();
        $this->registerDebuggingScripts ();

        if(AuxLib::isIPad ()){
            $this->registerScriptFile($baseUrl.'/js/jquery.mobile.custom.js');
        }
        $this->registerInitScript ();
        $this->registerAuxLibTranslationsScript ();

        if(Yii::app()->session['translate'])
            $this->registerScriptFile($baseUrl.'/js/translator.js');

        $this->registerScriptFile($baseUrl.'/js/backgroundFade.js');
        $this->registerScript('datepickerLanguage', "
            $.datepicker.setDefaults($.datepicker.regional['']);
        ");
        $mmPath = Yii::getPathOfAlias('application.extensions.moneymask.assets');
        $aMmPath = Yii::app()->getAssetManager()->publish($mmPath);
        $this->registerScriptFile("$aMmPath/jquery.maskMoney.js");
        $this->registerCssFile($baseUrl.'/css/normalize.css', 'all')
            ->registerCssFile($themeUrl.'/css/print.css', 'print')
            ->registerCoreScript('cookie');
        $this->registerCombinedCss ();
        if(AuxLib::getLayoutType () !== 'responsive' && AuxLib::isAndroid ()) {
            $this->registerCssFile(
                $themeUrl.'/css/androidLayout.css', 'screen, projection');
        } elseif (AuxLib::isIPad ()) {
            $this->registerCssFile($themeUrl.'/css/ipadLayout.css', 'screen, projection');
        }

        $this->registerScript('fullscreenToggle', '
            window.enableFullWidth = '.(!Yii::app()->user->isGuest ? 
                ($profile->enableFullWidth ? 'true' : 'false') : 'true').';
            window.fullscreen = '.($fullscreen ? 'true' : 'false').';
        ', CClientScript::POS_HEAD);

        if(is_object(Yii::app()->controller->module)){
            $this->registerScript('saveCurrModule', "
                x2.currModule = '".Yii::app()->controller->module->name."';
            ", CClientScript::POS_HEAD);
        }

        if(!$isGuest){
            $this->registerScript('notificationsParams', "
                x2.notifications = new x2.Notifs ({
                    disablePopup: ".($profile->disableNotifPopup ? 'true' : 'false').",
                    translations: {
                        clearAll: '".addslashes(
                            Yii::t('app', 'Permanently delete all notifications?'))."'
                    }
                });
            ", CClientScript::POS_READY);
            $this->registerScriptFile($baseUrl.'/js/jstorage.min.js')
               ->registerScriptFile(
                $baseUrl.'/js/notifications.js', CClientScript::POS_BEGIN);
        }

        if(!$isGuest && ($profile->language == 'he' || $profile->language == 'fa'))
            $this->registerCss('rtl-language', 'body{text-align:right;}');

        $this->registerCoreScript('rating');
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
            ;(function () {
                var actionFramesName = 'actionFrames';
                x2[actionFramesName] = new x2.ActionFrames ({ 
                    instanceName: actionFramesName,
                    deleteActionUrl: '".
                        Yii::app()->controller->createUrl ('/actions/actions/delete')."'
                });
            }) ();
        ", CClientScript::POS_HEAD);
    }

    private function getJSPathname ($path) {
        if (preg_match('/(http|https):\/\//',$path) || preg_match ('/\.min\.js$/', $path)){
            return $path;
        }
        $fileName = str_replace('/',DIRECTORY_SEPARATOR, str_replace(Yii::app()->baseUrl, '', $path));
        $altPath = Yii::getRootPath ().preg_replace  ('/\.js(\?\d+)?$/', '.min.js', $fileName);
        if (!YII_DEBUG && file_exists ($altPath)) {
            return preg_replace ('/\.js(\?\d+)?$/', '.min.js\1', $path);
        } else {
            return $path;
        }
    }

    private function getCSSPathname ($path) {
        if (preg_match('/(http|https):\/\//',$path) || preg_match ('/\.min\.css$/', $path)){
            return $path;
        }
        $fileName = str_replace('/',DIRECTORY_SEPARATOR, str_replace(Yii::app()->baseUrl, '', $path));
        $altPath = Yii::getRootPath ().preg_replace ('/\.css(\?\d+)?$/', '.min.css', $fileName);
        if (!YII_DEBUG && file_exists ($altPath)) {
            return preg_replace ('/\.css(\?\d+)?$/', '.min.css\1', $path);
        } else {
            return $path;
        }
    }

}
