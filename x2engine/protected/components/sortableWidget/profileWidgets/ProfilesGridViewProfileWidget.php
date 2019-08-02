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
class ProfilesGridViewProfileWidget extends ProfileGridViewWidget {

    public $canBeDeleted = true;

    public $viewFile = '_activeGridViewProfileWidget';

    public $relabelingEnabled = true;

    private static $_JSONPropertiesStructure;

    protected $_viewFileParams;

    /**
     * @var array the config array passed to widget ()
     */
    private $_gridViewConfig;

    public $defaultTitle = 'People';

    protected function getModel () {
        if (!isset ($this->_model)) {
            $this->_model = new Profile ('search', 
                $this->widgetKey,
                $this->getWidgetProperty ('dbPersistentGridSettings'));

            $this->afterGetModel ();
        }
        return $this->_model;
    }

    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array ('label' => 'People')
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    public function getDataProvider () {
        if (!isset ($this->_dataProvider)) {
            $resultsPerPage = self::getJSONProperty (
                $this->profile, 'resultsPerPage', $this->widgetType, $this->widgetUID);
            $this->_dataProvider = $this->model->search (
                $resultsPerPage, $this->widgetKey, true);
        }
        return $this->_dataProvider;
    }

    /**
     * @return array the config array passed to widget ()
     */
    public function getGridViewConfig () {
        if (!isset ($this->_gridViewConfig)) {
            $this->_gridViewConfig = array_merge (
                parent::getGridViewConfig (),
                array (
                    'possibleResultsPerPage' => array (5, 10, 20, 30, 40, 50, 75, 100),
                    'defaultGvSettings'=>array(
                        'isActive' => 65,
                        'fullName' => 125,
                        //'tagLine' => 100,
                        //'cellPhone' => 100,
                        'lastLogin' => 80,
                        'emailAddress' => 100,
                    ),
                    'template'=>
                        CHtml::openTag ('div', X2Html::mergeHtmlOptions (array (
                            'class' => 'page-title'
                        ), array (
                            'style' =>  
                                !CPropertyValue::ensureBoolean (
                                    $this->getWidgetProperty('showHeader')) &&
                                !CPropertyValue::ensureBoolean (
                                    $this->getWidgetProperty('hideFullHeader')) ?
                                    'display: none;' : ''

                        ))).
                        '<h2 class="grid-widget-title-bar-dummy-element">'.
                        '</h2>{buttons}{filterHint}'.
                        '{summary}{topPager}</div>{items}{pager}',
                    'includedFields'=>array (
                        'tagLine', 'username', 'officePhone', 'cellPhone', 'emailAddress', 
                        'googleId', 'isActive', 'leadRoutingAvailability',
                    ),
                    'specialColumns'=>array(
                        'fullName'=>array(
                            'name'=>'fullName',
                            'header'=>Yii::t('profile', 'Full Name'),
                            'value'=>'CHtml::link(CHtml::encode($data->fullName),array("view","id"=>$data->id))',
                            'type'=>'raw',
                        ),
                        'lastLogin'=>array(
                            'name'=>'lastLogin',
                            'header'=>Yii::t('profile', 'Last Login'),
                            'value'=>'$data->user ? ($data->user->lastLogin == 0 ? "" : '.
                                'Formatter::formatDateDynamic ($data->user->lastLogin)) : ""',
                            'type'=>'raw',
                        ),
                        'isActive'=>array(
                            'name'=>'isActive',
                            'header'=>Yii::t('profile', 'Active'),
                            'value'=>'"<span title=\''.
                                '".(Session::isOnline ($data->username) ? '.
                                 '"'.Yii::t('profile', 'Active User').'" : "'.
                                    Yii::t('profile', 'Inactive User').'")."\''.
                                ' class=\'".(Session::isOnline ($data->username) ? '.
                                '"active-indicator" : "inactive-indicator")."\'></span>"',
                            'type'=>'raw',
                        ),
                        'username' => array(
                            'name' => 'username',
                            'header' => Yii::t('profile','Username'),
                            'value' => '$data->user ? CHtml::encode($data->user->alias) : ""',
                            'type' => 'raw'
                        ),
                        'leadRoutingAvailability' => array(
                            'name' => 'leadRoutingAvailability',
                            'header' => Yii::t('profile','Lead Routing Availability'),
                            'value' => 'CHtml::encode($data->leadRoutingAvailability ? 
                                Yii::t("profile", "Available") :
                                Yii::t("profile", "Unavailable"))',
                            'type' => 'raw'
                        ),
                    ),
                    'enableControls'=>false,
                )
            );
        }
        return $this->_gridViewConfig;
    }

}
?>
