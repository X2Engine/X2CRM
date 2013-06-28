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
 * Enables transparent serialization and storage of array objects in database
 * fields as JSON strings.
 * @package X2CRM.components
 * @author Demitri Morgan <demitri@x2engine.com>, Derek Mueller <derek@x2engine.com>
 */
class JSONFieldsBehavior extends TransformedFieldStorageBehavior {

	protected $_fields;

	protected $hasOptions = true;

	/**
	 * Given two associative arrays, returns an array with the same set of keys
	 * as the first, but with key/value pairs from the second if they are present.
	 * Any keys in the second and not in the first will be ignored/dropped.
	 * 
	 * @param array $expectedFields The array with key => default value pairs
	 * @param array $currentFields The array to copy values from
	 * @return array
	 */
	public static function normalizeToArray($expectedFields,$currentFields) {
		// Expected keys: defined in expectedFields
		$expKeys = array_keys($expectedFields);
		// Current keys: in the array to compare against
		$curKeys = array_keys($currentFields);
		// Keys to save: both already present in the current fields and defined in the expected fields
		$savKeys = array_intersect($expKeys,$curKeys);
		// New keys: that are not present in the current fields but defined in the expected fields
		$newKeys = array_diff($expKeys,$curKeys);
		// The array to return, with normalized data:
		$fields = array();

		// Use existing values
		foreach($savKeys as $fieldName)
			$fields[$fieldName] = $currentFields[$fieldName];
		// Use default values as defined in the expected fields
		foreach($newKeys as $fieldName)
			$fields[$fieldName] = $expectedFields[$fieldName];
		
		return $fields;
	}

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
        $attribute = is_array($attribute) ? self::normalizeToArray($fields,$attribute) : $fields;
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
		$attribute = is_array($attribute) ? self::normalizeToArray($fields,$attribute) : $fields;
		return $attribute;
	}
}

?>
