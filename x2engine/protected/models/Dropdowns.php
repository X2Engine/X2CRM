<?php
/* * *******************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 * ****************************************************************************** */

/**
 * This is the model class for table "x2_dropdowns".
 *
 * @package X2CRM.models
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

    /**
     * @return string the associated database table name
     */
    public function tableName(){
        return 'x2_dropdowns';
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
     * @param bool $multi wheter or not to include the "multi" column for distinguishing multiple selection from single selection
     */
    public static function getItems($id, $translationPack = null, $multi = false){
        $data = Yii::app()->db->createCommand()
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

    public function getDropdownIndex($id, $key){
        $arr = Dropdowns::getItems($id);
        if(array_search($key, $arr) !== false){
            return array_search($key, $arr);
        }else{
            return $key;
        }
    }

}
