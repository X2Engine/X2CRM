<?php

/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * This is the model class for table "x2_fields".
 *
 * @package X2CRM.models
 * @property integer $id
 * @property string $modelName
 * @property string $fieldName
 * @property string $attributeLabel
 * @property integer $show
 * @property integer $custom
 */
class Fields extends CActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @return Fields the static model class
     */
    public static function model($className = __CLASS__){
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName(){
        return 'x2_fields';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules(){
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('modelName, fieldName, attributeLabel', 'length', 'max' => 250),
            array('modelName, fieldName, attributeLabel', 'required'),
            array('custom, modified, readOnly', 'boolean'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, modelName, fieldName, attributeLabel, custom, modified, readOnly', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations(){
        return array();
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels(){
        return array(
            'id' => Yii::t('admin','ID'),
            'modelName' => Yii::t('admin','Model Name'),
            'fieldName' => Yii::t('admin','Field Name'),
            'attributeLabel' => Yii::t('admin','Attribute Label'),
            'custom' => Yii::t('admin','Custom'),
            'modified' => Yii::t('admin','Modified'),
            'readOnly' => Yii::t('admin','Read Only'),
            'required' => Yii::t('admin',"Required"),
            'searchable' => Yii::t('admin',"Searchable"),
            'relevance' => Yii::t('admin','Search Relevance'),
            'uniqueConstraint' => Yii::t('admin','Unique'),
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
        $criteria->compare('modelName', $this->modelName, true);
        $criteria->compare('fieldName', $this->fieldName, true);
        $criteria->compare('attributeLabel', $this->attributeLabel, true);
        $criteria->compare('custom', $this->custom);
        $criteria->compare('modified', $this->modified);
        $criteria->compare('readOnly', $this->readOnly);

        return new CActiveDataProvider(get_class($this), array(
                    'criteria' => $criteria,
                ));
    }

    /**
     * Finds a contact matching a full name; returns Contacts::name if a match was found, null otherwise.
     * @param string $type
     * @param string $name
     * @return mixed
     */
    public static function getLinkId($type, $name){
        if(strtolower($type) == 'contacts')
            $model = X2Model::model('Contacts')->find('CONCAT(firstName," ",lastName)=:name', array(':name' => $name));
        else
            $model = X2Model::model(ucfirst($type))->findByAttributes(array('name' => $name));
        if(isset($model))
            return $model->name;
        else
            return null;
    }

    /**
     * Parses a value for table insertion using X2Fields rules
     * @param mixed $value
     * @param bool $filter If true, replace HTML special characters (prevents markup injection)
     * @return mixed the parsed value
     */
    public function parseValue($value,$filter = false){
        if(in_array($this->type, array('int', 'float', 'currency', 'percentage'))){
            return self::strToNumeric($value, $this->type);
        }
        switch($this->type){
            case 'assignment':
                return ($this->linkType === 'multiple') ? self::parseUsers($value) : $value;

            case 'date':
            case 'dateTime':
                if(ctype_digit((string) $value))  // must already be a timestamp
                    return $value;
                $value = $this->type === 'dateTime' ? Formatter::parseDateTime($value) : Formatter::parseDate($value);
                return $value === false ? null : $value;

            case 'link':
                if(empty($value) || empty($this->linkType) || is_int($value)) // if it's empty, then whatever; if it's already numeric, assume it's valid
                    return $value;
                $linkId = Yii::app()->db->createCommand()
                        ->select('id')
                        ->from(X2Model::model($this->linkType)->tableName())
                        ->where('name=?', array($value))
                        ->queryScalar();
                return $linkId === false ? $value : $linkId;
            case 'boolean':
                return (bool) $value;
	    case 'dropdown':
	       return is_array($value)?CJSON::encode($value):$value;
        default:
                return $filter ? CHtml::encode($value) : $value;
        }
    }

    /**
     * Converts a string into a numeric value.
     *
     * @param string $input The string to convert
     * @param string $type A hint as to the type of input; one of 'int', 'float', 'currency' or 'percentage'
     * @param string $currencySymbol Optional currency symbol to trim off the string before conversion
     * @param string $percentSymbol Optional percent symbol to trim off the string before conversion
     */
    public static function strToNumeric($input, $type = 'float'){
        $sign = 1;
        // Typecasting in the case that it's not a string
        $inType = gettype($input);
        if($inType != 'string'){
            if($type == $inType)
                return $inType;
            else
                return ($type == 'int' ? (int) $input : (float) $input);
        }

        // Get rid of leading and trailing whitespace:
        $value = trim($input);
        if(strpos($value, '(') === 0) // Parentheses notation
            $sign = -1;
        $posNeg = strpos($value, '-');
        if($posNeg === 0 || $posNeg === strlen($value) - 1) // Minus sign notation
            $sign = -1;
        // Strip out currency/percent symbols and digit group separators, but exclude null currency symbols:
        if(!function_exists('stripSymbols')){
            function stripSymbols($s){ return !empty($s); }
        }
        $stripSymbols = array_filter(array_values(Yii::app()->params->supportedCurrencySymbols), 'stripSymbols');
        // Just in case "Other" currency used: include that currency's symbol
        $defaultSym = Yii::app()->getLocale()->getCurrencySymbol(Yii::app()->params->admin->currency);
        if($defaultSym)
            if(!in_array($defaultSym, $stripSymbols))
                $stripSymbols[] = $defaultSym;
        $stripSymbols[] = '%';
        $grpSym = Yii::app()->getLocale()->getNumberSymbol('group');
        if(!empty($grpSym) && $type != 'percentage')
            $stripSymbols[] = $grpSym;
        $value = strtr($value, array_fill_keys($stripSymbols, ''));
        // Trim away negative symbols and any remaining whitespace:
        $value = trim($value, "-() ");
        $converted = strtr($value, array_flip(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES)));
        $value = trim($converted, chr(0xC2).chr(0xA0));
        // Turn null string into zero:
        if($value === null || $value === '')
            return ($type != 'int') ? 0.0 : 0;
        else if(!in_array($type, array('int', 'currency', 'float', 'percentage')))
            return $value; // Unrecognized type
        else if(!preg_match('/^([\d\.,]+)e?[\+\-]?\d*$/', $value))
            return $input; // Unreadable input

        $value = str_replace(Yii::app()->getLocale()->getNumberSymbol('decimal'), '.', $value);
        if(in_array($type, array('float', 'currency', 'percentage'))){
            return ((float) $value) * $sign;
        }else if($type == 'int'){
            return ((int) $value) * $sign;
        } else
            return $value;
    }

    public static function getFieldTypes($scenario = null){
        $fieldTypes = array(
            'varchar' => array(
                'title' => Yii::t('admin', 'Single Line Text'),
                'validator' =>'safe',
                'columnDefinition' => 'VARCHAR(255)',
            ),
            'text' => array(
                'title' => Yii::t('admin', 'Multiple Line Text Area'),
                'validator' => 'safe',
                'columnDefinition' => 'TEXT',
            ),
            'date' =>array(
                'title'=>Yii::t('admin','Date'),
                'validator'=>'int',
                'columnDefinition'=>'BIGINT',
            ),
            'dateTime' =>array(
                'title'=>Yii::t('admin','Date/Time'),
                'validator'=>'int',
                'columnDefinition'=>'BIGINT',
            ),
            'dropdown'=>array(
                'title'=>Yii::t('admin','Dropdown'),
                'validator'=>'safe',
                'columnDefinition'=>'VARCHAR(255)',
            ),
            'int'=>array(
                'title'=>Yii::t('admin','Number'),
                'validator'=> 'int',
                'columnDefinition'=>'BIGINT',
            ),
            'email'=>array(
                'title'=>Yii::t('admin','Email'),
                'validator'=>'email',
                'columnDefinition'=>'VARCHAR(255)',
            ),
            'currency'=>array(
                'title'=>Yii::t('admin','Currency'),
                'validator'=>'numerical',
                'columnDefinition'=>'DECIMAL(18,2)',
            ),
            'url'=>array(
                'title'=>Yii::t('admin','URL'),
                'validator'=>'safe',
                'columnDefinition'=>'VARCHAR(255)',
            ),
            'float'=>array(
                'title'=>Yii::t('admin','Decimal'),
                'validator'=>'numerical',
                'columnDefinition'=>'FLOAT'
            ),
            'boolean'=>array(
                'title'=>Yii::t('admin','Checkbox'),
                'validator'=>'boolean',
                'columnDefinition'=>'BOOLEAN NOT NULL DEFAULT 0',
            ),
            'link'=>array(
                'title'=>Yii::t('admin','Lookup'),
                'validator'=>'safe',
                'columnDefinition'=>'VARCHAR(255)',
            ),
            'rating'=>array(
                'title'=>Yii::t('admin','Rating'),
                'validator'=>'safe',
                'columnDefinition'=>'VARCHAR(255)',
            ),
            'assignment'=>array(
                'title'=>Yii::t('admin','Assignment'),
                'validator'=>'safe',
                'columnDefinition'=>'VARCHAR(255)',
            ),
            'visibility'=>array(
                'title'=>Yii::t('admin','Visibility'),
                'validator'=>'int',
                'columnDefinition'=>'INT',
            ),
        );
        if(empty($scenario)){
            return $fieldTypes;
        }else{
            if(!is_array($scenario)){
                $scenario = array($scenario);
            }
            $ret = array();
            if(in_array('validator', $scenario)){
                if(count($scenario) == 1){
                    foreach($fieldTypes as $fieldType => $data){
                        $ret[$fieldType] = $data['validator'];
                    }
                }else{
                    foreach($fieldTypes as $fieldType => $data){
                        $ret[$fieldType]['validator']=$data['validator'];
                    }
                }
            }
            if(in_array('title', $scenario)){
                if(count($scenario) == 1){
                    foreach($fieldTypes as $fieldType => $data){
                        $ret[$fieldType] = $data['title'];
                    }
                }else{
                    foreach($fieldTypes as $fieldType => $data){
                        $ret[$fieldType]['title']=$data['title'];
                    }
                }
            }
            if(in_array('columnDefinition', $scenario)){
                if(count($scenario) == 1){
                    foreach($fieldTypes as $fieldType => $data){
                        $ret[$fieldType] = $data['columnDefinition'];
                    }
                }else{
                    foreach($fieldTypes as $fieldType => $data){
                        $ret[$fieldType]['columnDefinition']=$data['columnDefinition'];
                    }
                }
            }
            return $ret;
        }
    }

     /**
     * Implodes an array of assignees
     */
    public static function parseUsers($arr){
		$str="";
        if(is_array($arr)){
            $str=implode(', ',$arr);
        } else if(is_string($arr))
            $str = $arr;
		return $str;
	}

	public static function parseUsersTwo($arr){
		$str="";
		if(is_array($arr)){
            $arr=array_keys($arr);
            $str=implode(', ',$arr);
        }

		return $str;
	}

}
