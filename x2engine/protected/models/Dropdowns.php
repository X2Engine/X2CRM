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
 * This is the model class for table "x2_dropdowns".
 *
 * @package application.models
 * @property integer $id
 * @property string $name
 * @property string $options
 */
class Dropdowns extends CActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @return Dropdowns the static model class
     */
    public static function model($className = __CLASS__){
        return parent::model($className);
    }

    public static function getSocialSubtypes () {
        $dropdown = Dropdowns::model()->findByPk(113);
        if (!$dropdown) return array ();
        return json_decode (
            $dropdown->options,true);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName(){
        return 'x2_dropdowns';
    }

    public function scopes () {
        return array (  
            'children' => array (
                'condition' => 'parent=:id',
                'params' => array (':id' => $this->id)
            )
        );
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules(){
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name', 'length', 'max' => 250),
            array('options', 'safe'),
            array('options,name', 'required'),
            array('multi', 'boolean'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, name, options', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations(){
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels(){
        return array(
            'id' => Yii::t('admin', 'ID'),
            'name' => Yii::t('admin', 'Name'),
            'options' => Yii::t('admin', 'Options'),
            'multi' => Yii::t('admin', 'Allow multiple values'),
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search(){
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('options', $this->options, true);

        return new CActiveDataProvider(get_class($this), array(
                    'criteria' => $criteria,
                ));
    }

    /**
     * Retrieves items for the dropdown of given id, and whether multiple selection is allowed.
     * @param integer $id
     * @param string $translationPack The translation module to use, if applicable
     * @param bool $multi wheter or not to include the "multi" column for distinguishing multiple 
     *  selection from single selection
     * @return array (<options>) or array ('options' => <options>, 'multi' => <multi>)
     */
    public static function getItems($id, $translationPack = null, $multi = false){
        $data = Yii::app()->db->cache (1000, null)->createCommand()
                ->select('options,multi')
                ->from('x2_dropdowns')
                ->where('id=:id', array(':id' => $id))
                ->queryRow();
        if(!empty($data)){
            $data['options'] = CJSON::decode($data['options']);
            $data['options'] = is_array($data['options']) ? $data['options'] : array();
            if(!empty($translationPack)){
                foreach(array_keys($data['options']) as $item){
                    $data['options'][$item] = Yii::t($translationPack, $data['options'][$item]);
                }
            }
        } else
            $data = array('options' => array(), 'multi' => false);
        return $multi ? $data : $data['options'];
    }

    /**
     * @return dropdown label or the value, if no corresponding label can be found
     */
    public function getDropdownValue($id, $index){
        $arr = Dropdowns::getItems($id, null, true);
        if($arr['multi']){
            $jdIndex = CJSON::decode($index);
            $index = empty($jdIndex) && is_string($index) ? array($index) : $jdIndex;
            if(!is_array($index))
                $index = array();
            return implode(', ', array_map(function($o)use($arr){
                return isset($arr[$o]) ? $arr[$o] : $o;
            }, $index));
        }
        if(isset($arr['options'])){
            $arr = $arr['options'];
        }
        if(isset($arr[$index])){
            return $arr[$index];
        }else{
            return $index;
        }
    }

    /**
     * Returns dropdown value(s) for given key(s)
     * @return array|string 
     */
    public function getDropdownIndex($id, $key){
        $arr = Dropdowns::getItems($id);
        if (is_array ($key)) {
            return array_map (function ($value) use ($arr) {
                $index = array_search($value, $arr);
                if ($index === false) {
                    return $value;
                } else {
                    return $index;
                }
            }, $key);
        } else {
            if(array_search($key, $arr) !== false){
                return array_search($key, $arr);
            }else{
                return $key;
            }
        }
    }

}
