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

Yii::import('application.extensions.NLSClientScript');

/**
 * Custom extension of CClientScript used by the app.
 *
 * @property bool $fullscreen Whether to render in full screen mode
 * @package X2CRM.components 
 */
class X2ClientScript extends NLSClientScript {

    private $_admin;
    private $_baseUrl;
    private $_fullscreen;
    private $_isGuest;
    private $_profile;
    private $_scriptUrl;
    private $_themeUrl;
    
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
		foreach($this->scripts as $script)	// the good stuff!
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
        $(\'<link rel="stylesheet" type="text/css" href="'.$url.'">\').appendTo("head");
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
     * Performs all the necessary JavaScript/CSS initializations for most parts of the app.
     */
    public function registerMain(){
        $cs = $this;
        $jsVersion = '?'.Yii::app()->params->buildDate;
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

        // Declare currency format(s) from Yii for the formatCurrency plugin
        $locale = Yii::app()->locale;
        $cldFormat = array();
        foreach(explode(';', $locale->getCurrencyFormat()) as $format){
            $newFormat = preg_replace('/¤/', '%s', $format);
            $newFormat = preg_replace('/[#,\.0]+/', '%n', $newFormat); // The number, in positive/negative
            $cldFormat[] = $newFormat;
        }
        if(count($cldFormat) == 1){ // Default convention if no negative format is defined
            $cldFormat[] = $locale->getNumberSymbol('minusSign').$cldFormat[0];
        }
        $decSym = $locale->getNumberSymbol('decimal');
        $grpSym = $locale->getNumberSymbol('group');
// Declare:
        $cldScript = '(function($) {'."\n";
        foreach(Yii::app()->params->supportedCurrencySymbols as $curCode => $curSym){
            $cldScript .= '$.formatCurrency.regions["'.$curCode.'"] = '.CJSON::encode(array(
                        'symbol' => $curSym,
                        'positiveFormat' => $cldFormat[0],
                        'negativeFormat' => $cldFormat[1],
                        'decimalSymbol' => $decSym,
                        'digitGroupSymbol' => $grpSym,
                        'groupDigits' => true
                    )).";\n";
        }
        $cldScript .= "\n})(jQuery);";

        AuxLib::registerPassVarsToClientScriptScript('auxlib', array(
            'saveMiscLayoutSettingUrl' =>
            "'".addslashes(Yii::app()->createUrl('/profile/saveMiscLayoutSetting'))."'"
                ), 'passAuxLibVars'
        );

// custom scripts
        $cs->registerScriptFile($baseUrl.'/js/json2.js')
                ->registerScriptFile($baseUrl.'/js/main.js'.$jsVersion, CCLientScript::POS_HEAD)
                ->registerScriptFile($baseUrl.'/js/auxlib.js', CClientScript::POS_HEAD)
                ->registerScriptFile($baseUrl.'/js/LayoutManager.js')
                ->registerScriptFile($baseUrl.'/js/publisher.js')
                ->registerScriptFile($baseUrl.'/js/media.js')
                ->registerScriptFile($baseUrl.'/js/x2forms.js')
                ->registerScriptFile($baseUrl.'/js/LGPL/jquery.formatCurrency-1.4.0.js'.$jsVersion)
                ->registerScript('formatCurrency-locales', $cldScript, CCLientScript::POS_HEAD)
                ->registerScriptFile($baseUrl.'/js/modernizr.custom.66175.js')
                ->registerScriptFile($baseUrl.'/js/relationships.js')
                ->registerScriptFile($baseUrl.'/js/widgets.js')
                ->registerScriptFile($baseUrl.'/js/qtip/jquery.qtip.min.js'.$jsVersion)
                ->registerScriptFile($baseUrl.'/js/actionFrames.js'.$jsVersion)
                ->registerScriptFile($baseUrl.'/js/bgrins-spectrum-2c2010c/spectrum.js')
                ->registerScriptFile($baseUrl.'/js/checklistDropdown/jquery.multiselect.js');

        if(IS_IPAD){
            $cs->registerScriptFile($baseUrl.'/js/jquery.mobile.custom.js');
        }
        //$cs->registerScriptFile($baseUrl.'/js/jquery.mobile-1.3.2.js');

        if(Yii::app()->session['translate'])
            $cs->registerScriptFile($baseUrl.'/js/translator.js');

        $cs->registerScriptFile($baseUrl.'/js/backgroundFade.js');
        $cs->registerScript('datepickerLanguage', "
    $.datepicker.setDefaults( $.datepicker.regional[ '' ] );
");
// $cs ->registerScriptFile($baseUrl.'/js/backgroundImage.js');
// MoneyMask extension:
        $mmPath = Yii::getPathOfAlias('application.extensions.moneymask.assets');
        $aMmPath = Yii::app()->getAssetManager()->publish($mmPath);
        $cs->registerScriptFile("$aMmPath/jquery.maskMoney.js");
//$cs->registerCoreScript('jquery');
// blueprint CSS framework
        $cs->registerCssFile($baseUrl.'/css/normalize.css', 'all')
                ->registerCssFile($themeUrl.'/css/print.css'.$jsVersion, 'print')
                /* ->registerCssFile($themeUrl.'/css/screen.css'.$jsVersion,'screen, projection')
                  ->registerCssFile($themeUrl.'/css/auxlib.css'.$jsVersion,'screen, projection')
                  ->registerCssFile($themeUrl.'/css/jquery-ui.css'.$jsVersion,'screen, projection')
                  ->registerCssFile($themeUrl.'/css/dragtable.css'.$jsVersion,'screen, projection')
                  ->registerCssFile($themeUrl.'/css/main.css'.$jsVersion,'screen, projection')
                  ->registerCssFile($themeUrl.'/css/combined.css'.$jsVersion,'screen, projection')
                  ->registerCssFile($themeUrl.'/css/ui-elements.css'.$jsVersion,'screen, projection')
                  ->registerCssFile($themeUrl.'/css/layout.css'.$jsVersion,'screen, projection')
                  ->registerCssFile($themeUrl.'/css/details.css'.$jsVersion,'screen, projection')
                  ->registerCssFile($themeUrl.'/css/x2forms.css'.$jsVersion,'screen, projection')
                  ->registerCssFile($themeUrl.'/css/form.css'.$jsVersion,'screen, projection') */
                ->registerCssFile($themeUrl.'/css/combined.css'.$jsVersion, 'screen, projection')
                //->registerCssFile($baseUrl.'/js/qtip/jquery.qtip.min.css'.$jsVersion,'screen, projection')
                ->registerCoreScript('cookie');
// $cs->registerCssFile($cs->getCoreScriptUrl().'/jui/css/base/jquery-ui.css'.$jsVersion);
//$cs->registerCssFile($baseUrl.'/js/bgrins-spectrum-2c2010c/spectrum.css');

        if(IS_ANDROID)
            $cs->registerCssFile($themeUrl.'/css/androidLayout.css'.$jsVersion, 'screen, projection');
        else if(IS_IPAD)
            $cs->registerCssFile($themeUrl.'/css/ipadLayout.css'.$jsVersion, 'screen, projection');

        $cs->registerScript('fullscreenToggle', '
window.enableFullWidth = '.(!Yii::app()->user->isGuest ? ($profile->enableFullWidth ? 'true' : 'false') : 'true').';
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
            $cs->registerScriptFile($baseUrl.'/js/jstorage.min.js'.$jsVersion)
                    ->registerScriptFile($baseUrl.'/js/notifications.js'.$jsVersion, CClientScript::POS_BEGIN);
        }

        if(!$isGuest && ($profile->language == 'he' || $profile->language == 'fa'))
            $cs->registerCss('rtl-language', 'body{text-align:right;}');

        $cs->registerCoreScript('rating');
//$cs->registerCssFile($cs->getCoreScriptUrl().'/rating/jquery.rating.css');
        $cs->registerCssFile(Yii::app()->getTheme()->getBaseUrl().'/css/rating/jquery.rating.css');
    }

    public function getAdmin() {
        if(!isset($this->_admin)) {
            $this->_admin = Yii::app()->params->admin;
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


}