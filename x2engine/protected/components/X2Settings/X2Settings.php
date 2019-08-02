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
 * Manages storage and retrieval of settings.
 *
 * @package application.components.X2Settings
 */
abstract class X2Settings extends CBehavior {

    /**
     * @var string|null $uid Allows state prefix to be explicitly set
     */
    public $uid = null; 
    
    /**
     * @var string $modelClass class of model for which settings are being saved/retrieved
     */
    public $modelClass; 

    /**
     * session/db JSON key
     * @var string|null
     */
    private $_statePrefix; 
    
    // commented out since they might become useful
    /**
     * @param string $uid 
     * @param array (<setting name> => <setting val>) $settings The settings to save
     * @return bool true for success, false otherwise
     */
    //public function saveSettings ($uid, array $settings);

    /**
     * @param string $uid 
     */
    //public function getSettings ($uid);

    /**
     * @param string key the setting name
     * @param string key the setting value
     * @return bool true for success, false otherwise
     */
    abstract public function saveSetting ($key, $val);

    /**
     * @param string key the setting name
     * @return mixed The value of the gv setting
     */
    abstract public function getSetting ($key);

    /**
     * state prefix defaults to uid or uid constructed from path and model id. It might be
     * better to call this getUID since the state prefix isn't actually a prefix, it is the key in
     * its entirety.
     * @return string 
     */
    public function getStatePrefix () {
        if (!isset ($this->_statePrefix)) {
            if (isset ($this->uid)) {
                $this->_statePrefix = $this->uid;
            } else {
                $this->_statePrefix = ((!Yii::app()->params->noSession ?
                    Yii::app()->controller->uniqueid . '/' . Yii::app()->controller->action->id . 
                        (isset($_GET['id']) ? '/' . $_GET['id'] : '') : '').
                    $this->modelClass);
            }
        }
        return $this->_statePrefix;
    }

}
?>
