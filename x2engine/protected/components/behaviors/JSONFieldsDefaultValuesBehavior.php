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




Yii::import ('application.components.behaviors.NormalizedJSONFieldsBehavior');

/**
 * Enables transparent serialization and storage of array objects in database
 * fields as JSON strings. In contrast to NormalizedJSONFieldsBehavior where JSON properties default to null,
 * here the transformAttributes array is treated as an associative array where the values are used 
 * as the default values for each JSON property. Note that a failure to explicitly define a 
 * default value in the transformAttributes array will result in potentially unexpected behavior
 * (e.g. array ('<JSON field 0>, <JSON field 1> => <JSON field 1 default value>), here 
 * <JSON field 0> would be the default value of a JSON field called '0').
 * @package application.components
 */
class JSONFieldsDefaultValuesBehavior extends NormalizedJSONFieldsBehavior {

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
                $this->_fields[$attr] = $fields;
            }
		}
		return $this->_fields[$name];
	}

    public function afterConstruct ($event) {
        $this->afterFind ($event);
    }

}
