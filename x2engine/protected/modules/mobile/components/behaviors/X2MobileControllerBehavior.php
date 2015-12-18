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

Yii::import ('application.components.behaviors.*');
Yii::import ('application.modules.mobile.components.actions.*');
Yii::import ('application.modules.mobile.*');
Yii::import ('application.modules.mobile.models.*');

class X2MobileControllerBehavior extends X2ControllerBehavior {

    /**
     * used for jquerymobile layout
     */
    public $dataUrl; 
    public $pageId; 
    public $pageClass; 
    public $headerTitle; 

    public $pathAliasBase = 'application.modules.mobile.';

    /**
     * @var bool $includeActions
     */
    public $includeActions = true; 

    public function hasMobileAction ($action) {
        $actions = $this->owner->actions ();
        return isset ($actions[$action]);
    }

    private $_assetsUrl;
    public function getAssetsUrl () {
        if (!isset ($this->_assetsUrl)) {
            if (isset ($this->owner->module)) {
                $this->_assetsUrl = $this->owner->module->assetsUrl;
            } else {
                $this->_assetsUrl = Yii::app()->getAssetManager()->publish (
                    Yii::getPathOfAlias($this->pathAliasBase.'assets'), false, -1, true);
            }
        }
        return $this->_assetsUrl;
    }

    public function setAssetsUrl ($assetsUrl) {
        $this->_assetsUrl = $assetsUrl;
    }

    public function actions () {
        if ($this->owner instanceof MobileController || !$this->includeActions) return array ();
        return array (
            'mobileIndex' => array (
                'class' => 'MobileIndexAction'
            ),
            'mobileView' => array (
                'class' => 'MobileViewAction'
            ),
            'mobileCreate' => array (
                'class' => 'MobileCreateAction'
            ),
            'mobileUpdate' => array (
                'class' => 'MobileUpdateAction'
            ),
            'mobileDelete' => array (
                'class' => 'MobileDeleteAction'
            ),
        );
    }

    public function beforeAction ($action) {
        if (!($this->owner instanceof MobileController) &&
            !in_array ($this->owner->action->getId (), array_keys ($this->actions ()))) {

            return true;
        }
        
        Yii::app()->user->loginUrl = array ('/mobile/login');

        Yii::app()->params->isMobileApp = true;

        // fix profile linkable behavior since model was instantiated before action
        if (!preg_match (
            '/\/mobileView$/',
            Yii::app()->params->profile->asa ('X2LinkableBehavior')->viewRoute)) {

            Yii::app()->params->profile->asa ('X2LinkableBehavior')->viewRoute .= '/mobileView';
        }
        
        $this->dataUrl = $this->owner->createAbsoluteUrl ($action->getId ());
        $this->pageId = lcfirst (preg_replace ('/Controller$/', '', get_class ($this->owner))).'-'.
            $action->getId ();

        $cookie = new CHttpCookie('isMobileApp', 'true'); // create cookie
        $cookie->expire = 2147483647; // max expiration time
        Yii::app()->request->cookies['isMobileApp'] = $cookie; // save cookie

          


        if (!($this->owner instanceof MobileController)) {
            $this->owner->layout = $this->pathAliasBase.'views.layouts.main';
            if ($this->owner->module) {

                $this->owner->setAssetsUrl (Yii::app()->getAssetManager()->publish (
                    Yii::getPathOfAlias($this->pathAliasBase.'assets'), false, -1, true));
                $this->owner->module->assetsUrl = $this->owner->assetsUrl;
                Yii::app()->clientScript->packages = MobileModule::getPackages (
                    $this->owner->module->assetsUrl);
            } else {
                Yii::app()->clientScript->packages = MobileModule::getPackages (
                    $this->owner->assetsUrl);
            }
        }

        return true;
    }

    public function includeDefaultJsAssets () {
        return !$this->owner->isAjaxRequest () || isset ($_GET['includeX2TouchJsAssets']);
    }

    public function includeDefaultCssAssets () {
        return !$this->owner->isAjaxRequest () || isset ($_GET['includeX2TouchCssAssets']);
    }

    /**
     * Wrap specified JS in the appropriate on load handler 
     * @param string $js
     */
    public function onPageLoad ($js) {
        static $i=0;
        if ($this->owner->isAjaxRequest ()) {
            Yii::app()->clientScript->registerScript('X2MobileControllerBehavior.onPageLoad.'.$i, 
            "$($js);", CClientScript::POS_END);
        } else {
            Yii::app()->clientScript->registerScript('X2MobileControllerBehavior.onPageLoad.'.$i, 
            "$(document).on ('pagecontainercreate', $js);", CClientScript::POS_END);
        }
        $i++;
    }

    /**
     * Guarantees unique suffix across page loads
     */
    public function getUniquePageIdSuffix () {
        if (isset ($_SESSION['X2MobileControllerBehavior.random'])) {
            $oldVal = $_SESSION['X2MobileControllerBehavior.random'];
            while (($suffix = (mt_rand ().preg_replace ('/\./', '_', microtime (true)))) === 
                $oldVal) {};

            $_SESSION['X2MobileControllerBehavior.random'] = $suffix;
        } else {
            $suffix = mt_rand ().preg_replace ('/\./', '_', microtime (true));
        }
        return $suffix;
    }
}

?>
