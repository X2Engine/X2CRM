<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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

Yii::import ('application.modules.mobile.components.*');

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
                'css' => array(
                    'css/jquery.mobile.structure-1.4.5.css',
                ),
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
                    array (
                        'js/lib/jqueryui/jquery-ui.structure.css',
                        'js/lib/datepicker/jquery.mobile.datepicker.css',
                    ), 
                    YII_DEBUG && !self::$useMergedCss ? array(
                        'css/main.css',
                        'css/forms.css',
                        'css/jqueryMobileCssOverrides.css',
                        'css/login.css',
                        'css/passwordReset.css',
                        'css/recordIndex.css',
                        'css/recordView.css',
                        'css/activityFeed.css',
                        'css/settings.css',
                        'css/about.css',
                        'css/license.css',
                        'css/recentItems.css',
                         
                    ) : array (
                        'css/combined.css'
                    )),
                'depends' => array('jqueryMobileCss'),
            ),
            'x2TouchJs' => array (
                'baseUrl' => $assetsUrl,
                'js' => array(
                    'js/x2touchJQueryOverrides.js',
                    'js/MobileForm.js',
                    'js/Controller.js',
                    'js/CameraButton.js',
                    'js/Main.js',
                    'js/MobileAutocomplete.js',
                    'js/lib/jqueryui/jquery-ui.js',
                    'js/lib/datepicker/jquery.mobile.datepicker.js',
                ),
                'depends' => array('jqueryMobileJs', 'auxlib', 'bbq', 'X2Widget'),
            ),
             
            'x2TouchSupplementalCss' => array (
                'baseUrl' => Yii::app()->baseUrl,
                'css' => array_merge (
                    array (
                        'themes/x2engine/css/fontAwesome/css/font-awesome.css',
                        'themes/x2engine/css/css-loaders/load8.css',
                        'themes/x2engine/css/x2IconsStandalone.css',
                        'themes/x2engine/css/x2touchIcons.css',
                         
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
