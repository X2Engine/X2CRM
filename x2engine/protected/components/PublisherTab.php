<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
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

/**
 * @package application.components
 */
abstract class PublisherTab extends X2Widget {
    
    /**
     * Path to 
     * @var String 
     */
    public $viewFile;

    /**
     * @var String 
     */
    public $title;

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
    public $tabPrototypeName = 'PublisherTab';

    /**
     * Packages which will be registered when the widget content gets rendered.
     */
    protected $_packages;

    /**
     * @var string This script gets registered when the widget content gets rendered.
     */
    protected $_setupScript;

    /**
     * Magic getter. Returns this widget's setup script.
     * @return string JS string which gets registered when widget content gets rendered
     */
    public function getSetupScript () {
        if (!isset ($this->_setupScript)) {
            $this->_setupScript = "
                (function () {
                    var tab = new x2.".$this->tabPrototypeName." ({
                        id: '".$this->tabId."',
                        translations: {
                            beforeSubmit: '".addslashes(
                                Yii::t('actions', 'Please enter a description.'))."',
                            startDateError: '".addslashes(
                                Yii::t('actions', 'Please enter a start date.'))."',
                            endDateError: '".addslashes(
                                Yii::t('actions', 'Please enter an end date.'))."',
                        }
                    });
                    x2.publisher.addTab (tab);
                }) ();
            ";
        }
        return $this->_setupScript;
    }

    /**
     * Magic getter. Returns this widget's packages. 
     */
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array (
                'auxlib' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/auxlib.js',
                    ),
                ),
                'PublisherTabJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/publisher/PublisherTab.js',
                    ),
                    'depends' => array ('auxlib')
                ),
            );
        }
        return $this->_packages;
    }

    public function renderTab ($viewParams) {
        Yii::app()->clientScript->registerPackages ($this->packages);
        Yii::app()->clientScript->registerScript (
            $this->tabId.'Script', $this->setupScript, CClientScript::POS_END);
        Yii::app()->controller->renderPartial ($this->viewFile, array_merge (
            $viewParams, array ('startVisible' => $this->startVisible)));
    }

    public function renderTitle () {
        echo '<a href="#'.$this->tabId.'">'.$this->title.'</a>';
    }

}
