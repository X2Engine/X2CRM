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




Yii::import ('application.modules.mobile.components.*');
Yii::import ('application.modules.mobile.controllers.MobileController');

/**
 * All CSS for the mobile module should be specified in this file. Corresponding sass files
 * are automatically merged into combined.css. When new CSS assets are added, the console 
 * command combinemobilecss must be run in order to regenerate the combined.scss file.
 */

/**
 * @package application.modules.mobile
 */
class MobileModule extends X2WebModule {

    public static $useMergedCss = true;

    /**
     * @var string the path of the assets folder for this module. Defaults to 'assets'.
     */
    public $packages = array();

    public static function registerDefaultCss () {
        $packages = self::getPackages (Yii::app()->controller->assetsUrl);
    }

    public static function registerDefaultJs () {
        $packages = self::getPackages (Yii::app()->controller->assetsUrl);
    }

    public static function getPlatform () {
        //return 'iOS';
        if (isset ($_COOKIE[MobileController::PLATFORM_COOKIE_NAME])) {
            return $_COOKIE[MobileController::PLATFORM_COOKIE_NAME];
        } 
    }

    public static function supportedModules (CDbCriteria $criteria = null) {
        $basicModules = array (
            'x2Activity',
	    
            'topics',
            'contacts',
            'charts',
            'accounts',
            'opportunities',
            'x2Leads',
            'quotes',
            'products',
            'services',
            'bugReports',
            'users',
            //'groups',
        );

        $qpg = new QueryParamGenerator;
        $newCriteria = new CDbCriteria;
        $newCriteria->condition = 
            '(name in '.$qpg->bindArray ($basicModules, true).' or custom) and visible and 
             moduleType in ("module", "pseudoModule") and name != "document"';
        $newCriteria->params = $qpg->getParams ();
        // sort null values to the bottom by using max menuPosition
        $newCriteria->order = '(CASE WHEN menuPosition IS NULL THEN '.pow(2,11).' ELSE menuPosition END) ASC';
        if ($criteria) {
            $newCriteria->mergeWith ($criteria);
            $criteria = $newCriteria;
        } else {
            $criteria = $newCriteria;
        }

        $modules = Modules::model ()->findAll (
            $criteria
        );
        return $modules;
    }

    public static function getPackages ($assetsUrl) {
        return array(
            'jquery-migrate' => array(
                'baseUrl' => Yii::app()->baseUrl,
                'js' => array(
                    'js/lib/jquery-migrate-1.2.1.js',
                ),
                'depends' => array('jquery')
            ),
            'jqueryMobileCss' => array(
                'baseUrl' => $assetsUrl,
                'css' => (Yii::app()->params->isPhoneGap ? array () : array(
                    'css/jquery.mobile.structure-1.4.5.css',
                )),
                'depends' => array('jquery', 'jquery-migrate'),
            ),
            'jqueryMobileJs' => array(
                'baseUrl' => $assetsUrl,
                'js' => array(
                    'js/x2mobile-init.js',
                    'js/lib/jquery.mobile-1.4.5.js',
                ),
                'depends' => array('jquery', 'jquery-migrate'),
            ),
            'x2TouchCss' => array (
                'baseUrl' => $assetsUrl,
                'css' => array_merge (
                    array_merge (array (
                        'js/lib/jqueryui/jquery-ui.structure.css',
                        'js/lib/datepicker/jquery.mobile.datepicker.css',
                         
                        'js/lib/nano/nanoscroller.css',
                         
                    ), 
                    Yii::app()->params->isPhoneGap ? array () : array ('css/shared.css')), 

                    // enables inclusion of individual css files. Speeds up scss compilation which
                    // is important when making iterative scss changes.
                    YII_DEBUG && !self::$useMergedCss ? array_map (function ($path) {
                            if (preg_match ('/\/debug\//', $path)) {
                                return 
                                    preg_replace ('/\.css$/', '', $path) .
                                    (MobileModule::getPlatform () === 'iOS' ? 'IOS' : '') .
                                    '.css';
                            } else {
                                return $path;
                            }
                        }, array_merge (array(
                            'css/debug/main.css',
                            'css/debug/forms.css',
                            'css/jqueryMobileCssOverrides.css',
                            'css/debug/login.css',
                            'css/debug/passwordReset.css',
                            'css/debug/recordIndex.css',
                            'css/debug/topicsIndex.css',
                            'css/debug/recordView.css',
                            'css/debug/recordCreate.css',
                            'css/debug/topicsCreate.css',
                            'css/debug/topicsView.css',
                            'css/debug/activityFeed.css',
                            'css/debug/settings.css',
                            'css/debug/about.css',
                            'css/debug/license.css',
                            'css/debug/recentItems.css',
                             
                            'css/debug/chartDashboard.css',
                            
                        ), self::getPlatform () === 'iOS' ? array (
                            'css/iOS.css'
                        ) : array ()
                    )) : array (
                        self::getPlatform () === 'iOS' ? 
                            'css/zcombinediOS.css' : 
                            'css/zcombined.css'
                    )//,
                ),
                'depends' => array('jqueryMobileCss'),
            ),
            'x2TouchJs' => array (
                'baseUrl' => $assetsUrl,
                'js' => array(
                    'js/jQueryOverrides.js',
                    'js/x2touchJQueryOverrides.js',
                    'js/MobileForm.js',
                    'js/Controller.js',
                    'js/CameraButton.js',
                    'js/LocationButton.js',
                    'js/AudioButton.js',
                    'js/VideoButton.js',
                    'js/Main.js',
                    'js/MobileAutocomplete.js',
                    'js/lib/jqueryui/jquery-ui.js',
                    'js/lib/datepicker/jquery.mobile.datepicker.js',
                     
                    'js/lib/nano/jquery.nanoscroller.js',
                     
                ),
                'depends' => array('jqueryMobileJs', 'auxlib', 'bbq', 'X2Widget'),
            ),
             
//            'phoneGapCss' => array (
//                'baseUrl' => $assetsUrl,
//                'css' => array (
//                ), 
//                'depends' => array('jqueryMobileCss'),
//            ),
//            'phoneGapJs' => array (
//                'baseUrl' => $assetsUrl,
//                'js' => array(
//                ),
//                'depends' => array('jqueryMobileJs'),
//            ),
             
            'x2TouchSupplementalCss' => array (
                'baseUrl' => Yii::app()->baseUrl,
                'css' => array_merge (
                    array (
                        'themes/x2engine/css/fontAwesome/css/font-awesome.css',
                        'themes/x2engine/css/css-loaders/load8.css',
                        //'themes/x2engine/css/x2IconsStandalone.css',
                        'themes/x2engine/css/x2touchIcons.css',
                         
                        'themes/x2engine/css/components/DataWidget/DataWidget.css',
                        'js/c3/c3.css'
                         
                    ), 
                    YII_DEBUG && !self::$useMergedCss ? array(
                    ) : array ()),
            ),
            'x2TouchSupplementalJs' => array (
                'baseUrl' => Yii::app()->baseUrl,
                'js' => array(
                    'js/jQueryOverrides.js',
                    'js/webtoolkit.sha256.js',
                    'js/auxlib.js',
                    'js/jstorage.min.js',
                    'js/notifications.js',
                    'js/Attachments.js',
                ),
            ),
            'yiiactiveform' => array(
                'js' => array('jquery.yiiactiveform.js'),
                'depends' => array('jqueryMobileJs'),
            )
        );
    }
    
    public function init() {
        // this method is called when the module is being created
        // you may place code here to customize the module or the application
        // import the module-level models and components
        $this->setImport(array(
            'mobile.models.*',
            'mobile.components.*',
        ));

        Yii::app()->clientScript->packages = self::getPackages ($this->assetsUrl);

        // set module layout
        $this->layout = 'main';
    }

    public function beforeControllerAction($controller, $action) {
        if (parent::beforeControllerAction($controller, $action)) {
            // this method is called before any module controller action is performed
            // you may place customized code here
            return true;
        }
        else
            return false;
    }

}
