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
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_fields';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('modelName, fieldName, attributeLabel', 'length', 'max'=>250),
			array('modelName, fieldName, attributeLabel','required'),
			array('custom, modified, readOnly', 'boolean'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, modelName, fieldName, attributeLabel, custom, modified, readOnly', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		return array();
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => 'ID',
			'modelName' => 'Model Name',
			'fieldName' => 'Field Name',
			'attributeLabel' => 'Attribute Label',
			'custom' => 'Custom',
			'modified' => 'Modified',
			'readOnly' => 'Read Only',
                        'required' => "Required",
                        'searchable' => "Searchable",
                        'relevance' => 'Search Relevance',
		);
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
		$criteria->compare('modelName',$this->modelName,true);
		$criteria->compare('fieldName',$this->fieldName,true);
		$criteria->compare('attributeLabel',$this->attributeLabel,true);
		$criteria->compare('custom',$this->custom);
		$criteria->compare('modified',$this->modified);
		$criteria->compare('readOnly',$this->readOnly);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
	
	/**
	 * Finds a contact matching a full name; returns Contacts::name if a match was found, null otherwise.
	 * @param string $type
	 * @param string $name
	 * @return mixed 
	 */
	public static function getLinkId($type,$name) {
		if(strtolower($type) == 'contacts')
			$model = X2Model::model('Contacts')->find('CONCAT(firstName," ",lastName)=:name',array(':name'=>$name));
		else
			$model = X2Model::model(ucfirst($type))->findByAttributes(array('name'=>$name));
		if(isset($model))
			return $model->name;
		else
			return null;
	}
	
	/**
	 * Parses a value for table insertion using X2Fields rules
	 * @param string $type the type of field
	 * @param mixed $value
	 * @return mixed the parsed value
	 */
	public function parseValue($value) {
		if(in_array($this->type,array('int','float','currency','percentage'))) {
			return self::strToNumeric($value,$this->type);
		}
		switch($this->type) {
			case 'assignment':
				return ($this->linkType === 'multiple')? Accounts::parseUsers($value) : $value;
				
			case 'date':
			case 'dateTime':
				if(ctype_digit((string)$value))		// must already be a timestamp
					return $value;
				$value = $this->type === 'dateTime'? Formatter::parseDateTime($value) : Formatter::parseDate($value);
				return $value === false? null : $value;
				
			case 'link':
				if(empty($value) || empty($this->linkType) || is_int($value))	// if it's empty, then whatever; if it's already numeric, assume it's valid
					return $value;
				$linkId = Yii::app()->db->createCommand()
					->select('id')
					->from(X2Model::model($this->linkType)->tableName())
					->where('name=?',array($value))
					->queryScalar();
				return $linkId === false? $value : $linkId;
			case 'boolean':
				return (bool)$value;
			default:
				return $value;
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
	public static function strToNumeric($input, $type='float',$currencySymbol='$',$percentSymbol = '%') {
		$sign = 1;
		// Get rid of leading and trailing whitespace:
		$value = trim($input);
		if(strpos($value,'(') === 0) // Parentheses notation
			$sign = -1;
		if(strpos($value,'-') === 0) // Minus sign notation
			$sign = -1;
		$value = trim($value, "-() $percentSymbol$currencySymbol" . Yii::app()->locale->getCurrencySymbol(Yii::app()->params->admin->currency));

		if($value === null || $value === '')
			return $type=='float'?0.0:0;
		else if (!in_array($type, array('int', 'currency', 'float', 'percentage')))
			return $value;
		else if (!preg_match('/^([\d\.,]+)e?[\+\-]?\d*$/', $value)) // Unrecognized numeric string format
			return $input;
		if(in_array($type,array('float','currency','percentage'))) {
			// Determine numeric format (since there's no sure-fire way to
			// do the inverse of a number format and parse the numeric value
			// out through CLocale or the like)
			if(preg_match('/([,\.])\d*e?[\+\-]?\d*$/',$value,$num)) {
				$separator = $num[1];
				$part1NumberInd = strrpos($value,$separator);
				$part1Number = substr($value,0,$part1NumberInd);
				$part2Number = substr($value,$part1NumberInd+1);
				if(preg_match('/([,\.])/',$part1Number,$num)) {
					// This number is greater than 10^6 or is greater than 10^3
					// and not rounded to an integer; there's a second separator.
					if($num[1] == $separator) {
						// The number is at least 10^6 and is rounded to an integer;
						// 2+ separators were found, and they are exactly the same.
						return ((float) str_replace($num[1],'',$value))*$sign;
					} else {
						// Thousands separator found and is not the same as 
						// the separator nearest the end. Number is larger than
						// 10^3 and is not rounded to an integer.
						$part1Number = str_replace($num[1],'',$part1Number);
						return ((float) $part1Number.$separator.$part2Number)*$sign;
					}
				} else {
					// There was only one separator found. That indicates:
					// - Less than 10^3, not rounded to an integer, or:
					// - Between 10^3 and 10^6, and rounded to an integer, or:
					// - Scientific notation.
					//
					// At this point we can only try native typecasting (which 
					// will depend on the PHP locale), and if that fails, assume
					// that it's rounded to an integer, and strip out any
					// separators.
					if((float) $value == $value) {
						return ((float)$value)*$sign;
					} else {
						// Typecasting failed. Strip out all separators.
						$value = str_replace($separator,'',$value);
						return (float) $value == $value ? ((float) $value)*$sign : $input;
					}
				}
			} else {
				// No separators were found. It is rounded to an integer and
				// less than a thousand. Native typecasting should work fine.
				return ((float) $value)*$sign;

			}
		} else if($type == 'int') {
			return ((int) $value)*$sign;
		} else
			return $value;
		switch ($type) {
			case 'int':
				return ((int) $value)*$sign;
			case 'float':
				return ((float) $value)*$sign;
			case 'currency':
				return ((float) $value)*$sign;
			case 'percentage':
				return ((float) $value)*$sign;
			default: // Nothing will work. Spit the input back out.
				return $input;
		}
	}
}