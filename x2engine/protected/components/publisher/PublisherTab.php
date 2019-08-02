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
 * @package application.components
 */
abstract class PublisherTab extends X2Widget {

    public $id = '';

    public $viewFile = 'application.components.views.publisher._tab';

    /**
     * @var String 
     */
    public $title;

    /**
     * @var Publisher $publisher
     */
    public $publisher; 

    /**
     * @var bool If true, tab content container will be rendered with contents shown   
     */
    public $startVisible = false;

    /**
     * Id of tab content container
     * @var String 
     */
    public $tabId; 

    /**
     * Name of tab JS prototype 
     * @var String
     */
    public $JSClass = 'PublisherTab';

    protected $_innerViewFile;
    public function getInnerViewFile () {
        if (!isset ($this->_innerViewFile)) {

            $this->_innerViewFile = 'application.modules.actions.views.actions._'.
                ($this->type ? $this->type : 'action').'Form';
        }
        return $this->_innerViewFile;
    }

    /**
     * Packages which will be registered when the widget content gets rendered.
     */
    protected $_packages;

    /**
     * @var string This script gets registered when the widget content gets rendered.
     */
    protected $_setupScript;

    /**
     * @param bool $onReady whether or not JS class should be instantiated after page is ready
     */
    public function instantiateJSClass ($onReady=true) {
        parent::instantiateJSClass ($onReady);
        if (isset ($this->publisher)) {
            Yii::app()->clientScript->registerScript (
                $this->namespace.get_class ($this).'AddTabJS', 
                ($onReady ? "$(function () {" : "").
                    $this->publisher->getJSObjectName ().".addTab (".
                        $this->getJSObjectName ().");".
                ($onReady ? "});" : ""), CClientScript::POS_END);
        }
    }


    public function getJSClassParams () {
        if (!isset ($this->_JSClassParams)) {
            $this->_JSClassParams = array_merge (parent::getJSClassParams (), array (
                'tabId' => $this->tabId,
                'translations' => array ( 
                    'beforeSubmit' => Yii::t('actions', 'Please enter a description.'),
                    'startDateError' => Yii::t('actions', 'Please enter a start date.'),
                    'endDateError' => Yii::t('actions', 'Please enter an end date.'),
                ),
            ));
        }
        return $this->_JSClassParams;
    }


    /**
     * Magic getter. Returns this widget's packages. 
     */
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages (), array (
                'PublisherTabJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/publisher/PublisherTab.js',
                    ),
                    'depends' => array ('auxlib')
                ),
            ));
        }
        return $this->_packages;
    }

    protected $_modelType;
    public function getModelType () {
        if (!isset ($this->_modelType)) {
            $this->_modelType = ucfirst ($this->type ? $this->type : 'Action').'FormModel';
        }
        return $this->_modelType;
    }

    public function renderTab ($viewParams) {
        $this->registerPackages ();
        $this->instantiateJSClass (false);
        $modelType = $this->getModelType ();
        $model = new $modelType;
        $model->associationType = $this->publisher->model->associationType;
        $model->associationId = $this->publisher->model->associationId;
        $model->assignedTo = $this->publisher->model->assignedTo;
        $viewParams['model'] = $model;
        $viewParams['htmlOptions'] = array (
        );
        $this->render ($this->viewFile, array_merge (
            $viewParams, array ('startVisible' => $this->startVisible)));
    }

    public function renderTitle () {
        echo '<a href="#'.$this->resolveId ($this->tabId).'">'.Yii::t('actions',$this->title).'</a>';
    }

}
