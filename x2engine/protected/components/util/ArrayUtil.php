<?php

/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * Standalone class with miscellaneous array functions
 * 
 * @package
 * @author Demitri Morgan <demitri@x2engine.com>, Derek Mueller <derek@x2engine.com>
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
     * A recursive version of normalizeToArray (). 
     *
	 * @param array $expectedFields The array with key => default value pairs
	 * @param array $currentFields The array to copy values from
	 * @return array
     */
	public static function normalizeToArrayR ($expectedFields, $currentFields) {
        $fields = array ();

        /* 
        Use values in current fields if they are present, otherwise use default values in
        expected fields. If the default value is an array, Apply array normalization 
        recursively.
        */
        foreach ($expectedFields as $key => $val) {
            if (is_array ($val) && isset ($currentFields[$key]) && 
                is_array ($currentFields[$key])) {

                $fields[$key] = self::normalizeToArrayR (
                    $expectedFields[$key], $currentFields[$key]);
            } else if (isset ($currentFields[$key])) {
                $fields[$key] = $currentFields[$key];
            } else {
                $fields[$key] = $expectedFields[$key];
            }
        }

        /*
        Maintain array ordering of current fields
        */
        $orderedFields = array ();
        foreach ($currentFields as $key => $val) {
            if (in_array ($key, array_keys ($fields))) {
                $orderedFields[$key] = $fields[$key];
                unset ($fields[$key]);
            }
        }

        /* 
        Add fields not specified in currentFields. These fields can't be sorted so they are 
        simply appended.
        */
        foreach ($fields as $key => $val) {
            $orderedFields[$key] = $fields[$key];
        }

        return $orderedFields;
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


    /**
     * Improved version of array_search that allows for regex searching
     *
     * @param string $find Regex to search on
     * @param array $in_array An array to search in
     * @param array $keys_found An array of keys which meet the regex
     * @return type Returns the an array of keys if $in_array is valid, or false if not.
     */
    public static function arraySearchPreg($find, $in_array, $keys_found = Array()) {
        if (is_array($in_array)) {
            foreach ($in_array as $key => $val) {
                if (is_array($val))
                    self::arraySearchPreg($find, $val, $keys_found);
                else {
                    if (preg_match('/' . $find . '/', $val))
                        $keys_found[] = $key;
                }
            }
            return $keys_found;
        }
        return false;
    }

}

?>
