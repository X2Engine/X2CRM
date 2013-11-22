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

/**
 * Enables transparent serialization and storage of array objects in database
 * fields as JSON strings. In contrast to JSONFieldsBehavior where JSON properties default to null,
 * here the transformAttributes array is treated as an associative array where the values are used 
 * as the default values for each JSON property. Note that a failure to explicitly define a 
 * default value in the transformAttributes array will result in potentially unexpected behavior
 * (e.g. array ('<JSON field 0>, <JSON field 1> => <JSON field 1 default value>), here 
 * <JSON field 0> would be the default value of a JSON field called '0').
 * @package X2CRM.components
 */
class JSONFieldsDefaultValuesBehavior extends JSONFieldsBehavior {

	/**
	 * Returns an array defining the expected structure of the JSON-bearing
	 * attribute specified by $name.
	 *
	 * @param
	 * @return type
	 */
	public function fields($name) {
		if(!isset($this->_fields)) {
			$this->_fields = array();
			foreach($this->transformAttributes as $attr => $fields) {
                $fieldArr = array ();
                foreach ($fields as $JSONAttrName => $defaultVal) {
                    $fields[$JSONAttrName] = $defaultVal;
                }
                $this->_fields[$attr] = $fields;
            }
		}
		return $this->_fields[$name];
	}

}
