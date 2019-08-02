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
 * @package application.modules.mobile.components 
 */
class MobileController extends X2Controller {

    const APP_VERSION_COOKIE_NAME = 'phoneGapAppVersionNumber';
    const PLATFORM_COOKIE_NAME = 'phoneGapAppPlatform';

    public $layout = 'application.modules.mobile.views.layouts.main';

    /**
     * @var array context menu items. This property will be assigned to {@link CMenu::items}.
     */
    public $menu = array();

    /**
     * @var array the breadcrumbs of the current page. The value of this property will
     * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
     * for more details on how to specify this property.
     */
    public $breadcrumbs = array();
    public $modelClass = 'Admin';

    public function behaviors() {
        return array_merge(parent::behaviors(), array(
            'CommonSiteControllerBehavior' => array('class' => 'application.components.behaviors.CommonSiteControllerBehavior'),
            'CommonControllerBehavior' => array(
                'class' => 'application.components.behaviors.CommonControllerBehavior'),
            'MobileControllerBehavior' => array(
                'class' =>
                'application.modules.mobile.components.behaviors.MobileControllerBehavior')
        ));
    }

    /**
     * override to allow user loginUrl to be reset for mobile
     * @return void
     */
    public function filterAccessControl($filterChain) {
        $user = Yii::app()->getUser();
        if ($user != null)
            $user->loginUrl = $this->createAbsoluteUrl('login');
        parent::filterAccessControl($filterChain);
    }

    /**
     * @return array action filters
     */
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array( 'chat', 'logout', 'home', 'getMessages', 'newMessage', 'contact',
                    'home2', 'more', 'online', 'activity', 'people', 'profile', 'recentItems', 'error',
                    'about', 'settings', 'license'),
                'users' => array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array('ping', 'index', 'login', 'forgetMe', 'captcha'),
                'users' => array('*'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    public function actions() {
        return array_merge(parent::actions(), array(
            // captcha action renders the CAPTCHA image displayed on the contact page
            'captcha' => array(
                'class' => 'CCaptchaAction',
                'backColor' => 0xFFFFFF,
                'testLimit' => 1,
            ),
        ));
    }

    

    public function actionSettings() {
        $profile = Yii::app()->params->profile;
        if (isset($_POST['Profile'])) {
            $attrs = array_intersect_key(
                    $_POST['Profile'], array_flip(array('language'))
            );
            $profile->setAttributes($attrs);
            if ($profile->save()) {
                $this->redirect('settings');
            }
        }
        $this->headerTitle = Yii::t('mobile', 'Settings');

        $this->render('settings', array(
            'profile' => $profile,
        ));
    }

    public function actionLicense() {
        $this->pageDepth = 1;
        $this->headerTitle = Yii::t('mobile', 'License');
        $basePath = Yii::getRootPath();
        $filename = implode(DIRECTORY_SEPARATOR, array($basePath, 'LICENSE.txt'));
        $fh = fopen($filename, 'r');
        $license = fread($fh, filesize($filename));
        $license = preg_replace('/\n/', '<br>', $license);
        fclose($fh);
        $this->render('license', array(
            'license' => $license,
        ));
    }

    public function actionAbout() {
        $this->headerTitle = Yii::t('mobile', 'About');
        $viewParams = array();
        if (Yii::app()->params->isPhoneGap) {
            if (isset(Yii::app()->request->cookies[self::APP_VERSION_COOKIE_NAME])) {
                $phoneGapAppVersion = Yii::app()->request->cookies[self::APP_VERSION_COOKIE_NAME];
                $viewParams['phoneGapAppVersion'] = $phoneGapAppVersion;
            }
        } else {
            
        }
        $this->render('about', $viewParams);
    }

    /**
     * Used by PhoneGap mobile app to validate installation URL and ensure mutual app compatibility
     */
    public function actionPing($version, $platform = 'Android') {
        // for phonegap testing
        //if (YII_DEBUG) header('Access-Control-Allow-Origin: *'); 

        $response = array();
        $requiresVersion = '0.0.2';
        if (version_compare($version, $requiresVersion, '<')) {
            $response['error'] = 'wrongVersion';
            $response['requiresVersion'] = $requiresVersion;
        } else {
            $cookie = new CHttpCookie(self::APP_VERSION_COOKIE_NAME, $version);
            $cookie->expire = 2147483647; // max expiration time
            Yii::app()->request->cookies[self::APP_VERSION_COOKIE_NAME] = $cookie;
            $cookie = new CHttpCookie(self::PLATFORM_COOKIE_NAME, $platform);
            $cookie->expire = 2147483647; // max expiration time
            Yii::app()->request->cookies[self::PLATFORM_COOKIE_NAME] = $cookie;
            $response['success'] = true;
            $response['appInfo'] = array(
                'version' => Yii::app()->params->version,
                'edition' => Yii::app()->edition,
            );
        }

        echo CJSON::encode($response);
    }

    public function actionRecentItems() {
        $recentItems = MobileRecentItems::getDataProvider(null);
        $this->render('recentItems', array(
            'dataProvider' => $recentItems,
        ));
    }

    /**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     */
    public function actionIndex() {
        $user = Yii::app()->user;
        if ($user == null || $user->isGuest)
            $this->redirect($this->createAbsoluteUrl('login'));
        else
            $this->redirect($this->createAbsoluteUrl('/profile/mobileActivity'));
    }

//    /**
//     * This is the action to handle external exceptions.
//     */
//    public function actionError() {
//        if ($error = Yii::app()->errorHandler->error) {
//            if ($this->isAjaxRequest ()) {
//                echo $error['message'];
//            else
//                $this->render('error', $error);
//        }
//    }

    /**
     * Obtain the IP address of the current web client.
     * @return string
     */
    public function getRealIp() {
        foreach (array(
    'HTTP_CLIENT_IP',
    'HTTP_X_FORWARDED_FOR',
    'HTTP_X_FORWARDED',
    'HTTP_X_CLUSTER_CLIENT_IP',
    'HTTP_FORWARDED_FOR',
    'HTTP_FORWARDED',
    'REMOTE_ADDR'
        ) as $var) {
            if (array_key_exists($var, $_SERVER)) {
                foreach (explode(',', $_SERVER[$var]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false)
                        return $ip;
                }
            }
        }
        return false;
    }

    /**
     * Clears remember me cookies and redirects to login page. 
     */
    public function actionForgetMe() {
        $loginForm = new LoginForm;
        foreach (array('username', 'rememberMe') as $attr) {
            // Remove the cookie if they unchecked the box
            AuxLib::clearCookie(CHtml::resolveName($loginForm, $attr));
        }
        $this->redirect($this->createAbsoluteUrl('/mobile/site/login'));
    }

    /**
     * Displays the login page
     */
    public function actionLogin() {
        if (Yii::app()->user->isInitialized && !Yii::app()->user->isGuest) {
            $this->redirect($this->createAbsoluteUrl('home'));
            return;
        }

        // allows client to detect login page redirect
        if ($this->isAjaxRequest()) {
            header('X2-Requested-Url: ' . AuxLib::getRequestUrl());
        }

        $model = new LoginForm;
        $model->useCaptcha = false;
        if ($this->loginRequiresCaptcha()) {
            $model->useCaptcha = true;
            $model->setScenario('loginWithCaptcha');
        }

        // if it is ajax validation request
        /* this would bypass captcha. commented out to prevent security vulnerability */
        /* if (isset($_POST['ajax']) && $_POST['ajax'] === 'login-form') {
          echo CActiveForm::validate($model);
          Yii::app()->end();
          } */

        // collect user input data
        if (isset($_POST['LoginForm'])) {
            $this->login($model, true);
        }

        // display the login form
        $this->render('login', array('model' => $model));
    }

    /**
     * Displays the home page
     */
    public function actionHome() {
        // display the home page
        $this->redirect($this->createAbsoluteUrl('/profile/mobileActivity'));
        //$this->redirect ($this->createAbsoluteUrl ('/accounts/mobileCreate'));
        //$this->render('dashboard', array());
    }

    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout() {
        $user = User::model()->findByPk(Yii::app()->user->getId());
        if (isset($_COOKIE['sessionToken'])) {
            unset(Yii::app()->request->cookies['sessionToken']);
        }
        if (isset($user)) {
            $user->lastLogin = time();
            $user->save();

            if (isset($_SESSION['sessionId'])) {
                SessionLog::logSession($user->username, $_SESSION['sessionId'], 'logout');
                X2Model::model('Session')->deleteByPk($_SESSION['sessionId']);
            } else {
                X2Model::model('Session')->deleteAllByAttributes(array('IP' => $this->getRealIp()));
            }
        }
        if (isset($_SESSION['access_token']))
            unset($_SESSION['access_token']);

        if (isset($_COOKIE['sessionToken'])) {
            $sessionCookie = $_COOKIE['sessionToken'];
            //unset($sessionCookie);
            X2Model::model('SessionToken')->deleteByPk($sessionCookie);
        } else {
            X2Model::model('SessionToken')->deleteAllByAttributes(array('IP' => $this->getRealIp()));
        }


        $this->redirect($this->createAbsoluteUrl('login'));
    }

    /**
     * Allow special PhoneGap parameters to persist across redirects
     */
    public function redirect($url, $terminate = true, $statusCode = 302) {
        $params = array();
        if (isset($_GET['x2ajax']))
            $params['x2ajax'] = $_GET['x2ajax'];
        if (isset($_GET['isMobileApp']))
            $params['isMobileApp'] = $_GET['isMobileApp'];

        if (isset($_GET['isPhoneGap']))
            $params['isPhoneGap'] = $_GET['isPhoneGap'];
        if (isset($_GET['includeX2TouchJsAssets']))
            $params['includeX2TouchJsAssets'] = $_GET['includeX2TouchJsAssets'];
        if (isset($_GET['includeX2TouchCssAssets']))
            $params['includeX2TouchCssAssets'] = $_GET['includeX2TouchCssAssets'];

        $url = UrlUtil::mergeParams($url, $params);
        return parent::redirect($url, $terminate, $statusCode);
    }

}
