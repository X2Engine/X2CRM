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






Yii::import ('application.components.sortableWidget.components.GooglePlusResources');
Yii::import('application.components.sortableWidget.SortableWidgetResizeBehavior');

/**
 * Class for displaying contact google+ profiles
 * 
 * @package application.components.sortableWidget
 */
class GooglePlusProfileWidget extends SortableWidget {

    public $viewFile = '_googlePlusProfile';

    public $model;

    public $sortableWidgetJSClass = 'GooglePlusProfileWidget';

    public $template = '<div class="submenu-title-bar widget-title-bar">{googlePlusIcon}{userIdSelector}{closeButton}{minimizeButton}</div>{widgetContents}';

    private static $_JSONPropertiesStructure;

    private $_userId;

    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'Google+ Profile',
                    'hidden' => false,
                    'height' => '700',
                    //'showProfile' => true,
                    //'showActivity' => true,
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    /**
     * Overridden to allow just widget contents to be fetched via ajax 
     */
    public function renderWidgetContents () {
        if (isset ($_GET['googlePlusProfileAjax'])) {
            ob_clean ();
            ob_start (); 
        }
        parent::renderWidgetContents ();

        if (isset ($_GET['googlePlusProfileAjax'])) {
            echo ob_get_clean (); 
            ob_flush ();
            Yii::app()->end ();
        }
    }

    private $_modelGooglePlusAliases;
    public function getModelGooglePlusAliases () {
        if (!isset ($this->_modelGooglePlusAliases)) {
            $this->_modelGooglePlusAliases = RecordAliases::getAliases ($this->model, 'googlePlus');
        }
        return $this->_modelGooglePlusAliases;
    }

    public function renderGooglePlusIcon () {
        echo '<span id="google-plus-widget-top-bar-logo"></span>';
    }

    public function renderUserIdSelector () {
        $options = array ();
        foreach ($this->getModelGooglePlusAliases () as $alias) {
            $options[$alias->alias] = $alias->label ? $alias->label : $alias->alias;
        }
        echo CHtml::dropDownList ('googlePlusUserId', null, $options, array (
            'class' => 'x2-minimal-select',
            'id' => 'google-plus-user-id-selector',
        ));
    }

    /**
     * overrides parent method. Adds JS file necessary to run the setup script.
     */
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (
                parent::getPackages (),
                array (
                    'GooglePlusProfileWidgetJS' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array (
                            'js/sortableWidgets/GooglePlusProfileWidget.js',
                        ),
                        'depends' => array ('SortableWidgetJS')
                    ),
                )
            );
        }
        return $this->_packages;
    }

    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $height = self::getJSONProperty (
                $this->profile, 'height', $this->widgetType, $this->widgetUID);
            $this->_viewFileParams = array_merge (
                parent::getViewFileParams (),
                array (
                    'username' => $this->_userId,
                    'height' => $height === 'auto' ? $height : $height.'px',
                )
            );
        }
        return $this->_viewFileParams;
    } 

    public function addErrorFromGoogleException (Google_Exception $exception) {
        $this->addError (GooglePlusResources::getErrorMessage ($exception));
    }

    private $_resources;
    public function getResources () {
        if (!isset ($this->_resources)) {
            $creds = GooglePlusResources::getGooglePlusAPICredentials ();
            $this->_resources = Yii::createComponent (array (
                'class' => 'GooglePlusResources',
                'userId' => $this->_userId,
            ));
            try {
                $this->_resources->init ();
            } catch (Google_Exception $e) {
                $this->addErrorFromGoogleException ($e);
            }
        }
        return $this->_resources;
    }

    public function getProfile () {
        $resources = $this->getResources ();
        return $resources->getProfile ();
    }

    private $_posts;
    public function getPosts () {
        $resources = $this->getResources ();
        return $resources->getProfile ();
    }

    public function run () {
        if (!GooglePlusResources::integrationIsEnabled ()) return '';
        if (!extension_loaded('curl')) {
            $this->addError (
                Yii::t('app', 'The Google Plus widget requires the PHP curl extension.'));
            return parent::run (); 
        } else {
            $credentials = GooglePlusResources::getGooglePlusAPICredentials ();
            $aliases = $this->getModelGooglePlusAliases ();
            if (!count ($aliases)) return '';
            if (isset ($_GET['googlePlusUserId'])) {
                $this->_userId = $_GET['googlePlusUserId'];
            } else {
                $this->_userId = $aliases[0]->alias;
            }
        }

        // initialize components so that we can abort if API errors occur
        $this->getResources ();

        return parent::run ();
    }
    
    protected function getJSSortableWidgetParams () {
        if (!isset ($this->_JSSortableWidgetParams)) {
            $this->_JSSortableWidgetParams = array_merge (parent::getJSSortableWidgetParams (),
                array (
                    'userId' => $this->_userId,
                    'enableResizing' => true,
                )
            );
        }
        return $this->_JSSortableWidgetParams;
    }

}

?>
