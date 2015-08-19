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

/**
 * Widget class for displaying all available inline actions.
 *
 * Displays tabs for "log a call","new action" and the like.
 *
 * @package application.components
 */
class Publisher extends X2Widget {

    public static $actionTypeToTab = array (
        'note' => 'PublisherCommentTab',
        'action' => 'PublisherActionTab',
        'call' => 'PublisherCallTab',
        'time' => 'PublisherTimeTab',
        'event' => 'PublisherEventTab',
        'products' => 'PublisherProductsTab',
    );

    public $id = '';
    public $JSClass = 'Publisher';
    public $model;
    public $associationType; // type of record to associate actions with
    public $associationId = ''; // record to associate actions with
    public $assignedTo = null; // user actions will be assigned to by default
    public $renderTabs = true;

    public $viewParams = array(
        'model',
        'associationId',
        'associationType',
    );

    protected $_packages;
    private $_tabs; // available tabs with tab titles
    private $_hiddenTabs;

    public function getTabs () {
        if (!isset ($this->_tabs)) {
            $visibleTabs = array_filter (Yii::app()->settings->actionPublisherTabs,
                function ($shown) {
                    return $shown; 
                });
            $this->_tabs = array ();
            foreach ($visibleTabs as $tabName => $shown) {
                $tab = new $tabName ();
                $tab->publisher = $this;
                $tab->namespace = $this->namespace;
                $this->_tabs[] = $tab;
            }
        }
        return $this->_tabs;
    }

    public function setTabs ($tabs) {
        $this->_tabs = $tabs;
        foreach ($this->_tabs as $tab) {
            $tab->publisher = $this;
        }
    }

    /**
     * Magic getter. Returns this widget's packages. 
     */
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages (), array (
                'PublisherJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/publisher/Publisher.js',
                    ),
                    'depends' => array ('auxlib', 'MultiRowTabsJS')
                ),
                'MultiRowTabsJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/MultiRowTabs.js',
                    ),
                    'depends' => array ('jquery', 'jquery.ui')
                ),
            ));
        }
        return $this->_packages;
    }

    public function getJSClassParams () {
        if (!isset ($this->_JSClassParams)) {
            $selectedTab = $this->tabs[0]->tabId;
            $this->_JSClassParams = array_merge (parent::getJSClassParams (), array (
                'translations' => array (),
                'initTabId' => $selectedTab,
                'publisherCreateUrl' => 
                    Yii::app()->controller->createUrl ('/actions/actions/publisherCreate'),
                'isCalendar' => $this->calendar,
                'renderTabs' => $this->renderTabs,
            ));
        }
        return $this->_JSClassParams;
    }

    public function getTranslations () {
        if (!isset ($this->_translations)) {
            $this->_translations = array_merge (parent::getTranslations (), array (
                'View History Item' => Yii::t('app', 'View History Item')
            ));
        }
        return $this->_translations;
    }

    public function run() {
        $model = new Actions;
        $model->associationType = $this->associationType;
        $model->associationId = $this->associationId;
        if($this->assignedTo) {
            $model->assignedTo = $this->assignedTo;
        } else {
            $model->assignedTo = Yii::app()->user->getName();
        }
        $this->model = $model;
        $selectedTabObj = $this->tabs[0];
        $selectedTabObj->startVisible = true;

        $this->registerPackages ();
        $this->instantiateJSClass (false);

        Yii::app()->clientScript->registerScript('loadEmails', "
        $(document).on('ready',function(){
            $(document).on('click','.email-frame',function(){
                var id=$(this).attr('id');
                x2.Publisher.loadFrame(id,'Email');
            });
            $(document).on ('click', '.quote-frame', function(){
                var id=$(this).attr('id');
                x2.Publisher.loadFrame(id,'Quote');
            });

            $(document).on ('click', '.quote-print-frame', function(){
                var id=$(this).attr('id');
                x2.Publisher.loadFrame(id,'QuotePrint');
            });
        });
        ", CClientScript::POS_HEAD);

        Yii::app()->clientScript->registerCss('recordViewPublisherCss', '
            .action-event-panel {
                margin-top: 5px;
            }
        ');

        if ($this->renderTabs) {
            $that = $this;
            $this->render(
                'application.components.views.publisher.publisher',
                array_merge (
                    array_combine(
                        $this->viewParams,
                        array_map(function($p)use($that){return $that->$p;}, $this->viewParams)
                    ),
                    array (
                        'tabs' => $this->tabs, 
                    )
                )
            );
        }
    }

    //////////////////////////////////////////////////////////////
    // BACKWARDS COMPATIBILITY FUNCTIONS FOR OLD CUSTOM MODULES //
    //////////////////////////////////////////////////////////////

    /**
     * Old Publisher had "halfWidth" property
     */
    public function setHalfWidth($value) {
        $this->calendar = !$value;
    }
    public $calendar = false; 
    public $hideTabs = array ();
    public $selectedTab = '';


}
