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
 * @package application.components.sortableWidget
 */
class ActionsWidget extends TransactionalViewWidget {

     

    protected $labelIconClass = 'fa-play-circle'; 
    protected $historyType = 'action';

    public function getCreateButtonTitle () {
        return Yii::t('app', 'New action');
    }
 
    private static $_JSONPropertiesStructure;
    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'Actions',
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    public function getDataProvider () {
        if (!isset ($this->_dataProvider)) {
            $this->_dataProvider = parent::getDataProvider ();
            //if (!isset ($_GET[$this->getWidgetKey ().'_sort'])) {
                //$this->_dataProvider->criteria->order = 'dueDate asc';
            //}
        }
        return $this->_dataProvider;
    }

    public function getGridViewConfig () {
        if (!isset ($this->_gridViewConfig)) {
            $this->_gridViewConfig = array_merge (
                parent::getGridViewConfig (),
                array (
                    'defaultGvSettings' => $this->buildDefaultGvSettings (array (
                        'actionDescription',
                        'assignedTo',
                        'dueDate',
                    ), array (array (1, 2), array (3, 4))),
                )
            );
            $this->_gridViewConfig['specialColumns'] = array_merge (
                $this->_gridViewConfig['specialColumns'],
                array (
                    'dueDate' => array (
                        'name' => 'dueDate',
                        'header' => Yii::t('app', 'Due Date'),
                        'value' => 'Actions::parseStatus ($data->dueDate, "short", "short")',
                        'type' => 'raw',
                    ),
                )
            );
            if (Yii::app()->params->profile->historyShowRels) {
                $this->_gridViewConfig['defaultGvSettings'] = $this->buildDefaultGvSettings (array (
                    'actionDescription',
                    'assignedTo',
                    'associationName',
                    'dueDate',
                ), array (array (3, 4)));
            }
        }
        return $this->_gridViewConfig;
    }
}

?>
