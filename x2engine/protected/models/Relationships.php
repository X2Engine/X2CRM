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
 * This is the model class for table "x2_relationships".
 *
 * @package application.models
 * @property integer $id
 * @property string $firstType
 * @property integer $firstId
 * @property string $secondType
 * @property integer $secondId
 */
class Relationships extends X2ActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @return Relationships the static model class
     */
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }

    /**
     * @return array model titles indexed by type name for types for which the current user has 
     *  module view permissions
     */
    public static function getRelationshipTypeOptions () {
        $options = X2Model::getModelTypesWhichSupportRelationships (true);
        foreach ($options as $model => $title) {
            $accessLevel = $model::model ()->getAccessLevel ();
            if ($accessLevel === X2PermissionsBehavior::QUERY_NONE) {
                unset ($options[$model]);
            }
        }
        return $options;
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'x2_relationships';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('firstId, secondId', 'numerical', 'integerOnly'=>true),
            array('firstType, secondType, firstLabel, secondLabel', 'length', 'max'=>100),
            array('firstType, secondType', 'linkables','on'=>'api'),
            array('firstType, firstId, secondType, secondId','required','on'=>'api'),
            array('firstType, secondType','validateType'),
            array('firstId, secondId','validateModel'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array(
                'firstType, firstId, firstLabel, secondType, secondId, secondLabel', 
                'safe', 'on'=>'search,api'),
        );
    }

    /**
     * Ensure that type corresponds to child of X2Model which supports relationships 
     */
    public function validateType ($attr) {
        $value = $this->$attr;
        $validTypes = X2Model::getModelTypesWhichSupportRelationships (true);
        if (!class_exists ($value) || !isset ($validTypes[$value])) {
            $this->addError ($attr, Yii::t('app', 'Invalid record type') );
        }
    }

    /**
     * Assuming that type is valid, ensure that id corresponds to an existing record 
     */
    public function validateModel ($attr) {
        $value = $this->$attr;
        $position = preg_replace ('/Id$/', '', $attr);
        $type = $this->{$position.'Type'};
        if (!$type::model ()->findByPk ($value)) {
            $this->addError ($attr, Yii::t('app', 'Record could not be found'));
        }
    }

    /**
     * @return bool true if relationship already exists, false otherwise 
     */
    public function hasDuplicates () {
        return ((int) Yii::app()->db->createCommand ()
            ->select ('count(*)')
            ->from ('x2_relationships')
            ->where ('
                firstType=:firstType0 AND firstId=:firstId0 AND secondType=:secondType0 AND
                secondId=:secondId0', 
                array (
                    ':firstType0' => $this->firstType,
                    ':firstId0' => $this->firstId,
                    ':secondType0' => $this->secondType,
                    ':secondId0' => $this->secondId,
                ))
            ->orWhere ('
                firstType=:firstType1 AND firstId=:firstId1 AND secondType=:secondType1 AND
                secondId=:secondId1', 
                array (
                    ':firstType1' => $this->secondType,
                    ':firstId1' => $this->secondId,
                    ':secondType1' => $this->firstType,
                    ':secondId1' => $this->firstId,
                ))
            ->queryScalar ()) > 0;
    }

    /**
     * Add duplicate validation  
     */
    public function beforeValidate () {
        $valid = parent::beforeValidate ();
        if ($valid) {
            if ($this->hasDuplicates ()) {
                $this->addError ('secondType', Yii::t('app', 'Relationship already exists'));
                $this->addErrors (
                    array_map (
                        function () { return null; }, 
                        array_flip (array ('firstType', 'firstId', 'secondId'))));
                $valid = false;
            }
        }
        return $valid;
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'firstType' => 'First Type',
            'firstId' => 'First',
            'firstLabel' => 'First Label',
            'secondType' => 'Second Type',
            'secondId' => 'Second',
            'secondLabel' => 'Second Label',
        );
    }

    /**
     * @param string $myType 
     * @param string $myId 
     * @return string
     * @throws CException
     */
    public function getLabel ($myType, $myId) {
        if ($this->firstType === $myType && $this->firstId == $myId) {
            return $this->secondLabel;
        } elseif ($this->secondType === $myType && $this->secondId == $myId) {
            return $this->firstLabel;
        }
        throw new CException ('myType and myId don\'t match either related model');
    }

    public function setFirstModel (X2Model $model) {
        $this->firstType = get_class ($model);
        $this->firstId = $model->id;
    }

    public function setSecondModel (X2Model $model) {
        $this->secondType = get_class ($model);
        $this->secondId = $model->id;
    }

    /**
     * @param string $myType 
     * @param string $myId 
     * @return null|CActiveRecord
     * @throws CException
     */
    private $_otherModel = array ();
    public function getOtherModel ($myType, $myId) {
        if (!isset ($this->_otherModel[$myType.$myId])) {
            if ($this->firstType === $myType && $this->firstId == $myId) {
                $model = X2Model::model ($this->secondType)->findByPk ($this->secondId);
            } elseif ($this->secondType === $myType && $this->secondId == $myId) {
                $model = X2Model::model ($this->firstType)->findByPk ($this->firstId);
            } else {
                throw new CException ('myType and myId don\'t match either related model');
            }
            $this->_otherModel[$myType.$myId] = $model;
        }
        return $this->_otherModel[$myType.$myId];
    }
    
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the 
     *  search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('firstType',$this->firstType,true);
        $criteria->compare('firstId',$this->firstId);
        $criteria->compare('firstLabel',$this->firstLabel);
        $criteria->compare('secondType',$this->secondType,true);
        $criteria->compare('secondId',$this->secondId);
        $criteria->compare('secondLabel',$this->secondLabel);

        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
        ));
    }

    public function linkables($attribute, $params) {
        if(!class_exists($this->$attribute))
            $this->addError(
                $attribute,
                Yii::t('app','Class "{class}" specified for {attribute} does not exist, so cannot create relationships with it.',array('{class}'=>$this->$attribute)));

        // See if the active record class has the linkable behavior:
        $staticModel = CActiveRecord::model($this->$attribute);
        $has = false;
        foreach($staticModel->behaviors() as $name=>$config){
            if($config['class'] == 'LinkableBehavior'){
                $has = true;
                break;
            }
        }
        if(!$has)
            $this->addError(
                $attribute,
                Yii::t('app','Class "{class}" specified for {attribute} does not have LinkableBehavior, and thus cannot be used with relationships.',array('{class}'=>$this->$attribute)));

        $model = $staticModel->findByPk($attribute=='firstType' ? $this->firstId : $this->secondId);
        if(!$model)
            $this->addError($attribute,Yii::t('app','Model record not found for {attribute}.'));
    }

}
