<?php
/*********************************************************************************
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
 ********************************************************************************/
Yii::import('application.components.util.ArrayUtil');
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
        $attribute = is_array($attribute) ? ArrayUtil::normalizeToArray($fields,$attribute) : $fields;
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
		$attribute = is_array($attribute) ? ArrayUtil::normalizeToArray($fields,$attribute) : $fields;
		return $attribute;
	}
}

?>
