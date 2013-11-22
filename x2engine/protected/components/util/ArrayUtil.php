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
 * Standalone class with miscellaneous array functions
 * 
 * @package
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class ArrayUtil {

	/**
	 * Given two associative arrays, returns an array with the same set of keys
	 * as the first, but with key/value pairs from the second if they are present.
	 * Any keys in the second and not in the first will be ignored/dropped.
	 *
	 * @param array $expectedFields The array with key => default value pairs
	 * @param array $currentFields The array to copy values from
	 * @return array
	 */
	public static function normalizeToArray($expectedFields, $currentFields){
		// Expected keys: defined in expectedFields
		$expKeys = array_keys($expectedFields);
		// Current keys: in the array to compare against
		$curKeys = array_keys($currentFields);
		// Keys to save: both already present in the current fields and defined in the expected fields
		$savKeys = array_intersect($expKeys, $curKeys);
		// New keys: that are not present in the current fields but defined in the expected fields
		$newKeys = array_diff($expKeys, $curKeys);
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
     * Determines whether a given array is associative
     *
     * @param array $array The array for which the check is made
     * @return bool True if $array is associative, false otherwise
     */
    public static function is_assoc ($array) {
        $keys = array_keys ($array);
        $type;
        foreach ($keys as $key) {
            if (gettype ($key) === 'string') {
                return true;
            }
        }
        return false;
    }
}

?>
