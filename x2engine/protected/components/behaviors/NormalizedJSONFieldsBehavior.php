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




Yii::import('application.components.util.ArrayUtil');
Yii::import('application.components.behaviors.JSONFieldsBehavior');

/**
 * Enables transparent serialization and storage of array objects in database
 * fields as JSON strings.
 * @package application.components
 * @author Demitri Morgan <demitri@x2engine.com>, Derek Mueller <derek@x2engine.com>
 */
class NormalizedJSONFieldsBehavior extends JSONFieldsBehavior {

	protected $_fields;

	protected $hasOptions = true;

    /**
     * If true, when setting the JSON field, the order of the current field values will be 
     *  maintained.
     * @param bool 
     */
    public $maintainCurrentFieldsOrder = false; 

	/**
	 * Returns an array defining the expected structure of the JSON-bearing
	 * attribute specified by $name.
	 *
	 * Child classes can override this method to specify default values for
	 * fields in the embedded JSON object other than null (in this class, all
	 * embedded fields within all attributes are null by default).
	 *
	 * @param
	 * @return type
	 */
	public function fields($name) {
		if(!isset($this->_fields)) {
			$this->_fields = array();
			// Assume all are null by default:
			foreach($this->transformAttributes as $attr => $fields)
				$this->_fields[$attr] = array_fill_keys($fields,null);
		}
		return $this->_fields[$name];
	}

	/**
	 * Normalizes the attribute array to the structure defined in {@link fields}
	 * and then JSON-encodes it to prepare it for saving.
	 * @param type $name
	 * @return type
	 */
	public function packAttribute($name){
		$fields = $this->fields($name);
		$attribute = $this->getOwner()->$name;
        $attribute = is_array($attribute) ? 
            ArrayUtil::normalizeToArrayR($fields,$attribute,$this->maintainCurrentFieldsOrder) : $fields;
		return CJSON::encode ($attribute);
	}

	/**
	 * JSON-decodes the value stored in the database column for the attribute,
	 * and then normalizes it to the structure defined in {@link fields}
	 * @param string $name The attribute to be unpacked
	 * @return type
	 */
	public function unpackAttribute($name){
		$fields = $this->fields($name);
		$attribute = CJSON::decode ($this->getOwner()->$name);
		$attribute = is_array($attribute) ? 
            ArrayUtil::normalizeToArrayR($fields,$attribute,$this->maintainCurrentFieldsOrder) : $fields;
		return $attribute;
	}
}

?>
