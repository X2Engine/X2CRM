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




Yii::import ('application.components.sortableWidget.ProfileGridViewWidget');



/**
 * @package application.components
 */
class EmailInboxWidget extends SortableWidget {

    public $defaultTitle = 'Emails';

    public $canBeDeleted = false;

    public $viewFile = '_emailInboxWidget';

    public $relabelingEnabled = true;

    public $sortableWidgetJSClass = 'EmailInboxWidget';

    public $template = '<div class="submenu-title-bar widget-title-bar">{widgetLabel}{inbox}{folder}{closeButton}{minimizeButton}{settingsMenu}</div>{widgetContents}';

    public $configExceptionMessage; 

    public static $canBeCreated = false;

    private static $_JSONPropertiesStructure;

    protected $_viewFileParams;

    /**
     * @var array the config array passed to widget ()
     */
    private $_gridViewConfig;

    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'folder' => null,
                    'emailInboxId' => null,
                    'hidden' => true,
                    'label' => Yii::t('app', 'Emails'),
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    private $_mailbox;
    public function getMailbox () {
        if (!isset ($this->_mailbox)) {
            $emailInboxId = $this->getWidgetProperty ('emailInboxId');
            $mailbox = EmailInboxes::model ()->findByPk ($emailInboxId);
            if ($mailbox && !$mailbox->isVisibleTo (Yii::app()->params->profile->user)) {
                $mailbox = null;    
                $this->setWidgetProperties (array (
                    'emailInboxId' => null,
                    'folder' => null,
                ));
            } elseif ($mailbox) {
                $folder = $this->getWidgetProperty ('folder');
                if ($folder) {
                    $mailbox->selectFolder ($folder);
                } else {
                    $this->setWidgetProperty ('folder', $mailbox->getCurrentFolder ());
                }
            }
            $this->_mailbox = $mailbox;
        }
        return $this->_mailbox;
    }

    public function getAjaxUpdateRouteAndParams () {
        $updateRoute = '/profile/view';
        $updateParams =  array (
            'widgetClass' => get_called_class (),        
            'widgetType' => $this->widgetType,
            'id' => $this->profile->id,
        );
        return array ($updateRoute, $updateParams);
    }

    private $_dataProvider;
    private $_loadMessagesOnPageLoad = true;
    public function getDataProvider () {
        if (!isset ($this->_dataProvider)) {
            $mailbox = $this->getMailbox ();
            $dataProvider = null;
            if ($mailbox) {
                $lastUid = filter_input (INPUT_GET, 'lastUid');
                if (isset ($_POST['emailAction']) && $_POST['emailAction'] === 'refresh') {
                    $mailbox->fetchLatest ();
                }
                $searchCacheOnly = !isset ($_GET['ajax']);
                $dataProvider = $mailbox->searchInbox (null, $searchCacheOnly, $lastUid, 10);
                if ($dataProvider) {
                    $this->_loadMessagesOnPageLoad = false;
                }
            } 
            if (!$dataProvider) $dataProvider = new CArrayDataProvider (array ());
            $ret = $this->getAjaxUpdateRouteAndParams ();
            list ($updateRoute, $updateParams) = $this->getAjaxUpdateRouteAndParams ();
            $dataProvider->pagination->route = $updateRoute;
            $dataProvider->pagination->params = $updateParams;
            $this->_dataProvider = $dataProvider;
        }
        return $this->_dataProvider;
    }

    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            if (!$this->hasError () &&
                !isset ($this->configExceptionMessage)) {

                $loadMessagesOnPageLoad = false;
                $mailbox = $this->getMailbox ();
                $dataProvider = $this->getDataProvider ();
                $this->_viewFileParams = array_merge (parent::getViewFileParams (), array (
                    'mailbox' => $mailbox,
                    'dataProvider' => $dataProvider,
                    'uid' => null,
                    'loadMessagesOnPageLoad' => $this->_loadMessagesOnPageLoad,
                    'notConfigured' => false,
                    'pollTimeout' => Yii::app()->settings->imapPollTimeout,
                ));
            } else {
                $this->_viewFileParams = parent::getViewFileParams ();
            }
        }
        return $this->_viewFileParams;
    } 

    public function renderInbox () {
        if ($this->hasError ()) return;
        $inboxOptions = EmailInboxes::model ()->getTabOptions ();
        echo CHtml::dropDownList (
            'emailInboxId',
            $this->getWidgetProperty ('emailInboxId'),
            (array (    
                '' => Yii::t('app', 'Select an inbox') 
            ) + $inboxOptions),
            array (
                'class' => 'x2-select email-inbox-selector',
                'title' => CHtml::encode (Yii::t('app', 'Inbox')),
            )
        );
    }

    public function renderFolder () {
        if ($this->hasError ()) return;
        $mailbox = $this->getMailbox ();
        if ($mailbox) {
            echo CHtml::dropDownList (
                'folder',
                $this->getWidgetProperty ('folder'),
                array_combine ($mailbox->folders, $mailbox->folders),
                array (
                    'class' => 'x2-select email-inbox-folder-selector',
                    'title' => CHtml::encode (Yii::t('app', 'Folder')),
                )
            );
        }
    }

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (
                parent::getPackages (),
                array (
                    'EmailInboxWidgetJS' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array(
                            'js/sortableWidgets/EmailInboxWidget.js',
                        ),
                        'depends' => array ('SortableWidgetJS')
                    ),
                )
            );
        }
        return $this->_packages;
    }

    public function init () {
        if (!extension_loaded ('imap')) { 
            $this->addError (
                Yii::t('app', 'The Email Module requires the PHP IMAP extension.'));
            return parent::init ();
        }

        $mailbox = $this->getMailbox ();
        if (!$mailbox) {
        } elseif ($mailbox->credentialId === null) {
            $this->addError (true);
        } else {
            try {
                $mailbox->getStream ();
                $mailbox->getFolders ();
            } catch (EmailConfigException $e) {
                $this->configExceptionMessage = $e->getMessage ();
                $this->addError (true);
            }
        }
        parent::init ();
    }

    public function renderErrors () {
        Yii::app()->clientScript->registerCssFile(
            Yii::app()->theme->baseUrl.'/css/components/sortableWidget/views/emailInboxWidget.css');
        if ($this->getMailbox () && $this->getMailbox ()->credentialId === null) {
            Yii::app()->controller->renderPartial (
                'application.modules.emailInboxes.views.emailInboxes._noCredentials');
        } elseif ($this->configExceptionMessage) {
            Yii::app()->clientScript->registerCssFile(
                X2WebModule::getAssetsUrlOfModule ('EmailInboxes').'/css/emailInboxes.css');
            Yii::app()->controller->renderPartial (
                'application.modules.emailInboxes.views.emailInboxes._badCredentials', array (
                    'error' => $this->configExceptionMessage,
                ));
        } else {
            parent::renderErrors ();
        }
    }

    protected function getJSSortableWidgetParams () {
        if (!isset ($this->_JSSortableWidgetParams)) {
            $this->_JSSortableWidgetParams = array_merge (
                parent::getJSSortableWidgetParams (), 
                $this->getWidgetProperties ());
        }
        return $this->_JSSortableWidgetParams;
    }

}
?>
