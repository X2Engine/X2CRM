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




Yii::import ('application.components.sortableWidget.SortableWidget');
Yii::import('application.components.sortableWidget.SortableWidgetResizeBehavior');

/**
 * @package application.components
 */
class DocViewerProfileWidget extends SortableWidget {

    public $canBeDeleted = true;

    public $defaultTitle = 'Doc Viewer';

    public $sortableWidgetJSClass = 'DocViewerProfileWidget';

    public $viewFile = '_docViewerProfileWidget';

    public $template = '<div class="submenu-title-bar widget-title-bar">{widgetLabel}{closeButton}{minimizeButton}{settingsMenu}</div>{widgetContents}';

    private static $_JSONPropertiesStructure;

    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $this->_viewFileParams = array_merge (
                parent::getViewFileParams (),
                array (
                    'docId' => self::getJSONProperty (
                        $this->profile, 'docId', $this->widgetType, $this->widgetUID),
                    'height' => self::getJSONProperty (
                        $this->profile, 'height', $this->widgetType, $this->widgetUID),
                )
            );
        }
        return $this->_viewFileParams;
    } 

    /**
     * overrides parent method
     */
    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'docId' => '', // id of the doc record to be displayed
                    'label' => Yii::t('app', 'Doc Viewer'),
                    'height' => '200',
                    'hidden' => true
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    /**
     * @return array translations to pass to JS objects 
     */
    protected function getTranslations () {
        if (!isset ($this->_translations )) {
            $this->_translations = array_merge (parent::getTranslations (), array (
                'dialogTitle'=> Yii::t('profile', 'Select a {Doc}', array(
                    '{Doc}' => Modules::displayName(false, 'Docs')
                )),
                'closeButton'=> Yii::t('profile', 'Close'),
                'selectButton'=> Yii::t('profile', 'Select'),
                'docError'=> Yii::t('profile', 'Please select an existing {Doc}', array(
                    '{Doc}' => Modules::displayName(false, 'Docs')
                )),
            ));
        }
        return $this->_translations;
    }

    protected function getJSSortableWidgetParams () {
        if (!isset ($this->_JSSortableWidgetParams)) {
            $docId = self::getJSONProperty (
                $this->profile, 'docId', $this->widgetType, $this->widgetUID);
            $this->_JSSortableWidgetParams = array_merge (parent::getJSSortableWidgetParams (),
                array (
                    'getItemsUrl' => Yii::app()->createUrl ("/docs/docs/getItems"),
                    'getDocUrl' => Yii::app()->createUrl("/docs/docs/getItem"),
                    'enableResizing' => true,
                    'docId' => $docId,
                )
            );
        }
        return $this->_JSSortableWidgetParams;
    }

    /**
     * overrides parent method. Adds JS file necessary to run the setup script.
     */
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (
                parent::getPackages (),
                array (
                    'DocViewerProfileWidgetJS' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array(
                            'js/sortableWidgets/IframeWidget.js',
                            'js/sortableWidgets/DocViewerProfileWidget.js',
                        ),
                        'depends' => array ('SortableWidgetJS')
                    ),
                )
            );
        }
        return $this->_packages;
    }

    /**
     * Magic getter. Returns this widget's css
     * @return array key is the proposed name of the css string which should be passed as the first
     *  argument to yii's registerCss. The value is the css string.
     */
    protected function getCss () {
        if (!isset ($this->_css)) {
            $this->_css = array_merge (
                parent::getCss (),
                array (
                    'docViewerProfileWidgetCss' => "
                        #".get_called_class()."-widget-content-container {
                            padding-bottom: 1px;
                        }

                        #select-a-document-dialog p {
                            display: inline;
                            margin-right: 5px;
                        }

                        .default-text-container {
                            text-align: center;
                            position: absolute;
                            top: 0;
                            bottom: 0;
                            left: 0;
                            right: 0;
                        }

                        .default-text-container a {
                            height: 17%;
                            text-decoration: none;
                            font-size: 16px;
                            margin: auto;
                            position: absolute;
                            left: 0;
                            top: 0;
                            right: 0;
                            bottom: 0;
                            color: #222222 !important;
                        }
                    "
                )
            );
        }
        return $this->_css;
    }

    protected function getSettingsMenuContentEntries () {
        return 
            '<li class="select-a-document-button">'.
                Yii::t('profile', 'Select a Document').'
            </li>'.parent::getSettingsMenuContentEntries ();
    }

    protected function getSettingsMenuContentDialogs () {
        return
            '<div id="select-a-document-dialog-'.$this->widgetUID.'" 
              style="display: none;">'.
              '<div>'.Yii::t('profile', 'Enter the name of a {Doc}:', array(
                '{Doc}' => Modules::displayName(false, 'Docs')
              )).'</div>'.
                '<input class="selected-doc">'.
            '</div>'.parent::getSettingsMenuContentDialogs ();
    }

}
?>
