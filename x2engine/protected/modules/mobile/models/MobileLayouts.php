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




class MobileLayouts extends CActiveRecord {

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

//    private $_layoutModel;
//    public function getLayoutModel () {
//        if (!isset ($this->_layoutModel)) {
//            $modelName = $this->modelName;
//            if (!is_subclass_of ($modelName, 'X2Model')) return null;
//            $this->_layoutModel =  $modelName::model ();
//        }
//        return $this->_layoutModel;
//    }

    /**
     * Generates a default layout from the desktop layout if available, or from the model's
     * fields otherwise.
     * @param string $type 'form' or 'view'
     * @param string $modelName
     * @return array
     */
    public static function generateDefaultLayout ($type, $modelName) {
        if ($type === 'form') {
            $layout = FormLayout::model()->findByAttributes(
                array(
                    'model' => ucfirst($modelName),
                    'defaultForm' => 1,
                    'scenario' => 'Default'
                ));
        } else {
            $layout = FormLayout::model()->findByAttributes(
                array(
                    'model' => ucfirst($modelName),
                    'defaultView' => 1,
                    'scenario' => 'Default'
                ));
        }
        $layoutData = array ();
        if ($layout) {
            $layout = CJSON::decode ($layout->layout);
            if (isset ($layout['sections'])) {
                foreach ($layout['sections'] as $section) {
                    foreach ($section['rows'] as $row) {
                        foreach ($row['cols'] as $col) {
                            foreach ($col['items'] as $item) {
                                if (isset ($item['name'])) {
                                    $fieldName = preg_replace(
                                        '/^formItem_/u', '', $item['name']);
                                    $layoutData[] = $fieldName;
                                }
                            }
                        }
                    }
                }
            }
        } elseif (is_subclass_of ($modelName, 'X2Model')) {
            $layoutData = Yii::app()->db->createCommand ()
                ->select ('fieldName')
                ->from ('x2_fields')
                ->where ('modelName=:modelName', array (':modelName' => $modelName))
                ->andWhere ($type==='view' ? 'readOnly' : 'true')
                ->queryColumn ();
        }
        return $layoutData;
    }

     
    public static function getFieldOptions (array $layout, $modelName) {
        // format layout options
        $model = new $modelName;
        $labelled = array ();
        foreach ($layout as $fieldName) {
            $field = $model->getField ($fieldName);
            if ($field) {
                $labelled[$fieldName] = $field->attributeLabel;
            }
        }
        $layout = $labelled;

        // get all field options
        $fields = Fields::model()->findAllByAttributes(
            array('modelName' => $modelName),
            new CDbCriteria(
                array(
                    'order' => 'attributeLabel ASC',
                    'condition' => 'keyType IS NULL OR (keyType!="PRI" AND keyType!="FIX")',
                )));

        // format field options
        $labelled = array ();
        foreach ($fields as $field) {
            $labelled[$field->fieldName] = $field->attributeLabel;
        }
        $fields = $labelled;

        $unselected = array_diff_key ($fields, $layout);
        return array ($layout, $unselected);
    }

    public static function getMobileLayout ($modelName, $type='view') {
        $mobileLayout = self::model ()->findByAttributes (array (
            'modelName' => $modelName ,
            'defaultView' => $type !== 'form',
            'defaultForm' => $type === 'form',
        ));
        return $mobileLayout;
    }

    public function attributeLabels () {
        return array (
            'modelName' => Yii::t('mobile', 'Record Type'),
            'defaultView' => Yii::t('mobile', 'Default View Layout'),
            'defaultForm' => Yii::t('mobile', 'Default Form Layout'),
            'layout' => Yii::t('mobile', 'Fields'),
        );
    }

    public function tableName () {
        return 'x2_mobile_layouts';
    }

    public function behaviors () {
       return array_merge (parent::behaviors (), array (
            'JSONFieldsBehavior' => array (
                'class' => 'application.components.behaviors.JSONFieldsBehavior',
                'transformAttributes' => array (
                    'layout',
                ),
            ),
       ));
    }

}

?>
