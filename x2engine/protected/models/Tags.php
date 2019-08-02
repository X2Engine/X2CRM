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
 * This is the model class for table "x2_tags".
 *
 * @package application.models
 * @property integer $id
 * @property string $type
 * @property integer $itemId
 * @property string $taggedBy
 * @property string $tag
 * @property integer $timestamp
 * @property string $itemName
 */
class Tags extends CActiveRecord {

    const DELIM = ',';

    /**
     * Returns the static model of the specified AR class.
     * @return Tags the static model class
     */
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'x2_tags';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array ('tag', 'validateTag'),
            array('type, itemId, taggedBy, tag', 'required'),
            array('itemId, timestamp', 'numerical', 'integerOnly'=>true),
            array('type, taggedBy', 'length', 'max'=>50),
            array('tag, itemName', 'length', 'max'=>250),
            array(
                'tag', 
                'application.extensions.unique-attributes-validator.UniqueAttributesValidator', 
                'with'=>'tag,type,itemId',
                'binary'=>true,
            ),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, type, itemId, taggedBy, tag, timestamp, itemName', 'safe', 'on'=>'search'),
        );
    }

    /**
     * Normalizes tag format before all other forms of tag validation
     */
    public function validateTag ($attr) {
        $this->$attr = self::normalizeTag ($this->$attr);
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'type' => 'Type',
            'itemId' => 'Item',
            'taggedBy' => 'Tagged By',
            'tag' => 'Tag',
            'timestamp' => 'Timestamp',
            'itemName' => 'Item Name',
        );
    }

    /*
     * Returns a list of all existing tags, without the # at the beginning
     */
    public static function getAllTags() {
        $tags = Yii::app()->db->createCommand()
            ->selectDistinct('tag')
            ->from('x2_tags')
            ->order('tag DESC')
            ->queryColumn();

        foreach ($tags as &$tag) {
            $tag = substr($tag, 1);
        }

        return $tags;
    }

    /**
     * Return a list of tag links associated with a specified model
     * @param $model Model type, e.g., "Contacts"
     * @param $id Model ID
     * @param $limit Number of tags to return, or -1 to disable
     * @return string HTML containing links to each tag
     */
    public static function getTagLinks($model,$id,$limit = -1) {
        // Disable limit in CDbCriteria with a value less than 0
        if(!is_numeric($limit) || empty($limit))
            $limit = -1;
    
        $tags = Tags::model()->findAllByAttributes(
            array('type'=>$model,'itemId'=>$id),
            new CDbCriteria(array('order'=>'id DESC','limit'=>$limit))
        );
        $tagCount = Tags::model()->countByAttributes(array('type'=>$model,'itemId'=>$id));
        
        $links = array();
        foreach($tags as &$tag) {
            $links[] = CHtml::link(
                CHtml::encode($tag->tag),array('/search/search','term'=>CHtml::encode($tag->tag)));
        }
        if($limit !== -1 && $tagCount > $limit)
            $links[] = '...';
            
        return implode(' ',$links);
    }
    
    
    
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('type',$this->type,true);
        $criteria->compare('itemId',$this->itemId);
        $criteria->compare('taggedBy',$this->taggedBy,true);
        $criteria->compare('tag',$this->tag,true);
        $criteria->compare('timestamp',$this->timestamp);
        $criteria->compare('itemName',$this->itemName,true);

        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
        ));
    }
    
    /**
     * Splits the provided string on commas, removes spaces, makes sure each tag has a hash
     * @param string $str a string containing 1 or more comma-separated tags
     * @param boolean $suppressHash if true, hash tag will not be prepended to tag and any existing
     *  leading hash tag will be removed.
     * @return array the properly formatted tags
     */
    public static function parseTags($str, $suppressHash=false) {
        $tags = array();
        
        foreach(explode(self::DELIM,$str) as $tag) {    // split the string
            $tag = trim($tag); 
            if(strlen($tag) > 0) {                    // eliminate empty tags
                $tags[] = self::normalizeTag ($tag, $suppressHash);
            }
        }
        return $tags;
    }

    public static function normalizeTag ($tag, $suppressHash=false) {
        $tag = trim($tag);      
        if (strpos ($tag, self::DELIM) !== false) {
            $tag = strtr($tag,array(self::DELIM => ''));
        }
        if(substr($tag,0,1) !== '#' && !$suppressHash) // make sure they have the hash
            $tag = '#'.$tag;
        if (substr ($tag, 0, 1) === '#' && $suppressHash) {
            $tag = preg_replace ('/^#/', '', $tag);
        }
        return $tag;
    }

    public static function normalizeTags (array $tags, $suppressHash=false) {
        foreach ($tags as &$tag) {
            $tag = Tags::normalizeTag ($tag, $suppressHash);
        }
        return $tags;
    }

}
