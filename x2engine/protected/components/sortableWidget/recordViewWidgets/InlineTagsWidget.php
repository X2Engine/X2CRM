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

/**
 * Class for displaying tags on a record.
 * 
 * @package application.components.sortableWidget
 */
class InlineTagsWidget extends SortableWidget {

    /**
     * @var CActiveRecord Model whose tags are displayed in this widget
     */
	public $model;

    public $viewFile = '_inlineTagsWidget';

    private static $_JSONPropertiesStructure;

    private $_tags;

    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'Tags',
                    'hidden' => false,
                    'containerNumber' => 2,
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    /**
     * overrides parent method. Adds JS file necessary to run the setup script.
     */
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (
                parent::getPackages (),
                array (
                    'InlineTagsWidgetJS' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array(
                            'js/X2Tags/TagContainer.js',
                            'js/X2Tags/TagCreationContainer.js',
                            'js/X2Tags/InlineTagsContainer.js',
                        ),
                        'depends' => array ('auxlib'),
                    ),
                )
            );
        }
        return $this->_packages;
    }

    /**
     * @return array Tags associated with $this->model 
     */
    public function getTags () {
        if (!isset ($this->_tags)) {
            $this->_tags = Yii::app()->db->createCommand()
                ->select('COUNT(*) AS count, tag')
                ->from('x2_tags')
                ->where('type=:type AND itemId=:itemId',
                    array(
                        ':type'=>get_class($this->model),
                        ':itemId'=>$this->model->id
                    ))
                ->group('tag')
                ->order('count DESC')
                ->limit(20)
                ->queryAll();
        }
        return $this->_tags;
    }

    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $this->_viewFileParams = array_merge (
                parent::getViewFileParams (),
                array (
                    'model' => $this->model,
                    'tags' => $this->tags,
                )
            );
        }
        return $this->_viewFileParams;
    } 

    protected function getCss () {
        if (!isset ($this->_css)) {
            $this->_css = array_merge (
                parent::getCss (),
                array (
                'inlineTagsWidgetCSS' => "
                    #x2-tags-container {
                        padding: 7px;
                    }
                ")
            );
        }
        return $this->_css;
    }


}
